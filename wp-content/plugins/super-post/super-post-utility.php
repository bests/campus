<?php
/*
    Super Post Utility
    Author: zourbuth
    Author URI: http://zourbuth.com
    License: GPL2

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


// print the user costum style sheet.
add_action( 'init', 'super_post_load_style');


function super_post_load_style() {
	wp_enqueue_style( 'super-post', SUPER_POST_URL . 'css/super-post.css' );
}


/**
 * Create the post list with custom argumenents, see $defaults for available arguments
 * @param: 	$args array consist of all the variable needed for generating the post list
 * $return	$html
 * @since 1.0
 */
function super_post( $args ) {
	$defaults = array(
		'id' 				=> '',
		'title' 			=> '',
		'query' 			=> 'recent',
		'order' 			=> 'DESC',
		'taxonomy' 			=> array(),
		'items' 			=> 5,
		'offset' 			=> 0,
		'show_excerpt' 		=> true,
		'excerpt_length' 	=> 15,
		'excerpt_more' 		=> '...',
		'show_thumbnail' 	=> true,
		'show_date' 		=> true,
		'date_icon' 		=> SUPER_POST_URL . 'img/date.png',
		'date_format' 		=> get_option( 'date_format' ),
		'show_comments' 	=> true,
		'comment_icon' 		=> SUPER_POST_URL . 'img/comments.png',
		'icon_height' 		=> 40,
		'icon_width' 		=> 40,
		'icon_empty' 		=> SUPER_POST_URL . 'img/thumbnail.png',
		'template'			=> 'left',
		'read_more'			=> '',
		'load_style'		=> 'append',
		'load_text'			=> __('Load more posts', 'super-post')
	);

	/* Merge the user-selected arguments with the defaults. */
	$arg = wp_parse_args( (array) $args, $defaults );
		
	extract($arg, EXTR_SKIP);
	
	$html = '';
	
	$posts = sp_get_posts($arg);
	
	// Ok then, lets we proceed the posts into the template.
	if ( !empty($posts) ) {

		$html .= "<ul class='super-post template-$template'>";

		foreach($posts as $post) {
			$html .= sp_generate_post_list( $post, $arg );
		}
		
		$html.= '</ul>';
	}
	
	return $html;
}


/**
 * @subpackage Functions - Pagination
 * Functions file for handling the page pagination, not the post pagination link.
**/
function super_post_pagination( $pages = '', $range = 5 ) {

	$showitems = ( $range * 2 ) + 1; 

	$paged = 1;

	$html = '';
	if ( 1 != $pages ) {
		
		if( 2 < $paged && $paged > $range + 1 && $showitems < $pages ) 
			$html .= "<a href='#'>" . __('First', 'neupaper') . "</a> ";
			
		if( 1 < $paged && $showitems < $pages ) 
			$html .= "<a href='#'>&lsaquo;</a> ";

		for ( $i = 1; $i <= $pages; $i++ ) {
			if ( 1 != $pages && ( !( $i >= $paged + $range + 1 || $i <= $paged - $range - 1 ) || $pages <= $showitems ) ) {
				$html .= ( $paged == $i ) ? "<span class='sp-paging'>". $i ."</span> " : "<a href='#' class='sp-paging sp-inactive' ><span>" . $i ."</span></a> ";
			}
		}

		if ( $paged < $pages && $showitems < $pages )
			$html .= "<a href='#'>&rsaquo;</a> ";
			
		if ( $paged < $pages - 1 && $paged + $range - 1 < $pages && $showitems < $pages )
			$html .= "<a href='#'>" . __('Last', 'neupaper') . "</a> ";
	}
	
	return $html;
}


/**
 * Load the posts request via ajax or normal requests
 * @param: 	$args see defaults for more details
 & $return	$html
 * @since 1.0.0
 */
