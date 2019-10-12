<?php

require_once(dirname(dirname(dirname(__DIR__))).'/wp-load.php');

$mail_settings = [];
if( function_exists('get_field') ){

	$mail_profiles = get_field('email_profile', 'option');

	if( empty($mail_profiles) && isset($_POST['Форма']) ){
		$result['delay'] = 0;
		$result['status'] = 'error';
		$result['error'] = 'Формы не настроены.';
		$result['error_msg'] = 'Ошибка отправки!<br><br>Причина ошибки: <b>'.$result['error'].'</b>';
		die(json_encode($result));
	}

	if( is_array($mail_profiles) ){
		foreach($mail_profiles as $profile){
			if($profile['form'] === 'default') $default_profile = $profile;
		}
		foreach($mail_profiles as $profile){
			if( in_array($_POST['Форма'], array_map('trim', explode(',', $profile['form'])) )) $form_profile = $profile;
		}
	}

	if(!empty($form_profile)){
		$mail_settings = $form_profile;
	}else{
		$mail_settings = $default_profile;
	}

	if( !empty($form_profile) && !empty($default_profile) ){
		if( !$form_profile['smtp'] && $default_profile['smtp'] ){
			$mail_settings['smtp'] = true;
			$mail_settings['host'] = $default_profile['host'];
			$mail_settings['auth'] = $default_profile['auth'];
			$mail_settings['secure'] = $default_profile['secure'];
			$mail_settings['port'] = $default_profile['port'];
			$mail_settings['charset'] = $default_profile['charset'];
			$mail_settings['username'] = $default_profile['username'];
			$mail_settings['password'] = $default_profile['password'];
		}
	}
} else {
	die();
}

if( !isset($_POST['Форма']) ){

  print_js($mail_settings['recaptcha'], $mail_settings['recaptcha_site_key']);

} else {

$result = [];
$result['redirect'] = $mail_settings['redirect'];
$result['delay'] = $mail_settings['delay'];
$result['hide'] = $mail_settings['hide'];
$result['hide_lbox'] = $mail_settings['hide_lbox'];
$result['lbox_class'] = $mail_settings['lbox_class'];
$result['success_msg'] = $mail_settings['success_msg'];
$result['error_msg'] = $mail_settings['error_msg'];

$recaptcha_secret_key = $mail_settings['recaptcha_secret_key'];

$fields = "";
foreach($_POST as $key => $value){
  if($value === 'on'){ $value = 'Да'; }
  if($key === 'sendto') $email = $value;
	if($key === 'g-recaptcha-response' && $mail_settings['recaptcha'] ){
    $recaptcha = $value;
    if(!empty($recaptcha)){
      $google_url = "https://www.google.com/recaptcha/api/siteverify";
      $url = $google_url."?secret=".$recaptcha_secret_key."&response=".$recaptcha."&remoteip=".$_SERVER['REMOTE_ADDR'];
      $res = SiteVerify($url);
      $res = json_decode($res, true);
      if(!$res['success']){
        echo 'ERROR_RECAPTCHA'; die();
      }
    }else{
      echo 'ERROR_RECAPTCHA'; die();
    }
  } elseif($key === 'required_fields') {
    $required = explode(',', $value);
  } else {
    if(in_array($key, $required) && $value === ''){ echo 'ERROR_REQUIRED'; die(); }
    if(is_array($value)) {
      $fields .= str_replace('_',' ',$key).': <b>'.implode(', ', $value).'</b> <br />';
    }else{
      if($value !== ''){ $fields .= str_replace('_',' ',$key).': <b>'.$value.'</b> <br />'; }
    }
  }
}
$fields .= 'IP: <b>'.$_SERVER['REMOTE_ADDR'].'</b><br />';
$fields .= 'Browser: <b>'.$_SERVER['HTTP_USER_AGENT'].'</b><br />';

$mail_settings['subject'] = str_replace('%site%', $_SERVER['HTTP_HOST'], $mail_settings['subject']);
$mail_settings['subject'] = str_replace('%ip%', $_SERVER['REMOTE_ADDR'], $mail_settings['subject']);
$mail_settings['message'] = str_replace('%fields%', $fields, $mail_settings['message']);
$mail_settings['message'] = str_replace('%ip%', $_SERVER['REMOTE_ADDR'], $mail_settings['message']);
send_mail($mail_settings['email'], $mail_settings['subject'], $mail_settings['message']);

if( $mail_settings['reply'] && !empty($mail_settings['reply_email']) && isset($_POST[$mail_settings['reply_email']]) ){
	$mail_settings['reply_subject'] = str_replace('%site%', $_SERVER['HTTP_HOST'], $mail_settings['reply_subject']);
	$mail_settings['reply_message'] = str_replace('%fields%', $fields, $mail_settings['reply_message']);
  send_mail($_POST[$mail_settings['reply_email']], $mail_settings['reply_subject'], $mail_settings['reply_message'], true);
}

if( $mail_settings['export'] && $mail_settings['export_file'] !== '' ){
  $vars = explode(',', $mail_settings['export_fields']);
  $str_arr[] = '"'.date("d.m.y H:i:s").'"';
  foreach($vars as $var_name) {
    if(isset($_POST[$var_name])){ $str_arr[] = '"'.$_POST[$var_name].'"'; }
  }
  file_put_contents($mail_settings['export_file'], implode(';', $str_arr)."\n", FILE_APPEND | LOCK_EX);
}

}

