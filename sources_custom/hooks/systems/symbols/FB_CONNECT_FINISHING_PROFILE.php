<?php /*

 Composr
 Copyright (c) ocProducts, 2004-2016

 See text/EN/licence.txt for full licencing information.

*/

/**
 * @license    http://opensource.org/licenses/cpal_1.0 Common Public Attribution License
 * @copyright  ocProducts Ltd
 * @package    facebook_support
 */

/**
 * Hook class.
 */
class Hook_symbol_FB_CONNECT_FINISHING_PROFILE
{
    public function run($param)
    {
        require_code('facebook_connect');

        if (isset($GLOBALS['FACEBOOK_FINISHING_PROFILE'])) {
            return '1';
        }
        return '0';
    }
}
