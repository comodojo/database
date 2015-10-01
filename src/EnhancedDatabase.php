<?php namespace Comodojo\Database;

use \Comodojo\Database\QueryBuilder\QueryGet;
use \Comodojo\Database\QueryBuilder\QueryStore;
use \Comodojo\Database\QueryBuilder\QueryUpdate;
use \Comodojo\Database\QueryBuilder\QueryDelete;
use \Comodojo\Database\QueryBuilder\QueryTruncate;
use \Comodojo\Database\QueryBuilder\QueryCreate;
use \Comodojo\Database\QueryBuilder\QueryDrop;
use \Comodojo\Exception\DatabaseException;
use \Exception;

/**
 * Enhanced database class for comodojo.
 *
 * It extends the base class 'Database' with a cross-database query builder.
 * 
 * @package     Comodojo Spare Parts
 * @author      Marco Giovinazzi <marco.giovinazzi@comodojo.org>
 * @license     MIT
 *
 * LICENSE:
 * 
 * THE SOFTWARE IS PROVIDED "AS IS", WITHOUT WARRANTY OF ANY KIND, EXPRESS OR
 * IMPLIED, INCLUDING BUT NOT LIMITED TO THE WARRANTIES OF MERCHANTABILITY,
 * FITNESS FOR A PARTICULAR PURPOSE AND NONINFRINGEMENT. IN NO EVENT SHALL THE
 * AUTHORS OR COPYRIGHT HOLDERS BE LIABLE FOR ANY CLAIM, DAMAGES OR OTHER
 * LIABILITY, WHETHER IN AN ACTION OF CONTRACT, TORT OR OTHERWISE, ARISING FROM,
 * OUT OF OR IN CONNECTION WITH THE SOFTWARE OR THE USE OR OTHER DEALINGS IN
 * THE SOFTWARE.
 */

class EnhancedDatabase extends Database {

    /**
     * Database table to manipulate
     *
     * @var string
     */
    private $table = null;

    /**
     * Default table prefix
     *
     * @var string
     */
    private $table_prefix = null;

    /**
     * Use SELECT DISTINCT instead of SELECT
     *
     * @var bool
     */
    private $distinct = false;

    /**
     * Keys, imploded as a string
     *
     * @var string
     */
    private $keys = null;

    /**
     * Keys, in array form
     *
     * @var array
     */
    private $keys_array = array();

    /**
     * Values, imploded as a string
     *
     * @var string
     */
    private $values = null;

    /**
     * Values, in array form
     *
     * @var array
     */
    private $values_array = array();

    /**
     * Where conditions
     *
     * @var string
     */
    private $where = null;

    /**
     * Table joins, if any
     *
     * @var string
     */
    private $join = null;

    /**
     * Using clause
     *
     * @var string
     */
    private $using = null;

    /**
     * On clause
     *
     * @var string
     */
    private $on = null;

    /**
     * Orderby clause
     *
     * @var string
     */
    private $order_by = null;

    /**
     * Groupby clause
     *
     * @var string
     */
    private $group_by = null;

    /**
     * Having clause
     *
     * @var string
     */
    private $having = null;

    /**
     * Columns definition for create table method
     *
     * @var array
     */
    private $columns = array();

    /**
     * If true, builder will reset itself after each build
     *
     * @var bool
     */
    private $auto_clean = false;

    /**
     * List of currently supported types of query
     *
     * @var array
     */
    static private $supported_query_types = array('GET', 'STORE', 'UPDATE', 'DELETE', 'TRUNCATE', 'CREATE', 'DROP'/*,'ALTER'*/);

    /**
     * If $mode == true, builder will reset itself after each build
     *
     * @param   bool    $mode
     *
     * @return  \Comodojo\Database\EnhancedDatabase
     */
    final public function autoClean($mode = true) {

        $this->auto_clean = filter_var($mode, FILTER_VALIDATE_BOOLEAN);

        return $this;

    }

    /**
     * Return the current query as a string
     *
     * @return  string
     *
     * @throws  \Comodojo\Exception\DatabaseException
     */
    public function getQuery($query, $parameters = array()) {

        try {
            
            $query = $this->buildQuery($query, $parameters);

            $query = str_replace("*_DBPREFIX_*", $this->table_prefix, $query);

        } catch (DatabaseException $de) {
            
            throw $de;

        }

        return $query;

    }

