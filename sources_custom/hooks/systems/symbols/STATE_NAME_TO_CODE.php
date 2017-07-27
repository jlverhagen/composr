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
 * Hook class.
 */
class Hook_symbol_STATE_NAME_TO_CODE
{
    /**
     * Run function for symbol hooks. Searches for tasks to perform.
     *
     * @param  array $param Symbol parameters
     * @return string Result
     */
    public function run($param)
    {
        $value = '';

        if (!empty($param[0])) {
            require_code('locations/us');
            $value = find_state_code_from_name_US($param[0]);
        }

        return $value;
    }
}
