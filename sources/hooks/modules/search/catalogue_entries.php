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
 * @package    catalogues
 */

/**
 * Hook class.
 */
class Hook_search_catalogue_entries extends FieldsSearchHook
{
    /**
     * Find details for this search hook.
     *
     * @param  boolean $check_permissions Whether to check permissions.
     * @return ?array Map of search hook details (null: hook is disabled).
     */
    public function info($check_permissions = true)
    {
        if (!module_installed('catalogues')) {
            return null;
        }

        if ($check_permissions) {
            if (!has_actual_page_access(get_member(), 'catalogues')) {
                return null;
            }
        }

        if ($GLOBALS['SITE_DB']->query_select_value('catalogue_entries', 'COUNT(*)') == 0) {
            return null;
        }

        require_lang('catalogues');
        require_code('catalogues');

        $info = array();
        $info['lang'] = do_lang_tempcode('CATALOGUE_ENTRIES');
        $info['default'] = (get_option('search_catalogue_entries') == '1');
        $info['category'] = 'cc_id';
        $info['integer_category'] = true;

        $extra_sort_fields = array();
        $catalogue_name = get_param_string('catalogue_name', null);
        if ($catalogue_name !== null) {
            $extra_sort_fields = $this->_get_extra_sort_fields($catalogue_name);
        }
        $info['extra_sort_fields'] = $extra_sort_fields;

        $info['permissions'] = array(
            array(
                'type' => 'zone',
                'zone_name' => get_module_zone('catalogues'),
            ),
            array(
                'type' => 'page',
                'zone_name' => get_module_zone('catalogues'),
                'page_name' => 'catalogues',
            ),
        );

        return $info;
    }

    /**
     * Get details for an ajax-tree-list of entries for the content covered by this search hook.
     *
     * @return ?mixed Either Tempcode of a full screen to show, or a pair: the hook, and the options (null: no tree)
     */
    public function ajax_tree()
    {
        $catalogue_name = get_param_string('catalogue_name', '');
        if ($catalogue_name == '') {
            if (get_param_string('content') != '') {
                return null; // Mid-searc
            }

            $tree = create_selection_list_catalogues(null, true);
            if ($tree->is_empty()) {
                inform_exit(do_lang_tempcode('NO_ENTRIES', 'catalogue'));
            }

            require_code('form_templates');
            $fields = form_input_list(do_lang_tempcode('NAME'), '', 'catalogue_name', $tree, null, true);
            $post_url = get_self_url(false, false, array(), false, true);
            $submit_name = do_lang_tempcode('PROCEED');
            $hidden = build_keep_post_fields();

            $title = get_screen_title('SEARCH');
            return do_template('FORM_SCREEN', array(
                '_GUID' => 'a2812ac8056903811f444682d45ee448',
                'TARGET' => '_self',
                'GET' => true,
                'SKIP_WEBSTANDARDS' => true,
                'HIDDEN' => $hidden,
                'TITLE' => $title,
                'TEXT' => '',
                'URL' => $post_url,
                'FIELDS' => $fields,
                'SUBMIT_ICON' => 'buttons__search',
                'SUBMIT_NAME' => $submit_name,
            ));
        }

        return array('choose_catalogue_category', array('catalogue_name' => $catalogue_name));
    }

    /**
     * Get a list of extra fields to ask for.
     *
     * @return ?array A list of maps specifying extra fields (null: no tree)
     */
    public function get_fields()
    {
        $catalogue_name = get_param_string('catalogue_name', '');
        if ($catalogue_name == '') {
            return array();
        }
        return $this->_get_fields($catalogue_name);
    }