    /**
     * Set the database table
     *
     * @param   string  $table
     *
     * @return  \Comodojo\Database\EnhancedDatabase
     *
     * @throws  \Comodojo\Exception\DatabaseException
     */
    public function table($table) {

        $table_pattern = in_array($this->model, Array('MYSQLI', 'MYSQL_PDO')) ? "`*_DBPREFIX_*%s`" : "*_DBPREFIX_*%s";

        if ( empty($table) ) throw new DatabaseException('Invalid table name');

        else if ( is_null($this->table) ) $this->table = sprintf($table_pattern, trim($table));

        else $this->table .= ", ".sprintf($table_pattern, trim($table));

        return $this;

    }

    /**
     * Set the database tables' prefix
     *
     * @param   string  $prefix
     *
     * @return  \Comodojo\Database\EnhancedDatabase
     */
    public function tablePrefix($prefix) {

        $this->table_prefix = empty($prefix) ? null : $prefix;

        return $this;

    }

    /**
     * Enable use of SELECT DISTINCT instead of SELECT
     *
     * @param   bool    $value
     *
     * @return  \Comodojo\Database\EnhancedDatabase
     */
    final public function distinct($value = true) {

        $this->distinct = filter_var($value, FILTER_VALIDATE_BOOLEAN);

        return $this;

    }

    /**
     * Declare query keys
     *
     * Param $keys may be a string or an array of values.
     *
     * In both cases, there are two special char notation that are processed if contained in a key definition:
     *
     * - '::' defines an operation on one or more keys:
     *
     *        - $this->keys('COUNT::id') will be transformed into COUNT('id')
     *
     *        - $this->keys('CONCAT::first::, ::second') will be transformed into CONCAT(first,', ',second) 
     *
     * - '=>' defines an alias name for key:
     *
     *        - $this->keys('id=>foo') will be transformed into 'id' AS 'foo'
     *
     * Two notation can be mixed to obtain a complex expression:
     *
     *        - $this->keys('CONCAT::id::, ::position=>foo') become CONCAT(id,', ',position) AS foo
     *
     * @param   mixed   $keys
     *
     * @return  \Comodojo\Database\EnhancedDatabase
     *
     * @throws  \Comodojo\Exception\DatabaseException
     */
    public function keys($keys) {

        $processed_keys = array();

        try {
            
            if ( empty($keys) ) throw new DatabaseException('Invalid key/s');

            else if ( is_array($keys) ) foreach ( $keys as $key ) array_push($processed_keys, $this->composeKey($key));

            else array_push($processed_keys, $this->composeKey($keys));


        } catch (DatabaseException $de) {
            
            throw $de;

        }

        $this->keys = implode(',', $processed_keys);

        $this->keys_array = $processed_keys;
        
        return $this;
        
    }

    /**
     * Declare query values
     *
     * Param $values may be a string or an array of values.
     *
     * This method can be called n times to add multiple values to query.
     *
     * @param   mixed   $values
     *
     * @return  \Comodojo\Database\EnhancedDatabase
     *
     * @throws  \Comodojo\Exception\DatabaseException
     */
    public function values($values) {

        if ( empty($values) ) throw new DatabaseException('Invalid value/s');

        $processed_values = array();
        
        try {
            
            if ( is_array($values) ) foreach ( $values as $value ) array_push($processed_values, $this->composeValue($value));

            else array_push($processed_values, $this->composeValue($values));


        } catch (DatabaseException $de) {
            
            throw $de;

        }

        $this->values = is_null($this->values) ? '('.implode(',', $processed_values).')' : $this->values.', ('.implode(',', $processed_values).')';

        array_push($this->values_array, $processed_values);
        
        return $this;
        
    }

    /**
     * Add first where condition
     *
     * Simple where conditions are composed defining scalar $column and $value parameters; for example:
     *
     * - $this->where('name','=', "jhon")
     *
     * - $this->where('name','LIKE', "jhon%")
     *
     * Nested where conditions can be created using nested arrays; for example:
     *
     * - $this->where(array('name','=', "jhon"), 'AND', array('lastname', '=', 'smith'))
     *
     * - $this->where('name', 'BETWEEN', array(1,10))
     *
     * This method can be also called n times to add multiple values to query.
     *
     * @param   mixed   $column
     * @param   string  $operator
     * @param   mixed   $value
     *
     * @return  \Comodojo\Database\EnhancedDatabase
     *
     * @throws  \Comodojo\Exception\DatabaseException
     */
    public function where($column, $operator, $value) {
        
        try {

            $this->where = "WHERE ".$this->composeWhereCondition($column, $operator, $value);

        } catch (DatabaseException $de) {

            throw $de;

        }

        return $this;

    }

