<?php

class MysqliDatabaseTest extends \Comodojo\Database\Tests\Db {

    public function setUp() {

        $this->db = new \Comodojo\Database\Database(
            'MYSQLI',
            '127.0.0.1',
            3306,
            'comodojo',
            'root',
            ''
        );

    }

}