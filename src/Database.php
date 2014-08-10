<?php namespace Comodojo\Database;

use \Comodojo\Exception\DatabaseException;
use \Exception;

/**
 * Database connect/query class for comodojo
 * 
 * @package     Comodojo Spare Parts
 * @author      Marco Giovinazzi <info@comodojo.org>
 * @license     GPL-3.0+
 *
 * LICENSE:
 * 
 * This program is free software: you can redistribute it and/or modify
 * it under the terms of the GNU Affero General Public License as
 * published by the Free Software Foundation, either version 3 of the
 * License, or (at your option) any later version.
 *
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU Affero General Public License for more details.
 *
 * You should have received a copy of the GNU Affero General Public License
 * along with this program. If not, see <http://www.gnu.org/licenses/>.
 */

class Database {

    /**
     * Host to connect to
     * 
     * @var string
     */
    private $host = null;

    /**
     * Database port
     * 
     * @var int
     */
    private $port = null;

    /**
     * Database name
     * 
     * @var string
     */
    private $name = null;

    /**
     * User name
     * 
     * @var string
     */
    private $user = null;

    /**
     * User password
     * 
     * @var string
     */
    private $pass = null;

    /**
     * Transaction id (if any)
     * 
     * @var integer
     */
    private $id = false;

    /**
     * Affected rows
     * 
     * @var integer
     */
    private $rows = false;

    /**
     * Fetch mode (ASSOC, NUM, BOTH)
     * 
     * @var string
     */
    private $fetch = "ASSOC";

    /**
     * Supported database data model
     * 
     * @var array
     */
    private $supported_models = Array("MYSQLI","MYSQL_PDO","ORACLE_PDO","SQLITE_PDO","DBLIB_PDO","DB2","POSTGRESQL");

    /**
     * Database Handler
     * 
     * @var Object
     */
    protected $dbh = false;

    /**
     * Database data model
     * 
     * @var string
     */
    protected $model = null;

    /**
     * Constructor
     *
     * It validate database parameters and try to establish a connection
     *
     * @param   string  $model  Database data model
     * @param   string  $host   Host to connect to
     * @param   int     $port   Database port
     * @param   string  $name   Database name
     * @param   string  $user   User name
     * @param   string  $pass   User password
     */
    final public function __construct($model, $host, $port, $name, $user, $pass=null) {

        $this->model = in_array(strtoupper($model), $this->supported_models) ? strtoupper($model) : null;
        $this->host = is_null($host) ? null : $host;
        $this->port = filter_var($port, FILTER_VALIDATE_INT, array(
            "options" => array(
                "min_range" => 1,
                "max_range" => 65535
                )
            )
        );
        $this->name = is_null($name) ? null : $name;
        $this->user = is_string($user) ? $user : null;

        if ( empty($this->model)   ) throw new DatabaseException('Invalid database model');
        if ( empty($this->host)    ) throw new DatabaseException('Invalid database host');
        if ( $this->port == false  ) throw new DatabaseException('Invalid database port');
        if ( empty($this->name)    ) throw new DatabaseException('Invalid database name');
        if ( empty($this->user)    ) throw new DatabaseException('Invalid database user');

        $this->pass = is_string($pass) ? $pass : null;

        try {

            $this->connect();

        } catch (DatabaseException $ce) {

            throw $ce;

        } catch (Exception $e) {

            throw $e;

        }

    }

    /**
     * Destructure
     *
     * It's only mission is to unset (disconnect) database
     */
    final public function __destruct() {

        $this->disconnect();

    }

    /**
     * Set fetch mode
     *
     * @param   string  $mode   Fetch mode (ASSOC, NUM, BOTH)
     *
     * @return  Object          $this
     */
    public function fetch($mode) {

        if ( in_array(strtoupper($fetch), Array('ASSOC','NUM','BOTH')) ) {

            $this->fetch = strtoupper($fetch);

        }
        else throw new DatabaseException('Invalid data fetch method');

        return $this;

    }

    /**
     * Set if database should return a transaction id
     *
     * @param   bool    $enabled
     *
     * @return  Object  $this
     */
    public function id($enabled=true) {

        $this->id = filter_var($enabled, FILTER_VALIDATE_BOOLEAN);

        return $this;

    }

