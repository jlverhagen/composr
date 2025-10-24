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
 * @package    booking
 */

// TODO (takes an ID of a bookable) - for choosing a recurrence of a specific bookable

class Block_side_choose_showing
{
    public function info() : ?array
    {
        $info = [];
        $info['author'] = 'Chris Graham';
        $info['organisation'] = 'Composr';
        $info['hacked_by'] = null;
        $info['hack_version'] = null;
        $info['version'] = 1;
        $info['locked'] = false;
        $info['min_cms_version'] = 11.0;
        $info['addon'] = 'booking';
        $info['parameters'] = [];
        return $info;
    }

    public function run()
    {
        $error_msg = new Tempcode();
        if (!addon_installed__messaged('booking', $error_msg)) {
            return $error_msg;
        }

        if (!addon_installed('calendar')) {
            return do_template('RED_ALERT', ['_GUID' => 'fb845aa226d41500c7d3f52d45a916fb', 'TEXT' => do_lang_tempcode('MISSING_ADDON', escape_html('calendar'))]);
        }
        if (!addon_installed('ecommerce')) {
            return do_template('RED_ALERT', ['_GUID' => '1dfd3d076545fb3d978dc0f2f14924da', 'TEXT' => do_lang_tempcode('MISSING_ADDON', escape_html('ecommerce'))]);
        }

        return make_string_tempcode('Not yet implemented');
    }
}
