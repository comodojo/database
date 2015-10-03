<?php namespace Comodojo\Database\QueryBuilder;

use \Comodojo\Exception\DatabaseException;
use \Exception;

/**
 * TRUNCATE query builder
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

class QueryTruncate {

    private $model = null;

    private $table = null;

    /**
     * @param string $model
     */
    public function __construct($model) {

        $this->model = $model;

    }

    /**
     * @param string $data
     */
    final public function table($data) {

        $this->table = $data;

        return $this;

    }

    public function getQuery() {
        
        if ( is_null($this->table) ) throw new DatabaseException('Invalid parameters for database->empty', 1016);

        switch ( $this->model ) {

            case ("MYSQLI"):
            case ("MYSQL_PDO"):
            case ("ORACLE_PDO"):
            case ("DBLIB_PDO"):

                $query_pattern = "TRUNCATE TABLE %s";

                break;

            case ("POSTGRESQL"):

                $query_pattern = "TRUNCATE %s RESTART IDENTITY";

                break;

            case ("DB2"):

                $query_pattern = "TRUNCATE TABLE %s IGNORE DELETE TRIGGERS DROP STORAGE IMMEDIATE";

                break;
            
            case ("SQLITE_PDO"):

                $query_pattern = "DELETE FROM %s; DELETE FROM SQLITE_SEQUENCE WHERE name='%s'";

                break;

        }

        if ( $this->model == "SQLITE_PDO" ) return sprintf($query_pattern, $this->table, $this->table);

        else return sprintf($query_pattern, $this->table);

    }

}
