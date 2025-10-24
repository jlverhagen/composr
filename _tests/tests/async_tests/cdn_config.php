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
 * @package    testing_platform
 */

/**
 * Composr test case class (unit testing).
 */
class cdn_config_test_set extends cms_test_case
{
    public function testCDNConfig()
    {
        $hostname = get_base_url_hostname();
        $server_ips = get_server_ips();
        $ip_address = $server_ips[0];

        if ($hostname == $ip_address) {
            $this->assertTrue(false, 'Test can only run from a hostname.');
        }

        require_code('images');

        $_cdn_config = $hostname . ',' . $ip_address;
        set_option('cdn', $_cdn_config);

        if ($this->debug) {
            var_dump($_cdn_config);
        }

        $a = false;
        $b = false;

        $path = get_file_base() . '/themes/default/images';
        $dh = opendir($path);
        if ($dh !== false) {
            while (($file = readdir($dh)) !== false) {
                if (is_image($file, IMAGE_CRITERIA_WEBSAFE)) {
                    $ext = get_file_extension($file);
                    $url = find_theme_image(basename($file, '.' . $ext), false, false, 'default');
                    if (strpos($url, $hostname) !== false) {
                        $a = true;
                    }
                    if (strpos($url, $ip_address) !== false) {
                        $b = true;
                    }
                }
            }
            closedir($dh);
        }

        $this->assertTrue($a && $b);

        set_option('cdn', '');
    }
}
