<?php namespace Comodojo\Database;

use \Comodojo\Exception\DatabaseException;
use \Exception;

/**
 * Cross database result object model
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

class QueryResult {
    
    /**
     * Result's raw data
     * 
     * @var mixed
     */
    private $raw_data = null;

    /**
     * Result's data
     * 
     * @var array
     */
    private $data = null;
    
    /**
     * Handler from Database class
     * 
     * @var Object
     */
    private $handler = null;
    
    /**
     * Model from Database class
     * 
     * @var string
     */
    private $model = null;
    
    /**
     * Fetch mode from Database class
     * 
     * @var string
     */
    private $fetch = 'ASSOC';

    /**
     * Build a result set
     *
     * @throws   \Comodojo\Exception\DatabaseException
     * @param string $model
     * @param string $fetch
     */
    public function __construct($handler, $model, $fetch, $data) {
        
        $this->setHandler($handler);
        
        $this->setModel($model);
        
        $this->setFetch($fetch);
        
        try {
            
            $this->setRawData($data);
            
        } catch (DatabaseException $de) {
            
            throw $de;
            
        }
        
    }

    /**
     * Get database handler
     *
     * @return   Object  $handler
     */
    final public function getHandler() {
        
        return $this->handler;
        
    }
    
    /**
     * Get database model
     *
     * @return  string
     */
    final public function getModel() {
        
        return $this->model;
        
    }
    
    /**
     * Get database fetch mode
     *
     * @return  string
     */
    final public function getFetch() {
        
        return $this->fetch;
        
    }
    
    /**
     * Get raw data from query's result
     *
     * @return  mixed
     */
    final public function getRawData() {
        
        return $this->raw_data;
        
    }
    
    /**
     * Get result's data as an array (indexed according to fetch method selected)
     *
     * @return  array
     */
    public function getData() {

        if ( !is_null($this->data) ) return $this->data;
        
        $result = array();
        
        $iterator = 0;

        switch ( $this->model ) {

            case ("MYSQLI"):

                switch ( $this->fetch ) {
                    case 'NUM':     $fetch = MYSQLI_NUM; break;
                    case 'ASSOC':   $fetch = MYSQLI_ASSOC; break;
                    default:        $fetch = MYSQLI_BOTH; break;
                }
                
                if ( is_bool($this->raw_data) ) {

                    $result = null;

                } else {

                    while ( $iterator < $this->raw_data->num_rows ) {
                    
                        $result[$iterator] = $this->raw_data->fetch_array($fetch);
                        
                        $iterator++;
                        
                    }

                }

                //if ( is_object($this->raw_data) ) $this->raw_data->free();

                break;

            case ("MYSQL_PDO"):
            case ("SQLITE_PDO"):
            case ("ORACLE_PDO"):
            case ("DBLIB_PDO"):
            
                switch ( $this->fetch ) {
                    case 'NUM':     $fetch = \PDO::FETCH_NUM; break;
                    case 'ASSOC':   $fetch = \PDO::FETCH_ASSOC; break;
                    default:        $fetch = \PDO::FETCH_BOTH; break;
                }

                $result = $this->raw_data->fetchAll($fetch);

                break;
            
            case ("DB2"):

                switch ( $this->fetch ) {
                    
                    case 'NUM':
                        
                        while ( $row = db2_fetch_row($this->raw_data) ) array_push($result, $row);
                        
                        break;
                    
                    case 'ASSOC':
                        
                        while ( $row = db2_fetch_assoc($this->raw_data) ) array_push($result, $row);
                        
                        break;
                    
                    default:
                        
                        while ( $row = db2_fetch_both($this->raw_data) ) array_push($result, $row);
                        
                        break;
                
                }

                break;

            case ("POSTGRESQL"):

                while ( $iterator < pg_num_rows($this->raw_data) ) {
                    
                    switch ( $this->fetch ) {
                        
                        case 'NUM':
                            
                            $result[$iterator] = pg_fetch_array($this->raw_data);
                            
                            break;
                        
                        case 'ASSOC':
                            
                            $result[$iterator] = pg_fetch_assoc($this->raw_data);
                            
                            break;
                        
                        default:
                            
                            $result[$iterator] = pg_fetch_all($this->raw_data);
                            
                            break;
                            
                    }
                    
                    $iterator++;
                    
                }

                break;

        }
        
        // current data to buffer;
        $this->data = $result;

        return $result;
        
    }
    
    /**
     * Get length of result returned from query
     *
     * @return  int
     */
    public function getLength() {
        
        switch ( $this->model ) {

            case ("MYSQLI"):
                
                $return = $this->raw_data->num_rows;
                
                break;

            case ("MYSQL_PDO"):
            case ("SQLITE_PDO"):
            case ("ORACLE_PDO"):
            case ("DBLIB_PDO"):

                if ( is_null($this->data) ) $this->getData();

                $return = count($this->data);

                break;

            case ("DB2"):

                $return = db2_num_fields($this->raw_data);
                
                break;

            case ("POSTGRESQL"):

                $return = pg_num_rows($this->raw_data);
                
                break;

        }

        return $return;
        
    }
    
    /**
     * Get number of rows affected by query
     *
     * @return  int
     */
    public function getAffectedRows() {
        
        switch ( $this->model ) {

            case ("MYSQLI"):
                
                $return = $this->handler->affected_rows;
                
                break;

            case ("MYSQL_PDO"):
            case ("SQLITE_PDO"):
            case ("ORACLE_PDO"):
            case ("DBLIB_PDO"):

                $return = $this->raw_data->rowCount();
                
                break;

            case ("DB2"):

                $return = db2_num_rows($this->raw_data);
                
                break;

            case ("POSTGRESQL"):

                $return = pg_affected_rows($this->raw_data);
                
                break;

        }

        return $return;
        
    }
    
    /**
     * Get last insert id (if available)
     *
     * @return  int
     * 
     * @throws  \Comodojo\Exception\DatabaseException
     */
    public function getInsertId() {
        
        switch ( $this->model ) {

            case ("MYSQLI"):
                
                $return = $this->handler->insert_id;
                
                break;

            case ("MYSQL_PDO"):
            case ("SQLITE_PDO"):
            
                $return = $this->handler->lastInsertId();

                break;
            
            case ("ORACLE_PDO"):
        
                try {
            
                    $return = self::oracleLastInsertId($this->handler);
                    
                } catch (DatabaseException $de) {
                    
                    throw $de;
                    
                }

                break;
                
            case ("DBLIB_PDO"):

                try {
            
                    $return = self::dblibLastInsertId($this->handler);
                    
                } catch (DatabaseException $de) {
                    
                    throw $de;
                    
                }

                break;

            case ("DB2"):

                $return = db2_last_insert_id($this->handler);
                
                break;

            case ("POSTGRESQL"):

                //$return = pg_last_oid($this->raw_data);
                $return = self::postgresqlLastInsertId($this->handler);

                break;

        }

        return intval($return);
        
    }
    
    /**
     * Set database handler
     *
     * @param   Object  $handler
     *
     * @return  QueryResult  \Comodojo\Database\QueryResult
     */
    private function setHandler($handler) {
        
        $this->handler = $handler;
        
        return $this;
        
    }
    
    /**
     * Set database model
     *
     * @param   string  $model
     *
     * @return  QueryResult  \Comodojo\Database\QueryResult
     */
    private function setModel($model) {
        
        $this->model = $model;
        
        return $this;
        
    }
    
    /**
     * Set database fetch mode
     *
     * @param   string  $fetch
     *
     * @return  QueryResult  \Comodojo\Database\QueryResult
     */
    private function setFetch($fetch) {
        
        $this->fetch = $fetch;
        
        return $this;
        
    }
    
    /**
     * Set raw data from query's result
     *
     * @param   Object  $data
     *
     * @return  \Comodojo\Database\QueryResult
     * 
     * @throws  \Comodojo\Exception\DatabaseException
     */
    private function setRawData($data) {
        
        if ( self::checkRawData($data, $this->model) === false ) throw new DatabaseException("Invalid result statement for selected model");
        
        $this->raw_data = $data;
        
        return $this;
        
    }
    
    /**
     * Check if raw data (statement) matches the expeced one
     *
     * @param   Object  $data
     * @param   string  $model
     *
     * @return  bool
     */
    private static function checkRawData($data, $model) {
    
        switch ( $model ) {

            case ("MYSQLI"):

                $return = ($data instanceof \mysqli_result || is_bool($data));
                
                break;

            case ("MYSQL_PDO"):
            case ("SQLITE_PDO"):
            case ("ORACLE_PDO"):
            case ("DBLIB_PDO"):
                
                $return = ($data instanceof \PDOStatement);
                
                break;

            case ("DB2"):

                $return = (is_resource($data) && @get_resource_type($data) == "DB2 Statement");
                
                break;

            case ("POSTGRESQL"):

                $return = (is_resource($data) && @get_resource_type($data) == "pgsql result");
                
                break;

        }
        
        return $return;
    
    }
    
    /**
     * Trik to enable last insert id (scope-relative) for dblib PDO, since
     * lastInsertId() is not supported by driver
     *
     * @return  int
     * 
     * @throws  \Comodojo\Exception\DatabaseException
     */
    private static function dblibLastInsertId($handler) {

        $query = "SELECT SCOPE_IDENTITY() as id";

        try {

            $response = $handler->prepare($query);
            
            $response->execute();
            
            $id = $response->fetchAll(\PDO::FETCH_ASSOC);

        } catch (\PDOException $e) {

            throw new DatabaseException($e->getMessage(), (int) $e->getCode());

        }

        return is_null($id[0]['id']) ? null : intval($id[0]['id']);

    }
    
    /**
     * Trik to enable last insert id (session-relative) for ORACLE_PDO, since
     * lastInsertId() is not supported by driver
     *
     * @return  int
     * 
     * @throws  \Comodojo\Exception\DatabaseException
     */
    private static function oracleLastInsertId($handler) {

        $query = "SELECT id.currval as id from dual";

        try {

            $response = $handler->prepare($query);
            
            $response->execute();
            
            $id = $response->fetchAll(\PDO::FETCH_ASSOC);

        } catch (\PDOException $e) {

            throw new DatabaseException($e->getMessage(), (int) $e->getCode());

        }

        return is_null($id[0]['id']) ? null : intval($id[0]['id']);

    }

    /**
     * Trik to enable last insert id for POSTGRESQL, since
     * last_oid is no more supported
     *
     * @return  int
     * 
     * @throws  \Comodojo\Exception\DatabaseException
     */
    private static function postgresqlLastInsertId($handler) {

        $query = "SELECT lastval()";

        $response = pg_query($handler, $query);

        if ( !$response ) throw new DatabaseException(pg_last_error());
            
        $id = pg_fetch_all($response);

        return is_null($id[0]['lastval']) ? null : intval($id[0]['lastval']);

    }
    
}