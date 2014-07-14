<?php namespace comodojo\DispatcherLibrary;

/**
 * Database connect/query plugin for comodojo/dispatcher.framework	
 * 
 * @package		Comodojo dispatcher (Spare Parts)
 * @author		comodojo <info@comodojo.org>
 * @license		GPL-3.0+
 *
 * LICENSE:
 * 
 * This program is free software: you can redistribute it and/or modify
 * it under the terms of the GNU Affero General Public License as
 * published by the Free Software Foundation, either version 3 of the
 * License, or (at your option) any later version.
 *
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU Affero General Public License for more details.
 *
 * You should have received a copy of the GNU Affero General Public License
 * along with this program. If not, see <http://www.gnu.org/licenses/>.
 */

define(COMODOJO_DEFAULT_DB_DATA_MODEL, "MYSQLI");
define(COMODOJO_DEFAULT_DB_HOST, "localhost");
define(COMODOJO_DEFAULT_DB_PORT, 3306);
define(COMODOJO_DEFAULT_DB_NAME, "comodojo");
define(COMODOJO_DEFAULT_DB_USER, "comodojo");
define(COMODOJO_DEFAULT_DB_PASSWORD, "");

use \comodojo\Exception\DatabaseException;
use \comodojo\Dispatcher\debug;
use \Exception;

class database {

	private $model = NULL;

	private $host = NULL;

	private $port = NULL;

	private $name = NULL;

	private $user = NULL;

	private $pass = NULL;

	private $dbh = false;

	private $id = false;

	private $fetch = "ASSOC";

	private $rows = false;

	private $supported_models = Array("MYSQLI","MYSQL_PDO","ORACLE_PDO","SQLITE_PDO","DBLIB_PDO","DB2","POSTGRESQL");

	public function __construct($model=NULL, $host=NULL, $port=NULL, $name=NULL, $user=NULL, $pass=NULL) {

		$this->model = in_array(strtoupper($model), $this->supported_models) ? strtoupper($model) : COMODOJO_DEFAULT_DB_DATA_MODEL;
		$this->host = is_null($host) ? COMODOJO_DEFAULT_DB_HOST : $host;
		$this->port = is_null($port) ? COMODOJO_DEFAULT_DB_PORT : filter_var($port, FILTER_VALIDATE_INT);
		$this->name = is_null($name) ? COMODOJO_DEFAULT_DB_NAME : $name;
		$this->user = is_null($user) ? COMODOJO_DEFAULT_DB_USER : $user;
		$this->pass = is_null($pass) ? COMODOJO_DEFAULT_DB_PASSWORD : $pass;

		debug("Creating database handler (".$this->model.") - ".$this->name."@".$this->host.":".$this->port, "INFO", "database");

		try {

			$this->connect();

		} catch (DatabaseException $ce) {

			debug("Error creating database handler (".$this->model.") - ".$ce->getMessage, "ERROR", "database");

			throw $ce;

		} catch (Exception $e) {

			debug("Not manageable exception thrown: ".$e->getMessage, "ERROR", "database");

			throw $e;

		}

	}

	public function __destruct() {

		$this->disconnect();

	}

	public function fetch($mode) {

		if ( in_array(strtoupper($fetch), Array('ASSOC','NUM','BOTH')) ) {

			$this->fetch = strtoupper($fetch);

		}
		else throw new DatabaseException('Invalid data fetch method');

		return $this;

	}

	public function id($enabled=true) {

		$this->id = filter_var($enabled, FILTER_VALIDATE_BOOLEAN);

		return $this;

	}

	public function query($query, $return_raw=false) {

		debug("Ready to perform query: ".$query, "DEBUG", "database");

		switch ($this->model) {

			case ("MYSQLI"):
				
				$response = $this->dbh->query($query);
				if (!$response) {
					debug("Cannot perform query: ".$this->dbh->error, "ERROR", "database");
					throw new DatabaseException($this->dbh->error, $this->dbh->errno);
				}

			break;

			case ("MYSQL_PDO"):
			case ("ORACLE_PDO"):
			case ("SQLITE_PDO"):
			case ("DBLIB_PDO"):

				try {
					$response = $this->dbh->prepare($query);
					//$response->setFetchMode($fetch);
					$response->execute();
				}
				catch (PDOException $e) {
					$error = $dbHandler->errorInfo();
					debug("Cannot perform query: ".$error[2], "ERROR", "database");
					throw new DatabaseException($error[1], $error[2]);
				}

			break;

			case ("DB2"):

				$response = db2_exec($this->dbh,$query);
				if (!$response) {
					debug("Cannot perform query: ".db2_stmt_error(), "ERROR", "database");
					throw new DatabaseException(db2_stmt_error());
				}
				
			}
			break;

			case ("POSTGRESQL"):

				$response = pg_query($this->dbh,$query);
				if (!$response) {
					$_error = pg_last_error();
					debug("Cannot perform query: ".pg_last_error(), "ERROR", "database");
					throw new DatabaseException(pg_last_error());
				}

			break;

		}

