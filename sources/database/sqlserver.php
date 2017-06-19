<?php /*

 Composr
 Copyright (c) ocProducts, 2004-2016

 See text/EN/licence.txt for full licencing information.


 NOTE TO PROGRAMMERS:
   Do not edit this file. If you need to make changes, save your changed file to the appropriate *_custom folder
   **** If you ignore this advice, then your website upgrades (e.g. for bug fixes) will likely kill your changes ****

*/

/*EXTRA FUNCTIONS: (mssql|sqlsrv)\_.+*/

/**
 * @license    http://opensource.org/licenses/cpal_1.0 Common Public Attribution License
 * @copyright  ocProducts Ltd
 * @package    core_database_drivers
 */

/*
Use the Enterprise Manager to get things set up.
You need to go into your server properties and turn the security to "SQL Server and Windows"
*/

/**
 * Standard code module initialisation function.
 *
 * @ignore
 */
function init__database__sqlserver()
{
    safe_ini_set('mssql.textlimit', '300000');
    safe_ini_set('mssql.textsize', '300000');
}

/**
 * Database Driver.
 *
 * @package    core_database_drivers
 */
class Database_Static_sqlserver extends DatabaseDriver
{
    public $cache_db = array();

    /**
     * Get the default user for making db connections (used by the installer as a default).
     *
     * @return string The default user for db connections
     */
    public function default_user()
    {
        return 'sa';
    }

    /**
     * Get the default password for making db connections (used by the installer as a default).
     *
     * @return string The default password for db connections
     */
    public function default_password()
    {
        return '';
    }

    /**
     * Get a database connection. This function shouldn't be used by you, as a connection to the database is established automatically.
     *
     * @param  boolean $persistent Whether to create a persistent connection
     * @param  string $db_name The database name
     * @param  string $db_host The database host (the server)
     * @param  string $db_user The database connection username
     * @param  string $db_password The database connection password
     * @param  boolean $fail_ok Whether to on error echo an error and return with a null, rather than giving a critical error
     * @return ?array A database connection (null: failed)
     */
    public function get_connection($persistent, $db_name, $db_host, $db_user, $db_password, $fail_ok = false)
    {
        // Potential caching
        if (isset($this->cache_db[$db_name][$db_host])) {
            return $this->cache_db[$db_name][$db_host];
        }

        if ((!function_exists('sqlsrv_connect')) && (!function_exists('mssql_pconnect'))) {
            $error = 'The sqlserver PHP extension not installed (anymore?). You need to contact the system administrator of this server.';
            if ($fail_ok) {
                echo ((running_script('install')) && (get_param_string('type', '') == 'ajax_db_details')) ? strip_html($error) : $error;
                return null;
            }
            critical_error('PASSON', $error);
        }

        if (function_exists('sqlsrv_connect')) {
            if ($db_host == '127.0.0.1' || $db_host == 'localhost') {
                $db_host = '(local)';
            }
            $connection = @sqlsrv_connect($db_host, ($db_user == '') ? array('Database' => $db_name) : array('UID' => $db_user, 'PWD' => $db_password, 'Database' => $db_name));
        } else {
            $connection = $persistent ? @mssql_pconnect($db_host, $db_user, $db_password) : @mssql_connect($db_host, $db_user, $db_password);
        }
        if ($connection === false) {
            $error = 'Could not connect to database-server (' . @strval($php_errormsg) . ')';
            if ($fail_ok) {
                echo ((running_script('install')) && (get_param_string('type', '') == 'ajax_db_details')) ? strip_html($error) : $error;
                return null;
            }
            critical_error('PASSON', $error); //warn_exit(do_lang_tempcode('CONNECT_DB_ERROR'));
        }
        if (!function_exists('sqlsrv_connect')) {
            if (!mssql_select_db($db_name, $connection)) {
                $error = 'Could not connect to database (' . mssql_get_last_message() . ')';
                if ($fail_ok) {
                    echo ((running_script('install')) && (get_param_string('type', '') == 'ajax_db_details')) ? strip_html($error) : $error;
                    return null;
                }
                critical_error('PASSON', $error); //warn_exit(do_lang_tempcode('CONNECT_ERROR'));
            }
        }

        $this->cache_db[$db_name][$db_host] = $connection;
        return $connection;
    }

