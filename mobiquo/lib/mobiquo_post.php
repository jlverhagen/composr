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

/*EXTRA FUNCTIONS: json_encode|var_export*/

/**
 * Mobiquo server implementation.
 */
class MobiquoServerPOST extends MobiquoServer
{
    private $output_server;

    /**
     * Decode parameters we were called with.
     *
     * @param  mixed $raw_params Raw params
     * @return array Params as an array
     */
    public function params_decode($raw_params)
    {
        return $raw_params; // No decoding needed
    }

    /**
     * Find Mobiquo method name (endpoint name).
     *
     * @return string Method name
     */
    public function get_method_name()
    {
        if (isset($_POST['method_name'])) {
            return $_POST['method_name'];
        }

        header('Content-type: text/plain');
        exit('No method is provided');
    }

    /**
     * Dispatch a server request.
     */
    public function dispatch_request()
    {
        $this->output_server = $this->get_output_server();

        if (is_null($this->output_server)) {
            header('Content-type: text/plain');
        }

        $get = get_magic_quotes_gpc() ? array_map('stripslashes', $_GET) : $_GET;
        unset($get['method_name']);
        $post = get_magic_quotes_gpc() ? array_map('stripslashes', $_POST) : $_POST;
        unset($post['method_name']);

        $params = array_merge($post, $get);

        ini_set('ocproducts.xss_detect', '0');

        global $SERVER_DEFINE;
        $function = $SERVER_DEFINE[$this->get_method_name()]['function'];
        try {
            $response = call_user_func($function, $params);
        } catch (Exception $e) {
            @header('HTTP/1.0 200'); // We always give 200 responses, so make sure we undo any other ones given

            $msg = $e->getMessage();
            if ($GLOBALS['DEV_MODE'] && get_param_integer('keep_fatalistic', 0) == 1) {
                $msg .= ' ' . var_export($e->getTrace(), true);
            }
            $response = mobiquo_response_false($msg);

            if ((is_file(TAPATALK_LOG)) && (is_writable_wrap(TAPATALK_LOG))) {
                // Request
                $log_file = fopen(TAPATALK_LOG, 'at');
                flock($log_file, LOCK_EX);
                fseek($log_file, 0, SEEK_END);
                fwrite($log_file, TAPATALK_REQUEST_ID . ' -- ' . date('Y-m-d H:i:s') . " *TRACE*:\n");
                fwrite($log_file, var_export($e->getTrace(), true));
                fwrite($log_file, "\n\n");
                flock($log_file, LOCK_UN);
                fclose($log_file);
            }
        }
        $this->output_response($response);
    }

    /**
     * Get output server object for this request.
     *
     * @return ?object The output Moquiquo server (null: none)
     */
    private function get_output_server()
    {
        if (isset($_POST['format'])) {
            $format = trim($_POST['format']);
        } else {
            $format = 'xmlrpc';
        }

        switch ($format) {
            case 'json':
                require_once(dirname(__FILE__) . '/mobiquo_json.php');
                return new MobiquoServerJSON();
            case 'xmlrpc':
                require_once(dirname(__FILE__) . '/mobiquo_xmlrpc.php');
                return new MobiquoServerXMLRPC();
            case 'serialize':
                return null;
        }

        warn_exit($format . ' is not valid format');
    }

    /**
     * Wrap a value for the particular MobiquoServer server implementation.
     *
     * @param  mixed $data Data
     * @param  ?string $type Type (null: autodetect)
     * @set string boolean base64 int dateTime.iso8601 array struct
     * @return mixed Mobiquo result
     */
    public function val($data, $type)
    {
        if (is_null($this->output_server)) {
            if (is_string($data)) {
                $data = convert_to_internal_encoding($data, get_charset(), 'utf-8');
                $data = html_entity_decode($data, ENT_QUOTES, 'utf-8');
            }

            if ($type === 'dateTime.iso8601') {
                $data = $this->date_encode($data);
            }

            return $data;
        }

        return $this->output_server->val($data, $type);
    }

    /**
     * Generate a standard Mobiquo date.
     *
     * @param  integer $timet Timestamp
     * @param  integer $timezone Timezone hour offset
     * @return string iso8601 date
     */
    private function date_encode($timet, $timezone = 0)
    {
        return gmdate('Y-m-d\TH:i:s', $timet + $timezone * 3600) . '+00:00';
    }

    /**
     * Wrap a result for the particular MobiquoServer server implementation.
     *
     * @param  mixed $data Data
     * @return mixed Mobiquo response
     */
    public function response($data)
    {
        if (is_null($this->output_server)) {
            return serialize($data);
        }

        return $this->output_server->response($data);
    }

    /**
     * Output a response.
     *
     * @param  mixed $response Response
     */
    public function output_response($response)
    {
        if (is_null($this->output_server)) {
            parent::output_response($response);
            return;
        }

        $this->output_server->output_response($response);
    }
}