    /**
     * Run function for search results.
     *
     * @param  string $content Search string
     * @param  boolean $only_search_meta Whether to only do a META (tags) search
     * @param  ID_TEXT $direction Order direction
     * @param  integer $max Start position in total results
     * @param  integer $start Maximum results to return in total
     * @param  boolean $only_titles Whether only to search titles (as opposed to both titles and content)
     * @param  string $content_where Where clause that selects the content according to the main search string (SQL query fragment) (blank: full-text search)
     * @param  SHORT_TEXT $author Username/Author to match for
     * @param  ?MEMBER $author_id Member-ID to match for (null: unknown)
     * @param  mixed $cutoff Cutoff date (TIME or a pair representing the range)
     * @param  string $sort The sort type (gets remapped to a field in this function)
     * @set    title add_date
     * @param  integer $limit_to Limit to this number of results
     * @param  string $boolean_operator What kind of boolean search to do
     * @set    or and
     * @param  string $where_clause Where constraints known by the main search code (SQL query fragment)
     * @param  string $search_under Comma-separated list of categories to search under
     * @param  boolean $boolean_search Whether it is a boolean search
     * @return array List of maps (template, orderer)
     */
    public function run($content, $only_search_meta, $direction, $max, $start, $only_titles, $content_where, $author, $author_id, $cutoff, $sort, $limit_to, $boolean_operator, $where_clause, $search_under, $boolean_search)
    {
        if (!module_installed('catalogues')) {
            return array();
        }

        $remapped_orderer = '';
        switch ($sort) {
            case 'average_rating':
            case 'compound_rating':
                $remapped_orderer = $sort . ':catalogues:id';
                break;

            case 'title':
                $remapped_orderer = 'b_cv_value'; // short table
                break;

            case 'add_date':
                $remapped_orderer = 'ce_add_date';
                break;

            case 'relevance':
                break;

            default:
                if (preg_match('#^f\d+_actual_value$#', $sort) != 0) {
                    $remapped_orderer = str_replace('_actual_value', '_cv_value', $sort);
                }
                break;
        }

        require_code('catalogues');
        require_lang('catalogues');

        // Calculate our where clause (search)
        $sq = build_search_submitter_clauses('ce_submitter', $author_id, $author);
        if ($sq === null) {
            return array();
        } else {
            $where_clause .= $sq;
        }
        $this->_handle_date_check($cutoff, 'r.ce_add_date', $where_clause);
        if (!$GLOBALS['FORUM_DRIVER']->is_super_admin(get_member())) {
            $where_clause .= ' AND ';
            $where_clause .= 'z.category_name IS NOT NULL';
            $where_clause .= ' AND ';
            $where_clause .= 'p.category_name IS NOT NULL';
        }
        if ((!has_privilege(get_member(), 'see_unvalidated')) && (addon_installed('unvalidated'))) {
            $where_clause .= ' AND ';
            $where_clause .= 'ce_validated=1';
        }

        $g_or = _get_where_clause_groups(get_member());

        $privacy_join = '';
        if (addon_installed('content_privacy')) {
            require_code('content_privacy');
            list($privacy_join, $privacy_where) = get_privacy_where_clause('catalogue_entry', 'r');
            $where_clause .= $privacy_where;
        }

        // Calculate and perform query
        $catalogue_name = get_param_string('catalogue_name', '');
        if ($catalogue_name != '') {
            $table = 'catalogue_entries r';
            list($sup_table, $where_clause, $trans_fields, $nontrans_fields, $title_field) = $this->_get_search_parameterisation_advanced($catalogue_name);
            $table .= $sup_table;
            $table .= $privacy_join;

            $extra_select = '';

            if ($title_field === null) {
                return array(); // No fields in catalogue -- very odd
            }
            if ($g_or == '') {
                $rows = get_search_rows('catalogue_entry', 'id', $content, $boolean_search, $boolean_operator, $only_search_meta, $direction, $max, $start, $only_titles, $table, $trans_fields, $where_clause, $content_where, $remapped_orderer, 'r.*,r.id AS id,r.cc_id AS r_cc_id,' . $title_field . ' AS b_cv_value' . $extra_select, $nontrans_fields);
            } else {
                $rows = get_search_rows('catalogue_entry', 'id', $content, $boolean_search, $boolean_operator, $only_search_meta, $direction, $max, $start, $only_titles, $table . ' LEFT JOIN ' . $GLOBALS['SITE_DB']->get_table_prefix() . 'group_category_access z ON (' . db_string_equal_to('z.module_the_name', 'catalogues_category') . ' AND z.category_name=r.cc_id AND ' . str_replace('group_id', 'z.group_id', $g_or) . ') LEFT JOIN ' . $GLOBALS['SITE_DB']->get_table_prefix() . 'group_category_access p ON (' . db_string_equal_to('p.module_the_name', 'catalogues_catalogue') . ' AND p.category_name=r.c_name AND ' . str_replace('group_id', 'p.group_id', $g_or) . ')', $trans_fields, $where_clause, $content_where, $remapped_orderer, 'r.*,r.id AS id,r.cc_id AS r_cc_id,' . $title_field . ' AS b_cv_value' . $extra_select, $nontrans_fields);
            }
        } else {
            if (multi_lang_content() && $GLOBALS['SITE_DB']->query_select_value('translate', 'COUNT(*)') > 10000) { // Big sites can't do indescriminate catalogue translatable searches for performance reasons
                $trans_fields = array();
                $join = ' JOIN ' . get_table_prefix() . 'catalogue_efv_short c ON (r.id=c.ce_id AND f.id=c.cf_id)';
                $extra_select = '';
                $non_trans_fields = array('c.cv_value');
            } else {
                $join = ' LEFT JOIN ' . get_table_prefix() . 'catalogue_efv_short_trans a ON (r.id=a.ce_id AND f.id=a.cf_id) LEFT JOIN ' . get_table_prefix() . 'catalogue_efv_long_trans b ON (r.id=b.ce_id AND f.id=b.cf_id) LEFT JOIN ' . get_table_prefix() . 'catalogue_efv_long d ON (r.id=d.ce_id AND f.id=d.cf_id) LEFT JOIN ' . get_table_prefix() . 'catalogue_efv_short c ON (r.id=c.ce_id AND f.id=c.cf_id)';
                //' LEFT JOIN ' . get_table_prefix() . 'catalogue_efv_float g ON (r.id=g.ce_id AND f.id=g.cf_id) LEFT JOIN ' . get_table_prefix() . 'catalogue_efv_integer h ON (r.id=h.ce_id AND f.id=h.cf_id)';       No search is done on these unless it's an advanced search
                $trans_fields = array('a.cv_value' => 'LONG_TRANS__COMCODE', 'b.cv_value' => 'LONG_TRANS__COMCODE');
                $extra_select = ',b.cv_value AS b_cv_value';
                $non_trans_fields = array('c.cv_value', 'd.cv_value'/*, 'g.cv_value', 'h.cv_value'*/);
            }

            $where_clause .= ' AND ';
            $where_clause .= 'r.c_name NOT LIKE \'' . db_encode_like('\_%') . '\''; // Don't want results drawn from the hidden custom-field catalogues

            $join .= $privacy_join;

            if ($g_or == '') {
                $rows = get_search_rows('catalogue_entry', 'id', $content, $boolean_search, $boolean_operator, $only_search_meta, $direction, $max, $start, $only_titles, 'catalogue_fields f LEFT JOIN ' . get_table_prefix() . 'catalogue_entries r ON (r.c_name=f.c_name)' . $join, $trans_fields, $where_clause, $content_where, $remapped_orderer, 'r.*,r.id AS id,r.cc_id AS r_cc_id' . $extra_select, $non_trans_fields);
            } else {
                $rows = get_search_rows('catalogue_entry', 'id', $content, $boolean_search, $boolean_operator, $only_search_meta, $direction, $max, $start, $only_titles, 'catalogue_fields f LEFT JOIN ' . get_table_prefix() . 'catalogue_entries r ON (r.c_name=f.c_name)' . $join . ((get_value('disable_cat_cat_perms') === '1') ? '' : (' LEFT JOIN ' . $GLOBALS['SITE_DB']->get_table_prefix() . 'group_category_access z ON (' . db_string_equal_to('z.module_the_name', 'catalogues_category') . ' AND z.category_name=r.cc_id AND ' . str_replace('group_id', 'z.group_id', $g_or) . ')')) . ' LEFT JOIN ' . $GLOBALS['SITE_DB']->get_table_prefix() . 'group_category_access p ON (' . db_string_equal_to('p.module_the_name', 'catalogues_catalogue') . ' AND p.category_name=r.c_name AND ' . str_replace('group_id', 'p.group_id', $g_or) . ')', $trans_fields, $where_clause, $content_where, $remapped_orderer, 'r.*,r.id AS id,r.cc_id AS r_cc_id' . $extra_select, $non_trans_fields);
            }
        }

        $out = array();
        if (count($rows) == 0) {
            return array();
        }

        global $SEARCH_CATALOGUE_ENTRIES_CATALOGUES_CACHE;
        $query = 'SELECT c.* FROM ' . get_table_prefix() . 'catalogues c';
        if ($GLOBALS['DB_STATIC_OBJECT']->can_arbitrary_groupby()) {
            $query .= ' JOIN ' . get_table_prefix() . 'catalogue_entries e ON e.c_name=c.c_name GROUP BY c.c_name';
        }
        $_catalogues = $GLOBALS['SITE_DB']->query($query);
        foreach ($_catalogues as $catalogue) {
            $SEARCH_CATALOGUE_ENTRIES_CATALOGUES_CACHE[$catalogue['c_name']] = $catalogue;
        }
        foreach ($rows as $i => $row) {
            $out[$i]['data'] = $row;
            unset($rows[$i]);

            if (($remapped_orderer != '') && (array_key_exists($remapped_orderer, $row))) {
                $out[$i]['orderer'] = $row[$remapped_orderer];
            } elseif (strpos($remapped_orderer, '_rating:') !== false) {
                $out[$i]['orderer'] = $row[$remapped_orderer];
            }
        }

        return $out;
    }

    /**
     * Run function for rendering a search result.
     *
     * @param  array $row The data row stored when we retrieved the result
     * @return ?Tempcode The output (null: compound output)
     */
    public function render($row)
    {
        require_code('catalogues');
        return render_catalogue_entry_box($row, '_SEARCH');
    }
}
