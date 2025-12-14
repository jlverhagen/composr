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
 * @package    cms_release_build
 */

/**
 * Module page class.
 */
class Module_admin_make_hotfix
{
    /**
     * Find details of the module.
     *
     * @return ?array Map of module info (null: module is disabled)
     */
    public function info() : ?array
    {
        $info = [];
        $info['author'] = 'Chris Graham and Patrick Schmalstig';
        $info['organisation'] = 'Composr';
        $info['hacked_by'] = null;
        $info['hack_version'] = null;
        $info['version'] = 1;
        $info['locked'] = false;
        $info['min_cms_version'] = 11.0;
        $info['addon'] = 'cms_release_build';
        return $info;
    }

    /**
     * Find entry-points available within this module.
     *
     * @param  boolean $check_perms Whether to check permissions
     * @param  ?MEMBER $member_id The member to check permissions as (null: current user)
     * @param  boolean $support_crosslinks Whether to allow cross links to other modules (identifiable via a full-page-link rather than a screen-name)
     * @param  boolean $be_deferential Whether to avoid any entry-point (or even return null to disable the page in the Sitemap) if we know another module, or page_group, is going to link to that entry-point. Note that "!" and "browse" entry points are automatically merged with container page nodes (likely called by page-groupings) as appropriate.
     * @return ?array A map of entry points (screen-name=>language-code/string or screen-name=>[language-code/string, icon-theme-image]) (null: disabled)
     */
    public function get_entry_points(bool $check_perms = true, ?int $member_id = null, bool $support_crosslinks = true, bool $be_deferential = false) : ?array
    {
        if (!addon_installed('cms_release_build')) {
            return null;
        }

        require_lang('cms_release_build');

        return [
            'step1' => ['RELEASE_TOOLS_MAKE_HOTFIX', 'admin/tool'],
        ];
    }

    public $title;

    /**
     * Module pre-run function. Allows us to know metadata for <head> before we start streaming output.
     *
     * @return ?Tempcode Tempcode indicating some kind of exceptional output (null: none)
     */
    public function pre_run() : ?object
    {
        i_solemnly_declare(I_UNDERSTAND_SQL_INJECTION | I_UNDERSTAND_XSS | I_UNDERSTAND_PATH_INJECTION);

        $error_msg = new Tempcode();
        if (!addon_installed__messaged('cms_release_build', $error_msg)) {
            return $error_msg;
        }

        require_lang('cms_release_build');

        // We must be running from Git to use this tool
        if (!is_dir(get_file_base() . '/.git')) {
            warn_exit(do_lang_tempcode('MAKE_RELEASE_REQUIRES_GIT'));
        }

        global $GIT_PATH;
        $GIT_PATH = 'git';
        $git_result = shell_exec($GIT_PATH . ' --help 2>&1');
        if (strpos($git_result, 'git: command not found') !== false) {
            if (file_exists('/usr/local/git/bin/git')) {
                $GIT_PATH = '/usr/local/git/bin/git';
            } elseif (file_exists('C:\\Program Files (x86)\\Git\\bin\\git.exe')) {
                $GIT_PATH = '"C:\\Program Files (x86)\\Git\\bin\\git.exe"';
            }
        }

        $type = get_param_string('type', 'step1');

        switch ($type) {
            case 'step1':
                $this->title = get_screen_title('MAKE_HOTFIX_TITLE', true, [escape_html('1')]);
                break;
            case 'step2':
                $this->title = get_screen_title('MAKE_HOTFIX_TITLE', true, [escape_html('2')]);
                break;
        }

        return null;
    }

    /**
     * Execute the module.
     *
     * @return Tempcode The result of execution
     */
    public function run() : object
    {
        $type = get_param_string('type', 'step1');

        if ($type == 'step1') {
            return $this->step1();
        }
        if ($type == 'step2') {
            return $this->step2();
        }

        return new Tempcode();
    }

