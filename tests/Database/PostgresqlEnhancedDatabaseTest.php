<?php

class PostgresqlEnhancedDatabaseTest extends \Comodojo\Database\Tests\Edb {

    public function setUp() {

        $this->db = new \Comodojo\Database\EnhancedDatabase(
            'POSTGRESQL',
            '127.0.0.1',
            5432,
            'comodojo',
            'postgres',
            ''
        );

    }

}