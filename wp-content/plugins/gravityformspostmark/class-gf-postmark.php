<?php

defined( 'ABSPATH' ) or die();

GFForms::include_addon_framework();

/**
 * Gravity Forms Postmark Add-On.
 *
 * @since     1.0
 * @package   GravityForms
 * @author    Rocketgenius
 * @copyright Copyright (c) 2018, Rocketgenius
 */
class GF_Postmark extends GFAddOn {

	/**
	 * Contains an instance of this class, if available.
	 *
	 * @since  1.0
	 * @access private
	 * @var    object $_instance If available, contains an instance of this class.
	 */
	private static $_instance = null;

	/**
	 * Defines the version of the Postmark Add-On.
	 *
	 * @since  1.0
	 * @access protected
	 * @var    string $_version Contains the version, defined from postmark.php
	 */
	protected $_version = GF_POSTMARK_VERSION;

	/**
	 * Defines the minimum Gravity Forms version required.
	 *
	 * @since  1.0
	 * @access protected
	 * @var    string $_min_gravityforms_version The minimum version required.
	 */
	protected $_min_gravityforms_version = '2.2.3.8';

	/**
	 * Defines the plugin slug.
	 *
	 * @since  1.0
	 * @access protected
	 * @var    string $_slug The slug used for this plugin.
	 */
	protected $_slug = 'gravityformspostmark';

	/**
	 * Defines the main plugin file.
	 *
	 * @since  1.0
	 * @access protected
	 * @var    string $_path The path to the main plugin file, relative to the plugins folder.
	 */
	protected $_path = 'gravityformspostmark/postmark.php';

	/**
	 * Defines the full path to this class file.
	 *
	 * @since  1.0
	 * @access protected
	 * @var    string $_full_path The full path.
	 */
	protected $_full_path = __FILE__;

	/**
	 * Defines the URL where this Add-On can be found.
	 *
	 * @since  1.0
	 * @access protected
	 * @var    string The URL of the Add-On.
	 */
	protected $_url = 'https://www.gravityforms.com';

	/**
	 * Defines the title of this Add-On.
	 *
	 * @since  1.0
	 * @access protected
	 * @var    string $_title The title of the Add-On.
	 */
	protected $_title = 'Gravity Forms Postmark Add-On';

	/**
	 * Defines the short title of the Add-On.
	 *
	 * @since  1.0
	 * @access protected
	 * @var    string $_short_title The short title.
	 */
	protected $_short_title = 'Postmark';

	/**
	 * Defines if Add-On should use Gravity Forms servers for update data.
	 *
	 * @since  1.0
	 * @access protected
	 * @var    bool
	 */
	protected $_enable_rg_autoupgrade = true;

	/**
	 * Defines the capability needed to access the Add-On settings page.
	 *
	 * @since  1.0
	 * @access protected
	 * @var    string $_capabilities_settings_page The capability needed to access the Add-On settings page.
	 */
	protected $_capabilities_settings_page = 'gravityforms_postmark';

	/**
	 * Defines the capability needed to access the Add-On form settings page.
	 *
	 * @since  1.0
	 * @access protected
	 * @var    string $_capabilities_form_settings The capability needed to access the Add-On form settings page.
	 */
	protected $_capabilities_form_settings = 'gravityforms_postmark';

	/**
	 * Defines the capability needed to uninstall the Add-On.
	 *
	 * @since  1.0
	 * @access protected
	 * @var    string $_capabilities_uninstall The capability needed to uninstall the Add-On.
	 */
	protected $_capabilities_uninstall = 'gravityforms_postmark_uninstall';

	/**
	 * Defines the capabilities needed for the Postmark Add-On
	 *
	 * @since  1.0
	 * @access protected
	 * @var    array $_capabilities The capabilities needed for the Add-On
	 */
	protected $_capabilities = array( 'gravityforms_postmark', 'gravityforms_postmark_uninstall' );

	/**
	 * Contains an instance of the Postmark API library, if available.
	 *
	 * @since  1.0
	 * @access protected
	 * @var    GF_Postmark_API $api If available, contains an instance of the Postmark API library.
	 */
	protected $api = null;