    /**
     * Add an AND where condition
     *
     * @param   mixed   $column
     * @param   string  $operator
     * @param   mixed   $value
     *
     * @return  \Comodojo\Database\EnhancedDatabase
     *
     * @throws  \Comodojo\Exception\DatabaseException
     */
    public function andWhere($column, $operator, $value) {
        
        try {

            $this->where .= " AND ".$this->composeWhereCondition($column, $operator, $value);

        } catch (DatabaseException $de) {

            throw $de;

        }

        return $this;

    }

    /**
     * Add an OR where condition
     *
     * @param   mixed   $column
     * @param   string  $operator
     * @param   mixed   $value
     *
     * @return  \Comodojo\Database\EnhancedDatabase
     *
     * @throws  \Comodojo\Exception\DatabaseException
     */
    public function orWhere($column, $operator, $value) {
        
        try {

            $this->where .= " OR ".$this->composeWhereCondition($column, $operator, $value);

        } catch (DatabaseException $de) {

            throw $de;

        }

        return $this;

    }

    /**
     * Add a join clause to the query.
     *
     * WARNING: not all databases support joins like RIGHT, NATURAL or FULL.
     * This method WILL NOT alert or throw exception in case of unsupported join,
     * this kind of check will be implemented in next versions.
     *
     * @param   string  $join_type
     * @param   string  $table
     *
     * @return  \Comodojo\Database\EnhancedDatabase
     *
     * @throws  \Comodojo\Exception\DatabaseException
     */
    public function join($join_type, $table, $as = null) {
        
        $join = strtoupper($join_type);

        $join_type_list = Array('INNER', 'NATURAL', 'CROSS', 'LEFT', 'RIGHT', 'LEFT OUTER', 'RIGHT OUTER', 'FULL OUTER', null);

        if ( !in_array($join, $join_type_list) || empty($table) ) throw new DatabaseException('Invalid parameters for database join');

        if ( is_null($as) ) {

            $join_pattern = " %sJOIN %s";

            if ( is_null($this->join) ) $this->join = sprintf($join_pattern, $join." ", $table);

            else $this->join .= " ".sprintf($join_pattern, $join." ", $table);

        } else {

            $join_pattern = " %sJOIN %s AS %s";

            if ( is_null($this->join) ) $this->join = sprintf($join_pattern, $join." ", $table, $as);

            else $this->join .= " ".sprintf($join_pattern, $join." ", $table, $as);

        }
        
        return $this;

    }

    /**
     * Add a using clause to the query.
     *
     * $columns may be a string (signle column) or an array (multple columns)
     *
     * @param   mixed   $columns
     *
     * @return  \Comodojo\Database\EnhancedDatabase
     *
     * @throws  \Comodojo\Exception\DatabaseException
     */
    public function using($columns) {

        $using_pattern = "USING (%s)";

        if ( empty($columns) ) throw new DatabaseException('Invalid parameters for database::using');
        
        $this->using = sprintf($using_pattern, is_array($columns) ? implode(',', $columns) : $columns);
        
        return $this;

    }

    /**
     * Add a ON clause to the query.
     *
     * @param   string  $first_column
     * @param   string  $operator
     * @param   string  $second_column
     *
     * @return  \Comodojo\Database\EnhancedDatabase
     *
     * @throws  \Comodojo\Exception\DatabaseException
     */
    public function on($first_column, $operator, $second_column) {

        try {

            $this->on = "ON ".$this->composeOnClause($first_column, $operator, $second_column);

        } catch (DatabaseException $de) {

            throw $de;

        }

        return $this;

    }

    /**
     * Add an AND ON clause to the query.
     *
     * @param   string  $first_column
     * @param   string  $operator
     * @param   string  $second_column
     *
     * @return  \Comodojo\Database\EnhancedDatabase
     *
     * @throws  \Comodojo\Exception\DatabaseException
     */
    public function andOn($first_column, $operator, $second_column) {

        try {

            $this->on .= " AND ".$this->composeOnClause($first_column, $operator, $second_column);

        } catch (DatabaseException $de) {

            throw $de;

        }

        return $this;

    }

    /**
     * Add a OR ON clause to the query.
     *
     * @param   string  $first_column
     * @param   string  $operator
     * @param   string  $second_column
     *
     * @return  \Comodojo\Database\EnhancedDatabase
     *
     * @throws  \Comodojo\Exception\DatabaseException
     */
    public function orOn($first_column, $operator, $second_column) {

        try {

            $this->on .= " OR ".$this->composeOnClause($first_column, $operator, $second_column);

        } catch (DatabaseException $de) {

            throw $de;

        }

        return $this;

    }

