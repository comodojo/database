<?php namespace Comodojo\Database\Tests;

use \Comodojo\Exception\DatabaseException;
use \Comodojo\Database\EnhancedDatabase;
use \Comodojo\Database\QueryBuilder\Column;
use \Exception;

class Create extends \PHPUnit_Framework_TestCase {

    public function testCreate() {

        $this->db->autoClean();

        $this->db->tablePrefix('test_')->table('names')->drop(true);

        $names = $this->db->tablePrefix('test_')
        	->table('names')
        	->column(Column::create('id','INTEGER')->unsigned()->autoIncrement()->primaryKey())
        	->column(Column::create('firstname','STRING')->length(64)->notNull())
        	->column(Column::create('lastname','STRING')->length(64)->defaultValue(null))
        	->create(true);
	
		$this->assertInstanceOf('\Comodojo\Database\QueryResult', $names);

    }

}