	/**
	 * Get instance of this class.
	 *
	 * @since  1.0
	 * @access public
	 *
	 * @return GF_Postmark
	 */
	public static function get_instance() {

		if ( null === self::$_instance ) {
			self::$_instance = new self;
		}

		return self::$_instance;

	}

	/**
	 * Autoload the required libraries.
	 *
	 * @since  1.0
	 * @access public
	 *
	 * @uses   GFAddOn::is_gravityforms_supported()
	 */
	public function pre_init() {

		parent::pre_init();

		if ( $this->is_gravityforms_supported() ) {

			// Load the Postmark API library.
			if ( ! class_exists( 'GF_Postmark_API' ) ) {
				require_once( 'includes/class-gf-postmark-api.php' );
			}

		}

	}

	/**
	 * Register needed hooks.
	 *
	 * @since 1.0
	 * @access public
	 */
	public function init() {

		parent::init();

		// Add Postmark as a notification send via type.
		add_filter( 'gform_notification_services', array( $this, 'add_notification_service' ) );

		// Add Postmark notification fields.
		add_filter( 'gform_notification_ui_settings', array( $this, 'add_notification_fields' ), 10, 3 );

		// Save Postmark notification fields.
		add_filter( 'gform_pre_notification_save', array( $this, 'save_notification_fields' ), 10, 2 );

		// Handle Postmark notifications.
		add_filter( 'gform_pre_send_email', array( $this, 'maybe_send_email' ), 19, 4 );

	}






	// # PLUGIN SETTINGS -----------------------------------------------------------------------------------------------

	/**
	 * Define plugin settings fields.
	 *
	 * @since  1.0
	 * @access public
	 *
	 * @uses   GFAddOn::add_field_after()
	 * @uses   GF_Postmark::initialize_api()
	 *
	 * @return array
	 */
	public function plugin_settings_fields() {

		return array(
			array(
				'description' => sprintf(
					'<p>%s</p>',
					sprintf(
						esc_html__( 'Postmark makes it easy to reliably send and track email notifications. If you don\'t have a Postmark account, you can %1$ssign up for one here%2$s. Once you have signed up, you can %3$sfind your Account and Server tokens here%4$s.', 'gravityformspostmark' ),
						'<a href="https://postmarkapp.com" target="_blank">', '</a>',
						'<a href="https://account.postmarkapp.com/api_tokens" target="_blank">', '</a>'
					)
				),
				'fields' => array(
					array(
						'name'              => 'accountToken',
						'label'             => esc_html__( 'Account API Token', 'gravityformspostmark' ),
						'type'              => 'text',
						'class'             => 'medium',
						'feedback_callback' => array( $this, 'validate_account_token' ),
					),
					array(
						'name'              => 'serverToken',
						'label'             => esc_html__( 'Server API Token', 'gravityformspostmark' ),
						'type'              => 'text',
						'class'             => 'medium',
						'feedback_callback' => array( $this, 'validate_server_token' ),
					),
					array(
						'name'  => 'serverStats',
						'label' => esc_html__( 'Email Statistics', 'gravityformspostmark' ),
						'type'  => 'server_stats',
						'dependency' => array( $this, 'initialize_api' ),
					),
					array(
						'type'     => 'save',
						'messages' => array(
							'success' => esc_html__( 'Postmark settings have been updated.', 'gravityformspostmark' ),
						),
					),
				),
			),
		);

	}