    /**
     * This function is a very basic query executor. It shouldn't usually be used by you, as there are abstracted versions available.
     *
     * @param  string $query The complete SQL query
     * @param  mixed $connection The DB connection
     * @param  ?integer $max The maximum number of rows to affect (null: no limit)
     * @param  integer $start The start row to affect
     * @param  boolean $fail_ok Whether to output an error on failure
     * @param  boolean $get_insert_id Whether to get the autoincrement ID created for an insert query
     * @return ?mixed The results (null: no results), or the insert ID
     */
    public function query($query, $connection, $max = null, $start = 0, $fail_ok = false, $get_insert_id = false)
    {
        if ($max !== null) {
            $max += $start;

            if ((strtoupper(substr(ltrim($query), 0, 7)) == 'SELECT ') || (strtoupper(substr(ltrim($query), 0, 8)) == '(SELECT ')) { // Unfortunately we can't apply to DELETE FROM and update :(. But its not too important, LIMIT'ing them was unnecessarily anyway
                $query = 'SELECT TOP ' . strval($max) . substr($query, 6);
            }
        }

        push_suppress_error_death(true);
        if (function_exists('sqlsrv_query')) {
            $results = sqlsrv_query($connection, $query, array(), array('Scrollable' => 'static'));
        } else {
            $results = mssql_query($query, $connection);
        }
        pop_suppress_error_death();
        if (($results === false) && (strtoupper(substr(ltrim($query), 0, 12)) == 'INSERT INTO ') && (strpos($query, '(id, ') !== false)) {
            $pos = strpos($query, '(');
            $table_name = substr($query, 12, $pos - 13);
            if (function_exists('sqlsrv_query')) {
                @sqlsrv_query($connection, 'SET IDENTITY_INSERT ' . $table_name . ' ON');
            } else {
                @mssql_query('SET IDENTITY_INSERT ' . $table_name . ' ON', $connection);
            }
        }
        if ($start != 0) {
            if (function_exists('sqlsrv_fetch_array')) {
                sqlsrv_fetch($results, SQLSRV_SCROLL_ABSOLUTE, $start - 1);
            } else {
                @mssql_data_seek($results, $start);
            }
        }
        if ((($results === false) || ((strtoupper(substr(ltrim($query), 0, 7)) == 'SELECT ') || (strtoupper(substr(ltrim($query), 0, 8)) == '(SELECT ')) && ($results === true)) && (!$fail_ok)) {
            if (function_exists('sqlsrv_errors')) {
                $err = serialize(sqlsrv_errors());
            } else {
                $_error_msg = array_pop($GLOBALS['ATTACHED_MESSAGES_RAW']);
                if ($_error_msg === null) {
                    $error_msg = make_string_tempcode('?');
                } else {
                    $error_msg = $_error_msg[0];
                }
                $err = mssql_get_last_message() . '/' . $error_msg->evaluate();
                if (function_exists('ocp_mark_as_escaped')) {
                    ocp_mark_as_escaped($err);
                }
            }
            if ((!running_script('upgrader')) && ((!get_mass_import_mode()) || (get_param_integer('keep_fatalistic', 0) == 1))) {
                if ((!function_exists('do_lang')) || (do_lang('QUERY_FAILED', null, null, null, null, false) === null)) {
                    $this->failed_query_exit(htmlentities('Query failed: ' . $query . ' : ' . $err));
                }

                $this->failed_query_exit(do_lang_tempcode('QUERY_FAILED', escape_html($query), ($err)));
            } else {
                $this->failed_query_echo(htmlentities('Database query failed: ' . $query . ' [') . ($err) . htmlentities(']'));
                return null;
            }
        }

        if (((strtoupper(substr(ltrim($query), 0, 7)) == 'SELECT ') || (strtoupper(substr(ltrim($query), 0, 8)) == '(SELECT ')) && ($results !== false) && ($results !== true)) {
            return $this->get_query_rows($results);
        }

        if ($get_insert_id) {
            if (strtoupper(substr(ltrim($query), 0, 7)) == 'UPDATE ') {
                return null;
            }

            $pos = strpos($query, '(');
            $table_name = substr($query, 12, $pos - 13);
            if (function_exists('sqlsrv_query')) {
                $res2 = sqlsrv_query($connection, 'SELECT MAX(IDENTITYCOL) AS v FROM ' . $table_name);
                $ar2 = sqlsrv_fetch_array($res2, SQLSRV_FETCH_ASSOC);
            } else {
                $res2 = mssql_query('SELECT MAX(IDENTITYCOL) AS v FROM ' . $table_name, $connection);
                $ar2 = mssql_fetch_array($res2);
            }
            return $ar2['v'];
        }

        return null;
    }

