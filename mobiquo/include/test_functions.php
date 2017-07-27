<?php /*

 Composr
 Copyright (c) ocProducts, 2004-2016

 See text/EN/licence.txt for full licencing information.

*/

/**
 * @license    http://opensource.org/licenses/cpal_1.0 Common Public Attribution License
 * @copyright  ocProducts Ltd
 * @package    cns_tapatalk
 */

/*EXTRA FUNCTIONS: .*xmlrpc.*|json_.**/

/*
XML-RPC library documentation:
http://gggeek.github.io/phpxmlrpc/doc-2/
*/

/**
 * Call a test for an endpoint.
 *
 * @param  string $method Method name
 */
function call_mobiquo_test($method)
{
    $mobiquo = basename(dirname(dirname(__FILE__)));

    require_code('character_sets');

    // We may need, in case we get XML-RPC output
    require_once(get_file_base() . '/' . $mobiquo . '/lib/xmlrpc.php');
    require_once(get_file_base() . '/' . $mobiquo . '/lib/xmlrpcs.php');

    require_once(get_file_base() . '/' . $mobiquo . '/include/mobiquo_functions.php');
    require_once(get_file_base() . '/' . $mobiquo . '/include/server_define.php');

    $filename = request_helper_get_file($method);
    require_once(dirname(dirname(__FILE__)) . '/tests/' . $filename . '.php');

    call_user_func($method . '_test');
}

/**
 * Send out an XML-RPC request, to test an endpoint.
 *
 * @param  string $method Method name
 * @param  array $params Parameters (as PHP types)
 * @param  ?string $username Username to run with (null: guest)
 * @return mixed Result (as PHP types)
 */
function mobiquo_xmlrpc_simple_call($method, $params, $username = null)
{
    ini_set('ocproducts.xss_detect', '0');

    $mobiquo = basename(dirname(dirname(__FILE__)));

    $url = get_base_url() . '/' . $mobiquo . '/mobiquo.php?';
    if (!is_null($username)) {
        $url .= '&keep_su=' . urlencode($username);
    }
    $url .= static_evaluate_tempcode(symbol_tempcode('KEEP', array()));

    // We need this to have the XML-RPC parameter encoding code as available
    require(dirname(dirname(__FILE__)) . '/lib/mobiquo.php');
    require(dirname(dirname(__FILE__)) . '/lib/mobiquo_xmlrpc.php');
    global $MOBIQUO_SERVER;
    $MOBIQUO_SERVER = new MobiquoServerXMLRPC();

    $ob = new xmlrpc_client($url);

    $ob->setDebug(2);

    $arr = array();
    foreach ($params as $param) {
        $arr[] = recursive_php_xmlrpc_encode($param);
    }
    $response = $ob->send(new xmlrpcmsg($method, $arr));
    $result = $response->value();
    if ($result === 0) {
        @header('Content-type: text/plain; charset=utf-8');
        exit($response->faultString());
    }
    return php_xmlrpc_decode($result);
}

/**
 * Send out an POST request, to test an endpoint.
 *
 * @param  string $method Method name
 * @param  array $params Parameters (as PHP types)
 * @param  ?string $username Username to run with (null: guest)
 * @param  ?array $files Uploads (map of parameter name to file path on disk) (null: none)
 * @return mixed Result (as raw result string, or processed, depending on context)
 */
function mobiquo_post_simple_call($method, $params, $username = null, $files = null)
{
    ini_set('ocproducts.xss_detect', '0');

    $mobiquo = basename(dirname(dirname(__FILE__)));

    $url = get_base_url() . '/' . $mobiquo . '/mobiquo.php?';
    if (!is_null($username)) {
        $url .= '&keep_su=' . urlencode($username);
    }
    $url .= static_evaluate_tempcode(symbol_tempcode('KEEP', array()));

    $extra_headers = array(
        'X-TT' => '90b333d6-cadf-4761-a31a-06c01201ecc6',
    );

    $post = $params + array('method_name' => $method);
    $data = http_download_file($url, null, true, false, 'Composr', $post, null, null, null, null, null, null, null, 6.0, false, $files, $extra_headers);

    $test = @php_xmlrpc_decode_xml($data);
    if (is_object($test)) {
        return php_xmlrpc_decode($test->val);
    }
    $test = @json_decode($data, true);
    if (is_array($test)) {
        return $test;
    }

    return $data;
}

/**
 * Send out a JSON request, to test an endpoint.
 *
 * @param  string $method Method name
 * @param  array $params Parameters (as PHP types)
 * @param  ?string $username Username to run with (null: guest)
 * @return mixed Result (as raw result string)
 */
function mobiquo_json_simple_call($method, $params, $username = null)
{
    ini_set('ocproducts.xss_detect', '0');

    $mobiquo = basename(dirname(dirname(__FILE__)));

    $url = get_base_url() . '/' . $mobiquo . '/mobiquo.php?method_name=' . rawurlencode($method);
    if (!is_null($username)) {
        $url .= '&keep_su=' . urlencode($username);
    }
    $url .= static_evaluate_tempcode(symbol_tempcode('KEEP', array()));

    $extra_headers = array(
        'Content-Type' => 'application/json',
        'X-TT' => '90b333d6-cadf-4761-a31a-06c01201ecc6',
    );

    $post = json_encode($params);
    $data = http_download_file($url, null, true, false, 'Composr', array($post), null, null, null, null, null, null, null, 6.0, true, null, array());
    echo 'RAW: ' . $data;
    return json_decode($data, true);
}

/**
 * Apply XML-RPC encoding recursively.
 *
 * @param  mixed $param Input
 * @return mixed Output
 */
function recursive_php_xmlrpc_encode($param)
{
    if (is_array($param)) {
        foreach ($param as $i => $val) {
            if (!is_object($val)) {
                $param[$i] = recursive_php_xmlrpc_encode($val);
            }
        }
    }
    if ((is_string($param)) && (substr($param, 0, 1) == '%')) {
        $param = mobiquo_val(substr($param, 1), 'base64');
    }
    if (!is_object($param)) {
        $param = php_xmlrpc_encode($param);
    }
    return $param;
}

/**
 * Take a mobiquo result from mobiquo_xmlrpc_simple_call, and print it out to the browser, and exit.
 *
 * @param  mixed $result Input
 */
function print_mobiquo_result($result)
{
    echo '<pre>'; // This is for correct handling of newlines
    ob_start();
    var_dump($result);
    $a = ob_get_contents();
    ob_end_clean();
    echo htmlentities($a, ENT_QUOTES, 'utf-8'); // Escape every HTML special chars (especially > and < )
    echo '</pre>';
    exit();
}
