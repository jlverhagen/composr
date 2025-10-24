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
class CMSTopicWrite
{
    /**
     * Create a new topic.
     *
     * @param  AUTO_LINK $forum_id Forum ID
     * @param  string $title Title
     * @param  string $post Post body
     * @param  array $attachment_ids List of attachment IDs to include with the post
     * @return array A pair: new topic ID, validated status (binary)
     */
    public function new_topic(int $forum_id, string $title, string $post, array $attachment_ids) : array
    {
        cms_verify_parameters_phpdoc();

        $post = add_attachments_from_comcode($post, $attachment_ids);

        require_code('wordfilter');
        $title = check_wordfilter($title);

        require_code('cns_topics_action');
        $new_topic_id = cns_make_topic($forum_id);

        require_code('cns_posts_action');
        $new_post_id = cns_make_post($new_topic_id, $title, $post, 0, true, null, 0, null, null, null, null, null, null, null, true, true, $forum_id); // NB: Checks perms implicitly

        $validated = $GLOBALS['FORUM_DB']->query_select_value('f_topics', 't_validated', ['id' => $new_topic_id]);

        return [$new_topic_id, $validated];
    }
}
