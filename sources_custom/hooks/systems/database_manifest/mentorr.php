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
 * @package    mentorr
 */

/**
 * Hook class.
 */
class Hook_database_manifest_mentorr
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
                'members_mentors' => [
                    'addon' => 'mentorr',
                    'fields' => [
                        'id' => '*AUTO',
                        'member_id' => '*MEMBER',
                        'mentor_member_id' => '*MEMBER',
                        'date_and_time' => 'TIME',
                    ],
                ],
            ],
            'indices' => [
                'members_mentors__date_and_time' => [
                    'addon' => 'mentorr',
                    'name' => 'date_and_time',
                    'table' => 'members_mentors',
                    'fields' => [
                        0 => 'date_and_time',
                    ],
                    'is_full_text' => false,
                ],
            ],
            'foreign_keys' => [],
            'privileges' => [],
        ];
    }
}
