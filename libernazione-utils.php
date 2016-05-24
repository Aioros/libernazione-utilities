<?php
/*
Plugin Name: Libernazione Utilities
Plugin URI: http://libernazione.it
Author: Aioros
Version: 0.1
*/

function aioros_debug($variable) {
	echo '<div id="AIOROSTEST" style="display: none;">';
	var_dump($variable);
	echo '</div>';
}

add_action("wp_enqueue_scripts", "lib_scripts");
function lib_scripts() {
	wp_register_script("sidebar-async", plugin_dir_url( __FILE__ ) . "js/sidebar.js", "jquery", "1.0", true);
	wp_enqueue_script("sidebar-async");

	// https://github.com/madgex/lazy-ads
	wp_register_script("lazyad-async", plugin_dir_url( __FILE__ ) . "js/lazyad-loader.min.js", array(), "1.0", true);
	wp_enqueue_script("lazyad-async");	
}

/**
 * Get The First Image From a Post
 */
function first_post_image() {
	global $post, $posts;
	$first_img = '';
	ob_start();
	ob_end_clean();
	if( preg_match_all('/<img.+src=[\'"]([^\'"]+)[\'"].*>/i', $post->post_content, $matches ) ){
		$first_img = $matches[1][0];
		return $first_img;
	}
}

function lib_author_box($author_id, $opts = array()) {
	global $post;
	if (!$author_id) {
		$author_id = $post->post_author;
	}

	$defaults = array(
		"num_posts" => 0,
		"description" => true
	);

	$opts = array_merge($defaults, $opts);
	
	$out = '';
	$name = get_the_author_meta('display_name', $author_id);

	$email      = get_the_author_meta('public_email', $author_id);
	$website    = get_the_author_meta('user_url', $author_id);
	$author_url = get_author_posts_url($author_id);

	$social = array(
		'facebook'  => get_the_author_meta('facebook', $author_id),
		'twitter'   => get_the_author_meta('twitter', $author_id),
		'linkedin'  => get_the_author_meta('linkedin', $author_id),
		'dribbble'  => get_the_author_meta('dribbble', $author_id),
		'google'    => get_the_author_meta('google', $author_id),
		'instagram' => get_the_author_meta('instagram', $author_id),
	);

	// get user profile image
	if ( get_user_meta($author_id, 'user_img_id', true) != '' ) {

		$user_img = wp_get_attachment_image_src(get_user_meta($author_id, 'user_img_id', true), 'thumbnail');

		$avatar = '';

		$avatar .= !is_author() ? '<a href="'. esc_url($author_url) .'" title="'. sprintf( "Tutti i post di %s", esc_attr($name) ) .'">' : '';
		$avatar .= '<img src="'. esc_url($user_img[0]) .'" width="'. $user_img[1] .'" height="'. $user_img[2] .'" alt="'. esc_attr($name) .'" />';
		$avatar .= !is_author() ? '</a>' : '';
	} else {
		$avatar =  get_avatar($author_id, 80);
	}

	$description = get_the_author_meta('description', $author_id);

	$links = '<ul class="author-links-wrap">';

	foreach ( $social as $key => $value ) {
		if ( $value != '' ) {
			$links .= '<li class="author-links"><a href="' . esc_url($value) . '" target="_blank" rel="nofollow" class="fa fa-' . $key . '"><i ></i></a></li>';
		}
	}

	if ( !empty($email) ) {
		$links .= '<li class="author-links"><a href="mailto:' . $email . '" rel="nofollow" class="fa fa-envelope-o"></a></li>';
	}

	if ( !empty($website) ) {
		$links .= '<li class="author-links"><a href="' . esc_url($website) . '" rel="nofollow" class="fa fa-home"></a></li>';
	}

	$links .= '</ul><!-- .author-links-wrap -->';

	$out .= '<div class="author-meta clearfix">';

	$out .= '<div class="author-meta-avatar">';
	$out .= $avatar;

	$out .= '</div><!-- .author-meta-avatar -->';

	$out .= '<div class="author-description">';
	$out .= '<div class="author-meta-heading">';
	$out .= '<h2 class="author-name">';
	$out .= !is_author() ? '<a href="'. esc_url($author_url) .'" title="'. sprintf( "Tutti i post di %s", esc_attr($name) ) .'">' : '';
	$out .= $name;
	$out .= !is_author() ? '</a>' : '';
	$out .='</h2>';

	$out .= '</div>';

	if ($opts["description"]) {
		$out .= '<div class="description-text">' . $description .'</div>';
		$out .= $links;
	}
	
	$out .= '</div><!-- .author-description -->';

	ob_start();
	if ($opts["num_posts"] > 0) {
		echo '<ul class="related-posts author-related-posts">' .
			'<h5>Ultimi articoli di '.$name.'</h5>';
			;
		$args = array(
			'author' => $author_id,
			'numberposts' => $opts["num_posts"], /* you can change this to show more */
			'post__not_in' => array($post->ID)
		);
		$related_posts = get_posts( $args );
		if ($related_posts) {
			foreach ( $related_posts as $post ) : setup_postdata( $post ); ?>
				<li class="related_post">
					<a class="entry-unrelated" href="<?php the_permalink() ?>" title="<?php the_title_attribute(); ?>">
						<?php the_post_thumbnail(); ?>
						<h3><?php the_title(); ?></h3>
					</a>
				</li>
			<?php endforeach;
		}
		
		wp_reset_postdata();
		echo '</ul>';
	}
	$out .= ob_get_contents();
	ob_end_clean();

	$out .= '</div><!-- .author-meta -->';

	return $out;
}

