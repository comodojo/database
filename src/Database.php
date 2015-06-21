<?php namespace Comodojo\Database;

use \Comodojo\Exception\DatabaseException;
use \Exception;

/**
 * Database connect/query class for comodojo
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
     * Destructor
     *
     * Unset (disconnect) database handler
     */
    final public function __destruct() {

        $this->disconnect();

    }

    /**
     * Get defined host
     *
     * @return string
     */
    final public function getHost() {

        return $this->host;

    }

    /**
     * Get defined port
     *
     * @return int
     */
    final public function getPort() {

        return $this->port;

    }

    /**
     * Get defined name
     *
     * @return string
     */
    final public function getName() {

        return $this->name;

    }

    /**
     * Get defined user
     *
     * @return string
     */
    final public function getUser() {

        return $this->user;

    }

    /**
     * Get defined pass
     *
     * @return string
     */
    final public function getPass() {

        return $this->pass;

    }

    /**
     * Get defined fetch
     *
     * @return string
     */
    final public function getFetch() {

        return $this->fetch;

    }

    /**
     * Get defined model
     *
     * @return string
     */
    final public function getModel() {

        return $this->model;

    }

    /**
     * Set fetch mode
     *
     * @param   string  $mode   Fetch mode (ASSOC, NUM, BOTH)
     *
     * @return  Object          $this
     */
    public function fetch($mode) {

        if ( in_array(strtoupper($mode), Array('ASSOC','NUM','BOTH')) ) {

            $this->fetch = strtoupper($mode);

        }
        else throw new DatabaseException('Invalid data fetch method');

        return $this;

    }

    /**
     * Shot a query to database and build a result set (QueryResult object).
     *
     * @param   string  $query
     *
     * @return  Object  \Comodojo\Database\QueryResult
     * 
     * @throws  \Comodojo\Exception\DatabaseException
     */
    public function query($query) {

        try {

            $result = $this->rawQuery($query);
            
            $return = new QueryResult($this->dbh, $this->model, $this->fetch, $result);

        } catch (DatabaseException $e) {
            
            throw $e;

        }
        
        return $return;

    }
    
    /**
     * Shot a query to database and return raw data
     *
     * @param   string  $query
     *
     * @return  mixed
     * 
     * @throws  \Comodojo\Exception\DatabaseException
     */
    public function rawQuery($query) {
        
        switch ($this->model) {

            case ("MYSQLI"):
                
                $response = $this->dbh->query($query);

                if (!$response) throw new DatabaseException($this->dbh->error, $this->dbh->errno);

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

                    throw new DatabaseException($e->getMessage(), (int)$e->getCode());

                }

                break;

            case ("DB2"):

                $response = db2_exec($this->dbh,$query);

                if (!$response)  throw new DatabaseException(db2_stmt_error());
                
                break;

            case ("POSTGRESQL"):

                $response = pg_query($this->dbh,$query);

                if (!$response) throw new DatabaseException(pg_last_error());

                break;

        }

        return $response;
        
    }

    /**
     * Cleanup database extra parameters
     *
     * It resets:
     *
     * - fetch mode to ASSOC
     *
     * @return  Object  $this
     */
    public function clean() {

        $this->fetch = 'ASSOC';

        return $this;

    }

    /**
     * Get database handler
     *
     * @return  Object
     */
    final public function getHandler() {

        return $this->dbh;

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

                if ($this->dbh->connect_error) throw new DatabaseException($this->dbh->connect_error, $this->dbh->connect_errno);

                break;

            case ("MYSQL_PDO"):

                if ( !in_array('mysql', \PDO::getAvailableDrivers()) ) throw new DatabaseException('Unsupported database model - '.$this->model);

                $dsn="mysql:host=".$this->host.";port=".$this->port .";dbname=".$this->name;
                
                try {

                    $this->dbh = new \PDO($dsn, $this->user, $this->pass, array(\PDO::ATTR_ERRMODE => \PDO::ERRMODE_EXCEPTION));

                }
                catch (\PDOException $e) {
                    
                    throw new DatabaseException($e->getMessage(), $e->getCode());
                    
                }

                break;

            case ("ORACLE_PDO"):

                if ( !in_array('oci', \PDO::getAvailableDrivers()) ) throw new DatabaseException('Unsupported database model - '.$this->model);

                $dsn="oci:dbname=".$this->host.":".$this->port."/".$this->name;
                
                try {
                    
                    $this->dbh = new \PDO($dsn, $this->user, $this->pass, array(\PDO::ATTR_ERRMODE => \PDO::ERRMODE_EXCEPTION));
                    
                }
                catch (\PDOException $e) {
                    
                    throw new DatabaseException($e->getMessage(), $e->getCode());
                    
                }

                break;

            case ("SQLITE_PDO"):
            
                if ( !in_array('sqlite', \PDO::getAvailableDrivers()) ) throw new DatabaseException('Unsupported database model - '.$this->model);

                $dsn="sqlite:".$this->name;

                try {
                    
                    $this->dbh = new \PDO($dsn, $this->user, $this->pass, array(\PDO::ATTR_ERRMODE => \PDO::ERRMODE_EXCEPTION));
                    
                }
                catch (\PDOException $e) {
                    
                    throw new DatabaseException($e->getMessage(), $e->getCode());
                    
                }

                break;

            case ("DB2"):

                if ( !function_exists('db2_pconnect') ) throw new DatabaseException('Unsupported database model - '.$this->model);

                $dsn  = "ibm:DRIVER={IBM DB2 ODBC DRIVER};DATABASE=".$this->name;
                $dsn .= ";HOSTNAME=".$this->host.";PORT=".$this->port.";PROTOCOL=TCPIP;UID=".$this->user.";PWD=".$this->pass.";";

                $this->dbh = db2_pconnect($dsn,$this->user,$this->pass);
                
                if (!$this->dbh){
                    
                    throw new DatabaseException(db2_conn_errormsg());
                    
                }

                break;

            case ("DBLIB_PDO"):

                if ( !in_array('dblib', \PDO::getAvailableDrivers()) ) throw new DatabaseException('Unsupported database model - '.$this->model);

                $dsn = "dblib:host=".$this->host.":".$this->port.";dbname=".$this->name;
            
                try {
                    
                    $this->dbh = new \PDO($dsn, $this->user, $this->pass, array(\PDO::ATTR_ERRMODE => \PDO::ERRMODE_EXCEPTION));
                    
                }
                catch (\PDOException $e) {
                    
                    throw new DatabaseException($e->getMessage(), $e->getCode());
                    
                }

                break;

            case ("POSTGRESQL"):

                if ( !function_exists('pg_connect') ) throw new DatabaseException('Unsupported database model - '.$this->model);

                $dsn = "host=".$this->host." port=".$this->port." dbname=".$this->name." user=".$this->user." password=".$this->pass;

                $this->dbh = @pg_connect($dsn);
                
                if ($this->dbh == false) {
                    
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
                
                if ($this->dbh !== false) @pg_close($this->dbh);
                
                $this->dbh = null;
                
                break;
            
        }

    }

    

}
