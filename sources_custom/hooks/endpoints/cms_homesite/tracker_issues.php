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
 * @package    cms_homesite_tracker
 */

/**
 * Hook class.
 */
class Hook_endpoint_cms_homesite_tracker_issues
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
        if (!addon_installed('cms_homesite_tracker')) {
            return null;
        }

        // FUDGE: We POST when getting tracker issues because the discovered parameter can be very long
        $ids = post_param_string('discovered', null);
        if ($ids !== null) {
            $type = 'view';
        }

        return [
            'authorization' => ($type !== 'view') ? ['super_admin', 'maintenance_password'] : false,
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
        // FUDGE: We POST when getting tracker issues because the discovered parameter can be very long
        $ids = post_param_string('discovered', null);
        if ($ids !== null) {
            $type = 'view';

            // Log the change in type
            $_log_file = get_custom_file_base() . '/data_custom/endpoints.log';
            if (is_file($_log_file)) {
                require_code('files');
                $log_message = loggable_date() . ' INFO /cms_homesite/tracker_issues we are actually doing a view type request as discovered was POSTed.' . "\n";
                $log_file = cms_fopen_text_write($_log_file, true, 'ab');
                fwrite($log_file, $log_message);
                flock($log_file, LOCK_UN);
                fclose($log_file);
            }
        }

        require_code('cms_homesite');

        switch ($type) {
            case 'add':
                require_code('mantis');
                $results = create_tracker_issue(post_param_string('version_dotted'), post_param_string('tracker_title'), post_param_string('tracker_message'), post_param_string('tracker_additional'), post_param_integer('tracker_severity'), post_param_integer('tracker_category'), post_param_integer('tracker_project'));
                return [
                    'id' => $results,
                ];

            case 'edit':
                $data = ['success' => false];
                if ($id === null) {
                    return $data;
                }

                $close = post_param_integer('close', 0);
                if ($close == 1) {
                    require_code('mantis');
                    resolve_tracker_issue(intval($id));
                    $data['success'] = true;
                }

                if (isset($_FILES['upload'])) {
                    require_code('mantis');
                    $file_id = upload_to_tracker_issue(intval($id), $_FILES['upload']);
                    $data['upload'] = $file_id;
                    $data['success'] = true;
                }

                return $data;

            default:
                $ids = post_param_string('discovered');
                $version = post_param_string('new_version');
                $previous_version = post_param_string('previous_version', null);

                $_ids = ($ids == '') ? [] : array_map('intval', explode(',', $ids)); // Security to prevent SQL injection
                require_code('mantis');
                return get_tracker_issues($_ids, $version, $previous_version);
        }
    }
}
