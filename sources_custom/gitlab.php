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
 * @package    gitlab_shared
 */

function init__gitlab()
{
    if (!defined('COMPOSR_GITLAB_PROJECT_ID')) {
        define('COMPOSR_GITLAB_PROJECT_ID', '14182874');
    }

    require_lang('gitlab_shared');
}

/**
 * Add a comment to the specified commit.
 *
 * @param  ID_TEXT $commit_id The commit
 * @param  LONG_TEXT $comment The comment to add
 * @throws \Exception
 */
function gitlab_add_commit_comment(string $commit_id, string $comment)
{
    $token = get_option('gitlab_access_token');
    if ($token == '') {
        throw new Exception('GitLab access token must be defined in configuration.');
    }

    $url = 'https://gitlab.com/api/v4/projects/' . COMPOSR_GITLAB_PROJECT_ID . '/repository/commits/' . $commit_id . '/comments';

    $result = cms_http_request($url, ['timeout' => 10.0, 'trigger_errors' => false, 'extra_headers' => ['Private-Token' => $token], 'post_params' => ['note' => $comment]]);
    if (substr($result->message, 0, 1) != '2') {
        throw new Exception($result->data);
    }
}

/**
 * Add a note to the specified issue.
 *
 * @param  integer $issue_id The issue ID
 * @param  LONG_TEXT $note The note to add
 * @throws \Exception
 */
function gitlab_add_issue_note(int $issue_id, string $note)
{
    $token = get_option('gitlab_access_token');
    if ($token == '') {
        throw new Exception('GitLab access token must be defined in configuration.');
    }

    $url = 'https://gitlab.com/api/v4/projects/' . COMPOSR_GITLAB_PROJECT_ID . '/issues/' . strval($issue_id) . '/notes';

    $result = cms_http_request($url, ['timeout' => 10.0, 'trigger_errors' => false, 'extra_headers' => ['Private-Token' => $token], 'post_params' => ['body' => $note]]);
    if (substr($result->message, 0, 1) != '2') {
        throw new Exception($result->data);
    }
}
