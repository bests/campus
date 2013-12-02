<?php
/*
	Super Post Share
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

class Super_Post_Share {

	/**
	 * Class constructor
	 * @return void
	 * @since 1.5
	**/
	function __construct() {
		add_action( 'wp_head', array( &$this, 'head_enqueue' ), 1 );
		add_action( 'wp_footer', array( &$this, 'footer_html' ), 2 );
		
		add_action('wp_ajax_get_google_plus_one', array( &$this, 'get_google_plus_one' ) );
		add_action('wp_ajax_nopriv_get_google_plus_one', array( &$this, 'get_google_plus_one' ) );
		
		add_action('wp_ajax_send_email', array( &$this, 'send_email' ) );
		add_action('wp_ajax_nopriv_send_email', array( &$this, 'send_email' ) );
		
		add_filter( 'the_content', array( &$this, 'the_content' ) );		
	}


	/**
	 * Get the current post ID and + 1 to the meta for counting
	 * using the get_post_meta and update_post_meta
	 * @return $content.
	 * @since 1.5
	**/
	function the_content( $content ) {		
		global $post;
		$option = get_option( 'super_post' );
		
		// Check if the social share button enable for current post type
		// and the share method is not empty
		if( $option['post_type_share'] && array_key_exists ( $post->post_type, $option['post_type_share'] ) && is_array( $option['share_method'] ) ) {
			
			$permalink = get_permalink( $post->ID );
			$html  = '';			
			$html .= "<br /><ul id='sp-sharer-{$post->ID}' class='sp-sharer sp-sharer-default' data-id='{$post->ID}'>";
			$html .= $option['share_title'] ? "<li><span class='share-title'>{$option['share_title']}</span></li>" : '';

			foreach( $option['share_method'] as $key => $val ) :
				switch( $key ) :
					case 'twitter':
						$twitter_link = 'http://twitter.com/intent/tweet?text='. urlencode( $post->post_title.' '.$permalink );
						$html .= "<li><a id='spsharer-twitter-{$post->ID}' class='spsharer spsharer-twitter' title='Share on Twitter' href='$twitter_link' rel='nofollow'><span>Twitter</span></a></li>";
						break;
					
					case 'facebook':
						$facebook_link = 'https://www.facebook.com/sharer.php?u='. urlencode( $permalink ) . '&t='. urlencode( $post->post_title );
						$html .= "<li><a id='spsharer-facebook-{$post->ID}' class='spsharer spsharer-facebook' title='Share on Facebook' href='$facebook_link' rel='nofollow'><span>Facebook</span></a></li>";
						break;
						
					case 'google+':
						$googleplus_link = 'https://plus.google.com/share?url='. urlencode( $permalink );
						$html .= "<li><a id='spsharer-google-{$post->ID}' class='spsharer spsharer-google' title='Share on Google+' href='$googleplus_link' rel='nofollow'><span>Google +1</span></a></li>";
						break;
					
					case 'email':
						$meta = get_post_meta( $post->ID, 'super_post', true );
						$mailscount = isset( $meta['emails'] ) ? $meta['emails'] : '';
						$html .= "<li><a id='spsharer-mail-{$post->ID}' class='spsharer-email' title='Share by email to a friend' href='$permalink' rel='nofollow'><span>Email</span><span class='count'>{$mailscount}</span></a></li>";
						break;
				endswitch;
			endforeach;
						
			$html .= "</ul>";
			return $content . $html;
		}
		
		return $content;
	}

	
	/**
	 * Additional column for the selected post type from the plugin option
	 * Later action by add add_filter( "manage_{$post_type}_posts_columns", $posts_columns );
	 * @return $columns.
	 * @since 1.5
	**/
	function head_enqueue() {
		add_filter( 'sp_localize_script', array( &$this, 'localize_script' ) );
		wp_enqueue_style( 'super-post', SUPER_POST_URL . 'css/super-post.css' );
		wp_enqueue_script( 'jquery' );		
		wp_enqueue_script( 'super-post', SUPER_POST_URL . 'js/jquery.super-post.js' );		
	}
	
	
	/**
	 * Create scripts in flip mode {"http:\/\/stackoverflow.com\/":"714"}
	 * Will break if in archive page
	 * @since 1.5
	**/
	function localize_script( $script ) {
		if( ! is_singular() )
			return $script;
			
		$post_id = get_the_ID();
		$sharer = array();
		$sharer[ (int) $post_id ] = get_permalink( $post_id );
		$scripts = $script + array( 
			'share' 		=> array_flip( $sharer ),
			'google_plus'	=> 'get_google_plus_one',
			'sendmail'		=> 'send_email'
		);
		return $scripts;
	}
	
	
	/**
	 * Inserting the mail form in the footer
	 * @return HTML.
	 * @since 1.5
	**/
	function footer_html() {
		global $current_user;
		$option = get_option( 'super_post' );
		$post_id = get_the_ID();
		?>
		<div id="sphare-email" style="display: none;">
			<form action=""class="sp-form" method="post" action="#">
				<?php if ( is_user_logged_in() ) : ?>
				
					<input type="hidden" name="name" value="<?php echo esc_attr( $current_user->display_name ); ?>" />
					<input type="hidden" name="sender" value="<?php echo esc_attr( $current_user->user_email ); ?>" />
				
				<?php else : ?>

					<p><label for="<?php echo $post_id; ?>-sp-name"><?php _e( 'Your name:', 'super-post' ) ?></label>
					<input id="<?php echo $post_id; ?>-sp-name" class="input-medium" type="text" name="name" value="" /></p>
					
					<p><label for="<?php echo $post_id; ?>-sp-sender"><?php _e( 'Your email address:', 'super-post' ) ?></label>
					<input id="<?php echo $post_id; ?>-sp-sender" class="input-medium" type="text" name="sender" value="" /></p>				
				
				<?php endif; ?>
				
				<p><?php if( $option['mail_info'] ) : ?><label for="<?php echo $post_id; ?>-sp-recipient"><?php echo $option['mail_info']; ?></label><?php endif; ?>
				<input id="<?php echo $post_id; ?>-sp-recipient" type="text" name="recipient" value="" /></p>
				
				<input type="submit" value="<?php _e( 'Send Email', 'super-post' ); ?>" class="sp-mail-send" />
				<a href="#spsharer-cancel" class="sp-mail-cancel"><?php _e( 'Cancel', 'super-post' ); ?></a>
				<div id="sp-response"></div>
			</form>
		</div>
		<?php
	}
	
	
	/**
	 * Share send mail function
	 * Uses wp_mail( $to, $subject, $message, $headers, $attachments )
	 * @since 1.5
	**/
	function send_email() {
		$nonce = $_POST['nonce'];
		if ( ! wp_verify_nonce( $nonce, 'super-post' ) && ! isset( $_POST['data'] ) )
			die();
		
		parse_str( $_POST['data'], $data );
		
		$option = get_option( 'super_post' );
		$post = get_post( $_POST['id'] );
		
		$subject = $body = '';
		
		// String replacements [sender-name], [sender-mail], [excerpt] and [link] for mail subject		
		if( $option['mail_subject'] ) {
			$subject .= str_replace(
						array( '[post-title]' ), 
						array( $post->post_title ), 
						$option['mail_subject']
					);
		}
		
		// String replacements [sender-name], [sender-mail], [excerpt] and [link] for mail body
		if( $option['mail_body'] ) {
			$body .= str_replace(
						array( '[sender-name]', '[sender-mail]', '[excerpt]', '[link]' ), 
						array( $data['name'], $data['sender'], super_post_excerpt( (int) $_POST['id'], 55 ), get_permalink( $_POST['id'] ) ), 
						$option['mail_body']
					);
			$body .= "\n";
		}

		$to = array();
		
		// Doube check email format if user input many emails
		$emails = explode( ',', $data['recipient'] );
		foreach( $emails as $key => $val ) {
			if( is_email( trim( $val ) ) )
				$to[] = trim( $val );
		}

		$headers[] = 'From: ' . get_bloginfo('name') . ' <' . get_bloginfo( 'admin_email' ) . '>';

		if ( wp_mail( $to, $subject, $body, $headers ) )
			echo '<span class="sp-mail-success">' . sprintf( __( 'Email sent to <span>%1$s</span>', 'super-post' ), implode(', ', $to) ) . '</span>';
		else
			echo '<span class="sp-mail-error">' . __('Error, please try again later!', 'super-post') . '</span>';
			
		// +1 for each email sent
		$meta = get_post_meta( $_POST['id'], 'super_post', true );
		$meta['emails'] = (int) $meta['emails'] + count($to);
		update_post_meta( $_POST['id'], 'super_post', $meta );

		exit;
	}
	
	
	/**
	 * Additional column for the selected post type from the plugin option
	 * Later action by add add_filter( "manage_{$post_type}_posts_columns", $posts_columns );
	 * @return $columns.
	 * @since 1.5
	**/
	function get_google_plus_one() {
		$nonce = $_POST['nonce'];
		if ( ! wp_verify_nonce( $nonce, 'super-post' ) && ! isset( $_POST['url'] ) )
			die();
		
		$url = $_POST['url'];
		$ch = curl_init();
			curl_setopt_array($ch, array(
			CURLOPT_HTTPHEADER => array('Content-type: application/json'),
			CURLOPT_POST => true,
			CURLOPT_POSTFIELDS => '[{"method":"pos.plusones.get","id":"p","params":{"nolog":true,"id":"'.$url.'","source":"widget","userId":"@viewer","groupId":"@self"},"jsonrpc":"2.0","key":"p","apiVersion":"v1"}]',
			CURLOPT_RETURNTRANSFER => true,
			CURLOPT_SSL_VERIFYPEER => false,
			CURLOPT_URL => 'https://clients6.google.com/rpc'
		));
		$res = curl_exec($ch);
		curl_close($ch);
		  
		if( $res ) {
			$json = json_decode( $res,true );
			$count = (int) $json[0]['result']['metadata']['globalCounts']['count'];
			echo json_encode( array( 'url' => $url, 'count' => $count ) );
		}
		exit();
	}
}
?>