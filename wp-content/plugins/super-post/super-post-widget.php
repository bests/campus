<?php
/**
 * Widget - Super Post
 * 
 * @package zFrame 1.0
 * @subpackage Classes
 * For another improvement, you can drop email to zourbuth@gmail.com or visit http://zourbuth.com/
**/

class Super_Post_Widget_Class extends WP_Widget {
	
	/**
	 * Textdomain for the widget.
	 * @since 2.0
	 */
	var $textdomain;
	
	function __construct() {
		
		// Set the widget textdomain
		$this->slug = 'super-post';
		$this->textdomain = 'super-post';

		// Load the widget stylesheet for the widgets admin screen.
		add_action( 'load-widgets.php', array( &$this, 'custom_post_admin_style' ) );

		// Set up the widget options.
		$widget_options = array(
			'classname' => $this->slug,
			'description' => esc_html__( '[+] Show your popular, most commented, or else to a widget.', $this->textdomain )
		);

		// Set up the widget control options.
		$control_options = array(
			'width' => 420,
			'height' => 350,
			'id_base' => $this->slug
		);

		$this->WP_Widget( $this->slug, esc_attr__( 'Super Post', $this->textdomain ), $widget_options, $control_options );	
			
		if ( is_active_widget( false, false, $this->id_base ) && !is_admin() ) {
			// print the user costum style sheet
			wp_enqueue_style( $this->slug, SUPER_POST_URL . 'css/super-post.css' );
			wp_enqueue_script('jquery');
			wp_enqueue_script( $this->slug, SUPER_POST_URL . 'js/jquery.super-post.js' );
			add_action( 'wp_head', array( &$this, 'sp_head_print_script' ) );
		}
	}
	
	/* Push the widget stylesheet widget.css into widget admin page */
	function custom_post_admin_style() {
		wp_enqueue_script( 'jquery' );
		wp_enqueue_style( 'farbtastic' );
		wp_enqueue_script( 'farbtastic' );
		wp_enqueue_script( 'thickbox' );
		wp_enqueue_style( 'thickbox' );			
		wp_enqueue_style( 'super-post-admin', SUPER_POST_URL . 'css/dialog.css', false, 0.7, 'screen' );
		wp_enqueue_script( 'super-post-admin', SUPER_POST_URL . 'js/jquery.dialog.js' );
	}
	

	/**
	 * Print all widget settings for use in ajax
	 * @since 1.0.0
	 */	
	function sp_print_localize_script() {
		$id = array();
		foreach ( $this->get_settings() as $key => $setting ){
			return $id[$key] = $setting;
		}
	}
	
	
	function sp_head_print_script() {
		$settings = $this->get_settings();
		foreach ( $settings as $key => $setting ){
			$widget_id = $this->id_base . '-' . $key;
			if( is_active_widget( false, $widget_id, $this->id_base ) ){
				echo '<style type="text/css">';
				echo '.cpw-date { background: url(' . $setting['date_icon'] . ') no-repeat scroll 0 0 transparent; }';
				echo '.cpw-comments { background: url(' . $setting['comment_icon'] . ') no-repeat scroll 0 0 transparent; }';
				echo '</style>';
				
				if ( !empty( $setting['customstylescript'] ) ) echo $setting['customstylescript'];
			}
		}
	}
	
