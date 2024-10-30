<?php
/**
Plugin Name: Kv Simple Custom User Photo
Plugin URI: http://www.kvcodes.com/
Description: Now, you can add Custom photo while creating new User. I have checked with other plug-ins, I can't find one which will add photo or picture an user while creating New user.
Author: Kvvaradha
Version: 1.0
Author URI: http://profiles.wordpress.org/kvvaradha
*/

// Add settings link on plugin page
function kvcodes_add_new_user_link($links) { 
  $settings_link = '<a href="user-new.php">Add New User With Custom Photo</a>'; 
  array_unshift($links, $settings_link); 
  return $links; 
}
 
$plugin = plugin_basename(__FILE__); 
add_filter("plugin_action_links_$plugin", 'kvcodes_add_new_user_link' );
add_action( 'admin_enqueue_scripts', 'kvcodes_enqueue_scripts_styles' );
add_action( 'show_user_profile', 'kvcodes_profile_img_fields' );
add_action( 'edit_user_profile', 'kvcodes_profile_img_fields' );
add_action( 'user_new_form', 'kvcodes_profile_img_fields' );
add_action( 'personal_options_update', 'kvcodes_save_img_meta' );
add_action( 'edit_user_profile_update', 'kvcodes_save_img_meta' );
add_action( 'user_register', 'kvcodes_save_img_meta', 10, 1 );
add_filter( 'get_avatar', 'kvcodes_avatar', 1, 5 );

/**
 * Kvcodes -  Enqueue scripts and styles
 */
function kvcodes_enqueue_scripts_styles() {
	// Register.
	wp_register_style( 'kvcodes_admin_css', plugin_dir_url( __FILE__ ).'css/styles.css', false, '1.0.0', 'all' );
	wp_register_script( 'kvcodes_admin_js', plugin_dir_url( __FILE__ ). 'js/scripts.js' , array( 'jquery' ), '1.0.0', true );

	// Enqueue.
	wp_enqueue_style( 'kvcodes_admin_css' );
	wp_enqueue_script( 'kvcodes_admin_js' );
}

/**
 * Show the new image field in the user profile page.
 */
function kvcodes_profile_img_fields( $user ) {
	if ( ! current_user_can( 'upload_files' ) ) {
		return;
	}

	$url             = get_the_author_meta( 'kvcodes_meta', $user->ID );
	$upload_url      = get_the_author_meta( 'kvcodes_upload_meta', $user->ID );
	$upload_edit_url = get_the_author_meta( 'kvcodes_upload_edit_meta', $user->ID );
	$button_text     = $upload_url ? 'Change Current Image' : 'Upload New Image';

	//if ( $upload_url ) {
	//	$upload_edit_url = get_site_url() . $upload_edit_url;
	//}?>

	<div id="kvcodes_container">
		<h3> <i class="dashicons dashicons-admin-users" > </i> <?php _e( 'User Photo', 'kvcodes' ); ?></h3>

		<table class="form-table">
			<tr><th><label for="kvcodes_meta"><?php _e( 'Profile Photo', 'kvcodes' ); ?></label></th>
				<td>					
					<div id="current_img">
						<?php if ( $upload_url ): ?>
							<img class="kvcodes-current-img" src="<?php echo esc_url( $upload_url ); ?>"/>

							<div class="edit_options uploaded">
								<a class="remove_img">
									<span> <i class="dashicons dashicons-dismiss" > </i> </span>
								</a>

								<a class="edit_img" href="<?php echo esc_url( $upload_edit_url ); ?>" target="_blank">
									<span> <i class="dashicons dashicons-welcome-write-blog" > </i> </span>
								</a>
							</div>
						<?php elseif ( $url ) : ?>
							<img class="kvcodes-current-img" src="<?php echo esc_url( $url ); ?>"/>
							<div class="edit_options single">
								<a class="remove_img">
									<span> <i class="dashicons dashicons-dismiss" > </i></span>
								</a>
							</div>
						<?php else : ?>
							<img class="kvcodes-current-img placeholder"    src="<?php echo esc_url( plugin_dir_url( __FILE__ ).'img/empty.gif' ); ?>"/>
						<?php endif; ?>
					</div>
					
					<div id="kvcodes_options"><!-- Select an option: Upload to WPMU or External URL -->
						<label for="upload_option"><i class="dashicons dashicons-format-image" > </i> <input type="radio" id="upload_option" name="img_option" value="upload" class="tog" checked>
						<?php _e( 'Upload or Select', 'kvcodes' ); ?></label>

						<label for="external_option">
						<i class="dashicons dashicons-admin-links" > </i> <input type="radio" id="external_option" name="img_option" value="external" class="tog">
						<?php _e( 'External Photo Link', 'kvcodes' ); ?></label>
					</div>
					
					<div id="kvcodes_upload"><!-- Hold the value here if this is a WPMU image -->
						<input class="hidden" type="hidden" name="kvcodes_placeholder_meta" id="kvcodes_placeholder_meta"   value=" "/>
						<input class="hidden" type="hidden" name="kvcodes_upload_meta" id="kvcodes_upload_meta"  value="<?php echo esc_url_raw( $upload_url ); ?>"/>
						<input class="hidden" type="hidden" name="kvcodes_upload_edit_meta" data-url="<?php echo get_admin_url().'post.php?post='; ?>" id="kvcodes_upload_edit_meta" value="<?php echo esc_url_raw( $upload_edit_url ); ?>"/>
						<input id="uploadimage" type='button' class="kvcodes_wpmu_button button-primary"    value="<?php _e( esc_attr( $button_text ), 'kvcodes' ); ?>"/>
						<br/>
					</div>

					<div id="kvcodes_external">
						<label>  <input class="regular-text" type="text" name="kvcodes_meta" id="kvcodes_meta"   value="<?php echo esc_url_raw( $url ); ?>"/> </label>
					</div>
					<span class="description">
						<?php _e('You can select an existing image or upload new or you can provide remote link of another profile photo. But Avoid Using External Images, links may  broke any time',	'kvcodes');?>
					</span>					
				</td>
			</tr>
		</table><!-- end form-table -->
	</div> <!-- end #kvcodes_container -->
	<?php
	wp_enqueue_media();
}

