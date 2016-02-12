<?php namespace Comodojo\Database\Results;

use \PDO;
use \PDOException;
use \Comodojo\Database\Database;
use \Comodojo\Database\DatabaseException;

/**
 * Results object for SQLServerPDO model
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

class SQLServerPDO extends AbstractQueryResult {

    use PDOResultTrait;

    /**
     * {@inheritDoc}
     */
    public function getInsertId() {

        return self::dblibLastInsertId($this->handler);

    }

    /**
     * Trik to enable last insert id (scope-relative) for dblib PDO, since
     * lastInsertId() is not supported by driver
     *
     * @return  int
     *
     * @throws  DatabaseException
     */
    private static function dblibLastInsertId(Database $handler) {

        $query = "SELECT SCOPE_IDENTITY() as id";

        try {

            $response = $handler->prepare($query);

            $response->execute();

            $id = $response->fetchAll(PDO::FETCH_ASSOC);

        } catch (PDOException $e) {

            throw new DatabaseException($e->getMessage(), (int)$e->getCode());

        }

        return is_null($id[0]['id']) ? null : intval($id[0]['id']);

    }

}
