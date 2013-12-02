<?php
/*
    Grouping Widget Settings
	
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

class Super_Post_Settings {
	
	private $sections;
	private $checkboxes;
	private $sidebar;
	private $settings;
	
	var $textdomain;
	var $title;
	var $slug;
	
	
	/**
	 * Construct
	 *
	 * @since 1.0
	 */
	function __construct() {
	
		$this->textdomain = 'super-post';
		$this->title = 'Super Post';
		$this->slug = 'super_post';
		
		// This will keep track of the checkbox options for the validate_settings function.
		$this->checkboxes = array();
		$this->sidebar = array();
		$this->settings = array();
		$this->sections = array();
		$this->get_option();
		
		$this->sections = array (
			'general'		=> __( 'General', $this->textdomain ),
			'share'			=> __( 'Share', $this->textdomain ),			
			'advanced'		=> __( 'Advanced', $this->textdomain ),
			'upgrade'		=> __( 'Premium', $this->textdomain )
		);

		add_action( 'admin_menu', array( &$this, 'add_pages' ) );
		add_action( 'admin_init', array( &$this, 'register_settings' ) );
		add_action( 'wp_head', array( &$this, 'print_custom'));
		add_action( "wp_ajax_{$this->slug}_ajax_dialog", array( &$this, 'ajax_dialog'));
		add_action( "wp_ajax_{$this->slug}_shortcode_utility", array( &$this, 'shortcode_utility'));
		
		if ( ! get_option( $this->slug ) )
			$this->initialize_settings();
	}
	
	
	/**
	 * Create settings field
	 * @since 1.0
	 */
	function create_setting( $args = array() ) {
		
		$defaults = array(
			'id'      	=> 'default_field',
			'title'   	=> __( 'Default Field', $this->textdomain ),
			'desc'    	=> __( 'This is a default description.', $this->textdomain ),
			'std'     	=> '',
			'type'    	=> 'text',
			'section' 	=> 'general',
			'opts' 		=> array(),
			'slide'		=> array(),
			'class'   	=> ''
		);
			
		extract( wp_parse_args( $args, $defaults ) );
		
		$field_args = array(
			'type'      => $type,
			'id'        => $id,
			'section' 	=> $section,
			'desc'      => $desc,
			'std'       => $std,
			'opts'   	=> $opts,
			'slide'   	=> $slide,
			'label_for' => $id,
			'class'     => $class
		);
		
		if ( $type == 'checkbox' )
			$this->checkboxes[] = $id;
		
		add_settings_field( $id, $title, array( $this, 'display_setting' ), $this->slug, $section, $field_args );
	}
	
	
	/**
	 * Display options page
	 *
	 * @since 1.0
	 */
	function display_page() {
		
		echo '<div class="wrap">
			<div class="icon32" id="icon-options-general"></div>
			<h2>' . $this->title . __( ' Settings', $this->textdomain ) . '</h2>';
			
			$options = get_option( $this->slug );
			global $wp_registered_sidebars;			
			//print_r( $options );
			echo '<div id="totalForm" class="totalControls tabbable tabs-left">';

			echo '<div id="totalFooter">
					<p class="totalInfo">
						<a target="_blank" href="http://zourbuth.com/?p=862">Super Post ' . SUPER_POST_VERSION . '</a> | 
						<a target="_blank" href="http://www.gnu.org/licenses/gpl-2.0.html">' . __('Licenses', $this->textdomain) . '</a> | 
						<a target="_blank" href="http://codecanyon.net/item/super-post-wordpress-premium-plugin/741603?ref=zourbuth">' . __('Super Post Premium', $this->textdomain) . '</a><br />  
						<a href="https://twitter.com/zourbuth" class="twitter-follow-button" data-show-count="false" data-lang="en">Follow @zourbuth</a>
					</p>
				  </div>';
				  
				echo '<ul class="nav nav-tabs">';					
					$i = 0;
					foreach ( $this->sections as $slug => $section ) {
					
						if ( ! isset( $options['tab'] ) )
							$class = $i == 0 ? 'active' : '';
						else
							$class = $slug == $options['tab'] ? 'active' : '';
						
						$val = isset($options['tab'][$i]) ? $options['tab'][$i] : '';
						
						echo '<li class="' . $class . '">';
							echo $section . "<input type='hidden' name='{$this->slug}[tab][]' value='$val' />";
						echo '</li>';

						$i++;
					}
				echo '</ul>';
					
				echo '<div class="tab-content">';
					foreach ( $this->sections as $slug => $section ) {
						echo '<div id="' . $slug . '-section" class="tab-pane">';
						
						if( ! in_array( $slug, array('shortcodes', 'upgrade') ) )
							echo '<form action="options.php" method="post">';													
						
						settings_fields( $this->slug );
						echo "<input type='hidden' name='{$this->slug}[tab]' value='$slug' />";
						
						echo '<table class="form-table">';
						do_settings_fields( $this->slug, $slug );
						
						if( ! in_array( $slug, array('shortcodes', 'upgrade') ) ) {
							echo '<tr valign="top">
									<th scope="row">&nbsp;</th>
									<td>
										<input id="submit" class="button-primary" type="submit" value="' . __( 'Save Changes', $this->textdomain ) . '" name="submit">
									</td>
								  </tr>';
							echo '</form>';
						}
						
						echo '</table>';
						echo '</div>';
					}
				echo '</div>';

			echo '</div>';		
			
		echo '</div>';
		echo '<script type="text/javascript">
			// !function(d,s,id){var js,fjs=d.getElementsByTagName(s)[0];if(!d.getElementById(id)){js=d.createElement(s);js.id=id;js.src="//platform.twitter.com/widgets.js";fjs.parentNode.insertBefore(js,fjs);}}(document,"script","twitter-wjs");		
			
			jQuery(document).ready(function($) {
				var sections = [];';
				$i = 0;
				foreach ( $this->sections as $slug => $value ) {
					echo "sections['$i'] = '$slug';";
					$i++;
				}

				$options = get_option( $this->slug );
				$tab = isset( $options['tab'] ) ? $options['tab'] : 'general';
				
				echo '
				$(".tab-pane").each(function(index) {
					$(this).attr("id", sections[index]+\'-section\');
					if (sections[index] == "' . $tab . '")
						$(this).addClass("active");

				});
				
				$("ul.nav-tabs li").each(function(i) { // Tabs function
					$(this).bind("click", function(){
						var liIndex = $(this).index();
						var content = $(this).parent("ul").next().children(".tab-pane").eq(liIndex);
						$(this).addClass("active").siblings("li").removeClass("active");
						$(content).show().addClass("active").siblings().hide().removeClass("active");
	
						$(this).parent("ul").find("input").val(0);
						$("input", this).val(1);
						return false;
					});
				});
			});
		</script>';
	}
	
	
	/**
	 * Description for section
	 * @since 1.0
	 */
	function display_section() {
		// code
	}
	
	
	/**
	 * HTML output for text field
	 * @since 1.0
	 */
	function display_setting( $args = array() ) {
		extract( $args );

		$options = get_option( $this->slug );

		if ( ! isset( $options[$id] ) && $type != 'checkbox' )
			$options[$id] = $std;
		elseif ( ! isset( $options[$id] ) )
			$options[$id] = 0;

		$field_class = $class ? $class : 'regular-text';
		
		switch ( $type ) {
			
			case 'checkbox':
				if( $opts ) {
					$opsi = is_array( $options[$id] ) ? array_merge( $options[$id], $opts ) : $opts;
					foreach( $opsi as $key => $val ) {
						echo "<label style='font-weight: normal'><input class='checkbox $field_class' type='checkbox' name='{$this->slug}[$id][$key]' value='1'" . checked( $options[$id][$key], 1, false ) . " />$val</label>";
					}
				} else {
					echo "<input class='checkbox' id='$id' type='checkbox' name='{$this->slug}[$id]' value='1'" . checked( $options[$id], 1, false ) . " />";
				}
				
				if ( $desc != '' ) echo "<span class='description'>$desc</span>";
				break;
			
			case 'select':
				echo "<select class='select{$field_class}' name='{$this->slug}[$id]'>";
				foreach ( $opts as $key => $value ) {
					$key = esc_attr( $key );
					echo "<option value='$key'" . selected( $options[$id], $key, false ) . ">$value</option>";
				}
				echo "</select>";
				if ( $desc != '' ) echo "<span class='description'>$desc</span>";
				break;
			
			case 'radio':
				$i = 0;
				foreach ( $opts as $key => $value ) {
					echo "<input class='radio $field_class' type='radio' name='{$this->slug}[$id]' id='$id$i' value='$key'" . checked( $options[$id], $key, false ) . "> <label for='$id$i'>$value</label>";
					if ( $i < count( $options[$section] ) - 1 )
						echo '<br />';
					$i++;
				}
				if ( $desc != '' ) echo "<span class='description'>$desc</span>";	
				break;
			
			case 'textarea':
				echo "<textarea class='widefat' id='$id' name='{$this->slug}[$id]'>{$options[$id]}</textarea>";				
				if ( $desc != '' ) echo "<span class='description'>$desc</span>";
				break;
			
			case 'image':
				$img = $options[$section][$id];
				if ( empty($img) ) $class = 'hideRemove'; else $class= 'showRemove';

				echo "<img alt='' class='optionImage' src='$img'>";
				echo "<a href='#' class='addImage button'>" . __( 'Add Image', $this->textdomain ) . "</a>";
				echo "<a class='$class removeImage button' href='#'>" . __( 'Remove', $this->textdomain ) . "</a>";
				echo "<input type='hidden' id='$id' name='{$this->slug}[$id]' value='$img' />";
				if ( $desc != '' ) echo "<span class='description'>$desc</span>";
				break;
				
			case 'farbtastic':
				echo '<input type="text" class="color-input" id="' . $id . '" name="navmenu[' . $id . ']" style="background: #' . esc_attr( $options[$section][$id] ) . '; color: #';
					$colortype = esc_attr( $options[$section][$id] ); 
					$colortype = $colortype[0]; 
					if( is_numeric($colortype) ) echo 'fff'; else echo '000';
					echo '" value="' . $options[$section][$id] . '" />
				<a class="pickcolor" href="#" id="pickcolor' . $id . '">pickcolor</a>
				<div id="zcolorpicker' . $id . '" style="z-index: 100; background:#eee; border:1px solid #ccc; position:absolute; display:none; margin-top: 10px;"></div>
			
				<script type="text/javascript">

				var farbtastic' . $id . ';
				function pickcolor' . $id . '(a){
					farbtastic' . $id . '.setColor(a);
					jQuery("#' . $id . '").val(a);
					jQuery("#' . $id . '").css("background-color",a)
				}

				jQuery("#pickcolor' . $id . '").click(function(){
					jQuery("#zcolorpicker' . $id . '").show();
					return false});
					jQuery("#' . $id . '").keyup(function(){var b=jQuery("#' . $id . '").val(),a=b;
					/* if(a.charAt(0)!="#"){a="#"+a}a=a.replace(/[^#a-fA-F0-9]+/,""); */ // uncomment this if you want the "#" still left at the textbox.
					if(a!=b){jQuery("#' . $id . '").val(a)}if(a.length==4||a.length==7){pickcolor' . $id . '(a)}});
					farbtastic' . $id . '=jQuery.farbtastic("#zcolorpicker' . $id . '",function(a){pickcolor' . $id . '(a)});pickcolor' . $id . '(jQuery("#' . $id . '").val());
					jQuery(document).mousedown(function(){
						jQuery("#zcolorpicker' . $id . '").each(function(){var a=jQuery(this).css("display");if(a=="block"){jQuery(this).fadeOut(2)}})
				})

				</script>';
				if ( $desc != '' ) echo "<span class='description'>$desc</span>";
				break;

			case 'sidebar':
				$this->generate_dynamic_sidebar($id, $options[$section][$id], $desc);
				break;

			case 'cpt':
				$this->custom_post_type($id, $options[$id], $desc);
			break;			
			
			case 'shortcode':
				$this->shortcode_generator($id, $options[$id], $desc);
			break;
			
			case 'upgrade':
				$this->upgrade();
			break;
			
			case 'text':
			default:
		 		echo "<input class='$field_class' type='text' id='$id' name='{$this->slug}[$id]' value='".esc_attr( $options[$id] )."' />";
		 		if ( $desc != '' ) echo "<span class='description'>$desc</span>";
		 		break;
		}
		
	}

	
	/**
	 * Function for and fetching the api key
	 * Grid element drag-resize
	 * @since 1.1
	 */
	function shortcode_generator( $id, $options, $desc ) {
		$options = get_option( $this->slug );
		echo "<div id='shortcodeWrapper' class='' style='background: none;'>";
			if( $options['shortcodes'] ) {
				foreach( $options['shortcodes'] as $key => $val ) {
					$this->generate_shortcode_form( $val );
				}
			}
		echo "</div>";				
		echo "<a class='addShortcode button' href='#'>Create Shortcode</a>";
		if ( $desc != '' ) echo '<p class="description">' . $desc . '</p>';
		
	}
	
	
	/**
	 * Function for and fetching the api key
	 * Grid element drag-resize
	 * @since 1.1
	 */
	function generate_shortcode_form( $args ) {
		?><div id="shortcode-<?php echo $args['id']; ?>" class="widget">
				<div class="widget-top">
					<div class="widget-title-action">
						<a href="#available-widgets" class="widget-action hide-if-no-js"></a>
						<a href="#" class="widget-control-edit hide-if-js">
							<span class="edit">Edit</span>
							<span class="add">Add</span>
							<span class="screen-reader-text">[navmenu id="<?php echo $args['id']; ?>"]</span>
						</a>
					</div>
					<div class="widget-title"><h4>Shortcode <?php echo $args['id']; ?></h4></div>
				</div>
		
				<div class="widget-inside">
					<form method="post" action="">					
						<div class="widget-content">
							<?php navmenu_shortcode_forms($args); ?>	
						</div>
						<input type="hidden" value="<?php echo $args['id']; ?>" class="widget-id" name="id">

						<div class="widget-control-actions">
							<div class="alignleft">
								<a href="#remove" class="shortcode-remove">Delete</a> |
								<a href="#close" class="widget-control-close">Close</a>
							</div>
							<div class="alignright">
								<input type="submit" value="Save" class="button button-primary shortcode-save right" id="widget-calendar-2-savewidget" name="savewidget">
								<span class="spinner"></span>
							</div>
							<br class="clear">
						</div>
					</form>
				</div>
			</div><?php
	}
	

	/**
	 * Function for and fetching the api key
	 * Grid element drag-resize
	 * @since 1.1
	 */
	function shortcode_utility() {
		// Check the nonce and if not isset the id, just die
		$nonce = $_POST['nonce'];
		if ( ! wp_verify_nonce( $nonce, 'navmenu' ) )
			die();
			
		$options = get_option( $this->slug );
		$data = array();
		parse_str($_POST['data'], $data);
			
		if( 'create' == $_POST['mode'] ) {
			$options['num']++;
			$args = array(
				'id'	=> $options['num'],
			);
			$this->generate_shortcode_form( $args );
			$options['shortcodes'][$options['num']] = $args;
			update_option( $this->slug, $options );
		
		} elseif( 'save' == $_POST['mode'] ) {
			$options['shortcodes'][$data['id']] = $data;
			update_option( $this->slug, $options );
		
		} elseif( 'delete' == $_POST['mode'] ) {
			unset( $options['shortcodes'][$data['id']] );
			update_option( $this->slug, $options );
		}
		exit();
	}
	
	
	/**
	 * Ajax function
	 * @since 1.1
	 */
	function upgrade(){  ?>
		<ul style="margin-top: 0;">
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
		<?php
	}
	
	
	/**
	 * Ajax function
	 * @since 1.1
	 */
	function ajax_dialog(){
		require_once( NAVMENU_DIR  . 'navmenu-dialog.php' );
		exit; 
	}
	
	
	/**
	 * Function for generating the grid system
	 * Grid element drag-resize
	 * @since 1.0
	 */
	function custom_post_type( $id, $options, $desc ) {
		$options = get_option( $this->slug );
		
		$types = array( 'post' => 'post' );
		
		if ( ! empty( $types ) ) {
			foreach ( $types as $type ) {
				echo "<label style='font-weight: normal'><input class='checkbox' type='checkbox' name='{$this->slug}[$id][$type]' value='1' " . checked( isset($options[$id][$type]), 1, false ) . " /> $type</label>";
			}
			if ( $desc != '' ) echo '<span class="description">' . $desc . '</span>';
		}
	}
	

	/**
	 * Settings and defaults
	 * 
	 * @since 1.0
	 */
	function get_option() {
		
		/* General Settings
		===========================================*/
		$this->settings['post_type_meta'] = array(
			'section'	=> 'general',
			'title'		=> __( 'Post Type Meta', $this->textdomain ),
			'desc'		=> __( 'Check the custom post type(s) to enable the post views, likes and ratings option. More custom post types available for <a href="http://goo.gl/HDhZx"><strong>premium version</strong></a>.', $this->textdomain ),
			'type'		=> 'cpt',
			'std'		=> array( 'post' => 1, 'page' => 1 )
		);			
		$this->settings['display_post_views'] = array(
			'section' => 'general',
			'title'   => __( 'Display Post Views', $this->textdomain ),
			'desc'    => __( 'Check this option for dislaying the total post views for <em>single</em> post view in the front end.', $this->textdomain ),
			'type'    => 'checkbox',
			'std'     => true
		);
		$this->settings['display_post_likes'] = array(
			'section' => 'general',
			'title'   => __( 'Display Post Likes', $this->textdomain ),
			'desc'    => __( 'Check this option for dislaying the total post likes for <em>single</em> post view in the front end.', $this->textdomain ),
			'type'    => 'checkbox',
			'std'     => true
		);
		$this->settings['likes_success_message'] = array(
			'section' => 'general',
			'title'   => __( 'Likes Success Message', $this->textdomain ),
			'desc'    => __( 'Set the success message after user likes the current post.', $this->textdomain ),
			'type'    => 'text',
			'std'     => __('Thaks for liking this post.', $this->textdomain )
		);
		$this->settings['likes_error_message'] = array(
			'section' => 'general',
			'title'   => __( 'Likes Error Message', $this->textdomain ),
			'desc'    => __( 'Set error message if the user already likes the current post.', $this->textdomain ),
			'type'    => 'text',
			'std'     => __('You have liked this post.', $this->textdomain )
		);
		$this->settings['display_post_ratings'] = array(
			'section' => 'general',
			'title'   => __( 'Display Post Ratings', $this->textdomain ),
			'desc'    => __( 'Check this option for dislaying the post ratings for <em>single</em> post view in the front end.', $this->textdomain ),
			'type'    => 'checkbox',
			'std'     => true
		);
		$this->settings['rates_success_message'] = array(
			'section' => 'general',
			'title'   => __( 'Rating Success Message', $this->textdomain ),
			'desc'    => __( 'Set the success message after user rates the current post.', $this->textdomain ),
			'type'    => 'text',
			'std'     => __('Thaks for rating this post.', $this->textdomain )
		);
		$this->settings['rates_error_message'] = array(
			'section' => 'general',
			'title'   => __( 'Rating Error Message', $this->textdomain ),
			'desc'    => __( 'Set error message if the user already rates the current post.', $this->textdomain ),
			'type'    => 'text',
			'std'     => __('You have rated this post.', $this->textdomain )
		);		
		
		/* Share
		===========================================*/
		$this->settings['post_type_share'] = array(
			'section'	=> 'share',
			'title'   	=> __( 'Display Share Button', $this->textdomain ),
			'desc'    	=> __( 'Check this option to display the social share button for posts view in the front end. More custom post types available for <a href="http://goo.gl/HDhZx"><strong>premium version</strong></a>.', $this->textdomain ),
			'type'    	=> 'cpt',
			'std'		=> array( 'post' => 1 )
		);
		$this->settings['share_method'] = array(
			'section'	=> 'share',
			'title'   	=> __( 'Share Method', $this->textdomain ),
			'desc'    	=> __( 'Click to include oe exclude methods. Drag and drop function for arrangement available for <a href="http://goo.gl/HDhZx"><strong>premium version<strong></a>', $this->textdomain ),
			'type'    	=> 'checkbox',
			'class'    	=> 'totals-sortables',
			'opts'    	=> array(
							'twitter' 	=> 'Twitter', 
							'facebook' 	=> 'Facebook', 
							'google+' 	=> 'Google +1', 
							'email' 	=> 'Email'
						),
			'std'		=> array(
							'twitter' 	=> 1, 
							'facebook' 	=> 1, 
							'google+' 	=> 1, 
							'email' 	=> 1
						)
		);
		$this->settings['share_title'] = array(
			'section'	=> 'share',
			'title'   	=> __( 'Share Title', $this->textdomain ),
			'desc'    	=> __( 'Change the share title or leave empty for no title.', $this->textdomain ),
			'type'    	=> 'text',
			'std'		=> __( 'Share Post', $this->textdomain )
		);
		$this->settings['mail_info'] = array(
			'section'	=> 'share',
			'title'   	=> __( 'Mail Info', $this->textdomain ),
			'desc'    	=> __( 'The mail info displayed on the front end. Supports HTML', $this->textdomain ),
			'type'    	=> 'textarea',
			'std'		=> __( 'Send post to email address, <strong>comma separated</strong> for multiple emails.', $this->textdomain )
		);
		$this->settings['mail_subject'] = array(
			'section'	=> 'share',
			'title'   	=> __( 'Mail Subject', $this->textdomain ),
			'desc'    	=> __( 'The mail subject. Availabel tempalte: [post-title].', $this->textdomain ),
			'type'    	=> 'text',
			'std'		=> 'Shared Post - [post-title]'
		);
		$this->settings['mail_body'] = array(
			'section'	=> 'share',
			'title'   	=> __( 'Mail Body', $this->textdomain ),
			'desc'    	=> __( 'The mail content. Available template: [sender-name], [sender-mail], [excerpt] and [link]', $this->textdomain ),
			'type'    	=> 'textarea',
			'std'		=> '[sender-name] ( [sender-mail] ) sent a post to you and perhaps you are interested with the following post:

[excerpt]
							
Read more: [link]'
		);
		
		
		/* Shortcode
		===========================================*/
		$this->settings['shortcode'] = array(
			'section'	=> 'shortcodes',
			'title'		=> __( 'Shortcode Lists', $this->textdomain ),
			'desc'		=> __( 'Please generate your shortcode here and paste it in your content or template.<br />
								Use shortcode <code>[super-post id=2]</code> in your content or<br />
								<code>&lt;?php if( function_exists( \'navmenu\' ) ) navmenu(\'2\'); ?&gt;</code><br />
								where 2 is the given shortcode list id above.', $this->textdomain ),
			'type'		=> 'shortcode',
			'std'		=> ''
		);
		
		/* Advanced
		===========================================*/
		$this->settings['enable_custom'] = array(
			'section' => 'advanced',
			'title'   => __( 'Enable Custom', $this->textdomain ),
			'desc'    => __( 'Check this to push the style script option below', $this->textdomain ),
			'type'    => 'checkbox',
			'std'     => false
		);
		$this->settings['custom'] = array(
			'section' => 'advanced',
			'title'   => __( 'Custom Style & Script', $this->textdomain ),
			'desc'    => __( 'Use this option to add additional styles or script with the tag included.', $this->textdomain ),
			'type'    => 'textarea',
			'std'     => ''
		);
		
		/* Advanced
		===========================================*/
		$this->settings['upgrade'] = array(
			'section' => 'upgrade',
			'title'   => __( 'Premium Features', $this->textdomain ),
			'desc'    => __( '', $this->textdomain ),
			'type'    => 'upgrade',
			'std'     => false
		);
	}

	
	/**
	 * Push the custom styles or scripts to the front end
	 * Check if the custom option is enable and not empty
	 * Use the wp_head action.
	 * @since 1.0
	 */	
	function print_custom() {
		$options = get_option( $this->slug );
		
		if ( isset( $options['enable_custom'] ) && ! empty( $options['custom'] ) ) {
			echo $options['custom'];
		}

	}	

	
	/**
	 * Initialize settings to their default values
	 * @since 1.0
	 */
	function initialize_settings() {
		$defaults = array();
		foreach ( $this->settings as $id => $setting ) {
			$defaults[$id] = $setting['std'];
		}
		
		update_option( $this->slug, $defaults );
	}

	
	/**
	* Register settings
	* add_settings_section($id, $title, $callback, $page)
	* @since 1.0
	*/
	function register_settings() {
		
		register_setting( $this->slug, $this->slug, array ( &$this, 'validate_settings' ) );
		
		foreach ( $this->sections as $slug => $title ) {
			add_settings_section( $this->slug . '-' . $slug, $title, array( &$this, 'display_section' ), $this->slug . '-' . $slug );
		}
		
		$this->get_option();
		
		foreach ( $this->settings as $id => $setting ) {
			$setting['id'] = $id;
			$this->create_setting( $setting );			
		}
	}

	
	/**
	* Enqueue Script
	* @since 1.0
	*/
	function scripts() {
		wp_print_scripts( 'jquery' );
		wp_enqueue_script('admin-widgets');
		wp_print_scripts( 'jquery-ui-droppable' );
		wp_print_scripts( 'jquery-ui-resizable' );
		wp_print_scripts( 'jquery-ui-draggable' );
		wp_print_scripts( 'jquery-ui-sortable' );
		wp_enqueue_script( 'farbtastic' );
		wp_enqueue_script( 'media-upload' );
		wp_enqueue_script( 'thickbox' );
		wp_enqueue_script( $this->slug . '-dialog', SUPER_POST_URL . 'js/jquery.dialog.js');
		wp_enqueue_script( $this->slug . '-settings', SUPER_POST_URL . 'js/jquery.settings.js');
		wp_localize_script( $this->slug . '-settings', 'navmenu', array(
			'nonce'		=> wp_create_nonce( 'navmenu' ),  // generate a nonce for further checking below
			'action'	=> 'navmenu_fetch_ajax',
			'dialog'	=> 'navmenu_ajax_dialog',
			'shortcode'	=> 'navmenu_shortcode_utility'
		));	
	}

	
	/**
	* Styling for the theme options page
	* @since 1.0
	*/
	function styles() {
		wp_enqueue_style( 'farbtastic' );
		wp_enqueue_style( 'thickbox' );			
		wp_enqueue_style( "{$this->slug}-dialog", SUPER_POST_URL . 'css/dialog.css' );
		wp_enqueue_style( "{$this->slug}-settings", SUPER_POST_URL . 'css/settings.css' );
	}
	
	
	/**
	 * Add settings page
	 * @since 1.0
	 */
	function add_pages() {
		$admin_page = add_options_page( $this->title . __( ' Settings', $this->textdomain ), $this->title, 'manage_options', $this->slug, array( &$this, 'display_page' ) );		
		add_action( 'admin_print_styles-' . $admin_page, array( &$this, 'styles' ) );
		add_action( 'admin_print_scripts-' . $admin_page, array( &$this, 'scripts' ) );
	}
	
	
	/**
	* Validate settings function
	* Returning input by excluding the selected input
	* @params $input
	* @since 1.0
	*/
	function validate_settings( $input ) {
		$options = get_option( $this->slug );
		
		foreach( $this->settings as $key => $val ) {
			if( ! isset( $input[$key] ) && $val['section'] != $input['tab'] ) 
				$input[$key] = $options[$key];
		}
		return $input;
	}
	
} // end class.

$superpost = new Super_Post_Settings();
?>