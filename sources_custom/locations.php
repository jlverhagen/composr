<?php /*

 Composr
 Copyright (c) ocProducts, 2004-2016

 See text/EN/licence.txt for full licencing information.

*/

/**
 * @license    http://opensource.org/licenses/cpal_1.0 Common Public Attribution License
 * @copyright  ocProducts Ltd
 * @package    world_regions
 */

/**
 * Get a nice, formatted XHTML list of regions
 *
 * @param  ?array $regions The currently selected regions (null: none selected)
 * @return Tempcode The list of regions
 */
function create_region_selection_list($regions = null)
{
    if (is_null($regions)) {
        $regions = array();
    }

    require_code('locations/us');

    require_code('locations');
    $continents_and_countries = find_continents_and_countries();

    $list_groups = new Tempcode();
    foreach ($continents_and_countries as $continent => $countries) {
        $list = new Tempcode();
        foreach ($countries as $country_code => $country_name) {
            if ($country_code == 'US') {
                $list_groups->attach(form_input_list_group($continent, $list));
                $list = new Tempcode();

                foreach (get_region_structure_US() as $usa_region => $usa_states) {
                    $usa_region_list = new Tempcode();
                    foreach ($usa_states as $usa_state_code => $usa_state_name) {
                        $usa_region_list->attach(form_input_list_entry('US_' . $usa_state_code, in_array('US_' . $usa_state_code, $regions), $usa_state_name));
                    }
                    $list_groups->attach(form_input_list_group($continent . ' \\ ' . $country_name . ' \\ ' . $usa_region, $usa_region_list));
                }

                $list->attach(form_input_list_entry($country_code, in_array($country_code, $regions), $country_name . ' (unknown state)'));
            } else {
                $list->attach(form_input_list_entry($country_code, in_array($country_code, $regions), $country_name));
            }
        }
        $list_groups->attach(form_input_list_group($continent, $list));
    }
    return $list_groups;
}

/**
 * Find the active region for the current user.
 * Function likely to be overridden if a region scheme more complex than ISO countries is in use. E.g. to detect via considering state CPF also.
 *
 * @return ?string The active region (null: none found, unfiltered)
 */
function get_region()
{
    $region = get_param_string('keep_region', null);
    if ($region !== null) {
        if ($region == '') {
            return null;
        }
        return $region;
    }

    $ret = get_country();

    if ($ret == 'US') {
        require_code('locations/us');

        if (!is_guest()) {
            $state = get_cms_cpf('state');
            if (!empty($state)) {
                return 'US_' . $state;
            }
        }
        return $region;
    }

    return $ret;
}
