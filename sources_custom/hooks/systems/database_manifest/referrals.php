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
 * @package    referrals
 */

/**
 * Hook class.
 */
class Hook_database_manifest_referrals
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
                'referees_qualified_for' => [
                    'addon' => 'referrals',
                    'fields' => [
                        'id' => '*AUTO',
                        'q_action' => 'ID_TEXT',
                        'q_scheme_name' => 'ID_TEXT',
                        'q_referred_member' => 'MEMBER',
                        'q_referring_member' => 'MEMBER',
                        'q_email_address' => 'SHORT_TEXT',
                        'q_time' => 'TIME',
                    ],
                ],
                'referrer_override' => [
                    'addon' => 'referrals',
                    'fields' => [
                        'o_scheme_name' => '*ID_TEXT',
                        'o_referring_member' => '*MEMBER',
                        'o_is_qualified' => '?BINARY',
                        'o_referrals_dif' => 'INTEGER',
                    ],
                ],
            ],
            'indices' => [],
            'foreign_keys' => [],
            'privileges' => [],
        ];
    }
}
