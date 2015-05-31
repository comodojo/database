<?php

class SqliteDatabaseTest extends \Comodojo\Database\Tests\Db {

    public function setUp() {

        $this->db = new \Comodojo\Database\Database(
            'SQLITE_PDO',
            'localhost',
            1,
            realpath(dirname(__FILE__)).'/../tmp/comodojo',
            'root',
            ''
        );

    }

}