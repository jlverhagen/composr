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
 * @package    core_database_drivers
 */

// See tut_sqlite3 tutorial for documentation on using SQLite3.

/**
 * Database driver class.
 *
 * @package core_database_drivers
 */
class Source_database_static_sqlite3 extends Source_database_driver
{
    protected $cache_db = [];

    protected $table_prefix;

    /**
     * Set up the database driver.
     *
     * @param  string $table_prefix Table prefix
     */
    public function __construct(string $table_prefix)
    {
        $this->table_prefix = $table_prefix;
    }

    /**
     * Get the default user for making db connections (used by the installer as a default).
     *
     * @return string The default user for db connections
     */
    public function default_user() : string
    {
        return 'sqlite';
    }

    /**
     * Get the default password for making db connections (used by the installer as a default).
     *
     * @return string The default password for db connections
     */
    public function default_password() : string
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
     * @return ?mixed A database connection (null: failed)
     */
    public function get_connection(bool $persistent, string $db_name, string $db_host, string $db_user, string $db_password, bool $fail_ok = false)
    {
        // Potential caching
        if (isset($this->cache_db[$db_name])) {
            return $this->cache_db[$db_name];
        }

        if (!class_exists('SQLite3')) {
            $error = 'The \'sqlite3\' PHP extension is not installed (anymore?). You need to contact the system administrator of this server.';
            if ($fail_ok) {
                echo ((running_script('install')) && (get_param_string('type', '') == 'ajax_db_details')) ? strip_html($error) : $error;
                return null;
            }
            critical_error('PASSON', $error);
        }

        // Make the directory where we will create the database
        $path = get_custom_file_base() . '/data_custom/sqlitedb/' . $db_name . '.sqlite';
        if (!is_dir(dirname($path))) {
            require_code('files2');
            make_missing_directory(dirname($path));
        }

        require_code('failure');

        push_throw_errors(true);
        try {
            $db_link = new SQLite3($path);
            $db_link->createFunction('MD5', 'md5', 1);
            $db_link->exec('PRAGMA foreign_keys = ON;'); // Activates foreign key constraints
        } catch (Exception $e) {
            $error = 'Could not connect to database (' . $e->getMessage() . ')';
            if ($fail_ok) {
                echo $error . "\n";
                return null;
            }
            critical_error('PASSON', $error);
        }
        pop_throw_errors();

        $this->cache_db[$db_name] = $db_link;

        return $db_link;
    }

    /**
     * Find whether the database backend uses offset syntax.
     *
     * @return boolean Whether it does
     */
    public function uses_offset_syntax() : bool
    {
        return true;
    }

    /**
     * Apply a limit clause to the query.
     *
     * @param  string $query The query to apply the limit clause to
     * @param  ?integer $max The maximum number of rows to return
     * @param  integer $start The row to start at
     */
    public function apply_sql_limit_clause(string &$query, ?int $max = null, int $start = 0)
    {
        if ($max < 0) {
            $max = null;
        }

        if ((cms_strtoupper_ascii(substr(ltrim($query), 0, 7)) == 'SELECT ') || (cms_strtoupper_ascii(substr(ltrim($query), 0, 8)) == '(SELECT ')) { // Unfortunately we can't apply to DELETE FROM and update :(. But its not too important, LIMIT'ing them was unnecessarily anyway
            if ($max === null) {
                if ($start != 0) {
                    $query .= ' LIMIT -1 OFFSET ' . strval($start);
                }
            } else {
                $query .= ' LIMIT ' . strval($max) . ' OFFSET ' . strval($start);
            }
        }
    }

