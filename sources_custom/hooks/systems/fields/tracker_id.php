<?php /*

 Composr
 Copyright (c) Christopher Graham, 2004-2024

 See docs/LICENSE.md for full licensing information.

*/

/**
 * @license    http://opensource.org/licenses/cpal_1.0 Common Public Attribution License
 * @copyright  Christopher Graham
 * @package    cms_homesite_tracker
 */

require_lang('tracker');
require_code('hooks/systems/fields/integer');

/**
 * Hook class.
 */
class Hook_fields_tracker_id extends Hook_fields_integer
{
    /**
     * Convert a field value to something renderable.
     *
     * @param  array $field The field details
     * @param  mixed $ev The raw value
     * @param  integer $i Position in fieldset
     * @param  ?array $only_fields List of fields the output is being limited to (null: N/A)
     * @param  ?ID_TEXT $table The table we store in (null: N/A)
     * @param  ?AUTO_LINK $id The ID of the row in the table (null: N/A)
     * @param  ?ID_TEXT $id_field Name of the ID field in the table (null: N/A)
     * @param  ?ID_TEXT $field_id_field Name of the field ID field in the table (null: N/A)
     * @param  ?ID_TEXT $url_field Name of the URL field in the table (null: N/A)
     * @param  ?MEMBER $submitter Submitter (null: current member)
     * @param  ?mixed $ev_pure The 'pure' form of the raw value, meaning Comcode is not pre-parsed and string conversion has not been performed (null: unknown)
     * @return mixed Rendered field (string or Tempcode)
     */
    public function render_field_value(array &$field, $ev, int $i, ?array $only_fields, ?string $table = null, ?int $id = null, ?string $id_field = null, ?string $field_id_field = null, ?string $url_field = null, ?int $submitter = null, $ev_pure = null)
    {
        if (is_object($ev)) {
            if ($ev->evaluate() == do_lang('NA_EM')) {
                return '';
            }

            return $ev;
        }

        if ($ev == '') {
            return '';
        }

        if (($GLOBALS['XSS_DETECT']) && (ocp_is_escaped($ev))) {
            ocp_mark_as_escaped($ev);
        }

        return '#' . $ev;
    }

    /**
     * Get form inputter.
     *
     * @param  string $_cf_name The field name
     * @param  string $_cf_description The field description
     * @param  array $field The field details
     * @param  ?string $actual_value The actual current value of the field (null: none)
     * @param  boolean $new Whether this is for a new entry
     * @return ?Tempcode The Tempcode for the input field (null: skip the field - it's not input)
     */
    public function get_field_inputter(string $_cf_name, string $_cf_description, array $field, ?string $actual_value, bool $new) : ?object
    {
        return null;
    }

    /**
     * Find the posted value from the get_field_inputter field.
     *
     * @param  boolean $editing Whether we were editing (because on edit, it could be a fractional edit)
     * @param  array $field The field details
     * @param  ?string $upload_dir Where the files will be uploaded to (null: do not store an upload, return null if we would need to do so)
     * @param  ?array $old_value Former value of field (null: none)
     * @return ?string The value (null: could not process)
     */
    public function inputted_to_field_value(bool $editing, array $field, ?string $upload_dir = 'uploads/catalogues', ?array $old_value = null) : ?string
    {
        if ($old_value !== null) {
            return strval($old_value['cv_value']);
        }

        $id = $field['id'];
        return $this->get_field_auto_increment($id, '');
    }
}
