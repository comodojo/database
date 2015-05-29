<?php namespace Comodojo\Database\Tests;

use \Comodojo\Exception\DatabaseException;
use \Comodojo\Database\EnhancedDatabase;
use \Comodojo\Database\QueryBuilder\Column;
use \Exception;

class EnhancedDatabase extends \PHPUnit_Framework_TestCase {

    public function testCreate() {

        $peoples = $this->db->tablePrefix('test_')
        	->table('peoples')
        	->column(Column::create('id','INTEGER')->unsigned()->autoIncrement()->primaryKey())
        	->column(Column::create('firstname','STRING')->length(64)->notNull())
        	->column(Column::create('lastname','STRING')->length(64)->defaultValue(null))
        	->create(true);
	
		$this->assertInstanceOf('\Comodojo\Database\QueryResult', $peoples);

    }
    
    public function testDrop() {
        
        $result = $this->db->tablePrefix('test_')->table('peoples')->drop();
        
        $this->assertInstanceOf('\Comodojo\Database\QueryResult', $result);
        
    }
    
    public function testMultipleCreate() {

        $this->db->autoClean();

        $peoples = $this->db->tablePrefix('test_')
        	->table('peoples')
        	->column(Column::create('id','INTEGER')->unsigned()->autoIncrement()->primaryKey())
        	->column(Column::create('firstname','STRING')->length(64)->notNull())
        	->column(Column::create('lastname','STRING')->length(64)->defaultValue(null))
        	->create(true, 'InnoDB');
	
		$this->assertInstanceOf('\Comodojo\Database\QueryResult', $peoples);
		
		$planets = $this->db->tablePrefix('test_')
        	->table('planets')
        	->column(Column::create('id','INTEGER')->unsigned()->autoIncrement()->primaryKey())
        	->column(Column::create('name','STRING')->length(64)->notNull())
        	->create(true, 'InnoDB');
        	
        $this->assertInstanceOf('\Comodojo\Database\QueryResult', $planets);

    }
    
    public function testInsert() {
        
        $this->db->autoClean();
        
        $result = $this->db->tablePrefix('test_')
        	->table('peoples')
        	->keys(array('firstname', 'lastname'))
        	->values(array('ARTHUR','DENT'))
        	->values(array('FORD','PREFECT'))
        	->values(array('ZAPHOD','BEEBLEBROX'))
        	->values(array('Tricia','Mc Millan'))
        	->values(array('MARVIN',null))
        	->store();
        	
        $this->assertInstanceOf('\Comodojo\Database\QueryResult', $result);
        	
        $result = $this->db->tablePrefix('test_')
        	->table('planets')
        	->keys('name')
        	->values('Earth')
        	->values('Magrathea')
        	->values('Vogsphere')
        	->values('Lamuella')
        	->values('Brequinda')
        	->store();
        
        $this->assertInstanceOf('\Comodojo\Database\QueryResult', $result);
        
    }
    
    public function testGet() {
        
        $this->db->autoClean();
        
        $result = $this->db->tablePrefix('test_')
        	->table('peoples')
        	->keys(array('firstname', 'lastname'))
        	->get();
        	
        $this->assertInstanceOf('\Comodojo\Database\QueryResult', $result);
        	
        $result = $this->db->tablePrefix('test_')
        	->table('planets')
        	->keys('*')
        	->get();
        
        $this->assertInstanceOf('\Comodojo\Database\QueryResult', $result);
        
    }
    
    public function testTruncate() {
        
        $result = $this->db->tablePrefix('test_')->table('planets')->truncate();
        
        $this->assertInstanceOf('\Comodojo\Database\QueryResult', $result);
        
    }

}
