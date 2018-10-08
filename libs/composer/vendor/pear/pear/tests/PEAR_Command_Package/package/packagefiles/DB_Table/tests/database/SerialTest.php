<?php
#require_once 'PHPUnit/TestCase.php';
#require_once 'PHPUnit2/Framework/TestCase.php';
#require_once 'DB/Table/Database.php';
require_once 'DatabaseTest.php';

#class SerialTest extends PHPUnit_TestCase {
#class SerialTest extends PHPUnit2_Framework_TestCase {
class SerialTest extends DatabaseTest {

     var $insert = false;
#
     function setUp() 
     {
         parent::setUp();

         $serial_string = serialize($this->db);
         unset($this->db);
         $this->db = unserialize($serial_string);

     }

    function testGetTable1()
    {
        // Test get of entire $table property
        print "\n" . ">testGetTable1";
        $db =& $this->db;
        $table = $db->getTable();
        if (PEAR::isError($table)) {
            $success = false;
            print $table->getMessage();
        } else {
            $success = is_array($table);
            if (!$success) {
                print "Table is not an array in testGetTable1";
            }
        }
        $this->assertTrue($success);
    }
 
    function testGetPrimaryKey1()
    {
        // Test get of entire $primary_key property
        print "\n" . ">testGetPrimaryKey1";
        $db =& $this->db;
        $primary_key = $db->getPrimaryKey();
        if (PEAR::isError($primary_key)) {
            $success = false;
            print $primary_key->getMessage();
            $this->assertTrue($success);
            return;
        } else {
            $success = is_array($primary_key);
            if (!$success) {
                print "PrimaryKey is not an array in testGetPrimaryKey1";
                $this->assertTrue($success);
                return;
            } else {
                $this->assertEquals($primary_key, $this->primary_key);
            }
        }
    }
 
    function testGetRef1() 
    {
        print "\n" . ">testGetRef1";
        $db =& $this->db;
        $ref = $db->getRef();
        if (PEAR::isError($ref)) {
            $success = false;
            print $ref->getMessage();
            $this->assertTrue($success);
            return;
        } else {
            $success = is_array($ref);
            if (!$success) {
                print "\nRef is not an array in testGetRef1";
                $this->assertTrue($success);
                return;
            } else {
                $this->assertEquals($ref, $this->ref);
            }
        }
    }

    function testGetRefTo1() 
    {
        print "\n" . ">testGetRefTo1";
        $db =& $this->db;
        $ref_to = $db->getRefTo();
        if (PEAR::isError($ref_to)) {
            $success = false;
            print $ref_to->getMessage();
            $this->assertTrue($success);
            return;
        } else {
            $success = is_array($ref_to);
            if (!$success) {
                print "\n" . "RefTo is not an array in testGetRefTo1";
                $this->assertTrue($success);
                return;
            } else {
                $this->assertEquals($ref_to, $this->ref_to);
            }
        }
    }

    function testGetLink1() 
    {
        print "\n" . ">testGetLink1";
        $db =& $this->db;
        $link = $db->getLink();
        if (PEAR::isError($link)) {
            $success = false;
            print $link->getMessage();
            $this->assertTrue($success);
            return;
        } else {
            $success = is_array($link);
            if (!$success) {
                print "\n" . 'Link is not an array in testGetLink1';
                $this->assertTrue($success);
                return;
            } else {
                $this->assertEquals($link, $this->link);
            }
        }
    }

    function testGetCol1() 
    {
        // Test get of entire column property
        print "\n" . ">testGetCol1";
        $db =& $this->db;
        $col = $db->getCol();
        /*
        foreach ($col as $column => $tables) {
            print "\n" . "$column" . implode(', ', $tables);
        }
        */
        $this->assertEquals($col, $this->col);
    }

    function testGetForeignCol1() 
    {
        // Test get of entire column property
        print "\n" . ">testGetForeignCol1";
        $db =& $this->db;
        $foreign_col = $db->getForeignCol();
        $this->assertEquals($foreign_col, $this->foreign_col);
    }

}

?>