	/**
	 * Renders and initializes a field that displays the statistics for the Postmark Server API token for the past 30 days.
	 *
	 * @since  1.0
	 * @access public
	 *
	 * @param array $field Field settings.
	 * @param bool  $echo  Display field. Defaults to true.
	 *
	 * @uses   GF_Postmark_API::get_outbound_stats()
	 *
	 * @return string
	 */
	public function settings_server_stats( $field, $echo = true ) {

		try {

			// Get outbound stats.
			$stats = $this->api->get_outbound_stats( date( 'Y-m-d', strtotime( '30 days ago' ) ), date( 'Y-m-d' ) );

		} catch ( Exception $e ) {

			return '';

		}

		// If no server stats exist, return.
		if ( empty( $stats ) ) {
			return '';
		}

		// Prepare server stats table.
		$html = '<table id="gform_postmark_server_stats">';
		$html .= '<tr><th>' . esc_html__( 'Sent', 'gravityformspostmark' ) . '</th><td>' . rgar( $stats, 'Sent' ) . '</td></tr>';
		$html .= '<tr><th>' . esc_html__( 'Bounced', 'gravityformspostmark' ) . '</th><td>' . rgar( $stats, 'Bounced' ) . '</td></tr>';
		$html .= '<tr><th>' . esc_html__( 'Spam Complaints', 'gravityformspostmark' ) . '</th><td>' . rgar( $stats, 'SpamComplaints' ) . '</td></tr>';
		$html .= '<tr><th>' . esc_html__( 'Opens', 'gravityformspostmark' ) . '</th><td>' . rgar( $stats, 'Opens' ) . '</td></tr>';
		$html .= '<tr><th>' . esc_html__( 'Unique Opens', 'gravityformspostmark' ) . '</th><td>' . rgar( $stats, 'UniqueOpens' ) . '</td></tr>';
		$html .= '<tr><th>' . esc_html__( 'Tracked', 'gravityformspostmark' ) . '</th><td>' . rgar( $stats, 'Tracked' ) . '</td></tr>';
		$html .= '</table>';

		if ( $echo ) {
			echo $html;
		}

		return $html;

	}





	// # NOTIFICATIONS -------------------------------------------------------------------------------------------------

	/**
	 * Add Postmark as a notification service.
	 *
	 * @since  1.0
	 * @access public
	 *
	 * @param  array $services Existing notification services.
	 *
	 * @uses   GFAddOn::get_base_url()
	 * @uses   GF_Postmark::initialize_api()
	 *
	 * @return array
	 */
	public function add_notification_service( $services = array() ) {

		// If running GF prior to 2.4, check that API is initialized.
		if ( version_compare( GFFormsModel::get_database_version(), '2.4-beta-2', '<' ) && ! $this->initialize_api() ) {
			return $services;
		}

		// Add service.
		$services['postmark'] = array(
			'label'            => esc_html__( 'Postmark', 'gravityformspostmark' ),
			'image'            => $this->get_base_url() . '/images/icon.svg',
			'disabled'         => ! $this->initialize_api(),
			'disabled_message' => sprintf(
				esc_html__( 'You must %sauthenticate with Postmark%s before sending emails using their service.', 'gravityformspostmark' ),
				"<a href='" . esc_url( admin_url( 'admin.php?page=gf_settings&subview=' . $this->_slug ) ) . "'>",
				'</a>'
			),
		);

		return $services;

	}