    /**
     * Order by column or a group of columns
     *
     * @param   mixed   $columns
     * @param   mixed   $directions
     *
     * @return  \Comodojo\Database\EnhancedDatabase
     *
     * @throws  \Comodojo\Exception\DatabaseException
     */
    public function orderBy($columns, $directions = null) {

        if ( empty($columns) ) throw new DatabaseException('Invalid order by column');

        $supported_directions = array("DESC", "ASC");

        switch ( $this->model ) {
            
            case ("SQLITE_PDO"):

                $order_column_pattern = " %s COLLATE NOCASE%s";

                break;

            //case ("MYSQLI"):
            //case ("MYSQL_PDO"):
            //case ("POSTGRESQL"):
            //case ("DB2"):
            //case ("DBLIB_PDO"):
            //case ("ORACLE_PDO"):
            default:

                $order_column_pattern = " %s%s";

                break;

        }

        if ( is_array($columns) ) {

            $column = array();

            for ( $i = 0; $i < sizeof($columns) - 1; $i++ ) {
                
                if ( is_array($directions) && @isset($directions[$i]) && @in_array(strtoupper($directions[$i]), $supported_directions) ) $direction = ' '.strtoupper($direction[$i]);

                else $direction = '';

                array_push($column, sprintf($order_column_pattern, $columns[$i], $direction));

            }

            $this->order_by = "ORDER BY".implode(', ', $column);

        } else {

            $column = trim($columns);

            $direction = is_null($directions) ? null : ' '.strtoupper($directions);

            $this->order_by = "ORDER BY".sprintf($order_column_pattern, $column, $direction);

        }

        return $this;

    }

    /**
     * Group by column or a group of columns
     *
     * @param   mixed   $columns
     *
     * @return  \Comodojo\Database\EnhancedDatabase
     *
     * @throws  \Comodojo\Exception\DatabaseException
     */
    public function groupBy($columns) {

        $group_column_pattern = "%s";

        if ( empty($columns) ) throw new DatabaseException('Invalid group by column');

        elseif ( is_array($columns) ) {

            array_walk($columns, function(&$column, $key) {

                $column = sprintf($group_column_pattern, trim($column));

            });

            $this->group_by = "GROUP BY ".implode(',', $columns);

        } else {

            $column = trim($columns);

            $this->group_by = "GROUP BY ".sprintf($group_column_pattern, $column);

        }

        return $this;

    }

    /**
     * Set the having clause in a sql statement.
     *
     * Differently from other methods, $having_clauses should contain the FULL CLAUSE.
     *
     * @param   mixed $having_clauses
     *
     * @return  \Comodojo\Database\EnhancedDatabase
     *
     * @throws  \Comodojo\Exception\DatabaseException
     */
    public function having($having_clauses) {

        $having_column_pattern = "%s";

        if ( empty($having_clauses) ) throw new DatabaseException('Invalid having clause');

        elseif ( is_array($having_clauses) ) {

            array_walk($having_clauses, function(&$column, $key) {

                $column = sprintf($having_column_pattern, trim($column));

            });

            $this->having = "HAVING ".implode(' AND ', $having_clauses);

        } else $this->having = "HAVING ".sprintf($having_column_pattern, trim($having_clauses));

        return $this;

    }

    /**
     * Add a column in columns' list 
     *
     * @param   \Comodojo\Database\QueryBuilder\Column $column
     *
     * @return  \Comodojo\Database\EnhancedDatabase
     */
    public function column(\Comodojo\Database\QueryBuilder\Column $column) {

        array_push($this->columns, $column->getColumnDefinition($this->model));

        return $this;

    }

    /**
     * Perform a SELECT query
     *
     * @param   int    $limit
     * @param   int    $offset
     * @param   bool   $return_raw
     *
     * @return  mixed
     *
     * @throws  \Comodojo\Exception\DatabaseException
     */
    public function get($limit = 0, $offset = 0, $return_raw = false) {

        try {
            
            $query = $this->buildQuery('GET', array(
                "limit"  =>  $limit,
                "offset" =>  $offset
            ));

            $result = $return_raw === false ? $this->query($query) : $this->rawQuery($query);

        } catch (DatabaseException $de) {
            
            throw $de;

        }

        return $result;

    }

