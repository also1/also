<?php

defined( 'ABSPATH' ) || exit;

$theme_version = '1570911575';

add_action('init', 'get_theme_path');
function get_theme_path(){
	if( isset( $_GET['theme_path'] ) ) {
		echo get_bloginfo('template_url');
		exit;
	}
}

if(file_exists(dirname( __FILE__ ).'/dynamic_functions.php')){ include_once 'dynamic_functions.php'; }
if(file_exists(dirname( __FILE__ ).'/shop_functions.php')){ include_once 'shop_functions.php'; }
if(file_exists(dirname( __FILE__ ).'/custom_functions.php')){ include_once 'custom_functions.php'; }
if(file_exists(dirname( __FILE__ ).'/configurator.php')){ include_once 'configurator.php'; }

require_once( ABSPATH . 'wp-admin/includes/plugin.php' );
if(file_exists(dirname( __FILE__ ).'/vendor/ajax-simply') && !is_plugin_active('ajax-simply/ajax-simply.php')){
  include_once 'vendor/ajax-simply/ajax-simply.php';
}

add_theme_support('menus');
add_theme_support('woocommerce');
add_theme_support('post-thumbnails');
add_filter('widget_text', 'do_shortcode');

function add_admin_scripts() {
  wp_register_script('libs_script', get_template_directory_uri().'/js/libs.js', array('jquery'),false,true);
  wp_enqueue_script('libs_script');
  wp_register_script('admin_script', get_template_directory_uri().'/js/admin.js', array('jquery'),false,true);
  wp_enqueue_script('admin_script');
  wp_enqueue_style('admin-styles', get_template_directory_uri().'/css/admin.css');
}
add_action( 'admin_enqueue_scripts', 'add_admin_scripts' );

function add_site_scripts() {
  global $theme_version;
	wp_enqueue_style('admin-styles', get_template_directory_uri().'/css/admin.css');
  wp_deregister_script( 'jquery-core' );
  wp_register_script( 'jquery-core', '//ajax.googleapis.com/ajax/libs/jquery/3.3.1/jquery.min.js',false,false,true);
  wp_enqueue_script( 'jquery' );
}
add_action( 'wp_enqueue_scripts', 'add_site_scripts' );

function remove_jquery_migrate( &$scripts ) {
 if( !is_admin() ) {
 $scripts->remove( 'jquery' );
 $scripts->add( 'jquery', false, array( 'jquery-core' ), '1.12.4' );
 }
}
add_filter( 'wp_default_scripts', 'remove_jquery_migrate' );

if (!is_admin()) {
   wp_enqueue_script("jquery-ui-core", array('jquery'));
   wp_enqueue_script("jquery-ui-slider",
   array('jquery','jquery-ui-core'));
}

function slugify($text)
{
    $translation = [
        'а' => 'a', 'б' => 'b', 'в' => 'v', 'г' => 'g', 'д' => 'd', 'ж' => 'zh',
        'з' => 'z', 'и' => 'i', 'й' => 'j', 'к' => 'k', 'л' => 'l', 'м' => 'm',
        'н' => 'n', 'о' => 'o', 'п' => 'p', 'р' => 'r', 'с' => 's', 'т' => 't',
        'у' => 'u', 'ф' => 'f', 'х' => 'h', 'ц' => 'c', 'ч' => 'ch', 'ш' => 'sh',
        'щ' => 'sch', 'ы' => 'y', 'э' => 'e', 'ю' => 'yu', 'я' => 'ya', 'і' => 'i',
        'ї' => 'yi', 'є' => 'ye', 'ґ' => 'g', 'е' => 'e', 'ё' => 'e',
        '\'' => '', '"' => '', '`' => '', 'ь' => '', 'ъ' => ''
    ];
    $text = trim($text);
    $text = mb_convert_case($text, MB_CASE_LOWER, "UTF-8");
    $text = strtr($text, $translation);
    $text = preg_replace('~(\W+)~', '_', $text);
    $text = trim($text, '_');
    $text = strtolower($text);
    return $text;
}

function get_layout_var($layout_field, $layout_name, $sub_field, $post_id = ''){
	if($post_id === '') $post_id = get_the_ID();
	foreach(get_field($layout_field, $post_id) as $layout){
		if($layout['acf_fc_layout'] === $layout_name){
			return $layout[$sub_field];
		}
	}
	return '';
}

