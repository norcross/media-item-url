<?php
/**
 * Plugin Name: Media Item URL
 * Plugin URI: https://github.com/norcross/media-item-url
 * Description: Get full attachment URL from the media row table without opening item.
 * Author: Andrew Norcross
 * Author URI: http://reaktivstudios.com/
 * Version: 1.0.1
 * Text Domain: media-item-url
 * Domain Path: languages
 * License: MIT
 * GitHub Plugin URI: https://github.com/norcross/media-item-url
 */

// Set my base for the plugin.
if ( ! defined( 'RKV_MEDIA_ITEM_URL_BASE' ) ) {
	define( 'RKV_MEDIA_ITEM_URL_BASE', plugin_basename( __FILE__ ) );
}

// Set my directory for the plugin.
if ( ! defined( 'RKV_MEDIA_ITEM_URL_DIR' ) ) {
	define( 'RKV_MEDIA_ITEM_URL_DIR', plugin_dir_path( __FILE__ ) );
}

// Set my version for the plugin.
if ( ! defined( 'RKV_MEDIA_ITEM_URL_VER' ) ) {
	define( 'RKV_MEDIA_ITEM_URL_VER', '1.0.1' );
}

/**
 * Set up and load our class.
 */
class RKV_Media_Item_URL
{

	/**
	 * Load our hooks and filters.
	 *
	 * @return void
	 */
	public function init() {
		add_action( 'plugins_loaded',                   array( $this, 'textdomain'          )           );
		add_action( 'admin_head',                       array( $this, 'load_admin_css'      )           );
		add_action( 'admin_enqueue_scripts',            array( $this, 'load_admin_js'       ),  10      );
		add_filter( 'media_row_actions',                array( $this, 'media_url_field'     ),  50, 3   );
	}

	/**
	 * Load textdomain for international goodness.
	 *
	 * @return void
	 */
	public function textdomain() {
		load_plugin_textdomain( 'media-item-url', false, dirname( plugin_basename( __FILE__ ) ) . '/languages/' );
	}

	/**
	 * Load our tiny amount of CSS.
	 *
	 * @return void
	 */
	public function load_admin_css() {

		// Bail if we aren't on the uploads (i.e. media ) page.
		if ( false === $check = self::check_current_screen() ) {
			return;
		}

		// Echo out the CSS.
		echo '<style media="screen" type="text/css">' . "\n";
		echo 'a.media-url-click { outline: 0 none; box-shadow: none; }' . "\n";
		echo 'a.media-url-click.media-url-open { font-weight: bold; }' . "\n";
		echo 'div.media-url-box { margin: 10px 0; }' . "\n";
		echo '</style>';
	}

	/**
	 * Load our JS file in media library.
	 *
	 * @param  string $hook  The admin page currently loaded.
	 *
	 * @return void
	 */
	public function load_admin_js( $hook ) {

		// Bail if we aren't on the uploads (i.e. media ) page.
		if ( false === $check = self::check_current_screen() ) {
			return;
		}

		// Set up loading our normal or minified file based on script debug.
		$file   = defined( 'SCRIPT_DEBUG' ) && SCRIPT_DEBUG ? 'rkvmr.admin.js' : 'rkvmr.admin.min.js';
		$vers   = defined( 'SCRIPT_DEBUG' ) && SCRIPT_DEBUG ? time() : RKV_MEDIA_ITEM_URL_VER;

		// Load our JS file.
		wp_enqueue_script( 'rkv-media-row', plugins_url( 'lib/js/' . $file, __FILE__ ) , array( 'jquery' ), $vers, true );
	}

	/**
	 * Build the text link and hidden field containing the item URL.
	 *
	 * @param  array  $actions   The existing array of action links for each attachment.
	 * @param  object $post      The WP_Post object for the current attachment.
	 * @param  bool   $detached  Whether the list table contains media not attached to any posts.
	 *
	 * @return array  $actions   The updated array of action links for each attachment.
	 */
	public function media_url_field( $actions, $post, $detached ) {

		// Only load on attachment post type.
		if ( ! is_object( $post ) || empty( $post->ID ) || 'attachment' !== $post->post_type ) {
			return $actions;
		}

		// Get our media item URL.
		$link   = wp_get_attachment_url( $post->ID );

		// If we have no URL, return our actions.
		if ( empty( $link ) ) {
			return $actions;
		}

		// Now fetch our label.
		$label  = self::get_media_type_label( $post->ID );

		// Now build out our box.
		$build  = '';

		$build .= '<a class="media-url-click" href="#">' . esc_html( $label ) . '</a>';
		$build .= '<div class="media-url-box">';
		$build .= '<input type="url" class="widefat media-url-field" value="' . esc_url( $link ) . '" readonly>';
		$build .= '</div>';

		// Add our newly built box to the action row.
		$actions['media-url'] = $build;

		// Return the actions.
		return $actions;
	}

	/**
	 * Fetch the item label based on MIME type.
	 *
	 * @param  integer $post_id  The attachment ID.
	 *
	 * @return string            The appropriate label.
	 */
	public static function get_media_type_label( $post_id ) {

		// Fetch our item MIME type.
		$type   = get_post_mime_type( $post_id );

		// Filter through my types and return the label based on that.
		switch ( $type ) {
			case 'image/jpeg':
			case 'image/png':
			case 'image/gif':
				$label  = __( 'View Image URL', 'media-item-url' );
				break;

			case 'video/mpeg':
			case 'video/mp4':
			case 'video/webm':
			case 'video/ogg':
			case 'video/quicktime':
				$label  = __( 'View Video URL', 'media-item-url' );
				break;

			case 'text/csv':
			case 'text/xml':
				$label  = __( 'View Data File URL', 'media-item-url' );
				break;

			case 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet':
			case 'application/vnd.ms-excel':
				$label  = __( 'View Spreadsheet URL', 'media-item-url' );
				break;

			case 'application/pdf':
			case 'application/rtf':
			case 'application/msword':
			case 'application/vnd.openxmlformats-officedocument.wordprocessingml.document':
				$label  = __( 'View Document URL', 'media-item-url' );
				break;

			case 'text/html':
				$label  = __( 'View HTML file URL', 'media-item-url' );
				break;

			default:
				$label  = __( 'View Item URL', 'media-item-url' );
		}

		// Pass through filter to catch whatever else may be out there.
		$label  = apply_filters( 'rkv_media_type_label', $label, $type );

		// Return the label.
		return $label;
	}

	/**
	 * A helper function for checking the current screen on admin.
	 *
	 * @param  string $id  The screen ID we are checking against.
	 *
	 * @return bool        Whether or not we're on the desired screen.
	 */
	public static function check_current_screen( $id = 'upload' ) {

		// If we don't have the `get_current_screen` function, bail immediately.
		if ( ! function_exists( 'get_current_screen' ) ) {
			return false;
		}

		// Get current screen info.
		$screen = get_current_screen();

		// Our various checks that would return false.
		if ( ! is_object( $screen ) || empty( $screen->id ) || empty( $id ) ) {
			return false;
		}

		// Now do our check against the desired ID.
		return esc_attr( $id ) === esc_attr( $screen->id ) ? true : false;
	}

	// End class.
}

// Instantiate our class.
$RKV_Media_Item_URL = new RKV_Media_Item_URL();
$RKV_Media_Item_URL->init();
