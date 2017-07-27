<?php /*

 Composr
 Copyright (c) ocProducts/Tapatalk, 2004-2016

 See text/EN/licence.txt for full licencing information.

*/

/**
 * @license    http://opensource.org/licenses/cpal_1.0 Common Public Attribution License
 * @copyright  ocProducts Ltd
 * @package    cns_tapatalk
 */

/**
 * Standard Tapatalk endpoint test.
 * Never use on a live site.
 * Call via, and works with, our API in test_functions.php.
 */
function get_real_url_test()
{
    $result = mobiquo_post_simple_call(
        preg_replace('#_test$#', '', __FUNCTION__),
        array(
            'post',
            '4',
        )
    );
    print_mobiquo_result($result);
}

/**
 * Standard Tapatalk endpoint test.
 * Never use on a live site.
 * Call via, and works with, our API in test_functions.php.
 */
function set_api_key_test()
{
    $result = mobiquo_post_simple_call(
        preg_replace('#_test$#', '', __FUNCTION__),
        array(
            'code' => '456',
            'key' => 'abc',
        )
    );
    print_mobiquo_result($result);
}

/**
 * Standard Tapatalk endpoint test.
 * Never use on a live site.
 * Call via, and works with, our API in test_functions.php.
 */
function verify_connection_test()
{
    $result = mobiquo_post_simple_call(
        preg_replace('#_test$#', '', __FUNCTION__),
        array(
            'format' => 'serialize',
            'type' => 'both',
            'code' => '456',
        )
    );
    print_mobiquo_result($result);
}
