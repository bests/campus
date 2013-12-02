<?php
/**
 * @package Super_Simple_Related_posts
 * @version 1.2
 */

/**
 * Plugin Name: Super Simple Related Posts
 * Plugin URI:  http://mightyminnow.com
 * Description: A super simple plugin to output related posts based on categories, tags, or custom taxonomies.
 * Version:     1.2
 * Author:      MIGHTYminnow
 * Author URI:  http://mightyminnow.com
 * License:     GPLv2+
 * Text Domain: ssrp
 * Domain Path: /languages/
 * 
 * Coded By: Mickey Kay
 *
 */

/**
 * @todo  Add shortcode output
 */

// Make sure we don't expose any info if called directly
if ( !function_exists( 'add_action' ) ) {
    _e('Hi there!  I\'m just a plugin, not much I can do when called directly.', 'ssrp');
    exit;
}

// Useful global constants
define( 'SSRP_VERSION', '1.2' );
define( 'SSRP_URL',     plugin_dir_url( __FILE__ ) );
define( 'SSRP_PATH',    dirname( __FILE__ ) . '/' );

/**
 * Calls initializing function and flushes rewrite rules on plugin activation
 *
 * @package Super_Simple_Related_Posts
 * @since   1.0
 */
function ssrp_activate() {
    // First load the init scripts
    ssrp_init();

    flush_rewrite_rules();
}
register_activation_hook( __FILE__, 'ssrp_activate' );

/**
 * Flushes rewrite rules on plugin deactivation
 *
 * @package Super_Simple_Related_Posts
 * @since   1.0
 */
function ssrp_deactivate() {
    flush_rewrite_rules();
}
register_deactivation_hook( __FILE__, 'ssrp_deactivate' );

/**
 * Registers widget and loads plugin text domain
 *
 * @package Super_Simple_Related_Posts
 * @since   1.0
 */
function ssrp_init() {
    // Register widget
    register_widget( 'SSRP_Widget');

    // Load plugin text domain
    load_plugin_textdomain( 'ssrp', false, dirname( plugin_basename( __FILE__ ) ) . '/languages/' );
}
add_action( 'widgets_init', 'ssrp_init' );

/**
 * The main Super Simple Related Posts Widget class
 *
 * @package Super_Simple_Related_Posts
 * @since   1.0
 */
class SSRP_Widget extends WP_Widget {

    /**
     * The default widget values, which is populated in __contstruct
     * 
     * @var array
     */
    public $defaults = array();
    
    /**
     * Optional array of taxonomy slugs to limit choices
     *
     * e.g. public $included_taxonomies = array('category', 'post_tag');
     * Only 'Category' and 'Tag' will appear in the widget 'Show posts related by:' drop-down <select>
     * 
     * @var array
     */
    public $included_taxonomies = array();
    
    /**
     * PHP5 constructor - initializes widget and sets defaults
     *
     * @package Super_Simple_Related_Posts
     * @since   1.0
     */
    public function __construct() {
        // Set widget options
        $widget_options = array(
            'classname' => 'ssrp',
            'description' => __('A list of posts related to the current post/page.', 'ssrp') );
            parent::WP_Widget('ssrp', __('Super Simple Related Posts', 'ssrp'), $widget_options
        );

        // Set widget defaults
        $this->defaults = array(
            'title'                   => '',
            'hide_title'              => '',
            'post_types'              => '',
            'taxonomy'                => 'category',
            'orderby'                 => 'date',
            'order'                   => 'DESC',
            'number_of_posts'         => -1,
            'no_posts_action'         => 'hide',
            'no_posts_message'        => __('No posts found', 'ssrp'),
            'post_heading_links'      => '',
            'hide_post_type_headings' => '',
            'term_heading_links'      => '',
            'hide_term_headings'      => '',
            'before_HTML'             => '',
            'after_HTML'              => '',
        );

        // Add shortcode
        add_shortcode( 'ssrp', array( $this, 'ssrp_shortcode' ) );
    }
    
