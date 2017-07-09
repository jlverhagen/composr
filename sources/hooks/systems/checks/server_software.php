<?php /*

 Composr
 Copyright (c) ocProducts, 2004-2017

 See text/EN/licence.txt for full licencing information.


 NOTE TO PROGRAMMERS:
   Do not edit this file. If you need to make changes, save your changed file to the appropriate *_custom folder
   **** If you ignore this advice, then your website upgrades (e.g. for bug fixes) will likely kill your changes ****

*/

/**
 * @license    http://opensource.org/licenses/cpal_1.0 Common Public Attribution License
 * @copyright  ocProducts Ltd
 * @package    core
 */

/**
 * Hook class.
 */
class Hook_check_server_software
{
    /**
     * Check various input var restrictions.
     *
     * @return array List of warnings
     */
    public function run()
    {
        $warning = array();

        $server_software = $_SERVER['SERVER_SOFTWARE'];

        /*$unsupported_server_software = array('lighttpd', 'Tengine', 'nginx', 'IdeaWebServer');
        foreach ($unsupported_server_software as $_server_software) {
            if (stripos($server_software, $_server_software) !== false) {
                $warning[] = do_lang_tempcode('WARNING_SERVER_SOFTWARE', escape_html($_server_software));
            }
        }*/

        if (count($warning) == 0) {
            $supported_server_software = array('LiteSpeed', 'Apache', 'Microsoft-IIS');
            $supported = false;
            foreach ($supported_server_software as $_server_software) {
                if (stripos($server_software, $_server_software) !== false) {
                    $supported = true;
                }
            }
            if (!$supported) {
                $warning[] = do_lang_tempcode('WARNING_SERVER_SOFTWARE', escape_html($server_software));
            }
        }

        if ((!is_maintained('platform_litespeed')) && (stripos($server_software, 'LiteSpeed') !== false)) {
            $warning[] = do_lang_tempcode('WARNING_NON_MAINTAINED', escape_html('LiteSpeed'), escape_html(get_brand_base_url()), escape_html('platform_litespeed'));
        }

        if ((!is_maintained('platform_iis')) && (stripos($server_software, 'Microsoft-IIS') !== false)) {
            $warning[] = do_lang_tempcode('WARNING_NON_MAINTAINED', escape_html('Microsoft IIS'), escape_html(get_brand_base_url()), escape_html('platform_iis'));
        }

        return $warning;
    }
}
