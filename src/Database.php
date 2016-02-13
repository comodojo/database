<?php namespace Comodojo\Database;

use \Comodojo\Database\Models\ModelInterface;
use \Comodojo\Database\Results\QueryResultInterface;
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
     * Fetch data as numeric array
     *
     * @const string
     */
    const FETCH_NUM = "NUM";

    /**
     * Fetch data as associative array
     *
     * @const string
     */
    const FETCH_ASSOC = "ASSOC";

    /**
     * Fetch data as both numeric and associative array
     *
     * @const string
     */
    const FETCH_BOTH = "BOTH";

    /**
     * Connect to database using MySQLi model
     *
     * @const string
     */
    const MODEL_MYSQLI = "MySQLi";

    /**
     * Connect to database using MySQLPDO model
     *
     * @const string
     */
    const MODEL_MYSQLPDO = "MySQLPDO";

    /**
     * Connect to database using OraclePDO model
     *
     * @const string
     */
    const MODEL_ORACLEPDO = "OraclePDO";

    /**
     * Connect to database using SQLitePDO model
     *
     * @const string
     */
    const MODEL_SQLITEPDO = "SQLitePDO";

    /**
     * Connect to database using SQLServerPDO model
     *
     * @const string
     */
    const MODEL_SQLSERVERPDO = "SQLServerPDO";

    /**
     * Connect to database using DB2 model
     *
     * @const string
     */
    const MODEL_DB2 = "DB2";

    /**
     * Connect to database using PostgreSQL model
     *
     * @const string
     */
    const MODEL_POSTGRESQL = "PostgreSQL";

    /**
     * Fetch mode (ASSOC, NUM, BOTH)
     *
     * @var string
     */
    private $fetch = "ASSOC";

    /**
     * Database Model
     *
     * @var ModelInterface
     */
    private $model;

    /**
     * Supported database models
     *
     * @var array
     */
    private $supported_models = Array("MySQLi", "MySQLPDO", "OraclePDO", "SQLitePDO", "SQLServerPDO", "DB2", "PostgreSQL");

    /**
     * Constructor
     *
     * It validate database parameters and try to establish a connection
     *
     * @param   string|ModelInterface   $model  Database data model
     * @param   string                  $host   Host to connect to
     * @param   int                     $port   Database port
     * @param   string                  $name   Database name
     * @param   string                  $user   User name
     * @param   string                  $pass   User password
     *
     * @throws  DatabaseException
     */
    public function __construct($model, $host, $port, $name, $user, $pass = null) {

        if ( $model instanceof ModelInterface ) {

            $this->model = $model;

        } else if ( in_array($model, $this->supported_models) ) {

            $model_name = "\\Comodojo\\Database\\Models\\".$model;

            $this->model = new $model_name;

        } else {

            throw new DatabaseException('Unknown database model; have you typed it correctly?');

        }

        if ( empty($host) ) throw new DatabaseException('Invalid database host');

        if ( $port = filter_var($port, FILTER_VALIDATE_INT, array(
            "options" => array(
                "min_range" => 1,
                "max_range" => 65535
                )
            )) === false
        ) throw new DatabaseException('Invalid database port');

        if ( empty($name) ) throw new DatabaseException('Invalid database name');

        if ( empty($user) ) throw new DatabaseException('Invalid database user');

        try {

            $this->model->connect($host, $port, $name, $user, $pass);

        } catch (DatabaseException $ce) {

            throw $ce;

        }

    }

    /**
     * Destructor
     *
     * Unset (disconnect) database handler
     */
    public function __destruct() {

        $this->model->disconnect();

    }

    /**
     * Get current Database Model
     *
     * @return ModelInterface
     */
    final public function model() {

        return $this->model;

    }

    /**
     * Get defined fetch
     *
     * @return string
     */
    final public function getFetchMode() {

        return $this->fetch;

    }

    /**
     * Set fetch mode
     *
     * @param   string  $mode   Fetch mode (ASSOC, NUM, BOTH)
     *
     * @return  Database
     * @throws  DatabaseException
     */
    public function setFetchMode($mode) {

        if ( in_array( $fetch = strtoupper($mode), Array('ASSOC', 'NUM', 'BOTH')) ) {

            $this->fetch = $fetch;

        } else throw new DatabaseException('Invalid data fetch method');

        return $this;

    }

    /**
     * Cleanup database extra parameters
     *
     * It resets:
     *
     * - fetch mode to ASSOC
     *
     * @return  \Comodojo\Database\Database
     */
    public function clean() {

        $this->fetch = 'ASSOC';

        return $this;

    }

    /**
     * Shot a query to database and build a result set (QueryResultInterface object).
     *
     * @param   string  $statement
     *
     * @return  QueryResultInterface
     * @throws  DatabaseException
     */
    public function query($statement) {

        $model_name = $this->model->getName();

        $handler = $this->model->handler();

        $result_class = "\\Comodojo\\Database\\Results\\".$model_name;

        try {

            $result = $this->rawQuery($query);

            $return = new $result_class($handler, $this->model, $this->fetch, $result);

        } catch (DatabaseException $e) {

            throw $e;

        }

        return $return;

    }

    /**
     * Shot a query to database and return raw data
     *
     * @param   string  $statement
     *
     * @return  resource
     * @throws  DatabaseException
     */
    public function rawQuery($statement) {

        try {

            $response = $this->model->query($statement);

        } catch (DatabaseException $de) {

            throw $de;
        }

        return $response;

    }

}