    /**
     * The UI for step 1: gathering information.
     *
     * @return Tempcode The UI
     */
    public function step1() : object
    {
        require_code('form_templates');

        $text = do_lang_tempcode('MAKE_HOTFIX_STEP1_TEXT');

        $fields = new Tempcode();

        // Tracker ID
        $fields->attach(form_input_integer(do_lang_tempcode('MAKE_HOTFIX_STEP1_TRACKER_ID'), do_lang_tempcode('DESCRIPTION_MAKE_HOTFIX_STEP1_TRACKER_ID'), 'tracker_id', null, true));

        // Commit IDs
        $fields->attach(form_input_huge(do_lang_tempcode('MAKE_HOTFIX_STEP1_COMMIT_IDS'), do_lang_tempcode('DESCRIPTION_MAKE_HOTFIX_STEP1_COMMIT_IDS'), 'commit_ids', '', true, null, 5));

        // Bundled or custom code? We cannot put both in a hotfix.
        $fields->attach(form_input_tick(do_lang_tempcode('MAKE_HOTFIX_STEP1_IS_CUSTOM'), do_lang_tempcode('DESCRIPTION_MAKE_HOTFIX_STEP1_IS_CUSTOM'), 'is_custom', false));

        $post_url = build_url(['page' => '_SELF', 'type' => 'step2'], '_SELF');

        list($warning_details, $ping_url) = handle_conflict_resolution(false, false);

        return do_template('FORM_SCREEN', [
            '_GUID' => 'TODO',
            'GET' => false,
            'SKIP_WEBSTANDARDS' => true,
            'HIDDEN' => new Tempcode(),
            'TITLE' => $this->title,
            'TEXT' => $text,
            'SUBMIT_ICON' => 'buttons/proceed',
            'SUBMIT_NAME' => do_lang_tempcode('PROCEED'),
            'FIELDS' => $fields,
            'URL' => $post_url,
            'WARNING_DETAILS' => $warning_details,
            'PING_URL' => $ping_url,
        ]);
    }