		if ($return_raw) $return = $response;

		else {

			try {

				$return = $this->results_to_array($response);

			} catch (DatabaseException $e) {
				
				throw $e;

			}

		}

		return $return;

	}

	private function connect() {

		if ( empty($this->model) ) throw new DatabaseException('Invalid database data model');

		switch ($this->model) {

			case ("MYSQLI"):
				
				if ( empty($this->host) OR empty($this->port) OR empty($this->name) OR empty($this->user) OR empty($this->pass) ) {
					throw new DatabaseException('Invalid database parameters');
				}

				$this->dbh = new mysqli($this->host, $this->user, $this->pass, $this->name, $this->port);

				if ($this->dbh->connect_error) {
					throw new DatabaseException($this->dbh->connect_error, $this->dbh->connect_errno);
				}

			break;

			case ("MYSQL_PDO"):

				if ( empty($this->host) OR empty($this->port) OR empty($this->name) OR empty($this->user) OR empty($this->pass) ) {
					throw new DatabaseException('Invalid database parameters');
				}

				$dsn="mysql:host=".$this->host.";port=".$this->port .";dbname=".$this->name;
				
				try {
					$this->dbh = new PDO($dsn,$this->user,$this->pass);
				}
				catch (PDOException $e) {
					throw new DatabaseException($e->getMessage(), $e->getCode());
				}

			break;

			case ("ORACLE_PDO"):

				if ( empty($this->host) OR empty($this->port) OR empty($this->name) OR empty($this->user) OR empty($this->pass) ) {
					throw new DatabaseException('Invalid database parameters');
				}

				$dsn="oci:dbname=".$this->host.":".$this->port."/".$this->name;
				
				try {
					$this->dbh = new PDO($dsn,$this->user,$this->pass);
				}
				catch (PDOException $e) {
					throw new DatabaseException($e->getMessage(), $e->getCode());
				}

			break;

			case ("SQLITE_PDO"):
			
				if ( empty($this->name) ) {
					throw new DatabaseException('Invalid database parameters');
				}

				$dsn="sqlite:".$this->name;

				try {
					$this->dbh = new PDO($dsn);
				}
				catch (PDOException $e) {
					throw new DatabaseException($e->getMessage(), $e->getCode());
				}

			break;

			case ("DB2"):

				if ( empty($this->host) OR empty($this->port) OR empty($this->name) OR empty($this->user) OR empty($this->pass) ) {
					throw new DatabaseException('Invalid database parameters');
				}

				$dsn="ibm:DRIVER={IBM DB2 ODBC DRIVER};DATABASE=".$this->name.";HOSTNAME=".$this->host.";PORT=".$this->port.";PROTOCOL=TCPIP;UID=".$this->user.";PWD=".$this->pass.";";

				$this->dbh = db2_pconnect($dsn,$this->user,$this->pass);
				if (!$this->dbh){
					throw new DatabaseException(db2_conn_errormsg());
				}

			}
			break;

			case ("DBLIB_PDO"):

				if ( empty($this->host) OR empty($this->port) OR empty($this->name) OR empty($this->user) OR empty($this->pass) ) {
					throw new DatabaseException('Invalid database parameters');
				}

				$dsn = "dblib:host=".$this->host.":".$this->port.";dbname=".$this->name;
			
				try {
					$this->dbh = new PDO($dsn,$this->user,$this->pass);
				}
				catch (PDOException $e) {
					throw new DatabaseException($e->getMessage(), $e->getCode());
				}

			break;

			case ("POSTGRESQL"):

				if ( empty($this->host) OR empty($this->port) OR empty($this->name) OR empty($this->user) OR empty($this->pass) ) {
					throw new DatabaseException('Invalid database parameters');
				}

				$dsn = "host=".$this->host." port=".$this->port." dbname=".$this->name." user=".$this->user." password=".$this->pass;

				$this->dbh = @pg_connect($dsn);
				if (!$this->dbh) {
					throw new DatabaseException(pg_last_error());
				}

			break;

		}

	}

	private function disconnect() {

		debug("Closing database handler (".$this->model.")", "INFO", "database");

		switch($this->model) {
			
			case ("MYSQLI"):
				if ($this->dbh !== false) $this->dbh->close();
			break;
			
			case ("MYSQL_PDO"):
			case ("ORACLE_PDO"):
			case ("SQLITE_PDO"):
			case ("DBLIB_PDO"):
				$this->dbh = null;
			break;
			
			case ("DB2"):
				if ($this->dbh !== false) db2_close($this->dbh);
			break;
			
			case ("POSTGRESQL"):
				if ($this->dbh !== false) pg_close($this->dbh);
				$this->dbh = null;
			break;
			
			default:
				debug("Unknown database model (".$this->model.")", "WARNING", "database");
			break;
		}

	}

	private function results_to_array($data) {

		debug("Building result set (".$this->model.")", "INFO", "database");

		if ( empty($this->model) ) throw new DatabaseException('Invalid database data model');

		$result	= Array();
		$id		= false;
		$length = 0;
		$rows	= 0;

		$iterator = 0;

		switch ($this->model) {

			case ("MYSQLI"):
				
				if ( !is_object($data) OR !is_a($data, 'mysqli_result') ) throw new DatabaseException('Invalid result data for model '.$this->model);

				switch ($this->fetch) {
					case 'NUM':		$fetch = MYSQLI_NUM;	break;
					case 'ASSOC':	$fetch = MYSQLI_ASSOC;	break;
					default:		$fetch = MYSQLI_BOTH;	break;
				}
				
									$length = $data->num_rows;
				if ($this->id)		$id 	= $this->dbh->insert_id;
				if ($this->rows)	$rows 	= $this->dbh->affected_rows;

				while($iterator < $length) {
					$result[$iterator] = $data->fetch_array($fetch);
					$iterator++;
				}
				$data->free();

			break;

			case ("MYSQL_PDO"):
			case ("ORACLE_PDO"):
			case ("SQLITE_PDO"):
			case ("DBLIB_PDO"):

				if ( !is_object($data) ) throw new DatabaseException('Invalid result data for model '.$this->model);

				switch ($this->fetch) {
					case 'NUM':		$fetch = PDO::FETCH_NUM;	break;
					case 'ASSOC':	$fetch = PDO::FETCH_ASSOC;	break;
					default:		$fetch = PDO::FETCH_BOTH;	break;
				}

				$result = $sth->fetchAll($fetch);

									$length = sizeof($result);
				if ($this->id)		$id 	= $this->dbh->lastInsertId();
				if ($this->rows)	$rows 	= $data->rowCount();

			break;

			case ("DB2"):

				if ( !is_resource($data) OR @get_resource_type($data) != "DB2 Statement" ) throw new DatabaseException('Invalid result data for model '.$this->model);

									$length = db2_num_fields($data);
				if ($this->id)		$id 	= db2_last_insert_id($this->dbh);
				if ($this->rows)	$rows 	= db2_num_rows($data);

				switch ($this->fetch) {
					case 'NUM': 	while ($row = db2_fetch_row($data)) array_push($result, $row);		break;
					case 'ASSOC': 	while ($row = db2_fetch_assoc($data)) array_push($result, $row);	break;
					default: 		while ($row = db2_fetch_both($data)) array_push($result, $row);		break;
				}

			}
			break;

			case ("POSTGRESQL"):

				if ( !is_resource($data) OR @get_resource_type($data) != "pgsql result" ) throw new DatabaseException('Invalid result data for model '.$this->model);
				
									$length = pg_num_rows($data);
				if ($this->id)		$id 	= pg_last_oid($data);
				if ($this->rows)	$rows 	= pg_affected_rows($data);

				while($iterator < $length) {
					switch ($this->fetch) {
						case 'NUM': 	$result[$iterator] = pg_fetch_array($data);	break;
						case 'ASSOC': 	$result[$iterator] = pg_fetch_assoc($data);	break;
						default: 		$result[$iterator] = pg_fetch_all($data);	break;
					}
					$iterator++;
				}

			break;

		}

		return Array(
			"data"			=>	$result,
			"length"		=>	$length,
			"id"			=>	$id,
			"affected_rows"	=>	$rows
		);

	}

}

?>