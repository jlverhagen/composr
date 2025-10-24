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
class Hook_hybridauth__misc_overrides
{
    /**
     * Get extended integration info to enhance Hybridauth, with easier and better provider integration.
     *
     * @return array Map of integration info
     */
    public function info() : array
    {
        if (!addon_installed('hybridauth')) {
            return [];
        }

        return [
            'BitBucket' => [
                'background_colour' => '205081',
                'text_colour' => 'FFFFFF',
            ],
            'Dropbox' => [
                'background_colour' => '000000', // 1087DD, except our icon contains the colour
                'text_colour' => 'FFFFFF',
                'icon' => 'links/dropbox',
            ],
            'MicrosoftGraph' => [
                'label' => 'Microsoft',
                'icon' => 'links/microsoft',
            ],
            'Yahoo' => [
                'background_colour' => 'F2EDAA', // 720E9E, except our icon contains the colour
                'text_colour' => '000000',
                'icon' => 'links/yahoo',
            ],
        ];
    }
}
