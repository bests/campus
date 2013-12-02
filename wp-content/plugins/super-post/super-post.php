<?php
/*
    Plugin Name: Super Post
    Plugin URI: http://zourbuth.com/?p=862
    Description: A complete posts and custom posts processing for your content, sidebar or even template files for displaying it in a better way. With highly customization and easy to use, it only takes seconds to create your posts or custom post list.
    Version: 1.0.7
    Author: zourbuth
    Author URI: http://zourbuth.com
    License: GPLv2 or later

	Copyright 2013 zourbuth.com (email : zourbuth@gmail.com)

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


// Launch the plugin
add_action( 'plugins_loaded', 'super_post_plugin_loaded' );


/**
 * Initializes the plugin and it's features with the 'plugins_loaded' action
 * Creating custom constan variable and load necessary file for this plugin
 * Attach the widget on plugin load
 * @since 1.0
 */
function super_post_plugin_loaded() {

	// Set constant variable
	define( 'SUPER_POST_VERSION', '1.0.7' );
	define( 'SUPER_POST_DIR', plugin_dir_path( __FILE__ ) );
	define( 'SUPER_POST_URL', plugin_dir_url( __FILE__ ) );
	
	// Load widget file
	require_once( SUPER_POST_DIR . 'super-post.php' );
	require_once( SUPER_POST_DIR . 'super-post-settings.php' );
	require_once( SUPER_POST_DIR . 'super-post-utility.php' );
	require_once( SUPER_POST_DIR . 'super-post-shortcode.php' );
	require_once( SUPER_POST_DIR . 'super-post-premium.php' );
	
	require_once( SUPER_POST_DIR . 'super-post-share.php' );
	require_once( SUPER_POST_DIR . 'super-post-meta.php' );
	
	$sp_metas = new Super_Post_Meta();
	$sp_share = new Super_Post_Share();	
	
	add_action( 'widgets_init', 'super_post_widgets_init' );
}


/**
 * Register widget
 * @since 1.0.0
 */
function super_post_widgets_init() {
	require_once( SUPER_POST_DIR . 'super-post-widget.php' );
	register_widget( 'Super_Post_Widget_Class' );
}
?>