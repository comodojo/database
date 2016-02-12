<?php namespace Comodojo\Database\Results;

use \Comodojo\Database\Database;
use \Comodojo\Database\Models\ModelInterface;
use \Comodojo\Exception\DatabaseException;
use \Exception;

/**
 * Abstract query result object
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

abstract class AbstractQueryResult implements QueryResultInterface {

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
     * Result's raw data
     *
     * @var resource
     */
    protected $raw_data;

    /**
     * Result's data
     *
     * @var array
     */
    protected $data;

    /**
     * Database Handler
     *
     * @var Database
     */
    protected $handler;

    /**
     * Database Model
     *
     * @var ModelInterface
     */
    protected $model;

    /**
     * Fetch mode
     *
     * @var string
     */
    protected $fetch_mode = 'ASSOC';

    /**
     * {@inheritDoc}
     */
    public function __construct(Database $handler, ModelInterface $model, $fetch_mode, $raw_data) {

        if ( !self::checkFetchMode($fetch_mode) ) {

            throw new DatabaseException("Unknown fetch mode");

        }

        if ( !$this->checkRawData($raw_data) ) {

            throw new DatabaseException("Invalid resource object from database handler");

        }

        $this->handler = $handler;

        $this->model = $model;

        $this->fetch_mode = strtoupper($fetch_mode);

        $this->raw_data = $raw_data;

    }

    /**
     * Get database handler
     *
     * @return   Database
     */
    final public function handler() {

        return $this->handler;

    }

    /**
     * Get database handler (alias)
     *
     * @return   Database
     */
    final public function getHandler() {

        return $this->handler();

    }

    /**
     * Get database model
     *
     * @return  ModelInterface
     */
    final public function model() {

        return $this->model;

    }

    /**
     * Get database model (alias)
     *
     * @return   ModelInterface
     */
    final public function getModel() {

        return $this->model();

    }

    /**
     * Get database fetch mode
     *
     * @return  string
     */
    final public function getFetchMode() {

        return $this->fetch_mode;

    }

    /**
     * {@inheritDoc}
     */
    public function getRawData() {

        return $this->raw_data;

    }

    /**
     * {@inheritDoc}
     */
    abstract public function getData();

    /**
     * {@inheritDoc}
     */
    abstract public function getLength();

    /**
     * {@inheritDoc}
     */
    abstract public function getAffectedRows();

    /**
     * {@inheritDoc}
     */
    abstract public function getInsertId();

    /**
     * Check if raw data is coherent with current database model
     *
     * @return   bool
     */
    abstract public function checkRawData($raw_data);

    /**
     * Check if fetch mode is supported
     *
     * @return   bool
     */
    protected static function checkFetchMode($fetch_mode) {

        return in_array(strtoupper($fetch_mode), array("NUM","ASSOC", "BOTH"));

    }

}