	/**
	 * Add Postmark notification fields.
	 *
	 * @since  1.0
	 * @access public
	 *
	 * @param array $ui_settings  An array of settings for the notification UI.
	 * @param array $notification The current notification object being edited.
	 * @param array $form         The current form object to which the notification being edited belongs.
	 *
	 * @uses GF_Postmark::initialize_api()
	 * @uses GF_Postmark_API::get_sender_signatures()
	 *
	 * @return array
	 */
	public function add_notification_fields( $ui_settings, $notification, $form ) {

		// If Postmark is not the selected notification service or the API is not initialized, return default UI settings.
		if ( ( ! rgpost( 'gform_notification_service' ) && 'postmark' !== rgar( $notification, 'service' ) ) || ( rgpost( 'gform_notification_service' ) && 'postmark' !== rgpost( 'gform_notification_service' ) ) || ! $this->initialize_api() ) {
			return $ui_settings;
		}

		try {

			// Get sender signatures.
			$sender_signatures = $this->api->get_sender_signatures();

		} catch ( Exception $e ) {

			// Log that sender signatured could not be retrieved.
			$this->log_error( __METHOD__ . '(): Unable to retrieve sender signatures; ' . $e->getMessage() );

			return $ui_settings;

		}

		// Remove the "From Name" field.
		unset( $ui_settings['notification_from_name'] );

		// Build from address row.
		$from_address  = '<tr valign="top">';
		$from_address .= '<th scope="row">';
		$from_address .= sprintf( '<label for="gform_notification_from">%s %s</label>', esc_html__( 'From Email', 'gravityforms' ), gform_tooltip( 'notification_from_email', '', true ) );
		$from_address .= '</th>';
		$from_address .= '<td>';

		// If no sender signatures are provided, display a message to configure.
		if ( empty( $sender_signatures ) ) {

			$from_address .= sprintf(
				esc_html__( 'To setup a notification using Postmark, you must define at least one %1$sSender Signature%3$s. You can learn about Sender Signatures in the %2$sPostmark Help Center%3$s.', 'gravityformspostmark' ),
				'<a href="https://postmarkapp.com/signatures" target="_blank">',
				'<a href="http://support.postmarkapp.com/category/45-category" target="_blank">',
				'</a>'
			);

		} else {

			$from_address .= '<select name="gform_notification_from" id="gform_notification_from">';
			foreach ( $sender_signatures as $sender_signature ) {
				$from_address .= sprintf( '<option value="%s" %s>%s</option>', esc_attr( $sender_signature['EmailAddress'] ), selected( $sender_signature['EmailAddress'], rgar( $notification, 'from' ), false ), esc_attr( $sender_signature['EmailAddress'] ) );
			}
			$from_address .= '</select>';

		}

		$from_address .= '</td>';
		$from_address .= '</tr>';

		// Insert from address row.
		$ui_settings['notification_from'] = $from_address;

		// Build tracking row.
		$tracking  = '<tr valign="top">';
		$tracking .= '<th scope="row">';
		$tracking .= sprintf( '<label for="gform_notification_postmark_track_opens">%s</label>', esc_html__( 'Email Tracking', 'gravityformspostmark' ) );
		$tracking .= '</th>';
		$tracking .= '<td>';
		$tracking .= sprintf( '<input type="checkbox" name="gform_notification_postmark_track_opens" id="gform_notification_postmark_track_opens" value="1" %s />', checked( '1', rgar( $notification, 'postmarkTrackOpens' ), false ) );
		$tracking .= sprintf( '<label for="gform_notification_postmark_track_opens" class="inline"> &nbsp;%s</label>', esc_html__( 'Enable open tracking for this notification', 'gravityformspostmark' ) );
		$tracking .= '</td>';
		$tracking .= '</tr>';

		// Get UI settings array keys.
		$ui_settings_keys = array_keys( $ui_settings );

		// Loop through UI settings.
		foreach ( $ui_settings as $key => $ui_setting ) {

			// If this is not the conditional logic setting, skip.
			if ( 'notification_conditional_logic' !== $key ) {
				continue;
			}

			// Get position.
			$position = array_search( $key, $ui_settings_keys );

			// Add tracking row.
			array_splice( $ui_settings, $position, 0, array( 'postmark_tracking' => $tracking ) );

		}

		return $ui_settings;

	}

	/**
	 * Save Postmark notification fields.
	 *
	 * @since  1.0
	 * @access public
	 *
	 * @param array $notification The notification object about to be saved.
	 * @param array $form         The current form object to which the notification being saved belongs.
	 *
	 * @return array
	 */
	public function save_notification_fields( $notification, $form ) {

		if ( rgpost( 'gform_notification_service' ) == 'postmark' ) {
			$notification['postmarkTrackOpens'] = sanitize_text_field( rgpost( 'gform_notification_postmark_track_opens' ) );
		}

		return $notification;

	}

