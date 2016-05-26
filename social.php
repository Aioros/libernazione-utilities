<?php

/**
 *
 * Generates meta description, helper function
 *
 * @param bool|true $strip_tags
 *
 * @return mixed|string|void
 */
function bones_get_meta_description($strip_tags = true) {
    global $post;

    $description = '';

    if ( !is_front_page() && (is_single() || is_page()) ) {

        if ( !empty($post->post_excerpt) ) {
            $description = apply_filters( 'excerpt', $post->post_excerpt );
        } elseif ( !empty($post->post_content) ) {
            $content = apply_filters( 'the_content', $post->post_content );
            $description = wp_trim_words($content);
        } else {
            $description = '';
        }
    } elseif ( is_category() ) {
        $description = category_description();
    } elseif ( is_tag() ) {
        $description = tag_description();
    } elseif ( is_home() || is_front_page() ) {
        $description = get_bloginfo('description');
    }

    if ( !empty($description) && $strip_tags ) {
        // strip all tags and left over line breaks and white space characters
        $description = wp_strip_all_tags($description, true);
    }

    return $description;
}

/**
 * Generates and prints Open Graph meta tags
 */

function bones_print_open_graph_tags() {
    global $post;

    $og_desc_length = 220;

    $og_tags = array();

    // og_site_name
    $og_tags['og:site_name'] = get_bloginfo('name');

    // og_title
    if ( function_exists('wp_get_document_title') ) {
        $og_tags['og:title'] = wp_get_document_title();
    } elseif ( function_exists('wp_title') ) {
        $og_tags['og:title'] = wp_title( '|', false, 'right' );
    }

    // og_url
    if ( is_single() || is_page() ) {
        $og_tags['og:url'] = get_permalink();
    }

    // og_type
    if ( !is_front_page() && !is_home() && (is_singular() || is_page()) ) {
        $og_tags['og:type'] = 'article';
    } else {
        $og_tags['og:type'] = 'website';
    }

    // og_image
    if ( (is_single() || is_page()) && has_post_thumbnail() && !is_front_page() ) {
        $image = wp_get_attachment_image_src( get_post_thumbnail_id(get_the_ID()),'full' );
        $og_tags['og:image'] = $image[0];
    } else {
        // display fb share image or site logo
        $fb_img_id = "http://libernazione.it/wp-content/themes/lib-bones/screenshot.png";

        if ( !empty($fb_img_id) ) {
            $fb_image = wp_get_attachment_image_src($fb_img_id, 'full');
            $og_tags['og:image'] = $fb_image[0];
        }
    }


    // og_description
    $og_tags['og:description'] = bones_get_meta_description();
    if ( strlen($og_tags['og:description']) > $og_desc_length ) {
        $og_tags['og:description'] = mb_substr($og_tags['og:description'], 0, $og_desc_length, 'UTF-8') . '...';
    }

    $out = '';

    $og_tags['fb:app_id'] = "370346486491395";
    
    foreach ( $og_tags as $key => $content ) {
        $out .= '<meta property="'. esc_attr($key) .'" content="'. esc_attr($content) .'" />' . "\n";;
    }

    echo $out;
}

/*
* Generates and prints Twitter Card data
*/

function bones_print_twitter_card() {
    global $post;


    if ( !is_single() ) {
        return;
    }

    $og_desc_length = 220;

    $out = '';

    // card type
    $out .= '<meta name="twitter:card" content="summary">' ."\n";

    $out .= '<meta name="twitter:site" content="@libernazione">' ."\n";

    // post title
    $tw_title = '';
    if ( function_exists('wp_get_document_title') ) {
        $tw_title = wp_get_document_title();
    } elseif ( function_exists('wp_title') ) {
        $tw_title = wp_title( '|', false, 'right' );
    }

    $out .= '<meta name="twitter:title" content="'. esc_attr($tw_title) .'">' ."\n";

    // post author
    if ( get_the_author_meta('twitter', $post->post_author) != '' ) {
        $matches = array();
        preg_match("|https?://(www\.)?twitter\.com/(#!/)?@?([^/]*)|", get_the_author_meta('twitter', $post->post_author), $matches);

        if ( !empty($matches[3]) ) {
            $out .= '<meta name="twitter:creator" content="@'. esc_attr($matches[3]) .'">' ."\n";
        }
    }

    // post excerpt
    $description = bones_get_meta_description();
    if ( strlen($description) > $og_desc_length ) {
        $description = mb_substr($description, 0, $og_desc_length, 'UTF-8') . '...';
    }

    $out .= '<meta name="twitter:description" content="'. esc_attr($description) .'">' ."\n";

    // post image
    if ( has_post_thumbnail() ) {
        $thumb = wp_get_attachment_image_src( get_post_thumbnail_id(get_the_ID()), 'full');
        $image = $thumb[0];

        $out .= '<meta name="twitter:image:src" content="'. esc_url($image) .'">' ."\n";
    } else {
        // site logo ?
    }

    echo $out;
}

add_filter("wp_head", "lib_social_meta");
function lib_social_meta() {
// Open Graph, Twitter Cards
    bones_print_open_graph_tags();
    bones_print_twitter_card();
}

// Social buttons
// Not sure about this all, but I didn't like injecting them into the content with a filter.
// Now they are included in the template, but this couples theme and plugin.
function lib_social_buttons($position = "top") {
    wp_enqueue_style("lib-social", plugins_url('css/lib-social.css', __FILE__));
    wp_enqueue_script("lib-social", plugins_url('js/social.js', __FILE__), array("jquery"), '', true);
    ob_start();
    ?>
    <div class="social-buttons-placeholder" data-position="<?php echo $position; ?>"></div>
    <?php if ($position == "top") { ?>
    <div class="social-buttons clearfix">
        <div class="social-button fb-button">
            <div class="fb-like" data-href="<?php the_permalink(); ?>" data-layout="button_count" data-action="like" data-show-faces="false" data-share="false"></div>
        </div>
        <div class="social-button tw-button">
            <a href="https://twitter.com/share" class="twitter-share-button" data-url="<?php the_permalink(); ?>">Tweet</a>
        </div>
        <div class="social-button gp-button">
            <div class="g-plusone" data-size="tall" data-annotation="none"></div>
        </div>
    </div> <!-- .social-buttons -->
    <?php } ?>
    <?php
    $social_buttons = ob_get_contents();
    ob_end_clean();
    return $social_buttons;
}