if (!function_exists("listAuthors")) {

	/*** Shortcode per pagina autori (by Aioros) ***/
	function listAuthors() {

		global $wpdb;
		$order = 'display_name';
		$users = $wpdb->get_results(
			"SELECT u.ID, u.user_nicename, IF(DATEDIFF(NOW(), MAX(p.post_date)) < 180, 1, 0) active
			FROM $wpdb->users u INNER JOIN $wpdb->posts p ON p.post_author = u.ID
			WHERE p.post_type = 'post'
			AND p.post_status = 'publish'
			AND p.post_parent = 0
			GROUP BY p.post_author
			ORDER BY $order"
		);

		$output = "";
		$hidden = array("paolomossetti","zarzuela","francesconardi","libtalent");
		$inactive = array();
		$active = array();
		
		foreach ($users as $user) {
			if ($user->active) {
				$active[] = $user->ID;
			} else if (!in_array($user->user_nicename, $hidden)) {
				$inactive[] = $user->ID;
			}
		}

		$output .= '<div class="active-users">';
		foreach ($active as $user) {
			$output .= lib_author_box($user);
		}
		$output .= '</div><div class="inactive-users">' .
		'<h2>Hanno collaborato con noi:</h2>';
		foreach ($inactive as $user) {
			$output .= lib_author_box($user);
		}
		$output .= '</div>';
		return $output;
	}

	add_shortcode("list_authors", "listAuthors");

}

/*** CUSTOM POST TYPES ****/
// MICROPOST
function micropost_register_type() {
	register_post_type("micropost", array(
		'labels' => array(
			'name' => 'Microposts',
			'singular_name' => 'Micropost',
			'add_new' => 'Aggiungi nuovo',
			'add_new_item' => 'Aggiungi nuovo micropost',
			'edit_item' => 'Modifica micropost',
			'new_item' => 'Nuovo micropost',
			'all_items' => 'Tutti i micropost',
			'view_item' => 'Vedi i micropost',
			'search_items' => 'Cerca micropost',
			'not_found' => 'Nessun micropost trovato'
		),
		'public' => true,
		'publicly_queryable' => true,
		'show_ui' => true,
		'show_in_nav_menus' => true,
		'show_in_menu' => true,
		'query_var' => true,
		'rewrite' => true,
		'capability_type' => 'post',
		'has_archive' => true, 
		'hierarchical' => false,
		'menu_position' => null,
		'supports' => array( 'title', 'editor', 'author', 'thumbnail', 'comments' )
	));
}
add_action('init', 'micropost_register_type');

