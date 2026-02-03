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
 * @package    testing_platform
 */

/*
    NB: This is a very sensitive test and needs to be able to clean itself up (or other tests and the site as a whole will break).
    As such, all tests should take place in testStats to ensure tearDown runs and only ever runs once.
*/

/**
 * Composr test case class (unit testing).
 */
class stats_test_set extends cms_test_case
{
    protected $dummy_data_added = [];
    protected $tracker_issue = null;
    public function setUp()
    {
        parent::setUp();

        disable_php_memory_limit();
        cms_set_time_limit(TIME_LIMIT_EXTEND__CRAWL);

        require_code('stats');
        require_code('temporal');
        require_lang('stats');
        require_code('developer_tools');

        push_query_limiting(false);
    }

    public function testStats()
    {
        /* Generating stats */

        $dummy_data = [
            // 'table_name' => [array of forced field=>value maps; one row will be created for each array item],
            'actionlogs' => [
                [],
                ['the_type' => 'ACCESSED_ADMIN_ZONE']
            ],
            'f_moderator_logs' => [[]],
            'banner_clicks' => [[]],
            'f_topics' => [
                [],
                ['_ALLOW_NULL_' => false],
                ['_ALLOW_NULL_' => false, 't_forum_id' => null],
            ],
            'f_posts' => [
                [],
                ['_ALLOW_NULL_' => false, 'p_whisper_to_member' => null],
                ['p_whisper_to_member' => 2],
            ],
            'f_poll_votes' => [
                [],
                ['pv_revoked' => 0],
            ],
            'f_members' => [
                [],
                ['m_dob_year' => 2000, 'm_dob_month' => 1, 'm_dob_day' => 1],
                ['m_cache_num_posts' => 10],
            ],
            'download_logging' => [[]],
            'logged_mail_messages' => [[]],
            'stats_known_events' => [[]],
            'stats_known_tracking' => [[]],
            'stats_events' => [[]],
            'stats' => [
                [],
                ['member_id' => 2],
                ['browser' => 'Mozilla Firefox'],
                ['referer_url' => 'https://example.com'],
            ],
            'f_invites' => [[]],
            'stats_link_tracker' => [[]],
            'stats_known_links' => [[]],
            'newsletter_subscribers' => [
                [],
                ['code_confirm' => 0],
            ],
            'poll_votes' => [[]],
            'rating' => [[]],
            'searches_logged' => [[]],
            'failedlogins' => [[]],
            'hackattack' => [[]],
            'sitemap_cache' => [
                [],
                ['_ALLOW_NULL_' => false, 'page_link' => ':' . uniqid('', false)],
            ],
            'ecom_subscriptions' => [[]],
            'ecom_transactions' => [
                [],
                ['t_status' => 'Completed'],
            ],
            'usersonline_track' => [[]],
            'f_warnings' => [[]],

            'authors' => [[]],
            'banners' => [[]],
            'banner_types' => [[]],
            'calendar_types' => [[]],
            'catalogues' => [[]],
            'catalogue_categories' => [[]],
            'catalogue_entries' => [[]],
            'chat_rooms' => [[]],
            'comcode_pages' => [[]],
            'download_downloads' => [[]],
            'download_categories' => [[]],
            'calendar_events' => [[]],
            'f_forums' => [[]],
            'galleries' => [[]],
            'f_groups' => [[]],
            'images' => [[]],
            'news' => [[]],
            'news_categories' => [[]],
            'poll' => [[]],
            'quizzes' => [[]],
            'videos' => [[]],
            'wiki_pages' => [[]],
            'wiki_posts' => [[]],
            'unsubscribed_emails' => [[]],
        ];
        if (addon_installed('tickets')) {
            require_code('tickets');
            $dummy_data['f_topics'][] = ['_ALLOW_NULL_' => false, 't_forum_id' => get_ticket_forum_id()];
        }
        if (addon_installed('points')) {
            $dummy_data['points_ledger'] = [['amount_gift_points' => 100, 'amount_points' => 100, 'status' => 0]];
            $dummy_data['points_ledger'][] = ['amount_gift_points' => 0, 'amount_points' => 100, 'status' => 0, 'sending_member' => $GLOBALS['FORUM_DRIVER']->get_guest_id(), 'receiving_member' => 2];
            $dummy_data['points_ledger'][] = ['amount_gift_points' => 0, 'amount_points' => 100, 'status' => 0, 'sending_member' => 2, 'receiving_member' => $GLOBALS['FORUM_DRIVER']->get_guest_id()];
            $dummy_data['points_ledger'][] = ['amount_gift_points' => 0, 'amount_points' => 100, 'status' => 0, 'sending_member' => 3, 'receiving_member' => 2];
            $dummy_data['points_ledger'][] = ['amount_gift_points' => 1, 'amount_points' => 0, 'status' => 0, 'sending_member' => 3, 'receiving_member' => 2];

            // FUDGE: so we can test top members by points (which uses CPF and not database)
            $GLOBALS['FORUM_DRIVER']->set_custom_field(2, 'points_rank', '1000');
        }

        if (addon_installed('cms_homesite')) {
            $dummy_data['telemetry_errors'] = [[], ['e_resolved' => 0], ['e_resolved' => 1]];
        }

        if (addon_installed('cms_homesite_tracker') && addon_installed('catalogues')) {
            require_code('catalogues');
            require_code('cms_homesite_tracker');

            require_lang('tracker');

            $category = $GLOBALS['SITE_DB']->query_select_value('catalogue_categories', 'id', ['c_name' => 'tracker', $GLOBALS['SITE_DB']->translate_field_ref('cc_title') => do_lang('TRACKER_CATALOGUE_CATEGORY_1')]);
            $this->tracker_issue = create_tracker_issue(
                '1111',
                'dummy issue',
                'minor',
                'dummy description',
                'dummy additional',
                'core',
                $category
            );
        }

        // Remove old preprocessed stats so we can force pre-processing again
        $GLOBALS['SITE_DB']->query_delete('stats_preprocessed');
        $GLOBALS['SITE_DB']->query_delete('stats_preprocessed_flat');
        $GLOBALS['SITE_DB']->query_delete('stats_preprocessed_delta');

        // Generate dummy data so we can process stats on them
        foreach ($dummy_data as $table => $rows) {
            foreach ($rows as $forced_map) {
                if (isset($forced_map['_ALLOW_NULL_'])) {
                    $allow_null = $forced_map['_ALLOW_NULL_'];
                } else {
                    $allow_null = true;
                }
                if (!isset($this->dummy_data_added[$table])) {
                    $this->dummy_data_added[$table] = [];
                }
                $this->dummy_data_added[$table][] = make_dummy_db_row($table, $allow_null, $forced_map);
            }
        }

        $p_day = to_epoch_interval_index(time(), 'days');
        $server_timezone = get_server_timezone();

        $today = cms_date('Y-m-d');
        list($year, $month, $day) = array_map('intval', explode('-', $today));
        $end_time = cms_mktime(0, 0, 0, $month, $day, $year) + (60 * 60 * 31);
        $end_time = tz_time($end_time, $server_timezone);
        $start_time = cms_mktime(0, 0, 0, $month, $day, $year) - (60 * 60 * 24 * 365);
        $start_time = tz_time($start_time, $server_timezone);

        $hook_obs = find_all_hook_obs('modules', 'admin_stats', 'Hook_admin_stats_');
        $buckets = [];
        $buckets_existing = [];
        foreach ($hook_obs as $hook_name => $ob) {
            $_buckets = $ob->info();
            if ($_buckets === null) {
                continue;
            }
            $buckets = array_merge($buckets, array_keys($_buckets));
            preprocess_raw_data_for($hook_name, $start_time, $end_time);
        }

        stats_merge_deltas(0);

        $count = $GLOBALS['SITE_DB']->get_table_count_approx('stats_preprocessed_delta');
        $this->assertTrue(($count == 0), 'Expected all rows in stats_preprocessed_delta to get processed, but ' . integer_format($count) . ' rows did not.');

        $rows = $GLOBALS['SITE_DB']->query_select('stats_preprocessed', ['DISTINCT p_bucket']);
        foreach ($rows as $row) {
            $buckets_existing[] = $row['p_bucket'];
        }
        $rows = $GLOBALS['SITE_DB']->query_select('stats_preprocessed_flat', ['p_bucket']);
        foreach ($rows as $row) {
            $buckets_existing[] = $row['p_bucket'];
        }

        $buckets = array_unique($buckets);
        $buckets_existing = array_unique($buckets_existing);
        $buckets_diff = array_diff($buckets, $buckets_existing);

        $this->assertTrue(empty($buckets_diff), 'Expected pre-processed statistical data on these buckets but got none (maybe you need to define in this test the db tables used so dummy data can be generated): ' . implode(', ', $buckets_diff));

        /* Final Data */

        $bucket_hook = [];
        $bucket_filters = [];
        $hook_obs = find_all_hook_obs('modules', 'admin_stats', 'Hook_admin_stats_');
        foreach ($hook_obs as $hook_name => $ob) {
            $_buckets = $ob->info();
            if ($_buckets === null) {
                continue;
            }
            foreach (array_keys($_buckets) as $bucket) {
                $bucket_hook[$bucket] = $hook_name;
                $bucket_filters[$bucket] = (((isset($_buckets['filters'])) && (is_array($_buckets['filters']))) ? $_buckets['filters'] : []);
            }
        }

        $rows = $GLOBALS['SITE_DB']->query_select('stats_preprocessed', ['p_bucket', 'p_pivot']);
        foreach ($rows as $row) {
            $this->assertTrue(isset($bucket_hook[$row['p_bucket']]), 'Orphaned bucket in database: ' . $row['p_bucket']);
            if (isset($bucket_hook[$row['p_bucket']])) {
                $this->run_filter_tests($bucket_filters[$row['p_bucket']], $hook_obs[$bucket_hook[$row['p_bucket']]], $row['p_bucket'], $row['p_pivot'], $p_day);
            }
        }

        $rows = $GLOBALS['SITE_DB']->query_select('stats_preprocessed_flat', ['p_bucket'], []);
        foreach ($rows as $row) {
            $this->assertTrue(isset($bucket_hook[$row['p_bucket']]), 'Orphaned bucket in database: ' . $row['p_bucket']);
            if (isset($bucket_hook[$row['p_bucket']])) {
                $this->run_filter_tests($bucket_filters[$row['p_bucket']], $hook_obs[$bucket_hook[$row['p_bucket']]], $row['p_bucket'], '', $p_day);
            }
        }
    }

