<?php

class SqliteEnhancedDatabaseTest extends \Comodojo\Database\Tests\Edb {

    public function setUp() {

        $this->db = new \Comodojo\Database\EnhancedDatabase(
            'SQLITE_PDO',
            'localhost',
            1,
            realpath(dirname(__FILE__)).'/../tmp/comodojo',
            'root',
            ''
        );

    }

}