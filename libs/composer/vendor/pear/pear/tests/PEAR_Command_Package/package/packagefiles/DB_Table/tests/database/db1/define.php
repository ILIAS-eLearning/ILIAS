<?php
#-----------------------------------------------------------------------
# Define a relational database for people, phone numbers, and addresses
#-----------------------------------------------------------------------

// Database class
require_once 'DB/Table/Database.php';

// Create DB/MDB2 connection
require 'config.php';

// Construct DB_Table objects

$Person = new DB_Table($conn, 'Person');
$Person->col['PersonID'] = array('type' => 'integer', 'require' =>true);
$Person->col['FirstName'] = array('type' => 'char', 'size' => 32, 'require' =>true);
$Person->col['MiddleName'] = array('type' => 'char', 'size' => 32);
$Person->col['LastName'] = array('type' => 'char', 'size' => 64, 'require' =>true);
$Person->col['NameSuffix'] = array('type' => 'char', 'size' => 16);
$Person->idx['PersonID'] = array('cols' => 'PersonID', 'type' => 'primary');
$Person->idx['Name'] = array(
   'cols' =>array('LastName', 'FirstName', 'MiddleName', 'NameSuffix'),
   'type' => 'normal');
$Person->auto_inc_col = 'PersonID';

$Address = new DB_Table($conn, 'Address');
$Address->col['AddressID'] = array('type' => 'integer', 'require' =>true);
$Address->col['Building'] = array('type' => 'char', 'size' => 16);
$Address->col['Street'] = array('type' => 'char', 'size' => 64, 'default' => 'AnyStreet');
$Address->col['UnitType'] = array('type' => 'char', 'size' => 16);
$Address->col['Unit'] = array('type' => 'char', 'size' => 16);
$Address->col['City'] = array('type' => 'char', 'size' => 64, 'default' => 'AnyCity');
$Address->col['StateAbb'] = array('type' => 'char', 'size' => 2);
$Address->col['ZipCode'] = array('type' => 'char', 'size' => 16);
$Address->idx['AddressID'] = array('cols' => 'AddressID', 'type' => 'primary');
$Address->idx['StreetAddress'] = array('cols' =>array('City', 'Street', 'Building', 'Unit'), 'type' => 'unique');
$Address->auto_inc_col = 'AddressID';

$Phone = new DB_Table($conn, 'Phone');
$Phone->col['PhoneID'] = array('type' => 'integer', 'require' =>true);
$Phone->col['PhoneNumber'] = array('type' => 'char', 'size' => 16, 'require' => true);
$Phone->idx['PhoneID'] = array('cols' => 'PhoneID', 'type' => 'primary');
$Phone->idx['PhoneNumber'] = array('cols' => 'PhoneNumber', 'type' => 'unique');
$Phone->auto_inc_col = 'PhoneID';

$PersonAddress = new DB_Table($conn, 'PersonAddress');
$PersonAddress->col['PersonID2'] = array('type' => 'integer', 'default' => 50);
$PersonAddress->col['AddressID'] = array('type' => 'integer', 'require' => true);
$PersonAddress->idx['PersonID2'] = array('cols' => 'PersonID2', 'type' => 'normal');
$PersonAddress->idx['AddressID'] = array('cols' => 'AddressID', 'type' => 'normal');

/*
$PersonPhone = new DB_Table($conn, 'PersonPhone');
$PersonPhone->col['PersonID'] = array('type' => 'integer', 'default' => 75);
$PersonPhone->col['PhoneID'] = array('type' => 'integer', 'require' => true);
$PersonPhone->idx['PersonID'] = array('cols' => 'PersonID', 'type' => 'normal');
$PersonPhone->idx['PhoneID'] = array('cols' => 'PhoneID', 'type' => 'normal');
*/

require_once 'db1/PersonPhone_Table.php';
$PersonPhone = new PersonPhone_Table($conn, 'PersonPhone');

$Street = new DB_Table($conn, 'Street');
$Street->col['Street'] = array('type' => 'char', 'size' => 64);
$Street->col['City'] = array('type' => 'char', 'size' => 64);
$Street->col['StateAbb'] = array('type' => 'char', 'size' => 2);
$Street->col['Sunny'] = array('type' => 'boolean');
$Street->idx['Street'] = array('cols' => array('Street', 'City', 'StateAbb'), 
                               'type' => 'primary');

// Instantiate new DB_Table_Database object 
$db = new DB_Table_Database($conn, $db_name);

// Add all tables to it
$db->addTable($Person);
$db->addTable($Address);
$db->addTable($Phone);
$db->addTable($PersonAddress);
$db->addTable($PersonPhone);
$db->addTable($Street);

// Add references to DB_Table_Database object
$db->addRef('PersonAddress', 'PersonID2', 'Person', null, 'cascade', 'cascade');
$db->addRef('PersonAddress', 'AddressID', 'Address', null, 'cascade', 'cascade');
$db->addRef('PersonPhone', 'PersonID', 'Person', null, 'cascade', 'cascade');
$db->addRef('PersonPhone', 'PhoneID', 'Phone', null, 'cascade', 'cascade');
$db->addRef('Address', array('Street', 'City', 'StateAbb'),
             'Street',  array('Street', 'City', 'StateAbb'),
             'cascade', 'cascade');