    /**
     * Shot a query to database
     *
     * It sends $query to database handler and build a result set. If $return_raw is
     * set to true, method resultsToArray() will not be invoked and this will return
     * query's result as it is. If false, result will be returned as a standard array
     * composed by:
     *
     * - "data": array of fetched data
     * - "length": result length
     * - "id": transaction id (if any)
     * - "affected_rows": affected rows (if any)
     *
     * @param   string  $query
     * @param   bool    $return_raw
     *
     * @return  Object  $this
     */
    public function query($query, $return_raw=false) {

        switch ($this->model) {

            case ("MYSQLI"):
                
                $response = $this->dbh->query($query);

                if (!$response) {
                    
                    throw new DatabaseException($this->dbh->error, $this->dbh->errno);

                }

                break;

            case ("MYSQL_PDO"):
            case ("ORACLE_PDO"):
            case ("SQLITE_PDO"):
            case ("DBLIB_PDO"):

                try {

                    $response = $this->dbh->prepare($query);
                    $response->execute();

                }
                catch (\PDOException $e) {

                    $error = $dbHandler->errorInfo();
                    throw new DatabaseException($error[1], $error[2]);

                }

                break;

            case ("DB2"):

                $response = db2_exec($this->dbh,$query);

                if (!$response) {
                    
                    throw new DatabaseException(db2_stmt_error());

                }
                
                break;

            case ("POSTGRESQL"):

                $response = pg_query($this->dbh,$query);

                if (!$response) {

                    throw new DatabaseException(pg_last_error());

                }

                break;

        }

        if ($return_raw) $return = $response;

        else {

            try {

                $return = $this->resultsToArray($response);

            } catch (DatabaseException $e) {
                
                throw $e;

            }

        }

        return $return;

    }

    /**
     * Cleanup database extra parameters
     *
     * It reset:
     *
     * - fetch mode to ASSOC
     * - return id to FALSE
     *
     * @return  Object  $this
     */
    public function clean() {

        $this->fetch = 'ASSOC';
        
        $this->id = false;

        return $this;

    }

    /**
     * Get transaction id (if any)
     *
     * @return  mixed   Integer if transaction id was populated, boolean false if not     
     */
    final public function getId() {

        return $this->id;

    }

   /**
     * Get affected rows (if any)
     *
     * @return  mixed   Integer if affected rows was populated, boolean false if not     
     */
    final public function getAffectedRows() {

        return $this->rows;

    }

    /**
     * Connecto to database
     *
     */
    private function connect() {

        switch ($this->model) {

            case ("MYSQLI"):
                
                if ( !class_exists('mysqli') ) throw new DatabaseException('Unsupported database model - '.$this->model);

                $this->dbh = new \mysqli($this->host, $this->user, $this->pass, $this->name, $this->port);

                if ($this->dbh->connect_error) {

                    throw new DatabaseException($this->dbh->connect_error, $this->dbh->connect_errno);

                }

                break;

            case ("MYSQL_PDO"):

                if ( !in_array('mysql', \PDO::getAvailableDrivers()) ) throw new DatabaseException('Unsupported database model - '.$this->model);

                $dsn="mysql:host=".$this->host.";port=".$this->port .";dbname=".$this->name;
                
                try {
                    $this->dbh = new \PDO($dsn,$this->user,$this->pass);
                }
                catch (\PDOException $e) {
                    throw new DatabaseException($e->getMessage(), $e->getCode());
                }

                break;

            case ("ORACLE_PDO"):

                if ( !in_array('oci', \PDO::getAvailableDrivers()) ) throw new DatabaseException('Unsupported database model - '.$this->model);

                $dsn="oci:dbname=".$this->host.":".$this->port."/".$this->name;
                
                try {
                    $this->dbh = new \PDO($dsn,$this->user,$this->pass);
                }
                catch (\PDOException $e) {
                    throw new DatabaseException($e->getMessage(), $e->getCode());
                }

                break;

            case ("SQLITE_PDO"):
            
                if ( !in_array('sqlite', \PDO::getAvailableDrivers()) ) throw new DatabaseException('Unsupported database model - '.$this->model);

                $dsn="sqlite:".$this->name;

                try {
                    $this->dbh = new \PDO($dsn);
                }
                catch (\PDOException $e) {
                    throw new DatabaseException($e->getMessage(), $e->getCode());
                }

                break;

            case ("DB2"):

                if ( !function_exists('db2_pconnect') ) throw new DatabaseException('Unsupported database model - '.$this->model);

                $dsn="ibm:DRIVER={IBM DB2 ODBC DRIVER};DATABASE=".$this->name.";HOSTNAME=".$this->host.";PORT=".$this->port.";PROTOCOL=TCPIP;UID=".$this->user.";PWD=".$this->pass.";";

                $this->dbh = db2_pconnect($dsn,$this->user,$this->pass);
                if (!$this->dbh){
                    throw new DatabaseException(db2_conn_errormsg());
                }

                break;

            case ("DBLIB_PDO"):

                if ( !in_array('dblib', \PDO::getAvailableDrivers()) ) throw new DatabaseException('Unsupported database model - '.$this->model);

                $dsn = "dblib:host=".$this->host.":".$this->port.";dbname=".$this->name;
            
                try {
                    $this->dbh = new \PDO($dsn,$this->user,$this->pass);
                }
                catch (\PDOException $e) {
                    throw new DatabaseException($e->getMessage(), $e->getCode());
                }

                break;

            case ("POSTGRESQL"):

                if ( !function_exists('pg_connect') ) throw new DatabaseException('Unsupported database model - '.$this->model);

                $dsn = "host=".$this->host." port=".$this->port." dbname=".$this->name." user=".$this->user." password=".$this->pass;

                $this->dbh = @pg_connect($dsn);
                if (!$this->dbh) {
                    throw new DatabaseException(pg_last_error());
                }

                break;

        }

    }

