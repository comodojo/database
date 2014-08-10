<?php namespace Comodojo\Database\QueryBuilder;

use \Comodojo\Exception\DatabaseException;
use \Exception;

/**
 * Column definitions
 * 
 * @package     Comodojo Spare Parts
 * @author      Marco Giovinazzi <info@comodojo.org>
 * @license     GPL-3.0+
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

class Column {

    private $name = null;

    private $type = null;

    private $length = null;

    private $unsigned = null;

    private $zerofill = null;

    private $charset = null;

    private $collate = null;

    private $notNull = null;

    private $defaultValue = null;

    private $autoincrement = null;

    private $unique = null;

    private $primaryKey = null;

    static private $supported_column_types = array('STRING','INTEGER','FLOAT','DECIMAL','BOOL','TIME','DATE','TIMESTAMP','TEXT','BLOB');

    static private $supported_column_conversions = array(

        "MYSQL" => array(
            "STRING"    =>  "VARCHAR",
            "INTEGER"   =>  "INTEGER",
            "FLOAT"     =>  "FLOAT",
            "DECIMAL"   =>  "BOOL",
            "BOOL"      =>  "TIME",
            "TIME"      =>  "DATETIME",
            "DATE"      =>  "DATE",
            "TIMESTAMP" =>  "TIMESTAMP",
            "TEXT"      =>  "TEXT",
            "BLOB"      =>  "BLOB"
        ),

        "POSTGRESQL" => array(
            "STRING"    =>  "VARCHAR",
            "INTEGER"   =>  "INTEGER",
            "FLOAT"     =>  "FLOAT4",
            "DECIMAL"   =>  "BOOL",
            "BOOL"      =>  "TIME",
            "TIME"      =>  "DATETIME",
            "DATE"      =>  "DATE",
            "TIMESTAMP" =>  "TIMESTAMP",
            "TEXT"      =>  "TEXT",
            "BLOB"      =>  "BYTEA"
        ),

        "ORACLE" => array(
            "STRING"    =>  "VARCHAR",
            "INTEGER"   =>  "NUMBER",
            "FLOAT"     =>  "FLOAT",
            "DECIMAL"   =>  "NUMBER",
            "BOOL"      =>  "DATE",
            "TIME"      =>  "DATE",
            "DATE"      =>  "DATE",
            "TIMESTAMP" =>  "DATE",
            "TEXT"      =>  "CLOB",
            "BLOB"      =>  "BLOB"
        ),

        "DB2" => array(
            "STRING"    =>  "VARCHAR",
            "INTEGER"   =>  "INTEGER",
            "FLOAT"     =>  "REAL",
            "DECIMAL"   =>  "INTEGER",
            "BOOL"      =>  "TIME",
            "TIME"      =>  "DATE",
            "DATE"      =>  "DATE",
            "TIMESTAMP" =>  "TIMESTAMP",
            "TEXT"      =>  "CLOB",
            "BLOB"      =>  "BLOB"
        ),

        "DBLIB" => array(
            "STRING"    =>  "NVARCHAR",
            "INTEGER"   =>  "INTEGER",
            "FLOAT"     =>  "FLOAT",
            "DECIMAL"   =>  "BIT",
            "BOOL"      =>  "TIME",
            "TIME"      =>  "DATETIME",
            "DATE"      =>  "DATE",
            "TIMESTAMP" =>  "TIMESTAMP",
            "TEXT"      =>  "NVARCHAR",
            "BLOB"      =>  "BLOB"
        ),

        "SQLITE" => array(
            "STRING"    =>  "TEXT",
            "INTEGER"   =>  "INTEGER",
            "FLOAT"     =>  "REAL",
            "DECIMAL"   =>  "INTEGER",
            "BOOL"      =>  "TEXT",
            "TIME"      =>  "TEXT",
            "DATE"      =>  "TEXT",
            "TIMESTAMP" =>  "TEXT",
            "TEXT"      =>  "TEXT",
            "BLOB"      =>  "BLOB"
        )

    );

    static private $column_patterns = array(

        "MYSQL"      => "`%s` %s%s%s%s %s%s%s%s%s",
        "POSTGRESQL" => "%s %s%s%s%s %s%s%s%s%s",
        "ORACLE"     => "%s %s%s%s%s %s%s%s%s%s",
        "DB2"        => "%s %s%s%s%s %s%s%s%s%s",
        "DBLIB"      => "%s %s%s%s%s %s%s%s%s%s",
        "SQLITE"     => "`%s` %s%s %s%s%s%s"

    );

    /**
     * Constructor
     *
     * @param   string  $name   User name
     * @param   string  $type   User password
     */
    final public function __construct(string $name, string $type) {

        if ( empty($name) ) throw new DatabaseException("Invalid column's name");

        if ( empty($type) OR !in_array($type, self::$supported_column_types) ) throw new DatabaseException("Invalid column's type");

        $this->name = $name;

        $this->type = strtoupper($type);

    }

    final public function length(int $length) {

        $this->length = $length;

        return $this;

    }

    final public function unsigned() {

        $this->unsigned = true;

        return $this;

    }

    final public function zerofill() {

        $this->zerofill = true;

        return $this;

    }

    final public function charset(string $charset) {

        $this->charset = $charset;

        return $this;

    }

    final public function collate($collate) {

        $this->collate = $collate;

        return $this;

    }

    final public function notNull() {

        $this->notNull = true;

        return $this;

    }

    final public function defaultValue($value) {

        $this->defaultValue = $value;

        return $this;

    }

    final public function autoincrement() {

        $this->autoincrement = true;

        return $this;

    }

    final public function unique() {

        $this->unique = true;

        return $this;

    }

    final public function primaryKey() {

        $this->primaryKey = true;

        return $this;

    }

    final public function getColumnName() {

        return $this->name;

    }

    final public function getColumnType(string $type, string $model) {

        return self::$supported_column_conversions[$model][$type];

    }

    final public function getColumnDefinition(string $model) {

        switch ($model) {

            case ("MYSQL"):
            case ("MYSQLI"):
            case ("MYSQL_PDO"):

                $definition = $this->mysqlColumnDefinition();

                break;

            case ("POSTGRESQL"):

                $definition = $this->postgresqlColumnDefinition();

                break;

            case ("DB2"):

                $definition = $this->oracleColumnDefinition();

                break;

            case ("DBLIB_PDO"):

                $definition = $this->db2ColumnDefinition();

                break;

            case ("ORACLE_PDO"):

                $definition = $this->dblibColumnDefinition();

                break;

            case ("SQLITE_PDO"):

                $definition = $this->sqliteColumnDefinition();

                break;

            default:

                throw new DatabaseException("Unsupported database model");
                
            break;

        }

        return $definition;

    }

    private function mysqlColumnDefinition() {

        $length = is_null($this->length) ? null : "(".intval($this->length).")";

        $attr_1 = is_null($this->unsigned) ? (is_null($this->charset) ? null : $this->charset) : ' UNSIGNED';

        $attr_2 = is_null($this->zerofill) ? (is_null($this->collate) ? null : $this->collate) : ' ZEROFILL';

        $notNull = is_null($this->notNull) ? null : ' NOT NULL';

        $defaultValue = is_null($this->defaultValue) ? null : ' DEFAULT '.$this->defaultValue;

        $autoincrement = is_null($this->autoincrement) ? null : ' AUTO_INCREMENT';
                
        $unique = is_null($this->unique) ? null : ' UNIQUE';

        $primaryKey = is_null($this->primaryKey) ? null : ' PRIMARY KEY';

        return sprintf($self::$column_patterns['MYSQL'],
            $this->name,
            self::$supported_column_conversions['MYSQL'][$this->type],
            $length,
            $attr_1,
            $attr_2,
            $notNull,
            $defaultValue,
            $autoincrement,
            $unique,
            $primaryKey
        );

    }

    private function postgresqlColumnDefinition() {

        $length = is_null($this->length) ? null : "(".intval($this->length).")";

        $attr_1 = is_null($this->unsigned) ? (is_null($this->charset) ? null : $this->charset) : ' UNSIGNED';

        $attr_2 = is_null($this->zerofill) ? (is_null($this->collate) ? null : $this->collate) : ' ZEROFILL';

        $notNull = is_null($this->notNull) ? null : ' NOT NULL';

        $defaultValue = is_null($this->defaultValue) ? null : ' DEFAULT '.$this->defaultValue;

        $autoincrement = is_null($this->autoincrement) ? null : ' AUTO_INCREMENT';
                
        $unique = is_null($this->unique) ? null : ' UNIQUE';

        $primaryKey = is_null($this->primaryKey) ? null : ' PRIMARY KEY';

        return sprintf($self::$column_patterns['POSTGRESQL'],
            $this->name,
            self::$supported_column_conversions['POSTGRESQL'][$this->type],
            $length,
            $attr_1,
            $attr_2,
            $notNull,
            $defaultValue,
            $autoincrement,
            $unique,
            $primaryKey
        );

    }
    
    private function oracleColumnDefinition() {

        $length = is_null($this->length) ? null : "(".intval($this->length).")";

        $attr_1 = is_null($this->unsigned) ? (is_null($this->charset) ? null : $this->charset) : ' UNSIGNED';

        $attr_2 = is_null($this->zerofill) ? (is_null($this->collate) ? null : $this->collate) : ' ZEROFILL';

        $notNull = is_null($this->notNull) ? null : ' NOT NULL';

        $defaultValue = is_null($this->defaultValue) ? null : ' DEFAULT '.$this->defaultValue;

        $autoincrement = is_null($this->autoincrement) ? null : ' AUTO_INCREMENT';
                
        $unique = is_null($this->unique) ? null : ' UNIQUE';

        $primaryKey = is_null($this->primaryKey) ? null : ' PRIMARY KEY';

        return sprintf($self::$column_patterns['ORACLE'],
            $this->name,
            self::$supported_column_conversions['ORACLE'][$this->type],
            $length,
            $attr_1,
            $attr_2,
            $notNull,
            $defaultValue,
            $autoincrement,
            $unique,
            $primaryKey
        );

    }
    
    private function db2ColumnDefinition() {

        $length = is_null($this->length) ? null : "(".intval($this->length).")";

        $attr_1 = is_null($this->unsigned) ? (is_null($this->charset) ? null : $this->charset) : ' UNSIGNED';

        $attr_2 = is_null($this->zerofill) ? (is_null($this->collate) ? null : $this->collate) : ' ZEROFILL';

        $notNull = is_null($this->notNull) ? null : ' NOT NULL';

        $defaultValue = is_null($this->defaultValue) ? null : ' DEFAULT '.$this->defaultValue;

        $autoincrement = is_null($this->autoincrement) ? null : ' AUTO_INCREMENT';
                
        $unique = is_null($this->unique) ? null : ' UNIQUE';

        $primaryKey = is_null($this->primaryKey) ? null : ' PRIMARY KEY';

        return sprintf($self::$column_patterns['DB2'],
            $this->name,
            self::$supported_column_conversions['DB2'][$this->type],
            $length,
            $attr_1,
            $attr_2,
            $notNull,
            $defaultValue,
            $autoincrement,
            $unique,
            $primaryKey
        );

    }
    
    private function dblibColumnDefinition() {

        $length = is_null($this->length) ? null : "(".intval($this->length).")";

        $attr_1 = is_null($this->unsigned) ? (is_null($this->charset) ? null : $this->charset) : ' UNSIGNED';

        $attr_2 = is_null($this->zerofill) ? (is_null($this->collate) ? null : $this->collate) : ' ZEROFILL';

        $notNull = is_null($this->notNull) ? null : ' NOT NULL';

        $defaultValue = is_null($this->defaultValue) ? null : ' DEFAULT '.$this->defaultValue;

        $autoincrement = is_null($this->autoincrement) ? null : ' AUTO_INCREMENT';
                
        $unique = is_null($this->unique) ? null : ' UNIQUE';

        $primaryKey = is_null($this->primaryKey) ? null : ' PRIMARY KEY';

        return sprintf($self::$column_patterns['DBLIB'],
            $this->name,
            self::$supported_column_conversions['DBLIB'][$this->type],
            $length,
            $attr_1,
            $attr_2,
            $notNull,
            $defaultValue,
            $autoincrement,
            $unique,
            $primaryKey
        );

    }
    
    private function sqliteColumnDefinition() {

        $attr_1 = is_null($this->unsigned) ? (is_null($this->collate) ? null : " COLLATE ".$this->collate) : ' UNSIGNED';

        $notNull = is_null($this->notNull) ? null : ' NOT NULL';

        $defaultValue = is_null($this->defaultValue) ? null : ' DEFAULT '.$this->defaultValue;

        $unique = is_null($this->unique) ? null : ' UNIQUE';

        $primaryKey = is_null($this->primaryKey) ? null : ' PRIMARY KEY';

        return sprintf($self::$column_patterns['SQLITE'],
            $this->name,
            self::$supported_column_conversions['SQLITE'][$this->type],
            $attr_1,
            $notNull,
            $defaultValue,
            $unique,
            $primaryKey
        );

    }

}
