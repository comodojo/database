<?php namespace Comodojo\Database\QueryBuilder;

use \Comodojo\Exception\DatabaseException;
use \Exception;

/**
 * DROP query builder
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

class QueryDrop {
  
    private $model = null;

    private $if_exists = null;

    private $table = null;

    public function __construct($model) {

        $this->model = $model;

    }

    final public function ifExists($data) {
    
        $this->if_exists = $data;
    
        return $this;
    
    }
    
    final public function table($data) {
    
        $this->table = $data;
    
        return $this;
    
    }

    public function getQuery() {
        
        if ( is_null($this->table) ) throw new DatabaseException('Invalid parameters for database->drop', 1023);
        
        $query_pattern = "DROP TABLE %s%s";

        if ( in_array($this->model, array("MYSQL", "MYSQLI", "MYSQL_PDO", "POSTGRESQL", "DBLIB_PDO", "ORACLE_PDO", "SQLITE_PDO")) ) $if_exists = is_null($this->if_exists) ? null : 'IF EXISTS ';

        else $if_exists = null;

        return sprintf($query_pattern, $if_exists, $this->table);

    }

}