/*** CUSTOM TAXONOMIES ***/
// FLAMEBOARD
function flameboard_register_taxonomy() {
	register_taxonomy("flameboard", array("post", "micropost"), array(
		'label' => 'Flameboard',
		'object_type' => 'post',
		'public' => true,
		'show_ui' => true,
		'hierarchical' => false,
		'show_tagcloud' => false,
		'show_admin_column' => true
	));
}
add_action('init', 'flameboard_register_taxonomy');
// RUBRICHE
function rubriche_register_taxonomy() {
	register_taxonomy("rubriche", array("post", "micropost"), array(
		'labels' => array(
			'name' => 'Rubriche',
			'singular_name' => 'Rubrica',
			'add_new_item' => 'Aggiungi rubrica',
			'edit_item' => 'Modifica rubrica',
			'update_item' => 'Aggiorna rubrica',
			'all_items' => 'Tutte le rubriche',
			'search_items' => 'Cerca rubriche',
			'popular_items' => 'Rubriche più seguite',
			'new_item_name' => 'Nuova rubrica',
			'add_or_remove_items' => 'Aggiungi o rimuovi rubriche',
			'choose_from_most_used' => 'Scegli tra le rubriche più usate'
		),
		'object_type' => 'post',
		'public' => true,
		'show_ui' => true,
		'hierarchical' => false,
		'show_tagcloud' => false,
		'show_admin_column' => true
	));
}
add_action('init', 'rubriche_register_taxonomy');
function assign_taxonomies() {
	register_taxonomy_for_object_type( "category", "micropost" );
	register_taxonomy_for_object_type( "post_tag", "micropost" );
	register_taxonomy_for_object_type( "rubriche", "micropost" );
}
add_action('init', 'assign_taxonomies');

function add_custom_types( $query ) {
    if ( !is_admin() && !$query->is_post_type_archive() && !$query->is_page() && $query->is_main_query() ) {
        $query->set( 'post_type', array("post", "micropost", "autogen") );
    }
}
add_filter( 'pre_get_posts', 'add_custom_types' );


function get_cat_or_term($thelist, $separator, $parents) {
	global $post;
	$thelist = preg_replace("/<a href=\"[^\"]*(\/uncategorized\/|\?cat=1)\"[^>]*>Uncategorized<\/a>(\s*,\s*)?/i", "", $thelist);
	$thelist = preg_replace("/senza categoria/i", "", $thelist);
	$thelist = rtrim($thelist, ", ");
	if ($thelist == "") {
		$rubriche = get_the_terms($post->ID, 'rubriche');
		if ($rubriche) {
			foreach ($rubriche as $rubrica)
				$thelist = '<a href="/rubriche/' . $rubrica->slug . '/">' . $rubrica->name . '</a>';
		} else {
			$flameboards = get_the_terms($post->ID, 'flameboard');
			if ($flameboards) {
				foreach ($flameboards as $flameboard)
					$thelist = '<a href="/flameboard/' . $flameboard->slug . '/">' . $flameboard->name . '</a>';
			} else {
				$post_type = get_post_type_object(get_post_type($post));
				if ($post_type)
					$thelist = $post_type->labels->singular_name;
				else
					$thelist = "Senza categoria";
			}
		}
	}
	return $thelist;
}
if (!is_admin()) {
	add_filter("the_category", "get_cat_or_term", 99999, 3);
}

add_filter("widget_categories_args", "ignore_uncategorized", 1, 1);
function ignore_uncategorized($cat_args) {
	$cat_args["exclude"] = 1;
	return $cat_args;
}

/******* WIDGET VIGNETTE ******/

