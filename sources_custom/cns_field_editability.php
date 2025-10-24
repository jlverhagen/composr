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
 * @package    hybridauth
 */

/* This file is designed to be overwritten by addons that implement external user sync schemes. */

/**
 * Find is a field is editable.
 * Called for fields that have a fair chance of being set to auto-sync, and hence be locked to local edits.
 *
 * @param  ID_TEXT $field_name Field name
 * @param  ID_TEXT $special_type The special type of the user (built-in types are: <blank>, ldap, httpauth, <name of import source>)
 * @return boolean Whether the field is editable
 */
function cns_field_editable(string $field_name, string $special_type) : bool
{
    if ((addon_installed('hybridauth')) && ($special_type != '')) {
        require_code('hybridauth');
        $is_hybridauth_account = is_hybridauth_special_type($special_type);

        if ($is_hybridauth_account) {
            switch ($field_name) {
                case 'username':
                    if (get_option('hybridauth_sync_username') == '1') {
                        return false;
                    }
                    break;

                // Actually, we want to allow changing password to disassociate the account from Hybridauth
                /*
                case 'password':
                    return false;
                    break;
                */

                case 'email':
                    if (get_option('hybridauth_sync_email') == '1') {
                        return false;
                    }
                    break;
            }
        }
    }

    return non_overridden__cns_field_editable($field_name, $special_type);
}
