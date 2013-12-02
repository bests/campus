<?php
/*
    Super Post Shortcode
    http://zourbuth.com/plugins/super-post
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


/**
 * The plugin shortcode uses the add_shortcode function
 * Overwrite the shortcode from string 'true' to boolean
 * @since 1.0.0
 */	
function super_post_sc($atts, $content) {
	
	$atts['show_excerpt'] 	= 'true' == $atts['show_excerpt'] ? 1 : 0;
	$atts['show_thumbnail'] = 'true' == $atts['show_thumbnail'] ? 1 : 0;
	$atts['show_date'] 		= 'true' == $atts['show_date'] ? 1 : 0;
	$atts['show_comments'] 	= 'true' == $atts['show_comments'] ? 1 : 0;
	$atts['show_author'] 	= 'true' == $atts['show_author'] ? 1 : 0;
		
	$return_html = super_post($atts);

	return $return_html;
}
add_shortcode( 'super-post', 'super_post_sc' );
?>