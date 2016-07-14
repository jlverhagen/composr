<?php /*

 Composr
 Copyright (c) ocProducts, 2004-2016

 See text/EN/licence.txt for full licencing information.


 NOTE TO PROGRAMMERS:
   Do not edit this file. If you need to make changes, save your changed file to the appropriate *_custom folder
   **** If you ignore this advice, then your website upgrades (e.g. for bug fixes) will likely kill your changes ****

*/

/**
 * @license    http://opensource.org/licenses/cpal_1.0 Common Public Attribution License
 * @copyright  ocProducts Ltd
 * @package    core_database_drivers
 */

/**
 * Base class for MySQL database drivers.
 *
 * @package    core_database_drivers
 */
class Database_super_mysql
{
    /**
     * Get queries needed to initialise the DB connection.
     *
     * @return array List of queries
     */
    protected function get_init_queries()
    {
        global $SITE_INFO;
        if (empty($SITE_INFO['database_charset'])) {
            $SITE_INFO['database_charset'] = (get_charset() == 'utf-8') ? 'utf8mb4' : 'latin1';
        }

        $queries = array();

        $queries[] = 'SET WAIT_TIMEOUT=28800';

        $queries[] = 'SET SQL_BIG_SELECTS=1';

        $queries[] = $this->strict_mode_query(true);
        // NB: Can add ,ONLY_FULL_GROUP_BY for testing on what other DBs will do, but can_arbitrary_groupby() would need to be made to return false

        return $queries;
    }

    /**
     * Get a strict mode set query. Takes into account configuration also.
     *
     * @param boolean $setting Whether it is on (may be overridden be configuration)
     * @return string The query
     */
    public function strict_mode_query($setting)
    {
        if ((get_forum_type() == 'cns') && (!$GLOBALS['IN_MINIKERNEL_VERSION'])) {
            $query = 'SET sql_mode=\'STRICT_ALL_TABLES\'';
        } else {
            $query = 'SET sql_mode=\'MYSQL40\'';
        }
        // NB: Can add ,ONLY_FULL_GROUP_BY for testing on what other DBs will do, but can_arbitrary_groupby() would need to be made to return false

        return $query;
    }

    /**
     * Find whether full-text-search is present
     *
     * @param  array $db A DB connection
     * @return boolean Whether it is
     */
    public function db_has_full_text($db)
    {
        if ($this->using_innodb()) {
            return false;
        }

        return true;
    }

    /**
     * Find whether subquery support is present
     *
     * @param  array $db A DB connection
     * @return boolean Whether it is
     */
    public function db_has_subqueries($db)
    {
        return true;
    }

    /**
     * Find whether collate support is present
     *
     * @param  array $db A DB connection
     * @return boolean Whether it is
     */
    public function db_has_collate_settings($db)
    {
        return true;
    }

    /**
     * Find whether full-text-boolean-search is present
     *
     * @return boolean Whether it is
     */
    public function db_has_full_text_boolean()
    {
        return true;
    }

    /**
     * Find whether the database may run GROUP BY unfettered with restrictions on the SELECT'd fields having to be represented in it or aggregate functions
     *
     * @return boolean Whether it can
     */
    public function can_arbitrary_groupby()
    {
        return true;
    }

    /**
     * Find if a database query may run, showing errors if it cannot
     *
     * @param  string $query The complete SQL query
     * @param  array $db_parts A DB connection
     * @param  boolean $get_insert_id Whether to get the autoincrement ID created for an insert query
     * @return boolean Whether it can
     */
    protected function db_query_may_run($query, $db_parts, $get_insert_id)
    {
        if (isset($query[500000])) { // Let's hope we can fail on this, because it's a huge query. We can only allow it if MySQL can.
            $test_result = $this->db_query('SHOW VARIABLES LIKE \'max_allowed_packet\'', $db_parts, null, null, true);

            if (!is_array($test_result)) {
                return false;
            }
            if (intval($test_result[0]['Value']) < intval(strlen($query) * 1.2)) {
                /*@mysql_query('SET session max_allowed_packet=' . strval(intval(strlen($query) * 1.3)), $db); Does not work well, as MySQL server has gone away error will likely just happen instead */

                if ($get_insert_id) {
                    fatal_exit(do_lang_tempcode('QUERY_FAILED_TOO_BIG', escape_html($query), escape_html(integer_format(strlen($query))), escape_html(integer_format(intval($test_result[0]['Value'])))));
                } else {
                    attach_message(do_lang_tempcode('QUERY_FAILED_TOO_BIG', escape_html(substr($query, 0, 300)) . '...', escape_html(integer_format(strlen($query))), escape_html(integer_format(intval($test_result[0]['Value'])))), 'warn', false, true);
                }
                return false;
            }
        }

        return true;
    }