/**
 * Save the new user Kvcodes url.
 */
function kvcodes_save_img_meta( $user_id ) {
	if ( ! current_user_can( 'upload_files', $user_id ) ) {
		return;
	}

	$values = array(
		'kvcodes_meta'             => filter_input( INPUT_POST, 'kvcodes_meta', FILTER_SANITIZE_STRING ),
		'kvcodes_upload_meta'      => filter_input( INPUT_POST, 'kvcodes_upload_meta', FILTER_SANITIZE_URL ),
		'kvcodes_upload_edit_meta' => filter_input( INPUT_POST, 'kvcodes_upload_edit_meta', FILTER_SANITIZE_URL ),
	);

	foreach ( $values as $key => $value ) {
		update_user_meta( $user_id, $key, $value );
	}
}

/**
 * Retrieve the appropriate image with desired size or preset size.
 */
function get_kvcodes_meta( $user_id, $size = 'thumbnail' ) {
	global $post;

	if ( ! $user_id || ! is_numeric( $user_id ) ) {
		$user_id = $post->post_author;
	}
	$attachment_upload_url = esc_url( get_the_author_meta( 'kvcodes_upload_meta', $user_id ) );

	if ( $attachment_upload_url ) {
		$attachment_id = attachment_url_to_postid( $attachment_upload_url );
		$image_thumb = wp_get_attachment_image_src( $attachment_id, $size );
		return isset( $image_thumb[0] ) ? $image_thumb[0] : '';
	}
	$attachment_ext_url = esc_url( get_the_author_meta( 'kvcodes_meta', $user_id ) );
	return $attachment_ext_url ? $attachment_ext_url : '';
}

/**
 *Modify WordPress Avatar Filter by detecting our custom Photo.
 */
function kvcodes_avatar( $avatar, $identifier, $size, $alt ) {
	if ( $user = kvcodes_get_user_by_id_or_email( $identifier ) ) {
		if ( $custom_avatar = get_kvcodes_meta( $user->ID, 'thumbnail' ) ) {
			return "<img alt='{$alt}' src='{$custom_avatar}' class='avatar avatar-{$size} photo' height='{$size}' width='{$size}' />";
		}
	}
	return $avatar;
}


/**
 * Get a WordPress User by ID or email which is already wrote in get_avator. But this is for our custom meta picture hooking. 
 */
function kvcodes_get_user_by_id_or_email( $identifier ) {
	if ( is_numeric( $identifier ) ) {
		return get_user_by( 'id', (int) $identifier );
	}
	
	if ( is_object( $identifier ) && property_exists( $identifier, 'ID' ) ) {
		return get_user_by( 'id', (int) $identifier->ID );
	}

	if ( is_object( $identifier ) && property_exists( $identifier, 'user_id' ) ) {
		return get_user_by( 'id', (int) $identifier->user_id );
	}
	return get_user_by( 'email', $identifier );
}
?>