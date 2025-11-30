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
class Hook_admin_stats_cms_homesite extends Source_hook_stats_provider
{
    /**
     * Find metadata about stats categories that are defined by this stats hook.
     *
     * @return ?array Map of metadata (null: hook is disabled)
     */
    public function category_info() : ?array
    {
        if (!addon_installed('cms_homesite')) {
            return null;
        }

        require_lang('cms_homesite');

        return [
            'cms_homesite' => [
                'label_lang_string' => 'STATS_CATEGORY_cms_homesite',
                'icon' => 'spare/development',
            ],
        ];
    }

    /**
     * Find metadata about stats graphs that are provided by this stats hook.
     *
     * @param  boolean $for_kpi Whether this is for setting up a KPI
     * @return ?array Map of metadata (null: hook is disabled)
     */
    public function info(bool $for_kpi = false) : ?array
    {
        if (!addon_installed('cms_homesite')) {
            return null;
        }

        $ret = [];
        $ret['relayed_errors'] = [
            'label' => do_lang_tempcode('CMS_SITE_ERRORS'),
            'category' => 'cms_homesite',
            'filters' => [
                'relayed_errors__day_range' => new Source_stats_filter_day_range('relayed_errors__day_range', do_lang_tempcode('DATE_RANGE'), null, $for_kpi),
                'relayed_errors__resolved' => new Source_stats_filter_tick('relayed_errors__resolved', do_lang_tempcode('RESOLVED'), true),
            ],
            'pivot' => new Source_stats_filter_date_pivot('relayed_errors__pivot', $this->get_date_pivots(!$for_kpi)),
            'support_kpis' => null,
        ];

        if (addon_installed('cms_homesite_tracker') && addon_installed('catalogues')) {
            require_code('addons2');

            require_lang('tracker');
            require_lang('addons');

            // Get tracker issue statuses
            $tracker_statuses = ['all' => do_lang('ALL')];
            $statuses = explode('|', do_lang('TRACKER_CATALOGUE_STATUS_DEFAULT'));
            foreach ($statuses as $status) {
                $tracker_statuses[$status] = do_lang('TRACKER_CATALOGUE_STATUS_DEFAULT_' . $status);
            }

            $ret['tracker_issue_activity'] = [
                'label' => do_lang_tempcode('TRACKER_ISSUE_ACTIVITY'),
                'category' => 'cms_homesite',
                'filters' => [
                    'tracker_issue_activity__day_range' => new Source_stats_filter_day_range('tracker_issue_activity__day_range', do_lang_tempcode('DATE_RANGE'), null, $for_kpi),
                    'tracker_issue_activity__status' => new Source_stats_filter_list('tracker_issue_activity__status', do_lang_tempcode('STATUS'), $tracker_statuses),
                ],
                'pivot' => new Source_stats_filter_date_pivot('tracker_issue_activity__pivot', $this->get_date_pivots(!$for_kpi)),
                'support_kpis' => self::KPI_HIGH_IS_GOOD,
            ];

            // Get tracker addons
            $available_addons = array_keys(find_available_addons(false, false, [], false, true));
            $installed_addons = array_keys(find_installed_addons(false, false, false));
            $_addons = array_unique(array_merge($available_addons, $installed_addons));
            $addons = ['all' => do_lang('ALL')];
            foreach ($_addons as $addon) {
                $addons[$addon] = $addon;
            }

            $ret['tracker_issues'] = [
                'label' => do_lang_tempcode('TRACKER_ISSUES'),
                'category' => 'cms_homesite',
                'filters' => [
                    'tracker_issues__day_range' => new Source_stats_filter_day_range('tracker_issues__day_range', do_lang_tempcode('DATE_RANGE'), null, $for_kpi),
                    'tracker_issues__addon' => new Source_stats_filter_list('tracker_issues__addon', do_lang_tempcode('ADDON'), $addons),
                ],
                'pivot' => new Source_stats_filter_date_pivot('tracker_issues__pivot', $this->get_date_pivots(!$for_kpi)),
                'support_kpis' => self::KPI_HIGH_IS_GOOD,
            ];
        }

        return $ret;
    }