    /**
     * Disconnect from database
     *
     */
    private function disconnect() {

        switch($this->model) {
            
            case ("MYSQLI"):
                if ($this->dbh !== false) $this->dbh->close();
                break;
            
            case ("MYSQL_PDO"):
            case ("ORACLE_PDO"):
            case ("SQLITE_PDO"):
            case ("DBLIB_PDO"):
                $this->dbh = null;
                break;
            
            case ("DB2"):
                if ($this->dbh !== false) db2_close($this->dbh);
                break;
            
            case ("POSTGRESQL"):
                if ($this->dbh !== false) pg_close($this->dbh);
                $this->dbh = null;
                break;
            
        }

    }

    /**
     * Transform database raw result in a standard array
     *
     * @param   mixed   $data   Query result as returned from database handler
     *
     * @return  array
     */
    private function resultsToArray($data) {

        $result = Array();
        $id     = false;
        $length = 0;
        $rows   = 0;

        $iterator = 0;

        switch ($this->model) {

            case ("MYSQLI"):
                
                if ( !is_object($data) OR !is_a($data, 'mysqli_result') ) throw new DatabaseException('Invalid result data for model '.$this->model);

                switch ($this->fetch) {
                    case 'NUM':     $fetch = MYSQLI_NUM;    break;
                    case 'ASSOC':   $fetch = MYSQLI_ASSOC;  break;
                    default:        $fetch = MYSQLI_BOTH;   break;
                }
                
                                    $length = $data->num_rows;
                if ($this->id)      $id     = $this->dbh->insert_id;
                if ($this->rows)    $rows   = $this->dbh->affected_rows;

                while($iterator < $length) {
                    $result[$iterator] = $data->fetch_array($fetch);
                    $iterator++;
                }
                $data->free();

                break;

            case ("MYSQL_PDO"):
            case ("ORACLE_PDO"):
            case ("SQLITE_PDO"):
            case ("DBLIB_PDO"):

                if ( !is_object($data) ) throw new DatabaseException('Invalid result data for model '.$this->model);

                switch ($this->fetch) {
                    case 'NUM':     $fetch = \PDO::FETCH_NUM;    break;
                    case 'ASSOC':   $fetch = \PDO::FETCH_ASSOC;  break;
                    default:        $fetch = \PDO::FETCH_BOTH;   break;
                }

                $result = $data->fetchAll($fetch);

                                    $length = sizeof($result);
                if ($this->id)      $id     = $this->dbh->lastInsertId();
                if ($this->rows)    $rows   = $data->rowCount();

                break;

            case ("DB2"):

                if ( !is_resource($data) OR @get_resource_type($data) != "DB2 Statement" ) throw new DatabaseException('Invalid result data for model '.$this->model);

                                    $length = db2_num_fields($data);
                if ($this->id)      $id     = db2_last_insert_id($this->dbh);
                if ($this->rows)    $rows   = db2_num_rows($data);

                switch ($this->fetch) {
                    case 'NUM':     while ($row = db2_fetch_row($data)) array_push($result, $row);      break;
                    case 'ASSOC':   while ($row = db2_fetch_assoc($data)) array_push($result, $row);    break;
                    default:        while ($row = db2_fetch_both($data)) array_push($result, $row);     break;
                }

                break;

            case ("POSTGRESQL"):

                if ( !is_resource($data) OR @get_resource_type($data) != "pgsql result" ) throw new DatabaseException('Invalid result data for model '.$this->model);
                
                                    $length = pg_num_rows($data);
                if ($this->id)      $id     = pg_last_oid($data);
                if ($this->rows)    $rows   = pg_affected_rows($data);

                while($iterator < $length) {
                    switch ($this->fetch) {
                        case 'NUM':     $result[$iterator] = pg_fetch_array($data); break;
                        case 'ASSOC':   $result[$iterator] = pg_fetch_assoc($data); break;
                        default:        $result[$iterator] = pg_fetch_all($data);   break;
                    }
                    $iterator++;
                }

                break;

        }

        return Array(
            "data"          =>  $result,
            "length"        =>  $length,
            "id"            =>  $id,
            "affected_rows" =>  $rows
        );

    }

}