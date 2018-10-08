<?php
require_once 'DatabaseTest.php';

class GetTest extends DatabaseTest {

    var $insert = false;

    function testGetTable1()
    {
        // Test get of entire $table property"
        if ($this->verbose > -1) {
            print "\n" . ">testGetTable1";
        }
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
 
    function testGetTable2()
    {
        // Test get of entire $table['Person'] property
        if ($this->verbose > -1) {
            print "\n" . ">testGetTable2";
        }
        $db =& $this->db;
        $table = $db->getTable('Person');
        if (PEAR::isError($table)) {
            $success = false;
            print $table->getMessage();
        } else {
            $success = is_a($table, 'DB_Table');
            if (!$success) {
                print "Table is not a DB_Table object in testGetTable2";
            }
        }
        $this->assertTrue($success);
    }
 
    function testGetTable3()
    {
        // Test get of invalid table name
        if ($this->verbose > -1) {
            print "\n" . ">testGetTable3";
        }
        $db =& $this->db;
        $table = $db->getTable('Thwack');
        if (PEAR::isError($table)) {
            $success = true;
            if ($this->verbose > 0) {
                print "\n".$table->getMessage();
            }
        } else {
            $success = false;
        }
        $this->assertTrue($success);
    }
 
    function testGetPrimaryKey1()
    {
        // Test get of entire $primary_key property
        if ($this->verbose > -1) {
            print "\n" . ">testGetPrimaryKey1";
        }
        $db =& $this->db;
        $primary_key = $db->getPrimaryKey();
        if (PEAR::isError($primary_key)) {
            $success = false;
            print "\n" . $primary_key->getMessage();
            $this->assertTrue($success);
            return;
        } else {
            $success = is_array($primary_key);
            if (!$success) {
                print "\nPrimaryKey is not an array in testGetPrimaryKey1";
                $this->assertTrue($success);
                return;
            } else {
                $this->assertEquals($primary_key, $this->primary_key);
            }
        }
    }
 
    function testGetPrimaryKey2()
    {
        // Test get of $primary_key['Person'] 
        if ($this->verbose > -1) {
            print "\n" . ">testGetPrimaryKey2";
        }
        $db =& $this->db;
        $primary_key = $db->getPrimaryKey('Person');
        if (PEAR::isError($primary_key)) {
            $success = false;
            print $primary_key->getMessage();
            $this->assertTrue($success);
            return;
        } else {
            $success = is_string($primary_key);
            if (!$success) {
                print "PrimaryKey['Person'] is not a string in testGetPrimaryKey2";
                $this->assertTrue($success);
                return;
            } else {
                $this->assertEquals($primary_key, $this->primary_key['Person']);
            }
        }
    }
 
    function testGetPrimaryKey3()
    {
        // Test get of $primary_key with invalid Table name
        if ($this->verbose > -1) {
            print "\n" . ">testGetPrimaryKey3";
        }
        $db =& $this->db;
        $primary_key = $db->getPrimaryKey('Thwack');
        if (PEAR::isError($primary_key)) {
            $success = true;
            if ($this->verbose > 0) {
                print "\n" . $primary_key->getMessage();
            }
        } else {
            $success = false;
        }
        $this->assertTrue($success);
   }

    function testTableSubclass1()
    {
        // Test get of entire $table_subclass property
        if ($this->verbose > -1) {
            print "\n" . ">testGetTableSubclass1";
        }
        $db =& $this->db;
        $table_subclass = $db->getTableSubclass();
        if (PEAR::isError($table_subclass)) {
            $success = false;
            print "\n" . $table_subclass->getMessage();
            $this->assertTrue($success);
            return;
        } else {
            $success = is_array($table_subclass);
            if (!$success) {
                print "\nTableSubclass is not an array in testGetTableSubclass1";
                $this->assertTrue($success);
                return;
            } else {
                $this->assertEquals($table_subclass, $this->table_subclass);
            }
        }
    }

    function testGetRef1() 
    {
        if ($this->verbose > -1) {
            print "\n" . ">testGetRef1";
        }
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

    function testGetRef2() 
    {
        // Test get of $ref['PersonAddress'], which should be an array
        if ($this->verbose > -1) {
            print "\n" . ">testGetRef2";
        }
        $db =& $this->db;
        $ref = $db->getRef('PersonAddress');
        if (PEAR::isError($ref)) {
            $success = false;
            print $ref->getMessage();
            $this->assertTrue($success);
            return;
        } else {
            $success = is_array($ref);
            if (!$success) {
                print "\nRef is not an array in testGetRef2";
                $this->assertTrue($success);
                return;
            } else {
                $this->assertEquals($ref, $this->ref['PersonAddress']);
            }
        }
    }

    function testGetRef3() 
    {
        // Test get of $ref['Person'], which should return null
        if ($this->verbose > -1) {
            print "\n" . ">testGetRef3";
        }
        $db =& $this->db;
        $ref = $db->getRef('Person');
        if (PEAR::isError($ref)) {
            $success = false;
            print "\n" . $ref->getMessage() . '- in GetRef3';
        } else {
            $success = is_null($ref);
        }
        $this->assertTrue($success);
    }

    function testGetRef4() 
    {
        // Test get of $ref['PersonAddress']['Person'], which should be an array
        if ($this->verbose > -1) {
            print "\n" . ">testGetRef4";
        }
        $db =& $this->db;
        $ref = $db->getRef('PersonAddress', 'Person');
        if (PEAR::isError($ref)) {
            $success = false;
            print "\n" . $ref->getMessage() . " - in testGetRef4";
            $this->assertTrue($success);
            return;
        } else {
            $success = is_array($ref);
            if (!$success) {
                print "\n" . "Ref is not an array in testGetRef4";
                $this->assertTrue($success);
                return;
            } else {
                $this->assertEquals($ref, $this->ref['PersonAddress']['Person']);
            }
        }
    }

    function testGetRefTo1() 
    {
        if ($this->verbose > -1) {
            print "\n" . ">testGetRefTo1";
        }
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

    function testGetRefTo2() 
    {
        // Test get of $ref_to['Person'], which should be an array
        if ($this->verbose > -1) {
            print "\n" . ">testGetRefTo2";
        }
        $db =& $this->db;
        $ref_to = $db->getRefTo('Person');
        if (PEAR::isError($ref_to)) {
            $success = false;
            print $ref_to->getMessage();
            $this->assertTrue($success);
            return;
        } else {
            $success = is_array($ref_to);
            if (!$success) {
                print "\n" . "RefTo is not an array in testGetRefTo2";
                $this->assertTrue($success);
                return;
            } else {
                $this->assertEquals($ref_to, $this->ref_to['Person']);
            }
        }
    }

    function testGetRefTo3() 
    {
        // Test get of $ref_to['PersonAddress'], which should return null
        if ($this->verbose > -1) {
            print "\n" . ">testGetRefTo3";
        }
        $db =& $this->db;
        $ref_to = $db->getRefTo('PersonAddress');
        if (PEAR::isError($ref_to)) {
            $success = false;
            print "\n" . $ref_to->getMessage()."- in GetRefTo3";
        } else {
            $success = is_null($ref_to);
        }
        $this->assertTrue($success);
    }

    function testGetLink1() 
    {
        if ($this->verbose > -1) {
            print "\n" . ">testGetLink1";
        }
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

    function testGetLink2() 
    {
        // Test get of $link['Person'], which should be an array
        if ($this->verbose > -1) {
            print "\n" . '>testGetLink2';
        }
        $db =& $this->db;
        $link = $db->getLink('Person');
        if (PEAR::isError($link)) {
            $success = false;
            print $link->getMessage();
            $this->assertTrue($success);
            return;
        } else {
            $success = is_array($link);
            if (!$success) {
                print "\n" . "Link is not an array in testGetLink2";
                $this->assertTrue($success);
                return;
            } else {
                $this->assertEquals($link, $this->link['Person']);
            }
        }
    }

    function testGetLink3() 
    {
        // Test get of $link['PersonAddress'], which should return null
        if ($this->verbose > -1) {
            print "\n" . '>testGetLink3';
        }
        $db =& $this->db;
        $link = $db->getLink('PersonAddress');
        if (PEAR::isError($link)) {
            $success = false;
            print "\n" . $link->getMessage()."- in GetLink3";
        } else {
            $success = is_null($link);
        }
        $this->assertTrue($success);
    }

    function testGetLink4() 
    {
        // Test get of $link['Person']['Address'], which should be an array
        if ($this->verbose > -1) {
            print "\n" . ">testGetLink4";
        }
        $db =& $this->db;
        $link = $db->getLink('Person', 'Address');
        if (PEAR::isError($link)) {
            $success = false;
            print "\n" . $link->getMessage() . " - in testGetLink4";
            $this->assertTrue($success);
            return;
        } else {
            $success = is_array($link);
            if (!$success) {
                print "\n" . "Link is not an array in testGetLink4";
                $this->assertTrue($success);
                return;
            } else {
                $this->assertEquals($link, $this->link['Person']['Address']);
            }
        }
    }

    function testGetCol1() 
    {
        // Test get of entire column property
        if ($this->verbose > -1) {
            print "\n" . ">testGetCol1";
        }
        $db =& $this->db;
        $col = $db->getCol();
        $this->assertEquals($col, $this->col);
    }

    function testGetCol2() 
    {
        // Test get of col['Building']
        if ($this->verbose > -1) {
            print "\n" . ">testGetCol2";
        }
        $db =& $this->db;
        $col = $db->getCol('Building');
        $this->assertEquals($col, $this->col['Building']);
    }

    function testGetForeignCol1() 
    {
        // Test get of entire column property
        if ($this->verbose > -1) {
            print "\n" . ">testGetForeignCol1";
        }
        $db =& $this->db;
        $foreign_col = $db->getForeignCol();
        $this->assertEquals($foreign_col, $this->foreign_col);
    }

    function testGetForeignCol2() 
    {
        // Test get of entire column property
        if ($this->verbose > -1) {
            print "\n" . ">testGetForeignCol2";
        }
        $db =& $this->db;
        $foreign_col = $db->getForeignCol('PersonID');
        $this->assertEquals($foreign_col, $this->foreign_col['PersonID']);
    }

    function testValidCol1()
    {
        // Test validCol('Building')
        if ($this->verbose > -1) {
            print "\n" . ">testValidCol1";
        }
        $db =& $this->db;
        $name = implode('.', $db->validCol('Building'));
        $this->assertEquals('Address.Building', $name);
    }

    function testValidCol1b()
    {
        // Test validCol('Building')
        if ($this->verbose > -1) {
            print "\n" . ">testValidCol1b";
        }
        $db =& $this->db;
        $from = array('Address');
        $name = implode('.', $db->validCol('City', $from));
        $this->assertEquals('Address.City', $name);
    }

    function testValidCol2()
    {
        if ($this->verbose > -1) {
            print "\n" . ">testValidCol2";
        }
        $db =& $this->db;
        $name = implode('.', $db->validCol('PersonID'));
        $this->assertEquals('Person.PersonID', $name);
    }

    function testValidCol2b()
    {
        if ($this->verbose > -1) {
            print "\n" . ">testValidCol2b";
        }
        $db =& $this->db;
        $from = array('PersonPhone');
        $name = implode('.', $db->validCol('PersonID', $from));
        $this->assertEquals('PersonPhone.PersonID', $name);
    }

    function testValidCol3()
    {
        if ($this->verbose > -1) {
            print "\n" . ">testValidCol3";
        }
        $db =& $this->db;
        $name = implode('.', $db->validCol('PersonID2'));
        $this->assertEquals('PersonAddress.PersonID2', $name);
    }

    function testValidCol4()
    {
        if ($this->verbose > -1) {
            print "\n" . ">testValidCol4";
        }
        $db =& $this->db;
        $name = implode('.', $db->validCol('Person.FirstName'));
        $this->assertEquals('Person.FirstName', $name);
    }

    function testValidCol5()
    {
        // validCol('Thwack.Building')
        if ($this->verbose > -1) {
            print "\n" . ">testValidCol5";
        }
        $db =& $this->db;
        $result = $db->validCol('Person.Thingy');
        $success = false;
        if (PEAR::isError($result)) {
            $success = true;
            if ($this->verbose > 0) {
                print "\n" . $result->getMessage();
            }
        }
        $this->assertTrue($success);
    }

    function testValidCol6()
    {
        // validCol('Thwack.Building')
        if ($this->verbose > -1) {
            print "\n" . ">testValidCol6";
        }
        $db =& $this->db;
        $result = $db->validCol('Thwack.Building');
        $success = false;
        if (PEAR::isError($result)) {
            $success = true;
            if ($this->verbose > 0) {
                print "\n" . $result->getMessage();
            }
        }
        $this->assertTrue($success);
    }

    function testValidCol7()
    {
        if ($this->verbose > -1) {
            print "\n" . ">testValidCol7";
        }
        $db =& $this->db;
        $result = $db->validCol('Street');
        if (PEAR::isError($result)) {
            print "\n" . $result->getMessage();
            $this->assertTrue(false);
        }
        $name = implode('.', $result);
        $this->assertEquals('Street.Street', $name);
    }

}

?>
