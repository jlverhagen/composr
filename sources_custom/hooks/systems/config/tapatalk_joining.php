<?php /*

 Composr
 Copyright (c) ocProducts, 2004-2016

 See text/EN/licence.txt for full licencing information.

*/

/**
 * @license    http://opensource.org/licenses/cpal_1.0 Common Public Attribution License
 * @copyright  ocProducts Ltd
 * @package    cns_tapatalk
 */

/**
 * Hook class.
 */
class Hook_config_tapatalk_joining
{
    /**
     * Gets the details relating to the config option.
     *
     * @return ?array The details (null: disabled)
     */
    public function get_details()
    {
        return array(
            'human_name' => 'TAPATALK_JOINING',
            'type' => 'tick',
            'category' => 'COMPOSR_APIS',
            'group' => 'TAPATALK',
            'explanation' => 'CONFIG_OPTION_tapatalk_joining',
            'shared_hosting_restricted' => '0',
            'list_options' => '',

            'addon' => 'cns_tapatalk',
        );
    }

    /**
     * Gets the default value for the config option.
     *
     * @return ?string The default value (null: option is disabled)
     */
    public function get_default()
    {
        return '1';
    }
}
