<?php /*

 Composr
 Copyright (c) ocProducts, 2004-2017

 See text/EN/licence.txt for full licencing information.


 NOTE TO PROGRAMMERS:
   Do not edit this file. If you need to make changes, save your changed file to the appropriate *_custom folder
   **** If you ignore this advice, then your website upgrades (e.g. for bug fixes) will likely kill your changes ****

*/

/**
 * @license    http://opensource.org/licenses/cpal_1.0 Common Public Attribution License
 * @copyright  ocProducts Ltd
 * @package    quizzes
 */

/**
 * Module page class.
 */
class Module_quiz
{
    /**
     * Find details of the module.
     *
     * @return ?array Map of module info (null: module is disabled).
     */
    public function info()
    {
        $info = array();
        $info['author'] = 'Chris Graham';
        $info['organisation'] = 'ocProducts';
        $info['hacked_by'] = null;
        $info['hack_version'] = null;
        $info['version'] = 6;
        $info['update_require_upgrade'] = true;
        $info['locked'] = false;
        return $info;
    }

    /**
     * Uninstall the module.
     */
    public function uninstall()
    {
        $GLOBALS['SITE_DB']->drop_table_if_exists('quizzes');
        $GLOBALS['SITE_DB']->drop_table_if_exists('quiz_questions');
        $GLOBALS['SITE_DB']->drop_table_if_exists('quiz_question_answers');
        $GLOBALS['SITE_DB']->drop_table_if_exists('quiz_entries');
        $GLOBALS['SITE_DB']->drop_table_if_exists('quiz_member_last_visit');
        $GLOBALS['SITE_DB']->drop_table_if_exists('quiz_winner');
        $GLOBALS['SITE_DB']->drop_table_if_exists('quiz_entry_answer');

        delete_privilege('bypass_quiz_repeat_time_restriction');
        delete_privilege('bypass_quiz_timer');
        delete_privilege('view_others_quiz_results');

        delete_privilege('autocomplete_keyword_quiz');
        delete_privilege('autocomplete_title_quiz');
    }

