<?php
require_once 'DatabaseTest.php';

class SelectTest extends DatabaseTest 
{

    function testSelect1()
    {
        print "\n" . ">testSelect1";

        // Loop over tables
        $success = true;
        $tables = $this->db->getTable();
        foreach ($tables as $table_name => $table_obj) {
            $sql = array('select'    => '*',
                         'from'      => $table_name,
                         'fetchmode' => $this->fetchmode_assoc );
            $result = $this->db->select($sql);
            if (PEAR::isError($result)){
                print $result->getMessage();
                $this->assertTrue(false);
                return;
            }
            $n_result = count($result);
            $n_table  = count($this->$table_name);
            if ($this->verbose > 0) {
                if ($this->verbose == 1) {
                    print "\n" . "$table_name $n_result $n_table";
                } else {
                    print "\nTable $table_name:";
                    print "\n\nQuery:\n" . $this->db->buildSQL($sql) . "\n";
                    foreach ($result as $row){
                        $s = array();
                        foreach ($row as $key => $value){
                            $s[] = "$key => $value";
                        }
                        print "\n" . implode(', ', $s);
                    }
                    print "\n\n" . "Count $n_result, $n_table \n";
                } 
            }
            if ($n_result != $n_table) {
                $success = false;
            }
        } // end loop over tables
        $this->assertTrue($success);
        
    }

    function testSelect2()
    {
        print "\n" . ">testSelect2";

        $cols = array();
        $cols[] = 'LastName';
        $cols[] = 'FirstName';
        $cols[] = 'PhoneNumber';
        $cols[] = 'Building';
        $cols[] = 'Street';
        $cols[] = 'City';
        $cols[] = 'ZipCode';
        $report = $this->db->autoJoin($cols);
        if (PEAR::isError($report)) {
            print "\n" . $report->getMessage();
            print_r($this->db->getLink());
            $this->assertTrue(false);
            return;
        }
        $report['order'] = 'LastName';
        $filter = "Street.City = 'MINNETONKA'";
        $result = $this->db->select($report, $filter);
        if (PEAR::isError($result)) {
            print "\n" . $result->getMessage();
            $this->assertTrue(false);
            return;
        }
        if ($this->verbose > 0) {
            print "\n\nQuery:\n" . 
                  $this->db->buildSQL($report, $filter) . "\n";
            foreach ($result as $row){
                $s = array();
                foreach ($row as $key => $value){
                    $s[] = (string) $value;
                }
                print "\n" . implode(',', $s);
            }
            print "\n";
        }
        $this->assertEquals(count($result), 10);
    }

    function testSelect3()
    {
        print "\n" . ">testSelect3";

        $cols = array();
        $cols[] = 'LastName';
        $cols[] = 'FirstName';
        $cols[] = 'PhoneNumber';
        $cols[] = 'Building';
        $cols[] = 'Street';
        $cols[] = 'City';
        $cols[] = 'ZipCode';
        $report = $this->db->autoJoin($cols);
        if (PEAR::isError($report)) {
            print "\n" . $report->getMessage();
            $this->assertTrue(false);
            return;
        }
        $this->db->sql['report'] = $report;
        $result = $this->db->select('report', 
                                    "Street.City = 'MINNETONKA'",
                                    'FirstName');
        if (PEAR::isError($result)) {
            print "\n" . $result->getMessage();
            $this->assertTrue(false);
            return;
        }
        if ($this->verbose > 0) {
            print "\n\nQuery:\n" . 
                  $this->db->buildSQL('report', 
                                   "Street.City = 'MINNETONKA'",
                                   'FirstName') . "\n";
            foreach ($result as $row){
                $s = array();
                foreach ($row as $key => $value){
                    $s[] = (string) $value;
                }
                print "\n" . implode(',', $s);
            }
            print "\n";
        }
        $this->assertEquals(count($result), 10);
    }

    function testSelect4() 
    {
        if ($this->verbose > -1) {
            print "\n" . ">testSelect4";
        }
        $db =& $this->db;
        $result = $db->select(1);
        if (PEAR::isError($result)){
           print "\n" . $result->getMessage();
           $this->assertTrue(true);
        } else {
           $this->assertTrue(false);
        }
    }

    function testSelect5() 
    {
        if ($this->verbose > -1) {
            print "\n" . ">testSelect5";
        }
        $db =& $this->db;
        $result = $db->select('not_a_key');
        if (PEAR::isError($result)){
           print "\n" . $result->getMessage();
           $this->assertTrue(true);
        } else {
           $this->assertTrue(false);
        }
    }


