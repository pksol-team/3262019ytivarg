<?php

if ( ! class_exists( 'GFForms' ) ) {
	die();
}

class Simple_GF_Field extends GF_Field {

	/**
	 * @var string $type The field type.
	 */
	public $type = 'simple';
	public $isRequired = true;

	/**
	 * Return the field title, for use in the form editor.
	 *
	 * @return string
	 */

	public function get_form_editor_field_title() {
		return esc_attr__( 'Payments', 'bancontact' );
	}

	/**
	 * Assign the field button to the Advanced Fields group.
	 *
	 * @return array
	 */
	public function get_form_editor_button() {

		$enable = false;
		$gatway_settings = get_option( 'gravityformsaddon_stripe-gateways_settings' );

		if($gatway_settings['enabled_bancontact'] != NULL && $gatway_settings['enabled_bancontact'] != '0') {
			$enable = true;
		}

		if($gatway_settings['enabled_eps'] != NULL && $gatway_settings['enabled_eps'] != '0') {
			$enable = true;
		}

		if($gatway_settings['enabled_ideal'] != NULL && $gatway_settings['enabled_ideal'] != '0') {
			$enable = true;
		}

		if($gatway_settings['enabled_sofort'] != NULL && $gatway_settings['enabled_sofort'] != '0') {
			$enable = true;
		}

		if($enable == true) {
			
			return array(
				'group' => 'advanced_fields',
				'text'  => $this->get_form_editor_field_title(),
			);

		}



	}

	/**
	 * The settings which should be available on the field in the form editor.
	 *
	 * @return array
	 */
	function get_form_editor_field_settings() {
		return array(
			'label_setting',
			'rules_setting',
			'input_class_setting',
			'css_class_setting',
		);
	}

	/**
	 * The scripts to be included in the form editor.
	 *
	 * @return string
	 */
	public function get_form_editor_inline_script_on_page_render() {

		// set the default field label for the simple type field
		$script = sprintf( "function SetDefaultValues_simple(field) {field.label = '%s';}", $this->get_form_editor_field_title() ) . PHP_EOL;

		// initialize the fields custom settings
		$script .= "alert('working'); jQuery(document).bind('gform_load_field_settings', function (event, field, form) {" .
		           "var inputClass = field.inputClass == undefined ? '' : field.inputClass;" .
		           "jQuery('#input_class_setting').val(inputClass);" .
		           "});" . PHP_EOL;

		// saving the simple setting
		$script .= "function SetInputClassSetting(value) {SetFieldProperty('inputClass', value);}" . PHP_EOL;

		return $script;
	}

	/**
	 * Define the fields inner markup.
	 *
	 * @param array $form The Form Object currently being processed.
	 * @param string|array $value The field value. From default/dynamic population, $_POST, or a resumed incomplete submission.
	 * @param null|array $entry Null or the Entry Object currently being edited.
	 *
	 * @return string
	 */
	public function get_field_input( $form, $value = '', $entry = null ) {

		
		
		$id              = absint( $this->id );
		$form_id         = absint( $form['id'] );
		$is_entry_detail = $this->is_entry_detail();
		$is_form_editor  = $this->is_form_editor();

		// Prepare the value of the input ID attribute.
		$field_id = $is_entry_detail || $is_form_editor || $form_id == 0 ? "input_$id" : 'input_' . $form_id . "_$id";

		$value = esc_attr( $value );

		// Get the value of the inputClass property for the current field.
		$inputClass = $this->inputClass;

		// Prepare the input classes.
		$class_suffix = $is_entry_detail ? '_admin' : '';
		$class        = $class_suffix . ' ' . $inputClass;

		// Prepare the other input attributes.
		$tabindex              = $this->get_tabindex();
		$logic_event           = ! $is_form_editor && ! $is_entry_detail ? $this->get_conditional_logic_event( 'keyup' ) : '';
		$placeholder_attribute = $this->get_field_placeholder_attribute();
		$required_attribute    = $this->isRequired ? 'aria-required="true"' : '';
		$invalid_attribute     = $this->failed_validation ? 'aria-invalid="true"' : 'aria-invalid="false"';
		$disabled_text         = $is_form_editor ? 'disabled="disabled"' : '';

		$options = '<option>Select Gateway</option>';
		$gatway_settings = get_option( 'gravityformsaddon_stripe-gateways_settings' );
		$enable = false;

		if($gatway_settings['enabled_bancontact'] != NULL && $gatway_settings['enabled_bancontact'] != '0') {
			$enable = true;
			$options .= "<option value='bancontact'>Bancontact</option>";
		}

		if($gatway_settings['enabled_eps'] != NULL && $gatway_settings['enabled_eps'] != '0') {
			$enable = true;
			$options .= "<option value='eps'>EPS</option>";
		}

		if($gatway_settings['enabled_ideal'] != NULL && $gatway_settings['enabled_ideal'] != '0') {
			$enable = true;
			$options .= "<option value='ideal'>Ideal</option>";
		}

		if($gatway_settings['enabled_sofort'] != NULL && $gatway_settings['enabled_sofort'] != '0') {
			$enable = true;
			$options .= "<option value='sofort'>Sofort</option>";
		}

		$input = "

			<select name='input_{$id}' id='{$field_id}' class='{$class} payment_calss' {$tabindex} {$logic_event} {$placeholder_attribute} {$required_attribute} {$invalid_attribute} {$disabled_text} >
				".$options."
			</select>

			<input type='hidden' name='total-input-gravity'>
			<input type='hidden' name='method_name' class='method_name'>
			<input type='hidden' name='uniqid' value='".uniqid()."'>

		";

		if($enable == true) {
			return sprintf( "<div class='ginput_container ginput_container_%s'>%s</div>", $this->type, $input );
		}


	}


}

GF_Fields::register( new Simple_GF_Field() );