function get_range_meta_value($post_type, $meta_field, $range){
	global $wpdb;
	$value = $wpdb->get_var("SELECT $range(CAST(meta_value AS UNSIGNED)) FROM `wp_postmeta` WHERE meta_key = '$meta_field'");
	if($value == '') $value = 0;
	return $value;
}

function getTerm( $term_name ){
  $terms = get_the_terms(get_the_ID(), $term_name);
  return $terms[0]->name ;
}

function getCatID(){
  global $wp_query;
  if(is_category() || is_single()){
		$cat_ID = get_query_var('cat');
  }
  return $cat_ID;
}

add_shortcode( 'show_file', 'show_file_func' );
function show_file_func( $atts ) {
    extract( shortcode_atts( array(
      'file' => ''
    ), $atts ) );
    if ($file!='') return @file_get_contents($file);
}

if (is_admin()) {
  foreach (get_taxonomies() as $taxonomy) {
    add_action("manage_edit-${taxonomy}_columns", 'tax_add_col');
    add_filter("manage_edit-${taxonomy}_sortable_columns", 'tax_add_col');
    add_filter("manage_${taxonomy}_custom_column", 'tax_show_id', 10, 3);
  }
  add_action('admin_print_styles-edit-tags.php', 'tax_id_style');
  function tax_add_col($columns) {return $columns + array ('tax_id' => 'ID');}
  function tax_show_id($v, $name, $id) {return 'tax_id' === $name ? $id : $v;}
  function tax_id_style() {print '<style>#tax_id{width:4em}</style>';}

  add_filter('manage_posts_columns', 'posts_add_col', 5);
  add_action('manage_posts_custom_column', 'posts_show_id', 5, 2);
  add_filter('manage_pages_columns', 'posts_add_col', 5);
  add_action('manage_pages_custom_column', 'posts_show_id', 5, 2);
  add_action('admin_print_styles-edit.php', 'posts_id_style');
  function posts_add_col($defaults) {$defaults['wps_post_id'] = __('ID'); return $defaults;}
  function posts_show_id($column_name, $id) {if ($column_name === 'wps_post_id') echo $id;}
  function posts_id_style() {print '<style>#wps_post_id{width:4em}</style>';}
}

function isCurrentLink($test_link){
  if($test_link == 'http://'.$_SERVER['HTTP_HOST'].$_SERVER['REQUEST_URI'] || ($test_link == site_url().'/' && substr_count(get_permalink(), 'p=') != 0 )){
    $current_class = ' w--current';}
  else{
    $current_class = '';
  }
  return $current_class;
}

function get_id_by_slug($page_slug) {
  $page = get_page_by_path($page_slug);
  if ($page) {
    return $page->ID;
  } else {
    return null;
  }
}

function posts_schet_class() {
  global $post_num;
  if ( ++$post_num % 2 )
    $class = 'nechet';
  else
    $class = 'chet';
  return $class;
}

function post_parity() {
  global $post_num;
  if ( ++$post_num % 2 )
    return 'odd';
  else
    return 'even';
}

add_filter('upload_mimes', 'my_myme_types', 1, 1);
function my_myme_types($mime_types){
    $mime_types['svg'] = 'image/svg+xml'; // поддержка SVG
    return $mime_types;
}

add_filter('post_thumbnail_html', 'remove_width_attribute', 10);
add_filter('image_send_to_editor', 'remove_width_attribute', 10);
function remove_width_attribute($html) {
   $html = preg_replace('/(width|height)="\d*"\s/', "", $html);
   return $html;
}

add_action('init', 'remheadlink');
function remheadlink()
{
  remove_action('wp_head', 'rsd_link');
  remove_action('wp_head', 'wlwmanifest_link');
  remove_action('wp_head', 'wp_generator');
  remove_action('wp_head', 'wp_shortlink_wp_head');
  remove_action('wp_head', 'adjacent_posts_rel_link_wp_head');
  remove_action('wp_head', 'feed_links_extra', 3);
}

function msg($message){
  echo('<pre style="z-index:10000; position:absolute; background-color:yellow; padding:5px;">');
  print_r($message);
  echo('</pre>');
}

