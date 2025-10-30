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
class Hook_login_provider_hybridauth
{
    /**
     * Standard login provider hook.
     *
     * @param  ?MEMBER $member_id Member ID already detected as logged in (null: none). May be a guest ID.
     * @return ?MEMBER Member ID now detected as logged in (null: none). May be a guest ID.
     */
    public function try_login(?int $member_id) : ?int
    {
        if (get_forum_type() != 'cns') {
            return $member_id;
        }

        if (!addon_installed('hybridauth')) {
            return $member_id;
        }

        if (!function_exists('curl_init')) {
            return $member_id;
        }

        // Too early in bootstrapping
        if (!function_exists('require_lang')) {
            return $member_id;
        }

        if ((($member_id === null) || (is_guest($member_id))) && (!running_script('hybridauth')) && (get_page_name() == 'join') || (currently_logging_in())) {
            require_code('hybridauth');
            require_lang('hybridauth');

            $before_type_strictness = ini_get('ocproducts.type_strictness');
            cms_ini_set('ocproducts.type_strictness', '0');
            $before_xss_detect = ini_get('ocproducts.xss_detect');
            cms_ini_set('ocproducts.xss_detect', '0');

            $hybridauth = initiate_hybridauth();

            // Log back in whatever is still connected
            if (isset($_SESSION['provider'])) {
                $provider = $_SESSION['provider'];

                try {
                    $adapter = $hybridauth->getAdapter($provider);

                    $success = $adapter->isConnected();
                    if ($success) {
                        if ((get_page_name() == 'login') && (get_param_string('type', 'browse') == 'logout')) {
                            $adapter->disconnect();
                        } else {
                            $userProfile = $adapter->getUserProfile();
                            $member_id = hybridauth_handle_authenticated_account($provider, $userProfile);
                        }
                    }
                } catch (Exception $e) {
                    $adapter->disconnect();
                    // Silent failure; user can always start to log in again. Maybe a revoked token for example
                }
            }

            if ($before_type_strictness !== false) {
                cms_ini_set('ocproducts.type_strictness', $before_type_strictness);
            }
            if ($before_xss_detect !== false) {
                cms_ini_set('ocproducts.xss_detect', $before_xss_detect);
            }
        }

        return $member_id;
    }
}
