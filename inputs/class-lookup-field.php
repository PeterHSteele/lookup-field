<?php
class GfLookupField extends GF_Field_Text {
  public $type = 'lookup';

  public function get_form_editor_field_title(){
    return __('Lookup', 'lookup-field');
  }
  
  public function get_form_editor_button(){
    return array(
      'group' => 'advanced_fields',
      'text'  => $this->get_form_editor_field_title()
  );
  }

  public function get_field_input( $form, $value = '', $entry = null ){
    $form_id         = absint( $form['id'] );
		$is_entry_detail = $this->is_entry_detail();
		$is_form_editor  = $this->is_form_editor();

		$html_input_type = 'text';

		if ( $this->enablePasswordInput && ! $is_entry_detail ) {
			$html_input_type = 'password';
		}

		$id          = (int) $this->id;
		$field_id    = $is_entry_detail || $is_form_editor || $form_id == 0 ? "input_$id" : 'input_' . $form_id . "_$id";

		$value        = esc_attr( $value );
		$size         = $this->size;
		$class_suffix = $is_entry_detail ? '_admin' : '';
		$class        = $size . $class_suffix;
		$class        = esc_attr( $class );

		$max_length = is_numeric( $this->maxLength ) ? "maxlength='{$this->maxLength}'" : '';

		$tabindex              = $this->get_tabindex();
		$disabled_text         = $is_form_editor ? 'disabled="disabled"' : '';
		$placeholder_attribute = $this->get_field_placeholder_attribute();
		$required_attribute    = $this->isRequired ? 'aria-required="true"' : '';
		$invalid_attribute     = $this->failed_validation ? 'aria-invalid="true"' : 'aria-invalid="false"';
		$aria_describedby      = $this->get_aria_describedby();
		$autocomplete          = $this->enableAutocomplete ? $this->get_field_autocomplete_attribute() : '';
    $url                   = $this->lookup_url;

		// For Post Tags, Use the WordPress built-in class "howto" in the form editor.
		$text_hint = '';
		if ( $this->type === 'post_tags' ) {
			$text_hint_class = $is_form_editor ? 'howto' : 'gfield_post_tags_hint gfield_description';
			$text_hint       = '<p class="' . $text_hint_class . '" id="' . $field_id . '_desc">' . gf_apply_filters( array(
					'gform_post_tags_hint',
					$form_id,
					$this->id,
				), esc_html__( 'Separate tags with commas', 'gravityforms' ), $form_id ) . '</p>';
		}

		$input = "<input name='input_{$id}' id='{$field_id}' type='{$html_input_type}' value='{$value}' class='{$class}' {$max_length} {$aria_describedby} {$tabindex} {$placeholder_attribute} {$required_attribute} {$invalid_attribute} {$disabled_text} {$autocomplete} data-url={$url} role='combobox' aria-controls='lookup-field-suggestions' aria-expanded='false' aria-haspopup='listbox'/> {$text_hint}";

		return sprintf( "<div class='ginput_container ginput_container_text'>%s</div>", $input );
  }

  public static function lookup_url_setting( $position, $form_id ){
    if ($position == 50){
    ?>
    <li class="field-setting">
      <label for="lookup_field_url" class="section-label">URL</label>
      <input type="text" id="lookup_field_url" onchange="SetFieldProperty('lookup_url', this.value)">
    </li>
    <?php
    }
  }

  public function get_field_content( $value, $force_frontend_label, $form ) {
		$form_id = (int) rgar( $form, 'id' );

		$field_label = $this->get_field_label( $force_frontend_label, $value );
		if ( ! in_array( $this->inputType, array( 'calculation', 'singleproduct' ), true ) ) {
			// Calculation and Single Product field add a screen reader text to the label so do not escape it.
			$field_label = esc_html( $field_label );
		}

		$validation_message_id = 'validation_message_' . $form_id . '_' . $this->id;
		$validation_message = ( $this->failed_validation && ! empty( $this->validation_message ) ) ? sprintf( "<div id='%s' class='gfield_description validation_message gfield_validation_message'>%s</div>", $validation_message_id, $this->validation_message ) : '';

		$is_form_editor  = $this->is_form_editor();
		$is_entry_detail = $this->is_entry_detail();
		$is_admin        = $is_form_editor || $is_entry_detail;

		$required_div = $this->isRequired ? '<span class="gfield_required">' . $this->get_required_indicator() . '</span>' : '';

		$admin_buttons = $this->get_admin_buttons();

		$target_input_id = $this->get_first_input_id( $form );

		$label_tag = $this->get_field_label_tag( $form );

		$for_attribute = empty( $target_input_id ) || $label_tag === 'legend' ? '' : "for='{$target_input_id}'";

		$admin_hidden_markup = ( $this->visibility == 'hidden' ) ? $this->get_hidden_admin_markup() : '';

		$description = $this->get_description( $this->description, 'gfield_description' );

    $tooltip = "<div class='lf-tooltip'><ul id='lookup-field-suggestions' role='listbox'></ul></div>";

		if ( $this->is_description_above( $form ) ) {
			$clear         = $is_admin ? "<div class='gf_clear'></div>" : '';
			$field_content = sprintf( "%s%s<$label_tag class='%s' $for_attribute >%s%s</$label_tag>%s{FIELD}%s%s$clear", $admin_buttons, $admin_hidden_markup, esc_attr( $this->get_field_label_class() ), $field_label, $required_div, $description, $validation_message, $tooltip );
		} else {
			$field_content = sprintf( "%s%s<$label_tag class='%s' $for_attribute >%s%s</$label_tag>{FIELD}%s%s%s", $admin_buttons, $admin_hidden_markup, esc_attr( $this->get_field_label_class() ), $field_label, $required_div, $description, $validation_message, $tooltip );
		}

		return $field_content;
	}

  
  function editor_script(){
    ?>
    <script type='text/javascript'>
        //adding setting to fields of type "text"
        fieldSettings.text += ", .encrypt_setting";
        //binding to the load field settings event to initialize the checkbox
        jQuery(document).on("gform_load_field_settings", function(event, field, form){
            jQuery( '#field_encrypt_value' ).prop( 'checked', Boolean( rgar( field, 'encryptField' ) ) );
        });
    </script>
    <?php
  } 
}