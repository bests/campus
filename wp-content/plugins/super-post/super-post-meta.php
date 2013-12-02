<?php
/*
	Super Post Meta
	@since 1.5
	
	Copyright 2013  zourbuth.com  (email : zourbuth@gmail.com)

    This program is free software; you can redistribute it and/or modify
    it under the terms of the GNU General Public License, version 2, as 
    published by the Free Software Foundation.

    This program is distributed in the hope that it will be useful,
    but WITHOUT ANY WARRANTY; without even the implied warranty of
    MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
    GNU General Public License for more details.

    You should have received a copy of the GNU General Public License
    along with this program; if not, write to the Free Software
    Foundation, Inc., 51 Franklin St, Fifth Floor, Boston, MA  02110-1301  USA
*/

class Super_Post_Meta {

	/**
	 * Class constructor
	 * @return void
	 * @since 1.5
	**/
	function __construct() {
		add_action( 'admin_init', array( &$this, 'add_meta_box' ) );
		add_action( 'save_post', array( &$this, 'save_metabox' ) );
		add_action( 'admin_enqueue_scripts', array( &$this, 'load_scripts' ), 99 );			
		add_action( 'wp_head', array( &$this, 'header_enqueue' ), 2 );
		add_action( 'wp_head', array( &$this, 'header_post_enqueue' ), 99 );

		add_action('wp_ajax_likes', array( &$this, 'likes' ) );
		add_action('wp_ajax_nopriv_likes', array( &$this, 'likes' ) );
		
		add_action('wp_ajax_rates', array( &$this, 'rates' ) );
		add_action('wp_ajax_nopriv_rates', array( &$this, 'rates' ) );	
		
		$option = get_option( 'super_post' );
		if ( is_array( $option['post_type_meta'] ) ) {
			foreach ( $option['post_type_meta'] as $key => $val ) {
				add_filter( "the_content", array( &$this, "the_content" ) );
				add_filter( "manage_{$key}_posts_columns", array( &$this, "column_info" ) );
				add_action( "manage_{$key}_posts_custom_column", array( &$this, "custom_column" ), 10, 2 );
			}
		}
	}
	
	
	/**
	 * Additional column for the selected post type from the plugin option
	 * Later action by add add_filter( "manage_{$post_type}_posts_columns", $posts_columns );
	 * @return $columns.
	 * @since 1.5
	**/
	function header_enqueue() {
		$options = get_option( 'super_post' );
		wp_enqueue_style( 'super-post', SUPER_POST_URL . 'css/super-post.css' );
		wp_enqueue_script( 'jquery');
		wp_enqueue_script( 'super-post', SUPER_POST_URL . 'js/jquery.super-post.js');
		wp_localize_script( 'super-post', 'superPost', apply_filters('sp_localize_script', array(
			'ajaxurl'	=> admin_url('admin-ajax.php'),
			'nonce'		=> wp_create_nonce( 'super-post' ),
			'likes'		=> array( 
							'action' 	=> 'likes',
							'success'	=> $options['likes_success_message'],
							'error'		=> $options['likes_error_message']
			),
			'rates'		=> array( 
							'action' 	=> 'rates',
							'success'	=> $options['rates_success_message'],
							'error'		=> $options['rates_error_message']
			)
		)));
	}
	
