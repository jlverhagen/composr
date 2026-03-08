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
 * @package    cms_homesite
 */

/*FORCE_ORIGINAL_LOAD_FIRST*/

/**
 * Module page class.
 */
class Mx_chat extends Module_chat
{
    /**
     * Execute the module.
     *
     * @return Tempcode The result of execution
     */
    public function run() : object
    {
        if (!addon_installed('chat')) {
            return new Tempcode();
        }

        $ret = parent::run();
        if (!$ret->is_empty()) {
            return $ret;
        }

        if (!addon_installed('cms_homesite')) {
            return new Tempcode();
        }

        $type = get_param_string('type', 'browse');

        if ($type == 'global_room') {
            return $this->global_room();
        }

        return new Tempcode();
    }

    /**
     * UI for the global chat room between Composr CMS sites.
     *
     * @return Tempcode The UI
     */
    public function global_room() : object
    {
        $nick = get_param_string('nick', null);
        $token = get_param_string('token');

        // Validate token
        require_code('telemetry');
        $data = json_decode(base64_decode($token), true);
        $decrypted = decrypt_data_telemetry($data['nonce'], $data['encrypted_data'], $data['encrypted_session_key'], $data['version']);
        if ($decrypted != 'Grant me le accezz 2 de chat!') {
            access_denied();
        }

        // Check if our special room exists (if not, create it)
        $chat_id = $GLOBALS['SITE_DB']->query_select_value_if_there('chat_rooms', 'id', ['room_name' => ('Global ' . brand_name() . ' Chat')], '', 1);
        if ($chat_id === null) {
            require_code('chat2');
            require_lang('cms_homesite');

            $chat_id = add_chatroom(
                do_lang('GLOBAL_CHAT_WELCOME', brand_name()),
                ('Global ' . brand_name() . ' Chat'),
                null,
                '',
                '',
                '',
                '',
                'EN',
                0
            );

            require_code('content2');
            set_url_moniker('chat', strval($chat_id));

            // HACKHACK: grant permission for every group
            $groups = $GLOBALS['FORUM_DRIVER']->get_usergroup_list(false, true);
            foreach (array_keys($groups) as $group_id) {
                $_POST['access_' . strval($group_id)] = 1;
            }

            require_code('permissions2');
            set_category_permissions_from_environment('chat', $chat_id);
        }

        // HACKHACK: load the room
        $url = build_url(['page' => 'chat', 'type' => 'room', 'id' => $chat_id, 'nick' => $nick, 'wide_high' => '1'], get_module_zone('chat'));
        return redirect_screen(null, $url);
    }

    /**
     * The UI for a chatroom.
     *
     * @return Tempcode The UI
     */
    public function chat_room() : object
    {
        if (addon_installed('cms_homesite')) {
            // Allow the origin to embed the Global Chat in an iframe
            if ($this->room_name == ('Global ' . brand_name() . ' Chat')) {
                require_code('csp');
                load_csp(['csp_enabled' => '0']);
            }
        }

        return parent::chat_room();
    }
}