    /**
     * Get the rows returned from a SELECT query.
     *
     * @param  resource $results The query result pointer
     * @param  integer $start Whether to start reading from
     * @return array A list of row maps
     */
    public function get_query_rows($results, $start = 0)
    {
        $out = array();

        if (!function_exists('sqlsrv_num_fields')) {
            $num_fields = mssql_num_fields($results);
            $types = array();
            $names = array();
            for ($x = 1; $x <= $num_fields; $x++) {
                $types[$x - 1] = mssql_field_type($results, $x - 1);
                $names[$x - 1] = strtolower(mssql_field_name($results, $x - 1));
            }

            $i = 0;
            while (($row = mssql_fetch_row($results)) !== false) {
                $j = 0;
                $newrow = array();
                foreach ($row as $v) {
                    $type = strtoupper($types[$j]);
                    $name = $names[$j];

                    if (($type == 'SMALLINT') || ($type == 'INT') || ($type == 'INTEGER') || ($type == 'UINTEGER') || ($type == 'BYTE') || ($type == 'COUNTER')) {
                        if ($v !== null) {
                            $newrow[$name] = intval($v);
                        } else {
                            $newrow[$name] = null;
                        }
                    } elseif (substr($type, 0, 5) == 'FLOAT') {
                        $newrow[$name] = floatval($v);
                    } else {
                        if ($v == ' ') {
                            $v = '';
                        }
                        $newrow[$name] = $v;
                    }

                    $j++;
                }

                $out[] = $newrow;

                $i++;
            }
        } else {
            if (function_exists('sqlsrv_fetch_array')) {
                while (($row = sqlsrv_fetch_array($results, SQLSRV_FETCH_ASSOC)) !== null) {
                    $out[] = $row;
                }
            } else {
                while (($row = mssql_fetch_row($results)) !== false) {
                    $out[] = $row;
                }
            }
        }

        if (function_exists('sqlsrv_free_stmt')) {
            sqlsrv_free_stmt($results);
        } else {
            mssql_free_result($results);
        }
        return $out;
    }

    /**
     * Get a map of Composr field types, to actual database types.
     *
     * @param  boolean $for_alter Whether this is for adding a table field
     * @return array The map
     */
    public function get_type_remap($for_alter = false)
    {
        $type_remap = array(
            'AUTO' => 'integer identity',
            'AUTO_LINK' => 'integer',
            'INTEGER' => 'integer',
            'UINTEGER' => 'bigint',
            'SHORT_INTEGER' => 'smallint',
            'REAL' => 'real',
            'BINARY' => 'smallint',
            'MEMBER' => 'integer',
            'GROUP' => 'integer',
            'TIME' => 'bigint',
            'LONG_TRANS' => 'bigint',
            'SHORT_TRANS' => 'bigint',
            'LONG_TRANS__COMCODE' => 'integer',
            'SHORT_TRANS__COMCODE' => 'integer',
            'SHORT_TEXT' => 'varchar(255)',
            'LONG_TEXT' => 'text',
            'ID_TEXT' => 'varchar(80)',
            'MINIID_TEXT' => 'varchar(40)',
            'IP' => 'varchar(40)',
            'LANGUAGE_NAME' => 'varchar(5)',
            'URLPATH' => 'varchar(255)',
        );
        return $type_remap;
    }