function send_mail($to, $subject, $content, $reply_mode = false){
if (!class_exists('PHPMailer')) require_once(dirname(dirname(dirname(__DIR__))).'/wp-includes/class-phpmailer.php');
global $mail_settings, $result;

$mail = new PHPMailer(true);
if($mail_settings['smtp']) $mail->IsSMTP();
try {
  $mail->SMTPDebug  = 0;
  $mail->Host       = $mail_settings['host'];
  $mail->SMTPAuth   = $mail_settings['auth'];
  $mail->SMTPSecure = $mail_settings['secure'];
  $mail->Port       = $mail_settings['port'];
  $mail->CharSet    = $mail_settings['charset'];
  $mail->Username   = $mail_settings['username'];
  $mail->Password   = $mail_settings['password'];

  if(!empty($mail_settings['username'])) $mail->SetFrom($mail_settings['username'], $mail_settings['from']);
  if(!empty($mail_settings['addreply'])) $mail->AddReplyTo($mail_settings['addreply'], $mail_settings['from']);

  $to_array = explode(',', $to); foreach ($to_array as $to){ $mail->AddAddress($to); }
  if(!empty($mail_settings['cc'])){ $to_array = explode(',', $mail_settings['cc']); foreach ($to_array as $to){ $mail->AddCC($to); }}
  if(!empty($mail_settings['bcc'])){ $to_array = explode(',', $mail_settings['bcc']); foreach ($to_array as $to){ $mail->AddBCC($to); }}

  $mail->Subject = htmlspecialchars($subject);
  $mail->MsgHTML($content);

  if(!empty($mail_settings['reply_file']) && $reply_mode){
    $mail->AddAttachment(str_replace(get_site_url(), ABSPATH, $mail_settings['reply_file']['url']));
  } else if(!$reply_mode){
		$files_array = reArrayFiles($_FILES['file']);
	  if( $files_array !== false ){
	  foreach ($files_array as $file) {
	    if( $file['error'] === UPLOAD_ERR_OK ) $mail->AddAttachment($file['tmp_name'],$file['name']);
	  }}
  }

  $mail->Send();
  if(!$reply_mode){
		$result['status'] = 'success';
		echo json_encode($result);
	};

} catch (phpmailerException $e) {
	$result['delay'] = 0;
	$result['status'] = 'error';
	$result['error'] = strip_tags($e->errorMessage());
	$result['error_msg'] = $mail_settings['error_msg'].'<br><br>Причина ошибки: '.strip_tags($result['error']);
	echo json_encode($result);
} catch (Exception $e) {
	$result['delay'] = 0;
	$result['status'] = 'error';
	$result['error'] = strip_tags($e->getMessage());
	$result['error_msg'] = $mail_settings['error_msg'].'<br><br>Причина ошибки: '.strip_tags($result['error']);
	echo json_encode($result);
}
}

