<?php
require_once 'DatabaseTest.php';

/**
 * Tests _quote(), buildFilter() and buildSQL string processing methods
 */
class SQLTest extends DatabaseTest {

    var $insert = false;

    function testQuoteString()
    {
        if ($this->verbose > -1) {
            print "\n" . ">testQuoteString";
        }
        $result = $this->conn->quote("This is not a number");
        if ($this->verbose > 0) {
            print "\n" . $result;
        }
        $this->assertEquals($result, "'This is not a number'");
    } 

    function testQuoteInteger()
    {
        if ($this->verbose > -1) {
            print "\n" . ">testQuoteInteger";
        }
        $result = $this->db->quote(256);
        if ($this->verbose > 0) {
            print "\n" . $result;
        }
        $this->assertEquals($result, "256");
    }
 
    function testQuoteFloat()
    {
        if ($this->verbose > -1) {
            print "\n" . ">testQuoteFloat";
        }
        $result = $this->db->quote(2.56);
        if ($this->verbose > 0) {
            print "\n" . $result;
        }
        $this->assertEquals($result, "2.56");
    }
 
    function testQuoteBooleanFalse()
    {
        if ($this->verbose > -1) {
            print "\n" . ">testQuoteBooleanFalse";
        }
        $result = $this->db->quote(false);
        if ($this->verbose > 0) {
            print "\n" . $result;
        }
        $this->assertEquals($result, "0");
    }
 
    function testQuoteNull()
    {
        if ($this->verbose > -1) {
            print "\n" . ">testQuoteNull";
        }
        $result = $this->db->quote(null);
        if ($this->verbose > 0) {
            print "\n" . $result;
        }
        $this->assertEquals($result, "NULL");
    }

    function testBuildFilter1() 
    {
        if ($this->verbose > -1) {
            print "\n" . ">testBuildFilter1";
        }
        $data['col1'] = 1;
        $data['col2'] = false;
        $data['col3'] = 'anyold string';
        $result = $this->db->buildFilter($data);
        $expect = "col1 = 1 AND col2 = 0 AND col3 = 'anyold string'";
        if ($this->verbose > 0) {
            print "\n" . $result;
        }
        $this->assertEquals($result, $expect);
    }

    function testBuildFilter2() 
    {
        if ($this->verbose > -1) {
            print "\n" . ">testBuildFilter2";
        }
        $data['col1'] = 1;
        $data['col2'] = false;
        $data['col3'] = 'anyold string';
        $data['col4'] = null;
        $result = $this->db->buildFilter($data);
        $expect = '';
        if ($this->verbose > 0) {
            print "\n" . $result;
        }
        $this->assertEquals($result, $expect);
    }

    function testBuildFKeyFilter1() 
    {
        if ($this->verbose > -1) {
            print "\n" . ">testBuildFKeyFilter1";
        }
        $data['col1'] = 1;
        $data['col2'] = false;
        $data['col3'] = 'anyold string';
        $data_key = 'col3';
        $result = $this->db->_buildFKeyFilter($data, $data_key);
        $expect = "col3 = 'anyold string'";
        if ($this->verbose > 0) {
            print "\n" . $result;
        }
        $this->assertEquals($result, $expect);
    }

    function testBuildFKeyFilter2() 
    {
        if ($this->verbose > -1) {
            print "\n" . ">testBuildFKeyFilter3";
        }
        $data['col1'] = 1;
        $data['col2'] = false;
        $data['col3'] = 'anyold string';
        $data_key = array('col1', 'col3');
        $result = $this->db->_buildFKeyFilter($data, $data_key);
        $expect = "col1 = 1 AND col3 = 'anyold string'";
        if ($this->verbose > 0) {
            print "\n" . $result;
        }
        $this->assertEquals($result, $expect);
    }

    function testBuildFKeyFilter4() 
    {
        if ($this->verbose > -1) {
            print "\n" . ">testBuildFKeyFilter4";
        }
        $data['col1'] = 1;
        $data['col2'] = false;
        $data['col3'] = 'anyold string';
        $data['col4'] = null;
        $data_key = 'col3';
        $filt_key = 'COL3';
        $result = $this->db->_buildFKeyFilter($data, $data_key, $filt_key);
        $expect = "COL3 = 'anyold string'";
        if ($this->verbose > 0) {
            print "\n" . $result;
        }
        $this->assertEquals($result, $expect);
    }

    function testBuildFKeyFilter5() 
    {
        if ($this->verbose > -1) {
            print "\n" . ">testBuildFKeyFilter5";
        }
        $data['col1'] = 1;
        $data['col2'] = false;
        $data['col3'] = 'anyold string';
        $data['col4'] = null;
        $data_key = array('col1', 'col3');
        $filt_key = array('COL1', 'COL3');
        $result = $this->db->_buildFKeyFilter($data, $data_key, $filt_key);
        $expect = "COL1 = 1 AND COL3 = 'anyold string'";
        if ($this->verbose > 0) {
            print "\n" . $result;
        }
        $this->assertEquals($result, $expect);
    }

