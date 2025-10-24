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
 * @package    early_access
 */

/**
 * Hook class.
 */
class Hook_actionlog_early_access extends Hook_actionlog
{
    /**
     * Get details of action log entry types handled by this hook.
     *
     * @return array Map of handler data in standard format
     */
    public function get_handlers() : array
    {
        if (!addon_installed('early_access')) {
            return [];
        }

        require_lang('early_access');

        return [
            'ADD_EARLY_ACCESS_CODE' => [
                'flags' => ACTIONLOG_FLAGS_NONE,
                'cma_hook' => null,
                'identifier_index' => 0,
                'written_context_index' => 1,
                'followup_page_links' => [
                    'EARLY_ACCESS_CODES' => '_SEARCH:admin_early_access:browse',
                    'EDIT_THIS_EARLY_ACCESS_CODE' => '_SEARCH:admin_early_access:_edit:{ID}',
                ],
            ],
            'EDIT_EARLY_ACCESS_CODE' => [
                'flags' => ACTIONLOG_FLAGS_NONE,
                'cma_hook' => null,
                'identifier_index' => 0,
                'written_context_index' => 1,
                'followup_page_links' => [
                    'EARLY_ACCESS_CODES' => '_SEARCH:admin_early_access:browse',
                    'EDIT_THIS_EARLY_ACCESS_CODE' => '_SEARCH:admin_early_access:_edit:{ID}',
                ],
            ],
            'DELETE_EARLY_ACCESS_CODE' => [
                'flags' => ACTIONLOG_FLAGS_NONE,
                'cma_hook' => null,
                'identifier_index' => 0,
                'written_context_index' => null,
                'followup_page_links' => [
                    'EARLY_ACCESS_CODES' => '_SEARCH:admin_early_access:browse',
                ],
            ],
        ];
    }
}
