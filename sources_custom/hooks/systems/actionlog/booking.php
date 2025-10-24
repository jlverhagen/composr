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
 * @package    booking
 */

/**
 * Hook class.
 */
class Hook_actionlog_booking extends Hook_actionlog
{
    /**
     * Get details of action log entry types handled by this hook.
     *
     * @return array Map of handler data in standard format
     */
    public function get_handlers() : array
    {
        if (!addon_installed('booking')) {
            return [];
        }

        require_lang('booking');

        return [
            'ADD_BOOKABLE' => [
                'flags' => ACTIONLOG_FLAGS_NONE,
                'cma_hook' => null,
                'identifier_index' => 0,
                'written_context_index' => 1,
                'followup_page_links' => [
                    'EDIT_THIS_BOOKABLE' => '_SEARCH:cms_booking:_edit:{ID}',
                    'ADD_BOOKABLE' => '_SEARCH:cms_booking:add',
                    'ADD_BOOKABLE_SUPPLEMENT' => '_SEARCH:cms_booking:add_category',
                    'ADD_BOOKABLE_BLACKED' => '_SEARCH:cms_booking:add_other',
                ],
            ],
            'EDIT_BOOKABLE' => [
                'flags' => ACTIONLOG_FLAGS_NONE,
                'cma_hook' => null,
                'identifier_index' => 0,
                'written_context_index' => 1,
                'followup_page_links' => [
                    'EDIT_THIS_BOOKABLE' => '_SEARCH:cms_booking:_edit:{ID}',
                    'ADD_BOOKABLE' => '_SEARCH:cms_booking:add',
                    'ADD_BOOKABLE_SUPPLEMENT' => '_SEARCH:cms_booking:add_category',
                    'ADD_BOOKABLE_BLACKED' => '_SEARCH:cms_booking:add_other',
                ],
            ],
            'DELETE_BOOKABLE' => [
                'flags' => ACTIONLOG_FLAGS_NONE,
                'cma_hook' => null,
                'identifier_index' => 0,
                'written_context_index' => 1,
                'followup_page_links' => [
                    'ADD_BOOKABLE' => '_SEARCH:cms_booking:add',
                ],
            ],
            'ADD_BOOKABLE_BLACKED' => [
                'flags' => ACTIONLOG_FLAGS_NONE,
                'cma_hook' => null,
                'identifier_index' => 0,
                'written_context_index' => 1,
                'followup_page_links' => [
                    'EDIT_THIS_BOOKABLE_BLACKED' => '_SEARCH:cms_booking:_edit_other:{ID}',
                    'ADD_BOOKABLE_BLACKED' => '_SEARCH:cms_booking:add_other',
                ],
            ],
            'EDIT_BOOKABLE_BLACKED' => [
                'flags' => ACTIONLOG_FLAGS_NONE,
                'cma_hook' => null,
                'identifier_index' => 0,
                'written_context_index' => 1,
                'followup_page_links' => [
                    'EDIT_THIS_BOOKABLE_BLACKED' => '_SEARCH:cms_booking:_edit_other:{ID}',
                    'ADD_BOOKABLE_BLACKED' => '_SEARCH:cms_booking:add_other',
                ],
            ],
            'DELETE_BOOKABLE_BLACKED' => [
                'flags' => ACTIONLOG_FLAGS_NONE,
                'cma_hook' => null,
                'identifier_index' => 0,
                'written_context_index' => 1,
                'followup_page_links' => [
                    'ADD_BOOKABLE_BLACKED' => '_SEARCH:cms_booking:add_other',
                ],
            ],
            'ADD_BOOKABLE_SUPPLEMENT' => [
                'flags' => ACTIONLOG_FLAGS_NONE,
                'cma_hook' => null,
                'identifier_index' => 0,
                'written_context_index' => 1,
                'followup_page_links' => [
                    'EDIT_THIS_BOOKABLE_SUPPLEMENT' => '_SEARCH:cms_booking:_edit_category:{ID}',
                    'ADD_BOOKABLE_SUPPLEMENT' => '_SEARCH:cms_booking:add_category',
                ],
            ],
            'EDIT_BOOKABLE_SUPPLEMENT' => [
                'flags' => ACTIONLOG_FLAGS_NONE,
                'cma_hook' => null,
                'identifier_index' => 0,
                'written_context_index' => 1,
                'followup_page_links' => [
                    'EDIT_THIS_BOOKABLE_SUPPLEMENT' => '_SEARCH:cms_booking:_edit_category:{ID}',
                    'ADD_BOOKABLE_SUPPLEMENT' => '_SEARCH:cms_booking:add_category',
                ],
            ],
            'DELETE_BOOKABLE_SUPPLEMENT' => [
                'flags' => ACTIONLOG_FLAGS_NONE,
                'cma_hook' => null,
                'identifier_index' => 0,
                'written_context_index' => 1,
                'followup_page_links' => [
                    'ADD_BOOKABLE_SUPPLEMENT' => '_SEARCH:cms_booking:add_category',
                ],
            ],
        ];
    }
}
