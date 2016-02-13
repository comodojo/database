<?php namespace Comodojo\Database\Models;

use \Comodojo\Exception\DatabaseException;

/**
 * PostgreSQL model
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

class PostgreSQL implements ModelInterface {

    use HandlerTrait;

    /**
     * {@inheritDoc}
     */
    public function check() {

        return function_exists('pg_connect');

    }

    /**
     * {@inheritDoc}
     */
    public function connect($host, $port, $name, $user, $pass) {

        $dsn = "host=".$host." port=".$port." dbname=".$name." user=".$user." password=".$pass;

        $this->handler = @pg_connect($dsn);

        if ( $this->handler === false ) {

            throw new DatabaseException(pg_last_error());

        }

        return $this->handler;

    }

    /**
     * {@inheritDoc}
     */
    public function disconnect() {

        if ( $this->handler !== null ) return @pg_close($this->handler);

        return true;

    }

    /**
     * {@inheritDoc}
     */
    public function query($statement) {

        $response = pg_query($this->handler, $statement);

        if ( $response === false ) {

            throw new DatabaseException(pg_last_error());

        }

        return $response;

    }

}
