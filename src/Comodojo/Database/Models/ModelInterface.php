<?php namespace Comodojo\Database\Models;

/**
 * Generic Model interface
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

interface ModelInterface {

    /**
     * Check if driver is installed
     *
     * @return  bool
     */
    public function check();

    /**
     * Connect to database
     *
     * @return  Object
     * @throws  DatabaseException
     */
    public function connect($host, $port, $name, $user, $pass);

    /**
     * Disconnect from database
     *
     * @return  bool
     */
    public function disconnect();

    /**
     * Shot a query to database
     *
     * @param   string  $statement  SQL statement
     *
     * @return  resource
     * @throws  DatabaseException
     */
    public function query($statement);

    /**
     * Get database handler
     *
     * @return  object
     */
    public function handler();

    /**
     * Get current model name
     *
     * @return  string
     */
    public function getName();

}
