<?php

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

if ( ! class_exists( 'Sandbox_Admin_Settings', false ) ) :

/**
 * Sandbox_Admin_Settings Class.
 */
class Sandbox_Admin_Settings {

	/**
	 * Setting pages.
	 *
	 * @var array
	 */
	private static $settings = array();

	/**
	 * Error messages.
	 *
	 * @var array
	 */
	private static $errors   = array();

	/**
	 * Update messages.
	 *
	 * @var array
	 */
	private static $messages = array();

	/**
	 * Include the settings page classes.
	 */
	public static function get_settings_pages() {
		if ( empty( self::$settings ) ) {
			$settings = array();

			include_once( dirname( __FILE__ ) . '/settings/class-sandbox-settings-page.php' );

			$settings[] = include( 'settings/class-sandbox-settings-expiration.php' );
			$settings[] = include( 'settings/class-sandbox-settings-template.php' );
			$settings[] = include( 'settings/class-sandbox-settings-advanced.php' );

			self::$settings = apply_filters( 'sandbox_get_settings_pages', $settings );
		}

		return self::$settings;
	}

	/**
	 * Save the settings.
	 */
	public static function save() {
		global $current_tab;

		if ( empty( $_REQUEST['_wpnonce'] ) || ! wp_verify_nonce( $_REQUEST['_wpnonce'], 'sandbox-settings' ) ) {
			die( __( 'Action failed. Please refresh the page and retry.', 'sandbox' ) );
		}

		// Trigger actions
		do_action( 'sandbox_settings_save_' . $current_tab );
		do_action( 'sandbox_update_options_' . $current_tab );
		do_action( 'sandbox_update_options' );

		self::add_message( __( 'Your settings have been saved.', 'sandbox' ) );

		do_action( 'sandbox_settings_saved' );
	}

	/**
	 * Add a message.
	 * @param string $text
	 */
	public static function add_message( $text ) {
		self::$messages[] = $text;
	}

	/**
	 * Add an error.
	 * @param string $text
	 */
	public static function add_error( $text ) {
		self::$errors[] = $text;
	}

	/**
	 * Output messages + errors.
	 */
	public static function show_messages() {
		if ( sizeof( self::$errors ) > 0 ) {
			foreach ( self::$errors as $error ) {
				echo '<div id="message" class="error inline"><p><strong>' . esc_html( $error ) . '</strong></p></div>';
			}
		} elseif ( sizeof( self::$messages ) > 0 ) {
			foreach ( self::$messages as $message ) {
				echo '<div id="message" class="updated inline"><p><strong>' . esc_html( $message ) . '</strong></p></div>';
			}
		}
	}

	/**
	 * Settings page.
	 *
	 * Handles the display of the main sandbox settings page in admin.
	 */
	public static function output() {

		global $current_section, $current_tab;

		$suffix = defined( 'SCRIPT_DEBUG' ) && SCRIPT_DEBUG ? '' : '.min';

		do_action( 'sandbox_settings_start' );

		// Get tabs for the settings page
		$tabs = apply_filters( 'sandbox_settings_tabs_array', array() );

		include( dirname( __FILE__ ) . '/views/html-admin-settings.php' );
	}

	/**
	 * @param $pageID
	 * @return string
	 */
	public static function get_page_view($pageID){

		return dirname( __FILE__ ) . '/views/html-' . $pageID . '-settings.php';

	}
}

endif;