	function widget($args, $instance) {
		extract( $args, EXTR_SKIP );

		/* Set up the arguments for z_list_authors(). */
		$args = array(
			'id'				=> $this->number,
			'query'				=> $instance['query'],
			'query'				=> $instance['query'],
			'order'				=> $instance['order'],
			'taxonomy' 			=> !empty( $instance['taxonomy'] ) ? join( ', ', $instance['taxonomy'] ) : '',
			'items'				=> !empty( $instance['items'] ) ? intval( $instance['items'] ) : 4,
			'show_excerpt' 		=> !empty( $instance['show_excerpt'] ) ? true : false,
			'excerpt_length'	=> !empty( $instance['excerpt_length'] ) ? intval( $instance['excerpt_length'] ) : 15,
			'excerpt_more'		=> $instance['excerpt_more'],
			'show_thumbnail' 	=> !empty( $instance['show_thumbnail'] ) ? true : false,
			'show_date' 		=> !empty( $instance['show_date'] ) ? true : false,
			'show_date_link' 	=> !empty( $instance['show_date_link'] ) ? true : false,
			'date_icon' 		=> SUPER_POST_URL . 'img/date.png',
			'date_format' 		=> $instance['date_format'],
			'show_comments' 	=> !empty( $instance['show_comments'] ) ? true : false,
			'comment_icon' 		=> SUPER_POST_URL . 'img/comments.png',
			
			'icon_height' 		=> $instance['icon_height'],
			'icon_width' 		=> $instance['icon_width'],
			
			'show_author' 		=> !empty( $instance['show_author'] ) ? true : false,
			'author_title' 		=> $instance['author_title'],
			
			'icon_empty' 		=> $instance['icon_empty'],
			'template'			=> $instance['template'],
			'read_more'			=> $instance['read_more'],
			'load_style'		=> $instance['load_style'],
			'load_text'			=> $instance['load_text'],
			'toggle_active'		=> $instance['toggle_active'],
			'intro_text' 		=> $instance['intro_text'],
			'outro_text' 		=> $instance['outro_text'],
			'customstylescript'	=> $instance['customstylescript']
		);

		/* Output the theme's $before_widget wrapper. */
		echo $before_widget;

		/* If a title was input by the user, display it. */
		if ( !empty( $instance['title'] ) )
			echo $before_title . apply_filters( 'widget_title',  $instance['title'], $instance, $this->id_base ) . $after_title;

		/* Print intro text if exist */
		if ( !empty( $instance['intro_text'] ) )
			echo '<p class="'. $this->id . '-intro-text">' . $instance['intro_text'] . '</p>';
		
		if (empty( $instance['taxonomy'] ) ) $instance['taxonomy'] = '';
		
		// Print the custom post
		echo super_post( $args );
		
		/* Print outro text if exist */
		if ( !empty( $instance['outro_text'] ) )
			echo '<p class="'. $this->id . '-outro_text">' . $instance['outro_text'] . '</p>';
			
		/* Close the theme's widget wrapper. */
		echo $after_widget;
	}

	function update($new_instance, $old_instance) {
		$instance = $old_instance;

		/* Set the instance to the new instance. */
		$instance = $new_instance;
		
		/* If new taxonomy is chosen, reset includes and excludes. */
		if ( $instance['query'] !== $old_instance['query'] && '' !== $old_instance['query'] ) {
			$instance['taxonomy'] = array();
		}

		$instance['items'] 				= $new_instance['items'];
		$instance['order'] 				= $new_instance['order'];
		$instance['show_thumbnail'] 	= ( isset( $new_instance['show_thumbnail'] ) ? 1 : 0 );
		$instance['show_date'] 			= ( isset( $new_instance['show_date'] ) ? 1 : 0 );
		$instance['show_date_link'] 	= ( isset( $new_instance['show_date_link'] ) ? 1 : 0 );
		$instance['date_icon'] 			= $new_instance['date_icon'];
		$instance['date_format'] 		= $new_instance['date_format'];
		$instance['show_excerpt'] 		= ( isset( $new_instance['show_excerpt'] ) ? 1 : 0 );
		$instance['excerpt_length'] 	= $new_instance['excerpt_length'];
		$instance['excerpt_more'] 		= $new_instance['excerpt_more'];
		$instance['show_comments'] 		= ( isset( $new_instance['show_comments'] ) ? 1 : 0 );
		$instance['comment_icon'] 		= $new_instance['comment_icon'];
		
		$instance['show_author'] 		= ( isset( $new_instance['show_author'] ) ? 1 : 0 );
		$instance['author_title'] 		= $new_instance['author_title'];
		
		$instance['icon_height'] 		= $new_instance['icon_height'];
		$instance['icon_width'] 		= $new_instance['icon_width'];
		$instance['icon_empty'] 		= $new_instance['icon_empty'];
		$instance['template']			= $new_instance['template'];
		$instance['read_more']			= $new_instance['read_more'];
		$instance['load_style']			= $new_instance['load_style'];
		$instance['load_text']			= $new_instance['load_text'];
		$instance['toggle_active'] 		= $new_instance['toggle_active'];
		$instance['intro_text'] 		= $new_instance['intro_text'];
		$instance['outro_text'] 		= $new_instance['outro_text'];
		$instance['customstylescript']	= $new_instance['customstylescript'];
		
		return $instance;
	}