function reArrayFiles(&$file_post) {
    if($file_post === null){ return false; }
    $files_array = array();
    $file_count = count($file_post['name']);
    $file_keys = array_keys($file_post);
    for ($i=0; $i<$file_count; $i++) {
        foreach ($file_keys as $key) {
            $files_array[$i][$key] = $file_post[$key][$i];
        }
    }
    return $files_array;
}

function SiteVerify($url){
    $curl = curl_init();
    curl_setopt($curl, CURLOPT_URL, $url);
    curl_setopt($curl, CURLOPT_RETURNTRANSFER, 1);
    curl_setopt($curl, CURLOPT_TIMEOUT, 15);
    curl_setopt($curl, CURLOPT_USERAGENT, "Mozilla/5.0 (Windows NT 6.1; WOW64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/55.0.2883.87 Safari/537.36");
    $curlData = curl_exec($curl);
    curl_close($curl);
    return $curlData;
}

function print_js($recaptcha, $recaptcha_site_key) {
if( $recaptcha ){ ?>
<script src='https://www.google.com/recaptcha/api.js'></script><?php } ?>
<script id="mailer" type="text/javascript">

var $ = jQuery.noConflict();
var form_ids = [], cur_id = '';
$('.w-form form:not([action],[data-action])').each(function(){
	$(this).attr('action', '/').attr('method', 'post');
	cur_id = $(this).attr('id');
	if( cur_id === undefined ) {
		cur_id = 'form_id_'+form_ids.length;
		$(this).attr('id', cur_id);
	}else if(form_ids.indexOf(cur_id) !== -1){
		cur_id = cur_id+form_ids.length;
		$(this).attr('id', cur_id);
	}
	form_ids.push(cur_id);
	$(this).find('.g-recaptcha').attr('data-sitekey', '<?= $recaptcha_site_key ?>');
});

$('.w-form [data-name]').each(function(indx) { $(this).attr('name', $(this).attr('data-name')); });
$('textarea').focus(function(){if($(this).val().trim() === '') $(this).val('');});
$('textarea').each(function(){if($(this).val().trim() === '') $(this).val('');});

jQuery(document).ready(function($){

  $('.w-form form[action = "/"]').submit(function(e) {

		e.preventDefault();

		action = '<?php bloginfo('template_url'); ?>/mailer.php';
    cur_id = '#' + $(this).attr('id');

		$(cur_id).parent().find('.w-form-done,.w-form-fail').hide();

    cur_action = $(cur_id).attr('action');
    if (cur_action !== '/') {
      action = cur_action;
    }

		submit_input = $(cur_id).find('[type = submit]');
		submit_label = submit_input.val();
    if (submit_input.attr('data-wait') === 'Please wait...') {
      submit_input.val('Идет отправка...');
    }else{
			submit_input.val(submit_input.attr('data-wait'));
		}

    if($(cur_id+' [name=Форма]').is('input')){
      $(cur_id).find('[name=Форма]').val($(cur_id).attr('data-name'));
    } else {
      $('<input type="hidden" data-name="Форма" name="Форма" value="' + $(cur_id).attr('data-name') + '">').prependTo(cur_id);
    }

    if($(cur_id+' [name=Запрос]').is('input')){
      $(cur_id).find('[name=Запрос]').val(document.location.search);
    } else {
      $('<input type="hidden" data-name="Запрос" name="Запрос" value="' + document.location.search + '">').prependTo(cur_id);
    }

    if($(cur_id+' [name=Заголовок]').is('input')){
      $(cur_id).find('[name=Заголовок]').val(document.title);
    } else {
      $('<input type="hidden" data-name="Заголовок" name="Заголовок" value="' + document.title + '">').prependTo(cur_id);
    }

    if($(cur_id+' [name=Страница]').is('input')){
      $(cur_id).find('[name=Страница]').val(document.location.origin + document.location.pathname);
    } else {
      $('<input type="hidden" data-name="Страница" name="Страница" value="' + document.location.origin + document.location.pathname + '">').prependTo(cur_id);
    }

    $('<input type="hidden" name="required_fields">').prependTo(cur_id);
    required_fields = '';

    required_fields = '';
    $(cur_id).find('[required=required]').each(function() {
      required_fields = required_fields + ',' + $(this).attr('name');
    });
    if(required_fields !== '') { $(cur_id).find('[name=required_fields]').val(required_fields); }

    var formData = new FormData($(cur_id)[0]);
    $.ajax({
        url: action,
        type: 'POST',
        processData: false,
        contentType: false,
        data: formData
      })
      .done(function(result) {

				if( result === 'ERROR_RECAPTCHA' ) {
					alert('Подтвердите, что вы не робот!');
					submit_input.val(submit_label);
					return;
				}

				if( !isJson(result) ) {
					console.log(result);
					alert('Ошибка отправки!');
					return;
				}

				result = JSON.parse(result);

				if(result['success_msg'] != '') {
					$(cur_id).parent().find('.w-form-done').html('<div>'+result['success_msg']+'</div>');
				}

				$(cur_id).parent().find('.w-form-fail').html('<div>'+result['error_msg']+'</div>');

				submit_input.val(submit_label);

        if(result['status'] == 'success'){
          if(result['redirect'] !== '' && result['redirect'] !== '/-') {
            document.location.href = result['redirect'];
            return (true);
          }
          $(cur_id).siblings('.w-form-fail').hide();
          replay_class = '.w-form-done';
          replay_msg = result['success_msg'];
        } else {
          $(cur_id).siblings('.w-form-done').hide();
          if(result['error'] === 'ERROR_REQUIRED') {
            replay_msg = 'Не заполнено обязательное поле!'
          } else {
            replay_msg = result['error_msg'];
          }
          replay_class = '.w-form-fail';
        }

				replay_div = $(cur_id).siblings(replay_class);
        replay_div.show();
        if(result['hide']) {
          $(cur_id).hide();
        }

				result['delay'] = parseInt(result['delay']);
        if(result['delay'] !== 0) {
          if(result['hide_lbox'] && result['status'] == 'success') {
            $('.'+result['lbox_class']).delay(result['delay']).fadeOut();
          }
          replay_div.delay(result['delay']).fadeOut();
          $(cur_id).delay(result['delay']+1000).fadeIn();
        }

        if(result['status'] == 'success') {
          $(cur_id).trigger("reset");
          $(this).siblings('div[for]').text('');
          $(this).find('textarea').val('');
        }
      });
  });

	$('label[for^=file]').each(function() {
	  file_id = $(this).attr('for');
	  $(this).after('<input name="file[]" type="file" id="' + file_id + '" multiple style="display:none;">');
	  $(this).siblings('div[for]').hide();
	  $('input#' + file_id).change(function() {
	    file_name = $(this).val().replace('C:\\fakepath\\', "");
	    file_text = $(this).siblings('div[for]').text().replace('%file%', file_name);
	    if(file_text.trim() === '') file_text = 'Файл прикреплен.';
	    $(this).siblings('div[for]').text(file_text).show();
	  });
	});
});

function isJson(str) {
    try {
        JSON.parse(str);
    } catch (e) {
        return false;
    }
    return true;
}

</script>
<?php
}
?>
