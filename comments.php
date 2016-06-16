<?php

add_action("wp_enqueue_scripts", "lib_comments_scripts");
function lib_comments_scripts() {
	if (is_single()) {
		wp_enqueue_script("lib-comments", plugin_dir_url( __FILE__ ) . "js/libcomments.js", array("jquery", "wp-api"), "1.0", true);
        wp_localize_script( 'lib-comments', 'libComments', array( 'ajaxurl' => admin_url( 'admin-ajax.php' ), 'post_id' => get_the_ID() ) );
	}
}

function ajaxify_comments( $comment_id, $comment_approved, $comment_data ){
    if( ! empty( $_SERVER['HTTP_X_REQUESTED_WITH'] ) && strtolower( $_SERVER['HTTP_X_REQUESTED_WITH'] ) == 'xmlhttprequest' ) {
        //If AJAX Request Then

        $data = array();

        switch( $comment_approved ) {
            case '0':
            	$data["status"] = "moderation";
                //notify moderator of unapproved comment
                //wp_notify_moderator( $comment_id );
            case '1': //Approved comment
                $data["status"] = "publish";
				$comment = get_comment( $comment_id );
				ob_start();
				bones_comments($comment);
				$data["comment_parent"] = $comment->comment_parent;
				$data["html"] = ob_get_contents();
				ob_end_clean();
                //wp_notify_postauthor( $comment_id );
                break;
            case 'spam':
            	$data["status"] = "moderation";
            	break;
            default:
                $data["status"] = "error";
        }

        echo json_encode($data);
        exit;
    }
}
//add_action( 'comment_post', 'ajaxify_comments', 20, 3 );

