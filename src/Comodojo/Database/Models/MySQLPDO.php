<?php namespace Comodojo\Database\Models;

use \PDO;

/**
 * MySQLPDO model
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

class MySQLPDO implements ModelInterface {

    use HandlerTrait;
    use PDOModelTrait;

    /**
     * {@inheritDoc}
     */
    public function check() {

        return in_array('mysql', PDO::getAvailableDrivers());

    }

    /**
     * Compose the dsn
     *
     * @param   string  $host
     * @param   int     $port
     * @param   string  $name
     *
     * @return  string
     */
    protected static function composeDsn($host, $port, $name) {

        return "mysql:host=".$host.";port=".$port.";dbname=".$name;

    }

}
