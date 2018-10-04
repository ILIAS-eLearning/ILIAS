<?php
require_once 'DatabaseTest.php';

class XMLTest extends DatabaseTest {

    var $insert = false;

    function testToXML() {
        if ($this->verbose > -1) {
            print "\n" . ">Test toXML() method";
        }
        $xml_string = $this->db->toXML();
        if ($this->verbose > 1) {
            print "\n" . $xml_string;
        }
    }
    
    function testToAndFromXML() {
        if ($this->verbose > -1) {
            print "\n" . ">Test round-trip toXML() -> fromXML";
        }
        $first_string = $this->db->toXML();
        $db_obj =& DB_Table_Database::fromXML($first_string,$this->conn);
        $second_string = $db_obj->toXML();
        $this->assertEquals($second_string, $first_string);
    }

}

?>