	/**
	 * Send email through Postmark.
	 *
	 * @since  1.0
	 * @access public
	 *
	 * @param  array  $email          The email details.
	 * @param  string $message_format The message format, html or text.
	 * @param  array  $notification   The Notification object.
	 * @param  array  $entry          The current Entry object.
	 *
	 * @return array
	 */
	public function maybe_send_email( $email, $message_format, $notification, $entry ) {

		// If email has been aborted, return the email.
		if ( $email['abort_email'] ) {
			$this->log_debug( __METHOD__ . '(): Not sending email via Postmark because the notification has already been aborted by another Add-On.' );
			return $email;
		}

		// If this is not a Postmark notification or Postmark API is not initialized, return the email.
		if ( 'postmark' !== rgar( $notification, 'service' ) || ! $this->initialize_api() ) {
			return $email;
		}

		// Get form object.
		$form = GFAPI::get_form( $entry['form_id'] );

		// Prepare email for Postmark.
		$postmark_email = array(
			'From'       => rgar( $notification, 'from' ),
			'To'         => rgar( $email, 'to' ),
			'Subject'    => rgar( $email, 'subject' ),
			'TrackOpens' => rgar( $notification, 'postmarkTrackOpens' ) == '1' ? true : false,
		);

		// Add BCC.
		if ( rgar( $notification, 'bcc' ) ) {
			$postmark_email['Bcc'] = GFCommon::replace_variables( rgar( $notification, 'bcc' ), $form, $entry, false, false, false, 'text' );
		}

		// Add Reply To.
		if ( rgar( $notification, 'replyTo' ) ) {
			$postmark_email['ReplyTo'] = GFCommon::replace_variables( rgar( $notification, 'replyTo' ), $form, $entry, false, false, false, 'text' );
		}

		// Add body based on message format.
		if ( $message_format == 'html' ) {
			$postmark_email['HtmlBody'] = $email['message'];
		} else {
			$postmark_email['TextBody'] = $email['message'];
		}

		// Add attachments.
		if ( ! empty( $email['attachments'] ) ) {

			// Loop through attachments, add to email.
			foreach ( $email['attachments'] as $attachment ) {

				// Get mime type of attachment.
				$finfo     = finfo_open( FILEINFO_MIME_TYPE );
				$mime_type = finfo_file( $finfo, $attachment );
				finfo_close( $finfo );

				// Add attachment.
				$postmark_email['Attachments'][] = array(
					'Name'        => basename( $attachment ),
					'Content'     => base64_encode( file_get_contents( $attachment ) ),
					'ContentType' => $mime_type
				);

			}

		}

		// Add any extra email headers.
		$postmark_headers = $email['headers'];
		unset( $postmark_headers['Bcc'], $postmark_headers['Reply-To'], $postmark_headers['From'], $postmark_headers['Content-type'] );
		$postmark_email['Headers'] = $postmark_headers;

		/**
		 * Modify the email being sent by Postmark.
		 *
		 * @since 1.0
		 *
		 * @param array $postmark_email The Postmark email arguments.
		 * @param array $email          The original email details.
		 * @param array $message_format The message format, html or text.
		 * @param array $notification   The Notification object.
		 * @param array $entry          The current Entry object.
		 */
		$postmark_email = apply_filters( 'gform_postmark_email', $postmark_email, $email, $message_format, $notification, $entry );

		// Log the email to be sent.
		$this->log_debug( __METHOD__ . '(): Sending email via Postmark; ' . print_r( $postmark_email, true ) );

		try {

			// Send email.
			$sent_email = $this->api->send_email( $postmark_email );

			// Log that email was sent.
			$this->log_debug( __METHOD__ . '(): Email successfully sent by Postmark; ' . print_r( $sent_email, true ) );

			// Prevent Gravity Forms from sending email.
			$email['abort_email'] = true;

		} catch ( Exception $e ) {

			// Log that email failed to send.
			$this->log_error( __METHOD__ . '(): Email unable to be sent by Postmark; ' . $e->getMessage() );

		}

		return $email;

	}





	// # HELPER METHODS ------------------------------------------------------------------------------------------------

