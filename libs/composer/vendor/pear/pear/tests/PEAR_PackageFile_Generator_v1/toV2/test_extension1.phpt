--TEST--
PEAR_PackageFile_Generator_v1->toV2(), test extension src package
--SKIPIF--
<?php
if (!getenv('PHP_PEAR_RUNTESTS')) {
    echo 'skip';
}
?>
--FILE--
<?php

require_once dirname(dirname(__FILE__)) . DIRECTORY_SEPARATOR . 'setup.php.inc';

$pf = &$parser->parse(implode('', file(dirname(__FILE__) . DIRECTORY_SEPARATOR . 'packagefiles' .
    DIRECTORY_SEPARATOR . 'test_extension.xml')), dirname(__FILE__) . DIRECTORY_SEPARATOR . 'packagefiles' .
    DIRECTORY_SEPARATOR . 'test_extension.xml');

$generator = &$pf->getDefaultGenerator();
$e = &$generator->toV2();
$phpunit->assertNoErrors('errors');
$egen = &$e->getDefaultGenerator();
$xml = $egen->toXml();

$phpunit->assertEquals('<?xml version="1.0" encoding="UTF-8"?>
<package packagerversion="' . $egen->getPackagerVersion() . '" version="2.0" xmlns="http://pear.php.net/dtd/package-2.0" xmlns:tasks="http://pear.php.net/dtd/tasks-1.0" xmlns:xsi="http://www.w3.org/2001/XMLSchema-instance" xsi:schemaLocation="http://pear.php.net/dtd/tasks-1.0
http://pear.php.net/dtd/tasks-1.0.xsd
http://pear.php.net/dtd/package-2.0
http://pear.php.net/dtd/package-2.0.xsd">
 <name>SQLite</name>
 <channel>pecl.php.net</channel>
 <summary>SQLite database bindings</summary>
 <description>SQLite is a C library that implements an embeddable SQL database engine.
Programs that link with the SQLite library can have SQL database access
without running a separate RDBMS process.
This extension allows you to access SQLite databases from within PHP.
Windows binary for PHP 4.3 is available from:
http://snaps.php.net/win32/PECL_4_3/php_sqlite.dll
**Note that this extension is built into PHP 5 by default**
 </description>
 <lead>
  <name>Wez Furlong</name>
  <user>wez</user>
  <email>wez@php.net</email>
  <active>yes</active>
 </lead>
 <lead>
  <name>Marcus Brger</name>
  <user>helly</user>
  <email>helly@php.net</email>
  <active>yes</active>
 </lead>
 <lead>
  <name>Ilia Alshanetsky</name>
  <user>iliaa</user>
  <email>ilia@php.net</email>
  <active>yes</active>
 </lead>
 <developer>
  <name>Tal Peer</name>
  <user>tal</user>
  <email>tal@php.net</email>
  <active>yes</active>
 </developer>
 <date>' . date('Y-m-d') . '</date>
 <time>' . $e->getTime() . '</time>
 <version>
  <release>1.0.3</release>
  <api>1.0.3</api>
 </version>
 <stability>
  <release>stable</release>
  <api>stable</api>
 </stability>
 <license uri="http://www.php.net/license">PHP</license>
 <notes>
Upgraded libsqlite to version 2.8.14

&quot;Fixed&quot; the bug where calling sqlite_query() with multiple SQL statements in a
single string would not work if you looked at the return value.  The fix for
this case is to use the new sqlite_exec() function instead. (Stas)
 </notes>
 <contents>
  <dir name="/">
   <dir name="libsqlite">
    <dir name="src">
     <file name="attach.c" role="src" />
     <file name="auth.c" role="src" />
     <file name="btree.c" role="src" />
     <file name="btree.h" role="src" />
     <file name="btree_rb.c" role="src" />
     <file name="build.c" role="src" />
     <file name="copy.c" role="src" />
     <file name="date.c" role="src" />
     <file name="delete.c" role="src" />
     <file name="encode.c" role="src" />
     <file name="expr.c" role="src" />
     <file name="func.c" role="src" />
     <file name="hash.c" role="src" />
     <file name="hash.h" role="src" />
     <file name="insert.c" role="src" />
     <file name="main.c" role="src" />
     <file name="opcodes.c" role="src" />
     <file name="opcodes.h" role="src" />
     <file name="os.c" role="src" />
     <file name="os.h" role="src" />
     <file name="pager.c" role="src" />
     <file name="pager.h" role="src" />
     <file name="parse.c" role="src" />
     <file name="parse.h" role="src" />
     <file name="parse.y" role="src" />
     <file name="pragma.c" role="src" />
     <file name="printf.c" role="src" />
     <file name="random.c" role="src" />
     <file name="select.c" role="src" />
     <file name="sqlite.h.in" role="src" />
     <file name="sqlite.w32.h" role="src" />
     <file name="sqliteInt.h" role="src" />
     <file name="sqlite_config.w32.h" role="src" />
     <file name="table.c" role="src" />
     <file name="tokenize.c" role="src" />
     <file name="trigger.c" role="src" />
     <file name="update.c" role="src" />
     <file name="util.c" role="src" />
     <file name="vacuum.c" role="src" />
     <file name="vdbe.c" role="src" />
     <file name="vdbe.h" role="src" />
     <file name="vdbeaux.c" role="src" />
     <file name="vdbeInt.h" role="src" />
     <file name="where.c" role="src" />
    </dir> <!-- /libsqlite/src -->
    <file name="README" role="doc" />
    <file name="VERSION" role="src" />
   </dir> <!-- /libsqlite -->
   <dir name="tests">
    <file name="blankdb.inc" role="test" />
    <file name="sqlite_001.phpt" role="test" />
    <file name="sqlite_002.phpt" role="test" />
    <file name="sqlite_003.phpt" role="test" />
    <file name="sqlite_004.phpt" role="test" />
    <file name="sqlite_005.phpt" role="test" />
    <file name="sqlite_006.phpt" role="test" />
    <file name="sqlite_007.phpt" role="test" />
    <file name="sqlite_008.phpt" role="test" />
    <file name="sqlite_009.phpt" role="test" />
    <file name="sqlite_010.phpt" role="test" />
    <file name="sqlite_011.phpt" role="test" />
    <file name="sqlite_012.phpt" role="test" />
    <file name="sqlite_013.phpt" role="test" />
    <file name="sqlite_014.phpt" role="test" />
    <file name="sqlite_015.phpt" role="test" />
    <file name="sqlite_016.phpt" role="test" />
    <file name="sqlite_017.phpt" role="test" />
   </dir> <!-- /tests -->
   <file name="config.m4" role="src" />
   <file name="CREDITS" role="doc" />
   <file name="php_sqlite.def" role="src" />
   <file name="php_sqlite.h" role="src" />
   <file name="README" role="doc" />
   <file name="sqlite.c" role="src" />
   <file name="sqlite.dsp" role="src" />
   <file name="sqlite.php" role="doc" />
   <file name="TODO" role="doc" />
  </dir> <!-- / -->
 </contents>
 <dependencies>
  <required>
   <php>
    <min>4.0.0</min>
   </php>
   <pearinstaller>
    <min>1.4.0b1</min>
   </pearinstaller>
  </required>
 </dependencies>
 <providesextension>SQLite</providesextension>
 <extsrcrelease />
 <changelog>
  <release>
   <version>
    <release>1.0.2</release>
    <api>1.0.2</api>
   </version>
   <stability>
    <release>stable</release>
    <api>stable</api>
   </stability>
   <date>2004-01-17</date>
   <license uri="http://www.php.net/license">PHP</license>
   <notes>Upgraded libsqlite to version 2.8.11
Fixed crash bug in module shutdown
Fixed crash with empty queries
Fixed column name mangling bug


   </notes>
  </release>
  <release>
   <version>
    <release>1.0</release>
    <api>1.0</api>
   </version>
   <stability>
    <release>stable</release>
    <api>stable</api>
   </stability>
   <date>2003-06-21</date>
   <license uri="http://www.php.net/license">PHP</license>
   <notes>
Added:
	sqlite_udf_encode_binary() and sqlite_udf_decode_binary() for
	handling binary data in UDF callbacks.
	sqlite_popen() for persistent db connections.
	sqlite_unbuffered_query() for high performance queries.
	sqlite_last_error() returns error code from last operation.
	sqlite_error_string() returns description of error.
	sqlite_create_aggregate() for registering aggregating SQL functions.
	sqlite_create_function() for registering regular SQL functions.
	sqlite_fetch_string() for speedy access to first column of result sets.
	sqlite_fetch_all() to receive all rowsets from a query as an array.
	iteration interface
	sqlite_query() functions accept resource/query string in either order,
	for compatibility with mysql and postgresql extensions.
Fixed some build issues for thread-safe builds.
Increase the default busy timeout interval to 60 seconds.
   </notes>
  </release>
 </changelog>
</package>
', $xml, 'xml');
echo 'tests done';
?>
--CLEAN--
<?php
require_once dirname(dirname(__FILE__)) . '/teardown.php.inc';
?>
--EXPECT--
tests done
