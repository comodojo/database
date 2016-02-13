<?php namespace Comodojo\Database\Results;

use \Comodojo\Database\Database;
use \Comodojo\Database\DatabaseException;

/**
 * Results object for PostgreSQL model
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

class PostgreSQL extends AbstractQueryResult {

    /**
     * {@inheritDoc}
     */
    public function getData() {

        if ( !is_null($this->data) ) return $this->data;

        $result = array();

        $iterator = 0;

        while ( $iterator < pg_num_rows($this->raw_data) ) {

            switch ( $this->fetch_mode ) {

                case Database::FETCH_NUM:

                    $result[$iterator] = pg_fetch_array($this->raw_data);

                    break;

                case Database::FETCH_ASSOC:

                    $result[$iterator] = pg_fetch_assoc($this->raw_data);

                    break;

                default:

                    $result[$iterator] = pg_fetch_all($this->raw_data);

                    break;

            }

            $iterator++;

        }

        // current data to buffer;
        $this->data = $result;

        return $result;

    }

    /**
     * {@inheritDoc}
     */
    public function getLength() {

        return pg_num_rows($this->raw_data);

    }

    /**
     * {@inheritDoc}
     */
    public function getAffectedRows() {

        return pg_affected_rows($this->raw_data);

    }

    /**
     * {@inheritDoc}
     */
    public function getInsertId() {

        return self::postgresqlLastInsertId($this->handler);

    }

    /**
     * {@inheritDoc}
     */
    public function checkRawData($raw_data) {

        return (is_resource($data) && @get_resource_type($data) == "pgsql result");

    }

    /**
     * Trik to enable last insert id for POSTGRESQL, since
     * last_oid is no more supported
     *
     * @return  int
     *
     * @throws  DatabaseException
     */
    private static function postgresqlLastInsertId($handler) {

        $query = "SELECT lastval()";

        $response = pg_query($handler, $query);

        if ( !$response ) throw new DatabaseException(pg_last_error());

        $id = pg_fetch_all($response);

        return is_null($id[0]['lastval']) ? null : intval($id[0]['lastval']);

    }

}
