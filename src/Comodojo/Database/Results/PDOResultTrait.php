<?php namespace Comodojo\Database\Results;

use \Comodojo\Database\Database;
use \PDO;
use \PDOStatement;

/**
 * Generic PDO trait
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

trait PDOResultTrait {

    /**
     * {@inheritDoc}
     */
    public function getData() {

        if ( !is_null($this->data) ) return $this->data;

        $fetch = self::selectFetchMode($this->fetch_mode);

        $result = $this->raw_data->fetchAll($fetch);

        $this->data = $result;

        return $result;

    }

    /**
     * {@inheritDoc}
     */
    public function getLength() {

        if ( is_null($this->data) ) $this->getData();

        return count($this->data);

    }

    /**
     * {@inheritDoc}
     */
    public function getAffectedRows() {

        return $this->raw_data->rowCount();

    }

    /**
     * {@inheritDoc}
     */
    public function checkRawData($raw_data) {

        return ($data instanceof PDOStatement);

    }

    private static function selectFetchMode($fetch_mode) {

        if ( $fetch_mode == Database::FETCH_NUM ) return PDO::FETCH_NUM;

        else if ( $fetch_mode == Database::FETCH_ASSOC ) return PDO::FETCH_ASSOC;

        else return PDO::FETCH_BOTH;

    }

}