	/**
	 * Initializes Postmark API if credentials are valid.
	 *
	 * @since  1.0
	 * @access public
	 *
	 * @uses   GFAddOn::get_plugin_settings()
	 * @uses   GFAddOn::log_debug()
	 * @uses   GFAddOn::log_error()
	 * @uses   GF_Postmark::validate_account_token()
	 * @uses   GF_Postmark::validate_server_token()
	 * @uses   GF_Postmark_API::set_account_token()
	 * @uses   GF_Postmark_API::set_server_token()
	 *
	 * @return bool|null
	 */
	public function initialize_api() {

		// If API object is already setup, return true.
		if ( ! is_null( $this->api ) ) {
			return true;
		}

		// Get the plugin settings.
		$settings = $this->get_plugin_settings();

		// If server or account token are empty, do not initialize API.
		if ( rgblank( $settings['serverToken'] ) || rgblank( $settings['accountToken'] ) ) {
			return null;
		}

		// Log that we are testing the API credentials.
		$this->log_debug( __METHOD__ . '(): Validating API credentials.' );

		// Get account and server token validity.
		$account_token_valid = $this->validate_account_token( $settings['accountToken'] );
		$server_token_valid  = $this->validate_server_token( $settings['serverToken'] );

		// If account and server token are valid, assign API object to this instance.
		if ( $account_token_valid && $server_token_valid ) {

			// Assign a new Postmark API object to this instance.
			$this->api = new GF_Postmark_API();

			// Set account token.
			$this->api->set_account_token( $settings['accountToken'] );

			// Set server token.
			$this->api->set_server_token( $settings['serverToken'] );

			// Log that test passed.
			$this->log_debug( __METHOD__ . '(): API credentials are valid.' );

			return true;

		} else {

			// Log that test failed.
			$this->log_error( __METHOD__ . '(): API credentials are invalid.' );

			return false;

		}

	}

	/**
	 * Validate Postmark account token.
	 *
	 * @since  1.0
	 * @access public
	 *
	 * @param string $account_token Postmark account token.
	 *
	 * @uses   GFAddOn::log_debug()
	 * @uses   GFAddOn::log_error()
	 * @uses   GF_Postmark_API::get_sender_signatures()
	 * @uses   GF_Postmark_API::set_account_token()
	 *
	 * @return bool|null
	 */
	public function validate_account_token( $account_token = null ) {

		// If the account token is empty, do not run a validation check.
		if ( rgblank( $account_token ) ) {
			return null;
		}

		// Log that we are testing the account token.
		$this->log_debug( __METHOD__ . '(): Validating account token.' );

		try {

			// Setup a new Postmark API object.
			$postmark = new GF_Postmark_API();

			// Set account token.
			$postmark->set_account_token( $account_token );

			// Attempt to get sender signatures.
			$postmark->get_sender_signatures();

			// Log that test passed.
			$this->log_debug( __METHOD__ . '(): Account token is valid.' );

			return true;

		} catch ( Exception $e ) {

			// Log that test failed.
			$this->log_error( __METHOD__ . '(): Account token is invalid; ' . $e->getMessage() );

			return false;

		}

	}

	/**
	 * Validate Postmark server token.
	 *
	 * @since  1.0
	 * @access public
	 *
	 * @param string $server_token Postmark server token.
	 *
	 * @uses   GFAddOn::log_debug()
	 * @uses   GFAddOn::log_error()
	 * @uses   GF_Postmark_API::get_sender_signatures()
	 * @uses   GF_Postmark_API::set_account_token()
	 *
	 * @return bool|null
	 */
	public function validate_server_token( $server_token = null ) {

		// If the server token is empty, do not run a validation check.
		if ( rgblank( $server_token ) ) {
			return null;
		}

		// Log that we are testing the server token.
		$this->log_debug( __METHOD__ . '(): Validating server token.' );

		try {

			// Setup a new Postmark API object.
			$postmark = new GF_Postmark_API();

			// Set server token.
			$postmark->set_server_token( $server_token );

			// Attempt to get current server.
			$postmark->get_current_server();

			// Log that test passed.
			$this->log_debug( __METHOD__ . '(): Server token is valid.' );

			return true;

		} catch ( Exception $e ) {

			// Log that test failed.
			$this->log_error( __METHOD__ . '(): Server token is invalid; ' . $e->getMessage() );

			return false;

		}

	}

}
