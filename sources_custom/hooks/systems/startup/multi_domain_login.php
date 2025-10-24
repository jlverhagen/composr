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
 * @package    multi_domain_login
 */

/**
 * Hook class.
 */
class Hook_startup_multi_domain_login
{
    public function run($MICRO_BOOTUP, $MICRO_AJAX_BOOTUP)
    {
        if (!addon_installed('multi_domain_login')) {
            return;
        }

        if ((!$MICRO_AJAX_BOOTUP) && (!$MICRO_BOOTUP) && (running_script('index'))) {
            //if (isset($_POST['username'])) return;  Actually, we'll use caching to avoid this

            $value = '';
            //$url = $this->session_syndicate_code(get_request_hostname(), preg_replace('#^.*://[^/]*(/|$)#', '', get_base_url()));
            //$value .= 'new Image().src=\'' . addslashes($url) . '\';';
            foreach ($GLOBALS['SITE_INFO'] as $key => $_val) {
                if (@$key[0] == 'Z' && substr($key, 0, strlen('ZONE_MAPPING_')) == 'ZONE_MAPPING_') {
                    if ($_val[0] != get_request_hostname()) {
                        $url = $this->session_syndicate_code($_val[0], $_val[1]);
                        $value .= 'new Image().src=\'' . addslashes($url) . '\';';
                    }
                }
            }
            if ($value != '') {
                $value = "<!-- Syndicate sessions -->\n<script " . csp_nonce_html() . ">" . $value . "</script>\n\n";

                attach_to_screen_header($value);
            }
        }
    }

    protected function session_syndicate_code($domain, $path)
    {
        $url = 'https://' . $domain . '/' . $path . (($path == '') ? '' : '/') . 'data_custom/multi_domain_login.php';
        $url .= '?session_expiry_time=' . urlencode(get_option('session_expiry_time'));
        $url .= '&session_id=' . urlencode(get_session_id());
        $url .= '&guest_session=' . (is_guest() ? '1' : '0');
        return $url;
    }
}