// Add links PersonPhone and PersonAddress
$db->addAllLinks();

// Enable foreign key validation by PHP layer
$db->setCheckFKey(true);

# List of tables in database
$table = array($Person, $Address, $Phone, $PersonAddress, $PersonPhone, $Street);

// Expected property array values after finalization
$primary_key = array();
$primary_key['Person']  = 'PersonID';
$primary_key['Address'] = 'AddressID';
$primary_key['Phone']   = 'PhoneID';
$primary_key['PersonAddress'] = null;
$primary_key['PersonPhone'] = null;
$primary_key['Street'] = array('Street', 'City', 'StateAbb');

$table_subclass = array();
$table_subclass['Person']  = null;
$table_subclass['Address'] = null;
$table_subclass['Phone']   = null;
$table_subclass['PersonAddress'] = null;
$table_subclass['PersonPhone'] = 'PersonPhone_Table';
$table_subclass['Street'] = null;

$ref = array();
$ref['PersonAddress'] = array();
$ref['PersonPhone']   = array();
$ref['PersonAddress']['Person'] = array(
      'fkey' => 'PersonID2',   'rkey' => 'PersonID',
      'on_delete' => 'cascade', 'on_update' => 'cascade');
$ref['PersonAddress']['Address'] = array(
      'fkey' => 'AddressID',   'rkey' => 'AddressID',
      'on_delete' => 'cascade', 'on_update' => 'cascade');
$ref['PersonPhone']['Person'] = array(
      'fkey' => 'PersonID',   'rkey' => 'PersonID',
      'on_delete' => 'cascade', 'on_update' => 'cascade');
$ref['PersonPhone']['Phone'] = array(
      'fkey' => 'PhoneID',   'rkey' => 'PhoneID',
      'on_delete' => 'cascade', 'on_update' => 'cascade');
$ref['Address']['Street'] = array(
      'fkey' =>array('Street', 'City', 'StateAbb'),
      'rkey' =>array('Street', 'City', 'StateAbb'),
      'on_delete' => 'cascade', 'on_update' => 'cascade');

$col = array();
$col['PersonID'] = array('Person', 'PersonPhone');
$col['FirstName'] = array('Person');
$col['MiddleName'] = array('Person');
$col['LastName'] = array('Person');
$col['NameSuffix'] = array('Person');
$col['AddressID'] = array('Address', 'PersonAddress');
$col['Building'] = array('Address');
$col['UnitType'] = array('Address');
$col['Unit'] = array('Address');
$col['ZipCode'] = array('Address');
$col['PhoneID'] = array('Phone', 'PersonPhone');
$col['PhoneNumber'] = array('Phone');
$col['Street'] = array('Address', 'Street');
$col['City'] = array('Address', 'Street');
$col['StateAbb']  = array('Address', 'Street');
$col['PersonID2'] = array('PersonAddress');
$col['Sunny'] = array('Street');

$foreign_col = array();
$foreign_col['PersonID2'] = array('PersonAddress');
$foreign_col['AddressID'] = array('PersonAddress');
$foreign_col['PersonID']  = array('PersonPhone');
$foreign_col['PhoneID']   = array('PersonPhone');
$foreign_col['Street']    = array('Address');
$foreign_col['City']      = array('Address');
$foreign_col['StateAbb']  = array('Address');

$ref_to = array();
$ref_to['Person']  = array('PersonAddress', 'PersonPhone');
$ref_to['Address'] = array('PersonAddress');
$ref_to['Phone']   = array('PersonPhone');
$ref_to['Street']  = array('Address');

$link = array();
$link['Person']  = array();
$link['Phone']   = array();
$link['Address'] = array();
$link['Person']['Address'] = array('PersonAddress');
$link['Address']['Person'] = array('PersonAddress');
$link['Person']['Phone']   = array('PersonPhone');
$link['Phone']['Person']   = array('PersonPhone');

$properties = array('table', 'primary_key', 'table_subclass',
                    'ref', 'col', 'foreign_col', 'ref_to', 'link');

#-----------------------------------------------------------------------
# Schema for array of example data in data.php
#-----------------------------------------------------------------------

$DataFile = new DB_Table($conn, 'DataFile');
$DataFile->col['FirstName'] = array('type' => 'varchar', 'size' => 255);
$DataFile->col['MiddleName'] = array('type' => 'varchar', 'size' => 255);
$DataFile->col['LastName'] = array('type' => 'varchar', 'size' => 255);
$DataFile->col['NameSuffix'] = array('type' => 'varchar', 'size' => 255);
$DataFile->col['Building'] = array('type' => 'varchar', 'size' => 255);
$DataFile->col['Street'] = array('type' => 'varchar', 'size' => 255);
$DataFile->col['UnitType'] = array('type' => 'varchar', 'size' => 255);
$DataFile->col['Unit'] = array('type' => 'varchar', 'size' => 255);
$DataFile->col['City'] = array('type' => 'varchar', 'size' => 255);
$DataFile->col['StateAbb'] = array('type' => 'varchar', 'size' => 255);
$DataFile->col['ZipCode'] = array('type' => 'varchar', 'size' => 255);
$DataFile->col['PhoneNumber'] = array('type' => 'varchar', 'size' => 255);

?>
