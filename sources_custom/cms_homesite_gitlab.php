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
 * @package    cms_homesite_gitlab
 */

/**
 * Given a GitLab webhook payload, try to figure out the homesite member who performed this action.
 * This will first try Hybridauth, then matching e-mail address, then matching username.
 *
 * @param  array $data The associative array of payload data from GitLab
 * @return ?MEMBER The site member who performed this action (null: not found)
 */
function gitlab_webhook_payload_to_member(array $data) : ?int
{
    $member_id = null;

    // Maximum authenticity: Hybridauth linked GitLab account
    if (($member_id === null) && (isset($data['user']['id']))) {
        $member_id = $GLOBALS['FORUM_DB']->query_select_value_if_there('f_members', 'id', ['m_password_compat_scheme' => 'GitLab', 'm_pass_hash_salted' => strval($data['user']['id'])], 'ORDER BY m_join_time DESC,id DESC');
    }

    // Medium authenticity: matching e-mail address
    if (($member_id === null) && (isset($data['user']['email']))) {
        $member_id = $GLOBALS['FORUM_DRIVER']->get_member_from_email_address($data['user']['email']);
    }

    // Low authenticity: matching username
    if (($member_id === null) && (isset($data['user']['username']))) {
        $member_id = $GLOBALS['FORUM_DRIVER']->get_member_from_username($data['user']['username']);
    }

    return $member_id;
}
