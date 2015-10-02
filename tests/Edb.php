<?php namespace Comodojo\Database\Tests;

use \Comodojo\Exception\DatabaseException;
use \Comodojo\Database\EnhancedDatabase;
use \Comodojo\Database\QueryBuilder\Column;
use \Exception;

class Edb extends \PHPUnit_Framework_TestCase {

    public function testCreate() {

        $people = $this->db->tablePrefix('test_')
            ->table('people')
            ->column(Column::create('id','INTEGER')->unsigned()->autoIncrement()->primaryKey())
            ->column(Column::create('firstname','STRING')->length(64)->notNull())
            ->column(Column::create('lastname','STRING')->length(64)->defaultValue(null))
            ->create(true);
    
        $this->assertInstanceOf('\Comodojo\Database\QueryResult', $people);

    }
    
    public function testDrop() {
        
        $result = $this->db->tablePrefix('test_')->table('people')->drop();
        
        $this->assertInstanceOf('\Comodojo\Database\QueryResult', $result);
        
    }
    
    public function testMultipleCreate() {

        $this->db->autoClean();

        $people = $this->db->tablePrefix('test_')
            ->table('people')
            ->column(Column::create('id','INTEGER')->unsigned()->autoIncrement()->primaryKey())
            ->column(Column::create('firstname','STRING')->length(64)->notNull())
            ->column(Column::create('lastname','STRING')->length(64)->defaultValue(null))
            ->create(true, 'InnoDB');
    
        $this->assertInstanceOf('\Comodojo\Database\QueryResult', $people);
        
        $planets = $this->db->tablePrefix('test_')
            ->table('planets')
            ->column(Column::create('id','INTEGER')->unsigned()->autoIncrement()->primaryKey())
            ->column(Column::create('name','STRING')->length(64)->notNull())
            ->create(true, 'InnoDB');
            
        $this->assertInstanceOf('\Comodojo\Database\QueryResult', $planets);

        $planets = $this->db->tablePrefix('test_')
            ->table('starships')
            ->column(Column::create('id','INTEGER')->unsigned()->autoIncrement()->primaryKey())
            ->column(Column::create('name','STRING')->length(64)->notNull())
            ->column(Column::create('speed','INTEGER')->defaultValue(null))
            ->column(Column::create('capacity','INTEGER')->defaultValue(null))
            ->create(true, 'InnoDB');
            
        $this->assertInstanceOf('\Comodojo\Database\QueryResult', $planets);

    }
    
    public function testInsert() {
        
        $this->db->autoClean();
        
        $result = $this->db->tablePrefix('test_')
            ->table('people')
            ->keys(array('firstname', 'lastname'))
            ->values(array('ARTHUR','DENT'))
            ->store();        

        $this->assertInstanceOf('\Comodojo\Database\QueryResult', $result);

        $this->assertSame(1, $result->getInsertId());

        $result = $this->db->tablePrefix('test_')
            ->table('people')
            ->keys(array('firstname', 'lastname'))
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

        $this->assertSame(5, $result->getAffectedRows());

        $result = $this->db->tablePrefix('test_')
            ->table('starships')
            ->keys(array('name','speed','capacity'))
            ->values(array('Billion Year Bunker',100,50))
            ->values(array('Business End',100,30))
            ->values(array('Heart of Gold',1000,50))
            ->values(array('Starship Titanic',10,500))
            ->values(array('Golgafrincham B-Ark',20,10))
            ->store();
        
        $this->assertInstanceOf('\Comodojo\Database\QueryResult', $result);

        $this->assertSame(5, $result->getAffectedRows());

    }
    
    public function testGet() {
        
        $this->db->autoClean();
        
        $result = $this->db->tablePrefix('test_')
            ->table('people')
            ->keys(array('firstname', 'lastname'))
            ->get();
            
        $this->assertInstanceOf('\Comodojo\Database\QueryResult', $result);

        $this->assertCount(5, $result->getData());

        $this->assertSame(5, $result->getLength());
            
        $result = $this->db->tablePrefix('test_')
            ->table('planets')
            ->keys('*')
            ->get();
        
        $this->assertInstanceOf('\Comodojo\Database\QueryResult', $result);

        $this->assertCount(5, $result->getData());
        
    }

    public function testOrderBy() {
        
        $this->db->autoClean();
        
        $result = $this->db->tablePrefix('test_')
            ->table('planets')
            ->keys('name')
            ->orderBy('name','ASC')
            ->get();
            
        $this->assertInstanceOf('\Comodojo\Database\QueryResult', $result);

        $data = $result->getData();

        $this->assertCount(5, $data);

        $this->assertSame(5, $result->getLength());
            
        $this->assertEquals("Brequinda", $data[0]['name']);
        
    }

    public function testGroupBy() {
        
        $this->db->autoClean();
        
        $result = $this->db->tablePrefix('test_')
            ->table('people')
            ->keys(array('firstname', 'lastname'))
            ->groupBy(array('firstname', 'lastname'))
            ->get();
            
        $this->assertInstanceOf('\Comodojo\Database\QueryResult', $result);

        $data = $result->getData();

        $this->assertEquals("ARTHUR", $data[0]['firstname']);

        $this->assertEquals("FORD", $data[1]['firstname']);

        $result = $this->db->tablePrefix('test_')
            ->table('starships')
            ->keys(array('speed','COUNT::id=>count'))
            ->groupBy('speed')
            ->get();

        $this->assertSame(4, $result->getLength());

        $data = $result->getData();

        foreach ($data as $value) {
            
            switch ($value['speed']) {
                
                case 10:
                    
                    $this->assertEquals(1, $value['count']);

                    break;

                case 20:
                    
                    $this->assertEquals(1, $value['count']);

                    break;

                case 100:
                    
                    $this->assertEquals(2, $value['count']);

                    break;

                case 1000:
                    
                    $this->assertEquals(1, $value['count']);

                    break;
            }

        }

        $result = $this->db->tablePrefix('test_')
            ->table('starships')
            ->keys(array('speed','capacity','COUNT::id=>count'))
            ->groupBy(array('speed','capacity'))
            ->get();

        $this->assertSame(5, $result->getLength());
        
    }
    
    public function testTruncate() {

        $this->db->autoClean();
        
        $result = $this->db->tablePrefix('test_')->table('planets')->truncate();
        
        $this->assertInstanceOf('\Comodojo\Database\QueryResult', $result);

        $result = $this->db->tablePrefix('test_')->table('starships')->truncate();
        
        $this->assertInstanceOf('\Comodojo\Database\QueryResult', $result);
        
    }

}