    /**
     * Preprocess raw data in the database into something we can efficiently draw graphs/conclusions from.
     *
     * @param  TIME $start_time Start timestamp
     * @param  TIME $end_time End timestamp
     */
    public function preprocess_raw_data(int $start_time, int $end_time)
    {
        require_code('temporal');

        $server_timezone = get_server_timezone();

        $date_pivots = $this->get_date_pivots();

        /* relayed errors */

        $max = 1000;
        $start = 0;

        $query = 'SELECT e_first_date_and_time,e_resolved FROM ' . $GLOBALS['SITE_DB']->get_table_prefix() . 'telemetry_errors WHERE ';
        $query .= 'e_first_date_and_time>=' . strval($start_time) . ' AND ';
        $query .= 'e_first_date_and_time<=' . strval($end_time);
        $query .= ' ORDER BY e_first_date_and_time';
        do {
            $rows = $GLOBALS['SITE_DB']->query($query, $max, $start);
            foreach ($rows as $row) {
                $timestamp = $row['e_first_date_and_time'];
                $timestamp = tz_time($timestamp, $server_timezone);

                $resolved = strval($row['e_resolved']);

                foreach (array_keys($date_pivots) as $pivot) {
                    $pivot_interval = $this->calculate_date_pivot_interval($pivot, $timestamp);
                    $pivot_value = $this->calculate_date_pivot_value($pivot, $timestamp);

                    if (!isset($this->data_buckets['relayed_errors'][$pivot][$pivot_interval][$pivot_value][$resolved])) {
                        $this->data_buckets['relayed_errors'][$pivot][$pivot_interval][$pivot_value][$resolved] = 0;
                    }
                    $this->data_buckets['relayed_errors'][$pivot][$pivot_interval][$pivot_value][$resolved]++;

                    // For all
                    if (!isset($this->data_buckets['relayed_errors'][$pivot][$pivot_interval][$pivot_value][''])) {
                        $this->data_buckets['relayed_errors'][$pivot][$pivot_interval][$pivot_value][''] = 0;
                    }
                    $this->data_buckets['relayed_errors'][$pivot][$pivot_interval][$pivot_value]['']++;
                }

                $this->dump_data_buckets_if_necessary();
            }

            $start += $max;
        } while (!empty($rows));

        /* tracker issue updates */

        if (addon_installed('cms_homesite_tracker') && addon_installed('catalogues')) {
            require_code('catalogues');

            require_lang('tracker');
            require_lang('addons');

            $status_field = $GLOBALS['SITE_DB']->query_select_value('catalogue_fields', 'id', ['c_name' => 'tracker', $GLOBALS['SITE_DB']->translate_field_ref('cf_name') => do_lang('STATUS')]);
            $addon_field = $GLOBALS['SITE_DB']->query_select_value('catalogue_fields', 'id', ['c_name' => 'tracker', $GLOBALS['SITE_DB']->translate_field_ref('cf_name') => do_lang('ADDON')]);

            $max = 100;
            $start = 0;

            $query = 'SELECT * FROM ' . $GLOBALS['SITE_DB']->get_table_prefix() . 'catalogue_entries WHERE ' . db_string_equal_to('c_name', 'tracker') . ' AND ';
            $query .= db_function('COALESCE', ['`ce_edit_date`', '`ce_add_date`']) . '>=' . strval($start_time) . ' AND ';
            $query .= db_function('COALESCE', ['`ce_edit_date`', '`ce_add_date`']) . '<=' . strval($end_time);
            $query .= ' ORDER BY ' . db_function('COALESCE', ['`ce_edit_date`', '`ce_add_date`']);
            do {
                $rows = $GLOBALS['SITE_DB']->query($query, $max, $start);
                foreach ($rows as $row) {
                    $entry_fields = get_catalogue_entry_field_values('tracker', $row['id'], null, null, false);

                    $timestamp = isset($row['ce_edit_date']) ? $row['ce_edit_date'] : $row['ce_add_date'];
                    $timestamp = tz_time($timestamp, $server_timezone);

                    $status = '';
                    $addon = '';
                    foreach ($entry_fields as $field_value) {
                        if ($field_value['id'] == $status_field) {
                            $status = strval($field_value['effective_value_pure']);
                        }
                        if ($field_value['id'] == $addon_field) {
                            $addon = strval($field_value['effective_value_pure']);
                        }
                    }

                    foreach (array_keys($date_pivots) as $pivot) {
                        $pivot_interval = $this->calculate_date_pivot_interval($pivot, $timestamp);
                        $pivot_value = $this->calculate_date_pivot_value($pivot, $timestamp);

                        if (!isset($this->data_buckets['tracker_issue_activity'][$pivot][$pivot_interval][$pivot_value][$status])) {
                            $this->data_buckets['tracker_issue_activity'][$pivot][$pivot_interval][$pivot_value][$status] = 0;
                        }
                        $this->data_buckets['tracker_issue_activity'][$pivot][$pivot_interval][$pivot_value][$status]++;

                        // For all
                        if (!isset($this->data_buckets['tracker_issue_activity'][$pivot][$pivot_interval][$pivot_value]['all'])) {
                            $this->data_buckets['tracker_issue_activity'][$pivot][$pivot_interval][$pivot_value]['all'] = 0;
                        }
                        $this->data_buckets['tracker_issue_activity'][$pivot][$pivot_interval][$pivot_value]['all']++;
                    }

                    $this->dump_data_buckets_if_necessary();
                }

                $start += $max;
            } while (!empty($rows));
        }

        /* tracker issues */

        if (addon_installed('cms_homesite_tracker') && addon_installed('catalogues')) {
            require_code('catalogues');

            require_lang('tracker');
            require_lang('addons');

            $status_field = $GLOBALS['SITE_DB']->query_select_value('catalogue_fields', 'id', ['c_name' => 'tracker', $GLOBALS['SITE_DB']->translate_field_ref('cf_name') => do_lang('STATUS')]);
            $addon_field = $GLOBALS['SITE_DB']->query_select_value('catalogue_fields', 'id', ['c_name' => 'tracker', $GLOBALS['SITE_DB']->translate_field_ref('cf_name') => do_lang('ADDON')]);

            $max = 100;
            $start = 0;

            $query = 'SELECT * FROM ' . $GLOBALS['SITE_DB']->get_table_prefix() . 'catalogue_entries WHERE ' . db_string_equal_to('c_name', 'tracker') . ' AND ';
            $query .= '`ce_add_date`>=' . strval($start_time) . ' AND ';
            $query .= '`ce_add_date`<=' . strval($end_time);
            $query .= ' ORDER BY `ce_add_date`';
            do {
                $rows = $GLOBALS['SITE_DB']->query($query, $max, $start);
                foreach ($rows as $row) {
                    $entry_fields = get_catalogue_entry_field_values('tracker', $row['id'], null, null, false);

                    $timestamp = $row['ce_add_date'];
                    $timestamp = tz_time($timestamp, $server_timezone);

                    $status = '';
                    $addon = '';
                    foreach ($entry_fields as $field_value) {
                        if ($field_value['id'] == $status_field) {
                            $status = strval($field_value['effective_value_pure']);
                        }
                        if ($field_value['id'] == $addon_field) {
                            $addon = strval($field_value['effective_value_pure']);
                        }
                    }

                    foreach (array_keys($date_pivots) as $pivot) {
                        $pivot_interval = $this->calculate_date_pivot_interval($pivot, $timestamp);
                        $pivot_value = $this->calculate_date_pivot_value($pivot, $timestamp);

                        if (!isset($this->data_buckets['tracker_issues'][$pivot][$pivot_interval][$pivot_value][$addon])) {
                            $this->data_buckets['tracker_issues'][$pivot][$pivot_interval][$pivot_value][$addon] = 0;
                        }
                        $this->data_buckets['tracker_issues'][$pivot][$pivot_interval][$pivot_value][$addon]++;

                        // For all
                        if (!isset($this->data_buckets['tracker_issues'][$pivot][$pivot_interval][$pivot_value]['all'])) {
                            $this->data_buckets['tracker_issues'][$pivot][$pivot_interval][$pivot_value]['all'] = 0;
                        }
                        $this->data_buckets['tracker_issues'][$pivot][$pivot_interval][$pivot_value]['all']++;
                    }

                    $this->dump_data_buckets_if_necessary();
                }

                $start += $max;
            } while (!empty($rows));
        }
    }

