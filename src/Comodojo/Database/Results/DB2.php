<?php namespace Comodojo\Database\Results;

/**
 * Results object for DB2 model
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

class DB2 extends AbstractQueryResult {

    /**
     * {@inheritDoc}
     */
    public function getData() {

        $result = array();

        switch ( $this->fetch_mode ) {

            case self::FETCH_NUM:

                while ( $row = db2_fetch_row($this->raw_data) ) array_push($result, $row);

                break;

            case self::FETCH_ASSOC:

                while ( $row = db2_fetch_assoc($this->raw_data) ) array_push($result, $row);

                break;

            default:

                while ( $row = db2_fetch_both($this->raw_data) ) array_push($result, $row);

                break;

        }

        // current data to buffer;
        $this->data = $result;

        return $result;

    }

    /**
     * {@inheritDoc}
     */
    public function getLength() {

        return db2_num_fields($this->raw_data);

    }

    /**
     * {@inheritDoc}
     */
    public function getAffectedRows() {

        return db2_num_rows($this->raw_data);

    }

    /**
     * {@inheritDoc}
     */
    public function getInsertId() {

        return intval(db2_last_insert_id($this->handler));

    }

    /**
     * {@inheritDoc}
     */
    public function checkRawData($raw_data) {

        return (is_resource($data) && @get_resource_type($data) == "DB2 Statement");

    }

}