	/**
	 * Additional column for the selected post type from the plugin option
	 * Later action by add add_filter( "manage_{$post_type}_posts_columns", $posts_columns );
	 * @return $columns.
	 * @since 1.5
	**/
	function header_post_enqueue() {	
		if( get_the_ID() ) {
			$meta = get_post_meta( get_the_ID(), 'super_post', true );
			if( isset( $meta['custom'] ) )
				echo "\n" . $meta['custom'];
		}
	}

	
	/**
	 * Ajax function for like
	 * @since 1.5
	**/
	function likes() {
		$nonce = $_POST['nonce'];
		if ( ! wp_verify_nonce( $nonce, 'super-post' ) && ! isset( $_POST['id'] ) )
			die();

		// Get the post ID
		$post_id = $_POST['id'];
		$meta = get_post_meta( $post_id, 'super_post', true );

		// Create or modified the cookie
		$this->set_cookie( $post_id, 'likes' );
		
		$meta['likes']++;
		update_post_meta( $post_id, 'super_post', $meta );
		
		echo $meta['likes'];
		exit;
	}
	
	
	/**
	 * Ajax function for rates
	 * @since 1.5
	**/
	function rates() {
		if( ! wp_verify_nonce( $_POST['nonce'], 'super-post' ) && ! isset( $_POST['id'] ) && ! isset( $_POST['star'] ) )
			die();

		// Get the post ID
		$post_id = $_POST['id'];
		$meta = get_post_meta( $post_id, 'super_post', true );
		
		// Create or modified the cookie
		$this->set_cookie( $post_id, 'rates' );
		
		$meta['rates']['vote']++;
		$meta['rates']['star'] = (int) $meta['rates']['star'] + (int) $_POST['star'];
		update_post_meta( $post_id, 'super_post', $meta );
		
		echo (int) $meta['rates']['star'] / (int) $meta['rates']['vote'];
		exit;
	}	
	
	
	/**
	 * Function for creating or modifying the super post cookie
	 * Uses WordPress define cookie path and domain
	 * Cookie expired time is 5 years 
	 * @since 1.5
	**/
	function set_cookie( $post_id, $type = 'likes' ) {
		$cookie = array();
		if( isset( $_COOKIE["super_post"] ) ) {
			$cookie = json_decode( stripslashes( $_COOKIE["super_post"] ) );

			if( isset( $cookie->$type ) )
				array_push( $cookie->$type, $post_id );
			else
				$cookie->$type = array( $post_id );
						
		} else {
			$cookie[$type] = array( $post_id );
		}
		
		setcookie( 'super_post', json_encode( $cookie ), time() + (5*365*24*60*60), COOKIEPATH, COOKIE_DOMAIN );
	}


	/**
	 * Creating the metabox
	 * Check if the current user can edit post or other post type
	 * Add the meta box if current custom post type is selected
	 * @param $key, 'side', 'high' for custom position
	 * @since 1.5
	**/
	function add_meta_box() {
		if( ! current_user_can( 'edit_others_posts' ) )
			return;

		$options = get_option( 'super_post' );
		
		if ( isset( $options['post_type_meta'] ) && is_array( $options['post_type_meta'] ) ) {
			foreach( $options['post_type_meta'] as $key => $post_type ) {
				add_meta_box( 'super_post_meta_box', __( 'Super Post Meta', 'super-post' ), array( &$this, 'super_post_meta_box' ), $key, 'normal', 'high' );
			}
		}
	}