    /**
     * Install the module.
     *
     * @param  ?integer $upgrade_from What version we're upgrading from (null: new install)
     * @param  ?integer $upgrade_from_hack What hack version we're upgrading from (null: new-install/not-upgrading-from-a-hacked-version)
     */
    public function install($upgrade_from = null, $upgrade_from_hack = null)
    {
        if (($upgrade_from !== null) && ($upgrade_from < 5)) {
            $GLOBALS['SITE_DB']->add_table_field('quiz_questions', 'q_required', 'BINARY');
        }

        if (($upgrade_from !== null) && ($upgrade_from < 6)) {
            $GLOBALS['SITE_DB']->add_table_field('quizzes', 'q_reveal_answers', 'BINARY');
            $GLOBALS['SITE_DB']->add_table_field('quizzes', 'q_shuffle_questions', 'BINARY');
            $GLOBALS['SITE_DB']->add_table_field('quizzes', 'q_shuffle_answers', 'BINARY');
            $GLOBALS['SITE_DB']->add_table_field('quiz_questions', 'q_marked', 'BINARY', 1);

            // Save in permissions for event type
            $quizzes = $GLOBALS['SITE_DB']->query_select('quizzes', array('id'));
            foreach ($quizzes as $quiz) {
                require_code('permissions2');
                set_global_category_access('quiz', $quiz['id']);
            }

            $GLOBALS['SITE_DB']->add_table_field('quiz_questions', 'q_type', 'ID_TEXT', 'MULTIPLECHOICE');
            $GLOBALS['SITE_DB']->query_update('quiz_questions', array('q_type' => 'LONG'), array('q_long_input_field' => 1));
            $GLOBALS['SITE_DB']->query('UPDATE ' . get_table_prefix() . 'quiz_questions SET q_type=\'MULTIMULTIPLE\' WHERE q_num_choosable_answers>0');
            $GLOBALS['SITE_DB']->delete_table_field('quiz_questions', 'q_long_input_field');
            $GLOBALS['SITE_DB']->delete_table_field('quiz_questions', 'q_num_choosable_answers');
            $GLOBALS['SITE_DB']->add_table_field('quiz_questions', 'q_question_extra_text', 'LONG_TRANS');
        }

        if ($upgrade_from === null) {
            $GLOBALS['SITE_DB']->create_table('quiz_member_last_visit', array(
                'id' => '*AUTO',
                'v_time' => 'TIME',
                'v_member_id' => 'MEMBER',
                'v_quiz_id' => 'AUTO_LINK',
            ));

            add_privilege('QUIZZES', 'bypass_quiz_repeat_time_restriction', false);

            $GLOBALS['SITE_DB']->create_table('quizzes', array(
                'id' => '*AUTO',
                'q_timeout' => '?INTEGER', // The number of minutes to complete the test (not secure)
                'q_name' => 'SHORT_TRANS',
                'q_start_text' => 'LONG_TRANS__COMCODE',
                'q_end_text' => 'LONG_TRANS__COMCODE',
                'q_notes' => 'LONG_TEXT', // Staff notes
                'q_percentage' => 'INTEGER', // Percentage required for successful completion, if a test
                'q_open_time' => 'TIME',
                'q_close_time' => '?TIME',
                'q_num_winners' => 'INTEGER',
                'q_redo_time' => '?INTEGER', // Number of hours between attempts. null implies it may never be re-attempted
                'q_type' => 'ID_TEXT', // COMPETITION, TEST, SURVEY
                'q_add_date' => 'TIME',
                'q_validated' => 'BINARY',
                'q_submitter' => 'MEMBER',
                'q_points_for_passing' => 'INTEGER',
                'q_tied_newsletter' => '?AUTO_LINK',
                'q_end_text_fail' => 'LONG_TRANS__COMCODE',
                'q_reveal_answers' => 'BINARY',
                'q_shuffle_questions' => 'BINARY',
                'q_shuffle_answers' => 'BINARY',
            ));
            $GLOBALS['SITE_DB']->create_index('quizzes', 'q_validated', array('q_validated'));

            $GLOBALS['SITE_DB']->create_table('quiz_questions', array( // Note there is only a matching question_answer if it is not a free question. If there is just one answer, then it is not multiple-choice.
                'id' => '*AUTO',
                'q_type' => 'ID_TEXT',
                'q_quiz' => 'AUTO_LINK',
                'q_question_text' => 'LONG_TRANS__COMCODE',
                'q_question_extra_text' => 'LONG_TRANS__COMCODE',
                'q_order' => 'INTEGER',
                'q_required' => 'BINARY',
                'q_marked' => 'BINARY',
            ));

            $GLOBALS['SITE_DB']->create_table('quiz_question_answers', array(
                'id' => '*AUTO',
                'q_question' => 'AUTO_LINK',
                'q_answer_text' => 'SHORT_TRANS__COMCODE',
                'q_is_correct' => 'BINARY', // If this is the correct answer; only applies for quizzes
                'q_order' => 'INTEGER',
                'q_explanation' => 'LONG_TRANS',
            ));

            $GLOBALS['SITE_DB']->create_table('quiz_winner', array(
                'q_quiz' => '*AUTO_LINK',
                'q_entry' => '*AUTO_LINK',
                'q_winner_level' => 'INTEGER',
            ));

            $GLOBALS['SITE_DB']->create_table('quiz_entries', array(
                'id' => '*AUTO',
                'q_time' => 'TIME',
                'q_member' => 'MEMBER',
                'q_quiz' => 'AUTO_LINK',
                'q_results' => 'INTEGER',
            ));

            $GLOBALS['SITE_DB']->create_table('quiz_entry_answer', array(
                'id' => '*AUTO',
                'q_entry' => 'AUTO_LINK',
                'q_question' => 'AUTO_LINK',
                'q_answer' => 'LONG_TEXT', // Either an ID or a textual answer
            ));

            $GLOBALS['SITE_DB']->create_index('quizzes', 'ftjoin_qstarttext', array('q_start_text'));
        }

        if (($upgrade_from === null) || ($upgrade_from < 6)) {
            add_privilege('QUIZZES', 'view_others_quiz_results', false);
            add_privilege('QUIZZES', 'bypass_quiz_timer', false);

            $GLOBALS['SITE_DB']->create_index('quizzes', '#quiz_search__combined', array('q_start_text', 'q_name'));

            add_privilege('SEARCH', 'autocomplete_keyword_quiz', false);
            add_privilege('SEARCH', 'autocomplete_title_quiz', false);
        }
    }