    /**
     * Handle messaging for a failed query.
     *
     * @param  string $query The complete SQL query
     * @param  string $err The error message
     * @param  array $db_parts A DB connection
     */
    protected function handle_failed_query($query, $err, $db_parts)
    {
        if (function_exists('ocp_mark_as_escaped')) {
            ocp_mark_as_escaped($err);
        }
        if ((!running_script('upgrader')) && (!get_mass_import_mode()) && (strpos($err, 'Duplicate entry') === false)) {
            $matches = array();
            if (preg_match('#/(\w+)\' is marked as crashed and should be repaired#U', $err, $matches) !== 0) {
                $this->db_query('REPAIR TABLE ' . $matches[1], $db_parts);
            }

            if (!function_exists('do_lang') || is_null(do_lang('QUERY_FAILED', null, null, null, null, false))) {
                fatal_exit(htmlentities('Query failed: ' . $query . ' : ' . $err));
            }
            fatal_exit(do_lang_tempcode('QUERY_FAILED', escape_html($query), ($err)));
        } else {
            echo htmlentities('Database query failed: ' . $query . ' [') . ($err) . htmlentities(']') . "<br />\n";
        }
    }

    /**
     * Get the default user for making db connections (used by the installer as a default).
     *
     * @return string The default user for db connections
     */
    public function db_default_user()
    {
        return 'root';
    }

    /**
     * Get the default password for making db connections (used by the installer as a default).
     *
     * @return string The default password for db connections
     */
    public function db_default_password()
    {
        return '';
    }

    /**
     * Create a table index.
     *
     * @param  ID_TEXT $table_name The name of the table to create the index on
     * @param  ID_TEXT $index_name The index name (not really important at all)
     * @param  string $_fields Part of the SQL query: a comma-separated list of fields to use on the index
     * @param  array $db The DB connection to make on
     */
    public function db_create_index($table_name, $index_name, $_fields, $db)
    {
        $query = $this->db_create_index_sql($table_name, $index_name, $_fields, $db);
        if (!is_null($query)) {
            $this->db_query($query, $db);
        }
    }

    /**
     * SQL to create a table index.
     *
     * @param  ID_TEXT $table_name The name of the table to create the index on
     * @param  ID_TEXT $index_name The index name (not really important at all)
     * @param  string $_fields Part of the SQL query: a comma-separated list of fields to use on the index
     * @return ?string SQL (null: do nothing)
     */
    public function db_create_index_sql($table_name, $index_name, $_fields)
    {
        if ($index_name[0] == '#') {
            if ($this->using_innodb()) {
                return null;
            }
            $index_name = substr($index_name, 1);
            $type = 'FULLTEXT';
        } else {
            $type = 'INDEX';
        }
        return 'ALTER TABLE ' . $table_name . ' ADD ' . $type . ' ' . $index_name . ' (' . $_fields . ')';
    }

    /**
     * Change the primary key of a table.
     *
     * @param  ID_TEXT $table_name The name of the table to create the index on
     * @param  array $new_key A list of fields to put in the new key
     * @param  array $db The DB connection to make on
     */
    public function db_change_primary_key($table_name, $new_key, $db)
    {
        $this->db_query('ALTER TABLE ' . $table_name . ' DROP PRIMARY KEY, ADD PRIMARY KEY (' . implode(',', $new_key) . ')', $db);
    }