    /**
     * Perform a table migration to change a field.
     *
     * @param  string $query Migration command
     * @param  object $connection Database connection
     * @return ?mixed The results
     */
    protected function do_field_migration(string $query, $connection)
    {
        /*
        list($command, $table, $old_name, $new_db_type, $may_be_null, $is_autoincrement, $new_name) = explode(':', $query);
        if ($new_name === '') $new_name = $old_name;

        // 1. Get current columns
        $columns = [];
        $res = $connection->query("PRAGMA table_info(" . $table . ")");
        while ($row = $res->fetchArray(SQLITE3_ASSOC)) {
            $columns[] = $row;
        }

        // 2. Build new column definitions
        $col_defs = [];
        $select_cols = [];
        $insert_cols = [];
        foreach ($columns as $col) {
            $c_name = $col['name'];
            $c_type = $col['type'];
            $c_notnull = $col['notnull'];
            $c_default = $col['dflt_value'];
            $c_pk = $col['pk'];

            $is_this_col = ($c_name === $old_name);

            $target_name = $is_this_col ? $new_name : $c_name;
            $target_type = $is_this_col ? $new_db_type : $c_type;
            $target_notnull = $is_this_col ? ($may_be_null ? 0 : 1) : $c_notnull;
            $target_pk = $c_pk; // Keep PK status for now unless it's changed to autoincrement

            if ($is_this_col && $is_autoincrement == '1') {
                $def = $target_name . ' INTEGER PRIMARY KEY AUTOINCREMENT';
            } else {
                $def = $target_name . ' ' . $target_type . ($target_notnull ? ' NOT NULL' : '');
                if ($c_default !== null) $def .= ' DEFAULT ' . $c_default;
                if ($target_pk && $is_autoincrement != '1') $def .= ' PRIMARY KEY';
            }

            $col_defs[] = $def;
            $select_cols[] = $c_name;
            $insert_cols[] = $target_name; // TODO: needs casting
        }

        // 3. Perform migration
        $connection->exec('BEGIN TRANSACTION');
        $temp_table = $table . '_new';
        $connection->exec('CREATE TABLE ' . $temp_table . ' (' . implode(', ', $col_defs) . ')');
        $connection->exec('INSERT INTO ' . $temp_table . ' (' . implode(', ', $insert_cols) . ') SELECT ' . implode(', ', $select_cols) . ' FROM ' . $table);
        // TODO: add indexes
        // TODO: add foreign keys
        $connection->exec('DROP TABLE ' . $table);
        $connection->exec('ALTER TABLE ' . $temp_table . ' RENAME TO ' . $table);
        $connection->exec('COMMIT');
        */

        return null;
    }

    /**
     * Perform a table migration to change the primary key.
     *
     * @param  string $query Migration command
     * @param  object $connection Database connection
     * @return ?mixed The results
     */
    protected function do_primary_key_migration(string $query, $connection)
    {
        /*
        list($command, $table, $new_key_str) = explode(':', $query);
        $new_key = ($new_key_str === '') ? [] : explode(',', $new_key_str);

        // 1. Get current columns
        $columns = [];
        $res = $connection->query("PRAGMA table_info(" . $table . ")");
        while ($row = $res->fetchArray(SQLITE3_ASSOC)) {
            $columns[] = $row;
        }

        // 2. Build new column definitions
        $col_defs = [];
        $select_cols = [];
        foreach ($columns as $col) {
            $c_name = $col['name'];
            $c_type = $col['type'];
            $c_notnull = $col['notnull'];
            $c_default = $col['dflt_value'];

            $def = $c_name . ' ' . $c_type . ($c_notnull ? ' NOT NULL' : '');
            if ($c_default !== null) $def .= ' DEFAULT ' . $c_default;

            $col_defs[] = $def;
            $select_cols[] = $c_name;
        }

        $pk_def = '';
        if (!empty($new_key)) {
            $pk_def = ', PRIMARY KEY (' . implode(', ', $new_key) . ')';
        }

        // 3. Perform migration
        $connection->exec('BEGIN TRANSACTION');
        $temp_table = $table . '_new';
        $connection->exec('CREATE TABLE ' . $temp_table . ' (' . implode(', ', $col_defs) . $pk_def . ')');
        $connection->exec('INSERT INTO ' . $temp_table . ' SELECT * FROM ' . $table);
        // TODO: add indexes
        // TODO: add foreign keys
        $connection->exec('DROP TABLE ' . $table);
        $connection->exec('ALTER TABLE ' . $temp_table . ' RENAME TO ' . $table);
        $connection->exec('COMMIT');
        */

        return null;
    }

