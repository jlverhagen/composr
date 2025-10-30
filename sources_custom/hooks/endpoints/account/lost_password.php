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
 * @package    composr_mobile_sdk
 */

/**
 * Hook class.
 */
class Hook_endpoint_account_lost_password
{
    /**
     * Return information about this endpoint.
     *
     * @param  ?string $type Standard type parameter, usually either of add/edit/delete/view (null: not-set)
     * @param  ?string $id Standard ID parameter (null: not-set)
     * @return ?array Info about the hook (null: endpoint is disabled)
     */
    public function info(?string $type, ?string $id) : ?array
    {
        if (!addon_installed('composr_mobile_sdk')) {
            return null;
        }

        if (get_forum_type() != 'cns') {
            return null;
        }

        return [
            'authorization' => false,
            'log_stats_event' => 'account/lost_password',
        ];
    }

    /**
     * Run an API endpoint.
     *
     * @param  ?string $type Standard type parameter, usually either of add/edit/delete/view (null: not-set)
     * @param  ?string $id Standard ID parameter (null: not-set)
     * @return array Data structure that will be converted to correct response type
     */
    public function run(?string $type, ?string $id) : array
    {
        $_username = post_param_string('username', '', INPUT_FILTER_POST_IDENTIFIER);
        $_email = post_param_string('email', '', INPUT_FILTER_POST_IDENTIFIER);

        require_code('cns_lost_password');
        require_lang('cns');
        list($email, $member_id) = lost_password_emailer_step($_username, $_email);

        $password_reset_process = get_password_reset_process();

        $mailed_message = lost_password_mailed_message($password_reset_process, $email);

        return [
            'message' => $mailed_message->evaluate(),
        ];
    }
}
