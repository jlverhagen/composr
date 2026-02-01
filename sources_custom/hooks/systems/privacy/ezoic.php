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
 * @package    ezoic
 */

/**
 * Hook class.
 */
class Hook_privacy_ezoic extends Source_hook_privacy_base
{
    /**
     * Find privacy details.
     *
     * @return ?array A map of privacy details in a standardised format (null: disabled)
     */
    public function info() : ?array
    {
        if (!addon_installed('ezoic')) {
            return null;
        }

        require_code('http');

        $ezoic_url = 'https://g.ezoic.net/privacy/' . get_base_url_hostname();
        $ezoic_data = cache_and_carry('cms_http_request', [$ezoic_url, []], (60 * 24));
        $ezoic_pp = new Tempcode();
        if ((is_array($ezoic_data)) && ($ezoic_data[0] !== null) && ($ezoic_data[4] == '200')) {
            $ezoic_pp->attach(strip_html($ezoic_data[0]));
        }

        return [
            'label' => 'ezoic:EZOIC',

            'description' => 'ezoic:DESCRIPTION_PRIVACY_EZOIC',

            'cookies' => [
            ],

            'positive' => [
                ((!$ezoic_pp->is_empty()) ? [
                    'heading' => do_lang('INFORMATION_DISCLOSURE'),
                    'explanation' => $ezoic_pp,
                ] : []),
            ],

            'general' => [
            ],

            'database_records' => [
            ],
        ];
    }
}