    /**
     * Run a query on the database connection.
     *
     * @param  string $query The query to run
     * @param  mixed $connection The database connection
     * @param  ?integer $max The maximum number of rows to affect; negative number is number of maximum bytes to return (null: no limit)
     * @param  integer $start The row to start at
     * @param  boolean $fail_ok Whether to on error echo an error and return with a null, rather than giving a critical error
     * @param  boolean $get_insert_id Whether to get the insert ID of the query
     * @param  boolean $save_as_volatile Whether to save the query as volatile (i.e. not to cache it)
     * @return ?mixed The results of the query (null: error)
     */
    public function query(string $query, $connection, ?int $max = null, int $start = 0, bool $fail_ok = false, bool $get_insert_id = false, bool $save_as_volatile = false)
    {
        static $attempts = [];
        $hash = md5($query);
        if (!isset($attempts[$hash])) {
            $attempts[$hash] = 0;
        }

        if (substr($query, 0, 17) === '!!!MIGRATE_FIELD:') {
            return $this->do_field_migration($query, $connection);
        }
        if (substr($query, 0, 23) === '!!!MIGRATE_PRIMARY_KEY:') {
            return $this->do_primary_key_migration($query, $connection);
        }

        $max_bytes = null;
        if (($max !== null) && $max < 0) {
            $max_bytes = abs($max);
            $max = null;
        }

        $this->apply_sql_limit_clause($query, $max, $start);

        // SQLite has DB-level locking
        do {
            $err = '';
            $results = @$connection->query($query);
            if ($results === false) {
                $attempts[$hash]++;
                $err = $connection->lastErrorMsg();
                usleep(mt_rand(25000, 100000));
            }
        } while (($results === false) && ($attempts[$hash] < 100) && (cms_strtolower_ascii($err) == 'database is locked'));

        if (($results === false) && (!$fail_ok)) {
            $this->handle_failed_query($query, $err, $connection);
            return null;
        }

        if ($get_insert_id) {
            return $connection->lastInsertRowID();
        }

        $sub = substr(ltrim($query), 0, 4);
        if (($results !== true) && (($sub === '(SEL') || ($sub === 'SELE') || ($sub === 'sele') || ($sub === 'CHEC') || ($sub === 'EXPL') || ($sub === 'REPA') || ($sub === 'DESC') || ($sub === 'SHOW')) && ($results !== false)) {
            return $this->get_query_rows($results, $query, $start, $max_bytes);
        }

        return null;
    }

    /**
     * Handle a failed query.
     *
     * @param  string $query The query that failed
     * @param  string $err The error message
     * @param  mixed $connection The database connection
     */
    protected function handle_failed_query(string $query, string $err, $connection)
    {
        if (function_exists('ocp_mark_as_escaped')) {
            ocp_mark_as_escaped($err);
        }
        if ((!running_script('upgrader')) && ((!get_mass_import_mode()) || (current_fatalistic() > 0)) && (strpos($err, 'Duplicate entry') === false)) {
            if ((!function_exists('do_lang')) || (do_lang('QUERY_FAILED', null, null, null, null, false) === null)) {
                $this->failed_query_exit(htmlentities('Query failed: ' . $query . ' : ' . $err));
            }
            $this->failed_query_exit(do_lang_tempcode('QUERY_FAILED', escape_html($query), ($err)));
        } else {
            $this->failed_query_echo(htmlentities('Database query failed: ' . $query . ' [') . ($err) . htmlentities(']'));
        }
    }

    /**
     * Get the rows returned from a query.
     *
     * @param  object $results The query result pointer
     * @param  string $query The query that was run
     * @param  integer $start The row to start at
     * @param  ?integer $max_bytes The maximum number of bytes to return (not supported by all drivers)
     * @return array The rows returned
     */
    protected function get_query_rows($results, string $query, int $start, ?int $max_bytes = null) : array
    {
        $out = [];
        $total_bytes = 0;
        while ($row = $results->fetchArray(SQLITE3_ASSOC)) {
            if ($max_bytes !== null) {
                $total_bytes += strlen(serialize($row));
                if ($total_bytes > $max_bytes) {
                    break;
                }
            }
            $out[] = $row;
        }
        return $out;
    }

    /**
     * Get a map of software field types, to actual database types.
     *
     * @param  boolean $for_alter Whether this is for adding a table field
     * @return array The map
     */
    public function get_type_remap(bool $for_alter = false) : array
    {
        $type_remap = [
            'AUTO' => 'integer',
            'AUTO_LINK' => 'integer',
            'INTEGER' => 'integer',
            'UINTEGER' => 'integer',
            'SHORT_INTEGER' => 'integer',
            'REAL' => 'real',
            'BINARY' => 'integer',
            'MEMBER' => 'integer',
            'GROUP' => 'integer',
            'TIME' => 'integer',
            'LONG_TRANS' => 'integer',
            'SHORT_TRANS' => 'integer',
            'LONG_TRANS__COMCODE' => multi_lang_content() ? 'integer' : 'text',
            'SHORT_TRANS__COMCODE' => multi_lang_content() ? 'integer' : 'text',
            'SHORT_TEXT' => 'text',
            'TEXT' => 'text',
            'LONG_TEXT' => 'text',
            'ID_TEXT' => 'text',
            'MINIID_TEXT' => 'text',
            'IP' => 'text',
            'LANGUAGE_NAME' => 'text',
            'TOKEN' => 'text',
            'SERIAL' => 'text',
            'URLPATH' => 'text',
            'BGUID' => 'text',
        ];
        return $type_remap;
    }