    protected function run_filter_tests(array $filters, $hook, string $bucket, string $pivot, int $p_day)
    {
        // Test that the filters do not cause crashes (TODO: does not yet actually test the filters filter as they should)
        foreach ($filters as $filter_name => $filter_class) {
            // Test day range filters
            if ($filter_class instanceof Source_stats_filter_day_range) {
                // Test integer filter
                $data = $hook->generate_final_data($bucket, $pivot, [
                    $filter_name => $p_day
                ]);
                $this->assertTrue(is_array($data) && (count($data) == 4), 'Did not receive standardised map data for day range filter (integer) on ' . $bucket . '=>' . $pivot . '=>' . $filter_name);

                // Test integer filter in array format
                $data = $hook->generate_final_data($bucket, $pivot, [
                    $filter_name => [$p_day]
                ]);
                $this->assertTrue(is_array($data) && (count($data) == 4), 'Did not receive standardised map data for day range filter (single item array) on ' . $bucket . '=>' . $pivot . '=>' . $filter_name);

                // Test integer filter in array range format
                $data = $hook->generate_final_data($bucket, $pivot, [
                    $filter_name => [$p_day - 1, $p_day]
                ]);
                $this->assertTrue(is_array($data) && (count($data) == 4), 'Did not receive standardised map data for day range filter (array range) on ' . $bucket . '=>' . $pivot . '=>' . $filter_name);
            }

            // Test text filters
            if ($filter_class instanceof Source_stats_filter_text) {
                // Test blank filter
                $data = $hook->generate_final_data($bucket, $pivot, [
                    $filter_name => ''
                ]);
                $this->assertTrue(is_array($data) && (count($data) == 4), 'Did not receive standardised map data for text filter (blank) on ' . $bucket . '=>' . $pivot . '=>' . $filter_name);

                // Test non-blank filter
                $data = $hook->generate_final_data($bucket, $pivot, [
                    $filter_name => 'qwertyuiop'
                ]);
                $this->assertTrue(is_array($data) && (count($data) == 4), 'Did not receive standardised map data for text filter (blank) on ' . $bucket . '=>' . $pivot . '=>' . $filter_name);
            }

            // Test tick filters
            if ($filter_class instanceof Source_stats_filter_tick) {
                // Test un-ticked
                $data = $hook->generate_final_data($bucket, $pivot, [
                    $filter_name => '0'
                ]);
                $this->assertTrue(is_array($data) && (count($data) == 4), 'Did not receive standardised map data for tick filter (off) on ' . $bucket . '=>' . $pivot . '=>' . $filter_name);

                // Test ticked
                $data = $hook->generate_final_data($bucket, $pivot, [
                    $filter_name => '1'
                ]);
                $this->assertTrue(is_array($data) && (count($data) == 4), 'Did not receive standardised map data for tick filter (on) on ' . $bucket . '=>' . $pivot . '=>' . $filter_name);
            }

            // Test list filters
            if ($filter_class instanceof Source_stats_filter_list) {
                foreach ($filter_class->get_list_values() as $key => $val) {
                    $data = $hook->generate_final_data($bucket, $pivot, [
                        $filter_name => $val
                    ]);
                    $this->assertTrue(is_array($data) && (count($data) == 4), 'Did not receive standardised map data for list filter (' . $val . ') on ' . $bucket . '=>' . $pivot . '=>' . $filter_name);
                }
            }

            // Test pivot filters
            if ($filter_class instanceof Source_stats_filter_date_pivot) {
                foreach ($filter_class->get_pivot_values() as $key => $val) {
                    $data = $hook->generate_final_data($bucket, $pivot, [
                        $filter_name => $val
                    ]);
                    $this->assertTrue(is_array($data) && (count($data) == 4), 'Did not receive standardised map data for pivot filter (' . $val . ') on ' . $bucket . '=>' . $pivot . '=>' . $filter_name);
                }
            }
        }
    }

