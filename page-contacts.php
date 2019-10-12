<?php
/*
Template name: Контакты
*/
?>
<!DOCTYPE html>

<!-- Last Published: Sat Oct 12 2019 19:03:17 GMT+0000 (UTC) -->
<html data-wf-page="5da1baf65ac6b04ae11a4cdf" data-wf-site="5d9c423459eb471dbcd49727">

<?php get_template_part("header_block", ""); ?>

<body>
  <div id="top" class="post_header">
    <div data-collapse="medium" data-animation="default" data-duration="400" class="navbar w-nav">
    </div>
    <div class="header_box">
      <div class="blog_main_title"><?php the_title(); ?></div>
    </div>
  </div>
  <div class="content_section" data-content="post"><?php if(have_posts()) : ?><?php while (have_posts()) : the_post(); ?>
    <div class="cont">
      <div class="post_box">
        <div class="post_content w-richtext"><?php the_content(); ?></div>
        <div class="form-block-2 w-form">
          <form id="email-form" name="email-form" data-name="Email Form">
            <label for="node">Имя</label>
            <input type="text" maxlength="256" data-name="Имя" id="node-3" class="text-field w-input" />
            <label for="node">Телефон</label>
            <input type="tel" maxlength="256" data-name="Телефон" id="node-2" class="text-field w-input" />
            <label for="email">Email адрес</label>
            <input type="email" class="text-field w-input" maxlength="256" name="email" data-name="Email" id="email" required="" />
            <label for="node">Сообщение</label>
            <input type="text" maxlength="256" required="" data-name="Сообщение" id="node" class="text-field w-input" />
            <input type="submit" value="Отправить" data-wait="Please wait..." class="button-1 w-button" />
          </form>
          <div class="w-form-done">
            <div>Thank you! Your submission has been received!</div>
          </div>
          <div class="w-form-fail">
            <div>Oops! Something went wrong while submitting the form.</div>
          </div>
        </div>
      </div>
      <a href="/" class="btn back w-button">На главную</a>
    </div>
  <?php endwhile; ?><?php endif; ?></div>
  <div id="footer" class="footer">
    <div class="cont">
      <img src="<?php $field = get_field('logo_v_podvale', 'option'); if(isset($field['url'])){ echo($field['url']); }else{ echo($field); } ?>" data-w-id="2612fa18-070f-3941-f25d-82580807fe4b" alt="<?php if(isset($field['alt'])){ echo($field['alt']); } ?>" class="footer_logo" title="<?php if(isset($field['title'])){ echo($field['title']); } ?>" />
      <p class="p_white"><?= get_field('tekst_v_podvale', 'option') ?></p>
      <h3 class="h3 on_footer"><?= get_field('zagolovok_v_podvale', 'option') ?></h3>
      <?php if( have_rows('adresa_v_podvale', 'option') ){ ?><div><?php global $parent_id; $parent_id = $loop_id; $loop_index = 0; $loop_field = 'adresa_v_podvale'; while( have_rows('adresa_v_podvale', 'option') ){ global $loop_id; $loop_index++; $loop_id++; the_row(); ?>
<p class="p_white"><?= get_sub_field('adres_v_podvale') ?></p>
<?php } ?></div><?php } ?>
    </div>
  </div>
  <div data-collapse="medium" data-animation="default" data-duration="400" class="navbar w-nav">
    <div class="s1_nav_cont w-container">
      <a href="/" class="brand w-inline-block">
        <img src="<?php bloginfo('template_url'); ?>/images/5d9c423459eb4774e5d49852_logo-light.png" height="67" alt="" class="logo" />
      </a>
      <nav role="navigation" class="nav-menu w-nav-menu"><?php if( $menu_items = wp_get_nav_menu_items('Главн меню') ) { $menu_list = ''; $current_class = '';
foreach ( (array)$menu_items as $key => $menu_item ) {
if($menu_item->url === get_home_url(null, $wp->request).'/'){$current_class = ' w--current';} else {$current_class = '';}
if($menu_items[$key+1]->menu_item_parent != 0 && $menu_items[$key]->menu_item_parent == 0){
  $menu_list .= '<div data-ix="" class="" ><div class=""><div>'.$menu_item->title.'</div><div class=""></div></div><nav class="">
';
}else{ if($menu_items[$key]->menu_item_parent == 0){$link_class = 'nav-link w-nav-link';}else{$link_class = '';}
  $menu_list .= '<a class="'.$link_class.$current_class.'" title="'.$menu_item->attr_title.'" target="'.$menu_item->target.'" href="'.$menu_item->url.'">'.$menu_item->title.'</a>
';
  if($menu_items[$key+1]->menu_item_parent == 0 && $menu_items[$key]->menu_item_parent != 0){
    $menu_list .= '</nav></div>';
  }
}
} echo $menu_list; } ?></nav>
      <div class="menu-button w-nav-button">
        <div class="w-icon-nav-menu">
        </div>
      </div>
    </div>
  </div>
  
  
  <!--[if lte IE 9]><script src="//cdnjs.cloudflare.com/ajax/libs/placeholders/3.0.2/placeholders.min.js"></script><![endif]-->
  <?php get_template_part("footer_block", ""); ?>