    /**
     * Find entry-points available within this module.
     *
     * @param  boolean $check_perms Whether to check permissions.
     * @param  ?MEMBER $member_id The member to check permissions as (null: current user).
     * @param  boolean $support_crosslinks Whether to allow cross links to other modules (identifiable via a full-page-link rather than a screen-name).
     * @param  boolean $be_deferential Whether to avoid any entry-point (or even return null to disable the page in the Sitemap) if we know another module, or page_group, is going to link to that entry-point. Note that "!" and "browse" entry points are automatically merged with container page nodes (likely called by page-groupings) as appropriate.
     * @return ?array A map of entry points (screen-name=>language-code/string or screen-name=>[language-code/string, icon-theme-image]) (null: disabled).
     */
    public function get_entry_points($check_perms = true, $member_id = null, $support_crosslinks = true, $be_deferential = false)
    {
        return array(
            'browse' => array('QUIZZES', 'menu/rich_content/quiz'),
        );
    }

    public $title;
    public $quiz_id;
    public $quiz;
    public $quiz_name;
    public $title_to_use;
    public $title_to_use_2;

    /**
     * Module pre-run function. Allows us to know metadata for <head> before we start streaming output.
     *
     * @return ?Tempcode Tempcode indicating some kind of exceptional output (null: none).
     */
    public function pre_run()
    {
        $type = get_param_string('type', 'browse');

        require_lang('quiz');
        require_code('quiz');
        require_css('quizzes');

        if ($type == 'browse') {
            $this->title = get_screen_title('QUIZZES');
        }

        if ($type == 'do') {
            $quiz_id = get_param_integer('id');

            // Check access
            if (!has_category_access(get_member(), 'quiz', strval($quiz_id))) {
                access_denied('CATEGORY_ACCESS');
            }

            $quizzes = $GLOBALS['SITE_DB']->query_select('quizzes', array('*'), array('id' => $quiz_id), '', 1);
            if (!array_key_exists(0, $quizzes)) {
                warn_exit(do_lang_tempcode('MISSING_RESOURCE', 'quiz'));
            }
            $quiz = $quizzes[0];

            if ((get_value('no_awards_in_titles') !== '1') && (addon_installed('awards'))) {
                require_code('awards');
                $awards = find_awards_for('quiz', strval($quiz_id));
            } else {
                $awards = array();
            }

            $quiz_name = get_translated_text($quiz['q_name']);
            $title_to_use = do_lang_tempcode('QUIZ_THIS_WITH', do_lang_tempcode($quiz['q_type']), make_fractionable_editable('quiz', $quiz_id, $quiz_name));
            $title_to_use_2 = do_lang('QUIZ_THIS_WITH', do_lang($quiz['q_type']), $quiz_name);
            seo_meta_load_for('quiz', strval($quiz_id), $title_to_use_2);

            breadcrumb_set_self(get_translated_text($quiz['q_name']));

            $type = 'Quiz';
            switch ($quiz['q_type']) {
                case 'COMPETITION':
                    $type = 'Competition';
                    break;

                case 'SURVEY':
                    $type = 'Survey';
                    break;

                case 'TEST':
                    $type = 'Test';
                    break;
            }

            set_extra_request_metadata(array(
                'type' => $type,
                'identifier' => '_SEARCH:quiz:do:' . strval($quiz_id),
            ), $quiz, 'quiz', strval($quiz_id));

            $this->quiz_id = $quiz_id;
            $this->quiz = $quiz;
            $this->quiz_name = $quiz_name;
            $this->title_to_use = $title_to_use;
            $this->title_to_use_2 = $title_to_use_2;

            breadcrumb_set_parents(array(array('_SELF:_SELF:browse', do_lang_tempcode('QUIZZES'))));

            $this->title = get_screen_title(do_lang_tempcode('QUIZ_THIS_WITH', do_lang_tempcode($quiz['q_type']), make_string_tempcode(escape_html(get_translated_text($quiz['q_name'])))), false);
        }

        if ($type == '_do') {
            $quiz_id = get_param_integer('id');

            // Check access
            if (!has_category_access(get_member(), 'quiz', strval($quiz_id))) {
                access_denied('CATEGORY_ACCESS');
            }

            $quizzes = $GLOBALS['SITE_DB']->query_select('quizzes', array('*'), array('id' => $quiz_id), '', 1);
            if (!array_key_exists(0, $quizzes)) {
                warn_exit(do_lang_tempcode('MISSING_RESOURCE', 'quiz'));
            }
            $quiz = $quizzes[0];
            $this->enforcement_checks($quiz);

            breadcrumb_set_self(do_lang_tempcode('DONE'));
            breadcrumb_set_parents(array(array('_SELF:_SELF:browse', do_lang_tempcode('QUIZZES')), array('_SELF:_SELF:do:' . strval($quiz_id), get_translated_text($quiz['q_name']))));

            $this->title = get_screen_title(do_lang_tempcode('QUIZ_THIS_WITH', do_lang_tempcode($quiz['q_type']), make_string_tempcode(escape_html(get_translated_text($quiz['q_name'])))), false);

            $this->quiz_id = $quiz_id;
            $this->quiz = $quiz;
        }

        return null;
    }

