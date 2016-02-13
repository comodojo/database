<?php namespace Comodojo\Database\Results;

use \Comodojo\Database\Database;
use \mysqli_result;

/**
 * Results object for MySQLi model
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

class MySQLi extends AbstractQueryResult {

    /**
     * {@inheritDoc}
     */
    public function getData() {

        if ( !is_null($this->data) ) return $this->data;

        $fetch = self::selectFetchMode($this->fetch_mode);

        if ( is_bool($this->raw_data) ) {

            $result = null;

        } else {

            $result = array();

            $iterator = 0;

            while ( $iterator < $this->raw_data->num_rows ) {

                $result[$iterator] = $this->raw_data->fetch_array($fetch);

                $iterator++;

            }

        }

        //if ( is_object($this->raw_data) ) $this->raw_data->free();

        // current data to buffer;
        $this->data = $result;

        return $result;

    }

    /**
     * {@inheritDoc}
     */
    public function getLength() {

        return $this->raw_data->num_rows;

    }

    /**
     * {@inheritDoc}
     */
    public function getAffectedRows() {

        return $this->handler->affected_rows;

    }

    /**
     * {@inheritDoc}
     */
    public function getInsertId() {

        return intval($this->handler->insert_id);

    }

    /**
     * {@inheritDoc}
     */
    public function checkRawData($raw_data) {

        return ($raw_data instanceof mysqli_result || is_bool($raw_data));

    }

    private static function selectFetchMode($fetch_mode) {

        if ( $fetch_mode == Database::FETCH_NUM ) return MYSQLI_NUM;

        else if ( $fetch_mode == Database::FETCH_ASSOC ) return MYSQLI_ASSOC;

        else return MYSQLI_BOTH;

    }

}
