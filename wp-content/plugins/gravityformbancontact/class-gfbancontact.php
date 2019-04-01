<?php

GFForms::include_addon_framework();

class GFBanContact extends GFAddOn {

	protected $_version = GF_BANCONTACT_VERSION;
	protected $_min_gravityforms_version = '1.9';
	protected $_slug = 'bancontact';
	protected $_path = 'gravityformbancontact/bancontact.php';
	protected $_full_path = __FILE__;
	protected $_title = 'Gravity Forms BanContact Add-On';
	protected $_short_title = 'Stripe BanContact';

	private static $_instance = null;

	/**
	 * Get an instance of this class.
	 *
	 * @return GFBanContact
	 */
	public static function get_instance() {
		if ( self::$_instance == null ) {
			self::$_instance = new GFBanContact();
		}

		return self::$_instance;
	}

	/**
	 * Handles hooks and loading of language files.
	 */
	public function init() {
		parent::init();
		add_filter( 'gform_submit_button', array( $this, 'form_submit_button' ), 10, 2 );
		add_action( 'gform_after_submission', array( $this, 'after_submission' ), 10, 2 );
		
	}

	// # FRONTEND FUNCTIONS --------------------------------------------------------------------------------------------

	/**
	 * Add the text in the plugin settings to the bottom of the form if enabled for this form.
	 *
	 * @param string $button The string containing the input tag to be filtered.
	 * @param array $form The form currently being displayed.
	 *
	 * @return string
	 */
	function form_submit_button( $button, $form ) {
		$settings = $this->get_form_settings( $form );
		if ( isset( $settings['enabled'] ) && true == $settings['enabled'] ) {
			$text   = $this->get_plugin_setting( 'mytextbox' );
			$button = "<div>{$text}</div>" . $button;
		}

		return $button;
	}

}