add_action( 'after_switch_theme', 'bt_flush_rewrite_rules' );
function bt_flush_rewrite_rules() {
	 flush_rewrite_rules();
}

function wtw_change_settings( $option, $old_value, $value ){
	if( substr($option, 0, 25) === 'options_custom_post_types'
  || substr($option, 0, 25) === 'options_custom_taxonomies' ){
    update_option( 'wtw_settings_changed', true );
  }
}
add_action( 'updated_option', 'wtw_change_settings', 10, 3 );

function wtw_flush_rewrite()
{
  if ( get_option( 'wtw_settings_changed' ) == true ) {
      flush_rewrite_rules();
      update_option( 'wtw_settings_changed', false );
  }
}
add_action( 'admin_init', 'wtw_flush_rewrite' );

add_filter( 'template_include', 'set_custom_templates' );
function set_custom_templates( $original_template ) {
	global $wp_query;
	if (is_tax() && get_queried_object()->parent){
		$child_template = str_replace('.php', '-child.php', $original_template);
		if( file_exists($child_template) ){
			return $child_template;
		} else {
			return $original_template;
		}
	} elseif( $wp_query->is_posts_page ) {
		if( file_exists(TEMPLATEPATH.'/archive-post.php') ){
			return TEMPLATEPATH.'/archive-post.php';
		} else {
			return TEMPLATEPATH.'/archive.php';
		}
	} else {
		return $original_template;
  }
}

add_filter( 'search_template', 'change_search_template' );
function change_search_template( $template ){
	if($_GET['post_type'] != '' && $_GET['post_type'] != 'post' && $_GET['post_type'] != 'page'){
		return locate_template('archive-'.$_GET['post_type'].'.php');
	}else{
		return locate_template('search.php');
	}
}

function wp_admin_bar_options() {
global $wp_admin_bar;
$wp_admin_bar->add_menu(array(
'id' => 'wp-admin-bar-options',
'title' => __('Опции сайта'),
'href' => get_site_url().'/wp-admin/themes.php?page=options'
));
}
add_action('wp_before_admin_bar_render', 'wp_admin_bar_options');

if(function_exists('acf_add_options_page') && current_user_can('manage_options')) {
	acf_add_options_page(array(
		'page_title' 	=> 'Опции',
		'menu_title' 	=> 'Опции',
		'menu_slug' 	=> 'options',
		'parent_slug'	=> 'themes.php',
		'update_button'		=> __('Update'),
		'updated_message'	=> __("Item updated."),
		'autoload' 		=> true
	));
}

if(function_exists('acf_add_options_page') && current_user_can('manage_options')) {
	acf_add_options_page(array(
		'page_title' 	=> 'Конфигуратор сайта',
		'menu_title' 	=> 'Конфигуратор',
		'menu_slug' 	=> 'config',
    'icon_url' => 'dashicons-screenoptions',
		'parent_slug'	=> 'tools.php',
		'update_button'		=> __('Update'),
		'updated_message'	=> __("Item updated."),
		'autoload' 		=> true
	));
}

add_filter('acf/load_field/name=taxonomy_for_query', 'get_taxonomies_for_query');
function get_taxonomies_for_query( $field ) {
  $taxonomies = get_taxonomies();
  unset($taxonomies['category']);
  unset($taxonomies['post_tag']);
  foreach($taxonomies as $key => $value){
    $tax = get_taxonomy($key);
    $taxonomies[$key] = get_taxonomy_labels($tax)->singular_name.' ('.$key.')';
  }
  $field['choices']['category_name'] = 'Рубрика (category)';
  $field['choices']['tag'] = 'Метка (post_tag)';
  $field['choices'] = array_merge($field['choices'], $taxonomies);
  return $field;
}

add_filter('acf/load_field/name=taxonomy_select', 'get_taxonomies_select');
function get_taxonomies_select( $field ) {
  $taxonomies = get_taxonomies();
  foreach($taxonomies as $key => $value){
    $tax = get_taxonomy($key);
    $taxonomies[$key] = get_taxonomy_labels($tax)->singular_name.' ('.$key.')';
  }
  $field['choices'] = array_merge($field['choices'], $taxonomies);
  return $field;
}