    /**
     * Perform a INSERT query
     *
     * @param   bool   $return_raw
     *
     * @return  mixed
     *
     * @throws  \Comodojo\Exception\DatabaseException
     */
    public function store($return_raw = false) {

        try {
            
            $query = $this->buildQuery('STORE');

            $result = $return_raw === false ? $this->query($query) : $this->rawQuery($query);

        } catch (DatabaseException $de) {
            
            throw $de;

        }

        return $result;

    }

    /**
     * Perform a UPDATE query
     *
     * @param   bool   $return_raw
     *
     * @return  mixed
     *
     * @throws  \Comodojo\Exception\DatabaseException
     */
    public function update($return_raw = false) {

        try {
            
            $query = $this->buildQuery('UPDATE');

            $result = $return_raw === false ? $this->query($query) : $this->rawQuery($query);

        } catch (DatabaseException $de) {
            
            throw $de;

        }

        return $result;

    }

    /**
     * Perform a DELETE query
     *
     * @param   bool   $return_raw
     *
     * @return  mixed
     *
     * @throws  \Comodojo\Exception\DatabaseException
     */
    public function delete($return_raw = false) {

        try {
            
            $query = $this->buildQuery('DELETE');

            $result = $return_raw === false ? $this->query($query) : $this->rawQuery($query);

        } catch (DatabaseException $de) {
            
            throw $de;

        }

        return $result;

    }

    /**
     * Perform a TRUNCATE query
     *
     * @param   bool   $return_raw
     *
     * @return  mixed
     *
     * @throws  \Comodojo\Exception\DatabaseException
     */
    public function truncate($return_raw = false) {

        try {
            
            $query = $this->buildQuery('TRUNCATE');

            $result = $return_raw === false ? $this->query($query) : $this->rawQuery($query);

        } catch (DatabaseException $de) {
            
            throw $de;

        }

        return $result;

    }

    /**
     * Perform a CREATE query
     *
     * @param   bool        $if_not_exists
     * @param   string|null $engine
     * @param   string|null $charset
     * @param   string|null $collate
     * @param   bool        $return_raw
     *
     * @return  mixed
     *
     * @throws  \Comodojo\Exception\DatabaseException
     */
    public function create($if_not_exists = false, $engine = null, $charset = null, $collate = null, $return_raw = false) {

        try {
            
            $query = $this->buildQuery('CREATE', array(
                "if_not_exists" =>  $if_not_exists,
                "engine"        =>  $engine,
                "charset"       =>  $charset,
                "collate"       =>  $collate
            ));

            $result = $return_raw === false ? $this->query($query) : $this->rawQuery($query);

        } catch (DatabaseException $de) {
            
            throw $de;

        }

        return $result;

    }

    /**
     * Perform a DROP query
     *
     * @param   bool    $if_exists
     * @param   bool    $return_raw
     *
     * @return  mixed
     *
     * @throws  \Comodojo\Exception\DatabaseException
     */
    public function drop($if_exists = false, $return_raw = false) {

        try {
            
            $query = $this->buildQuery('DROP', array(
                "if_exists" =>  $if_exists
            ));

            $result = $return_raw === false ? $this->query($query) : $this->rawQuery($query);

        } catch (DatabaseException $de) {
            
            throw $de;

        }

        return $result;

    }

    /**
     * Perform a query
     *
     * @param   string  $query
     *
     * @return  QueryResult
     *
     * @throws  \Comodojo\Exception\DatabaseException
     */
    public function query($query) {

        $query = str_replace("*_DBPREFIX_*", $this->table_prefix, $query);

        return parent::query($query);

    }

    /**
     * Perform a query and return raw results
     *
     * @param   string  $query
     *
     * @return  mixed
     *
     * @throws  \Comodojo\Exception\DatabaseException
     */
    public function rawQuery($query) {

        $query = str_replace("*_DBPREFIX_*", $this->table_prefix, $query);

        return parent::rawQuery($query);

    }

    /**
     * Cleanup querybuilder
     *
     * If $deep, call also Database->clean()
     *
     * @param bool $deep
     *
     * @return \Comodojo\Database\EnhancedDatabase
     */
    public function clean($deep = false) {
        
        $this->table = null;

        $this->distinct = false;

        $this->keys = null;

        $this->keys_array = array();

        $this->values = null;

        $this->values_array = array();

        $this->where = null;

        $this->join = null;

        $this->using = null;

        $this->on = null;

        $this->order_by = null;

        $this->group_by = null;

        $this->having = null;

        $this->columns = array();

        if ( filter_var($deep, FILTER_VALIDATE_BOOLEAN) ) {

            $this->table_prefix = null;

            parent::clean();

        }

        return $this;

    }

