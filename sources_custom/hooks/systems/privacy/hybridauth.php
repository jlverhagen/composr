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
 * @package    hybridauth
 */

/**
 * Hook class.
 */
class Hook_privacy_hybridauth extends Hook_privacy_base
{
    /**
     * Find privacy details.
     *
     * @return ?array A map of privacy details in a standardised format (null: disabled)
     */
    public function info() : ?array
    {
        if (!addon_installed('hybridauth')) {
            return null;
        }

        return [
            'label' => 'hybridauth:HYBRIDAUTH',

            'description' => 'hybridauth:DESCRIPTION_PRIVACY_HYBRIDAUTH',

            'cookies' => [
                'hybridauth' => [
                    'category' => 'ESSENTIAL',
                    'reason' => do_lang_tempcode('hybridauth:COOKIE_hybridauth'),
                    'session' => false,
                    'httponly' => true,
                ],
            ],

            'positive' => [
            ],

            'general' => [
            ],

            'database_records' => [
            ],
        ];
    }
}
