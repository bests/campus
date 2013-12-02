<?php
/*    
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
add_action( 'sp_meta_tab', 'super_post_premium_tab' );
add_action( 'sp_meta_tab_content', 'super_post_premium_tab_content', 1, 2 );


/**
 * Add additional tab for post meta
 * @since 1.0.0
 */
function super_post_premium_tab( $tabname ) {
	return $tabname + array( 
		'upgrade'	=> __( 'Upgrade', 'super-post' )
	);
}


/**
 * Add additional tab for post meta
 * @since 1.0.0
 */
function super_post_premium_tab_content( $active, $meta ) {
	?>
	<li class="tab-pane <?php echo $active['upgrade']; ?>">
		<ul>
			<li>
				<a href="http://goo.gl/HDhZx"><img class="spimg" src="<?php echo SUPER_POST_URL . 'img/super-post.png'; ?>" alt="" /></a>
				<h4 style="margin-bottom: 3px;"><?php _e( 'Upgrade To Premium Version', 'super-post' ); ?></h4>
				<span class="controlDesc">
					<?php _e( 'This premium version gives more abilities, features, options and premium supports for displaying your posts 
							in a better way. You will get help soon if you have problems with the premium version. Full documentation will let 
							you customize this premium version easily. <br />
							See the full <a href="http://zourbuth.com/plugins/super-post/"><strong>Live Preview</strong></a>.
							<br /><br />
							Main key features you will get with premium version:', 'super-post' ); ?>
				</span>
				
			</li>
			<li>
				<ul>
					<li>
						<strong><?php _e( 'Premium Supports', 'super-post' ) ; ?></strong>
						<span class="controlDesc"><?php _e( 'A premium supports, helps and documentation.', 'super-post' ); ?></span>
					</li>
					<li>
						<strong><?php _e( 'Search Posts', 'super-post' ) ; ?></strong>
						<span class="controlDesc"><?php _e( 'New feature for searching posts with shortcode or widget via Ajax.', 'super-post' ); ?></span>
					</li>								
					<li>
						<strong><?php _e( 'Post Taxonomies', 'super-post' ) ; ?></strong>
						<span class="controlDesc"><?php _e( 'Easy to display your post based on custom taxonomies, eq. posts from portfolio, testimonial, product etc.', 'super-post' ); ?></span>
					</li>
					<li>
						<strong><?php _e( 'Ajax Posts', 'super-post' ) ; ?></strong>
						<span class="controlDesc"><?php _e( 'Load more posts via Ajax + animations.', 'super-post' ); ?></span>
					</li>
					<li>
						<strong><?php _e( 'Advanced Post Shares, Likes, Views and Rating', 'super-post' ) ; ?></strong>
						<span class="controlDesc"><?php _e( 'Shares post via Facebook, Twitter, Google+ or Email.', 'super-post' ); ?></span>
					</li>
					<li>
						<strong><?php _e( 'Shortcode Editor + Widget Shortcode', 'super-post' ) ; ?></strong>
						<span class="controlDesc"><?php _e( 'Button in your post editor. No more writing shortcodes.', 'super-post' ); ?></span>
					</li>
					<li>
						<strong><?php _e( 'Easy Templates', 'super-post' ) ; ?></strong>
						<span class="controlDesc"><?php _e( 'Displaying your posts with more template style options.', 'super-post' ); ?></span>
					</li>
					<li>
						<strong><?php _e( 'Custom Icon', 'super-post' ) ; ?></strong>
						<span class="controlDesc"><?php _e( 'Easy to add another icon for post date and comment.', 'super-post' ); ?></span>
					</li>
					<li>
						<strong><?php _e( 'Default Post Thumbnail', 'super-post' ) ; ?></strong>
						<span class="controlDesc"><?php _e( 'Set a default post thumbnail for posts that do not have a thumbnail or featured image.', 'super-post' ); ?></span>
					</li>
					<li>
						<strong><?php _e( 'And more...', 'super-post' ) ; ?></strong>
						<span class="controlDesc"><?php _e( 'Much more option than the free version.', 'super-post' ); ?></span>
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
	</li><?php
}
?>