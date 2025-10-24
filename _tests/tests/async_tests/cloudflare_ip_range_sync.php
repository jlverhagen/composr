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
class cloudflare_ip_range_sync_test_set extends cms_test_case
{
    public function testInSync()
    {
        $current = '';
        $current .= trim(unixify_line_format(http_get_contents('https://www.cloudflare.com/ips-v4', ['convert_to_internal_encoding' => true])));
        $current .= "\n";
        $current .= trim(unixify_line_format(http_get_contents('https://www.cloudflare.com/ips-v6', ['convert_to_internal_encoding' => true])));
        $current = str_replace("\n", ',', $current);

        $c = cms_file_get_contents_safe(get_file_base() . '/sources/global.php', FILE_READ_LOCK);
        $matches = [];
        preg_match('#\$trusted_proxies = \'([^\']*)\';#', $c, $matches);
        $in_code = $matches[1];

        $this->assertTrue($in_code == $current, 'Expected ' . $current . ' in sources/global.php, got ' . $in_code);
    }
}
