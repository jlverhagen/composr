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
function mark_topic_read_test()
{
    $result = mobiquo_xmlrpc_simple_call(
        preg_replace('#_test$#', '', __FUNCTION__),
        array(
            array('1'),
        )
    );
    print_mobiquo_result($result);
}

/**
 * Standard Tapatalk endpoint test.
 * Never use on a live site.
 * Call via, and works with, our API in test_functions.php.
 */
function get_topic_status_test()
{
    $result = mobiquo_xmlrpc_simple_call(
        preg_replace('#_test$#', '', __FUNCTION__),
        array(
            array('1', '2', '4'),
        )
    );
    print_mobiquo_result($result);
}

/**
 * Standard Tapatalk endpoint test.
 * Never use on a live site.
 * Call via, and works with, our API in test_functions.php.
 */
function new_topic_test()
{
    $result = mobiquo_xmlrpc_simple_call(
        preg_replace('#_test$#', '', __FUNCTION__),
        array(
            '5',
            '%t_subject',
            '%t_body',
            '2',
            array('3', '6'),
            '1',
        )
    );
    print_mobiquo_result($result);
}

/**
 * Standard Tapatalk endpoint test.
 * Never use on a live site.
 * Call via, and works with, our API in test_functions.php.
 */
function get_topic_test() // Get topics in a forum
{
    $result = mobiquo_xmlrpc_simple_call(
        preg_replace('#_test$#', '', __FUNCTION__),
        array(
            '2',
            0,
            2,
            '', // Standard topics
        )
    );
    print_mobiquo_result($result);
}

/**
 * Standard Tapatalk endpoint test.
 * Never use on a live site.
 * Call via, and works with, our API in test_functions.php.
 */
function get_unread_topic_test()
{
    $result = mobiquo_xmlrpc_simple_call(
        preg_replace('#_test$#', '', __FUNCTION__),
        array(
            0,
            5,
            '1',
            array("only_in = {1,2,5,6}"),
        )
    );
    print_mobiquo_result($result);
}

/**
 * Standard Tapatalk endpoint test.
 * Never use on a live site.
 * Call via, and works with, our API in test_functions.php.
 */
function get_participated_topic_test()
{
    $result = mobiquo_xmlrpc_simple_call(
        preg_replace('#_test$#', '', __FUNCTION__),
        array(
            '%admin',
            0,
            2,
            '2',
            '2'
        )
    );
    print_mobiquo_result($result);
}

/**
 * Standard Tapatalk endpoint test.
 * Never use on a live site.
 * Call via, and works with, our API in test_functions.php.
 */
function get_latest_topic_test()
{
    $result = mobiquo_xmlrpc_simple_call(
        preg_replace('#_test$#', '', __FUNCTION__),
        array(
            0,
            2,
            '2',
        )
    );
    print_mobiquo_result($result);
}

/**
 * Standard Tapatalk endpoint test.
 * Never use on a live site.
 * Call via, and works with, our API in test_functions.php.
 */
function get_topic_participants_test()
{
    $result = mobiquo_xmlrpc_simple_call(
        preg_replace('#_test$#', '', __FUNCTION__),
        array(
            '20',
            15
        )
    );
    print_mobiquo_result($result);
}