    /**
     * Output the widget settings form
     *
     * @package Super_Simple_Related_Posts
     * @since   1.0
     *
     * @param   array $instance the current widget settings
     */
    public function form( $instance ) {
        // Map all undefined $instance properties to defaults
        $instance = wp_parse_args( (array) $instance, $this->defaults );

        // Output form HTML below
        ?>
        
        <!-- Title -->
        <p>  
            <label for="<?php echo $this->get_field_id( 'title' ); ?>"><?php _e('Title:', 'ssrp'); ?></label>  
            <input type="text" id="<?php echo $this->get_field_id( 'title' ); ?>" name="<?php echo $this->get_field_name( 'title' ); ?>" value="<?php echo $instance['title']; ?>" class="widefat" />
        </p>
        <p>
            <input type="checkbox" value="1" <?php checked( 1 == $instance['hide_title'] ); ?> id="<?php echo $this->get_field_id( 'hide_title' ); ?>" name="<?php echo $this->get_field_name( 'hide_title' ); ?>">
            <label for="<?php echo $this->get_field_id( 'hide_title' ); ?>"><?php _e('Hide the widget title', 'ssrp'); ?></label>
        </p>

        <!-- Post Types -->
        <p>
            <label for="<?php echo $this->get_field_id( 'post_types' ); ?>"><?php _e('Post types to include (by slug):', 'ssrp'); ?></label>  
            <input type="text" id="<?php echo $this->get_field_id( 'post_types' ); ?>" name="<?php echo $this->get_field_name( 'post_types' ); ?>" value="<?php echo esc_attr( $instance['post_types'] ); ?>" class="widefat" placeholder="<?php _e('e.g. post, page, faq', 'ssrp'); ?>" />
            <small><?php _e('Comma separated, ordered list of post type slugs. Available post type slugs:', 'ssrp'); ?> <code><?php echo implode( "</code>, <code>", get_post_types() ); ?></code></small>
        </p>

        <!-- Taxonomy -->
        <p>
            <label for="<?php echo $this->get_field_id( 'taxonomy' ); ?>"><?php _e('Show posts related by:', 'ssrp'); ?></label>  
            <select id="<?php echo $this->get_field_id( 'taxonomy' ); ?>" name="<?php echo $this->get_field_name( 'taxonomy' ); ?>" class="widefat">
                <?php

                // Get all registered taxonomies
                $taxonomies = get_taxonomies('', 'objects');
                
                // Output an <option> for each taxonomy
                foreach ( $taxonomies as $taxonomy ) {
                    // Get taxonomy name and slug
                    $tax_name = $taxonomy->labels->singular_name;
                    $tax_slug = $taxonomy->name;

                    /**
                     * Output taxonomy:
                     * 1. if the $included_taxonomies[] is empty (which will display all taxonomies)
                     * 2. this taxonomy is set to be included in $this->included_taxonomies[]
                     */
                    if ( empty($this->included_taxonomies) || in_array( $tax_slug, $this->included_taxonomies ) )
                        echo '<option value="' . $tax_slug . '" ' . selected( $instance['taxonomy'], $tax_slug ) . '>' . $tax_name . '</option>' . "\n";
                }
                ?>
            </select>
        </p>

        <!-- Orderby -->
        <p>
            <label for="<?php echo $this->get_field_id( 'orderby' ); ?>"><?php _e('Order posts by:', 'ssrp'); ?></label>  
            <select id="<?php echo $this->get_field_id( 'orderby' ); ?>" name="<?php echo $this->get_field_name( 'orderby' ); ?>" class="widefat">
                <?php
                    // Array of orderby values
                    $orderby_values = array(
                        'title'         => __('Title', 'ssrp'),
                        'name'          => __('Post Slug', 'ssrp'),
                        'date'          => __('Date Created', 'ssrp'),
                        'modified'      => __('Date Modified', 'ssrp'),
                        'menu_order'    => __('Menu Order', 'ssrp'),
                        'author'        => __('Author', 'ssrp'),
                        'ID'            => __('Post ID', 'ssrp'),
                        'parent'        => __('Parent ID', 'ssrp'), 
                        'rand'          => __('Random', 'ssrp'), 
                        'comment_count' => __('Comment Count', 'ssrp'),
                    );

                    // Output <option> for each $orderby_value
                    foreach ( $orderby_values as $orderby_value => $orderby_name ) {
                        echo '<option value="' . $orderby_value . '" ' . selected( $instance['orderby'], $orderby_value ) . '>' . $orderby_name . '</option>' . "\n";
                    }
                ?>
            </select>
        </p>

        <!-- Order -->
        <p>
            <label for="<?php echo $this->get_field_id( 'order' ); ?>"><?php _e('Order:', 'ssrp'); ?></label>  
            <select id="<?php echo $this->get_field_id( 'order' ); ?>" name="<?php echo $this->get_field_name( 'order' ); ?>" class="widefat">
                <?php
                    // Array of order values
                    $order_values = array(
                        'ASC'  => __('Ascending', 'ssrp'),
                        'DESC' => __('Descending', 'ssrp'),
                    );

                    // Output <option> for each $order_value
                    foreach ( $order_values as $order_value => $order_name ) {
                        echo '<option value="' . $order_value . '" ' . selected( $instance['order'], $order_value ) . '>' . $order_name . '</option>' . "\n";
                    }
                ?>
            </select>
        </p>

        <!-- Number of Posts -->
        <p>
            <label for="<?php echo $this->get_field_id( 'number_of_posts' ); ?>"><?php _e('Number of posts to show:', 'ssrp'); ?></label>  
            <select id="<?php echo $this->get_field_id( 'number_of_posts' ); ?>" name="<?php echo $this->get_field_name( 'number_of_posts' ); ?>" class="widefat">
                <?php
                    // First, output the <option> for unlimited
                    echo '<option value="-1" ' . selected( $instance['order'], $order_value ) . '>Unlimited</option>' . "\n";

                    // Output <option>'s for the numbers 1-100
                    for ( $i = 1; $i <= 100; $i++ ) {
                        echo '<option value="' . $i . '" ' . selected( $instance['number_of_posts'], $i ) . '>' . $i . '</option>' . "\n";
                    }
                ?>
            </select>
        </p>

        <!-- No Posts Found -->
        <p>
            <?php _e('If there are no related posts:', 'ssrp') ?><br />
            <input type="radio" value="hide" <?php checked( 'hide' == $instance['no_posts_action'] ); ?> id="<?php echo $this->get_field_id( 'no_posts_action' ); ?>-hide" name="<?php echo $this->get_field_name( 'no_posts_action' ); ?>">
            <label for="<?php echo $this->get_field_id( 'no_posts_action' ); ?>-hide"><?php _e('Hide the content/widget', 'ssrp'); ?></label><br />
            <input type="radio" value="message" <?php checked( 'message' == $instance['no_posts_action'] ); ?> id="<?php echo $this->get_field_id( 'no_posts_action' ); ?>-message" name="<?php echo $this->get_field_name( 'no_posts_action' ); ?>">
            <label for="<?php echo $this->get_field_id( 'no_posts_action' ); ?>-message"><?php _e('Show the following message:', 'ssrp'); ?></label>
            <input type="text" id="<?php echo $this->get_field_id( 'no_posts_message' ); ?>" name="<?php echo $this->get_field_name( 'no_posts_message' ); ?>" value="<?php echo esc_attr( $instance['no_posts_message'] ); ?>" class="widefat" />
        </p>

        <!-- Post Type Headings -->
        <p>
            <?php _e('Headings (post types):', 'ssrp') ?><br />
            <input type="checkbox" value="1" <?php checked( 1 == $instance['post_heading_links'] ); ?> id="<?php echo $this->get_field_id( 'post_heading_links' ); ?>" name="<?php echo $this->get_field_name( 'post_heading_links' ); ?>">
            <label for="<?php echo $this->get_field_id( 'post_heading_links' ); ?>"><?php _e('Link post type headings', 'ssrp'); ?></label><br />
            <input type="checkbox" value="1" <?php checked( 1 == $instance['hide_post_type_headings'] ); ?> id="<?php echo $this->get_field_id( 'hide_post_type_headings' ); ?>" name="<?php echo $this->get_field_name( 'hide_post_type_headings' ); ?>">
            <label for="<?php echo $this->get_field_id( 'hide_post_type_headings' ); ?>"><?php _e('Hide post type headings', 'ssrp'); ?></label><br />
        </p>

        <!-- Taxonomy Term Headings -->
        <p>
            <?php _e('Subheadings (taxonomy terms):', 'ssrp') ?><br />
            <input type="checkbox" value="1" <?php checked( 1 == $instance['term_heading_links'] ); ?> id="<?php echo $this->get_field_id( 'term_heading_links' ); ?>" name="<?php echo $this->get_field_name( 'term_heading_links' ); ?>">
            <label for="<?php echo $this->get_field_id( 'term_heading_links' ); ?>"><?php _e('Link taxonomy term subheadings', 'ssrp'); ?></label><br />
            <input type="checkbox" value="1" <?php checked( 1 == $instance['hide_term_headings'] ); ?> id="<?php echo $this->get_field_id( 'hide_term_headings' ); ?>" name="<?php echo $this->get_field_name( 'hide_term_headings' ); ?>">
            <label for="<?php echo $this->get_field_id( 'hide_term_headings' ); ?>"><?php _e('Hide taxonomy term subheadings', 'ssrp'); ?></label>  
        </p>

        <!-- Before Widget -->
        <p>
            <label for="<?php echo $this->get_field_id( 'before_HTML' ); ?>"><?php _e('HTML before widget:', 'ssrp'); ?></label>  
            <textarea id="<?php echo $this->get_field_id( 'before_HTML' ); ?>" name="<?php echo $this->get_field_name( 'before_HTML' ); ?>" class="widefat" rows="6" placeholder="<?php esc_html_e('e.g. <p>Intro text goes here&hellip;</p>', 'ssrp'); ?>"><?php echo esc_textarea( $instance['before_HTML'] ); ?></textarea>
        </p>

        <!-- After Widget -->
        <p>
            <label for="<?php echo $this->get_field_id( 'after_HTML' ); ?>"><?php _e('HTML after widget:', 'ssrp'); ?></label>  
            <textarea id="<?php echo $this->get_field_id( 'after_HTML' ); ?>" name="<?php echo $this->get_field_name( 'after_HTML' ); ?>" class="widefat" rows="6" placeholder="<?php esc_html_e('e.g. <a href="#">Learn more &raquo;</a>', 'ssrp'); ?>"><?php echo esc_textarea( $instance['after_HTML'] ); ?></textarea>
        </p>
    <?php
    }
    
