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
class Hook_endpoint_cms_homesite_dummy_data
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
            // 'authorization' => ['maintenance_password'], TODO: does not work
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
        // Set the size of the dummy data in bytes (32 MB)
        $size = 32 * 1024 * 1024;

        // Generate the dummy data
        $dummy_data = str_repeat('A', $size);

        // Send the appropriate headers
        header('Content-Type: application/octet-stream');
        header('Content-Length: ' . strval($size));
        header('Content-Disposition: attachment; filename="dummy_data.txt"');

        // Output the dummy data
        echo $dummy_data;

        // Non-standard output; we must terminate
        exit();
    }
}
