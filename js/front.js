jQuery(document).ready(function($){

$('[href*=\"brandjs\"]').attr('style', 'display:none !important');

if( $('[data-load]')[0] ){

$('[data-load-part]:first').each(function(){
  $('body').attr('data-load-current', $(this).attr('data-load-part'));
});

$('[data-set-part]').click(function(){
  $('body').attr('data-load-current', $(this).attr('data-set-part'));
});

$('[data-load]').click(function(){
  var part = $(this).attr('data-load'); $('body').attr('data-load-current', part);
  var max_pages = $('[data-load-part='+part+']:last').attr('data-max-pages');
  var current_page = $('[data-load-part='+part+']:last').attr('data-current-page');
  var data = {
    'query' : query_vars,
    'page' : current_page,
    'part' : part
  };
  if( current_page != max_pages ){
    ajaxs('ajaxs_load_posts', data, function(data){
      if( data ) {
          cur_part = $('body').attr('data-load-current');
          if( cur_part !== undefined ){
            data_load = '[data-load='+cur_part+']';
          }else{
            data_load = '[data-load]';
          }
  				$(data_load).before(data);
  				current_page++;
  				if (current_page == max_pages) $(data_load).remove();
          Webflow.destroy(); Webflow.ready();
  		} else {
  			$(data_load).remove();
  		}
    });
  }
});

if( $('body').attr('data-load-scroll') !== undefined ){
  $(window).scroll(function(){
    var scroll_offset = $('body').attr('data-load-scroll');
    var part = $('body').attr('data-load-current');
    var max_pages = $('[data-load-part='+part+']:last').attr('data-max-pages');
    var current_page = $('[data-load-part='+part+']:last').attr('data-current-page');
    var data = {
      'query' : query_vars,
      'page' : current_page,
      'part' : part
    };
    if( $(document).scrollTop() > ($(document).height() - scroll_offset) && current_page != max_pages && !$('body').hasClass('loading')){
      $('body').addClass('loading');
      ajaxs('ajaxs_load_posts', data, function(data){
        if( data ) {
          cur_part = $('body').attr('data-load-current');
          if( cur_part !== undefined ){
            data_load = '[data-load='+cur_part+']';
          }else{
            data_load = '[data-load]';
          }
          $('body').removeClass('loading');
  				$(data_load).before(data);
  				current_page++;
          if (current_page == max_pages) $(data_load).remove();
          Webflow.destroy(); Webflow.ready();
    		}
      });
    }
  });
}
}

$('[data-copy]').click(function(){
  params = $(this).attr('data-copy').split(' ');
  $('.'+params[1]).html($(this).parent().find('.'+params[0]).html());
	Webflow.ready();
});

$('[data-object=wp_term_menu] a').each(function(){
if(document.URL === $(this).attr('href')){
  $(this).addClass('active');
	$(this).addClass('w--current');
	$(this).parents().each(function(){
		if($(this).attr('data-object') === 'wp_term_menu') return false;
    $(this).addClass('active');
    $(this).addClass('w--current');
	});
}
});

$('a').not('.w--current,.active').each(function(){
if(document.URL === $(this).attr('href')){
	$(this).addClass('w--current');
}
});

// адаптивность изображений
/* $('img').each(function(){
	$(this).removeAttr('height');
}); */

// обработка полей фильтра
$('[name=search_filter]').submit(function(e){
	var form = $(this);
	if($(this).find('[name=s]').val() === ''){
		$(this).find('[name=s]').removeAttr('name');
	}
	$(this).find('[data-taxonomy]').each(function(){
		values = [];
		taxonomy = $(this).attr('data-taxonomy');
		$(this).find('input:checked:enabled').each(function(){
			values.push($(this).val());
			$(this).removeAttr('name');
		})
		values = values.join(',');
		if(values != '') {
			form.append('<input type = "hidden" name = "'+taxonomy+'" value = "'+values+'">');
		}
	});
	$(this).find('.w-input').each(function(){
		if($(this).attr('data-value') === $(this).val()){
			$(this).removeAttr('name');
		}
	});
});

// слайдер диапозонов значений
$('[data-range-slider]').each( function(){
	var field = $(this).attr('data-range-slider');
	$(this).slider({
	step: parseInt($(this).attr('data-ui-slider')),
	range: true,
	min: parseInt($("[name=min_pm_"+field+"]").attr('data-value')),
	max: parseInt($("[name=max_pm_"+field+"]").attr('data-value')),
	values: [ parseInt($("[name=min_pm_"+field+"]").val()), parseInt($("[name=max_pm_"+field+"]").val()) ],
	slide: function(event, ui) {
		$("[name=min_pm_"+field+"]").val(ui.values[0]).keyup();
		$("[name=max_pm_"+field+"]").val(ui.values[1]).keyup();
	}
	});
});

});