    /**
     * Sanitize and update widget form values
     *
     * @package Super_Simple_Related_Posts
     * @since   1.0
     *
     * @param   array $new_instance the old widget settings
     * @param   array $old_instance the new widget settings
     */
    public function update( $new_instance, $old_instance ) {
        // Sanitize title
        $new_instance['title'] = ( ! empty( $new_instance['title'] ) ) ? strip_tags( $new_instance['title'] ) : '';

        // Sanitize title
        $new_instance['no_posts_message'] = ( ! empty( $new_instance['no_posts_message'] ) ) ? wp_kses( $new_instance['no_posts_message'] ) : '';

        // Sanitize before_HTML & after_HTML
        if ( current_user_can('unfiltered_html') ) {
            $new_instance['before_HTML'] = $new_instance['before_HTML'];
            $new_instance['after_HTML'] = $new_instance['after_HTML'];
        } else {
            $new_instance['before_HTML'] = stripslashes( wp_filter_post_kses( addslashes($new_instance['before_HTML']) ) ); // wp_filter_post_kses() expects slashed
            $new_instance['after_HTML'] = stripslashes( wp_filter_post_kses( addslashes($new_instance['after_HTML']) ) ); // wp_filter_post_kses() expects slashed
        }

        return $new_instance;
    }
    
