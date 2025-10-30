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
class Hook_endpoint_account_setup_push_notifications
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

        if (is_guest(get_member())) {
            return null;
        }

        return [
            'authorization' => ['member'],
            'log_stats_event' => 'account/setup_push_notifications',
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
        // Store a device notification token (i.e. identification of a device, so we can send notifications to it).
        $token_type = either_param_string('device'); // iOS|android
        $token = either_param_string('token');

        $member_details = $GLOBALS['FORUM_DB']->query_select('f_members', ['id'], ['id' => get_member()], '', 1);
        if (!isset($member_details[0])) {
            warn_exit(do_lang_tempcode('MEMBER_NO_EXIST'), false, false, 404);
        }

        $GLOBALS['SITE_DB']->query_delete('device_token_details', ['member_id' => get_member(), 'token_type' => $token_type]);
        $GLOBALS['SITE_DB']->query_insert('device_token_details', [
            'token_type' => $token_type,
            'member_id' => get_member(),
            'device_token' => $token,
        ]);
        return ['message' => do_lang('SUCCESS')];
    }
}
