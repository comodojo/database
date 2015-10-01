<?php namespace Comodojo\Database\QueryBuilder;

use \Comodojo\Exception\DatabaseException;
use \Exception;

/**
 * CREATE query builder
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

class QueryCreate {

    private $model = null;

    private $if_not_exists = null;

    private $engine = null;
    
    private $charset = null;

    private $name = null;

    private $columns = array();

    /**
     * @param string $model
     */
    public function __construct($model) {

        $this->model = $model;

    }

    final public function ifNotExists($data) {
    
        $this->if_not_exists = $data;
    
        return $this;
    
    }
    
    final public function engine($data) {
    
        $this->engine = $data;
    
        return $this;
    
    }
    
    final public function charset($charset) {
    
        $this->charset = $charset;
    
        return $this;
    
    }
    
    final public function collate($collate) {
    
        $this->collate = $collate;
    
        return $this;
    
    }
    
    /**
     * @param string $data
     */
    final public function table($data) {
    
        $this->name = $data;
    
        return $this;
    
    }

    final public function columns($data) {
    
        $this->columns = $data;
    
        return $this;
    
    }

    public function getQuery() {
        
        if ( is_null($this->name) OR empty($this->columns)) throw new DatabaseException('Invalid parameters for database->create',1027);

        $if_not_exists = is_null($this->if_not_exists) ? null : " IF NOT EXISTS";

        $engine = is_null($this->engine) ? null : ' ENGINE '.$this->engine;
        
        $charset = is_null($this->charset) ? null : ' DEFAULT CHARSET='.$this->charset;
        
        $collate = is_null($this->collate) ? null : ' COLLATE='.$this->collate;

        switch ($this->model) {

            case 'MYSQLI':
            case 'MYSQL_PDO':

                // $table_pattern = "`*_DBPREFIX_*%s`";

                // $table = sprintf($table_pattern, trim($this->name));

                $query_pattern = "CREATE TABLE%s %s (%s)%s%s%s";

                // $query = sprintf($query_pattern, $if_not_exists, $table, implode(', ',$this->columns), $engine);

                $query = sprintf($query_pattern, $if_not_exists, $this->name, implode(', ',$this->columns), $engine, $charset, $collate);

                break;

            case 'POSTGRESQL':
            case 'DB2':
            case 'DBLIB_PDO':
            case 'ORACLE_PDO':
            case 'SQLITE_PDO':
            default:

                // $table_pattern = "*_DBPREFIX_*%s";

                // $table = sprintf($table_pattern, trim($this->name));

                $query_pattern = "CREATE TABLE%s %s (%s)";

                // $query = sprintf($query_pattern, $if_not_exists, $table, implode(', ',$this->columns));

                $query = sprintf($query_pattern, $if_not_exists, $this->name, implode(', ',$this->columns));

                break;
        
        }

        return $query;

    }

}
