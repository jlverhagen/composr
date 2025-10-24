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
 * @package    content_read_tracking
 */

/**
 * Hook class.
 */
class Hook_database_manifest_content_read_tracking
{

    /**
     * Database manifest for this addon.
     * This is automatically maintained by the software release tools if you are using it.
     *
     * @return array Map of tables, indices, foreign keys, and privileges
     */
    public function db_meta() : array
    {
        return [
            'tables' => [
                'content_read' => [
                    'addon' => 'content_read_tracking',
                    'fields' => [
                        'r_content_id' => '*ID_TEXT',
                        'r_content_type' => '*ID_TEXT',
                        'r_member_id' => '*MEMBER',
                        'r_time' => 'TIME',
                    ],
                ],
            ],
            'indices' => [
                'content_read__content_read' => [
                    'addon' => 'content_read_tracking',
                    'name' => 'content_read',
                    'table' => 'content_read',
                    'fields' => [
                        0 => 'r_content_type',
                        1 => 'r_content_id',
                    ],
                    'is_full_text' => false,
                ],
                'content_read__content_read_cleanup' => [
                    'addon' => 'content_read_tracking',
                    'name' => 'content_read_cleanup',
                    'table' => 'content_read',
                    'fields' => [
                        0 => 'r_time',
                    ],
                    'is_full_text' => false,
                ],
            ],
            'foreign_keys' => [],
            'privileges' => [],
        ];
    }
}
