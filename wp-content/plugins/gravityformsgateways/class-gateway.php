<?php

GFForms::include_addon_framework();

class GFGateways extends GFAddOn {

	protected $_version = GF_GATEWAY_VERSION;
	protected $_min_gravityforms_version = '1.9';
	protected $_slug = 'stripe-gateways';
	protected $_path = 'gravityformsgateways/gateway.php';
	protected $_full_path = __FILE__;
	protected $_title = 'Gravity Forms Stripe Payment Gateways Add-On';
	protected $_short_title = 'Stripe Payment Gateways';

	private static $_instance = null;

	/**
	 * Get an instance of this class.
	 *
	 * @return GFGateways
	 */
	public static function get_instance() {
		if ( self::$_instance == null ) {
			self::$_instance = new GFGateways();
		}

		return self::$_instance;
	}

	public function pre_init() {
		parent::pre_init();
		if ( $this->is_gravityforms_supported() && class_exists( 'GF_Field' ) ) {
			require_once( 'includes/class-simple-gf-field.php' );
		}
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

	
	/**
	 * Configures the settings which should be rendered on the add-on settings tab.
	 *
	 * @return array
	 */
	
	 public function plugin_settings_fields() {

		$show_plugin_list = true;
		$plugins_array = [];

		
		$bancontact_array = array(
			'label'   => esc_html__( 'Bancontact', 'stripe-gateways' ),
			'type'    => 'checkbox',
			'name'    => 'enabled_bancontact',
			'choices' => array(
				array(
					'label' => esc_html__( 'Enable/Disable', 'bancontact' ),
					'name'  => 'enabled_bancontact',
				),
			),
		);

		array_push($plugins_array, $bancontact_array);

		$eps_array = array(
			'label'   => esc_html__( 'EPS', 'stripe-gateways' ),
			'type'    => 'checkbox',
			'name'    => 'enabled_eps',
			'choices' => array(
				array(
					'label' => esc_html__( 'Enable/Disable', 'eps'),
					'name'  => 'enabled_eps',
				),
			),
		);

		array_push($plugins_array, $eps_array);

		$ideal_array = array(
			'label'   => esc_html__( 'Ideal', 'stripe-gateways' ),
			'type'    => 'checkbox',
			'name'    => 'enabled_ideal',
			'choices' => array(
				array(
					'label' => esc_html__( 'Enable/Disable', 'ideal'),
					'name'  => 'enabled_ideal',
				),
			),
		);

		array_push($plugins_array, $ideal_array);

		$sofort_array = array(
			'label'   => esc_html__( 'Sofort', 'stripe-gateways' ),
			'type'    => 'checkbox',
			'name'    => 'enabled_sofort',
			'choices' => array(
				array(
					'label' => esc_html__( 'Enable/Disable', 'sofort'),
					'name'  => 'enabled_sofort',
				),
			),
		);

		array_push($plugins_array, $sofort_array);


		if($show_plugin_list == false) {
			echo 'You haven\'t added any Gateway addons ';
			return false;
		} 

		return array(
			
			array(
				'title'  => esc_html__( '', 'stripe-gateways' ),
				'fields' => $plugins_array
			)
		);

		

	}



	/**
	 * Configures the settings which should be rendered on the Form Settings > Simple Add-On tab.
	 *
	 * @return array
	 */
	public function form_settings_fields( $form ) {

		$show_plugin_list = false;
		$plugins_array = [];
		
		$gatway_settings = get_option( 'gravityformsaddon_stripe-gateways_settings' );

		if($gatway_settings['enabled_bancontact'] != NULL && $gatway_settings['enabled_bancontact'] != '0') {
			
			$show_plugin_list = true;
			
			$bancontact_array = array(
				'label'   => esc_html__( 'Bancontact', 'stripe-gateways' ),
				'type'    => 'checkbox',
				'name'    => 'enabled_bancontact',
				'choices' => array(
					array(
						'label' => esc_html__( 'Enable/Disable', 'bancontact' ),
						'name'  => 'enabled_bancontact',
					),
				),
			);

			array_push($plugins_array, $bancontact_array);

		}

		if($gatway_settings['enabled_eps'] != NULL && $gatway_settings['enabled_eps'] != '0') {

			$show_plugin_list = true;

			$eps_array = array(
				'label'   => esc_html__( 'EPS', 'stripe-gateways' ),
				'type'    => 'checkbox',
				'name'    => 'enabled_eps',
				'choices' => array(
					array(
						'label' => esc_html__( 'Enable/Disable', 'eps'),
						'name'  => 'enabled_eps',
					),
				),
			);

			array_push($plugins_array, $eps_array);

		}

		if($gatway_settings['enabled_ideal'] != NULL && $gatway_settings['enabled_ideal'] != '0') {

			$show_plugin_list = true;

			$ideal_array = array(
				'label'   => esc_html__( 'Ideal', 'stripe-gateways' ),
				'type'    => 'checkbox',
				'name'    => 'enabled_ideal',
				'choices' => array(
					array(
						'label' => esc_html__( 'Enable/Disable', 'ideal'),
						'name'  => 'enabled_ideal',
					),
				),
			);

			array_push($plugins_array, $ideal_array);

		}
		

		if($gatway_settings['enabled_sofort'] != NULL && $gatway_settings['enabled_sofort'] != '0') {
			
			$show_plugin_list = true;

			$sofort_array = array(
				'label'   => esc_html__( 'Sofort', 'stripe-gateways' ),
				'type'    => 'checkbox',
				'name'    => 'enabled_sofort',
				'choices' => array(
					array(
						'label' => esc_html__( 'Enable/Disable', 'sofort'),
						'name'  => 'enabled_sofort',
					),
				),
			);

			array_push($plugins_array, $sofort_array);

		}

		if($show_plugin_list == false) {
			
			error_reporting(0);

			echo 'You haven\'t added or enabled any Gateway addons ';
			return false;

		} else {
			
			return array(
				
				array(
					'title'  => esc_html__( 'Stripe Payment Gateways Settings', 'stripe-gateways' ),
					'fields' => $plugins_array
				)
			);

		}


	}

	public function is_custom_logic_met( $form, $entry ) {
		if ( $this->is_gravityforms_supported( '2.0.7.4' ) ) {
			// Use the helper added in Gravity Forms 2.0.7.4.
			return $this->is_simple_condition_met( 'custom_logic', $form, $entry );
		}
		// Older version of Gravity Forms, use our own method of validating the simple condition.
		$settings = $this->get_form_settings( $form );
		$name       = 'custom_logic';
		$is_enabled = rgar( $settings, $name . '_enabled' );
		if ( ! $is_enabled ) {
			// The setting is not enabled so we handle it as if the rules are met.
			return true;
		}
		// Build the logic array to be used by Gravity Forms when evaluating the rules.
		$logic = array(
			'logicType' => 'all',
			'rules'     => array(
				array(
					'fieldId'  => rgar( $settings, $name . '_field_id' ),
					'operator' => rgar( $settings, $name . '_operator' ),
					'value'    => rgar( $settings, $name . '_value' ),
				),
			)
		);
		return GFCommon::evaluate_conditional_logic( $logic, $form, $entry );
	}

	

	public function after_submission( $entry, $form ) {
		// Evaluate the rules configured for the custom_logic setting.
		$result = $this->is_custom_logic_met( $form, $entry );
		if ( $result ) {
			// Do something awesome because the rules were met.
		}
	}


}
