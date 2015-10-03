<?php namespace Comodojo\Database\QueryBuilder;

use \Comodojo\Exception\DatabaseException;
use \Exception;

/**
 * DELETE query builder
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

class QueryDelete {

    private $model = null;

    private $table = null;

    private $where = null;

    public function __construct($model) {

        $this->model = $model;

    }

    final public function table($data) {

        $this->table = $data;

        return $this;

    }

    final public function where($data) {

        $this->where = $data;

        return $this;

    }

    public function getQuery() {
        
        if ( is_null($this->table) ) throw new DatabaseException('Invalid parameters for database->delete', 1018);

        $query_pattern = "DELETE FROM %s %s";

        return sprintf($query_pattern, $this->table, $this->where);

    }

}