add_filter('acf/load_field/name=post_type_select', 'get_post_type_select');
function get_post_type_select( $field ) {
  $post_types = get_post_types();
  foreach($post_types as $key => $value){
    $post_type = get_post_type_object($key);
    $post_types[$key] = get_post_type_labels($post_type)->singular_name.' ('.$key.')';
  }
  $field['choices'] = $post_types;
  return $field;
}

function select_query_by_name($query_name){
  $args = [];
  if(function_exists('have_rows')){
    if(have_rows('custom_query','option')):
      while(have_rows('custom_query','option')) : the_row();
        if(get_sub_field('name') === $query_name){
          $args['post_type'] = get_sub_field('post_type_select');
          $args['posts_per_page'] = get_sub_field('posts_per_page') === '' ? -1 : get_sub_field('posts_per_page');
          if(get_sub_field('paged')) $args['paged'] = get_query_var('paged');
          while(have_rows('taxonomy')) : the_row();
            $args[get_sub_field('taxonomy_for_query')] = get_sub_field('terms');
          endwhile;
        }
      endwhile;
    endif;
   }
  return $args;
}

function select_term_query_by_name($query_name){
  $args = [];
  if(function_exists('have_rows')){
    if(have_rows('custom_term_query','option')):
      while(have_rows('custom_term_query','option')) : the_row();
        if(get_sub_field('name') === $query_name){
          $args['taxonomy'] = get_sub_field('taxonomy_select');
          $args['hide_empty'] = get_sub_field('hide_empty');
          $args['orderby'] = get_sub_field('orderby');
          $args['order'] = get_sub_field('order');
        }
      endwhile;
    endif;
   }
  return $args;
}

add_action( 'init', 'register_cpts');
function register_cpts() {
if(function_exists('have_rows')):
  while(have_rows('custom_post_types','option')) : the_row();
    register_post_type(get_sub_field('name'),
      array(
      'labels' => array(
		'name' => get_sub_field('many_name'),
		'menu_name' => get_sub_field('menu_name') != '' ? get_sub_field('menu_name') : get_sub_field('many_name'),
		'singular_name' => get_sub_field('single_name'),
		'add_new' => 'Добавить',
		'add_new_item' => get_sub_field('single_name'),
		'edit_item' => 'Редактировать',
		'new_item' => get_sub_field('single_name'),
		'all_items' => 'Все '.mb_strtolower(get_sub_field('many_name')),
		'view_item' => 'Просмотреть',
		'search_items' => 'Найти',
		'not_found' =>  'Ничего не найдено.',
		'not_found_in_trash' => 'В корзине пусто.'
     ),
      'public' => true,
      'menu_icon' => get_sub_field('icon'),
      'menu_position' => 20,
      'has_archive' => true,
      'supports' => get_sub_field('support'),
      'taxonomies' => array(''),
      'rewrite' => array(
          'slug' => get_sub_field('slug')
        )
      )
    );
  endwhile;
endif;
}

add_action( 'init', 'register_taxs');
function register_taxs() {
if(function_exists('have_rows')):
  while(have_rows('custom_taxonomies','option')) : the_row();
	register_taxonomy(get_sub_field('name'),
		get_sub_field('post_type_select'),
		array(
			'labels' => array(
				'name' => get_sub_field('many_name'),
				'singular_name' => get_sub_field('single_name'),
				'search_items' =>  'Найти',
				'popular_items' => 'Популярные '.mb_strtolower(get_sub_field('many_name')),
				'all_items' => 'Все '.mb_strtolower(get_sub_field('many_name')),
				'parent_item' => null,
				'parent_item_colon' => null,
				'edit_item' => 'Редактировать',
				'update_item' => 'Обновить',
				'add_new_item' => 'Добавить новый элемент',
				'new_item_name' => 'Введите название записи',
				'separate_items_with_commas' => 'Разделяйте '.mb_strtolower(get_sub_field('many_name')).' запятыми',
				'add_or_remove_items' => 'Добавить или удалить '.mb_strtolower(get_sub_field('many_name')),
				'choose_from_most_used' => 'Выбрать из наиболее часто используемых',
				'menu_name' => get_sub_field('menu_name') != '' ? get_sub_field('menu_name') : get_sub_field('many_name')
			),
            'hierarchical' => get_sub_field('type'),
			'public' => true,
			'show_in_nav_menus' => true,
			'show_admin_column' => true,
			'show_in_quick_edit' => true,
			'show_ui' => true,
			'show_tagcloud' => true,
			'update_count_callback' => '_update_post_term_count',
			'query_var' => true,
			'rewrite' => array(
				'slug' => get_sub_field('slug'),
				'hierarchical' => false
			),
		)
	);
  endwhile;
endif;
}

