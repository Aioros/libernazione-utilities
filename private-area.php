<?php

function lib_edit_private_category_field( $term ) {
    $term_id = $term->term_id;
    $lib_data = get_term_meta( $term_id, "lib_data", true );
    $private = $lib_data["private"];
?>
    <tr class="form-field">
        <th scope="row">
            <label for="lib_data[private]"><?php echo _e('Private') ?></label>
            <td>
            	<input type="checkbox" name="lib_data[private]" id="lib_data[private]"<?php if ($private) echo ' checked'; ?> value="1" />
            </td>
        </th>
    </tr>
<?php
}

add_action( 'category_edit_form_fields', 'lib_edit_private_category_field' );

function lib_save_category_meta( $term_id ) {
	if ( isset( $_POST['lib_data'] ) ) {
		$lib_data = $_POST['lib_data'];
		update_term_meta( $term_id, "lib_data", $lib_data );
	} else {
		delete_term_meta( $term_id, "lib_data" );
	}
}

add_action( 'edited_category', 'lib_save_category_meta', 10, 2 );

add_filter( 'auth_redirect_scheme', 'lib_check_loggedin' );
function lib_check_loggedin(){
    return 'logged_in';
}

add_action( 'wp', 'lib_auth_private_cat' );
function lib_auth_private_cat() {
	if (is_category()) {
		$cat_id = get_query_var('cat');
		$lib_data = get_term_meta($cat_id, "lib_data", true);
		if (isset($lib_data["private"]) && $lib_data["private"]) {
			auth_redirect();
		}
	}
}

add_action('save_post', 'lib_set_private_post', 10, 1);
function lib_set_private_post($post_id) {
	if ($parent_id = wp_is_post_revision($post_id)) 
		$post_id = $parent_id;
	$categories = get_the_category($post_id);
	$private = false;
	foreach($categories as $category) {
		$cat_meta = get_term_meta($category->term_id, "lib_data", true);
		if (isset($cat_meta["private"]) && $cat_meta["private"])
			$private = true;
	}
	if ($private) {
		// unhook this function so it doesn't loop infinitely, update the post, then re-hook
		remove_action('save_post', 'lib_set_private_post', 10, 1);
		wp_update_post(array('ID' => $post_id, 'post_status' => 'private'));
		add_action('save_post', 'lib_set_private_post', 10, 1);
	}
}

function lib_remove_private_protected_from_titles( $format ) {
	return '%s';
}
add_filter( 'private_title_format', 'lib_remove_private_protected_from_titles' );