    function testSelectResult1()
    {
        print "\n" . ">testSelectResult1";

        $cols = array();
        $cols[] = 'LastName';
        $cols[] = 'FirstName';
        $cols[] = 'PhoneNumber';
        $cols[] = 'Building';
        $cols[] = 'Street';
        $cols[] = 'City';
        $cols[] = 'ZipCode';
        $report = $this->db->autoJoin($cols);
        if (PEAR::isError($report)) {
            print "\n" . $report->getMessage();
            $this->assertTrue(false);
            return;
        }
        $report['order'] = 'LastName';
        $result = $this->db->selectResult($report, "Street.City = 'MINNETONKA'");
        if (PEAR::isError($result)) {
            print "\n" . $result->getMessage();
            $this->assertTrue(false);
            return;
        }
        if ($this->verbose > 0) {
            print "\n\nQuery:\n" . 
                  $this->db->buildSQL($report, "Street.City = 'MINNETONKA'")
                  . "\n";
            $i = 0;
            while ($row = $result->fetchRow()) {
                $s = array();
                foreach ($row as $key => $value){
                    $s[] = (string) $value;
                }
                print "\n" . implode(',', $s);
                $i = $i + 1;
            }
            print "\n";
        }
        $this->assertEquals($i,10);
    }

    function testSelectResult2()
    {
        print "\n" . ">testSelectResult2";

        $cols = array();
        $cols[] = 'LastName';
        $cols[] = 'FirstName';
        $cols[] = 'PhoneNumber';
        $cols[] = 'Building';
        $cols[] = 'Street';
        $cols[] = 'City';
        $cols[] = 'ZipCode';
        $report = $this->db->autoJoin($cols);
        if (PEAR::isError($report)) {
            print "\n" . $report->getMessage();
            $this->assertTrue(false);
            return;
        }
        $report['order'] = 'LastName';
        $this->db->sql['report'] = $report;
        $result = $this->db->selectResult('report',
                                          "Street.City = 'MINNETONKA'");
        if (PEAR::isError($result)) {
            print "\n" . $result->getMessage();
            $this->assertTrue(false);
            return;
        }
        $i = 0;
        if ($this->verbose > 0) {
            print "\n\nQuery:\n" . 
                  $this->db->buildSQL('report', "Street.City = 'MINNETONKA'")
                  . "\n";
            while ($row = $result->fetchRow()) {
                $s = array();
                foreach ($row as $key => $value){
                    $s[] = (string) $value;
                }
                print "\n" . implode(',', $s);
                $i = $i + 1;
            }
            print "\n";
        }
        $this->assertEquals($i,10);
    }

    function testSelectResult3() 
    {
        if ($this->verbose > -1) {
            print "\n" . ">testSelectResult3";
        }
        $db =& $this->db;
        $result = $db->selectResult(1);
        if (PEAR::isError($result)){
           print "\n" . $result->getMessage();
           $this->assertTrue(true);
        } else {
           $this->assertTrue(false);
        }
    }

    function testSelectResult4() 
    {
        if ($this->verbose > -1) {
            print "\n" . ">testSelectResult4";
        }
        $db =& $this->db;
        $result = $db->selectResult('not_a_key');
        if (PEAR::isError($result)){
           print "\n" . $result->getMessage();
           $this->assertTrue(true);
        } else {
           $this->assertTrue(false);
        }
    }


    function testSelectCount1()
    {
        print "\n" . ">testSelectCount1";

        $cols = array();
        $cols[] = 'LastName';
        $cols[] = 'FirstName';
        $cols[] = 'PhoneNumber';
        $cols[] = 'Building';
        $cols[] = 'Street';
        $cols[] = 'City';
        $cols[] = 'ZipCode';
        $report = $this->db->autoJoin($cols);
        if (PEAR::isError($report)) {
            print "\n" . $report->getMessage();
            $this->assertTrue(false);
            return;
        }
        $report['order'] = 'LastName';
        $result = $this->db->selectCount($report, "Street.City = 'MINNETONKA'");
        if (PEAR::isError($result)) {
            print "\n" . $result->getMessage();
            $this->assertTrue(false);
            return;
        }
        if ($this->verbose > 0) {
            print "\n\nQuery:\n" . 
                  $this->db->buildSQL($report, "Street.City = 'MINNETONKA'")
                  . "\n";
            print "\nCount = $result\n";
        }
        $this->assertEquals($result, '10');
    }

    function testSelectCount2()
    {
        print "\n" . ">testSelectCount2";

        $cols = array();
        $cols[] = 'LastName';
        $cols[] = 'FirstName';
        $cols[] = 'PhoneNumber';
        $cols[] = 'Building';
        $cols[] = 'Street';
        $cols[] = 'City';
        $cols[] = 'ZipCode';
        $report = $this->db->autoJoin($cols);
        if (PEAR::isError($report)) {
            print "\n" . $report->getMessage();
            $this->assertTrue(false);
            return;
        }
        $result = $this->db->selectCount($report,
                             "Street.City = 'EDEN PRAIRIE'");
        if (PEAR::isError($result)) {
            print "\n" . $result->getMessage();
            $this->assertTrue(false);
            return;
        }
        if ($this->verbose > 0) {
            print "\n\nQuery:\n" . 
                  $this->db->buildSQL($report,
                             "Street.City = 'EDEN PRAIRIE'") . "\n";
            print "\nCount = $result\n";
        }
        $this->assertEquals($result, '8');
    }

}

?>
