<?php namespace Comodojo\Database\Models;

use \PDO;
use \PDOException;
use \Comodojo\Exception\DatabaseException;

/**
 * Generic PDO model trait
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

trait PDOModelTrait {

    /**
     * {@inheritDoc}
     */
    public function connect($host, $port, $name, $user, $pass) {

        $dsn = self::composeDsn($host, $port, $name);

        try {

            $this->handler = new PDO($dsn, $user, $pass, array(PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION));

        } catch (PDOException $e) {

            throw new DatabaseException($e->getMessage(), $e->getCode());

        }

        return $this->handler;

    }

    /**
     * {@inheritDoc}
     */
    public function disconnect() {

        $this->handler = null;

        return true;

    }

    /**
     * {@inheritDoc}
     */
    public function query($statement) {

        try {

            $query = $this->handler->prepare($statement);

            $query->execute();

        } catch (PDOException $e) {

            throw new DatabaseException($e->getMessage(), (int)$e->getCode());

        }

        return $query;

    }

}