    /**
     * Execute the module.
     *
     * @return Tempcode The result of execution.
     */
    public function run()
    {
        $type = get_param_string('type', 'browse');

        if ($type == 'browse') {
            return $this->archive();
        }
        if ($type == 'do') {
            return $this->do_quiz();
        }
        if ($type == '_do') {
            return $this->_do_quiz();
        }

        return new Tempcode();
    }

    /**
     * The UI to browse quizzes/surveys/tests.
     *
     * @return Tempcode The UI
     */
    public function archive()
    {
        $start = get_param_integer('quizzes_start', 0);
        $max = get_param_integer('quizzes_max', 20);

        $sql = 'SELECT q.* FROM ' . $GLOBALS['SITE_DB']->get_table_prefix() . 'quizzes q WHERE 1=1';
        if ((!has_privilege(get_member(), 'see_unvalidated')) && (addon_installed('unvalidated'))) {
            $sql .= ' AND q_validated=1';
        }
        $filter = get_param_string('filter', '');
        if ($filter != '') {
            $sql .= ' AND ' . $GLOBALS['SITE_DB']->translate_field_ref('q_name') . ' LIKE ' . '\'' . db_encode_like('%' . $filter . '%') . '\'';
        }
        $sql .= ' AND q_open_time<' . strval(time());
        $sql .= ' AND (q_close_time IS NULL OR q_close_time>' . strval(time()) . ')';
        $sql .= ' ORDER BY q_type ASC,q.id DESC';
        $rows = $GLOBALS['SITE_DB']->query($sql, null, 0, false, false, array('q_name' => 'SHORT_TRANS'));

        $content_tests = new Tempcode();
        $content_competitions = new Tempcode();
        $content_surveys = new Tempcode();
        $num = 0;
        foreach ($rows as $myrow) {
            // Check access
            if (!has_category_access(get_member(), 'quiz', strval($myrow['id']))) {
                continue;
            }

            if (($num >= $start) && ($num < $start + $max)) {
                $link = render_quiz_box($myrow, '_SEARCH', false);

                switch ($myrow['q_type']) {
                    case 'SURVEY':
                        $content_surveys->attach($link);
                        break;
                    case 'TEST':
                        $content_tests->attach($link);
                        break;
                    case 'COMPETITION':
                        $content_competitions->attach($link);
                        break;
                }
            }

            $num++;
        }
        $max_rows = $num;

        require_code('templates_pagination');
        $pagination = pagination(do_lang_tempcode('QUIZZES'), $start, 'quizzes_start', $max, 'quizzes_max', $max_rows);

        $tpl = do_template('QUIZ_ARCHIVE_SCREEN', array(
            '_GUID' => '3073f74b500deba96b7a3031a2e9c8d8',
            'TITLE' => $this->title,
            'CONTENT_SURVEYS' => $content_surveys,
            'CONTENT_COMPETITIONS' => $content_competitions,
            'CONTENT_TESTS' => $content_tests,
            'PAGINATION' => $pagination,
        ));

        require_code('templates_internalise_screen');
        return internalise_own_screen($tpl);
    }

