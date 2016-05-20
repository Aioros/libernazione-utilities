<?php

class Rubriche extends WP_Widget {

	public function __construct() {
		$widget_ops = array( 'classname' => 'rubriche', 'description' => "Una lista o dropdown di rubriche" );
		parent::__construct('rubriche', "Rubriche", $widget_ops);
	}

	public function widget( $args, $instance ) {

		$title = apply_filters( 'widget_title', empty( $instance['title'] ) ? "Rubriche" : $instance['title'], $instance, $this->id_base );

		$c = ! empty( $instance['count'] ) ? '1' : '0';

		echo $args['before_widget'];
		if ( $title ) {
			echo $args['before_title'] . $title . $args['after_title'];
		}

		$rub_args = array(
			'orderby'      => 'name',
			'show_count'   => $c
		);
?>
		<ul>
<?php
		$rub_args['title_li'] = '';
		$rub_args["taxonomy"] = "rubriche";
		wp_list_categories( apply_filters( 'widget_rubriche_args', $rub_args ) );
?>
		</ul>
<?php
		

		echo $args['after_widget'];
	}

	public function update( $new_instance, $old_instance ) {
		$instance = $old_instance;
		$instance['title'] = sanitize_text_field( $new_instance['title'] );
		$instance['count'] = !empty($new_instance['count']) ? 1 : 0;

		return $instance;
	}

	public function form( $instance ) {
		//Defaults
		$instance = wp_parse_args( (array) $instance, array( 'title' => '') );
		$title = sanitize_text_field( $instance['title'] );
		$count = isset($instance['count']) ? (bool) $instance['count'] :false;
		?>
		<p><label for="<?php echo $this->get_field_id('title'); ?>"><?php _e( 'Title:' ); ?></label>
		<input class="widefat" id="<?php echo $this->get_field_id('title'); ?>" name="<?php echo $this->get_field_name('title'); ?>" type="text" value="<?php echo esc_attr( $title ); ?>" /></p>

		<input type="checkbox" class="checkbox" id="<?php echo $this->get_field_id('count'); ?>" name="<?php echo $this->get_field_name('count'); ?>"<?php checked( $count ); ?> />
		<label for="<?php echo $this->get_field_id('count'); ?>"><?php _e( 'Show post counts' ); ?></label><br />

		<?php
	}

}

function rubriche_load_widget() {
	register_widget( 'rubriche' );
}
add_action( 'widgets_init', 'rubriche_load_widget' );