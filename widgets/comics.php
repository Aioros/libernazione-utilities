<?php
/*
 * Plugin Name: Comics Widget
 * Plugin URI: http://libernazione.it
 * Description: A widget that show latest comics
 * Version: 1.1
 * Author: Aioros
 * Author URI: http://libernazione.it
 */

include_once("class-comics.php");

add_action("comics_pre_action_hook", "comics_slider_scripts");

function comics_slider_scripts() {
	//wp_enqueue_style("flexslider", str_replace(get_home_path(), "/", locate_template("css/flexslider.css")));
	//wp_enqueue_script("comics-slider", plugins_url('comics.js', __FILE__), array("jquery-core", "jquery.flexslider"));
}

function get_recent_comics($items_num = 5) {
	$comics = array();
	$comics_query = new WP_Query(
		array(
			'post_type' => 'post',
			'posts_per_page' => $items_num,
			'tax_query' => array(array(
				'taxonomy' => 'post_format',
				'field' => 'slug',
				'terms' => array('post-format-image')
			))
		)
	);
	while ($comics_query->have_posts()) {
		$comics_query->the_post();
		$comics[] = $comics_query->post;
	}
	return $comics;
}

function comics_load_widget() {
	register_widget( 'comics' );
}
add_action( 'widgets_init', 'comics_load_widget' );