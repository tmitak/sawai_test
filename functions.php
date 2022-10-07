<?php

/* 子テーマのfunctions.phpは、親テーマのfunctions.phpより先に読み込まれることに注意してください。 */


/**
 * 親テーマのfunctions.phpのあとで読み込みたいコードはこの中に。
 */
// add_filter('after_setup_theme', function(){
// }, 11);


/**
 * 子テーマでのファイルの読み込み
 */
add_action('wp_enqueue_scripts', function() {
	
	$timestamp = date( 'Ymdgis', filemtime( get_stylesheet_directory() . '/style.css' ) );
	wp_enqueue_style( 'child_style', get_stylesheet_directory_uri() .'/style.css', [], $timestamp );

	/* その他の読み込みファイルはこの下に記述 */

}, 11);

/* カテゴリーを非表示 */

add_filter('user_trailingslashit', 'remcat_function');
function remcat_function($link) {
    return str_replace("/category/", "/", $link);
}
 
add_action('init', 'remcat_flush_rules');
function remcat_flush_rules() {
    global $wp_rewrite;
    $wp_rewrite->flush_rules();
}
 
add_filter('generate_rewrite_rules', 'remcat_rewrite');
function remcat_rewrite($wp_rewrite) {
    $new_rules = array('(.+)/page/(.+)/?' => 'index.php?category_name='.$wp_rewrite->preg_index(1).'&paged='.$wp_rewrite->preg_index(2));
    $wp_rewrite->rules = $new_rules + $wp_rewrite->rules;
}




/* ---------- カスタム投稿タイプを追加 施工事例---------- */
add_action( 'init', 'create_post_type' );

function create_post_type() {

  register_post_type(
    'works',
    array(
      'label' => 'works',
      'public' => true,
      'has_archive' => false,
      'show_in_rest' => true,
      'menu_position' => 9,
      'supports' => array(
        'title',
        'editor',
        'thumbnail',
        'revisions',
      ),
    )
  );

  register_taxonomy(
    'works-cat',
    'works',
    array(
      'label' => 'カテゴリー',
      'hierarchical' => true,
      'public' => true,
      'show_in_rest' => true,
    )
  );
  register_taxonomy(
    'works-tag',
    'works',
    array(
      'label' => 'タグ',
      'hierarchical' => false,
      'public' => true,
      'show_in_rest' => true,
      'update_count_callback' => '_update_post_term_count',
    )
  );
  

}

//カストノミー非表示
function my_custom_post_type_permalinks_set($termlink, $term, $taxonomy){
  return str_replace('/'.$taxonomy.'/', '/', $termlink);
}
add_filter('term_link', 'my_custom_post_type_permalinks_set',11,3);
 
 
//カスタム投稿タイプ名、タクソノミー名部分に該当するタイプ名・タクソノミー名を入力する
add_rewrite_rule('works/([^/]+)/?$', 'index.php?works-cat=$matches[1]', 'top');
add_rewrite_rule('works/([^/]+)/page/([0-9]+)/?$', 'index.php?works-cat=$matches[1]&paged=$matches[2]', 'top');


//タクソノミー未選択時に特定のタームを選択

function add_defaultcategory_automatically($post_ID) {
  global $wpdb;
  $curTerm = wp_get_object_terms($post_ID, 'works-cat');//タクソノミー名
  if (0 == count($curTerm)) {
    $defaultTerm= array(1);//選択させたいタームID
    wp_set_object_terms($post_ID, $defaultTerm, 'works-cat');//タクソノミー名
  }
}
add_action('publish_works', 'add_defaultcategory_automatically');//publish_カスタム投稿タイプ名