    /**
     * Convert a date string into default selected database's format
     *
     * @param  string  $dateString
     *
     * @return string
     */
    public function convertDate($dateString) {

        $dateReal = strtotime($dateString);

        switch ( $this->model ) {

            case 'ORACLE_PDO':

                $dateObject = date("d-M-y", $dateReal);

                break;

            case 'SQLITE_PDO':

                $dateObject = date("c", $dateReal);

                break;
                
            case 'MYSQLI':
            case 'MYSQL_PDO':
            case 'DBLIB_PDO':
            case 'POSTGRESQL':
            case 'DB2':
            default:
                
                $dateObject = date("Y-m-d", $dateReal);

                break;

        }

        return $dateObject;

    }

    /**
     * Convert a time string into default selected database's format
     *
     * @param  string  $timeString
     *
     * @return string
     */
    public function convertTime($timeString) {

        return ltrim($timeString, 'T');
        
    }

    /**
     * Keys' composer
     *
     * @param   string  $key
     *
     * @return  string
     *
     * @throws  \Comodojo\Exception\DatabaseException
     */
    private function composeKey($key) {

        $key_pattern = in_array($this->model, Array('MYSQLI', 'MYSQL_PDO')) ? "`%s`" : "%s";

        if ( !is_scalar($key) ) throw new DatabaseException("Invalid key");
        
        $key = trim($key);

        // process alias notation (=>)

        $alias_array = explode('=>', $key);

        $alias_array_size = sizeof($alias_array);

        if ( $alias_array_size == 1 ) {

            //no alias, keep key definition
            $alias = '';

        } else if ( $alias_array_size == 2 ) {

            //alias defined, splitting keys

            $key = $alias_array[0];

            $alias = ' AS '.$alias_array[1];

        } else throw new DatabaseException("Invalid key definition");
        
        // process operation notation (::)

        $operation_array = explode('::', $key);

        $operation_array_size = sizeof($operation_array);

        if ( $operation_array_size == 1 ) {

            //no operation, keep key definition
            $value = $operation_array[0];

            $key = $value == '*' ? $value : sprintf($key_pattern, $value);

        } else {

            $operation = $operation_array[0];

            unset($operation_array[0]);

            array_walk($operation_array, function(&$value, $index) use ($key_pattern) {

                $value = $value == '*' ? $value : sprintf($key_pattern, $value);

            });

            $key = $operation.'('.implode(',', $operation_array).')';

        }

        return $key.$alias;

    }

    /**
     * Values' composer
     *
     * @param   string  $value
     *
     * @return  string
     *
     * @throws  \Comodojo\Exception\DatabaseException
     */
    private function composeValue($value) {

        $value_string_pattern = "'%s'";

        $value_null_pattern = 'null';

        $processed_value = null;

        if ( is_bool($value) === true ) {

            switch ( $this->model ) {

                case 'MYSQLI':
                case 'MYSQL_PDO':
                case 'POSTGRESQL':
                case 'DB2':

                    $processed_value = $value ? 'TRUE' : 'FALSE';

                    break;

                case 'DBLIB_PDO':
                case 'ORACLE_PDO':
                case 'SQLITE_PDO':
                default:
                    
                    $processed_value = !$value ? 0 : 1;
                    
                    break;

            }

        } elseif ( is_numeric($value) ) $processed_value = $value;

        elseif ( is_null($value) ) $processed_value = $value_null_pattern;

        else {

            switch ( $this->model ) {
                
                case 'MYSQLI':

                    $processed_value = sprintf($value_string_pattern, $this->dbh->escape_string($value));

                    break;
                
                case 'POSTGRESQL':

                    $processed_value = sprintf($value_string_pattern, pg_escape_string($value));

                    break;
                
                case 'DB2':

                    $processed_value = sprintf($value_string_pattern, db2_escape_string($value));

                    break;
                
                case 'MYSQL_PDO':
                case 'ORACLE_PDO':
                case 'SQLITE_PDO':
                case 'DBLIB_PDO':

                    $processed_value = $this->dbh->quote($value);

                    $processed_value = $processed_value === false ? sprintf($value_string_pattern, $value) : $processed_value;

                    break;
                
                default:

                    $processed_value = sprintf($value_string_pattern, $value);

                    break;

            }

        }

        return $processed_value;

    }