    function testBuildFKeyFilter7() 
    {
        if ($this->verbose > -1) {
            print "\n" . ">testBuildFKeyFilter7";
        }
        $data['col1'] = 1;
        $data['col2'] = false;
        $data['col3'] = 'anyold string';
        $data['col4'] = null;
        $result = $this->db->_buildFKeyFilter($data, null, null, 'partial');
        $expect = "col1 = 1 AND col2 = 0 AND col3 = 'anyold string'";
        if ($this->verbose > 0) {
            print "\n" . $result;
        }
        $this->assertEquals($result, $expect);
    }

    /*
    function testBuildFKeyFilter8() 
    {
        if ($this->verbose > -1) {
            print "\n" . ">testBuildFKeyFilter8";
        }
        $data['col1'] = 1;
        $data['col2'] = false;
        $data['col3'] = 'anyold string';
        $data['col4'] = null;
        $result = $this->db->_buildFKeyFilter($data, null, null, 'full');
        if (PEAR::isError($result)) {
            $this->assertTrue(true);
        } else {
            if ($this->verbose > 0) {
                print "\n" . $result;
                print "\n" . $result;
            }
        }
    }
    */

    function testBuildFKeyFilter9() 
    {
        if ($this->verbose > -1) {
            print "\n" . ">testBuildFKeyFilter9";
        }
        $data['col1'] = 1;
        $data['col2'] = false;
        $data['col3'] = 'anyold string';
        $data['col4'] = null;
        $data_key = 'col4';
        $result = $this->db->_buildFKeyFilter($data, $data_key);
        $expect = '';
        if ($this->verbose > 0) {
            print "\n" . $result;
        }
        $this->assertEquals($result, $expect);
    }

    function testBuildFKeyFilter10() 
    {
        if ($this->verbose > -1) {
            print "\n" . ">testBuildFKeyFilter10";
        }
        $data['col1'] = 1;
        $data['col2'] = false;
        $data['col3'] = 'anyold string';
        $data['col4'] = null;
        $data_key = array('col2', 'col4');
        $result = $this->db->_buildFKeyFilter($data, $data_key);
        $expect = '';
        if ($this->verbose > 0) {
            print "\n" . $result;
        }
        $this->assertEquals($result, $expect);
    }

    function testBuildFKeyFilter11() 
    {
        if ($this->verbose > -1) {
            print "\n" . ">testBuildFKeyFilter11";
        }
        $data['col1'] = 1;
        $data['col2'] = false;
        $data['col3'] = 'anyold string';
        $data['col4'] = null;
        $data_key = array('col1', 'col4');
        $filt_key = array('COL1', 'COL4');
        $result = $this->db->_buildFKeyFilter($data, $data_key, $filt_key);
        $expect = '';
        if ($this->verbose > 0) {
            print "\n" . $result;
        }
        $this->assertEquals($result, $expect);
    }

    function testBuildSQL1() 
    {
        if ($this->verbose > -1) {
            print "\n" . ">testBuildSQL1";
        }
        $db =& $this->db;
        $query = array(
           'select' => 'FirstName, LastName, Building, Street, City',
           'from'   => 'Person, Address',
           'where'  => 'Person.PersonID = Address.PersonID2');
        $db->sql['test2'] = $query;
        $result = $db->buildSQL($query, "City = 'MINNETONKA'", 'City');
        if ($this->verbose > 0) {
            print "\n" . $result;
        }
        $this->assertTrue(true);
    }
       
    function testBuildSQL2() 
    {
        if ($this->verbose > -1) {
            print "\n" . ">testBuildSQL2";
        }
        $db =& $this->db;
        $query = array(
           'select' => 'Street, count(Building)',
           'from'   => 'Address',
           'group'  => 'Street',
           'having' => "City = 'MINNETONKA'",
           'order'  => 'Street' );
        $result = $db->buildSQL($query);
        if ($this->verbose > 0) {
            print "\n" . $result;
        }
        $this->assertTrue(true);
    }

    function testBuildSQL3() 
    {
        if ($this->verbose > -1) {
            print "\n" . ">testBuildSQL3";
        }
        $db =& $this->db;
        $result = $db->buildSQL(1);
        if (PEAR::isError($result)){
           print "\n" . $result->getMessage();
           $this->assertTrue(true);
        } else {
           $this->assertTrue(false);
        }
    }

    function testBuildSQL4() 
    {
        if ($this->verbose > -1) {
            print "\n" . ">testBuildSQL4";
        }
        $db =& $this->db;
        $result = $db->buildSQL('not_a_key');
        if (PEAR::isError($result)){
           print "\n" . $result->getMessage();
           $this->assertTrue(true);
        } else {
           $this->assertTrue(false);
        }
    }

}

?>