    /**
     * Get SQL for creating a new table.
     *
     * @param  ID_TEXT $table_name The table name
     * @param  array $fields A map of field names to software field types (with *#? encodings)
     * @param  mixed $connection The DB connection to make on
     * @param  ID_TEXT $raw_table_name The table name with no table prefix
     * @param  boolean $save_bytes Whether to use lower-byte table storage, with trade-offs of not being able to support all unicode characters; use this if key length is an issue
     * @return array List of SQL queries to run
     */
    public function create_table__sql(string $table_name, array $fields, $connection, string $raw_table_name, bool $save_bytes = false) : array
    {
        $type_remap = $this->get_type_remap();

        // First pass to count keys and find AUTO
        $keys = [];
        $auto_field = null;
        foreach ($fields as $name => $type) {
            if ($type[0] == '*') {
                $keys[] = $name;
                if (substr($type, 1) == 'AUTO') {
                    $auto_field = $name;
                }
            } elseif ($type == 'AUTO') {
                $auto_field = $name;
            }
        }

        $_fields = '';
        $real_keys = [];
        foreach ($fields as $name => $type) {
            $is_key = false;
            if ($type[0] == '*') {
                $type = substr($type, 1);
                $is_key = true;
            }

            if ($type[0] == '?') {
                $type = substr($type, 1);
                $perhaps_null = 'NULL';
            } else {
                $perhaps_null = 'NOT NULL';
            }

            $db_type = isset($type_remap[$type]) ? $type_remap[$type] : $type;

            if ($name === $auto_field) {
                $_fields .= '    ' . $name . ' INTEGER PRIMARY KEY AUTOINCREMENT,' . "\n";
            } else {
                $_fields .= '    ' . $name . ' ' . $db_type;

                if (substr($name, -13) == '__source_user') {
                    $_fields .= ' DEFAULT ' . strval(db_get_first_id());
                }

                $_fields .= ' ' . $perhaps_null . ',' . "\n";

                if ($is_key) {
                    $real_keys[] = $name;
                }
            }
        }

        if (!empty($real_keys)) {
            $query = 'CREATE TABLE ' . $table_name . ' (' . "\n" . $_fields . '    UNIQUE(' . implode(', ', $real_keys) . ")\n)";
        } else {
            $query = 'CREATE TABLE ' . $table_name . ' (' . "\n" . rtrim($_fields, ",\n") . "\n)";
        }
        return [$query];
    }

    /**
     * Get SQL for renaming a table.
     *
     * @param  ID_TEXT $old Old name
     * @param  ID_TEXT $new New name
     * @return string SQL query to run
     */
    public function rename_table__sql(string $old, string $new) : string
    {
        return 'ALTER TABLE ' . $old . ' RENAME TO ' . $new;
    }

    /**
     * Find whether drop table "if exists" is present.
     *
     * @return boolean Whether it is
     */
    public function has_drop_table_if_exists() : bool
    {
        return true;
    }

    /**
     * Find whether table truncation support is present.
     *
     * @return boolean Whether it is
     */
    public function has_truncate_table() : bool
    {
        return false;
    }

    /**
     * Find whether expression ordering can happen using ALIASes from the SELECT clause.
     *
     * @return boolean Whether it is
     */
    public function has_expression_ordering_by_alias() : bool
    {
        return true;
    }

    /**
     * Get SQL for changing the type of a DB field in a table.
     *
     * @param  ID_TEXT $table_name The table name
     * @param  ID_TEXT $name The field name
     * @param  ID_TEXT $db_type The new field type
     * @param  boolean $may_be_null If the field may be null
     * @param  ?boolean $is_autoincrement If the field is an auto-increment field (null: keep as-is)
     * @param  ID_TEXT $new_name The new field name (blank: keep as-is)
     * @return array List of SQL queries to run
     */
    public function alter_table_field__sql(string $table_name, string $name, string $db_type, bool $may_be_null, ?bool &$is_autoincrement, string $new_name) : array
    {
        //return ['!!!MIGRATE_FIELD:' . $table_name . ':' . $name . ':' . $db_type . ':' . ($may_be_null ? '1' : '0') . ':' . ($is_autoincrement ? '1' : '0') . ':' . $new_name];
        return []; // TODO
    }