    /**
     * Output the widget contents
     *
     * @package Super_Simple_Related_Posts
     * @since   1.0
     *
     * @param   array $args widget display arguments including before_title, after_title, before_widget, and after_widget
     * @param   array $instance the widget settings
     */
    public function widget( $args, $instance ) {

        // Exits if we're not looking at a single post/page
        if ( !is_singular() || empty( $instance['post_types'] ) )
            return;

        // Get widget $args as variables
        extract( $args );

        // Initialize main output string
        $output = '';

        // Get the widget title, taxonomy, and post types to be included
        $title = ( $instance['title'] ? $instance['title'] : __('Related Posts', 'ssrp') );
        $taxonomy = $instance['taxonomy'];
        $post_types = explode( ',', str_replace( ' ', '', $instance['post_types'] ) );

        // Get the taxonomy terms of the current post/page for the specified taxonomy
        $post_terms = wp_get_post_terms( get_the_ID(), $taxonomy );

        // Loop through each post type and generate output
        foreach ( $post_types as $post_type ) {
            // Initialize term output 
            $term_output = '';

            // Loop through the taxonomy terms of the current post
            foreach ( $post_terms as $term_object ) {
                // Get the ID of the term object
                $term_id = $term_object->term_id;

                // Set the arguments for the following get_posts() call
                $args = array(
                    'posts_per_page'            => $instance['number_of_posts'],
                    'orderby'                   => $instance['orderby'],
                    'order'                     => $instance['order'],
                    'post_type'                 => $post_type,
                    'exclude'                   => get_the_ID(), /* Exclude the current page from these lists */
                    'tax_query' => array(
                        array(
                            'taxonomy' => $taxonomy,
                            'field' => 'id',
                            'terms' => $term_id,
                            )
                        )          
                    );

                // Get all posts of the current post type that have the current taxonomy term
                $posts = get_posts( $args );

                // Initialize post output
                $post_link = '';
                $post_output = '';

                // Output posts for this post type and taxonomy
                foreach ( $posts as $post ) {
                    // Apply the_title and ssrp_post_title filters (within <a> tag)
                    $post_link = apply_filters( 'the_title', $post->post_title, $post->ID);
                    $post_link = apply_filters( 'ssrp_post_title', $post_link, $post->ID);

                    // Add actual link
                    $post_link = '<a href="' . get_permalink($post->ID) . '">' . $post_link . '</a>';
                    
                    // Apply ssrp_post_link (outside of <a> tag)
                    $post_output .= '<li>' . apply_filters( 'ssrp_post_link', $post_link, $post->ID ) . '</li>'; 
                }

                // Only output the term heading if some related posts exist within it
                if ( !empty( $post_output ) || 'message' == $instance['no_posts_action'] ) {
                   
                    // Only output the term heading if the widget box to hide it is unchecked
                    if ( !isset( $instance['hide_term_headings'] ) || 1 != $instance['hide_term_headings'] ) {
                        $term_output .= '<h5>';

                        // Output post type headings as links if specified
                        if ( isset( $instance['term_heading_links'] ) && 1 == $instance['term_heading_links'] ) 
                            $term_output .= '<a href="' . get_term_link( get_term_by('id', $term_id, $taxonomy) ) . '">';
                    
                        // Output taxonomy term heading text  
                        $term_output .= apply_filters( 'ssrp_taxonomy_term_heading', $term_object->name, $term_object );

                        // Close taxonomy term heading link if specified
                        if ( isset( $instance['term_heading_links'] ) && 1 == $instance['term_heading_links'] )
                            $term_output .= '</a>';

                        $term_output .= '</h5>';
                    }

                    // If there are no related posts
                    if ( empty( $post_output ) )
                        $post_output = '<div class="no-posts-message">' . $instance['no_posts_message'] . '</div>';

                    // Output the posts list and apply ssrp_posts_list filter
                    $term_output .= '<ul>' . apply_filters( 'ssrp_posts_list', $post_output, get_post_type_object($post_type), $term_object ) . '</ul>';

                }

            } /* End terms loop */

            // Only output the post type heading if some related posts exist within it
            if ( !empty($term_output) ) {
                // Open the container div
                $output .= '<div class="post-type-' . $post_type . '">';
                
                // Only output the post type heading if the widget box to hide it is unchecked
                if ( !isset( $instance['hide_post_type_headings'] ) || 1 != $instance['hide_post_type_headings'] ) {
                    $output .= '<h4>';

                    // Output post type headings as links if specified, requires 2 different treatments for regular and custom posts
                    if ( isset( $instance['post_heading_links'] ) && 1 == $instance['post_heading_links'] ) {
                        
                        // 1. Regular posts - get_post_type_archive_link() doesn't work for regular posts, so get the URL manually
                        if ( 'post' == $post_type ) {
                            
                            // If homepage is NOT set to be posts/blog page
                            if ( 'page' == get_option('show_on_front') )
                                $archive_link = get_permalink( get_option('page_for_posts' ) );

                            // If homepage IS set to be posts/blog page
                            else 
                                $archive_link = get_bloginfo('url');
                        }

                        // 2. Custom post types - get_post_type_archive_link() works just fine
                        else
                            $archive_link = get_post_type_archive_link( $post_type );

                        // Output actual link
                        $output .= '<a href="' . $archive_link . '">';

                    }
                    
                    // Output post type heading text and apply ssrp_post_type_heading filter
                    $output .= apply_filters( 'ssrp_post_type_heading', get_post_type_object($post_type)->labels->name, get_post_type_object($post_type) );
               
                    // Close post type heading link if need be
                    if ( isset( $instance['post_heading_links'] ) && 1 == $instance['post_heading_links'] )
                        $output .= '</a>';

                    $output .= '</h4>';

                }

                // Close the post type container div
                $output .= $term_output . '</div>';

            }

        } /* End post type loop */

        // If there are related posts or widget is set to display $no_posts_message 
        if ( !empty( $output ) || 'message' == $instance['no_posts_action'] ) {
           
            // If there are no related posts, output the $no_posts_message
            if ( empty( $output ) )
                $output = '<div class="no-posts-message">' . $instance['no_posts_message'] . '</div>';

            // Apply widget_title and widget_text filters
            $widget_title = $before_title . apply_filters( 'widget_title', $title, $instance, $this->id_base ) . $after_title;
            $widget_text = apply_filters( 'widget_text', $output, $instance, $this->id_base );

            // Get before_HTML and after_HTML
            $before_HTML = '<div class="ssrp-before">' . $instance['before_HTML'] . '</div>';
            $after_HTML = '<div class="ssrp-after">' . $instance['after_HTML'] . '</div>';

            // Don't show widget title if "hide title" setting is checked
            if ( !empty( $instance['hide_title'] ) )
                $widget_title = '';
            


            echo $before_widget . $widget_title .'<div class="textwidget">' . $before_HTML . $widget_text . $after_HTML . '</div>' . $after_widget;
        }
    }

    /**
     * Output content for shortcode
     *
     * @package Super_Simple_Related_Posts
     * @since   1.2
     *
     * @param   array $atts shortcode atts to be used in the $instance parameter
     */
    public function ssrp_shortcode( $atts ) {

        $instance = wp_parse_args( $atts, $this->defaults );

        the_widget( 'SSRP_Widget', $instance ); 

    }
}