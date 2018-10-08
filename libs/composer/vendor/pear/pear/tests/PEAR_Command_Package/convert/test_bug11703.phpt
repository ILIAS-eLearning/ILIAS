--TEST--
convert command, bug #11703 [pear convert and package.xml with optional dependencies fails]
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
chdir($temp_path);
copy(dirname(__FILE__) . DIRECTORY_SEPARATOR . 'packagefiles' . DIRECTORY_SEPARATOR . 'bug11703.xml',
    $temp_path . DIRECTORY_SEPARATOR . 'package.xml');
$e = $command->run('convert', array(), array());

$phpunit->assertNoErrors('1');
$phpunit->assertEquals(array (
  0 =>
  array (
    'info' => 'Wrote new version 2.0 package.xml to ".' . DIRECTORY_SEPARATOR .
        'package2.xml"',
    'cmd' => 'no command',
  ),
), $fakelog->getLog(), 'log 1');

$pkg = new PEAR_PackageFile($config);
$pf = &$pkg->fromPackageFile($temp_path . DIRECTORY_SEPARATOR . 'package2.xml', PEAR_VALIDATE_NORMAL);
$gen = &$pf->getDefaultGenerator();
$contents = implode('', file($temp_path . DIRECTORY_SEPARATOR . 'package2.xml'));

