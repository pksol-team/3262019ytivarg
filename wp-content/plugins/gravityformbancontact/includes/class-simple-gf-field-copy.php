<?php

class GF_Field_Attendees extends GF_Field {

public $type = 'simple';

public function get_form_editor_field_title() {
	return esc_attr__( 'Attendees', 'gravityforms' );
}

public function get_form_editor_button() {
	return array(
		'group' => 'advanced_fields',
		'text'  => $this->get_form_editor_field_title(),
	);
}

public function get_form_editor_field_settings() {
	return array(
		'conditional_logic_field_setting',
		'prepopulate_field_setting',
		'error_message_setting',
		'label_setting',
		'admin_label_setting',
		'rules_setting',
		'duplicate_setting',
		'description_setting',
		'css_class_setting',
	);
}

public function is_conditional_logic_supported() {
	return true;
}

public function get_field_input( $form, $value = '', $entry = null ) {
	$is_entry_detail = $this->is_entry_detail();
	$is_form_editor  = $this->is_form_editor();

	$form_id  = $form['id'];
	$field_id = intval( $this->id );

	$first = $last = $email = $phone = '';

	if ( is_array( $value ) ) {
		$first = esc_attr( rgget( $this->id . '.1', $value ) );
		$last  = esc_attr( rgget( $this->id . '.2', $value ) );
		$email = esc_attr( rgget( $this->id . '.3', $value ) );
		$phone = esc_attr( rgget( $this->id . '.4', $value ) );
	}

	$disabled_text = $is_form_editor ? "disabled='disabled'" : '';
	$class_suffix  = $is_entry_detail ? '_admin' : '';

	$first_tabindex = GFCommon::get_tabindex();
	$last_tabindex  = GFCommon::get_tabindex();
	$email_tabindex = GFCommon::get_tabindex();
	$phone_tabindex = GFCommon::get_tabindex();

	$required_attribute = $this->isRequired ? 'aria-required="true"' : '';
	$invalid_attribute  = $this->failed_validation ? 'aria-invalid="true"' : 'aria-invalid="false"';

	$first_markup = '<span id="input_' . $field_id . '_' . $form_id . '.1_container" class="attendees_first">';
	$first_markup .= '<input type="text" name="input_' . $field_id . '.1" id="input_' . $field_id . '_' . $form_id . '_1" value="' . $first . '" aria-label="First Name" ' . $first_tabindex . ' ' . $disabled_text . ' ' . $required_attribute . ' ' . $invalid_attribute . '>';
	$first_markup .= '<label for="input_' . $field_id . '_' . $form_id . '_1">First Name</label>';
	$first_markup .= '</span>';

	$last_markup = '<span id="input_' . $field_id . '_' . $form_id . '.2_container" class="attendees_last">';
	$last_markup .= '<input type="text" name="input_' . $field_id . '.2" id="input_' . $field_id . '_' . $form_id . '_2" value="' . $last . '" aria-label="Last Name" ' . $last_tabindex . ' ' . $disabled_text . ' ' . $required_attribute . ' ' . $invalid_attribute . '>';
	$last_markup .= '<label for="input_' . $field_id . '_' . $form_id . '_2">Last Name</label>';
	$last_markup .= '</span>';

	$email_markup = '<span id="input_' . $field_id . '_' . $form_id . '.3_container" class="attendees_email">';
	$email_markup .= '<input type="text" name="input_' . $field_id . '.3" id="input_' . $field_id . '_' . $form_id . '_3" value="' . $email . '" aria-label="Email" ' . $email_tabindex . ' ' . $disabled_text . ' ' . $required_attribute . ' ' . $invalid_attribute . '>';
	$email_markup .= '<label for="input_' . $field_id . '_' . $form_id . '_3">Email</label>';
	$email_markup .= '</span>';

	$phone_markup = '<span id="input_' . $field_id . '_' . $form_id . '.4_container" class="attendees_phone">';
	$phone_markup .= '<input type="text" name="input_' . $field_id . '.4" id="input_' . $field_id . '_' . $form_id . '_4" value="' . $phone . '" aria-label="Phone #" ' . $phone_tabindex . ' ' . $disabled_text . ' ' . $required_attribute . ' ' . $invalid_attribute . '>';
	$phone_markup .= '<label for="input_' . $field_id . '_' . $form_id . '_4">Phone #</label>';
	$phone_markup .= '</span>';

	$css_class = $this->get_css_class();

	return "<div class='ginput_complex{$class_suffix} ginput_container {$css_class} gfield_trigger_change' id='{$field_id}'>
				{$first_markup}
				{$last_markup}
				{$email_markup}
				{$phone_markup}
				<div class='gf_clear gf_clear_complex'></div>
			</div>";
}

public function get_css_class() {
	$first_input = GFFormsModel::get_input( $this, $this->id . '.1' );
	$last_input  = GFFormsModel::get_input( $this, $this->id . '.2' );
	$email_input = GFFormsModel::get_input( $this, $this->id . '.3' );
	$phone_input = GFFormsModel::get_input( $this, $this->id . '.4' );

	$css_class           = '';
	$visible_input_count = 0;

	if ( $first_input && ! rgar( $first_input, 'isHidden' ) ) {
		$visible_input_count ++;
		$css_class .= 'has_first_name ';
	} else {
		$css_class .= 'no_first_name ';
	}

	if ( $last_input && ! rgar( $last_input, 'isHidden' ) ) {
		$visible_input_count ++;
		$css_class .= 'has_last_name ';
	} else {
		$css_class .= 'no_last_name ';
	}

	if ( $email_input && ! rgar( $email_input, 'isHidden' ) ) {
		$visible_input_count ++;
		$css_class .= 'has_email ';
	} else {
		$css_class .= 'no_email ';
	}

	if ( $phone_input && ! rgar( $phone_input, 'isHidden' ) ) {
		$visible_input_count ++;
		$css_class .= 'has_phone ';
	} else {
		$css_class .= 'no_phone ';
	}

	$css_class .= "gf_attendees_has_{$visible_input_count} ginput_container_attendees ";

	return trim( $css_class );
}

public function get_form_editor_inline_script_on_page_render() {

	// set the default field label for the field
	$script = sprintf( "function SetDefaultValues_%s(field) {
	field.label = '%s';
	field.inputs = [new Input(field.id + '.1', '%s'), new Input(field.id + '.2', '%s'), new Input(field.id + '.3', '%s'), new Input(field.id + '.4', '%s')];
	}", $this->type, $this->get_form_editor_field_title(), 'First Name', 'Last Name', 'Email', 'Phone' ) . PHP_EOL;

	return $script;
}

public function get_value_entry_detail( $value, $currency = '', $use_text = false, $format = 'html', $media = 'screen' ) {
	if ( is_array( $value ) ) {
		$first = trim( rgget( $this->id . '.1', $value ) );
		$last  = trim( rgget( $this->id . '.2', $value ) );
		$email = trim( rgget( $this->id . '.3', $value ) );
		$phone = trim( rgget( $this->id . '.4', $value ) );

		$return = $first;
		$return .= ! empty( $return ) && ! empty( $last ) ? " $last" : $last;
		$return .= ! empty( $return ) && ! empty( $email ) ? " $email" : $email;
		$return .= ! empty( $return ) && ! empty( $phone ) ? " $phone" : $phone;

	} else {
		$return = '';
	}

	if ( $format === 'html' ) {
		$return = esc_html( $return );
	}

	return $return;
}

}

GF_Fields::register( new GF_Field_Attendees() );