    /**
     * Generate final data from preprocessed data.
     *
     * @param  string $bucket Data bucket we want data for
     * @param  string $pivot Pivot value
     * @param  array $filters Map of filters (including pivot if applicable)
     * @return ?array Final data in standardised map format (null: could not generate)
     */
    public function generate_final_data(string $bucket, string $pivot, array $filters) : ?array
    {
        switch ($bucket) {
            case 'relayed_errors':
                $data = [];
                $_data = $this->prepare_preprocessed_data_for_graph($bucket, $pivot, $filters);

                foreach ($_data as $_pivot => $__data) {
                    foreach ($__data as $pivot_interval => $_) {
                        foreach ($_ as $pivot_value => $__) {
                            $pivot_value_nice = $this->make_date_pivot_value_nice($_pivot, $pivot_interval, $pivot_value);
                            if (!isset($data[$pivot_value_nice])) {
                                $data[$pivot_value_nice] = 0;
                            }

                            if ($__ === null) {
                                continue;
                            }

                            foreach ($__ as $resolved => $value) {
                                if ((empty($filters[$bucket . '__resolved'])) && ($resolved == '1')) {
                                    continue;
                                }

                                $data[$pivot_value_nice] += $value;
                            }
                        }
                    }
                }

                return [
                    'type' => null,
                    'data' => $data,
                    'x_axis_label' => do_lang_tempcode('TIME_IN_TIMEZONE', escape_html(make_nice_timezone_name(get_site_timezone()))),
                    'y_axis_label' => do_lang_tempcode('COUNT_NEW'),
                ];

            case 'tracker_issue_activity':
                $data = [];
                $_data = $this->prepare_preprocessed_data_for_graph($bucket, $pivot, $filters);

                foreach ($_data as $_pivot => $__data) {
                    foreach ($__data as $pivot_interval => $_) {
                        foreach ($_ as $pivot_value => $__) {
                            $pivot_value_nice = $this->make_date_pivot_value_nice($_pivot, $pivot_interval, $pivot_value);
                            if (!isset($data[$pivot_value_nice])) {
                                $data[$pivot_value_nice] = 0;
                            }

                            if ($__ === null) {
                                continue;
                            }

                            foreach ($__ as $type => $value) {
                                if (($type != 'all') && (!empty($filters[$bucket . '__status'])) && ($filters[$bucket . '__status'] != $type)) {
                                    continue;
                                }

                                $data[$pivot_value_nice] += $value;
                            }
                        }
                    }
                }

                return [
                    'type' => null,
                    'data' => $data,
                    'x_axis_label' => do_lang_tempcode('TIME_IN_TIMEZONE', escape_html(make_nice_timezone_name(get_site_timezone()))),
                    'y_axis_label' => do_lang_tempcode('COUNT_TOTAL'),
                ];

            case 'tracker_issues':
                $data = [];
                $_data = $this->prepare_preprocessed_data_for_graph($bucket, $pivot, $filters);

                foreach ($_data as $_pivot => $__data) {
                    foreach ($__data as $pivot_interval => $_) {
                        foreach ($_ as $pivot_value => $__) {
                            $pivot_value_nice = $this->make_date_pivot_value_nice($_pivot, $pivot_interval, $pivot_value);
                            if (!isset($data[$pivot_value_nice])) {
                                $data[$pivot_value_nice] = 0;
                            }

                            if ($__ === null) {
                                continue;
                            }

                            foreach ($__ as $category => $value) {
                                if (($category != 'all') && (!empty($filters[$bucket . '__addon'])) && ($filters[$bucket . '__addon'] != $category)) {
                                    continue;
                                }

                                $data[$pivot_value_nice] += $value;
                            }
                        }
                    }
                }

                return [
                    'type' => null,
                    'data' => $data,
                    'x_axis_label' => do_lang_tempcode('TIME_IN_TIMEZONE', escape_html(make_nice_timezone_name(get_site_timezone()))),
                    'y_axis_label' => do_lang_tempcode('COUNT_NEW'),
                ];
        }

        fatal_exit(do_lang_tempcode('INTERNAL_ERROR', escape_html('45c7df6e0f45531285abf135c3143815')));
    }
}
