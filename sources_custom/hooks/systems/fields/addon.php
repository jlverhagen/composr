<?php /*

 The contents of this file are subject to the Common Public Attribution License Version 1.0 (the "License");
 you may not use this file except in compliance with the License.
 You may obtain a copy of the License at http://opensource.org/licenses/cpal_1.0.

 Software distributed under the License is distributed on an "AS IS" basis, WITHOUT WARRANTY OF ANY KIND, either express or implied.
 See the License for the specific language governing rights and limitations under the License.

 The Original Code is Composr CMS.

 The Original Developer is the Initial Developer.

 The Initial Developer of the Original Code is Chris Graham.
 All portions of the code written by Chris Graham are Copyright (c) Christopher Graham. All Rights Reserved.

 See docs/LICENSE.md for full licensing information.

*/

/**
 * @license    http://opensource.org/licenses/cpal_1.0 Common Public Attribution License
 * @copyright  Christopher Graham
 * @package    cms_homesite_tracker
 */

require_code('hooks/systems/fields/list');

/**
 * Hook class.
 */
class Hook_fields_addon extends Hook_fields_list
{
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
        if ($actual_value === null) {
            $actual_value = ''; // Plug anomaly due to unusual corruption
        }

        $input_name = @cms_empty_safe($field['cf_input_name']) ? ('field_' . strval($field['id'])) : $field['cf_input_name'];

        $edit_only = option_value_from_field_array($field, 'edit_only', '0');
        if (($field['cf_required'] == 1) && ($actual_value == '')) {
            $edit_only = '0';
        }
        if (($edit_only != '0') && $new) {
            return form_input_hidden($input_name, $actual_value);
        }

        $input_size = max(1, intval(option_value_from_field_array($field, 'input_size', '9')));
        $autocomplete = ($new && !empty($field['cf_autofill_type'])) ? (($field['cf_autofill_hint'] ? ($field['cf_autofill_hint'] . ' ') : '') . $field['cf_autofill_type']) : null;
        $list_tpl = new Tempcode();

        // Now we need to get our addons
        require_code('addons2');
        $available_addons = array_keys(find_available_addons(false, false, [], false, true));
        $installed_addons = array_keys(find_installed_addons(false, false, false));
        $addons = array_unique(array_merge($available_addons, $installed_addons));

        $auto_sort = option_value_from_field_array($field, 'auto_sort', 'off');
        if ($auto_sort == 'on') {
            cms_mb_asort($addons, SORT_NATURAL | SORT_FLAG_CASE);
        }

        $selected = (($actual_value !== null) && ($actual_value !== '') && ($actual_value !== $field['cf_default']));

        if (($field['cf_required'] == 0) || (!$selected) && (!array_key_exists('', $addons))) {
            $list_tpl->attach(form_input_list_entry('', !$selected, do_lang_tempcode('NA_EM')));
        }

        foreach ($addons as $file) {
            $l = basename($file, '.tar');

            $selected = ($l === $actual_value || (($actual_value == '') && ($l == do_lang('OTHER')) && ($field['cf_required'] == 1)));

            $list_tpl->attach(form_input_list_entry($l, $selected, protect_from_escaping(comcode_to_tempcode($l, null, true))));
        }

        return form_input_list($_cf_name, $_cf_description, $input_name, $list_tpl, null, false, $field['cf_required'] == 1, null, $input_size, $autocomplete);
    }
}