    /**
     * Make sure the entry rules of a quiz are not being broken. Exits when they may not enter.
     *
     * @param  array $quiz The DB row of the quiz
     */
    public function enforcement_checks($quiz)
    {
        // Check they are not a guest trying to do a quiz a guest could not do
        if ((is_guest()) && (($quiz['q_points_for_passing'] != 0) || ($quiz['q_redo_time'] !== null) || ($quiz['q_num_winners'] != 0))) {
            access_denied('NOT_AS_GUEST');
        }

        // Check they are on the necessary newsletter, if appropriate
        if (($quiz['q_tied_newsletter'] !== null) && (addon_installed('newsletter'))) {
            $on = $GLOBALS['SITE_DB']->query_select_value_if_there('newsletter_subscribe', 'email', array('newsletter_id' => $quiz['q_tied_newsletter'], 'email' => $GLOBALS['FORUM_DRIVER']->get_member_email_address(get_member())));
            if ($on === null) {
                warn_exit(do_lang_tempcode('NOT_ON_NEWSLETTER'));
            }
        }

        // Check it is open
        if ((($quiz['q_close_time'] !== null) && ($quiz['q_close_time'] < time())) || ($quiz['q_open_time'] > time())) {
            warn_exit(do_lang_tempcode('NOT_OPEN_THIS', do_lang_tempcode($quiz['q_type'])));
        }

        // Check they are allowed to do this (if repeating)
        if ((!has_privilege(get_member(), 'bypass_quiz_repeat_time_restriction')) && ($quiz['q_redo_time'] !== null)) {
            $last_entry = $GLOBALS['SITE_DB']->query_select_value_if_there('quiz_entries', 'q_time', array('q_member' => get_member(), 'q_quiz' => $quiz['id']), 'ORDER BY q_time DESC');
            if (($last_entry !== null) && ($last_entry + $quiz['q_redo_time'] * 60 * 60 > time()) && (($quiz['q_timeout'] === null) || (time() - $last_entry >= $quiz['q_timeout']))) { // If passed timeout and less than redo time, error
                warn_exit(do_lang_tempcode('REPEATING_TOO_SOON', get_timezoned_date_time($last_entry + $quiz['q_redo_time'] * 60 * 60)));
            }
        }
    }

    /**
     * The UI for doing a quiz.
     *
     * @return Tempcode The result of execution.
     */
    public function do_quiz()
    {
        $quiz_id = $this->quiz_id;
        $quiz = $this->quiz;
        $quiz_name = $this->quiz_name;
        $title_to_use = $this->title_to_use;
        $title_to_use_2 = $this->title_to_use_2;

        if (has_privilege(get_member(), 'bypass_quiz_timer')) {
            $quiz['q_timeout'] = null;
        }

        $this->enforcement_checks($quiz);

        $last_visit_time = $GLOBALS['SITE_DB']->query_select_value_if_there('quiz_member_last_visit', 'v_time', array('v_quiz_id' => $quiz_id, 'v_member_id' => get_member()), 'ORDER BY v_time DESC');
        if ($last_visit_time !== null) { // Refresh / new attempt
            $timer_offset = time() - $last_visit_time;
            if (($quiz['q_timeout'] === null) || ($timer_offset >= $quiz['q_timeout'] * 60)) { // Treat as a new attempt. Must be within redo time to get here
                $GLOBALS['SITE_DB']->query_delete('quiz_member_last_visit', array(
                    'v_member_id' => get_member(),
                    'v_quiz_id' => $quiz_id,
                ));
                $GLOBALS['SITE_DB']->query_insert('quiz_member_last_visit', array(
                    'v_quiz_id' => $quiz_id,
                    'v_time' => time(),
                    'v_member_id' => get_member(),
                ));
                $timer_offset = 0;
            }
        } else {
            $GLOBALS['SITE_DB']->query_insert('quiz_member_last_visit', array( // First attempt
                'v_quiz_id' => $quiz_id,
                'v_time' => time(),
                'v_member_id' => get_member(),
            ));
            $timer_offset = 0;
        }

        $all_required = true;

        $questions = $GLOBALS['SITE_DB']->query_select('quiz_questions', array('*'), array('q_quiz' => $quiz_id), 'ORDER BY q_order');
        if ($quiz['q_shuffle_questions'] == 1) {
            shuffle($questions);
        }
        foreach ($questions as $i => $question) {
            if ($question['q_required'] == 0) {
                $all_required = false;
            }

            $answers = $GLOBALS['SITE_DB']->query_select('quiz_question_answers', array('*'), array('q_question' => $question['id']), 'ORDER BY q_order');
            if ($quiz['q_shuffle_answers'] == 1) {
                shuffle($answers);
            }
            $questions[$i]['answers'] = $answers;
        }

        $fields = render_quiz($questions);

        // Validation
        if (($quiz['q_validated'] == 0) && (addon_installed('unvalidated'))) {
            if ((!has_privilege(get_member(), 'jump_to_unvalidated')) && ((is_guest()) || ($quiz['q_submitter'] != get_member()))) {
                access_denied('PRIVILEGE', 'jump_to_unvalidated');
            }

            $warning_details = do_template('WARNING_BOX', array(
                '_GUID' => 'fc690dedf8601cc456e011931dfec595',
                'WARNING' => do_lang_tempcode((get_param_integer('redirected', 0) == 1) ? 'UNVALIDATED_TEXT_NON_DIRECT' : 'UNVALIDATED_TEXT', 'quiz'),
            ));
        } else {
            $warning_details = new Tempcode();
        }

        $edit_url = new Tempcode();
        if ((has_actual_page_access(null, 'cms_quiz', null, null)) && (has_edit_permission('mid', get_member(), $quiz['q_submitter'], 'cms_quiz', array('quiz', $quiz_id)))) {
            $edit_url = build_url(array('page' => 'cms_quiz', 'type' => '_edit', 'id' => $quiz_id), get_module_zone('cms_quiz'));
        }

        // Display UI: start text, questions. Including timeout
        $start_text = get_translated_tempcode('quizzes', $quiz, 'q_start_text');
        $post_url = build_url(array('page' => '_SELF', 'type' => '_do', 'id' => $quiz_id), '_SELF');
        return do_template('QUIZ_SCREEN', array(
            '_GUID' => 'f390877672938ba62f79f9528bef742f',
            'EDIT_URL' => $edit_url,
            'TAGS' => get_loaded_tags('quiz'),
            'ID' => strval($quiz_id),
            'WARNING_DETAILS' => $warning_details,
            'URL' => $post_url,
            'TITLE' => $this->title,
            'START_TEXT' => $start_text,
            'FIELDS' => $fields,
            'TIMEOUT' => ($quiz['q_timeout'] === null) ? '' : strval($quiz['q_timeout'] * 60 - $timer_offset),
            'ALL_REQUIRED' => $all_required,
        ));
    }