	/**
	 * Creating the metabox fields
	 * We don't find any match to use the fields as a global variable, manually but best at least for now
	 * Using the name field [] for array results
	 * @param string $post_id
	 * @since 1.5
	**/
	function super_post_meta_box() {
		global $post, $post_id, $wp_registered_sidebars;
		$meta = get_post_meta($post_id, 'super_post', true);
		$options = get_option( 'super_post' );

		$callbacks = array(
			'none' 				=> __( 'No Callback', 'super-post' ),
			'lightbox' 			=> __( 'Lightbox', 'super-post' ),
			'hide-content' 		=> __( 'Hide Content', 'super-post' ),
			'show-content' 		=> __( 'Show Content', 'super-post' ),
			'redirect' 			=> __( 'Redirect', 'super-post' )
		);

		echo '<div class="totalControls tabbable tabs-left">';
		// Create nonces
		echo '<input type="hidden" name="sp_nonce" id="sp_nonce" value="' . wp_create_nonce( plugin_basename(__FILE__) ) . '" />';

		$tabs = isset( $meta['tab'] ) ? $meta['tab'] : array( 'general' => 1 );

		$tabname = array( 
			'general'	=> __( 'General', 'super-post' ),
			'advanced'	=> __( 'Advanced', 'super-post' )
		);
		$tabname = apply_filters( 'sp_meta_tab', $tabname ); // for addons tab

		echo '<ul class="nav nav-tabs">';
			$active = array();
			foreach ( $tabname as $key => $tab ) {
				$val = isset( $tabs[$key] ) ? $tabs[$key] : 0;			
				$active[$key] = isset( $tabs[$key] ) && $tabs[$key] ? 'active' : '';
				echo "<li class='" . $active[$key] . "'>" . $tab . "<input type='hidden' name='sp[tab][$key]' value='" . $val . "' /></li>";
			}
		echo '</ul>';
		
		// Create meta fields
		echo '<ul class="tab-content">';
			
			echo '<li class="tab-pane ' . $active['general'] . '">';
				echo '<ul>';			
					echo '<li>';
						$views = isset( $meta['views'] ) ? $meta['views'] : 0;
						echo '<label for="sp[views]">' . __( 'Post Views', 'the-countdown-pro' ) . '</label>';
						echo '<span class="controlDesc">' . __( 'The post total views, you can set this number by editing this field. This field will be added by 1 for each view.', 'the-countdown-pro' ) . '</span>';
						echo "<input id='sp[views]' name='sp[views]' type='text' value='$views' />";									
					echo '</li>';
					
					echo '<li>';
						$likes = isset($meta['likes']) ? $meta['likes'] : '';
						echo '<label for="sp[likes]">' . __( 'Post Likes', 'the-countdown-pro' ) . '</label>';
						echo '<span class="controlDesc">' . __( 'The post total likes, you can set this number by editing this field. This field will be added by 1 for each likes.', 'the-countdown-pro' ) . '</span>';					
						echo "<input id='sp[likes]' name='sp[likes]' type='text' value='$likes' />";					
					echo '</li>';
					
					echo '<li>';
						$emails = isset($meta['emails']) ? $meta['emails'] : '';
						echo '<label for="sp[emails]">' . __( 'Sharing Emails', 'the-countdown-pro' ) . '</label>';
						echo '<span class="controlDesc">' . __( 'The post sharing emails, you can set this number by editing this field. This field will be added by 1 for each sending.', 'the-countdown-pro' ) . '</span>';					
						echo "<input id='sp[emails]' name='sp[emails]' type='text' value='$emails' />";					
					echo '</li>';
				echo '</ul>';
			echo '</li>';
			
			echo '<li class="tab-pane ' . $active['advanced'] . '">';
				echo '<ul>';
					echo '<li>';
						$custom = isset($meta['custom']) ? $meta['custom'] : '';
						echo '<label for="sp[bg_image]">' . __( 'Custom Styles or Scripts', 'super-post' ). '</label>';
						echo "<textarea class='widefat code' id='sp[custom]' name='sp[custom]' >$custom</textarea>";				
						echo '<span class="controlDesc">' . __( 'Use this option to push custom syles or script to the header.', 'super-post' ) . '</span>';
					echo '</li>';
				echo '</ul>';
			echo '</li>';
			
			do_action('sp_meta_tab_content', $active, $meta); // for addons tab content
			
		echo '</ul>';
		
		echo '</div>';
	}


	/**
	 * Saving metabox data on save action
	 * Checking the nonce, make sure the current post type have sidebar option enable
	 * Save the post metadata with update_post_meta for the current $post_id in array
	 * @param string $post_id
	 * @since 1.5
	**/
	function save_metabox( $post_id ) {

		// Verify this came from the our screen with proper authorization,
		// because save_post can be triggered at other times
		if ( isset($_POST['sp_nonce']) && !wp_verify_nonce( $_POST['sp_nonce'], plugin_basename(__FILE__) ))
			return $post_id;

		// Verify if this is an auto save routine. If our form has not been submitted, so we dont want to do anything
		if ( defined('DOING_AUTOSAVE') && DOING_AUTOSAVE )
			return $post_id;

		$options = get_option( 'super_post' );

		// Check permissions if this post type is use the sidebar meta option
		// Array value [cpt] => Array ( [testimonial] => 1 [statement] => 1 )
		if ( isset( $_POST['post_type'] ) && is_array( $options['cpt'] ) && array_key_exists( $_POST['post_type'], $options['cpt'] ) )  {
			if ( !current_user_can( 'edit_page', $post_id ) )
				return $post_id;
		} else {
			if ( !current_user_can( 'edit_post', $post_id ) )
				return $post_id;
		}

		// Alright folks, we're authenticated, let process
		if ( $parent_id = wp_is_post_revision($post_id) )
			$post_id = $parent_id;

		// Save the post meta data
		if ( isset( $_POST['sp'] ) ) {
			$settings = get_post_meta( $post_id, 'super_post', true );
			foreach ( $_POST['sp'] as $key => $data ) {
				$settings[$key] = $data;
			}
			
			update_post_meta($post_id, 'super_post', $settings );
		}
	}