    /**
     * Where clauses' composer
     *
     * @param   mixed   $column
     * @param   string  $operator
     * @param   mixed   $value
     *
     * @return  string
     */
    private function composeWhereCondition($column, $operator, $value) {

        $to_return = null;

        $operator = strtoupper($operator);

        if ( is_array($column) AND is_array($value) ) {

            $clause_pattern = "(%s %s %s)";

            if ( !in_array($operator, Array('AND', 'OR')) ) throw new DatabaseException('Invalid syntax for a where clause');
            
            if ( sizeof($column) != 3 || sizeof($value) != 3 ) throw new DatabaseException('Invalid syntax for a where clause');

            try {

                $processed_column = $this->composeWhereCondition($column[0], $column[1], $column[2]);

                $processed_value = $this->composeWhereCondition($value[0], $value[1], $value[2]);

            } catch (DatabaseException $e) {

                throw $e;

            }

            $to_return = sprintf($clause_pattern, $processed_column, $operator, $processed_value);

        } elseif ( is_scalar($column) && is_array($value) ) {

            switch ( $operator ) {

                case 'IN':

                    $clause_pattern = in_array($this->model, Array('MYSQLI', 'MYSQL_PDO')) ? "`%s` IN (%s)" : "%s IN (%s)";

                    array_walk($value, function(&$keyvalue, $key) {

                        if ( is_bool($keyvalue) === true ) {

                            switch ( $this->model ) {

                                case 'MYSQLI':
                                case 'MYSQL_PDO':
                                case 'POSTGRESQL':
                                case 'DB2':

                                    $keyvalue = $keyvalue ? 'TRUE' : 'FALSE';

                                    break;

                                case 'DBLIB_PDO':
                                case 'ORACLE_PDO':
                                case 'SQLITE_PDO':
                                default:

                                    $keyvalue = $keyvalue ? 1 : 0;

                                    break;

                            }

                        } elseif ( is_numeric($keyvalue) ) $keyvalue = $keyvalue;

                        elseif ( is_null($keyvalue) ) $keyvalue = "NULL";

                        else $keyvalue = "'".$keyvalue."'";

                    });

                    $processed_value = implode(",", $value);

                    $to_return = sprintf($clause_pattern, $column, $processed_value);

                    break;

                case 'BETWEEN':

                    $clause_pattern = in_array($this->model, Array('MYSQLI', 'MYSQL_PDO')) ? "`%s` BETWEEN %s AND %s" : "%s BETWEEN %s AND %s";

                    $to_return = sprintf($clause_pattern, $column, intval($value[0]), intval($value[1]));

                    break;

                case 'NOT IN':
                case 'NOTIN':

                    $clause_pattern = in_array($this->model, Array('MYSQLI', 'MYSQL_PDO')) ? "`%s` NOT IN (%s)" : "%s NOT IN (%s)";

                    $processed_value = "'".implode("','", $value)."'";

                    $to_return = sprintf($clause_pattern, $column, $processed_value);

                    break;

                case 'NOT BETWEEN':
                case 'NOTBETWEEN':

                    $clause_pattern = in_array($this->model, Array('MYSQLI', 'MYSQL_PDO')) ? "`%s` NOT BETWEEN %s AND %s" : "%s NOT BETWEEN %s AND %s";

                    $to_return = sprintf($clause_pattern, $column, intval($value[0]), intval($value[1]));

                    break;

                default:
                    
                    throw new DatabaseException('Invalid syntax for a where clause');

                    break;

            }

        } elseif ( is_scalar($column) && (is_scalar($value) || is_null($value)) ) {
            
            $clause_pattern = in_array($this->model, Array('MYSQLI', 'MYSQL_PDO')) ? "`%s` %s %s" : "%s %s %s";

            if ( $operator == 'IS' || $operator == 'IS NOT' || $operator == 'ISNOT' ) {

                $processed_column = $column;

                $processed_operator = $operator == 'IS' ? $operator : 'IS NOT';

                $processed_value = (is_null($value) || $value == 'NULL') ? 'NULL' : 'NOT NULL';

            } elseif ( $operator == 'LIKE' || $operator == 'NOT LIKE' || $operator == 'NOTLIKE' ) {

                $processed_column = $column;

                $processed_operator = $operator == 'LIKE' ? $operator : 'NOT LIKE';

                $processed_value = "'".$value."'";

            } else {

                $processed_column = $column;

                $processed_operator = $operator;

                if ( is_bool($value) === true ) {

                    switch ( $this->model ) {

                        case 'MYSQLI':
                        case 'MYSQL_PDO':
                        case 'POSTGRESQL':
                        case 'DB2':

                            $processed_value = $value ? 'TRUE' : 'FALSE';

                            break;

                        case 'DBLIB_PDO':
                        case 'ORACLE_PDO':
                        case 'SQLITE_PDO':
                        default:

                            $processed_value = $value ? 1 : 0;

                            break;

                    }

                } elseif ( is_numeric($value) ) $processed_value = $value;

                elseif ( is_null($value) ) $processed_value = "NULL";

                else $processed_value = "'".$value."'";

            }

            $to_return = sprintf($clause_pattern, $processed_column, $processed_operator, $processed_value);            

        } else throw new DatabaseException('Invalid syntax for a where clause');

        return $to_return;

    }

