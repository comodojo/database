<?php namespace Comodojo\Database\Results;

use \Comodojo\Database\Database;
use \Comodojo\Database\Models\ModelInterface;

/**
 * Generic Query Result interface
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

interface QueryResultInterface {

    /**
     * Create the Query Result object
     *
     * @param   Database        $handler    Database Handler
     * @param   ModelInterface  $model      Selected Database Model
     * @param   string          $fetch      Fetch Method
     * @param   resource        $resource   Query resource
     *
     * @throws  \Comodojo\Exception\DatabaseException
     */
    public function __construct(Database $handler, ModelInterface $model, $fetch, $resource);

    /**
     * Get result's data as array (indexed according to fetch method selected)
     *
     * @return  array
     * @throws  \Comodojo\Exception\DatabaseException
     */
    public function getData();

    /**
     * Get raw data
     *
     * @return  resource
     */
    public function getRawData();

    /**
     * Get length of resultset
     *
     * @return  int
     * @throws  \Comodojo\Exception\DatabaseException
     */
    public function getLength();

     /**
      * Get number of rows affected by query
      *
      * @return  int
      * @throws  \Comodojo\Exception\DatabaseException
      */
     public function getAffectedRows();

    /**
     * Get last insert id (if available)
     *
     * @return  int
     * @throws  \Comodojo\Exception\DatabaseException
     */
    public function getInsertId();

}
