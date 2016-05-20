<?php
/*
 * Plugin Name: Flameboards Widget
 * Plugin URI: http://libernazione.it
 * Description: A widget that show latest flameboards
 * Version: 1.1
 * Author: Aioros
 * Author URI: http://libernazione.it
 */

class Flameboards extends WP_Widget {
	
	
	/**
	 * Register widget
	**/
	public function __construct() {
		
		parent::__construct(
	 		'flameboards', // Base ID
			__( 'Flameboards', 'themetext' ), // Name
			array( 'description' => __( 'Mostra le ultime Flameboard', 'themetext' ), ) // Args
		);
		
	}

	
	/**
	 * Front-end display of widget
	**/
	public function widget( $args, $instance ) {
		extract( $args );

		$title = apply_filters('widget_title', $instance['title'] );
		
		$widget_type = isset( $instance['widget_type'] ) ? $instance['widget_type'] : false;
		$send_article = $instance['send_article'];
		$fb_comments = $instance['fb_comments'];
		
		global $post;
		
		if (isset($instance["flameboards"])) {
			$flameboards = $instance["flameboards"];
		} else {
			$items_num = $instance['items_num'];
			$flameboards = get_recent_flameboards($items_num);
		}
		
		$archive_term = get_term_by( 'slug', get_query_var( 'term' ), get_query_var( 'taxonomy' ) );
		
		if (count($flameboards) > 0) {
			echo $before_widget;
			if ( $title ) echo $before_title . $title . $after_title;
			?>
			<article class="flameboard-container">
			<section class="flexslider flameboards-slider loading animated">
				
				<ul class="slides">
				<?php foreach ($flameboards as $flameboard) { ?>
					<?php $term = get_term_by( 'term_id', $flameboard->term_id, 'flameboard' );	?>
					<?php if ($flameboard->term_id != $archive_term->term_id) { ?>
					<li data-id="<?php echo $flameboard->term_id;?>">
						<?php
						if ($widget_type == 'flexslider')
							include(locate_template('flameboard-grid.php'));
						else
							include(locate_template('flameboard-list-simple.php'));
						?>
						<a class="flameboard-link"<?php if (!$send_article) echo ' style="width: 100%;"';?> id="flameboard_link_<?php echo $term->term_id; ?>" href="<?php echo get_term_link($term); ?>" target="_blank">Vai alla flameboard</a>
						<?php if ($send_article) {
							include(locate_template("inc/send-post.php"));
						} ?>
						<?php if ($fb_comments) { ?>
							<div class="fb-comments-container">
							<div class="fb-comments" data-href="<?php echo get_term_link($term); ?>" data-numposts="10" data-colorscheme="light" data-width="100%"></div>
							</div>
						<?php } ?>
					</li>
					<?php } ?>
				<?php } ?>
				
				<?php wp_reset_query(); ?>
				
				</ul>
				<?php if ($fb_comments) { ?>
					<script>
					$(document).ready(function() {
						var finished_rendering = function() {
							$(window).resize();
						}
						window.fbAsyncInit = function() {
							FB.init({"xfbml":true,"appId":"370346486491395"});
							if (FB_WP && FB_WP.queue && FB_WP.queue.flush) {
								FB_WP.queue.flush();
							}
							FB.Event.subscribe('xfbml.render', finished_rendering);
						}							
					});
					</script>
				<?php } ?>
			</section><!-- Slider -->
			</article>
			<?
			echo $after_widget;
		}
	}
	
	
	/**
	 * Sanitize widget form values as they are saved
	**/
	public function update( $new_instance, $old_instance ) {
		
		$instance = array();

		/* Strip tags to remove HTML. For text inputs and textarea. */
		$instance['title'] = strip_tags( $new_instance['title'] );
		$instance['items_num'] = strip_tags( $new_instance['items_num'] );
		$instance['widget_type'] = $new_instance['widget_type'];
		$instance['send_article'] = $new_instance['send_article'];
		$instance['fb_comments'] = $new_instance['fb_comments'];
		
		return $instance;
		
	}
	
	
	/**
	 * Back-end widget form
	**/
	public function form( $instance ) {
		
		/* Default widget settings. */
		$defaults = array(
			'title' => 'Flameboard',
			'items_num' => '5',
			'widget_type' => 'flexslider',
			'send_article' => false,
			'fb_comments' => false
		);
		$instance = wp_parse_args( (array) $instance, $defaults );
		
	?>
		<p>
			<label for="<?php echo $this->get_field_id( 'title' ); ?>"><?php _e('Title:', 'themeText'); ?></label>
			<input type="text" id="<?php echo $this->get_field_id( 'title' ); ?>" name="<?php echo $this->get_field_name( 'title' ); ?>" value="<?php echo $instance['title']; ?>" class="widefat" />
		</p>
		<p>
			<label for="<?php echo $this->get_field_id( 'items_num' ); ?>"><?php _e('Maximum posts to show:', 'themetext'); ?></label>
			<input type="text" id="<?php echo $this->get_field_id( 'items_num' ); ?>" name="<?php echo $this->get_field_name( 'items_num' ); ?>" value="<?php echo $instance['items_num']; ?>" size="1" />
		</p>
        <p>            
        	<input type="radio" id="<?php echo $this->get_field_id( 'flexslider' ); ?>" name="<?php echo $this->get_field_name( 'widget_type' ); ?>" <?php if ($instance["widget_type"] == 'flexslider') echo 'checked="checked"'; ?> value="<?php _e('flexslider', 'themetext'); ?>" />
            <label for="<?php echo $this->get_field_id( 'flexslider' ); ?>"><?php _e( 'Display posts as Slider', 'themetext' ); ?></label><br />
            
			<input type="radio" id="<?php echo $this->get_field_id( 'entries' ); ?>" name="<?php echo $this->get_field_name( 'widget_type' ); ?>" <?php if ($instance["widget_type"] == 'entries') echo 'checked="checked"'; ?> value="<?php _e('entries', 'themetext'); ?>" />
            <label for="<?php echo $this->get_field_id( 'entries' ); ?>"><?php _e( 'Display posts as List', 'themetext' ); ?></label>
        </p>
		<p>
			<input type="checkbox" id="<?php echo $this->get_field_id( 'send_article' ); ?>" name="<?php echo $this->get_field_name( 'send_article' ); ?>" <?php if( $instance['send_article'] == true ) echo 'checked'; ?> /> 
			<label for="<?php echo $this->get_field_id( 'send_article' ); ?>"><?php _e('Mostra tasto "Invia articolo"', 'themetext'); ?></label>
		</p>
		<p>
			<input type="checkbox" id="<?php echo $this->get_field_id( 'fb_comments' ); ?>" name="<?php echo $this->get_field_name( 'fb_comments' ); ?>" <?php if( $instance['fb_comments'] == true ) echo 'checked'; ?> /> 
			<label for="<?php echo $this->get_field_id( 'fb_comments' ); ?>"><?php _e('Mostra commenti di Facebook', 'themetext'); ?></label>
		</p>
	<?php
	}

}
register_widget( 'Flameboards' );