    /**
     * ON clauses' composer
     *
     * @param   string  $first_column
     * @param   string  $operator
     * @param   string  $second_column
     *
     * @return  string
     *
     * @throws  \Comodojo\Exception\DatabaseException
     */
    private function composeOnClause($first_column, $operator, $second_column) {

        $valid_operators = Array('=', '!=', '>', '>=', '<', '<=', '<>');

        $on_pattern = "%s%s%s";

        if ( !in_array($operator, $valid_operators) ) throw new DatabaseException('Invalid syntax for a on clause');
        
        return sprintf($on_pattern, $first_column, $operator, $second_column);

    }

    /**
     * Build the query
     *
     * @param   string  $query
     * @param   array   $parameters
     *
     * @return  string
     *
     * @throws  \Comodojo\Exception\DatabaseException
     */
    private function buildQuery($query, $parameters = array()) {

        if ( empty($query) ) throw new DatabaseException("Invalid query type");

        $query = strtoupper($query);

        if ( !in_array($query, self::$supported_query_types) ) throw new DatabaseException('Unsupported query type for buidler');
        
        try {
        
            switch ( $query ) {

                case 'GET':
                    
                    $builder = new QueryGet($this->model);

                    if ( array_key_exists('limit', $parameters) ) $builder->limit($parameters['limit']);
                    if ( array_key_exists('offset', $parameters) ) $builder->offset($parameters['offset']); 
                    
                    $builder->table($this->table)
                        ->keys($this->keys)
                        ->distinct($this->distinct)
                        ->join($this->join)
                        ->using($this->using)
                        ->on($this->on)
                        ->where($this->where)
                        ->groupBy($this->group_by)
                        ->having($this->having)
                        ->orderBy($this->order_by);

                    $composed_query = $builder->getQuery();

                    break;

                case 'STORE':
                    
                    $builder = new QueryStore($this->model);

                    $builder->table($this->table)
                        ->keys($this->keys)
                        ->values($this->values)
                        ->keysArray($this->keys_array)
                        ->valuesArray($this->values_array);

                    $composed_query = $builder->getQuery();

                    break;

                case 'UPDATE':
                    
                    $builder = new QueryUpdate($this->model);

                    $builder->table($this->table)
                        ->where($this->where)
                        ->keysArray($this->keys_array)
                        ->valuesArray($this->values_array);

                    $composed_query = $builder->getQuery();

                    break;
                
                case 'DELETE':
                    
                    $builder = new QueryDelete($this->model);

                    $builder->table($this->table)->where($this->where);

                    $composed_query = $builder->getQuery();

                    break;

                case 'TRUNCATE':
                    
                    $builder = new QueryTruncate($this->model);

                    $builder->table($this->table);

                    $composed_query = $builder->getQuery();

                    break;

                case 'CREATE':

                    $builder = new QueryCreate($this->model);

                    if ( array_key_exists('if_not_exists', $parameters) ) $builder->ifNotExists($parameters['if_not_exists']);
                    if ( array_key_exists('engine', $parameters) ) $builder->engine($parameters['engine']);
                    if ( array_key_exists('charset', $parameters) ) $builder->charset($parameters['charset']);
                    if ( array_key_exists('collate', $parameters) ) $builder->collate($parameters['collate']);

                    $builder->table($this->table)->columns($this->columns);

                    $composed_query = $builder->getQuery();

                    break;

                case 'DROP':

                    $builder = new QueryDrop($this->model);

                    if ( array_key_exists('if_exists', $parameters) ) $builder->ifExists($parameters['if_exists']);
                    
                    $builder->table($this->table);

                    $composed_query = $builder->getQuery();

                    break;

                default:

                    throw new DatabaseException('Invalid query for querybuilder');

                    break;
            }

        } catch (DatabaseException $de) {
            
            throw $de;

        }

        if ( $this->auto_clean ) $this->clean();

        return $composed_query;

    } 

}
