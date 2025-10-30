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
class Hook_cron_hybridauth_admin
{
    /**
     * Get info from this hook.
     *
     * @param  ?TIME $last_run Last time run (null: never)
     * @param  ?boolean $calculate_num_queued Calculate the number of items queued, if possible (null: the hook may decide / low priority)
     * @return ?array Return a map of info about the hook (null: disabled)
     */
    public function info(?int $last_run, ?bool $calculate_num_queued) : ?array
    {
        if (!addon_installed('hybridauth')) {
            return null;
        }

        if (!function_exists('curl_init')) {
            return null;
        }

        return [
            'label' => 'Refresh Hybridauth tokens',
            'num_queued' => null,
            'minutes_between_runs' => 24 * 60 * 30,
            'enabled_by_default' => true,
        ];
    }

    /**
     * Run function for system scheduler hooks. Searches for things to do. ->info(..., true) must be called before this method.
     *
     * @param  ?TIME $last_run Last time run (null: never)
     */
    public function run(?int $last_run)
    {
        $before_type_strictness = ini_get('ocproducts.type_strictness');
        cms_ini_set('ocproducts.type_strictness', '0');
        $before_xss_detect = ini_get('ocproducts.xss_detect');
        cms_ini_set('ocproducts.xss_detect', '0');

        require_code('hybridauth_admin');

        list($hybridauth, , $providers) = initiate_hybridauth_admin(HYBRIDAUTH__REQUIRES_TOKEN_MAINTENANCE);

        foreach (array_keys($providers) as $provider) {
            try {
                $adapter = $hybridauth->getAdapter($provider);

                try {
                    $adapter->maintainToken();
                } catch (\Hybridauth\Exception\AccessDeniedException $e) {
                    require_code('failure');
                    cms_error_log($e->getMessage(), 'error_occurred_api');
                    $adapter->disconnect();
                } catch (Exception $e) {
                    require_code('failure');
                    cms_error_log($e->getMessage(), 'error_occurred_api');
                }
            } catch (Exception $e) {
                require_code('failure');
                cms_error_log($e->getMessage(), 'error_occurred_api');
            }
        }

        if ($before_type_strictness !== false) {
            cms_ini_set('ocproducts.type_strictness', $before_type_strictness);
        }
        if ($before_xss_detect !== false) {
            cms_ini_set('ocproducts.xss_detect', $before_xss_detect);
        }
    }
}
