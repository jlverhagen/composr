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
class CMSForumWrite
{
    /**
     * Mark a forum as read.
     *
     * @param  AUTO_LINK $forum_id Forum ID
     */
    public function mark_forum_as_read(int $forum_id)
    {
        cms_verify_parameters_phpdoc();

        if (is_guest()) {
            warn_exit(do_lang_tempcode('permissions:ACCESS_DENIED__NOT_AS_GUEST'));
        }

        require_code('config2');

        require_code('cns_forums_action2');
        $_max_forum_detail = get_option('max_forum_detail');
        set_option('max_forum_detail', '10000');
        cns_ping_forum_read_all($forum_id);
        set_option('max_forum_detail', $_max_forum_detail);
    }
}
