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
 * @package    cns_tapatalk
 */

/*EXTRA FUNCTIONS: TapatalkPush*/

/**
 * Composr API helper class.
 */
class CMSMemberACL
{
    /**
     * Login.
     *
     * @param  string $username Username
     * @param  string $password Password
     * @param  boolean $invisible Log in as invisible
     * @return ?MEMBER Member ID (null: login failed)
     */
    public function authenticate_credentials_and_set_auth(string $username, string $password, bool $invisible = false) : ?int
    {
        $feedback = $GLOBALS['FORUM_DRIVER']->authorise_login($username, null, $password);

        $id = $feedback['id'];
        if ($id !== null) {
            $this->set_auth($id, $invisible);

            return $id;
        }
        return null;
    }

    /**
     * Login with no password check.
     *
     * @param  MEMBER $id Member ID
     * @param  boolean $invisible Log in as invisible
     */
    public function set_auth(int $id, bool $invisible = false)
    {
        require_code('cns_forum_driver_helper_auth');
        cns_create_login_cookie($id);

        if ($invisible) {
            set_invisibility();
        }

        $push = new TapatalkPush();
        $push->set_is_tapatalk_member($id);

        header('Mobiquo_is_login: true');
    }

    /**
     * Logout.
     */
    public function logout_user()
    {
        require_code('users_active_actions');
        handle_active_logout();
    }
}