    /**
     * Get SQL for creating an index on a table.
     *
     * @param  ID_TEXT $table_name The name of the table to create the index on
     * @param  ID_TEXT $index_name The name of the index
     * @param  string $_fields A comma-separated list of fields to index
     * @param  mixed $connection_read The DB connection to make on
     * @param  ID_TEXT $raw_table_name The table name with no table prefix
     * @param  string $unique_key_fields A comma-separated list of fields that form a unique key
     * @param  string $table_prefix The table prefix
     * @return array List of SQL queries to run
     */
    public function create_index__sql(string $table_name, string $index_name, string $_fields, $connection_read, string $raw_table_name, string $unique_key_fields, string $table_prefix) : array
    {
        $fields = array_map([$this, 'strip_index_length'], explode(',', $_fields));
        $fields_str = implode(', ', $fields);

        return ['CREATE INDEX idx_' . $table_name . '_' . $index_name . ' ON ' . $table_name . '(' . $fields_str . ')'];
    }

    /**
     * SQLite does not support index lengths; remove them from field definitions.
     *
     * @param  string $field The field definition
     * @return string The stripped field definition
     */
    protected function strip_index_length(string $field) : string
    {
        return preg_replace('/([a-zA-Z0-9]+)\s*\(.*?\)/', '$1', trim($field));
    }

    /**
     * Get SQL for dropping an index on a table.
     *
     * @param  ID_TEXT $table_name The name of the table to drop the index on
     * @param  ID_TEXT $index_name The name of the index
     * @return ?string SQL query to run (null: not supported)
     */
    public function drop_index__sql(string $table_name, string $index_name) : ?string
    {
        return 'DROP INDEX idx_' . $table_name . '_' . $index_name;
    }

    /**
     * Get SQL for changing the primary key of a table.
     *
     * @param  string $table_prefix The table prefix
     * @param  ID_TEXT $table_name The name of the table to create the index on
     * @param  array $new_key A list of fields to put in the new key
     * @return array List of SQL queries to run
     */
    public function change_primary_key__sql(string $table_prefix, string $table_name, array $new_key) : array
    {
        //return ['!!!MIGRATE_PRIMARY_KEY:' . $table_prefix . $table_name . ':' . implode(',', $new_key)];
        return []; // TODO
    }

    /**
     * Get the number of rows in a table, with approximation support for performance (if necessary on the particular database backend).
     *
     * @param  string $table The table name
     * @param  mixed $connection The DB connection
     * @return ?integer The number of rows (null: error)
     */
    public function get_table_count_approx(string $table, $connection) : ?int
    {
        $res = $this->query('SELECT COUNT(*) AS cnt FROM ' . $table, $connection, 1);
        if ($res === null) {
            return null;
        }
        return $res[0]['cnt'];
    }

    /**
     * Get the minimum search length.
     *
     * @param  mixed $connection The DB connection
     * @return integer The minimum search length
     */
    public function get_minimum_search_length($connection) : int
    {
        return 3;
    }

    /**
     * Escape a string for use in a query.
     *
     * @param  string $string The string to escape
     * @return string The escaped string
     */
    public function escape_string(string $string) : string
    {
        return SQLite3::escapeString($string);
    }

    /**
     * Close all database connections.
     */
    public function close_connections()
    {
        foreach ($this->cache_db as $connection) {
            $connection->close();
        }
        $this->cache_db = [];
    }

    /**
     * Get an SQL fragment for a database function.
     *
     * @param  string $function Function name
     * @param  array $args List of string arguments, assumed already quoted/escaped correctly for the particular database
     * @return ?string SQL fragment (null: not supported)
     */
    public function db_function(string $function, array $args = []) : ?string
    {
        switch ($function) {
            case 'IFF':
                return 'CASE WHEN ' . $args[0] . ' THEN ' . $args[1] . ' ELSE ' . $args[2] . ' END';
            case 'RAND':
                $function = 'RANDOM';
                break;
            case 'MOD':
                return $args[0] . ' % ' . $args[1];
            case 'CONCAT':
                $ret = $args[0];
                foreach ($args as $i => $arg) {
                    if ($i == 0) {
                        continue;
                    }

                    $ret .= ' || ' . $arg;
                }
                return $ret;
        }
        return parent::db_function($function, $args);
    }
}