    /**
     * Assemble part of a WHERE clause for doing full-text search
     *
     * @param  string $content Our match string (assumes "?" has been stripped already)
     * @param  boolean $boolean Whether to do a boolean full text search
     * @return string Part of a WHERE clause for doing full-text search
     */
    public function db_full_text_assemble($content, $boolean)
    {
        static $stopwords = null;
        if (is_null($stopwords)) {
            require_code('database_search');
            $stopwords = get_stopwords_list();
        }
        if (isset($stopwords[trim(strtolower($content), '"')])) {
            // This is an imperfect solution for searching for a stop-word
            // It will not cover the case where the stop-word is within the wider text. But we can't handle that case efficiently anyway
            return db_string_equal_to('?', trim($content, '"'));
        }

        if (!$boolean) {
            $content = str_replace('"', '', $content);
            if ((strtoupper($content) == $content) && (!is_numeric($content))) {
                return 'MATCH (?) AGAINST (_latin1\'' . $this->db_escape_string($content) . '\' COLLATE latin1_general_cs)';
            }
            return 'MATCH (?) AGAINST (\'' . $this->db_escape_string($content) . '\')';
        }

        return 'MATCH (?) AGAINST (\'' . $this->db_escape_string($content) . '\' IN BOOLEAN MODE)';
    }

    /**
     * Get the ID of the first row in an auto-increment table (used whenever we need to reference the first).
     *
     * @return integer First ID used
     */
    public function db_get_first_id()
    {
        return 1;
    }

    /**
     * Get a map of Composr field types, to actual database types.
     *
     * @return array The map
     */
    public function db_get_type_remap()
    {
        $type_remap = array(
            'AUTO' => 'integer unsigned auto_increment',
            'AUTO_LINK' => 'integer', // not unsigned because it's useful to have -ve for temporary usage while importing (NB: *_TRANS is signed, so trans fields are not perfectly AUTO_LINK compatible and can have double the positive range -- in the real world it will not matter though)
            'INTEGER' => 'integer',
            'UINTEGER' => 'integer unsigned',
            'SHORT_INTEGER' => 'tinyint',
            'REAL' => 'real',
            'BINARY' => 'tinyint(1)',
            'MEMBER' => 'integer', // not unsigned because it's useful to have -ve for temporary usage while importing
            'GROUP' => 'integer', // not unsigned because it's useful to have -ve for temporary usage while importing
            'TIME' => 'integer unsigned',
            'LONG_TRANS' => 'integer unsigned',
            'SHORT_TRANS' => 'integer unsigned',
            'LONG_TRANS__COMCODE' => 'integer',
            'SHORT_TRANS__COMCODE' => 'integer',
            'SHORT_TEXT' => 'varchar(255)',
            'LONG_TEXT' => 'longtext',
            'ID_TEXT' => 'varchar(80)',
            'MINIID_TEXT' => 'varchar(40)',
            'IP' => 'varchar(40)', // 15 for ip4, but we now support ip6
            'LANGUAGE_NAME' => 'varchar(5)',
            'URLPATH' => 'varchar(255) BINARY',
        );
        return $type_remap;
    }

    /**
     * Whether to use InnoDB for MySQL. Change this function by hand - official only MyISAM supported
     *
     * @return boolean Answer
     */
    public function using_innodb()
    {
        return false;
    }

    /**
     * Create a new table.
     *
     * @param  ID_TEXT $table_name The table name
     * @param  array $fields A map of field names to Composr field types (with *#? encodings)
     * @param  array $db The DB connection to make on
     * @param  ID_TEXT $raw_table_name The table name with no table prefix
     * @param  boolean $save_bytes Whether to use lower-byte table storage, with tradeoffs of not being able to support all unicode characters; use this if key length is an issue
     */
    public function db_create_table($table_name, $fields, $db, $raw_table_name, $save_bytes = false)
    {
        $query = $this->db_create_table_sql($table_name, $fields, $raw_table_name, $save_bytes);
        $this->db_query($query, $db, null, null);
    }

