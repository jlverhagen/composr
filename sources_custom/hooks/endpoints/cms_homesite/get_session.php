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
 * @package    cms_homesite
 */

/**
 * Hook class.
 */
class Hook_endpoint_cms_homesite_get_session
{
    /**
     * Return information about this endpoint.
     *
     * @param  ?string $type Standard type parameter, usually either of add/edit/delete/view (null: not-set)
     * @param  ?string $id Standard ID parameter (null: not-set)
     * @return ?array Info about the hook (null: endpoint is disabled)
     */
    public function info(?string $type, ?string $id) : ?array
    {
        if (!addon_installed('cms_homesite')) {
            return null;
        }

        return [
            'authorization' => false,
        ];
    }

    /**
     * Run an API endpoint.
     *
     * @param  ?string $type Standard type parameter, usually either of add/edit/delete/view (null: not-set)
     * @param  ?string $id Standard ID parameter (null: not-set)
     * @return array Data structure that will be converted to correct response type
     */
    public function run(?string $type, ?string $id) : array
    {
        require_code('cms_homesite');
        require_code('telemetry');

        // The JSON payload was coerced by the main endpoints script
        $data = json_decode($_POST['data'], true);

        // Sanity checks
        if ($data === false) {
            http_response_code(400);
            return ['success' => false, 'error_details' => 'Telemetry data sent was not in JSON format.'];
        }
        if (!array_key_exists('nonce', $data) || !array_key_exists('encrypted_data', $data) || !array_key_exists('encrypted_session_key', $data) || !array_key_exists('version', $data)) {
            http_response_code(400);
            return ['success' => false, 'error_details' => 'Invalid telemetry data sent.'];
        }

        // Decrypt our message (this is just to validate that the request probably came from a Composr site)
        $_data = decrypt_data_telemetry($data['nonce'], $data['encrypted_data'], $data['encrypted_session_key'], floatval($data['version']));
        $decrypted_data = @unserialize($_data);
        if (($decrypted_data === false) || !is_array($decrypted_data) || !array_key_exists('request', $decrypted_data) || ($decrypted_data['request'] != 'get_session')) {
            http_response_code(400);
            return ['success' => false, 'error_details' => 'Telemetry data sent is corrupt and cannot be decrypted.'];
        }

        return [
            //'session_id' => symbol_tempcode('SESSION'), // Disabled by default; not secure
            'session_id_hashed' => symbol_tempcode('SESSION_HASHED')->evaluate(),
        ];
    }
}