    /**
     * Actualiser: process quiz results.
     *
     * @return Tempcode The result of execution.
     */
    public function _do_quiz()
    {
        $quiz_id = $this->quiz_id;
        $quiz = $this->quiz;
        $quiz_name = get_translated_text($quiz['q_name']);

        if (has_privilege(get_member(), 'bypass_quiz_timer')) {
            $quiz['q_timeout'] = null;
        }

        $last_visit_time = $GLOBALS['SITE_DB']->query_select_value_if_there('quiz_member_last_visit', 'v_time', array('v_quiz_id' => $quiz_id, 'v_member_id' => get_member()), 'ORDER BY v_time DESC');
        if ($last_visit_time === null) {
            warn_exit(do_lang_tempcode('QUIZ_TWICE'));
        }
        if ($quiz['q_timeout'] !== null) {
            if (time() - $last_visit_time > $quiz['q_timeout'] * 60 + 10) {
                warn_exit(do_lang_tempcode('TOO_LONG_ON_SCREEN')); // +10 is for page load time, worst case scenario to be fair
            }
        }

        // Save our entry
        $entry_id = $GLOBALS['SITE_DB']->query_insert('quiz_entries', array(
            'q_time' => time(),
            'q_member' => get_member(),
            'q_quiz' => $quiz_id,
            'q_results' => 0,
        ), true);
        $questions = $GLOBALS['SITE_DB']->query_select('quiz_questions', array('*'), array('q_quiz' => $quiz_id), 'ORDER BY q_order');
        foreach ($questions as $i => $question) {
            $answers = $GLOBALS['SITE_DB']->query_select('quiz_question_answers', array('*'), array('q_question' => $question['id']), 'ORDER BY id');
            $questions[$i]['answers'] = $answers;
        }
        foreach ($questions as $i => $question) {
            if ($question['q_type'] == 'SHORT' || $question['q_type'] == 'SHORT_STRICT' || $question['q_type'] == 'LONG') { // Text box ("free question"). May be an actual answer, or may not be
                $GLOBALS['SITE_DB']->query_insert('quiz_entry_answer', array(
                    'q_entry' => $entry_id,
                    'q_question' => $question['id'],
                    'q_answer' => post_param_string('q_' . strval($question['id']), ''),
                ));
            } elseif ($question['q_type'] == 'MULTIMULTIPLE') { // Check boxes
                $accum = new Tempcode();
                foreach ($question['answers'] as $a) {
                    if (post_param_integer('q_' . strval($question['id']) . '_' . strval($a['id']), 0) == 1) {
                        $GLOBALS['SITE_DB']->query_insert('quiz_entry_answer', array(
                            'q_entry' => $entry_id,
                            'q_question' => $question['id'],
                            'q_answer' => strval($a['id']),
                        ));
                    }
                }
            } elseif ($question['q_type'] == 'MULTIPLECHOICE') { // Radio buttons
                $GLOBALS['SITE_DB']->query_insert('quiz_entry_answer', array(
                    'q_entry' => $entry_id,
                    'q_question' => $question['id'],
                    'q_answer' => post_param_string('q_' . strval($question['id']), ''),
                ));
            }
        }
        $GLOBALS['SITE_DB']->query_update('quiz_member_last_visit', array( // Say quiz was completed on time limit, to force next attempt to be considered a re-do
            'v_time' => time() - (($quiz['q_timeout'] === null) ? 0 : $quiz['q_timeout']) * 60,
        ), array(
            'v_member_id' => get_member(), 'v_quiz_id' => $quiz_id,
        ), '', 1);

        // Calculate results
        list(
            $marks,
            $potential_extra_marks,
            $out_of,
            $given_answers,
            $corrections,
            $affirmations,
            $unknowns,
            $minimum_percentage,
            $maximum_percentage,
            $marks_range,
            $percentage_range,
            $corrections_to_staff,
            $corrections_to_member,
            $affirmations_to_member,
            $unknowns_to_staff,
            $given_answers_to_staff,
            $passed,
            ) = score_quiz($entry_id, $quiz_id, $quiz, $questions);

        // Award points?
        if ((addon_installed('points')) && ($quiz['q_points_for_passing'] != 0) && (($quiz['q_type'] != 'TEST') || ($passed === true))) {
            require_code('points2');
            $points_difference = $quiz['q_points_for_passing'];
            system_gift_transfer(do_lang('POINTS_COMPLETED_QUIZ', $quiz_name), $points_difference, get_member());
        } else {
            $points_difference = 0;
        }

        // Give them their result if it is a test.
        require_code('notifications');
        $notification_title = do_lang(
            'QUIZ_NOTIFICATION_TITLE',
            do_lang($quiz['q_type']),
            $GLOBALS['FORUM_DRIVER']->get_username(get_member()),
            array(
                strval($entry_id),
                $quiz_name,
            ),
            get_site_default_lang()
        );
        switch ($quiz['q_type']) {
            // Show results if a test
            case 'TEST':
                if ($passed === true) { // Passed
                    $result_to_member = do_lang_tempcode('TEST_PASS', escape_html($marks_range), escape_html(integer_format($out_of)), escape_html($percentage_range));
                    $result_to_staff = do_lang('MAIL_TEST_PASS', comcode_escape($marks_range), comcode_escape(integer_format($out_of)), comcode_escape($percentage_range));

                    // Syndicate because passed
                    require_code('activities');
                    syndicate_described_activity('quiz:ACTIVITY_PASSED_TEST', $quiz_name, '', '', '_SEARCH:quiz:do:' . strval($quiz_id), '', '', 'quizzes');
                } elseif ($passed === false) { // Failed
                    $result_to_member = do_lang_tempcode('TEST_FAIL', escape_html($marks_range), escape_html(integer_format($out_of)), escape_html($percentage_range));
                    $result_to_staff = do_lang('MAIL_TEST_FAIL', comcode_escape($marks_range), comcode_escape(integer_format($out_of)), comcode_escape($percentage_range));
                } else { // Unknown
                    $result_to_member = do_lang_tempcode('TEST_UNKNOWN', escape_html($marks_range), escape_html(integer_format($out_of)), escape_html($percentage_range));
                    $result_to_staff = do_lang('MAIL_TEST_UNKNOWN', comcode_escape($marks_range), comcode_escape(integer_format($out_of)), comcode_escape($percentage_range));
                }

                // Send notification about the result to the staff: include result and corrections, and unknowns
                $mail = do_notification_template('QUIZ_TEST_ANSWERS_MAIL', array(
                    '_GUID' => 'a0f8f47cdc1ef83b59c93135ebb5c114',
                    'ENTRY_ID' => strval($entry_id),
                    'QUIZ_NAME' => $quiz_name,
                    'GIVEN_ANSWERS_ARR' => $given_answers,
                    'GIVEN_ANSWERS' => $given_answers_to_staff,
                    'UNKNOWNS' => $unknowns_to_staff,
                    'CORRECTIONS' => $corrections_to_staff,
                    'RESULT' => $result_to_staff,
                    'USERNAME' => $GLOBALS['FORUM_DRIVER']->get_username(get_member()),
                ), null, false, null, '.txt', 'text');
                dispatch_notification('quiz_results', strval($quiz_id), $notification_title, $mail->evaluate(get_site_default_lang()));

                break;

            // Give them corrections if it is a competition
            case 'COMPETITION':
                $result_to_member = do_lang_tempcode('COMPETITION_THANKYOU');

                // No notification to staff for competitions, as we expect lots of entries to happen; it should all be reviewed when the competition closes

                // Syndicate
                require_code('activities');
                syndicate_described_activity('quiz:ACTIVITY_ENTERED_COMPETITION', $quiz_name, '', '', '_SEARCH:quiz:do:' . strval($quiz_id), '', '', 'quizzes');

                break;

            // Show everything if it is a survey
            case 'SURVEY':
                $result_to_member = do_lang_tempcode('SURVEY_THANKYOU');

                $given_answers_to_staff = do_notification_template('QUIZ_SURVEY_ANSWERS_MAIL', array(
                    '_GUID' => '381f392c8e491b6e078bcae34adc45e8',
                    'ENTRY_ID' => strval($entry_id),
                    'QUIZ_NAME' => $quiz_name,
                    'GIVEN_ANSWERS_ARR' => $given_answers,
                    'GIVEN_ANSWERS' => $given_answers_to_staff,
                    'MEMBER_PROFILE_URL' => is_guest() ? '' : $GLOBALS['FORUM_DRIVER']->member_profile_url(get_member(), true),
                    'USERNAME' => $GLOBALS['FORUM_DRIVER']->get_username(get_member()),
                ), null, false, null, '.txt', 'text');

                // Send notification of answers to the staff
                dispatch_notification('quiz_results', strval($quiz_id), $notification_title, $given_answers_to_staff->evaluate(get_site_default_lang()));

                // Syndicate
                require_code('activities');
                syndicate_described_activity('quiz:ACTIVITY_FILLED_SURVEY', $quiz_name, '', '', '_SEARCH:quiz:do:' . strval($quiz_id), '', '', 'quizzes');

                break;

            // ??!
            default:
                warn_exit(do_lang_tempcode('INTERNAL_ERROR'));
        }

        // Store results for entry
        $GLOBALS['SITE_DB']->query_update('quiz_entries', array('q_results' => intval(round($marks))), array('id' => $entry_id), '', 1);

        // Show completion summary / results
        $fail_text = get_translated_tempcode('quizzes', $quiz, 'q_end_text_fail');
        $message = (($quiz['q_type'] != 'TEST') || ($passed) || ($fail_text->is_empty())) ? get_translated_tempcode('quizzes', $quiz, 'q_end_text') : $fail_text;
        $reveal_answers = ($quiz['q_reveal_answers'] == 1) && ($quiz['q_type'] == 'TEST');
        return do_template('QUIZ_DONE_SCREEN', array(
            '_GUID' => 'fa783f087eca7f8f577b134ec0bdc4ce',
            'TITLE' => $this->title,
            'ENTRY_ID' => strval($entry_id),
            'QUIZ_NAME' => $quiz_name,
            'GIVEN_ANSWERS_ARR' => $given_answers,
            'CORRECTIONS' => $corrections_to_member,
            'AFFIRMATIONS' => $affirmations_to_member,
            'PASSED' => $passed,
            'POINTS_DIFFERENCE' => strval($points_difference),
            'RESULT' => $result_to_member,
            'TYPE' => do_lang($quiz['q_type']),
            '_TYPE' => $quiz['q_type'],
            'MESSAGE' => $message,
            'REVEAL_ANSWERS' => $reveal_answers,
            'MARKS' => strval($marks),
            'POTENTIAL_EXTRA_MARKS' => strval($potential_extra_marks),
            'OUT_OF' => strval($out_of),
            'MARKS_RANGE' => $marks_range,
            'PERCENTAGE_RANGE' => $percentage_range,
        ));
    }
}
