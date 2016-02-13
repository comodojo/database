<?php namespace Comodojo\Database\Models;

use \Comodojo\Exception\DatabaseException;

/**
 * DB2 model
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

class DB2 implements ModelInterface {

    use HandlerTrait;

    /**
     * {@inheritDoc}
     */
    public function check() {

        return function_exists('db2_pconnect');

    }

    /**
     * {@inheritDoc}
     */
    public function connect($host, $port, $name, $user, $pass) {

        $dsn  = "ibm:DRIVER={IBM DB2 ODBC DRIVER};DATABASE=".$name;
        $dsn .= ";HOSTNAME=".$host.";PORT=".$port.";PROTOCOL=TCPIP;UID=".$user.";PWD=".$pass.";";

        $this->handler = db2_pconnect($dsn, $user, $pass);

        if ( $this->handler === false ) {

            throw new DatabaseException(db2_conn_errormsg());

        }

        return $this->handler;

    }

    /**
     * {@inheritDoc}
     */
    public function disconnect() {

        if ( $this->handler !== null ) return db2_close($this->handler);

        return true;

    }

    /**
     * {@inheritDoc}
     */
    public function query($statement) {

        $response = db2_exec($this->handler, $statement);

        if ( $response === false ) {

            throw new DatabaseException(db2_stmt_error());

        }

        return $response;

    }

}