function sp_generate_post_list( $post, $args ){

	extract( $args, EXTR_SKIP );
	
	$html = '';
	$permalink  = get_permalink( $post->ID );
	
	$liStyle = $show_thumbnail && !$icon_empty && has_post_thumbnail( $post->ID ) ? 'style="min-height: ' . ( $icon_height + 5 ) . 'px;"' : '';
	$html .= "<li $liStyle>";
		
		if ( $show_thumbnail ) {
			
			if ( has_post_thumbnail( $post->ID ) ) {
				
				$image_id = get_post_thumbnail_id( $post->ID );
				$image_url = sp_thumbnail($image_id, $icon_width, $icon_height );				
				
				global $blog_id;
				if (isset($blog_id) && $blog_id > 0) {
					$imageParts = explode('/super-post/files/', $image_url);
					if (isset($imageParts[1])) {
						$image_url = '/blogs.dir/' . $blog_id . '/files/' . $imageParts[1];
					}
				}				
				
				$html .= '<div class="sp-thumbnail-wrapper" style="height:' . $icon_height . 'px; width:' . $icon_width . 'px;">';
				
				// Change the Timthumb below
				$html .= '<a href="' . get_permalink( $post->ID ) . '">';
				$html .= "<img class='sp-thumbnail' src='$image_url' alt='{$post->post_title}' />";
				$html .= '</a>';
				
				$html .= '</div>';				
			} else {
				if ( $icon_empty ) {
					$image_url = $icon_empty;
					
					$html .= '<div class="sp-thumbnail-wrapper" style="height:' . $icon_height . 'px; width:' . $icon_width . 'px;">';
					
					// Change the Timthumb below
					$html .= '<a href="' . get_permalink( $post->ID ) . '">';
					$html .= '<img class="sp-thumbnail" src="' . $image_url . '" alt="' . $post->post_title . '" />';
					$html .= '</a>';
					
					$html .= '</div>';					
				}
			}
		}
		
		if ( $template != 'block'  ) {
			// Check if icon empty exist or not
			if ( $show_thumbnail ) {
				if ( has_post_thumbnail( $post->ID ) ) {
					$html .= "<div class='sp-post-info' style='padding-$template: " . ( $icon_width + 10 ) . "px;'>";
				} else {
					if ( $icon_empty ) {
						$html .= "<div class='sp-post-info' style='padding-$template: " . ( $icon_width + 10 ) . "px;'>";			
					} else {
						$html .= "<div class='sp-post-info'>";	
					}
				}
			} else {			
				$html .= "<div class='sp-post-info'>";
			}
		} else {
			$html .= "<div class='sp-post-info'>";
		}
		
		// check if post have title
		$ptitle = $post->post_title ? $post->post_title : __('No title', 'super-post');
		$custom_link = apply_filters( 'sp_permalink', $permalink, $post->ID );
		$html .= "<a class='sp-title' href='$custom_link'>$ptitle</a>";
		
		/* 
		$terms = get_the_terms( $post->ID, 'category' );     
		if ( !empty( $terms ) ) {
			$out = array();
			foreach ( $terms as $term )
				$out[] = '<a href="' .get_term_link($term->slug, 'category') .'">'.$term->name.'</a>';
				
			$return_html .= join( ', ', $out );
		};		
		*/		
		
		if ( $show_date ) {
			$date_icon_class = $date_icon ? '' : ' sp-noicon';
			$html .= '<a class="sp-date' . $date_icon_class .'" href="' . get_permalink( $post->ID ). '">' . mysql2date( $date_format , $post->post_date, false) . '</a>';					
		}
		
		if ( $show_comments ) {
			$comment_icon_class = $comment_icon ? '' : ' sp-noicon';
			$html .= '<a class="sp-comment' . $comment_icon_class . '" href="' . get_comments_link(  $post->ID ) . '">' . get_comments_number( $post->ID ) . '</a>';
		}
		
		if ( $show_author ) {
			$post_author = get_userdata( $post->post_author );
			$post_author_url = get_author_posts_url( $post->post_author );
			$author_title = str_replace('{author}', $post_author->display_name, $author_title);
			$html .= '<a class="sp-author url fn n" href="' . $post_author_url . '" title="' . $author_title . '">' . $post_author->display_name . '</a> ';
		}		
		
		if ( $show_excerpt )
			$html .= '<p class="sp-excerpt">' . super_post_excerpt( $post->ID, $excerpt_length, $excerpt_more, $read_more ) . '</p>';	
		
		$html .= '</div>';
	$html .= '</li>';
	
	return $html;
}


/**
 * Load the posts request via ajax or normal requests
 * @param: 	$args see defaults for more details
 & $return	$html
 * @since 1.0.0
 */
function sp_get_posts($args){

	extract( $args, EXTR_SKIP );
	$posts = array();
	if ($query == 'recent') {
		$q = array(
			'numberposts'	=> $items,
			'offset'		=> $offset, 
			'order'			=> $order,
			'orderby'		=> 'post_date',
			'post_type'		=> 'post'
		);
	} elseif ( $query == 'sticky' ) {	
		$q = array(
			'post_type'	=> 'post',
			'post__in' 	=> get_option('sticky_posts'), 
			'orderby' 	=> 'post_date',
			'showposts' => $items, 
			'order' 	=> $order
		); 			
	} elseif ($query == 'popular') {		
		$q = array(
			'post_type'	=> 'post',
			'orderby' 	=> 'comment_count', 
			'showposts' => $items, 
			'order' 	=> $order, 
		); 			
	} elseif ($query == 'pingbacked') {
		/* on demand */
	
	} elseif ($query == 'related') {

		// Get the current post id and post type
		$current_post_id = ! isset( $args['cur_id'] ) ? get_the_ID() : $args['cur_id'];
			
		$current_post_type = get_post_type( $current_post_id );		
		
		// Get current post taxonomies
		$post_type_taxs = get_object_taxonomies( $current_post_type );
		
		// For default post type, give priority to post tag.
		// If there is no tags, get the post category
		if( 'post' == $current_post_type )
			$post_type_taxs = array( 'post_tag', 'category' );
		
		// Ok there, let's loop through all taxonomies
		// Get the terms for taxonomy, if not found loop to other taxonomy
		foreach( $post_type_taxs as $tax ) {
			$terms = wp_get_post_terms( $current_post_id, $tax, array( 'fields' => 'ids' ) );
			$cur_tax = $tax;
			if( ! empty( $terms ) )
				break;
		}

		// Build the query, set to recent posts if no terms
		if( ! empty( $terms ) && 'page' != $current_post_type ) {
			$terms = implode(',', $terms);
			$q = array(
				'post_type'		=> 'post',
				'order'			=> $order,
				'orderby'		=> 'post_date',
				'numberposts' 	=> $items,
				'post__not_in' => array( $current_post_id ),
				'tax_query' => array(
					array(
						'taxonomy' => $cur_tax,
						'field' => 'id',
						'terms' => array( $terms ),
						'operator' => 'IN'
					)
				)
			);
		} else {
			$q = array(
				'numberposts'	=> $items,
				'order'			=> $order,
				'orderby'		=> 'post_date',
				'post_type'		=> 'post'
			);
		}
	
	} else {
		
		$taxonomies = get_taxonomies( array( 'show_tagcloud' => true ), 'objects' );
		
		if ( ! empty( $taxonomy ) && array_key_exists( $args['query'], $taxonomies ) ) {
		
			$q = array(
				'order' => $order,
				'orderby' => 'date',
				'posts_per_page' => $items,
				'offset' => $offset,
				'post_status' => 'publish',	
				'tax_query' => array(
					array(
						'taxonomy' => $args['query'],
						'field' => 'id',
						'terms' => $taxonomy,
						'operator' => 'IN'
					)
				)
			); 		
		}
	}
	
	$posts = get_posts( $q );
	
	if( $posts )
		return $posts;
	else
		return false;
}


