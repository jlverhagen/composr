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
class rate_limiting_test_set extends cms_test_case
{
    public function testRateLimitingWorks()
    {
        cms_extend_time_limit(TIME_LIMIT_EXTEND__MODEST);

        $config_file_path = get_file_base() . '/_config.php';
        $config_file = cms_file_get_contents_safe($config_file_path, FILE_READ_LOCK);
        file_put_contents($config_file_path, $config_file . "\n\n\$SITE_INFO['rate_limiting'] = '1';\n\$SITE_INFO['rate_limit_time_window'] = '60';\n\$SITE_INFO['rate_limit_hits_per_window'] = '3';");
        fix_permissions($config_file_path);

        $rate_limiter_path = get_custom_file_base() . '/data_custom/rate_limiting/' . str_replace(['.', ':'], ['_', '-'], get_ip_address()) . '.json';

        $url = build_url(['page' => ''], '');
        for ($i = 0; $i < 4; $i++) {
            $result = cms_http_request($url->evaluate(), ['trigger_error' => false, 'timeout' => 8.0]);
            if ($i < 3) {
                $this->assertTrue($result->data !== null, 'Iteration ' . strval($i) . ' expected data but did not get any.');
                $this->assertTrue($result->message === '200', 'Iteration ' . strval($i) . ' expected status code 200 but got ' . $result->message . '.');
            } else {
                $this->assertTrue($result->data === null, 'Iteration ' . strval($i) . ' expected NO data (rate limit) but got some.');
                $this->assertTrue($result->message === '429', 'Iteration ' . strval($i) . ' expected status code 429 (rate limit) but got ' . $result->message . '.');
            }

            if ($this->debug) {
                $this->dump($result->data, 'Iteration ' . strval($i));
            }
        }

        // Output contents of rate limit file if debugging
        if ($this->debug) {
            $test = cms_file_get_contents_safe($rate_limiter_path, FILE_READ_LOCK);
            var_dump($test);
        }

        file_put_contents($config_file_path, $config_file);
        //unlink($rate_limiter_path);
    }
}