    /**
     * Get SQL for creating a new table.
     *
     * @param  ID_TEXT $table_name The table name
     * @param  array $fields A map of field names to Composr field types (with *#? encodings)
     * @param  mixed $connection The DB connection to make on
     * @param  ID_TEXT $raw_table_name The table name with no table prefix
     * @param  boolean $save_bytes Whether to use lower-byte table storage, with tradeoffs of not being able to support all unicode characters; use this if key length is an issue
     * @return array List of SQL queries to run
     */
    public function create_table($table_name, $fields, $connection, $raw_table_name, $save_bytes = false)
    {
        $type_remap = $this->get_type_remap();

        $_fields = '';
        $keys = '';
        foreach ($fields as $name => $type) {
            if ($type[0] == '*') { // Is a key
                $type = substr($type, 1);
                if ($keys != '') {
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
            if (substr($name, -13) == '__text_parsed') {
                $_fields .= ' DEFAULT \'\'';
            } elseif (substr($name, -13) == '__source_user') {
                $_fields .= ' DEFAULT ' . strval(db_get_first_id());
            }
            $_fields .= ' ' . $perhaps_null . ',' . "\n";
        }

        $query = 'CREATE TABLE ' . $table_name . ' (' . "\n" . $_fields . '    PRIMARY KEY (' . $keys . ")\n)";
        return array($query);
    }

    /**
     * Find whether table truncation support is present
     *
     * @return boolean Whether it is
     */
    public function supports_truncate_table()
    {
        return true;
    }

    /**
     * Get SQL for creating a table index.
     *
     * @param  ID_TEXT $table_name The name of the table to create the index on
     * @param  ID_TEXT $index_name The index name (not really important at all)
     * @param  string $_fields Part of the SQL query: a comma-separated list of fields to use on the index
     * @param  mixed $connection The DB connection to make on
     * @param  ID_TEXT $raw_table_name The table name with no table prefix
     * @param  string $unique_key_fields The name of the unique key field for the table
     * @param  string $table_prefix The table prefix
     * @return array List of SQL queries to run
     */
    public function create_index($table_name, $index_name, $_fields, $connection, $raw_table_name, $unique_key_fields, $table_prefix)
    {
        if ($index_name[0] == '#') {
            $ret = array();
            if ($this->has_full_text()) {
                $index_name = substr($index_name, 1);

                $unique_index_name = $index_name . '__' . $table_name;

                $ret[] = 'CREATE UNIQUE INDEX ' . $unique_index_name . ' ON ' . $table_name . '(' . $unique_key_fields . ')';

                $ret[] = 'CREATE FULLTEXT CATALOG ft AS DEFAULT';

                $ret[] = 'CREATE FULLTEXT INDEX ON ' . $table_name . '(' . $_fields . ') KEY INDEX ' . $unique_index_name;
            }
            return $ret;
        }

        $_fields = preg_replace('#\(\d+\)#', '', $_fields);

        $fields = explode(',', $_fields);
        foreach ($fields as $field) {
            $sql = 'SELECT m_type FROM ' . $table_prefix . 'db_meta WHERE m_table=\'' . $this->escape_string($raw_table_name) . '\' AND m_name=\'' . $this->escape_string($field) . '\'';
            $values = $this->query($sql, $connection, null, null, true);
            if (!isset($values[0])) {
                continue; // No result found
            }
            $first = $values[0];
            $field_type = current($first); // Result found

            if (strpos($field_type, 'LONG') !== false) {
                // We can't support this in SQL Server https://blogs.msdn.microsoft.com/bartd/2011/01/06/living-with-sqls-900-byte-index-key-length-limit/.
                // We assume shorter numbers than 250 are only being used on short columns anyway, which will index perfectly fine without any constraint.
                return array();
            }
        }

        return array('CREATE INDEX ' . $index_name . '__' . $table_name . ' ON ' . $table_name . '(' . $_fields . ')');
    }

    /**
     * Change the primary key of a table.
     *
     * @param  ID_TEXT $table_name The name of the table to create the index on
     * @param  array $new_key A list of fields to put in the new key
     * @param  mixed $connection The DB connection to make on
     */
    public function change_primary_key($table_name, $new_key, $connection)
    {
        $this->query('ALTER TABLE ' . $table_name . ' DROP PRIMARY KEY', $connection);
        $this->query('ALTER TABLE ' . $table_name . ' ADD PRIMARY KEY (' . implode(',', $new_key) . ')', $connection);
    }

    /**
     * Get the number of rows in a table, with approximation support for performance (if necessary on the particular database backend).
     *
     * @param  string $table The table name
     * @param  mixed $connection The DB connection
     * @return ?integer The count (null: do it normally)
     */
    public function get_table_count_approx($table, $connection)
    {
        $sql = 'SELECT SUM(p.rows) FROM sys.partitions AS p
            INNER JOIN sys.tables AS t
            ON p.[object_id] = t.[object_id]
            INNER JOIN sys.schemas AS s
            ON s.[schema_id] = t.[schema_id]
            WHERE t.name = N\'' . $this->escape_string($table) . '\'
            AND s.name = N\'dbo\'
            AND p.index_id IN (0,1)';
        $values = $this->query($sql, $connection, null, null, true);
        if (!isset($values[0])) {
            return null; // No result found
        }
        $first = $values[0];
        $v = current($first); // Result found
        return $v;
    }

    /**
     * Set a time limit on future queries.
     * Not all database drivers support this.
     *
     * @param  integer $seconds The time limit in seconds
     * @param  mixed $connection The DB connection
     */
    public function set_query_time_limit($seconds, $connection)
    {
        safe_ini_set('mssql.timeout', strval($seconds));
    }

    /**
     * Encode an SQL statement fragment for a conditional to see if two strings are equal.
     *
     * @param  ID_TEXT $attribute The attribute
     * @param  string $compare The comparison
     * @return string The SQL
     */
    public function string_equal_to($attribute, $compare)
    {
        return $attribute . " LIKE '" . $this->escape_string($compare) . "'";
    }

    /**
     * Encode a LIKE string comparision fragement for the database system. The pattern is a mixture of characters and ? and % wildcard symbols.
     *
     * @param  string $pattern The pattern
     * @return string The encoded pattern
     */
    public function encode_like($pattern)
    {
        return $this->escape_string(str_replace('%', '*', $pattern));
    }

    /**
     * Find whether full-text-search is present
     *
     * @param  mixed $connection The DB connection
     * @return boolean Whether it is
     */
    public function has_full_text($connection)
    {
        global $SITE_INFO;
        if ((!empty($SITE_INFO['skip_fulltext_sqlserver'])) && ($SITE_INFO['skip_fulltext_sqlserver'] == '1')) {
            return false;
        }
        return true;
    }

    /**
     * Assemble part of a WHERE clause for doing full-text search
     *
     * @param  string $content Our match string (assumes "?" has been stripped already)
     * @param  boolean $boolean Whether to do a boolean full text search
     * @return string Part of a WHERE clause for doing full-text search
     */
    public function full_text_assemble($content, $boolean)
    {
        $content = str_replace('"', '', $content);
        return 'CONTAINS ((?),\'' . $this->escape_string($content) . '\')';
    }

    /**
     * Escape a string so it may be inserted into a query. If SQL statements are being built up and passed using db_query then it is essential that this is used for security reasons. Otherwise, the abstraction layer deals with the situation.
     *
     * @param  string $string The string
     * @return string The escaped string
     */
    public function escape_string($string)
    {
        $string = fix_bad_unicode($string);

        return str_replace("'", "''", $string);
    }

    /**
     * Close the database connections. We don't really need to close them (will close at exit), just disassociate so we can refresh them.
     */
    public function close_connections()
    {
        $this->cache_db = array();
    }
}
