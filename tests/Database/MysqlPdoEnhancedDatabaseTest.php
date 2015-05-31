<?php

class MysqlPdoEnhancedDatabaseTest extends \Comodojo\Database\Tests\Edb {

    public function setUp() {

        $this->db = new \Comodojo\Database\EnhancedDatabase(
            'MYSQL_PDO',
            '127.0.0.1',
            3306,
            'comodojo',
            'root',
            ''
        );

    }

}