<?php

class PostgresqlDatabaseTest extends \Comodojo\Database\Tests\Db {

    public function setUp() {

        $this->db = new \Comodojo\Database\Database(
            'POSTGRESQL',
            '127.0.0.1',
            5432,
            'comodojo',
            'postgres',
            ''
        );

    }

}