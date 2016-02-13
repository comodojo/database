<?php namespace Comodojo\Database\Results;

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
     * @var resource
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
    protected $fetch_mode;

    /**
     * {@inheritDoc}
     */
    public function __construct($handler, ModelInterface $model, $fetch_mode, $raw_data) {

        if ( !$this->checkRawData($raw_data) ) {

            throw new DatabaseException("Invalid resource object from database handler");

        }

        $this->handler = $handler;

        $this->model = $model;

        $this->fetch_mode = $fetch_mode;

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
     * Get database model
     *
     * @return  ModelInterface
     */
    final public function model() {

        return $this->model;

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

}