	function form($instance) {
		/* Set up the default form values. */
		$defaults = array(
			'title' 			=> '',
			'query' 			=> 'recent',
			'order' 			=> 'DESC',
			'taxonomy' 			=> array(),
			'items' 			=> '5',
			'show_excerpt' 		=> true,
			'excerpt_length' 	=> 15,
			'excerpt_more' 		=> '...',
			'show_thumbnail' 	=> true,
			'show_date' 		=> true,
			'show_date_link'	=> true,
			'date_icon' 		=> SUPER_POST_URL . 'img/date.png',
			'date_format' 		=> get_option( 'date_format' ),
			'show_comments' 	=> true,
			'comment_icon' 		=> SUPER_POST_URL . 'img/comments.png',
			
			'show_author' 		=> false,
			'author_title' 		=> 'View all posts by {author}',
			'author_icon' 		=> SUPER_POST_URL . 'img/author.png',
			
			'icon_height' 		=> 40,
			'icon_width' 		=> 40,
			'icon_empty' 		=> '',
			'template'			=> 'left',
			'read_more'			=> '',
			'load_style'		=> 'append',
			'load_text'			=> __('Load more posts', $this->textdomain),
			'toggle_active'		=> array(0 => 1),
			'intro_text' 		=> '',
			'outro_text' 		=> '',
			'customstylescript'	=> ''
		);

		/* Merge the user-selected arguments with the defaults. */
		$instance = wp_parse_args( (array) $instance, $defaults );

		$queries = array(	
			'recent'	=> esc_attr__( 'Recent Posts', $this->textdomain ), 
			'popular'	=> esc_attr__( 'Most Commented', $this->textdomain ),
			'sticky'	=> esc_attr__( 'Sticky', $this->textdomain ),
			'related'	=> esc_attr__( 'Related Posts', $this->textdomain )
		);
		$load_style = array(	
			'append'	=> esc_attr__( 'Append', $this->textdomain ), 
			'paging'	=> esc_attr__( 'Paging', $this->textdomain )
		);
		$templates = array( 
			'left' => esc_attr__( 'Left', $this->textdomain )
		);
		$orders = array(
			'ASC'	=> esc_attr__( 'Ascending', $this->textdomain ), 
			'DESC'	=> esc_attr__( 'Descending', $this->textdomain )
		);
		$taxonomies = get_taxonomies( array( 'show_tagcloud' => true, '_builtin' => true ), 'objects' );
		$terms = get_terms( $instance['query'] );
		
?>
		<div class="pluginName">Super Post<span class="pluginVersion"><?php echo SUPER_POST_VERSION; ?></span></div>
		<div id="cp-<?php echo $this->id ; ?>" class="totalControls tabbable tabs-left">
			<ul class="nav nav-tabs">
				<li class="<?php if ( $instance['toggle_active'][0] ) : ?>active<?php endif; ?>"><?php echo __( 'General', $this->textdomain); ?><input type="hidden" name="<?php echo $this->get_field_name( 'toggle_active' ); ?>[]" value="<?php echo esc_attr( $instance['toggle_active'][0] ); ?>" /></li>
				<li class="<?php if ( $instance['toggle_active'][1] ) : ?>active<?php endif; ?>"><?php echo __( 'Excerpts', $this->textdomain); ?><input type="hidden" name="<?php echo $this->get_field_name( 'toggle_active' ); ?>[]" value="<?php echo esc_attr( $instance['toggle_active'][1] ); ?>" /></li>
				<li class="<?php if ( $instance['toggle_active'][2] ) : ?>active<?php endif; ?>"><?php echo __( 'Thumbnails', $this->textdomain); ?><input type="hidden" name="<?php echo $this->get_field_name( 'toggle_active' ); ?>[]" value="<?php echo esc_attr( $instance['toggle_active'][2] ); ?>" /></li>
				<li class="<?php if ( $instance['toggle_active'][3] ) : ?>active<?php endif; ?>"><?php echo __( 'Comments', $this->textdomain); ?><input type="hidden" name="<?php echo $this->get_field_name( 'toggle_active' ); ?>[]" value="<?php echo esc_attr( $instance['toggle_active'][3] ); ?>" /></li>
				<li class="<?php if ( $instance['toggle_active'][4] ) : ?>active<?php endif; ?>"><?php echo __( 'Date', $this->textdomain); ?><input type="hidden" name="<?php echo $this->get_field_name( 'toggle_active' ); ?>[]" value="<?php echo esc_attr( $instance['toggle_active'][4] ); ?>" /></li>
				<li class="<?php if ( $instance['toggle_active'][5] ) : ?>active<?php endif; ?>"><?php echo __( 'Author', $this->textdomain); ?><input type="hidden" name="<?php echo $this->get_field_name( 'toggle_active' ); ?>[]" value="<?php echo esc_attr( $instance['toggle_active'][5] ); ?>" /></li>
				<li class="<?php if ( $instance['toggle_active'][6] ) : ?>active<?php endif; ?>"><?php echo __( 'Templates', $this->textdomain); ?><input type="hidden" name="<?php echo $this->get_field_name( 'toggle_active' ); ?>[]" value="<?php echo esc_attr( $instance['toggle_active'][6] ); ?>" /></li>
				<li class="<?php if ( $instance['toggle_active'][7] ) : ?>active<?php endif; ?>"><?php echo __( 'Customs', $this->textdomain); ?><input type="hidden" name="<?php echo $this->get_field_name( 'toggle_active' ); ?>[]" value="<?php echo esc_attr( $instance['toggle_active'][7] ); ?>" /></li>
				<li class="<?php if ( $instance['toggle_active'][8] ) : ?>active<?php endif; ?>"><?php echo __( 'Premium', $this->textdomain); ?><input type="hidden" name="<?php echo $this->get_field_name( 'toggle_active' ); ?>[]" value="<?php echo esc_attr( $instance['toggle_active'][8] ); ?>" /></li>
			</ul>
			<ul class="tab-content">
				<li class="tab-pane <?php if ( $instance['toggle_active'][0] ) : ?>active<?php endif; ?>">
					<ul>
						<li>
							<label for="<?php echo $this->get_field_id( 'title' ); ?>"><?php _e( 'Widget Title', $this->textdomain ); ?></label>
							<span class="controlDesc"><?php _e( 'Put the title here, or leave it empty to hide the title.', $this->textdomain ); ?></span>
							<input type="text" class="widefat" id="<?php echo $this->get_field_id( 'title' ); ?>" name="<?php echo $this->get_field_name( 'title' ); ?>" value="<?php echo esc_attr( $instance['title'] ); ?>" />
						</li>
						<li>
							<label for="<?php echo $this->get_field_id( 'query' ); ?>"><?php _e( 'Post Query', $this->textdomain ); ?></label> 
							<span class="controlDesc"><?php _e( 'Select the post type or custom query for post. For more queries please <a href="http://goo.gl/HDhZx">get premium</a>.', $this->textdomain ); ?></span>
							<select onchange="wpWidgets.save(jQuery(this).closest('div.widget'),0,1,0);" id="<?php echo $this->get_field_id( 'query' ); ?>" name="<?php echo $this->get_field_name( 'query' ); ?>">
								<?php foreach ( $queries as $key => $val ) { ?>
									<option value="<?php echo esc_attr( $key ); ?>" <?php selected( $instance['query'], $key ); ?>><?php echo esc_html( $val ); ?></option>
								<?php } ?>
								<?php foreach ( $taxonomies as $taxonomy ) { ?>
									<option value="<?php echo esc_attr( $taxonomy->name ); ?>" <?php selected( $instance['query'], $taxonomy->name ); ?>><?php echo 'Taxonomy: ' . $taxonomy->label; ?></option>
								<?php } ?>
							</select>
						</li>
						<li>
							<label for="<?php echo $this->get_field_id( 'taxonomy' ); ?>"><?php _e( 'Term(s)', $this->textdomain ); ?></label>
							<span class="controlDesc"><?php _e( 'Select the post type or custom query for post.', $this->textdomain ); ?></span>
							<select class="widefat" id="<?php echo $this->get_field_id( 'taxonomy' ); ?>" name="<?php echo $this->get_field_name( 'taxonomy' ); ?>[]" size="4" multiple="multiple">
								<?php if ( is_array( $terms ) ) { ?>
									<?php foreach ( $terms as $term ) { ?>
										<option value="<?php echo esc_attr( $term->term_id ); ?>" <?php echo ( in_array( $term->term_id, (array) $instance['taxonomy'] ) ? 'selected="selected"' : '' ); ?>><?php echo esc_html( $term->name ); ?></option>
									<?php } ?>
								<?php } ?>
							</select>
						</li>
						<li>
							<label for="<?php echo $this->get_field_id('items'); ?>"><?php _e( 'Post Number', $this->textdomain ); ?> </label>
							<span class="controlDesc"><?php _e( 'The total post to display in a widget.', $this->textdomain ); ?></span>
							<input class="smallfat" id="<?php echo $this->get_field_id('items'); ?>" name="<?php echo $this->get_field_name('items'); ?>" type="text" value="<?php echo esc_attr($instance['items']); ?>" />
						</li>						
						<li>
							<label for="<?php echo $this->get_field_id( 'order' ); ?>"><?php _e( 'Sort Order', $this->textdomain ); ?></label> 
							<span class="controlDesc"><?php _e( 'The page order in ascending or descending ordering', $this->textdomain ); ?></span>
							<select id="<?php echo $this->get_field_id( 'order' ); ?>" name="<?php echo $this->get_field_name( 'order' ); ?>">
								<?php foreach ( $orders as $key => $value ) { ?>
									<option value="<?php echo esc_attr( $key ); ?>" <?php selected( $instance['order'], $key ); ?>><?php echo esc_html( $value ); ?></option>
								<?php } ?>
							</select>
						</li>
					</ul>
				</li>
				<li class="tab-pane <?php if ( $instance['toggle_active'][1] ) : ?>active<?php endif; ?>">
					<ul>
						<li>
							<label for="<?php echo $this->get_field_id( 'show_excerpt' ); ?>">
							<input class="checkbox" type="checkbox" <?php checked( $instance['show_excerpt'], true ); ?> id="<?php echo $this->get_field_id( 'show_excerpt' ); ?>" name="<?php echo $this->get_field_name( 'show_excerpt' ); ?>" /><?php _e( 'Show Excerpt', $this->textdomain ); ?></label>
							<span class="controlDesc"><?php _e( 'Display the post excerpt.', $this->textdomain ); ?></span>
						</li>					
						<li>
							<label for="<?php echo $this->get_field_id('excerpt_length'); ?>"><?php _e( 'Excerpt Lenght', $this->textdomain ); ?> </label>
							<span class="controlDesc"><?php _e( 'The excerpt total spaces to generate.', $this->textdomain ); ?></span>
							<input class="smallfat" id="<?php echo $this->get_field_id('excerpt_length'); ?>" name="<?php echo $this->get_field_name('excerpt_length'); ?>" type="text" value="<?php echo esc_attr( $instance['excerpt_length'] ); ?>" />
						</li>
						<li>
							<label for="<?php echo $this->get_field_id( 'excerpt_more' ); ?>"><?php _e( 'More Text', $this->textdomain ); ?></label>
							<span class="controlDesc"><?php _e( 'Put the more text at the end of the excerpt.', $this->textdomain ); ?></span>
							<input type="text" class="widefat" id="<?php echo $this->get_field_id( 'excerpt_more' ); ?>" name="<?php echo $this->get_field_name( 'excerpt_more' ); ?>" value="<?php echo esc_attr( $instance['excerpt_more'] ); ?>" />
						</li>
						<li>
							<label for="<?php echo $this->get_field_id( 'read_more' ); ?>"><?php _e( 'Read More', $this->textdomain ); ?></label>
							<span class="controlDesc"><?php _e( 'The read more text with link for the complete post content view.', $this->textdomain ); ?></span>
							<input type="text" class="widefat" id="<?php echo $this->get_field_id( 'read_more' ); ?>" name="<?php echo $this->get_field_name( 'read_more' ); ?>" value="<?php echo esc_attr( $instance['read_more'] ); ?>" />
						</li>						
					</ul>
				</li>
				<li class="tab-pane <?php if ( $instance['toggle_active'][2] ) : ?>active<?php endif; ?>">
					<ul>
						<li>
							<label for="<?php echo $this->get_field_id( 'show_thumbnail' ); ?>">
							<input class="checkbox" type="checkbox" <?php checked( $instance['show_thumbnail'], true ); ?> id="<?php echo $this->get_field_id( 'show_thumbnail' ); ?>" name="<?php echo $this->get_field_name( 'show_thumbnail' ); ?>" /><?php _e( 'Show thumbnail', $this->textdomain ); ?></label>
							<span class="controlDesc"><?php _e( 'Display the standard post featured image or thumbnail.', $this->textdomain ); ?></span>
						</li>
						<li>
							<label for="<?php echo $this->get_field_id( 'icon_height' ); ?>"><?php _e( 'Height', $this->textdomain ); ?></label>
							<span class="controlDesc"><?php _e( 'The featured image or thumbnail height in pixels unit.', $this->textdomain ); ?></span>
							<input type="text" id="<?php echo $this->get_field_id( 'icon_height' ); ?>" name="<?php echo $this->get_field_name( 'icon_height' ); ?>" value="<?php echo esc_attr( $instance['icon_height'] ); ?>" />
						</li>
						<li>
							<label for="<?php echo $this->get_field_id( 'icon_width' ); ?>"><?php _e( 'Width', $this->textdomain ); ?></label>
							<span class="controlDesc"><?php _e( 'The featured image or thumbnail width in pixels unit.', $this->textdomain ); ?></span>
							<input type="text" id="<?php echo $this->get_field_id( 'icon_width' ); ?>" name="<?php echo $this->get_field_name( 'icon_width' ); ?>" value="<?php echo esc_attr( $instance['icon_width'] ); ?>" />
						</li>
					</ul>
				</li>				
				<li class="tab-pane <?php if ( $instance['toggle_active'][3] ) : ?>active<?php endif; ?>">
					<ul>
						<li>
							<label for="<?php echo $this->get_field_id( 'show_comments' ); ?>">
							<input class="checkbox" type="checkbox" <?php checked( $instance['show_comments'], true ); ?> id="<?php echo $this->get_field_id( 'show_comments' ); ?>" name="<?php echo $this->get_field_name( 'show_comments' ); ?>" /><?php _e( 'Show comments number', $this->textdomain ); ?></label>
							<span class="controlDesc"><?php _e( 'Display the comments for each post.', $this->textdomain ); ?></span>
						</li>			
					</ul>
				</li>
				<li class="tab-pane <?php if ( $instance['toggle_active'][4] ) : ?>active<?php endif; ?>">
					<ul>
						<li>
							<label for="<?php echo $this->get_field_id( 'show_date' ); ?>">
							<input class="checkbox" type="checkbox" <?php checked( $instance['show_date'], true ); ?> id="<?php echo $this->get_field_id( 'show_date' ); ?>" name="<?php echo $this->get_field_name( 'show_date' ); ?>" /><?php _e( 'Show date', $this->textdomain ); ?></label>
							<span class="controlDesc"><?php _e( 'Display the post date for each post.', $this->textdomain ); ?></span>
						</li>
						<li>
							<label for="<?php echo $this->get_field_id( 'show_date_link' ); ?>">
							<input class="checkbox" type="checkbox" <?php checked( $instance['show_date_link'], true ); ?> id="<?php echo $this->get_field_id( 'show_date_link' ); ?>" name="<?php echo $this->get_field_name( 'show_date_link' ); ?>" /><?php _e( 'Show Link', $this->textdomain ); ?></label>
							<span class="controlDesc"><?php _e( 'Check if you want the date as a a link to the post.', $this->textdomain ); ?></span>
						</li>
						<li>
							<label for="<?php echo $this->get_field_id( 'date_format' ); ?>"><?php _e( 'Date Format', $this->textdomain ); ?></label>
							<span class="controlDesc"><?php _e( 'Controls the format of the Page date set by the <b>show date</b> parameter. This parameter defaults to the date format configured in your WordPress options. See <a title="Formatting Date and Time" href="http://codex.wordpress.org/Formatting_Date_and_Time">Formatting Date and Time</a> and the <a title="http://php.net/date" class="external text" href="http://php.net/date">date format page on the php web site</a>.', $this->textdomain ); ?></span>
							<input type="text" style="width: 48%;" class="smallfat code" id="<?php echo $this->get_field_id( 'date_format' ); ?>" name="<?php echo $this->get_field_name( 'date_format' ); ?>" value="<?php echo esc_attr( $instance['date_format'] ); ?>" />
						</li>						
					</ul>
				</li>				
				<li class="tab-pane <?php if ( $instance['toggle_active'][5] ) : ?>active<?php endif; ?>">
					<ul>
						<li>
							<label for="<?php echo $this->get_field_id( 'show_author' ); ?>">
							<input class="checkbox" type="checkbox" <?php checked( $instance['show_author'], true ); ?> id="<?php echo $this->get_field_id( 'show_author' ); ?>" name="<?php echo $this->get_field_name( 'show_author' ); ?>" /><?php _e( 'Show Author', $this->textdomain ); ?></label>
							<span class="controlDesc"><?php _e( 'Display the post date for each post.', $this->textdomain ); ?></span>
						</li>
						<li>
							<label for="<?php echo $this->get_field_id( 'author_title' ); ?>"><?php _e( 'Author Link Title', $this->textdomain ); ?></label>
							<span class="controlDesc"><?php _e( 'The author post link title. Use {author} for template tag.', $this->textdomain ); ?></span>
							<input type="text" class="widefat" id="<?php echo $this->get_field_id( 'author_title' ); ?>" name="<?php echo $this->get_field_name( 'author_title' ); ?>" value="<?php echo esc_attr( $instance['author_title'] ); ?>" />
						</li>					
					</ul>
				</li>	
				<li class="tab-pane <?php if ( $instance['toggle_active'][6] ) : ?>active<?php endif; ?>">
					<ul>
						<li>
							<label for="<?php echo $this->get_field_id( 'template' ); ?>"><?php _e( 'Style', $this->textdomain ); ?></label> 
							<span class="controlDesc"><?php _e( 'Select widget template style here and adjust the thumbnail size. You will get more templates style with <a href="http://goo.gl/HDhZx">premium version</a>.', $this->textdomain ); ?></span>
							<select onchange="wpWidgets.save(jQuery(this).closest('div.widget'),0,1,0);" class="smallfat" id="<?php echo $this->get_field_id( 'template' ); ?>" name="<?php echo $this->get_field_name( 'template' ); ?>">
								<?php foreach ( $templates as $template => $option_label ) { ?>
									<option value="<?php echo esc_attr( $template ); ?>" <?php selected( $instance['template'], $template ); ?>><?php echo esc_html( $option_label ); ?></option>
								<?php } ?>
							</select>
						</li>	
						<li>
							<p><label><?php _e( 'Preview', $this->textdomain ); ?></label></p>
							<?php foreach ( $templates as $template => $option_label ) { ?>
								<?php if ( $instance['template'] == $template ) { ?>
									<div class="template-<?php echo esc_attr( $template ); ?> apw-template">
										<p><a class="template-image href="#"><img alt="" src="<?php echo SUPER_POST_URL; ?>img/thumbnail.png" /></a></p>
										<p><a class="template-title" href="#">Lorem Ipsum Dolor Sit Amet</a></p>
										<p>	
											<a class="template-date" href="#">November 12, 2012</a>
											<a class="template-comments" href="#">35</a>
										</p>
										<p class="template-desc">Lorem ipsum dolor sit amet, consectetur adipisicing elit, sed do eiusmod tempor incididunt ut labore et dolore magna aliqua...</p>
									</div>
								<?php } ?>
							<?php } ?>
						</li>
					</ul>
				</li>
				<li class="tab-pane <?php if ( $instance['toggle_active'][7] ) : ?>active<?php endif; ?>">
					<ul>
						<li>
							<label for="<?php echo $this->get_field_id('intro_text'); ?>"><?php _e( 'Intro Text', $this->textdomain ); ?></label>
							<span class="controlDesc"><?php _e( 'This option will display addtional text before the widget title and HTML supports.', $this->textdomain ); ?></span>
							<textarea name="<?php echo $this->get_field_name( 'intro_text' ); ?>" id="<?php echo $this->get_field_id( 'intro_text' ); ?>" rows="2" class="widefat"><?php echo esc_textarea($instance['intro_text']); ?></textarea>
							
						</li>
						<li>
							<label for="<?php echo $this->get_field_id('outro_text'); ?>"><?php _e( 'Outro Text', $this->textdomain ); ?></label>
							<span class="controlDesc"><?php _e( 'This option will display addtional text after widget and HTML supports.', $this->textdomain ); ?></span>
							<textarea name="<?php echo $this->get_field_name( 'outro_text' ); ?>" id="<?php echo $this->get_field_id( 'outro_text' ); ?>" rows="2" class="widefat"><?php echo esc_textarea($instance['outro_text']); ?></textarea>
							
						</li>				
						<li>
							<label for="<?php echo $this->get_field_id('customstylescript'); ?>"><?php _e( 'Custom Script & Stylesheet', $this->textdomain ) ; ?></label>
							<span class="controlDesc"><?php _e( 'Use this box for additional widget CSS style of custom javascript. This widget selector is: ', $this->textdomain ); ?><?php echo '<code>#' . $this->id . '</code>'; ?></span>
							<textarea name="<?php echo $this->get_field_name( 'customstylescript' ); ?>" id="<?php echo $this->get_field_id( 'customstylescript' ); ?>" rows="3" class="widefat code"><?php echo htmlentities($instance['customstylescript']); ?></textarea>
						</li>
					</ul>
				</li>
				<li class="tab-pane <?php if ( $instance['toggle_active'][8] ) : ?>active<?php endif; ?>">				
					<ul>
						<li>
							<a href="http://goo.gl/HDhZx"><img class="spimg" src="<?php echo SUPER_POST_URL . 'img/super-post.png'; ?>" alt="" /></a>
							<h3 style="margin-bottom: 3px;"><?php _e( 'Upgrade To Premium Version', $this->textdomain ); ?></h3>
							<span class="controlDesc">
								<?php _e( 'This premium version gives more abilities, features, options and premium supports for displaying your posts 
										in a better way. You will get help soon if you have problems with the premium version. Full documentation will let 
										you customize this premium version easily. <br />
										See the full <a href="http://zourbuth.com/plugins/super-post/"><strong>Live Preview</strong></a>.
										<br /><br />
										Main key features you will get with premium version:', $this->textdomain ); ?>
							</span>
							
						</li>
						<li>
							<ul>
								<li>
									<strong><?php _e( 'Premium Supports', $this->textdomain ) ; ?></strong>
									<span class="controlDesc"><?php _e( 'A premium supports, helps and documentation.', $this->textdomain ); ?></span>
								</li>
								<li>
									<strong><?php _e( 'Search Posts', $this->textdomain ) ; ?></strong>
									<span class="controlDesc"><?php _e( 'New feature for searching posts with shortcode or widget via Ajax.', $this->textdomain ); ?></span>
								</li>								
								<li>
									<strong><?php _e( 'Post Taxonomies', $this->textdomain ) ; ?></strong>
									<span class="controlDesc"><?php _e( 'Easy to display your post based on custom taxonomies, eq. posts from portfolio, testimonial, product etc.', $this->textdomain ); ?></span>
								</li>
								<li>
									<strong><?php _e( 'Ajax Posts', $this->textdomain ) ; ?></strong>
									<span class="controlDesc"><?php _e( 'Load more posts via Ajax + animations.', $this->textdomain ); ?></span>
								</li>
								<li>
									<strong><?php _e( 'Advanced Post Shares, Likes, Views and Rating', $this->textdomain ) ; ?></strong>
									<span class="controlDesc"><?php _e( 'Shares post via Facebook, Twitter, Google+ or Email.', $this->textdomain ); ?></span>
								</li>
								<li>
									<strong><?php _e( 'Shortcode Editor + Widget Shortcode', $this->textdomain ) ; ?></strong>
									<span class="controlDesc"><?php _e( 'Button in your post editor. No more writing shortcodes.', $this->textdomain ); ?></span>
								</li>
								<li>
									<strong><?php _e( 'Easy Templates', $this->textdomain ) ; ?></strong>
									<span class="controlDesc"><?php _e( 'Displaying your posts with more template style options.', $this->textdomain ); ?></span>
								</li>
								<li>
									<strong><?php _e( 'Custom Icon', $this->textdomain ) ; ?></strong>
									<span class="controlDesc"><?php _e( 'Easy to add another icon for post date and comment.', $this->textdomain ); ?></span>
								</li>
								<li>
									<strong><?php _e( 'Default Post Thumbnail', $this->textdomain ) ; ?></strong>
									<span class="controlDesc"><?php _e( 'Set a default post thumbnail for posts that do not have a thumbnail or featured image.', $this->textdomain ); ?></span>
								</li>
								<li>
									<strong><?php _e( 'And more...', $this->textdomain ) ; ?></strong>
									<span class="controlDesc"><?php _e( 'Much more option than the free version.', $this->textdomain ); ?></span>
								</li>
							</ul>
						</li>						
						<li>
							<style type="text/css">
								.spimg { 
									border: 1px solid #DDDDDD;
									border-radius: 2px 2px 2px 2px;
									float: right;
									padding: 4px;
									margin-left: 8px;
								}
								.spimg:hover { 
									border: 1px solid #cccccc;
								}
								.wp-core-ui .btnremium { 
									border-color: #CCCCCC;
									height: auto;
									margin-top: 9px;
									padding-bottom: 0;
									padding-right: 0;
								}
								.wp-core-ui .btnremium span {
									background: none repeat scroll 0 0 #FFFFFF;
									border-left: 1px solid #F2F2F2;
									display: inline-block;
									font-size: 18px;
									line-height: 25px;
									margin-left: 9px;
									padding: 0 9px;
									border-radius: 0 3px 3px 0;
								}
							</style>	
							<a href="http://zourbuth.com/plugins/super-post/"><strong>Live Preview</strong></a><br />
							<a class="button btnremium" href="http://goo.gl/HDhZx">Get Premium<span>$8</span></a>
						</li>
					</ul>
				</li>
			</ul>
		</div>
<?php
	}
}
?>