// The winner is - widget by Aioros
//include_once( 'widgets/the-winner-is.php' );
// Micropost - widget by Aioros
//include_once( 'widgets/microposts.php' );
// Flameboards - widget by Aioros
//include_once( 'widgets/flameboards.php' );
// Comics - widget by Aioros
include_once( 'widgets/comics.php' );
// Rubriche - widget by Aioros
include_once( 'widgets/rubriche.php' );

add_action('init', 'rubricheautogen');
function rubricheautogen() {
	register_taxonomy_for_object_type( "rubriche", "autogen" );
}

/*** Async or defer scripts ***/
/*** Append "-async" or "-defer" to the js handle in wp_enqueue_script ***/
add_filter('script_loader_tag', 'add_async_defer_attribute', 10, 2);
function add_async_defer_attribute($tag, $handle) {
	$len_handle = strlen($handle);
	if ($len_handle >= 6 && stripos($handle, "-async", $len_handle - 6) !== false) {
		$tag = str_replace( ' src', ' async="async" src', $tag );
	} else if ($len_handle >= 6 && stripos($handle, "-defer", $len_handle - 6) !== false) {
		$tag = str_replace( ' src', ' defer="defer" src', $tag );
	}
	return $tag;
}

/*** Shortcode per citazioni bibliche (by Aioros) ***/
function biblePopup($attrs) {

	//wp_enqueue_script("biblepopup", plugin_dir_url( __FILE__ ) . "js/biblepopup.js", "jquery", "1.0", true);

	add_thickbox();

	$query = $attrs["query"];
	$query_hash = hash("md5", $query);

	$popup_content = do_shortcode("[bibleget query=\"$query\" versions=\"CEI2008\"]");

	$output = '<sup><a href="#TB_inline?width=400&height=300&inlineId=popup_' . $query_hash . '" class="bible-link thickbox fancybox-inline" id="link_' . $query_hash . '">[' . $query . ']</a></sup>';
	$div = '<div style="display: none;"><div class="bible-popup" id="popup_' . $query_hash . '">' . $popup_content . '</div></div>';
	$output .= "<script>jQuery('.entry-content').append('" . str_replace(PHP_EOL, "", addslashes($div)) . "');</script>";

	return $output;
}

add_shortcode("bible_popup", "biblePopup");

/*** ADV flag ***/
function show_adv() {
	//return false;
	return true;
	//return (isset($_GET["advtest"]) && $_GET["advtest"]);
}

function print_adv($type) {
	if (show_adv()) { ?>
		<div class="adv ad" data-lazyad>
		<!-- Good Move Advertising -->
		<?php if ($type == "skin") {
			if (is_home()) { ?>
				<!-- Tag for LIB-HME Home Background Skin placement -->
				<script type="text/lazyad">
      				<!--
      					<script type="text/javascript" src="http://adx.adform.net/adx/?mid=94304"></script>
      				-->
      			</script>
			<?php } else { ?>
				<!-- Tag for LIB-INT Internal Pages Background Skin placement -->
				<script type="text/lazyad">
      				<!--
      					<script type="text/javascript" src="http://adx.adform.net/adx/?mid=94307"></script>
      				-->
      			</script>
				<!-- Tag for LIB-INT Internal Pages Interstitial placement -->
				<script type="text/lazyad">
      				<!--
      					<script type="text/javascript" src="http://adx.adform.net/adx/?mid=94310"></script>
      				-->
      			</script>
			<?php }
		} else if ($type == "strip") {
			if (is_home()) { ?>
				<!-- Tag for LIB-HME Home Billboard Area placement -->
				<script type="text/lazyad">
      				<!--
      					<script type="text/javascript" src="http://adx.adform.net/adx/?mid=94305"></script>
      				-->
      			</script>
			<?php } else { ?>
				<!-- Tag for LIB-INT Internal Pages Billboard Area placement -->
				<script type="text/lazyad">
      				<!--
      					<script type="text/javascript" src="http://adx.adform.net/adx/?mid=94308"></script>
      				-->
      			</script>
			<?php }
		} else if ($type == "sidebar-top") { ?>
			<!-- Tag for LIB-INT Internal Pages Med Rec Area placement -->
			<script type="text/lazyad">
  				<!--
  					<script  type="text/javascript" src="http://adx.adform.net/adx/?mid=94309"></script>
  				-->
  			</script>
		<?php } else if ($type == "sidebar-bottom") { ?>
			<!-- Tag for LIB-INT Internal Pages Med Rec Area 2 placement -->
			<script type="text/lazyad">
  				<!--
  					<script type="text/javascript" src="http://adx.adform.net/adx/?mid=94316"></script>
  				-->
  			</script>
		<?php } else if ($type == "home-1") { ?>
			<!-- Tag for LIB-HME Home Med Rec Area placement -->
			<script type="text/lazyad">
  				<!--
  					<script type="text/javascript" src="http://adx.adform.net/adx/?mid=94306"></script>
  				-->
  			</script>
		<?php } else if ($type == "home-2") { ?>
			<script type="text/lazyad">
  				<!--
  					<script type="text/javascript" src="http://adx.adform.net/adx/?mid=94315"></script>
  				-->
  			</script>
		<?php } ?>
		</div>
	<?php }
}

