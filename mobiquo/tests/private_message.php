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
function report_pm_test()
{
    $result = mobiquo_xmlrpc_simple_call(
        preg_replace('#_test$#', '', __FUNCTION__),
        array(
            '14',
            '%report_reason'
        )
    );
    print_mobiquo_result($result);
}

/**
 * Standard Tapatalk endpoint test.
 * Never use on a live site.
 * Call via, and works with, our API in test_functions.php.
 */
function create_message_test()
{
    $result = mobiquo_xmlrpc_simple_call(
        preg_replace('#_test$#', '', __FUNCTION__),
        array(
            array('%admin'),
            '%test_subject',
            '%test_text_body',
            1, // Reply
            '14'
        )
    );
    print_mobiquo_result($result);
}

/**
 * Standard Tapatalk endpoint test.
 * Never use on a live site.
 * Call via, and works with, our API in test_functions.php.
 */
function get_box_info_test()
{
    $result = mobiquo_xmlrpc_simple_call(
        preg_replace('#_test$#', '', __FUNCTION__),
        array()
    );
    print_mobiquo_result($result);
}

/**
 * Standard Tapatalk endpoint test.
 * Never use on a live site.
 * Call via, and works with, our API in test_functions.php.
 */
function get_box_test()
{
    $result = mobiquo_xmlrpc_simple_call(
        preg_replace('#_test$#', '', __FUNCTION__),
        array(
            '2',
            1,
            5
        )
    );
    print_mobiquo_result($result);
}

/**
 * Standard Tapatalk endpoint test.
 * Never use on a live site.
 * Call via, and works with, our API in test_functions.php.
 */
function get_message_test()
{
    $result = mobiquo_xmlrpc_simple_call(
        preg_replace('#_test$#', '', __FUNCTION__),
        array(
            '15',
            '5',
            true
        )
    );
    print_mobiquo_result($result);
}

/**
 * Standard Tapatalk endpoint test.
 * Never use on a live site.
 * Call via, and works with, our API in test_functions.php.
 */
function get_quote_pm_test()
{
    $result = mobiquo_xmlrpc_simple_call(
        preg_replace('#_test$#', '', __FUNCTION__),
        array(
            '15'
        )
    );
    print_mobiquo_result($result);
}

/**
 * Standard Tapatalk endpoint test.
 * Never use on a live site.
 * Call via, and works with, our API in test_functions.php.
 */
function delete_message_test()
{
    $result = mobiquo_xmlrpc_simple_call(
        preg_replace('#_test$#', '', __FUNCTION__),
        array(
            '73',
            '5'
        )
    );
    print_mobiquo_result($result);
}

/**
 * Standard Tapatalk endpoint test.
 * Never use on a live site.
 * Call via, and works with, our API in test_functions.php.
 */
function mark_pm_unread_test()
{
    $result = mobiquo_xmlrpc_simple_call(
        preg_replace('#_test$#', '', __FUNCTION__),
        array(
            '72'
        )
    );
    print_mobiquo_result($result);
}

/**
 * Standard Tapatalk endpoint test.
 * Never use on a live site.
 * Call via, and works with, our API in test_functions.php.
 */
function mark_pm_read_test()
{
    $result = mobiquo_xmlrpc_simple_call(
        preg_replace('#_test$#', '', __FUNCTION__),
        array(
            '72'
        )
    );
    print_mobiquo_result($result);
}
