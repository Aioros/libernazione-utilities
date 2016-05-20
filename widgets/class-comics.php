<?php

class Comics extends WP_Widget {
	
	
	/**
	 * Register widget
	**/
	public function __construct() {
		
		parent::__construct(
	 		'comics', // Base ID
			'Comics', // Name
			array( 'description' => 'Mostra le ultime vignette' ) // Args
		);
		
	}

	
	/**
	 * Front-end display of widget
	**/
	public function widget( $args, $instance ) {

		do_action("comics_pre_action_hook");

		extract( $args );

		$title = apply_filters('widget_title', $instance['title'] );
		
		$widget_type = isset( $instance['widget_type'] ) ? $instance['widget_type'] : false;
		
		global $post;
		
		if (isset($instance["comics"])) {
			$comics = $instance["comics"];
		} else {
			$items_num = $instance['items_num'];
			$comics = get_recent_comics($items_num);
		}
		
		if (count($comics) > 0) {
			echo $before_widget;
			if ( $title ) echo $before_title . '<a href="http://libernazione.it/type/image/">' . $title . '</a>' . $after_title;
			?>
			<div class="comic-container">
			<section class="flexslider comics-slider loading animated">
				
				<ul class="slides">
				<?php foreach ($comics as $comic) {
					global $post;
					$post = $comic;
					setup_postdata($post);
					?>
					<li data-id="<?php echo $comic->ID;?>">
						<article <?php post_class($class); ?>>

						    <figure class="comic-image">
						    	<a href="<?php the_permalink(); ?>">
									<?php
									if ( has_post_thumbnail() ) { // Set Featured Image
										the_post_thumbnail("medium");
						            } elseif( first_post_image() ) { // Set the first image from the editor
						            	echo '<img src="' . first_post_image() . '" class="wp-post-image" />';
						           	}
						            ?>
						    	</a>
						    </figure>
						        
						    <header class="entry-header comic-header article-header">
						        <h3 class="entry-title comic-title">
						            <a href="<?php the_permalink() ?>"><?php the_title(); ?></a>
						        </h3>
						        <div class="byline comic-meta entry-meta">
							        <span class="comic-author entry-author vcard author">
							        	<a href="<?php echo get_author_posts_url( get_the_author_meta( 'ID' ) ); ?>" rel="author"><?php the_author_meta( 'display_name', $post->post_author ); ?></a>
							        </span>
							        <span class="comic-date updated" itemprop="datePublished" content="<?php the_time( get_option( 'date_format' ) ); ?>">
					            		<a href="<?php the_permalink() ?>" title="Permalink to <?php the_title(); ?>" rel="bookmark">
					            			<time datetime="<?php the_time('c'); ?>"><?php the_time( get_option( 'date_format' ) ); ?></time>
					            		</a>
					            	</span>
						        </div>
						    </header>
						    
						</article>
					</li>
				<?php } ?>
				
				<?php wp_reset_query(); ?>
				
				</ul>
			</section><!-- Slider -->
			</div>
			<?php
			echo $after_widget;

			do_action("comics_post_action_hook");
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
		
		return $instance;
		
	}
	
	
	/**
	 * Back-end widget form
	**/
	public function form( $instance ) {
		
		/* Default widget settings. */
		$defaults = array(
			'title' => 'Ultime vignette',
			'items_num' => '5'
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
	<?php
	}

}