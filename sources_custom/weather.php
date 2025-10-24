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
 * @package    weather
 */

/**
 * Standard code module initialisation function.
 *
 * @ignore
 */
function init__weather()
{
    require_lang('weather');
}

/**
 * Lookup weather for a location.
 *
 * @param  ?string $location_search Location (null: use $latitude and $longitude)
 * @param  ?float $latitude Latitude (null: use $location_search)
 * @param  ?float $longitude Longitude (null: use $location_search)
 * @param  string $units Units to use
 * @set imperial metric
 * @param  ?integer $max_days Maximum number of days to return if supported (null: no limit)
 * @param  ?string $api The API to use (null: first available)
 * @return ?array A pair: Weather API current conditions in standardised simple format, Weather API forecast in standardised simple format (null: not available)
 */
function weather_lookup(?string $location_search = null, ?float $latitude = null, ?float $longitude = null, string $units = 'metric', ?int $max_days = null, ?string $api = null) : ?array
{
    if ($location_search === '') {
        $location_search = null;
    }

    if ($location_search === null) {
        if (($latitude === null) || ($longitude === null)) {
            $errormsg = do_lang('NO_LOCATION_SPECIFIED');
            throw new Exception($errormsg);
        }
    }

    $hook_obs = find_all_hook_obs('systems', 'weather', 'Hook_weather_');
    foreach ($hook_obs as $hook => $ob) {
        if (($api === null) || ($hook == $api)) {
            $result = $ob->lookup($location_search, $latitude, $longitude, $units, $max_days);
            if ($result !== null) {
                return $result;
            }
        }
    }

    $errormsg = do_lang('API_NOT_CONFIGURED', '(any weather)');
    throw new Exception($errormsg);
}
