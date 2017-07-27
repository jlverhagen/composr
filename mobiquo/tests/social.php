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
function thank_post_test()
{
    $result = mobiquo_xmlrpc_simple_call(
        preg_replace('#_test$#', '', __FUNCTION__),
        array(
            '1',
        ),
        'admin' // Log in as admin. Only works with a backdoored IP
    );
    print_mobiquo_result($result);
}

/**
 * Standard Tapatalk endpoint test.
 * Never use on a live site.
 * Call via, and works with, our API in test_functions.php.
 */
function follow_test()
{
    $result = mobiquo_xmlrpc_simple_call(
        preg_replace('#_test$#', '', __FUNCTION__),
        array(
            '3',
        ),
        'admin' // Log in as admin. Only works with a backdoored IP
    );
    print_mobiquo_result($result);
}

/**
 * Standard Tapatalk endpoint test.
 * Never use on a live site.
 * Call via, and works with, our API in test_functions.php.
 */
function unfollow_test()
{
    $result = mobiquo_xmlrpc_simple_call(
        preg_replace('#_test$#', '', __FUNCTION__),
        array(
            '2',
        ),
        'admin' // Log in as admin. Only works with a backdoored IP
    );
    print_mobiquo_result($result);
}

/**
 * Standard Tapatalk endpoint test.
 * Never use on a live site.
 * Call via, and works with, our API in test_functions.php.
 */
function like_post_test()
{
    $result = mobiquo_xmlrpc_simple_call(
        preg_replace('#_test$#', '', __FUNCTION__),
        array(
            '1',
        ),
        'admin' // Log in as admin. Only works with a backdoored IP
    );
    print_mobiquo_result($result);
}

/**
 * Standard Tapatalk endpoint test.
 * Never use on a live site.
 * Call via, and works with, our API in test_functions.php.
 */
function unlike_post_test()
{
    $result = mobiquo_xmlrpc_simple_call(
        preg_replace('#_test$#', '', __FUNCTION__),
        array(
            '5',
        ),
        'admin' // Log in as admin. Only works with a backdoored IP
    );
    print_mobiquo_result($result);
}

/**
 * Standard Tapatalk endpoint test.
 * Never use on a live site.
 * Call via, and works with, our API in test_functions.php.
 */
function get_following_test()
{
    $result = mobiquo_xmlrpc_simple_call(
        preg_replace('#_test$#', '', __FUNCTION__),
        array(),
        'admin' // Log in as admin. Only works with a backdoored IP
    );
    print_mobiquo_result($result);
}

/**
 * Standard Tapatalk endpoint test.
 * Never use on a live site.
 * Call via, and works with, our API in test_functions.php.
 */
function get_follower_test()
{
    $result = mobiquo_xmlrpc_simple_call(
        preg_replace('#_test$#', '', __FUNCTION__),
        array(),
        'admin' // Log in as admin. Only works with a backdoored IP
    );
    print_mobiquo_result($result);
}

/**
 * Standard Tapatalk endpoint test.
 * Never use on a live site.
 * Call via, and works with, our API in test_functions.php.
 */
function get_alert_test()
{
    $result = mobiquo_xmlrpc_simple_call(
        preg_replace('#_test$#', '', __FUNCTION__),
        array(),
        'admin' // Log in as admin. Only works with a backdoored IP
    );
    print_mobiquo_result($result);
}

/**
 * Standard Tapatalk endpoint test.
 * Never use on a live site.
 * Call via, and works with, our API in test_functions.php.
 */
function get_activity_test()
{
    $result = mobiquo_xmlrpc_simple_call(
        preg_replace('#_test$#', '', __FUNCTION__),
        array(),
        'admin' // Log in as admin. Only works with a backdoored IP
    );
    print_mobiquo_result($result);
}
