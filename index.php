<?php
/*
Plugin Name: Spotted Koi Custom Excerpt Manager
 Plugin URI: http://spottedkoi.com
 Description: Allows custom excerpt configuration for your blog. This will override all filters for excerpts from your theme or other plugins and use the settings you choose through your administration panel.
 Version: 0.1
 Author: Matt Bernier
 Author URI: http://spottedkoi.com
*/

/**
 * @todo make an admin page for modifying the excerpt length, read more options (is there, text, etc)
 * @todo pull the wp_options from db when using this
 */

function sk_new_excerpt_length($length) {
	return 120;
}
remove_all_filters( 'excerpt_length');
add_filter('excerpt_length', 'sk_new_excerpt_length');

function sk_excerpt_more() {
	global $post;
	return ' '.get_option('sk_pre_read_more_text').'<a href="'. get_permalink($post->ID) . 
					'" class="sk_read_more">'.get_option('sk_read_more_text').'</a>'.get_option('sk_post_read_more_text');
}
remove_all_filters( 'excerpt_more');
add_filter( 'excerpt_more', 'sk_excerpt_more' );

function sk_wp_trim_all_excerpt($text) {
   	// Creates an excerpt if needed; and shortens the manual excerpt as well
   	global $post;
	//do_settings_sections( 'sk-excerpt-settings-group' ); 
	
   	if ( empty($text) ) {
      	$text = get_the_content('');
   	}

   	$text = strip_shortcodes( $text ); // optional
   	//$text = apply_filters('the_content', $text);
   	$text = str_replace(']]>', ']]&gt;', $text);
   	$text = strip_tags($text);
	$excerptSize = get_option('sk_excerpt_length');
   	//$excerpt_length = apply_filters('excerpt_length', get_option('sk_excerpt_length'));
   	$excerpt_more = sk_excerpt_more();
   	$words = preg_split("/[\n\r\t ]+/", $text, $excerptSize + 1, PREG_SPLIT_NO_EMPTY);
   	
	if (count($words)> $excerptSize) {
      	array_pop($words);
      	$text = implode(' ', $words);
   	} else {
      	$text = implode(' ', $words);
   	}
   	$text = $text . $excerpt_more;

   	return $text;
}
remove_all_filters( 'get_the_excerpt');
add_filter('get_the_excerpt', 'sk_wp_trim_all_excerpt');

function custom_excerpts_page() {
	?>
	<div class="wrap">
	<h2>SK Custom Excerpts Manager</h2>
	<form method="post" action="options.php">
	    <?php 
			settings_fields( 'sk-excerpt-settings-group' );
			do_settings_sections( 'sk-excerpt-settings-group' ); 
		?>
	    <table class="form-table">
	        <tr valign="top">
				<?php
				$excerptLength = get_option('sk_excerpt_length');
				?>
	        	<th scope="row">Excerpt Length</th>
	        	<td><input type="text" name="sk_excerpt_length" value="<?php echo ($excerptLength === false ? 55 : $excerptLength); ?>" /><em>The word count of the excerpt text.</em></td>
	        </tr>

		        <tr valign="top">
					<?php
					$preReadMore = get_option('sk_pre_read_more_text');
					?>
		        	<th scope="row">Pre Read More Text</th>
		        	<td><input type="text" name="sk_pre_read_more_text" value="<?php echo ($preReadMore === false ? '' : $preReadMore); ?>" /><em>Text or symbols that go before the Read More Text, ex: &amp;raquo;</em></td>
		        </tr>

	        <tr valign="top">
				<?php
				$readMore = get_option('sk_read_more_text');
				?>
	        	<th scope="row">Read More Text</th>
	        	<td><input type="text" name="sk_read_more_text" value="<?php echo ($readMore === false ? '[...]' : $readMore); ?>" /><em>The actual content of the Read More Link, example "Read More"</em></td>
	        </tr>
			
			    <tr valign="top">
					<?php
					$postReadMore = get_option('sk_post_read_more_text');
					?>
		        	<th scope="row">Post Read More Text</th>
		        	<td><input type="text" name="sk_post_read_more_text" value="<?php echo ($postReadMore === false ? '' : $postReadMore); ?>" /><em>Text or symbols that go after the Read More Text, ex: "<-- click the link!"</em></td>
		        </tr>
		    
			
			<tr valign="top">
	        	<th scope="row">Link Read More Text to the Post?</th>
				<?php
				$linked = get_option('sk_read_more_linked');
				?>
	        	<td><select name="sk_read_more_linked">
	 					<option value="1"<?php echo ($linked === 1 ? ' selected="selected"' : '');?>>
							Yes
						</option>
						<option value="0"<?php echo ($linked === 0 || $linked == false ? ' selected="selected"' : '');?>>
							No
						</option>
				</td>
	        </tr>
		</table>
	    <p class="submit">
	    <input type="submit" class="button-primary" value="<?php _e('Save Changes') ?>" />
	    </p>
	</form>
	<div>
		<em> If you like this plugin please check out some of <a href="http://spottedkoi.com/plugins/" target="_blank">Spotted Koi's other plugins</a></em>.
	</div>
	</div>
	<?php 
}

function sk_excerpts_init() {
	add_submenu_page('options-general.php', 'Custom Excerpts', 'Custom Excerpts', 'manage_options', 'custom-excerpts', 'custom_excerpts_page' );
}
add_action('admin_menu', 'sk_excerpts_init');

function sk_register_settings() {
	register_setting( 'sk-excerpt-settings-group', 'sk_read_more_text' );
	register_setting( 'sk-excerpt-settings-group', 'sk_pre_read_more_text' );
	register_setting( 'sk-excerpt-settings-group', 'sk_post_read_more_text' );
  	register_setting( 'sk-excerpt-settings-group', 'sk_excerpt_length' );
  	register_setting( 'sk-excerpt-settings-group', 'sk_read_more_linked' );
}
add_action( 'admin_init', 'sk_register_settings' );