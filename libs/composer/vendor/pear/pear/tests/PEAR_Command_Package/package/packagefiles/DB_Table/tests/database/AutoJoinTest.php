<?php
#require_once 'PHPUnit/TestCase.php';
#require_once 'PHPUnit2/Framework/TestCase.php';
#require_once 'DB/Table/Database.php';
require_once 'DatabaseTest.php';

/**
 * Tests _quote(), buildFilter() and buildSQL string processing methods
 */
#class AutoJoinTest extends PHPUnit_TestCase {
#class AutoJoinTest extends PHPUnit2_Framework_TestCase {
class AutoJoinTest extends DatabaseTest {

    var $insert = false;

    function testJoin1() 
    {
        if ($this->verbose > -1) {
            print "\n" . ">testJoin1";
        }
        $db =& $this->db;
        $success = true;
       
        $cols = array(); 
        $cols[] = 'Street';
        $cols[] = 'FirstName';
        $cols[] = 'LastName';
        $cols[] = 'PhoneNumber';
        $cols[] = 'Building';
        $cols[] = 'City';
        $report = $db->autoJoin($cols);
        if (PEAR::isError($report)) {
            print "\n" . $report->getMessage();
            $this->assertTrue(false);
        }
        $result = $db->buildSQL($report, "City = 'MINNETONKA'");
        if (PEAR::isError($result)) {
            print "\n" . $result->getMessage();
            $this->assertTrue(false);
        } else {
            $expect = <<<EOT
SELECT Street.Street, Person.FirstName, Person.LastName, Phone.PhoneNumber, Address.Building, Street.City
FROM Street, Person, Phone, Address, PersonAddress, PersonPhone
WHERE ( Address.Street = Street.Street
  AND Address.City = Street.City
  AND Address.StateAbb = Street.StateAbb
  AND PersonAddress.PersonID2 = Person.PersonID
  AND PersonAddress.AddressID = Address.AddressID
  AND PersonPhone.PhoneID = Phone.PhoneID
  AND PersonPhone.PersonID = Person.PersonID )
  AND ( City = 'MINNETONKA' )
EOT;
            if ($this->verbose > 1) {
                print "\n" . $result;
            }
        }
        $this->assertEquals($result, $expect);
    }


    function testJoin2() 
    {
        if ($this->verbose > -1) {
            print "\n" . ">testJoin2";
        }
        $db =& $this->db;
        $success = true;
       
        $cols = array(); 
        $cols[] = 'PersonID';
        $cols[] = 'FirstName';
        $cols[] = 'LastName';
        $tables = array(); 
        $tables[] = 'PersonPhone'; 
        $report = $db->autoJoin($cols, $tables);
        if (PEAR::isError($report)) {
            print "\n" . $report->getMessage();
            $this->assertTrue(false);
        }
        $result = $db->buildSQL($report);
        if (PEAR::isError($result)) {
            print "\n" . $result->getMessage();
            $this->assertTrue(false);
        } else {
            $expect = <<<EOT
SELECT PersonPhone.PersonID, Person.FirstName, Person.LastName
FROM PersonPhone, Person
WHERE PersonPhone.PersonID = Person.PersonID
EOT;
            if ($this->verbose > 1) {
                print "\n" . $result;
            }
        }
        $this->assertEquals($result, $expect);
    }

    function testJoin3() 
    {
        if ($this->verbose > -1) {
            print "\n" . ">testJoin3";
        }
        $db =& $this->db;
        $success = true;

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
        $result = $db->buildSQL($report, "Street.City = 'MINNETONKA'");
        if (PEAR::isError($result)) {
            print "\n" . $result->getMessage();
            $this->assertTrue(false);
        } else {
            $expect = <<<EOT
SELECT Person.LastName, Person.FirstName, Phone.PhoneNumber, Address.Building, Street.Street, Street.City, Address.ZipCode
FROM Person, Phone, Address, Street, PersonPhone, PersonAddress
WHERE ( PersonPhone.PhoneID = Phone.PhoneID
  AND PersonPhone.PersonID = Person.PersonID
  AND PersonAddress.AddressID = Address.AddressID
  AND PersonAddress.PersonID2 = Person.PersonID
  AND Address.Street = Street.Street
  AND Address.City = Street.City
  AND Address.StateAbb = Street.StateAbb )
  AND ( Street.City = 'MINNETONKA' )
EOT;
            if ($this->verbose > 1) {
                print "\n" . $result;
            }
        }
        $this->assertEquals($result, $expect);

    }

}

?>