// Check if custom uploaded avatar
add_filter( 'get_avatar', 'check_custom_avatar', 10 , 5 );
function check_custom_avatar( $avatar, $id_or_email, $size, $default, $alt ) {
	if ( is_numeric($id_or_email) ) {
		$user = get_user_by( 'ID', (int) $id_or_email );
	} else if ( is_object($id_or_email) ) {
		if ( !empty($id_or_email->user_id) ) {
			$user = get_user_by( 'ID', (int) $id_or_email->user_id);
		}
	} else {
        $user = get_user_by( 'email', $id_or_email );
    }
	if ($user && is_object($user) && $user->data->ID > 0) {
		if ( get_user_meta($user->data->ID, 'user_img_id', true) != '' ) {
			$size = "thumbnail";
			if (is_admin())
				$size = array(50, 50);
			if (is_home())
				$size = array(24, 24);
			$user_img = wp_get_attachment_image_src(get_user_meta($user->data->ID, 'user_img_id', true), $size);
			$avatar = '';
			$avatar .= !is_author() && !is_admin() ? '<a href="'. esc_url($user->data->user_url) .'" title="'. sprintf( __( 'View all posts by %s', 'pukka' ), esc_attr($user->data->display_name) ) .'"">' : '';
			$avatar .= '<img src="'. esc_url($user_img[0]) .'" class="avatar avatar-50 photo" width="'. $user_img[1] .'" height="'. $user_img[2] .'" alt="'. esc_attr($user->data->display_name) .'" />';
			$avatar .= !is_author() && !is_admin() ? '</a>' : '';
		}
	}
	return $avatar;
}

// Taxonomy tree in menu
add_filter( 'wp_nav_menu_objects', 'lib_taxonomy_tree', 10, 2 );
function lib_taxonomy_tree($menu_items, $args) {
	if (is_admin())
		return;

	$prefix = "tax-";

	foreach ($menu_items as $item) {
		$parent_id = 0;

		if (strpos($item->title, $prefix) === 0) {
			$tax_name = substr($item->title, strlen($prefix));
			$taxonomy = get_taxonomy($tax_name);
			$tax_label = $taxonomy->labels->name;
			$item->title = ucfirst($tax_label);
			$item->classes = "menu-item " . rtrim($prefix, "-") . " " . $prefix . $tax_name;
			$parent_id = $item->ID;
		
			$terms = get_terms($tax_name);
			$tree = array(0 => array("title" => "root", "children" => array()));
			$menu_order = count( $menu_items ) + 1;
			$limit = 30;

			foreach ($terms as $term) {
				if (!in_array(strtolower($term->name), array("uncategorized", "senza categoria"))) {
					$menu_items[] = (object) array(
						"ID"				=> $term->term_id + 1000000000,
						"title"				=> $term->name,
						"url"				=> get_term_link($term, $tax_name),
						"menu_item_parent"	=> $term->parent == 0 ? $parent_id : $term->parent + 1000000000,
						"menu_order"		=> $menu_order,
						"type"				=> "taxonomy",
						"object"			=> $tax_name,
						"object_id"			=> $term->term_id + 1000000000,
						"db_id"				=> $term->term_id + 1000000000,
						"classes"			=> ""
					);
				}

				$menu_order++;
			}
		}
	}
	
	return $menu_items;
}

