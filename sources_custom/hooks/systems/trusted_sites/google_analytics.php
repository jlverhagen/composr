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
 * @package    google_analytics
 */

/*FORCE_ORIGINAL_LOAD_FIRST*/

/**
 * Hook class.
 */
class Hx_trusted_sites_google_analytics extends Hook_trusted_sites_google_analytics
{
    /**
     * Detect what needs to be 'added' to the trusted_sites_2 option.
     *
     * @param  array $sites List of trusted sites (written by reference)
     */
    public function find_trusted_sites_2(array &$sites)
    {
        parent::find_trusted_sites_2($sites);

        if (!addon_installed('google_analytics')) {
            return;
        }

        if ((get_option('ga_property_view_id') != '') && (get_option('google_apis_client_id') != '') && (get_option('google_apis_client_secret') != '')) {
            $sites[] = 'apis.google.com';
            $sites[] = 'stats.g.doubleclick.net';
            $sites[] = 'google-analytics.com';
        }
    }
}