function query_filter(){
if( isset($_GET['post_type'])
&& !isset($_GET['min_price'])
&& !isset($_GET['max_price'])
&& !strpos($_SERVER['QUERY_STRING'], 'filter_')
){

  global $wp_query;

  $args = array();
  $args['meta_query'] = array('relation' => 'AND');
  $args['tax_query'] = array('relation' => 'AND');

  foreach( $_GET as $key => $value ){
  	if(is_array($value)){
  		if(substr($key, 0, 6) === 'in_pm_'){
  			$args['meta_query'][] = array(
  				'key'     => substr($key, 6),
  				'value'   => $value,
  				'compare' => 'IN'
  			);
  		}else{
  			$args['tax_query'][] = array(
  				'taxonomy'=> $key,
  				'field'		=> 'slug',
  				'terms'		=> $value,
  				'operator'=> 'IN'
  			);
  		}
  	}

  	if( substr($key, 0, 7) === 'min_pm_' && $value != '' ){
  		$args['meta_query'][] = array(
  			'key'     => substr($key, 7),
  			'value'   => $value,
  			'type'    => 'numeric',
  			'compare' => '>='
  		);
  	}

  	if( substr($key, 0, 7) === 'max_pm_' && $value != '' ){
  		$args['meta_query'][] = array(
  			'key'     => substr($key, 7),
  			'value'   => $value,
  			'type'    => 'numeric',
  			'compare' => '<='
  		);
  	}

		if( substr($key, 0, 8) === 'min_pmd_' && $value != '' ){
  		$args['meta_query'][] = array(
  			'key'     => substr($key, 8),
  			'value'   => date('Ymd', strtotime($value)),
  			'compare' => '>='
  		);
  	}

  	if( substr($key, 0, 8) === 'max_pmd_' && $value != '' ){
  		$args['meta_query'][] = array(
  			'key'     => substr($key, 8),
  			'value'   => date('Ymd', strtotime($value)),
  			'type'    => 'date',
  			'compare' => '<='
  		);
  	}

    if( substr($key, 0, 3) === 'pm_' & $value !== '' ){
      $args['meta_query'][] = array(
  			'key' => substr($key, 3),
  			'value' => $value
  			);
    }

  	if( $key === 'post_types' ){
      $args['post_type'] = explode(',', $value);
    }

    if( $key === 'posts_per_page' ){
      $args['posts_per_page'] = $value;
    }

    if( $key === 'sort' ){
      $v = explode('.', $value);
      if( count($v) === 3 ){
        $args['orderby'] = $v[0];
        $args['meta_key'] = $v[1];
        $args['order'] = $v[2];
      } else {
        $args['orderby'] = $v[0];
        $args['order'] = $v[1];
      }
    }

  }
  query_posts(array_merge($args,$wp_query->query));
}
}

add_action( 'wp', 'query_filter' );

function ajaxs_load_posts($jx){
  $args = [];
  $args = unserialize( stripslashes( $jx->data['query'] ) );
  $args['post_status'] = 'publish';
	$args['paged'] = $jx->data['page'] + 1;
	$post = get_post($args['ID']);
  query_posts( $args );
  ob_start();
  require locate_template('template-parts/'.$jx->data['part'].'.php');
  return ob_get_clean();
};

function posts_per_page_change( $query ) {
	if ( isset($_GET['perpage']) && $query->is_main_query() && !$query->is_admin ) {
		$query->set( 'posts_per_page', $_GET['perpage'] );
	}
}
add_action( 'pre_get_posts', 'posts_per_page_change' );

add_action( 'after_setup_theme', 'add_editor_css' );
function add_editor_css(){
	add_theme_support( 'editor-styles' );
	//add_editor_style( 'css/main.css' );
}
