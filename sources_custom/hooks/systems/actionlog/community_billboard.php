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
 * @package    community_billboard
 */

/**
 * Hook class.
 */
class Hook_actionlog_community_billboard extends Hook_actionlog
{
    /**
     * Get details of action log entry types handled by this hook.
     *
     * @return array Map of handler data in standard format
     */
    public function get_handlers() : array
    {
        if (!addon_installed('community_billboard')) {
            return [];
        }

        require_lang('community_billboard');

        return [
            'ADD_COMMUNITY_BILLBOARD' => [
                'flags' => ACTIONLOG_FLAGS_NONE,
                'cma_hook' => null,
                'identifier_index' => 0,
                'written_context_index' => 1,
                'followup_page_links' => [
                    'EDIT_THIS_COMMUNITY_BILLBOARD' => '_SEARCH:admin_community_billboard:_edit:{ID}',
                    'ADD_COMMUNITY_BILLBOARD' => '_SEARCH:admin_community_billboard:add',
                ],
            ],
            'EDIT_COMMUNITY_BILLBOARD' => [
                'flags' => ACTIONLOG_FLAGS_NONE,
                'cma_hook' => null,
                'identifier_index' => 0,
                'written_context_index' => 1,
                'followup_page_links' => [
                    'EDIT_THIS_COMMUNITY_BILLBOARD' => '_SEARCH:admin_community_billboard:_edit:{ID}',
                    'ADD_COMMUNITY_BILLBOARD' => '_SEARCH:admin_community_billboard:add',
                ],
            ],
            'CHOOSE_COMMUNITY_BILLBOARD' => [
                'flags' => ACTIONLOG_FLAGS_NONE,
                'cma_hook' => null,
                'identifier_index' => 0,
                'written_context_index' => 1,
                'followup_page_links' => [
                    'EDIT_THIS_COMMUNITY_BILLBOARD' => '_SEARCH:admin_community_billboard:_edit:{ID}',
                    'ADD_COMMUNITY_BILLBOARD' => '_SEARCH:admin_community_billboard:add',
                ],
            ],
            'DELETE_COMMUNITY_BILLBOARD' => [
                'flags' => ACTIONLOG_FLAGS_NONE,
                'cma_hook' => null,
                'identifier_index' => 0,
                'written_context_index' => 1,
                'followup_page_links' => [
                    'ADD_COMMUNITY_BILLBOARD' => '_SEARCH:admin_community_billboard:add',
                ],
            ],
        ];
    }
}