// Fix per responsive oembeds
add_filter( 'embed_oembed_html', 'iframe_resp_oembed_filter', 10, 4 );
function iframe_resp_oembed_filter($html, $url, $attr, $post_ID) {
  $return = '<div class="iframe-embed-wrapper">'.$html.'</div>';
  return $return;
}

// Well, they don't always use the oembeds, so
add_filter( 'the_content', 'sanitize_embeds', 10, 1 );
function sanitize_embeds($content) {
  global $wp_embed;

  // YouTube iframes
  // e.g.: <iframe width="680" height="510" src="https://www.youtube.com/embed/3LDM20EuVzU?feature=oembed" frameborder="0" allowfullscreen></iframe>
  $content = preg_replace('#<iframe\s+.*?\bsrc="https?://www.youtube.com/embed/([\w-_]+).*?".*?></iframe>#', "\nhttps://www.youtube.com/watch?v=$1\n", $content);

  // DailyMotion iframes
  // e.g.: <iframe src="//www.dailymotion.com/embed/video/xjvljm" width="480" height="270" frameborder="0" allowfullscreen="allowfullscreen"></iframe>
  $content = preg_replace('#<iframe\s+.*?\bsrc="(https?:)?//www.dailymotion.com/embed/video/([\w-_]+).*?".*?></iframe>#', "\nhttp://www.dailymotion.com/video/$2\n", $content);

  return $wp_embed->autoembed($content);
}

// Enclose other non-standard embeds
add_filter( 'the_content', 'enclose_embeds', 10, 1 );
function enclose_embeds($content) {

	// Facebook
	$content = preg_replace(
		'#<iframe [^>]*\bsrc="(https://www\.facebook\.com/plugins/(\w+)\.php[^"]*)\"[^>]*></iframe>*#',
		'<div class="iframe-embed-wrapper">$0</div>',
		$content
	);

	return $content;
}


// Fix per capitalize meta title di categorie (bug in All In One Seo Pack già segnalato e ancora non risolto)
add_filter( 'aioseop_title', 'capitalize_archive_title', 10, 1 );
function capitalize_archive_title( $title ) {
	if (is_archive())
		return ucfirst($title);
}

// WP non aggiunge l'attr srcset alle GIF full size perché di default le altre dimensioni sono flattened.
// Noi però abbiamo già un plugin per evitare quella cosa, e l'srcset ci serve. Quindi in questo filtro
// togliamo il mime type ai metadati dell'immagine.
// (cfr. wp_calculate_image_srcset(), wp-includes/media.php:1010 e https://core.trac.wordpress.org/ticket/34528)
add_filter('wp_calculate_image_srcset_meta', 'lib_remove_gif_mime_for_srcset', 10, 4);
function lib_remove_gif_mime_for_srcset($image_meta, $size_array, $image_src, $attachment_id) {
	if (isset($image_meta['sizes']['thumbnail']['mime-type'])) {
		if ($image_meta['sizes']['thumbnail']['mime-type'] === "image/gif")
			unset($image_meta['sizes']['thumbnail']['mime-type']);
	}
	return $image_meta;
}



/*** SOCIAL EVERYTHING ***/
include_once "social.php";

/*** STRUCTURED DATA ***/
include_once "structured-data.php";