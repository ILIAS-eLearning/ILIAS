<?php
#require_once 'PHPUnit/TestCase.php';
require_once 'PHPUnit2/Framework/TestCase.php';
require_once 'DB/Table/Database.php';

#class DatbaseBaseTest extends PHPUnit_TestCase {
class DatabaseTest extends PHPUnit2_Framework_TestCase {

    var $insert  = true;

    var $name    = null;
    var $conn    = null;
    var $db      = null;
    var $db_conn = null;
    var $verbose = null;
    var $fetchmode_assoc = null;
    var $fetchmode_order = null;

    function setUp() 
    {
        // Create DB_Table_Database object $db and insert data
        if ($this->insert) {
            require 'db1/insert.php';
        } else {
            require 'db1/define.php';
        }
        $db->setTableSubclassPath('db1');

        $this->name    = $db_name;
        $this->conn    =& $conn;
        $this->db      =& $db;
        $this->db_conn = $db_conn;
        $this->verbose = $verbose;

        if ($this->db->backend == 'mdb2') {
            $this->fetchmode_assoc = MDB2_FETCHMODE_ASSOC;
            $this->fetchmode_order = MDB2_FETCHMODE_ORDERED;
        } else {
            $this->fetchmode_assoc = DB_FETCHMODE_ASSOC;
            $this->fetchmode_order = DB_FETCHMODE_ORDERED;
        }

        // Copy expected values of properties of $db
        foreach ($properties as $property_name) {
            $this->$property_name = $$property_name;
        }

        // Copy arrays containing contents of tables of $db
        if ($this->insert) {
            foreach ($table_arrays as $table_name => $array) {
                $this->$table_name = $array;
            }
        } 
    }

    function tearDown() {
        if ($this->insert) {
            if (!$this->db_conn) {
               // print "\nDropping Database";
               $this->conn->query("DROP DATABASE {$this->name}");
            } else {
               $tables = $this->db->getTable();
               foreach ($tables as $table) {
                   $name = $table->table;
                   $this->conn->query("DROP Table $name");
               }
               $this->conn->query("DROP Table DataFile");
               $this->conn->query("DROP Table Person_seq");
               $this->conn->query("DROP Table Address_seq");
               $this->conn->query("DROP Table Phone_seq");
            }
            // print "\nDisconnecting";
            $this->conn->disconnect();
        }
    }

    function print_result($result, $name) {
        if ($this->verbose > 1) {
            if ($name) {
                print "\nContents of $name";
            }
            foreach ($result as $row) {
                $s = array();
                foreach ($row as $key => $value){
                    $s[] = "$value";
                }
                print "\n" . implode(', ',$s);
            }
        }
    }

}

?>

