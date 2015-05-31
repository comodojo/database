<?php namespace Comodojo\Database\Tests;

use \Comodojo\Exception\DatabaseException;
use \Comodojo\Database\Database;
use \Exception;

class Db extends \PHPUnit_Framework_TestCase {

    public function testConnect() {

        $this->assertInstanceOf('\Comodojo\Database\Database', $this->db);

    }
    
    public function testSetFetch() {
        
        $db = $this->db->fetch('ASSOC');
        
        $this->assertInstanceOf('\Comodojo\Database\Database', $db);
        
    }

}