    /**
     * The actualiser for making a hotfix.
     *
     * @return Tempcode The UI
     */
    public function step2() : object
    {
        $tracker_id = post_param_integer('tracker_id');
        $commit_ids = explode("\n", post_param_string('commit_ids'));
        $is_custom = (post_param_string('is_custom', '0') == '1');

        require_code('make_release');
        require_code('tar');
        require_code('files');

        // This operation can take a bit of processing
        cms_extend_time_limit(TIME_LIMIT_EXTEND__MODEST);
        disable_php_memory_limit();

        // Prepare the TAR file
        $builds_path = get_builds_path();
        $hotfix_path = $builds_path . '/builds/hotfixes';
        if (!file_exists($hotfix_path)) {
            make_missing_directory($hotfix_path);
        }
        $filename = 'hotfix-' . strval($tracker_id) . ' ' . uniqid('', false);
        $tar_path = $hotfix_path . '/' . $filename . '.tar';
        $tar_file = tar_open($tar_path, 'wb');

        // Add files into the hotfix
        $files_processed = [];
        foreach ($commit_ids as $commit_id) {
            if (trim($commit_id) == '') {
                continue;
            }

            global $GIT_PATH;
            $git_command = $GIT_PATH . ' show --pretty="format:" --name-only ' . $commit_id;
            $git_result = shell_exec($git_command . ' 2>&1');
            if (($git_result === null) || ($git_result === false) || (preg_match('#^fatal:\s#mi', $git_result) !== 0)) {
                attach_message(do_lang_tempcode('MAKE_HOTFIX_STEP2_COMMIT_ID_ERROR', escape_html(strval($commit_id))), 'warn');
                continue;
            }

            $__fixed_files = explode("\n", $git_result);

            foreach ($__fixed_files as $file) {
                $file = trim($file);
                if ($file == '') {
                    continue;
                }

                if ($is_custom && !should_ignore_file($file, IGNORE_NONBUNDLED)) { // Reverse logic; files going in the hotfix are actually default ignored files and ignored non-bundled files
                    continue;
                }
                if (!$is_custom && should_ignore_file($file, IGNORE_HIDDEN_FILES | IGNORE_EDITFROM_FILES | IGNORE_REVISION_FILES | IGNORE_REBUILDABLE_OR_TEMP_FILES_FOR_BACKUP | IGNORE_CUSTOM_ZONES | IGNORE_CUSTOM_THEMES | IGNORE_CUSTOM_LANGS | IGNORE_NONBUNDLED | IGNORE_FLOATING | IGNORE_SHIPPED_VOLATILE | IGNORE_UNSHIPPED_VOLATILE | IGNORE_CUSTOM_DIRS | IGNORE_UPLOADS | IGNORE_ALIEN)) {
                    continue;
                }
                if (isset($files_processed[$file])) {
                    continue;
                }
                $files_processed[$file] = true;

                // Get the file data from Git at this commit
                $spec = $commit_id . ':' . $file;
                $git_show_cmd = $GIT_PATH . ' show ' . escapeshellarg($spec);
                $file_data = shell_exec($git_show_cmd . ' 2>&1');
                if (($file_data === null) || ($file_data === false) || (preg_match('#^fatal:\s#mi', $file_data) !== 0)) {
                    attach_message(do_lang_tempcode('MAKE_HOTFIX_STEP2_COMMIT_FILE_ERROR', escape_html(strval($commit_id)), escape_html(strval($file))), 'warn');
                    continue;
                }

                // Determine mode from git ls-tree (fallback to 0644)
                $mode = 0644;
                $git_ls_cmd = $GIT_PATH . ' ls-tree -z ' . escapeshellarg($commit_id) . ' -- ' . escapeshellarg($file);
                $ls_out = shell_exec($git_ls_cmd . ' 2>&1');
                if (($ls_out === null) || ($ls_out === false) || (preg_match('#^fatal:\s#mi', $ls_out) !== 0)) {
                    attach_message(do_lang_tempcode('MAKE_HOTFIX_STEP2_COMMIT_FILE_MODE_ERROR', escape_html(strval($commit_id)), escape_html(strval($file))), 'notice');
                } else {
                    if (is_string($ls_out) && ($ls_out != '')) {
                        $null_pos = strpos($ls_out, "\0");
                        if ($null_pos !== false) {
                            $ls_line = substr($ls_out, 0, $null_pos);
                        } else {
                            $ls_line = trim($ls_out);
                        }
                        $parts = preg_split('#\s+#', $ls_line);
                        if (!empty($parts)) {
                            $mode_str = $parts[0]; // e.g., 100644, 100755
                            if (preg_match('#^\d{6}$#', $mode_str) === 1) {
                                // Use last 3 digits as permission bits
                                $perm_str = substr($mode_str, -3);
                                if (preg_match('#^[0-7]{3}$#', $perm_str) === 1) {
                                    $mode = octdec('0' . $perm_str);
                                }
                            }
                        }
                    }
                }

                // Determine mtime from the commit timestamp
                $mtime = time();
                $git_time_cmd = $GIT_PATH . ' show -s --format=%ct ' . escapeshellarg($commit_id);
                $time_out = shell_exec($git_time_cmd . ' 2>&1');
                if (($ls_out === null) || ($ls_out === false) || (preg_match('#^fatal:\s#mi', $ls_out) !== 0)) {
                    // Too verbose to add a warning for this
                } else {
                    if (is_string($time_out)) {
                        $time_out = trim($time_out);
                        if ($time_out !== '' && ctype_digit($time_out)) {
                            $mtime = intval($time_out);
                        }
                    }
                }

                // Add to TAR
                tar_add_file($tar_file, $file, $file_data, $mode, $mtime);
            }
        }

        // Finish TAR
        tar_close($tar_file);

        return inform_screen($this->title, do_lang_tempcode('MAKE_HOTFIX_STEP2_SUCCESS', escape_html($tar_path)));
    }
}