$phpunit->assertEquals('<?xml version="1.0" encoding="UTF-8"?>
<package packagerversion="' . $gen->getPackagerVersion() . '" version="2.0" xmlns="http://pear.php.net/dtd/package-2.0" xmlns:tasks="http://pear.php.net/dtd/tasks-1.0" xmlns:xsi="http://www.w3.org/2001/XMLSchema-instance" xsi:schemaLocation="http://pear.php.net/dtd/tasks-1.0
http://pear.php.net/dtd/tasks-1.0.xsd
http://pear.php.net/dtd/package-2.0
http://pear.php.net/dtd/package-2.0.xsd">
 <name>Translation2</name>
 <channel>pear.php.net</channel>
 <summary>Class for multilingual applications management.</summary>
 <description>This class provides an easy way to retrieve all the strings for a multilingual site from a data source (i.e. db).
The following containers are provided, more will follow:
- PEAR::DB
- PEAR::MDB
- PEAR::MDB2
- gettext
- XML
- PEAR::DB_DataObject (experimental)
It is designed to reduce the number of queries to the db, caching the results when possible.
An Admin class is provided to easily manage translations (add/remove a language, add/remove a string).
Currently, the following decorators are provided:
- CacheLiteFunction (for file-based caching)
- CacheMemory (for memory-based caching)
- DefaultText (to replace empty strings with their keys)
- ErrorText (to replace empty strings with a custom error text)
- Iconv (to switch from/to different encodings)
- Lang (resort to fallback languages for empty strings)
- SpecialChars (replace html entities with their hex codes)
- UTF-8 (to convert UTF-8 strings to ISO-8859-1)

 </description>
 <lead>
  <name>Lorenzo Alberton</name>
  <user>quipo</user>
  <email>l.alberton@quipo.it</email>
  <active>yes</active>
 </lead>
 <developer>
  <name>Ian Eure</name>
  <user>ieure</user>
  <email>ieure@php.net</email>
  <active>yes</active>
 </developer>
 <developer>
  <name>Michael Wallner</name>
  <user>mike</user>
  <email>mike@php.net</email>
  <active>yes</active>
 </developer>
 <date>' . date('Y-m-d') . '</date>
 <time>' . $pf->getTime() . '</time>
 <version>
  <release>2.0.0beta13</release>
  <api>2.0.0beta13</api>
 </version>
 <stability>
  <release>beta</release>
  <api>beta</api>
 </stability>
 <license>BSD License</license>
 <notes>
- fixed bug #9855: missing call to _prepare() in setLang()
- propagate errors in the decorators
- fixed testsuite: added missing db_test_base.php file and
  fixed problem with class redeclaration
- fixed constraint creation in addLang() in the MDB2 Admin driver
- fixed setCharset() proxy in the Decorator
- fixed bug #11482: missing return in Translation2_Admin_Container_mdb2::addLang()
  when the table already exists
 </notes>
 <contents>
  <dir name="/">
   <dir name="sunger">
    <file baseinstalldir="freeb" md5sum="8332264d2e0e3c3091ebd6d8cee5d3a3" name="foo.dat" role="data" />
   </dir> <!-- //sunger -->
   <file baseinstalldir="freeb" md5sum="8332264d2e0e3c3091ebd6d8cee5d3a3" name="foo.php" role="php">
    <tasks:replace from="@pv@" to="version" type="package-info" />
   </file>
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
  <optional>
   <package>
    <name>Cache_Lite</name>
    <channel>pear.php.net</channel>
   </package>
   <package>
    <name>DB</name>
    <channel>pear.php.net</channel>
   </package>
   <package>
    <name>DB_DataObject</name>
    <channel>pear.php.net</channel>
   </package>
   <package>
    <name>MDB</name>
    <channel>pear.php.net</channel>
   </package>
   <package>
    <name>MDB2</name>
    <channel>pear.php.net</channel>
   </package>
   <package>
    <name>File_Gettext</name>
    <channel>pear.php.net</channel>
   </package>
   <package>
    <name>I18Nv2</name>
    <channel>pear.php.net</channel>
    <min>0.9.1</min>
   </package>
   <package>
    <name>XML_Serializer</name>
    <channel>pear.php.net</channel>
    <min>0.13.0</min>
   </package>
   <extension>
    <name>gettext</name>
   </extension>
  </optional>
 </dependencies>
 <phprelease>
  <filelist>
   <install as="merbl.php" name="foo.php" />
  </filelist>
 </phprelease>
 <changelog>
  <release>
   <version>
    <release>2.0.0beta13</release>
    <api>2.0.0beta13</api>
   </version>
   <stability>
    <release>beta</release>
    <api>beta</api>
   </stability>
   <date>2007-06-30</date>
   <license uri="http://www.example.com">BSD License</license>
   <notes>
- fixed bug #9855: missing call to _prepare() in setLang()
- propagate errors in the decorators
- fixed testsuite: added missing db_test_base.php file and
  fixed problem with class redeclaration
- fixed constraint creation in addLang() in the MDB2 Admin driver
- fixed setCharset() proxy in the Decorator
- fixed bug #11482: missing return in Translation2_Admin_Container_mdb2::addLang()
  when the table already exists
   </notes>
  </release>
  <release>
   <version>
    <release>2.0.0beta12</release>
    <api>2.0.0beta12</api>
   </version>
   <stability>
    <release>beta</release>
    <api>beta</api>
   </stability>
   <date>2006-12-15</date>
   <license uri="http://www.example.com">BSD License</license>
   <notes>
- force MDB2_FETCHMODE_ORDERED in Translation2_Container_mdb2::getPage() to
  avoid error when using an existing db connection with fetchmode set to
  MDB2_FETCHMODE_ASSOC (bug #8734)
- force lowercase keys in fetchLang() for Oracle compatibility (bug #8915)
- added defaultGroup to cache_lite options
- added new $options optional parameter to addLang() to set charset/collate info
  (MDB2 driver only)
- fixed bug #8546: index names are not escaped in SQL queries (DB and MDB admin
  containers)
- fixed dataobjectsimple container, get[Raw]Page() was returning integers as
  keys instead of strings (thanks to Michael Henry)
- added setCharset() method (currently only implemented in the MDB2 driver)
- added setLang() and setCacheOption() in CacheLiteFunction decorator (request #9301)
  (thanks to Sascha Grossenbacher)
- fixed bug #5539: DefaultText decorator does not call _replaceParams()
   </notes>
  </release>
  <release>
   <version>
    <release>2.0.0beta11</release>
    <api>2.0.0beta11</api>
   </version>
   <stability>
    <release>beta</release>
    <api>beta</api>
   </stability>
   <date>2006-09-07</date>
   <license uri="http://www.example.com">BSD License</license>
   <notes>
- fixed an error that would result in losing strings when getting a specific
  language&apos;s string in Translation2_Admin_Decorator_Autoadd.
- fixed bug #8287: addLang() SQL not compatible with MSSQL
- fixed MDB/MDB2 test runner
- fixed bug #8546: column/table names are not escaped in SQL queries
   </notes>
  </release>
  <release>
   <version>
    <release>2.0.0beta10</release>
    <api>2.0.0beta10</api>
   </version>
   <stability>
    <release>beta</release>
    <api>beta</api>
   </stability>
   <date>2006-07-12</date>
   <license uri="http://www.example.com">BSD License</license>
   <notes>
- fixed bug #7058, issue with array_merge not respecting existing array keys
- request #7736: ability to specify CacheLite group for CacheLite Decorator
  (thanks to ajt at localhype dot net)
- if an empty xml file is given, don&apos;t return an error (bug #7793)
- propagate errors in getPage() and getOne() (bug #8127)
- Fix Autoadd decorator, which was not adding entries for new string IDs for all languages,
  which made update() to fail on those strings.
   </notes>
  </release>
  <release>
   <version>
    <release>2.0.0beta9</release>
    <api>2.0.0beta9</api>
   </version>
   <stability>
    <release>beta</release>
    <api>beta</api>
   </stability>
   <date>2006-02-22</date>
   <license uri="http://www.example.com">BSD License</license>
   <notes>
- Translation2_Decorator_Lang: append keys when fallback lang 
  contains more than current (request #5773)
- Removed leftover code from the MDB admin container that caused
  inserting the same record twice (bug #6233)
- Better error handling and cache refreshing in the gettext driver
  (bug #6410) [thanks to Alan Knowles and ivanwyc@gmail.com]
- Honor global PEAR error settings in Translation2_Container::raiseError()
  (bug #6574)
- Added missing updateLang() proxy in Translation2_Admin_Decorator (bug #6753)
- Added length to INDEX on TEXT column in Translation2_Admin::addLang()
  when the dbms is MySQL (thanks to AJ Tarachanowicz)
- Fixed UNIQUE INDEX in Translation2_Admin::addLang(), one of the two columns
  got lost in a previous revision
   </notes>
  </release>
  <release>
   <version>
    <release>2.0.0beta8</release>
    <api>2.0.0beta8</api>
   </version>
   <stability>
    <release>beta</release>
    <api>beta</api>
   </stability>
   <date>2005-10-04</date>
   <license uri="http://www.example.com">BSD License</license>
   <notes>
- changed license to BSD
- removeLang() used to drop the entire table if there weren&apos;t any languages left.
  Now it does so only if the $force parameter is set (request #4218 and #5142)
- Translation2_Decorator now extends Translation2
- fixed warning with the CacheLiteFunction decorator and PHP 5.1
   </notes>
  </release>
  <release>
   <version>
    <release>2.0.0beta7</release>
    <api>2.0.0beta7</api>
   </version>
   <stability>
    <release>beta</release>
    <api>beta</api>
   </stability>
   <date>2005-06-28</date>
   <license uri="http://www.php.net/license">PHP License</license>
   <notes>
- added some options to the DefaultText decoration, now it is more customizable
  (thanks to Rolf &apos;Red&apos; Ochsenbein)
- added a __clone() method to clone the internal object references
  (bug #3641, patch by Olivier Guilyardi)
- Some fixes to the XML container, many thanks to Olivier Guilyardi:
  * fixed bug #3408: empty data sets were not correctly handled;
  * fixed bug #3420: get a shared file lock instead of an exclusive one;
  * fixed bug #3498: saveData() is not registered multiple times as shutdown
    function anymore; optimized saving when save_on_shutdown is set to false.
- added blank_on_missing option to the gettext container, which makes it
  behave like the other containers and automatically disables native mode;
  * see bug #4002
- fixed bug #4476: gettext container not working without the gettext extension
  (thanks to sergey at pushok dot com)
- added setContainerOptions() method to alter some container options after 
  the object instantiation (bug #2508)
- some minor fixes
   </notes>
  </release>
  <release>
   <version>
    <release>2.0.0beta6</release>
    <api>2.0.0beta6</api>
   </version>
   <stability>
    <release>beta</release>
    <api>beta</api>
   </stability>
   <date>2005-01-30</date>
   <license uri="http://www.php.net/license">PHP License</license>
   <notes>
- added Translation2::getRaw()
- fixed bug #3068: Translation2_Admin::update() on multiple tables didn&apos;t insert
  new records for missing langs, only updated the existing ones.
- fixed bug #3149: XML container didn&apos;t properly handle redundant strings
- added TRANSLATION2_DTD constant to the xml container (thanks to Olivier Guilyardi) 
- added t2xmlchk.php script to check if a XML file is Translation2 compliant
  (thanks to Olivier Guilyardi) 
- added ErrorText decorator
   </notes>
  </release>
  <release>
   <version>
    <release>2.0.0beta5</release>
    <api>2.0.0beta5</api>
   </version>
   <stability>
    <release>beta</release>
    <api>beta</api>
   </stability>
   <date>2004-12-24</date>
   <license uri="http://www.php.net/license">PHP License</license>
   <notes>
- renamed createNewLang() to addLang()  [BC break!]
- renamed addLangToAvailList() to addLangToList()
- added Translation2_Admin::getPageNames()
- added Translation2_Admin::updateLang()
- fixed bug #2890: getLang() raised a NOTICE if setLang() was not called before
- fixed bug #2972: CacheLiteFunction decorator not handling
  parameter substitution as expected
- updated dataobjectsimple container (alank)
- some internal minor fixes and tweaks
   </notes>
  </release>
  <release>
   <version>
    <release>2.0.0beta4</release>
    <api>2.0.0beta4</api>
   </version>
   <stability>
    <release>beta</release>
    <api>beta</api>
   </stability>
   <date>2004-11-20</date>
   <license uri="http://www.php.net/license">PHP License</license>
   <notes>
- added a complete TestSuite
- updated gettext docs
- fixed typo in error code (TRANSLATION_ERROR_UNKNOWN_LANG =&gt; TRANSLATION2_ERROR_UNKNOWN_LANG)
- fixed typo in the MDB Admin container [quote() =&gt; getTextValue()]
- fixed typo in db admin containers ($this-&gt;queries =&gt; $this-&gt;_queries)
- in the gettext admin container 
- fixed many bugs in the gettext admin container:
  * fixed old field [remove &quot;windows&quot;, add &quot;encoding&quot;]
  * fixed error in remove() [can&apos;t pass by reference]
  * fixed typo in update() [$stingID =&gt; $stringID]
  * in _add(), create the domains on demand
  * handle stale cache
- many fixes/updates to the xml container:
  * init() accepts an array as parameter (not a string)
  * added &apos;save_on_shutdown&apos; option
    (you can choose to save in real time, now)
  * return Translation2 errors with numeric codes
  * added &apos;encoding&apos; field
  * added removeLang()
  * other minor fixes and tweaks
   </notes>
  </release>
  <release>
   <version>
    <release>2.0.0beta3</release>
    <api>2.0.0beta3</api>
   </version>
   <stability>
    <release>beta</release>
    <api>beta</api>
   </stability>
   <date>2004-11-11</date>
   <license uri="http://www.php.net/license">PHP License</license>
   <notes>
- Welcome to the new developers, Ian Eure and Michael Wallner
- The last release contained an old gettext driver (bug #2503) (ieure)
- Many portability fixes applied to the database containers (thanks to Ian Eure and
  Xavier Lembo for their suggestions and patches)
- Minor changes to the table definitions for better portability (lowercase field names,
  VARCHAR instead of CHAR, bigger field size)
- setLang() now returns an error if called with an unknown $langID (bug #2498).
- Added $cleaningFrequency option to the CacheLiteFunction to implement
  statistic cache cleaning
- Added Translation2_Admin::cleanCache() method to clean the cache on demand.
  It is automatically triggered after a change if $options[&apos;autoCleanCache&apos;] is TRUE.
- Big cleanup of the DB and Admin_DB containers. See CVS changelog for details.
  (ieure)
- Added update() method to Translation2_Admin. This is a BC break; you used to be able
  to update strings with add(). This is no longer possible, use update(). (ieure)
- Re-added a check in add() to see if an update() is needed instead of an insert
- String ID columns are created as type TEXT to support gettext-style string IDs.
  (ieure)
- Reflect the changes made to the DB container into the MDB and MDB2 containers too,
  plus other minor fixes/optimizations.
- strings_default_table may use %s to represent the language name. You may now have one
  table per language without having to explicitly specify them all. (ieure)
- DefaultText decorator has new getStringID() method, which will return the string which
  was requested if no stringID exists. This mirrors the gettext() semantics. (ieure)
- Added Admin_Decorator class, which allows you to create Decorators for
  Translation2_Admin. (ieure)
- New &apos;Autoadd&apos; Admin Decorator, which automatically adds requested strings. (ieure)
- Removed translate(), added getStringID(). You can mimic the old behaviour in
  two steps:
  $stringID = $tr-&gt;getStringID(&apos;mystring&apos;, &apos;mypage&apos;);
  $translatedString = $tr-&gt;get($stringID, &apos;mypage&apos;, $otherLangID);
- Major cleanup of the gettext container and added some examples (mike)
- removeLang() was missing. Fixed.
- The gettext container no longer require the gettext extension (thanks to Sergey Korotkov);
  it is used when loaded, though, since it&apos;s faster.
- Both .mo and .po files are valid data sources for the gettext container (Sergey Korotkov)
- New Iconv decorator based on the one written by Sergey Korotkov
- Added a new &quot;encoding&quot; column to the langsAvail table
- New xml container by Olivier Guilyardi
   </notes>
  </release>
  <release>
   <version>
    <release>2.0.0beta1</release>
    <api>2.0.0beta1</api>
   </version>
   <stability>
    <release>beta</release>
    <api>beta</api>
   </stability>
   <date>2004-05-05</date>
   <license uri="http://www.php.net/license">PHP License</license>
   <notes>
- BC break! Run the example to see what&apos;s new
- refactoring in progress:
  added a Decorator class and some subclasses to control
  the output (now you can set a stack of fallback languages,
  a filter to deal with empty strings, one or more cache layers...)
- improved gettext support (thanks to Michael Wallner)
- added gettext admin class
- fixes in the db admin classes
- when adding a new string, if it matches one already in the db,
  the old one is replaced by the new one.
- added a MDB2 container
- added a DB_DataObject container (by Alan Knowles)
   </notes>
  </release>
  <release>
   <version>
    <release>2.0.0alpha2</release>
    <api>2.0.0alpha2</api>
   </version>
   <stability>
    <release>alpha</release>
    <api>alpha</api>
   </stability>
   <date>2004-02-05</date>
   <license uri="http://www.php.net/license">PHP License</license>
   <notes>
- added an experimental GNU gettext driver
- translate() now accepts a third parameter ($pageID)
- PHP5 fix
- renamed old getPage() to getRawPage()
- new getPage() resorts to fallback lang and replaces parameters when needed
- added error checking/codes
   </notes>
  </release>
  <release>
   <version>
    <release>0.0.1</release>
    <api>0.0.1</api>
   </version>
   <stability>
    <release>alpha</release>
    <api>alpha</api>
   </stability>
   <date>2004-01-21</date>
   <license uri="http://www.php.net/license">PHP License</license>
   <notes>
First alpha release
   </notes>
  </release>
 </changelog>
</package>
'
, $contents, 'contents 1');
chdir($savedir);
echo 'tests done';
?>
--CLEAN--
<?php
require_once dirname(dirname(__FILE__)) . '/teardown.php.inc';
?>
--EXPECT--
tests done
