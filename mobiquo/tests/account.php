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
function sign_in_test()
{
    $result = mobiquo_xmlrpc_simple_call(
        preg_replace('#_test$#', '', __FUNCTION__),
        array(
            '8f982156b2e84312eceadeeae3c7a334',
            'e47c2cb8d8f0c9ec6a66ea1b6c0bff2f',
            '%gfgfda@example.com',
            '%admdinx',
            '%xxxdxx',
            array('cms_city' => 'test value'),
        )
    );
    print_mobiquo_result($result);
}

/**
 * Standard Tapatalk endpoint test.
 * Never use on a live site.
 * Call via, and works with, our API in test_functions.php.
 */
function forget_password_test()
{
    $result = mobiquo_xmlrpc_simple_call(
        preg_replace('#_test$#', '', __FUNCTION__),
        array(
            '%adminx',
            '',
            '',
        )
    );
    print_mobiquo_result($result);
}

/**
 * Standard Tapatalk endpoint test.
 * Never use on a live site.
 * Call via, and works with, our API in test_functions.php.
 */
function update_password_test()
{
    $result = mobiquo_xmlrpc_simple_call(
        preg_replace('#_test$#', '', __FUNCTION__),
        array(
            '%',
            '%',
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
function update_email_test()
{
    $result = mobiquo_xmlrpc_simple_call(
        preg_replace('#_test$#', '', __FUNCTION__),
        array(
            '%',
            '%b@example.com',
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
function register_test()
{
    $result = mobiquo_xmlrpc_simple_call(
        preg_replace('#_test$#', '', __FUNCTION__),
        array(
            '%abcd',
            '%abcd',
            '%abcd@example.com',
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
function prefetch_account_test()
{
    $result = mobiquo_xmlrpc_simple_call(
        preg_replace('#_test$#', '', __FUNCTION__),
        array(
            '%abcd@example.com',
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
function sync_user_test()
{
    $result = mobiquo_post_simple_call(
        preg_replace('#_test$#', '', __FUNCTION__),
        array(
            'code' => '1234',
        )
    );
    print_mobiquo_result($result);
}