/**
 * Load the posts request via ajax 
 * @param see defaults for more details
 * $return	$html
 * @since 1.0.0
 */
function super_post_widget_ajax() {
	// Check the nonce and if not isset the id, just die. Not best, but maybe better for avoid errors
	$nonce = $_POST['nonce'];
	if ( ! wp_verify_nonce( $nonce, 'super-post' ) && ! isset($_POST['id']) )
		die();
		
	$options 		= get_option('widget_super-post');
	$args 	 		= $options[ (int) $_POST['id'] ];
	$args['offset']	= (int) $_POST['offset'];

	$posts = sp_get_posts($args);
	
	// Ok then, lets we proceed the posts into the template.
	if ( ! empty( $posts ) ) {
		$html = '';
		foreach( $posts as $post ) {
			$html .= sp_generate_post_list( $post, $args );
		}
	}
	
	echo $html;	
	
	exit;
}


/**
 * Get the super post excerpt with
 * @param: 	$post_id is the id for the post
 * 			$excerpt_length the total spaces ' ' in the excerpt
 * 			$excerpt_more additional text after the excerpt
 * 			$read_more additional text for link to the post
 & $return	$the_excerpt
 * @since 1.0.0
 */
function super_post_excerpt($post_id, $excerpt_length = 15, $excerpt_more = '...', $read_more = ''){
    $the_post = get_post( $post_id ); // Gets post ID
    $the_excerpt = $the_post->post_content; //Gets post_content to be used as a basis for the excerpt
    $the_excerpt = strip_tags(strip_shortcodes($the_excerpt)); //Strips tags and images
    $words = explode(' ', $the_excerpt, $excerpt_length + 1);

    if(count($words) > $excerpt_length) :
        array_pop($words);
        array_push($words, $excerpt_more);
        $the_excerpt = implode(' ', $words);
    endif;
	
	$read_more = $read_more ? " <a class='sp-more' href='" . get_permalink( $post_id ). "'>$read_more</a>" : '';

    return $the_excerpt . $read_more;
}


function super_post_short_title( $title, $after = '', $length = 8 ) {
	$title = explode(' ', $title, $length);
	if (count( $title)>=$length ) {
		array_pop($title);
		$title = implode(" ",$title). $after;
	} else {
		$title = implode(" ",$title);
	}
	return $title;
}


/**
 * Extracting the custom post shortcode inline.
 * @params $atts, see shortcode attributs for custom variables
 * @since 1.0.0
 */
function super_post_extract_attr( $atts ) {
	if ( is_array($atts) )
		foreach ( $atts as $att )
			$style .= ' ' . $att;

	return $style;
}


/**
 * Function to generate new image size if not available
 * @params $image_id, the attachment image ID
 * @params $size, image size name 
 * @params $width and $height, crop new image with the sizes
 * @since 1.0.6
 */
function sp_thumbnail($image_id, $width, $height) {
	if ( ! is_array( $imagedata = wp_get_attachment_metadata( $image_id ) ) )
		return false;
	
	$size = "$width-$height";
	
	if ( ! isset( $imagedata['sizes'][$size] ) ) {		
		global $_wp_additional_image_sizes;
		$_wp_additional_image_sizes["$width-$height"] = array( 'width' => $width, 'height' => $height, 'crop' => true );
		require_once(ABSPATH . 'wp-admin/includes/image.php');
		$fullsizepath = get_attached_file( $image_id );
		$metadata = wp_generate_attachment_metadata( $image_id, $fullsizepath );
		wp_update_attachment_metadata( $image_id, $metadata );
	}
	
	$img_src = wp_get_attachment_image_src( $image_id, $size, true );
	return $img_src[0];	
}
?>