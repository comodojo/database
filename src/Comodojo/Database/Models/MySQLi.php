<?php namespace Comodojo\Database\Models;

use \mysqli as php_mysqli;
use \Comodojo\Exception\DatabaseException;

/**
 * MySQLi model
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

class MySQLi implements ModelInterface {

    use HandlerTrait;

    /**
     * {@inheritDoc}
     */
    public function check() {

        return function_exists('mysqli_connect');

    }

    /**
     * {@inheritDoc}
     */
    public function connect($host, $port, $name, $user, $pass) {

        $this->handler = new php_mysqli($host, $user, $pass, $name, $port);

        if ( $this->handler->connect_error ) {

            throw new DatabaseException($this->handler->connect_error, $this->handler->connect_errno);

        }

        return $this->handler;

    }

    /**
     * {@inheritDoc}
     */
    public function disconnect() {

        if ( $this->handler !== null ) return $this->handler->close();

        return true;

    }

    /**
     * {@inheritDoc}
     */
    public function query($statement) {

        $response = $this->handler->query($statement);

        if ( $response === false ) {

            throw new DatabaseException($this->handler->error, $this->handler->errno);

        }

        return $response;

    }

}
