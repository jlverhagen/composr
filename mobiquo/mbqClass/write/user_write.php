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

/**
 * Composr API helper class.
 */
class CMSUserWrite
{
    /**
     * Update current member's signature.
     *
     * @param  string $signature New signature
     * @return string New signature, after any filtering
     */
    public function update_signature(string $signature) : string
    {
        cms_verify_parameters_phpdoc();

        if (is_guest()) {
            access_denied('NOT_AS_GUEST');
        }

        require_code('cns_members_action2');
        cns_member_choose_signature($signature, get_member());

        $_signature = $GLOBALS['FORUM_DRIVER']->get_member_row_field(get_member(), 'm_signature');
        return get_translated_text($_signature, $GLOBALS['FORUM_DB']);
    }

    /**
     * Make current member ignore another member.
     *
     * @param  MEMBER $user_id Member to ignore
     * @param  boolean $adding If the block is being added
     */
    public function ignore_user(int $user_id, bool $adding)
    {
        cms_verify_parameters_phpdoc();

        if (is_guest()) {
            access_denied('NOT_AS_GUEST');
        }

        if (!addon_installed('chat')) {
            warn_exit(do_lang_tempcode('INTERNAL_ERROR', escape_html('dc622b7c4b8f581d95320279ed76b5d0')));
        }

        require_code('chat2');
        if ($adding) {
            blocking_add(get_member(), $user_id);
        } else {
            blocking_remove(get_member(), $user_id);
        }
    }
}
