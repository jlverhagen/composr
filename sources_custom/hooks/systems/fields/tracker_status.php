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
class Hook_fields_tracker_status extends Hook_fields_list
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
        if ($ev == $field['cf_default']) {
            return '';
        }

        if (is_object($ev)) {
            return $ev;
        }

        $orig_ev = $ev;

        if (option_value_from_field_array($field, 'display_val', 'off') == 'on') {
            $map = $this->get_input_list_map($field, false);
            if (isset($map[$ev])) {
                $ev = $map[$ev];
            }
        }

        if ($orig_ev == 'open') {
            $ev = '[b][color="GoldenRod"]' . $ev . '[/color][/b]';
        }
        if (strpos($orig_ev, 'closed') === 0) {
            $ev = '[b][color="FireBrick"]' . $ev . '[/color][/b]';
        }
        if ($orig_ev == 'completed') {
            $ev = '[b][color="Green"]' . $ev . '[/color][/b]';
        }
        if ($orig_ev == 'information_needed') {
            $ev = '[b][color="Fuchsia"]' . $ev . '[/color][/b]';
        }

        return comcode_to_tempcode($ev, null, true);
    }
}
