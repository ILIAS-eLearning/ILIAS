--TEST--
package command, bug #11317
--SKIPIF--
<?php
if (!getenv('PHP_PEAR_RUNTESTS')) {
    echo 'skip';
}
?>
--FILE--
<?php

require_once dirname(dirname(__FILE__)) . DIRECTORY_SEPARATOR . 'setup.php.inc';
$savedir = getcwd();
chdir(dirname(__FILE__) . '/packagefiles/DB_Table');
$ret = $command->run('package', array('nocompress' => true), array());
$phpunit->assertErrors(array(
    array('package' => 'PEAR_PackageFile_v2', 'message' => 'Channel validator warning: field "date" - Release Date "2007-06-14" is not today'),
    array('package' => 'PEAR_PackageFile_v2', 'message' => 'Channel validator warning: field "date" - Release Date "2007-06-14" is not today'),
),
'after');
$fakelog->getLog();
$p = new PEAR_PackageFile($config);
$pf = $p->fromTgzFile(dirname(__FILE__) . '/packagefiles/DB_Table/DB_Table-1.5.0RC3.tar', PEAR_VALIDATE_DOWNLOADING);
$phpunit->showAll();
$phpunit->assertEquals('- Added complete documentation in the PEAR manual.
- new DB_Table_Base class added. This is a parent class for DB_Table and DB_Table_Database that contains methods and properties common to both classes. 
- The DB_Table::select*() and DB_Table::buildSQL() methods, which are now inherited from the DB_Table_Base class, now accept either a query array or (as before) a key of the $sql property array as a first argument.
- Added DB_Table_Database::onDeleteAction() and DB_Table_Database::onUpdateAction() methods, which implement referentially triggered actions (e.g., cascading deletes). This code had previously been part of the DB_Table_Database insert() and update() methods.
- Changed behavior of DB_Table::insert() and DB_Table::update() method for a DB_Table object that is part of a DB_Table_Database instance: If a parent DB_Table_Database object exists, these methods can now validate foreign keys and implement ON DELETE and ON UPDATE actions, if these behaviors are enabled in the parent DB_Table_Database object. The behaviors of the DB_Table and DB_Table_Database insert and update methods are now identical. (This is a BC break with 1.5.0RC1 and 1.5.0RC2 beta releases, but not with earlier stable releases.)
- Disable automatic foreign key validation by default (BC break with releases 1.5.0RC1 and 1.5.0RC2).
- Added buildFilter() method to the DB_Table_Base class. This a simplified version of the DB_Table_Database::buildFilter() method of previous 1.5.0RC* releases. (BC break with 1.5.0RC1 and 1.5.0RC2)
- Added a private DB_Table_Database::_buildForeignKeyFilter() for more specialized uses of the old buildFilter() method, which are used internally to construct queries.
- Changed return value of DB_Table_Database::validForeignKey on failure from boolean false to PEAR_Error. Changed related error codes.
- Changed \'autoinc\' element of XML output to \'autoincrement\' for consistency with MDB2 XML schema. 
- Simplified unit tests for DB_Table_Database by adding a parent DatabaseTest unit test class.', $pf->getNotes(), 'notes');
echo 'tests done';
?>
--CLEAN--
<?php
unlink(dirname(__FILE__) . '/packagefiles/DB_Table/DB_Table-1.5.0RC3.tar');
require_once dirname(dirname(__FILE__)) . '/teardown.php.inc';
?>
--EXPECT--
tests done
