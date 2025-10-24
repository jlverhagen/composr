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

// This test may fail because Google Geocoding is no longer free

/**
 * Composr test case class (unit testing).
 */
class __api_geocoding_test_set extends cms_test_case
{
    public function setUp()
    {
        parent::setUp();

        set_option('google_geocoding_api_enabled', '1');
        $this->load_key_options('mapquest');
        $this->load_key_options('bing');
        $this->load_key_options('google');
    }

    public function testIPGeocode()
    {
        if (get_db_type() == 'xml') {
            $this->assertTrue(false, 'Cannot run with XML database driver, too slow');
            return;
        }

        require_code('locations');

        $test = has_geolocation_data(); // Debugging note: Should be about 253k rows in this table
        $has_geolocation_data = ($test !== null);
        if (!$has_geolocation_data) {
            require_code('tasks');
            require_lang('stats');
            call_user_func_array__long_task(do_lang('INSTALL_GEOLOCATION_DATA'), get_screen_title('INSTALL_GEOLOCATION_DATA'), 'install_geolocation_data', [], false, true);
        }

        $country = geolocate_ip('217.160.72.6');
        $this->assertTrue(geolocate_ip('217.160.72.6') == 'DE', 'Expected DE got ' . (($country === null) ? '(unknown)' : $country));
    }

    public function testGeocode()
    {
        if (in_safe_mode()) {
            $this->assertTrue(false, 'Cannot work in safe mode');
            return;
        }

        $this->run_health_check('API connections', 'Geocoding');
    }
}