    /**
     * SQL to create a new table.
     *
     * @param  ID_TEXT $table_name The table name
     * @param  array $fields A map of field names to Composr field types (with *#? encodings)
     * @param  ID_TEXT $raw_table_name The table name with no table prefix
     * @param  boolean $save_bytes Whether to use lower-byte table storage, with tradeoffs of not being able to support all unicode characters; use this if key length is an issue
     * @return string SQL
     */
    public function db_create_table_sql($table_name, $fields, $raw_table_name, $save_bytes = false)
    {
        $type_remap = $this->db_get_type_remap();

        $_fields = '';
        $keys = '';
        foreach ($fields as $name => $type) {
            if ($type[0] == '*') { // Is a key
                $type = substr($type, 1);
                if ($keys !== '') {
                    $keys .= ', ';
                }
                $keys .= $name;
            }

            if ($type[0] == '?') { // Is perhaps null
                $type = substr($type, 1);
                $perhaps_null = 'NULL';
            } else {
                $perhaps_null = 'NOT NULL';
            }

            $type = isset($type_remap[$type]) ? $type_remap[$type] : $type;

            $_fields .= '    ' . $name . ' ' . $type;
            /*if (substr($name, -13) == '__text_parsed') {    BLOB/TEXT column 'description__text_parsed' can't have a default value
                $_fields .= ' DEFAULT \'\'';
            } else*/
            if (substr($name, -13) == '__source_user') {
                $_fields .= ' DEFAULT ' . strval(db_get_first_id());
            }
            $_fields .= ' ' . $perhaps_null . ',' . "\n";
        }

        $innodb = $this->using_innodb();
        $table_type = ($innodb ? 'INNODB' : 'MyISAM');
        $type_key = 'engine';
        if ($raw_table_name == 'sessions') {
            $table_type = 'HEAP';
        }

        $query = 'CREATE TABLE ' . $table_name . ' (' . "\n" . $_fields . '
            PRIMARY KEY (' . $keys . ')
        )';

        global $SITE_INFO;
        if (empty($SITE_INFO['database_charset'])) {
            $SITE_INFO['database_charset'] = (get_charset() == 'utf-8') ? 'utf8mb4' : 'latin1';
        }
        $charset = $SITE_INFO['database_charset'];
        if ($charset == 'utf8mb4' && $save_bytes) {
            $charset = 'utf8';
        }

        $query .= ' CHARACTER SET=' . preg_replace('#\_.*$#', '', $charset);

        $query .= ' ' . $type_key . '=' . $table_type;

        return $query;
    }

    /**
     * Encode an SQL statement fragment for a conditional to see if two strings are equal.
     *
     * @param  ID_TEXT $attribute The attribute
     * @param  string $compare The comparison
     * @return string The SQL
     */
    public function db_string_equal_to($attribute, $compare)
    {
        return $attribute . "='" . db_escape_string($compare) . "'";
    }

    /**
     * Encode an SQL statement fragment for a conditional to see if two strings are not equal.
     *
     * @param  ID_TEXT $attribute The attribute
     * @param  string $compare The comparison
     * @return string The SQL
     */
    public function db_string_not_equal_to($attribute, $compare)
    {
        return $attribute . "<>'" . db_escape_string($compare) . "'";
    }

    /**
     * Find whether expression ordering support is present
     *
     * @param  array $db A DB connection
     * @return boolean Whether it is
     */
    public function db_has_expression_ordering($db)
    {
        return true;
    }

    /**
     * This function is internal to the database system, allowing SQL statements to be build up appropriately. Some databases require IS NULL to be used to check for blank strings.
     *
     * @return boolean Whether a blank string IS NULL
     */
    public function db_empty_is_null()
    {
        return false;
    }

    /**
     * Delete a table.
     *
     * @param  ID_TEXT $table The table name
     * @param  array $db The DB connection to delete on
     */
    public function db_drop_table_if_exists($table, $db)
    {
        $this->db_query('DROP TABLE IF EXISTS ' . $table, $db);
    }

    /**
     * Determine whether the database is a flat file database, and thus not have a meaningful connect username and password.
     *
     * @return boolean Whether the database is a flat file database
     */
    public function db_is_flat_file_simple()
    {
        return false;
    }

    /**
     * Encode a LIKE string comparision fragement for the database system. The pattern is a mixture of characters and ? and % wildcard symbols.
     *
     * @param  string $pattern The pattern
     * @return string The encoded pattern
     */
    public function db_encode_like($pattern)
    {
        return $this->db_escape_string($pattern);
    }

    /**
     * Close the database connections. We don't really need to close them (will close at exit), just disassociate so we can refresh them.
     */
    public function db_close_connections()
    {
        $this->cache_db = array();
        $this->last_select_db = null;
    }
}
