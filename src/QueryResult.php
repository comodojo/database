<?php namespace Comodojo\Database;

use \Comodojo\Exception\DatabaseException;
use \Exception;

/**
 * Cross database result object model
 * 
 * @package     Comodojo Spare Parts
 * @author      Marco Giovinazzi <info@comodojo.org>
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

    private $raw_data = null;
    
    private $handler = null;
    
    private $model = null;
    
    private $fetch_mode = 'ASSOC';

    final static public function create($raw_data, $handler, $model) {
        
        $result = new Result();
        
        $result->setRawData($raw_data)->setHandler($handler, $model);
        
        return $result;
        
    }

    private function setRawData($data) {
        
        $this->raw_data = $data;
        
        return $this;
        
    }
    
    private function setHandler($handler, $model) {
        
        $this->handler = $handler;
        
        $this->model = $model;
        
        return $this;
        
    }
    
    /**
     * Set fetch mode
     *
     * @param   string  $mode   Fetch mode (ASSOC, NUM, BOTH)
     *
     * @return  Object          $this
     */
    final public function setFetchMode($mode) {

        if ( in_array(strtoupper($mode), Array('ASSOC','NUM','BOTH')) ) {

            $this->fetch_mode = strtoupper($mode);

        }
        else throw new DatabaseException('Invalid data fetch mode');

        return $this;

    }
    
    final public function getFetchMode() {
        
        return $this->fetch_mode;
        
    }
    
    final public function getRawData() {
        
        return $this->rawData;
        
    }
    
    final public function getData() {
    	
    	try {
    		
            $result = self::dataToArray($this->raw_data, $this->model, $this->fetch_mode);
    		
    	} catch (DatabaseException $de) {
    		
    		throw $de;
    		
    	}
    	
    	return $result;
    	
    }
    
    final public function getLength() {
        
        $length = 0;
        
        switch ($model) {
        
            case ("MYSQLI"):
                
                $length = is_object($raw_data) ? $raw_data->num_rows : 0;
                
                break;

            case ("MYSQL_PDO"):
            case ("SQLITE_PDO"):
            
                $length = sizeof($result);
                
                break;
            
            case ("ORACLE_PDO"):
        
                $this->length = sizeof($result);
                
                break;
                
            case ("DBLIB_PDO"):

                $this->length = sizeof($result);
                
                break;
                
            case ("DB2"):

                $this->length = db2_num_fields($data);
                
                break;

            case ("POSTGRESQL"):
                
                $this->length = pg_num_rows($data);
                
                break;

        }
        
        return $length;
        
    }
    
    final public function getId() {
        
        
        
    }
    
    final public function getAffectedRows() {}
    
    /**
     * Transform database raw result in a standard array
     *
     * @param   mixed   $rawData   Raw data from database handler
     *
     * @return  array
     */
    static private function dataToArray($data, $model, $fetch_mode) {

        $result = Array();
        
        switch ($model) {

            case ("MYSQLI"):
                
                if ( ( !is_object($data) OR !is_a($data, 'mysqli_result') ) AND $data != TRUE ) throw new DatabaseException('Invalid result data for model '.$model);

                switch ($fetch_mode) {
                    case 'NUM':     $fetch = MYSQLI_NUM;    break;
                    case 'ASSOC':   $fetch = MYSQLI_ASSOC;  break;
                    default:        $fetch = MYSQLI_BOTH;   break;
                }
                
                $length = is_object($data) ? $data->num_rows : 0;
                
                $iterator = 0;

                while($iterator < $this->length) {
                    
                    $result[$iterator] = $data->fetch_array($fetch);
                    
                    $iterator++;
                    
                }

                // if ( is_object($data) ) $data->free();

                break;

            case ("MYSQL_PDO"):
            case ("SQLITE_PDO"):
            case ("ORACLE_PDO"):
            case ("DBLIB_PDO"):
                
                if ( !is_object($data) ) throw new DatabaseException('Invalid result data for model '.$model);

                switch ($fetch_mode) {
                    case 'NUM':     $fetch = \PDO::FETCH_NUM;    break;
                    case 'ASSOC':   $fetch = \PDO::FETCH_ASSOC;  break;
                    default:        $fetch = \PDO::FETCH_BOTH;   break;
                }

                try {
                    
                    $result = $data->fetchAll($fetch);

                } catch (\PDOException $pe) {
                    
                    throw new DatabaseException( $pe->getMessage(), $pe->getCode() );

                }

                break;

            case ("DB2"):

                if ( !is_resource($data) OR @get_resource_type($data) != "DB2 Statement" ) throw new DatabaseException('Invalid result data for model '.$model);

                switch ($fetch_mode) {
                    case 'NUM':     while ($row = db2_fetch_row($data)) array_push($result, $row);      break;
                    case 'ASSOC':   while ($row = db2_fetch_assoc($data)) array_push($result, $row);    break;
                    default:        while ($row = db2_fetch_both($data)) array_push($result, $row);     break;
                }

                break;

            case ("POSTGRESQL"):

                if ( !is_resource($data) OR @get_resource_type($data) != "pgsql result" ) throw new DatabaseException('Invalid result data for model '.$model);
                
                $iterator = 0;
                
                $length = pg_num_rows($data);
                
                while($iterator < $length) {
                    
                    switch ($fetch_mode) {
                        case 'NUM':     $result[$iterator] = pg_fetch_array($data); break;
                        case 'ASSOC':   $result[$iterator] = pg_fetch_assoc($data); break;
                        default:        $result[$iterator] = pg_fetch_all($data);   break;
                    }
                    
                    $iterator++;
                    
                }

                break;

        }

        return $result;

    }
    
}