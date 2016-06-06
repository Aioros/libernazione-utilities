<?php

// Rimuoviamo la classe hentry (dati microformats.org)
function lib_remove_hentry_class( $classes ) {
    $count = count($classes);
    for ($i = 0; $i < $count; $i++) {
    	if ($classes[$i] == "hentry")
    		$classes[$i] = "entry";
    }
    return $classes;
}
add_filter( 'post_class', 'lib_remove_hentry_class' );

function get_post_structured_data($post) {

	setup_postdata($post);

	$main_type = "BlogPosting";
	$link = get_permalink($post);
	$headline = get_the_title($post);
	$thumb = wp_get_attachment_image_src( get_post_thumbnail_id($post->ID), 'full' );
	$comments = array();
	if (is_single())
		$comments = get_comments(array('post_id' => $post->ID));
	$author_image = get_avatar_url( get_the_author_meta( 'user_email' ), 96 );

	$structured_data = array(
		"@type" => $main_type,
		"headline" => $headline,
		"url" => $link,
		"datePublished" => get_the_time('c', $post),
		"dateModified" => get_the_modified_time('c', $post),
		"mainEntityOfPage" => $link,
		"commentCount" => count($comments),
		"author" => array(
			"@type" => "Person",
			"name" => get_the_author_meta('display_name', $post->post_author),
			"url" => get_author_posts_url($post->post_author),
			"description" => strip_tags(get_the_author_meta("description", $post->post_author))
		),
		"publisher" => array(
			"@type" => "Organization",
			"name" => "Libernazione",
			"logo" => array(
				"@type" => "ImageObject",
				"url" => "http://libernazione.it/wp-content/themes/lib-bones/logo-480x60.png",
				"width" => 480,
				"height" => 60
			)
		)
	);

	if ($author_image) {
		$structured_data["author"]["image"] = array(
			'@type' => 'ImageObject',
			'url' => $author_image,
			'height' => 96,
			'width' => 96
		);
	}

	if (is_single()) {
		$structured_data["mainEntityOfPage"] = array(
			"@type" => "WebPage",
			"@id" => $link
		);

		$structured_data["articleBody"] = strip_tags(apply_filters('the_content', $post->post_content));

		$structured_data["keywords"] = implode(", ", wp_get_post_terms($post->ID, 'post_tag', array('fields' => 'names')));

		$categories = get_the_category();
		if (count($categories) > 0) {
			foreach ($categories as $category) {
                $category_mames[] = $category->name;
            }
			$structured_data["about"] = $category_mames;
		}

		if (count($comments) > 0) {
			$structured_data["comment"] = array();
			foreach($comments as $comment) {
				$c_ID = $comment->comment_ID;
				$structured_data["comment"][] = array(
					"@type" => "Comment",
					"dateCreated" => get_comment_date("c", $c_ID),
					"description" => strip_tags(get_comment_text($c_ID)),
					"author" => array(
						"@type" => "Person",
						"name" => get_comment_author($c_ID)
					)
				);
			}
		}
	}/* else {
		$structured_data["articleBody"] = apply_filters('the_excerpt', get_the_excerpt($post));
	}*/

	if (has_post_thumbnail($post->ID) || has_post_format("image", $post->ID)) {
		$structured_data += array(
			"image" => array(
				"@type" => "ImageObject",
				"url" => $thumb[0],
				"width" => $thumb[1],
				"height" => $thumb[2]
			)
		);
	}

	return $structured_data;
}

add_action("wp_head", "lib_structured_data");
function lib_structured_data($html) {

	global $wp_query;

	$posts_structured_data = array();
	foreach ($wp_query->posts as $post) {
		$posts_structured_data[] = get_post_structured_data($post);
	}

	$structured_data = array(
		"@context" => "http://schema.org",
		"url" => home_url( add_query_arg( NULL, NULL ) ),
	);

	if (is_home()) {

		$structured_data += array(
			"@type" => "Blog",
			"headline" => get_bloginfo("name"),
			"description" => ucfirst(get_bloginfo("description"))
		);

		if (count($wp_query->posts) > 0) {
			$structured_data["blogPost"] = $posts_structured_data;
		}

	} else if (is_archive()) {

		$structured_data += array(
			"@type" => "CollectionPage",
			"headline" => ucfirst(get_category(get_query_var( 'cat' ))->cat_name)
		);

		if (count($wp_query->posts) > 0) {
			$structured_data["hasPart"] = $posts_structured_data;
		}

	} else if (count($posts_structured_data) > 0) {
		$structured_data += $posts_structured_data[0];
	}

	echo '<script type="application/ld+json">'.json_encode($structured_data).'</script>' . "\n";
}
