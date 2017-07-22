<?php /*

 Composr
 Copyright (c) ocProducts, 2004-2016

 See text/EN/licence.txt for full licencing information.


 NOTE TO PROGRAMMERS:
   Do not edit this file. If you need to make changes, save your changed file to the appropriate *_custom folder
   **** If you ignore this advice, then your website upgrades (e.g. for bug fixes) will likely kill your changes ****

*/

/**
 * @license    http://opensource.org/licenses/cpal_1.0 Common Public Attribution License
 * @copyright  ocProducts Ltd
 * @package    health_check
 */

/**
 * Hook class.
 */
class Hook_config_hc_cron_sections_to_run
{
    /**
     * Gets the details relating to the config option.
     *
     * @return ?array The details (null: disabled)
     */
    public function get_details()
    {
        return array(
            'human_name' => 'HC_CRON_SECTIONS_TO_RUN',
            'type' => 'special',
            'category' => 'HEALTH_CHECK',
            'group' => 'AUTOMATIC_CHECKS',
            'explanation' => 'CONFIG_OPTION_hc_cron_sections_to_run',
            'shared_hosting_restricted' => '0',
            'list_options' => '',
            'order_in_category_group' => 1,
            'required' => false,

            'addon' => 'health_check',
        );
    }

    /**
     * Gets the default value for the config option.
     *
     * @return ?string The default value (null: option is disabled)
     */
    public function get_default()
    {
        return '';
    }

    /**
     * Field inputter (because the_type=special).
     *
     * @param  ID_TEXT $name The config option name
     * @param  array $myrow The config row
     * @param  Tempcode $human_name The field title
     * @param  Tempcode $explanation The field description
     * @return Tempcode The inputter
     */
    public function field_inputter($name, $myrow, $human_name, $explanation)
    {
        $current = explode(',', get_option($name));

        $list = '';
        require_code('health_check');
        $categories = find_health_check_categories_and_sections();
        foreach ($categories as $category_label => $results) {
            foreach (array_keys($results) as $section_label) {
                $compound_label = $category_label . '/' . $section_label;
                $is_selected = in_array($compound_label, $current);
                $list .= static_evaluate_tempcode(form_input_list_entry($compound_label, $is_selected));
            }
        }
        return form_input_list($human_name, $explanation, $name, make_string_tempcode($list), null, 15);
    }
}
