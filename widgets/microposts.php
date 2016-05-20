<?php
/*
 * Plugin Name: Micropost Widget
 * Plugin URI: http://libernazione.it
 * Description: A widget that show latest post from the Micropost custom type
 * Version: 1.1
 * Author: Aioros
 * Author URI: http://libernazione.it
 */

class Micropost extends WP_Widget {
	
	
	/**
	 * Register widget
	**/
	public function __construct() {
		
		parent::__construct(
	 		'micropost', // Base ID
			__( 'Micropost', 'themetext' ), // Name
			array( 'description' => __( 'Mostra l\'ultimo micropost', 'themetext' ), ) // Args
		);
		
	}

	
	/**
	 * Front-end display of widget
	**/
	public function widget( $args, $instance ) {
				
		extract( $args );

		$title = apply_filters('widget_title', $instance['title'] );
		$items_num = $instance['items_num'];
		$widget_type = isset( $instance['widget_type'] ) ? $instance['widget_type'] : false;
		
		/** 
		 * Latest Microposts
		**/
		global $post;
		$ti_latest_posts = new WP_Query(
			array(
				'post_type' => 'micropost',
				'posts_per_page' => $items_num
			)
		);
		
		if ($ti_latest_posts->have_posts()) {
		
			echo $before_widget;
			
			if ( $title ) echo $before_title . $title . $after_title;
			?>
			
			<div class="<?php echo $instance['widget_type']; ?>">
				<?php if ( $instance['widget_type'] == 'flexslider' ) { echo '<ul class="slides">'; } ?>

					<?php while ( $ti_latest_posts->have_posts() ) : $ti_latest_posts->the_post(); ?>
					
					<?php if ( $instance['widget_type'] == 'flexslider' ) { echo '<li class="' . implode(' ', get_post_class('', $post->ID)) . '">'; } else { echo '<article class="' . implode(' ', get_post_class('', $post->ID)) . '">'; }  ?>
							<?php
							$title = get_the_title();
							$guest_author = get_post_meta($post->ID, "mp_author", true);
							?>
							<?php if (strlen(trim($title)) > 0) { ?>
							<h2 class="entry-title">
                                <a href="<?php the_permalink(); ?>"><?php echo $title; ?></a>
                            </h2>
							<?php } ?>
							<div class="entry-content">
								<?php if (strlen(trim($guest_author)) > 0) { ?>
								<blockquote>
								<?php } ?>
								<?php the_content(); ?>
								<?php if (strlen(trim($guest_author)) > 0) { ?>
								</blockquote>
								<?php } ?>
								<span class="entry-author">
									<?php if (strlen(trim($guest_author)) > 0)
										echo trim($guest_author);
									else
										the_author_posts_link(); ?>
								</span>
							</div>
							
						<?php if ( $instance['widget_type'] == 'flexslider' ) { echo '</li>'; } else { echo '</article>'; }  ?>
					
					<?php endwhile; ?>
					
				<?php if ( $instance['widget_type'] == 'flexslider' ) { echo '</ul>'; } ?>
			</div>
			
		<?php echo $after_widget;
		} ?>
		
		<?php wp_reset_query();		
		
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
		
		return $instance;
		
	}
	
	
	/**
	 * Back-end widget form
	**/
	public function form( $instance ) {
		
		/* Default widget settings. */
		$defaults = array(
			'title' => 'Micropost',
			'items_num' => '1',
			'widget_type' => 'flexslider'
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
	<?php
	}

}
register_widget( 'Micropost' );