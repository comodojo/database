<?php namespace Comodojo\Database\QueryBuilder;

use \Comodojo\Exception\DatabaseException;
use \Exception;

/**
 * GET query builder
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

class QueryGet {

    private $model = null;

    private $limit = null;

    private $offset = null;

    private $table = null;

    private $keys = null;

    private $distinct = null;

    private $join = null;

    private $using = null;

    private $on = null;

    private $where = null;

    private $group_by = null;

    private $having = null;

    private $order_by = null;

    private $query_pattern = "%s %s FROM %s%s%s%s%s%s%s";

    public function __construct($model) {

        $this->model = $model;

    }

    final public function limit($data) {

        $this->limit = $data;

        return $this;

    }

    final public function offset($data) {

        $this->offset = $data;

        return $this;

    }

    final public function table($data) {

        $this->table = $data;

        return $this;

    }

    final public function keys($data) {

        $this->keys = $data;

        return $this;

    }

    final public function distinct($data) {

        $this->distinct = $data;

        return $this;

    }

    final public function join($data) {

        $this->join = $data;

        return $this;

    }

    final public function using($data) {

        $this->using = $data;

        return $this;

    }

    final public function on($data) {

        $this->on = $data;

        return $this;

    }

    final public function where($data) {

        $this->where = $data;

        return $this;

    }

    final public function groupBy($data) {

        $this->group_by = $data;

        return $this;

    }

    final public function having($data) {

        $this->having = $data;

        return $this;

    }

    final public function orderBy($data) {

        $this->order_by = $data;

        return $this;

    }

    public function getQuery() {

        if ( is_null($this->table) OR is_null($this->keys) ) throw new DatabaseException('Invalid parameters for database->get', 1004);

        $select = $this->distinct ? "SELECT DISTINCT" : "SELECT";

        if ( is_null($this->join) ) $join = null;

        else {

            if ( !is_null($this->using) ) $join = $this->join." ".$this->using;

            elseif ( !is_null($this->on) ) $join = $this->join." ".$this->on;

            else $join = $this->join;

        }
        
        $where = is_null($this->where) ? null : " ".$this->where;

        $group_by = is_null($this->group_by) ? null : " ".$this->group_by;

        $having = is_null($this->having) ? null : " ".$this->having;

        $order_by = is_null($this->order_by) ? null : " ".$this->order_by;

        $limit = intval($this->limit) === 0 ? null : (' LIMIT '.(intval($this->offset) === 0 ? intval($this->limit) : intval($this->offset).",".intval($this->limit)));

        return sprintf($this->query_pattern, $select, $this->keys, $this->table, $join, $where, $group_by, $having, $order_by, $limit);

    }

}
