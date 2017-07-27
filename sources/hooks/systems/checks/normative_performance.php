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
 * @package    core
 */

/**
 * Hook class.
 */
class Hook_check_normative_performance
{
    /**
     * Check various input var restrictions.
     *
     * @return array List of warnings
     */
    public function run()
    {
        $warning = array();
        require_code('global4');
        $percentage = find_normative_performance();
        if ($percentage <= 4.0) {
            $warning[] = do_lang_tempcode('SLOW_SERVER', escape_html(float_format($percentage, 1)));
        }

        return $warning;
    }
}