    public function tearDown()
    {
        // Remove old preprocessed stats so we can force pre-processing again
        $GLOBALS['SITE_DB']->query_delete('stats_preprocessed');
        $GLOBALS['SITE_DB']->query_delete('stats_preprocessed_flat');
        $GLOBALS['SITE_DB']->query_delete('stats_preprocessed_delta');

        // Delete dummy data
        if (count($this->dummy_data_added) > 0) {
            foreach ($this->dummy_data_added as $table => $rows) {
                $db = get_db_for($table);
                foreach ($rows as $primary_map) {
                    if ($this->debug) {
                        $rows = $db->query_select($table, ['*'], $primary_map);
                        $this->dump($primary_map, $table . ' primary map');
                        $this->dump($rows, $table . ' data');
                    }
                    $db->query_delete($table, $primary_map);
                }
            }

            // FUDGE: We had to fudge points lifetime so re-calculate this
            if (addon_installed('points')) {
                require_code('tasks');
                call_user_func_array__long_task(do_lang('points:POINTS_CACHE'), null, 'points_recalculate_cpf', [], true, true, false);
            }
        }

        if ($this->tracker_issue !== null) {
            require_code('catalogues2');
            actual_delete_catalogue_entry($this->tracker_issue[0]);
        }

        $server_timezone = get_server_timezone();

        $today = cms_date('Y-m-d');
        list($year, $month, $day) = array_map('intval', explode('-', $today));
        $end_time = cms_mktime(0, 0, 0, $month, $day, $year) + (60 * 60 * 31);
        $end_time = tz_time($end_time, $server_timezone);
        $start_time = cms_mktime(0, 0, 0, $month, $day, $year) - (60 * 60 * 24 * 365);
        $start_time = tz_time($start_time, $server_timezone);

        // Re-populate with actual statistics so stats do not show our dummy ones
        $hook_obs = find_all_hook_obs('modules', 'admin_stats', 'Hook_admin_stats_');
        foreach ($hook_obs as $hook_name => $ob) {
            $_buckets = $ob->info();
            if ($_buckets === null) {
                continue;
            }
            preprocess_raw_data_for($hook_name, $start_time, $end_time);
        }

        pop_query_limiting();

        parent::tearDown();
    }
}
