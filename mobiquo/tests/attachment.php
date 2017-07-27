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
function upload_attach_test()
{
    $result = mobiquo_post_simple_call(
        preg_replace('#_test$#', '', __FUNCTION__),
        array(
            'method_name' => 'upload_attach',
        ),
        'admin',
        array('content[]' => get_file_base() . '/themes/default/images/cns_default_avatars/default_set/cartoons/crazy.jpg')
    );
    print_mobiquo_result($result);
}

/**
 * Standard Tapatalk endpoint test.
 * Never use on a live site.
 * Call via, and works with, our API in test_functions.php.
 */
function upload_avatar_test()
{
    $result = mobiquo_post_simple_call(
        preg_replace('#_test$#', '', __FUNCTION__),
        array(
            'method_name' => 'upload_avatar',
        ),
        'admin',
        array('content[]' => get_file_base() . '/themes/default/images/cns_default_avatars/default_set/cartoons/crazy.jpg')
    );
    print_mobiquo_result($result);
}

/**
 * Standard Tapatalk endpoint test.
 * Never use on a live site.
 * Call via, and works with, our API in test_functions.php.
 */
function remove_attachment_test()
{
    $result = mobiquo_xmlrpc_simple_call(
        preg_replace('#_test$#', '', __FUNCTION__),
        array(
            '34',
            '',
            '',
        ),
        'admin' // Log in as admin. Only works with a backdoored IP
    );
    print_mobiquo_result($result);
}
