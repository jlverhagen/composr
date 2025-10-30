<?php /*

 The contents of this file are subject to the Common Public Attribution License Version 1.0 (the "License");
 you may not use this file except in compliance with the License.
 You may obtain a copy of the License at http://opensource.org/licenses/cpal_1.0.

 Software distributed under the License is distributed on an "AS IS" basis, WITHOUT WARRANTY OF ANY KIND, either express or implied.
 See the License for the specific language governing rights and limitations under the License.

 The Original Code is Composr CMS.

 The Original Developer is the Initial Developer.

 The Initial Developer of the Original Code is Chris Graham.
 All portions of the code written by Chris Graham are Copyright (c) Christopher Graham. All Rights Reserved.

 See docs/LICENSE.md for full licensing information.

*/

/**
 * @license    http://opensource.org/licenses/cpal_1.0 Common Public Attribution License
 * @copyright  Christopher Graham
 * @package    cns_tapatalk
 */

/**
 * Mobiquo server base class.
 */
abstract class MobiquoServer
{
    /**
     * Decode parameters we were called with.
     *
     * @param  mixed $raw_params Raw params
     * @return array Params as an array
     */
    abstract public function params_decode($raw_params) : array;

    /**
     * Dispatch a server request.
     */
    abstract public function dispatch_request();

    /**
     * Wrap a value for the particular MobiquoServer server implementation.
     *
     * @param  mixed $data Data
     * @param  string $type Type
     * @set string boolean base64 int dateTime.iso8601 array struct
     * @return mixed Mobiquo result
     */
    abstract public function val($data, string $type);

    /**
     * Wrap a result for the particular MobiquoServer server implementation.
     *
     * @param  mixed $data Data
     * @return mixed Mobiquo response
     */
    abstract public function response($data);

    /**
     * Output a response.
     *
     * @param  mixed $response Response
     */
    public function output_response($response)
    {
        echo $response;
    }
}