	/**
	 * Load custom style or script to the current page admin
	 * Enqueue the jQuery library including UI, colorpicker, 
	 * the popup window and some custom styles/scripts
	 * @param string $hook.
	 * @since 1.5
	**/
	function load_scripts( $hook ) {
		if( 'post.php' != $hook && 'post-new.php' != $hook )
			return;
		
		wp_enqueue_style( 'farbtastic' );
		wp_enqueue_style( 'thickbox' );
		wp_enqueue_style( 'sp-dialog', SUPER_POST_URL . 'css/dialog.css' );

		wp_enqueue_script( 'jquery' );
		wp_enqueue_script( 'farbtastic' );
		wp_enqueue_script( 'media-upload' );
		wp_enqueue_script( 'thickbox' );

		wp_enqueue_script( 'sp-dialog', SUPER_POST_URL . 'js/jquery.dialog.js' );
		wp_enqueue_script( 'sp-meta', SUPER_POST_URL . 'js/jquery.meta.js' );
		wp_localize_script( 'sp-meta', 'spLocalize', array(
			'nonce'		=> wp_create_nonce( 'super-post' ),  // generate a nonce for further checking below
			'action'	=> 'super_post_generate_callback'
		));		
	}


	/**
	 * Get the current post ID and + 1 to the meta for counting
	 * using the get_post_meta and update_post_meta
	 * @return $content.
	 * @since 1.5
	**/
	function the_content( $content ) {
		
		// For a content text lower than the excerpt 
		if ( ! is_singular() )
			return $content;
			
		// Get the post ID
		global $post;
		$option = get_option( 'super_post' );
		
		// Check if the meta is enable from the plugin settings page
		if ( ! is_array( $option['post_type_meta'] ) || ! array_key_exists( $post->post_type, $option['post_type_meta'] ) )
			return $content;
		
		if( $post->ID ) {
			$meta = get_post_meta( $post->ID, 'super_post', true );
			if( isset( $meta['views'] ) ) {
				$meta['views']++;
				update_post_meta( $post->ID, 'super_post', $meta );
			}
		}			

		$views = isset( $meta['views'] ) ? $meta['views'] : 0;
		$views_html = $option['display_post_views'] ? "<li><span class='sp-views'>Views <span>{$views}</span></span></li>" : "";	
		
		$likes = isset( $meta['likes'] ) ? $meta['likes'] : 0;
		$likes_html = $option['display_post_likes'] ? "<li><a class='sp-likes' href='#'>Likes <span>{$likes}</span></a></li>" : "";	
		
		$stars = isset ( $meta['rates'] ) && isset ( $meta['rates']['star'] ) && isset (  $meta['rates']['vote'] ) ? $meta['rates']['star'] / $meta['rates']['vote'] : 0;
		$rates_html = "<span>1</span><span>2</span><span>3</span><span>4</span><span>5</span>";			
		$rates_html = $option['display_post_ratings'] ? "<li><span class='sp-rates sp-rating-default'>Rating <span data-rate='$stars'>$rates_html</span></span></li>" : "";		
		
		$utils_html = $option['display_post_views'] || $option['display_post_likes'] || $option['display_post_ratings'] ? "<ul id='sp-utility-{$post->ID}' class='sp-utility sp-utility-default'>$views_html$likes_html$rates_html</ul>" : "";		
		
		return $utils_html . $content;
	}


	/**
	 * Get the current post ID and + 1 to the meta for counting
	 * Uses the get_post_meta and update_post_meta
	 * Later action by add do_action( "manage_{$post->post_type}_posts_custom_column", $column_name, $post->ID );
	 * @return $content.
	 * @since 1.5
	**/
	function custom_column( $column_name, $post_ID ) {
		global $post;
		$meta = get_post_meta( $post->ID, 'super_post', true );
		
		// Display the post views
		$views = isset( $meta['views'] ) ? $meta['views'] : 0;
		echo __( 'Views: ', 'super-post' ) . $views . '<br />';
		
		// Display the post views
		$likes = isset( $meta['likes'] ) ? $meta['likes'] : 0;
		echo __( 'Likes: ', 'super-post' ) . $likes;
	}


	/**
	 * Additional column for the selected post type from the plugin option
	 * Later action by add add_filter( "manage_{$post_type}_posts_columns", $posts_columns );
	 * @return $columns.
	 * @since 1.5
	**/
	function column_info( $columns ) {
		$columns['sp-info'] = __( 'Infos', 'super-post' );
		return $columns;
	}
}
?>