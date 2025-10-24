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

/**
 * Hook class.
 */
class Hook_endpoint_cms_homesite_forum_topics
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
        if (!addon_installed('cms_homesite')) {
            return null;
        }
        if (!addon_installed('downloads')) {
            return null;
        }
        if (!addon_installed('news')) {
            return null;
        }

        return [
            'authorization' => ($type !== 'view') ? ['super_admin', 'maintenance_password'] : false,
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
        require_code('cms_homesite');

        switch ($type) {
            case 'add':
                $forum_id = post_param_string('forum_id');
                $topic_title = post_param_string('topic_title');
                $post = post_param_string('post');

                require_code('cns_topics_action');
                require_code('cns_posts_action');
                $topic_id = cns_make_topic(intval($forum_id), '', '', 1, 1, 0, 0, null, null, false);
                $post_id = cns_make_post(intval($topic_id), $topic_title, $post, 0, true, 1, 0, null, null, null, get_member(), null, null, null, false);

                return ['id' => $post_id];

            default:
                return []; // GET not implemented
        }
    }
}
