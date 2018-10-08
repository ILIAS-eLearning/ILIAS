--TEST--
upgrade command, test for bug #4060 - install/upgrade of package with an os installcondition * fails
--SKIPIF--
<?php
if (!getenv('PHP_PEAR_RUNTESTS')) {
    echo 'skip';
}
if (strolower(substr(php_uname('s'), 0, 3)) == 'win') {
    echo 'skip';
}
?>
--FILE--
<?php

require_once dirname(dirname(__FILE__)) . DIRECTORY_SEPARATOR . 'setup.php.inc';
$reg = &$config->getRegistry();
$c = $reg->getChannel('pear.php.net');
$c->setBaseURL('REST1.0', 'http://pear.php.net/rest/');
$reg->updateChannel($c);
$pearweb->addRESTConfig("http://pear.php.net/rest/r/file_passwd/allreleases.xml", '<?xml version="1.0" encoding="UTF-8" ?>
<a xmlns="http://pear.php.net/dtd/rest.allreleases"
    xmlns:xsi="http://www.w3.org/2001/XMLSchema-instance" xmlns:xlink="http://www.w3.org/1999/xlink"     xsi:schemaLocation="http://pear.php.net/dtd/rest.allreleases
    http://pear.php.net/dtd/rest.allreleases.xsd">
 <p>File_Passwd</p>
 <c>pear.php.net</c>
 <r><v>1.1.6</v><s>stable</s></r>
 <r><v>1.1.5</v><s>stable</s></r>
 <r><v>1.1.4</v><s>stable</s></r>
 <r><v>1.1.3</v><s>stable</s></r>
 <r><v>1.1.2</v><s>stable</s></r>
 <r><v>1.1.1</v><s>stable</s></r>
 <r><v>1.1.0</v><s>stable</s></r>
 <r><v>1.0.2</v><s>stable</s></r>
 <r><v>1.0.1</v><s>stable</s></r>
 <r><v>1.0.0</v><s>stable</s></r>
 <r><v>1.0b2</v><s>beta</s></r>
 <r><v>1.0b1</v><s>beta</s></r>
 <r><v>0.9.5</v><s>beta</s></r>
 <r><v>0.9.4</v><s>beta</s></r>
 <r><v>0.9.3</v><s>beta</s></r>
 <r><v>0.9.2a</v><s>beta</s></r>
 <r><v>0.9.2</v><s>beta</s></r>
 <r><v>0.9.1</v><s>beta</s></r>
 <r><v>0.9.0</v><s>beta</s></r>
</a>', 'text/xml');
$pearweb->addRESTConfig("http://pear.php.net/rest/p/file_passwd/info.xml", '<?xml version="1.0" encoding="UTF-8" ?>
<p xmlns="http://pear.php.net/dtd/rest.package"    xmlns:xsi="http://www.w3.org/2001/XMLSchema-instance"
    xmlns:xlink="http://www.w3.org/1999/xlink"    xsi:schemaLocation="http://pear.php.net/dtd/rest.package    http://pear.php.net/dtd/rest.package.xsd">
 <n>File_Passwd</n>
 <c>pear.php.net</c>
 <ca xlink:href="/rest/c/File+Formats">File Formats</ca>
 <l>PHP</l>
 <s>Manipulate many kinds of password files</s>
 <d>Provides methods to manipulate and authenticate against standard Unix,
SMB server, AuthUser (.htpasswd), AuthDigest (.htdigest), CVS pserver
and custom formatted password files.</d>
 <r xlink:href="/rest/r/file_passwd"/>
</p>', 'text/xml');
$pearweb->addRESTConfig("http://pear.php.net/rest/r/file_passwd/1.1.6.xml", '<?xml version="1.0" encoding="UTF-8" ?>
<r xmlns="http://pear.php.net/dtd/rest.release"
    xmlns:xsi="http://www.w3.org/2001/XMLSchema-instance"
    xmlns:xlink="http://www.w3.org/1999/xlink"
    xsi:schemaLocation="http://pear.php.net/dtd/rest.release
    http://pear.php.net/dtd/rest.release.xsd">
 <p xlink:href="/rest/p/file_passwd">File_Passwd</p>
 <c>pear.php.net</c>
 <v>1.1.6</v>
 <st>stable</st>
 <l>PHP</l>
 <m>mike</m>
 <s>Manipulate many kinds of password files</s>
 <d>Provides methods to manipulate and authenticate against standard Unix,
SMB server, AuthUser (.htpasswd), AuthDigest (.htdigest), CVS pserver
and custom formatted password files.

</d>
 <da>2005-09-27 02:30:34</da>
 <n>* Fixed bug #5532 (Authdigest: changing a password of a user removes the user from all other realms)

</n>
 <f>23832</f>
 <g>http://pear.php.net/get/File_Passwd-1.1.6</g>
 <x xlink:href="package.1.1.6.xml"/>
</r>', 'text/xml');
$pearweb->addRESTConfig("http://pear.php.net/rest/r/file_passwd/deps.1.1.6.txt", 'a:5:{i:1;a:4:{s:4:"type";s:3:"pkg";s:3:"rel";s:3:"has";s:8:"optional";s:2:"no";s:4:"name";s:4:"PEAR";}i:2;a:5:{s:4:"type";s:3:"pkg";s:3:"rel";s:2:"ge";s:7:"version";s:5:"1.0.0";s:8:"optional";s:3:"yes";s:4:"name";s:10:"Crypt_CHAP";}i:3;a:4:{s:4:"type";s:3:"php";s:3:"rel";s:2:"ge";s:7:"version";s:5:"4.3.0";s:8:"optional";s:3:"yes";}i:4;a:4:{s:4:"type";s:3:"php";s:3:"rel";s:2:"ge";s:7:"version";s:5:"4.0.6";s:8:"optional";s:2:"no";}i:5;a:4:{s:4:"type";s:3:"ext";s:3:"rel";s:3:"has";s:8:"optional";s:2:"no";s:4:"name";s:4:"pcre";}}', 'text/xml');
$pearweb->addRESTConfig("http://pear.php.net/rest/r/net_pop3/allreleases.xml", '<?xml version="1.0" encoding="UTF-8" ?>
<a xmlns="http://pear.php.net/dtd/rest.allreleases"
    xmlns:xsi="http://www.w3.org/2001/XMLSchema-instance" xmlns:xlink="http://www.w3.org/1999/xlink"     xsi:schemaLocation="http://pear.php.net/dtd/rest.allreleases
    http://pear.php.net/dtd/rest.allreleases.xsd">
 <p>Net_POP3</p>
 <c>pear.php.net</c>
 <r><v>1.3.6</v><s>stable</s></r>
 <r><v>1.3.5</v><s>stable</s></r>
 <r><v>1.3.4</v><s>beta</s></r>
 <r><v>1.3.3</v><s>stable</s></r>
 <r><v>1.3.2</v><s>stable</s></r>
 <r><v>1.3.1</v><s>stable</s></r>
 <r><v>1.3</v><s>stable</s></r>
 <r><v>1.2</v><s>stable</s></r>
 <r><v>1.1</v><s>stable</s></r>
 <r><v>1.0</v><s>stable</s></r>
</a>', 'text/xml');
$pearweb->addRESTConfig("http://pear.php.net/rest/p/net_pop3/info.xml", '<?xml version="1.0" encoding="UTF-8" ?>
<p xmlns="http://pear.php.net/dtd/rest.package"    xmlns:xsi="http://www.w3.org/2001/XMLSchema-instance"
    xmlns:xlink="http://www.w3.org/1999/xlink"    xsi:schemaLocation="http://pear.php.net/dtd/rest.package    http://pear.php.net/dtd/rest.package.xsd">
 <n>Net_POP3</n>
 <c>pear.php.net</c>
 <ca xlink:href="/rest/c/Networking">Networking</ca>
 <l>BSD</l>
 <s>Provides a POP3 class to access POP3 server.</s>
 <d>Provides a POP3 class to access POP3 server. Support all POP3 commands
including UIDL listings, APOP authentication,DIGEST-MD5 and CRAM-MD5 using optional Auth_SASL package</d>
 <r xlink:href="/rest/r/net_pop3"/>
</p>', 'text/xml');
$pearweb->addRESTConfig("http://pear.php.net/rest/r/net_pop3/1.3.6.xml", '<?xml version="1.0" encoding="UTF-8" ?>
<r xmlns="http://pear.php.net/dtd/rest.release"
    xmlns:xsi="http://www.w3.org/2001/XMLSchema-instance"
    xmlns:xlink="http://www.w3.org/1999/xlink"
    xsi:schemaLocation="http://pear.php.net/dtd/rest.release
    http://pear.php.net/dtd/rest.release.xsd">
 <p xlink:href="/rest/p/net_pop3">Net_POP3</p>
 <c>pear.php.net</c>
 <v>1.3.6</v>
 <st>stable</st>
 <l>BSD</l>
 <m>damian</m>
 <s>Provides a POP3 class to access POP3 server.</s>
 <d>Provides a POP3 class to access POP3 server. Support all POP3 commands
including UIDL listings, APOP authentication,DIGEST-MD5 and CRAM-MD5 using optional Auth_SASL package
</d>
 <da>2005-04-04 22:24:25</da>
 <n>* Fixed Bug #3551 Bug #2663 not fixed yet.
* Fixed Bug #3410 Error handling in _sendCmd
* Fixed Bug #1942 wrong parameter-type specification in Net_POP3::login
* Fixed Bug #239 Missing phpdoc tag.
</n>
 <f>10076</f>
 <g>http://pear.php.net/get/Net_POP3-1.3.6</g>
 <x xlink:href="package.1.3.6.xml"/>
</r>', 'text/xml');
$pearweb->addRESTConfig("http://pear.php.net/rest/r/net_pop3/deps.1.3.6.txt", 'a:1:{i:1;a:4:{s:4:"type";s:3:"pkg";s:3:"rel";s:2:"ge";s:7:"version";s:3:"1.0";s:4:"name";s:10:"Net_Socket";}}', 'text/xml');
$pearweb->addRESTConfig("http://pear.php.net/rest/r/db/allreleases.xml", '<?xml version="1.0" encoding="UTF-8" ?>
<a xmlns="http://pear.php.net/dtd/rest.allreleases"
    xmlns:xsi="http://www.w3.org/2001/XMLSchema-instance" xmlns:xlink="http://www.w3.org/1999/xlink"     xsi:schemaLocation="http://pear.php.net/dtd/rest.allreleases
    http://pear.php.net/dtd/rest.allreleases.xsd">
 <p>DB</p>
 <c>pear.php.net</c>
 <r><v>1.7.10</v><s>stable</s></r>
 <r><v>1.7.9</v><s>stable</s></r>
 <r><v>1.7.8</v><s>stable</s></r>
 <r><v>1.7.8RC2</v><s>beta</s></r>
 <r><v>1.7.8RC1</v><s>beta</s></r>
 <r><v>1.7.7</v><s>stable</s></r>
 <r><v>1.7.6</v><s>stable</s></r>
 <r><v>1.7.5</v><s>stable</s></r>
 <r><v>1.7.4</v><s>stable</s></r>
 <r><v>1.7.3</v><s>stable</s></r>
 <r><v>1.7.2</v><s>stable</s></r>
 <r><v>1.7.1</v><s>stable</s></r>
 <r><v>1.7.0</v><s>stable</s></r>
 <r><v>1.7.0RC1</v><s>beta</s></r>
 <r><v>1.6.8</v><s>stable</s></r>
 <r><v>1.6.7</v><s>stable</s></r>
 <r><v>1.6.6</v><s>stable</s></r>
 <r><v>1.6.5</v><s>stable</s></r>
 <r><v>1.6.4</v><s>stable</s></r>
 <r><v>1.6.3</v><s>stable</s></r>
 <r><v>1.6.2</v><s>stable</s></r>
 <r><v>1.6.1</v><s>stable</s></r>
 <r><v>1.6.0</v><s>stable</s></r>
 <r><v>1.6.0RC6</v><s>stable</s></r>
 <r><v>1.6.0RC5</v><s>beta</s></r>
 <r><v>1.6.0RC4</v><s>beta</s></r>
 <r><v>1.6.0RC3</v><s>beta</s></r>
 <r><v>1.6.0RC2</v><s>beta</s></r>
 <r><v>1.6.0RC1</v><s>beta</s></r>
 <r><v>1.5.0RC2</v><s>stable</s></r>
 <r><v>1.5.0RC1</v><s>stable</s></r>
 <r><v>1.4.0</v><s>stable</s></r>
 <r><v>1.4b1</v><s>beta</s></r>
 <r><v>1.3</v><s>stable</s></r>
 <r><v>1.2</v><s>stable</s></r>
</a>', 'text/xml');
$pearweb->addRESTConfig("http://pear.php.net/rest/p/db/info.xml", '<?xml version="1.0" encoding="UTF-8" ?>
<p xmlns="http://pear.php.net/dtd/rest.package"    xmlns:xsi="http://www.w3.org/2001/XMLSchema-instance"
    xmlns:xlink="http://www.w3.org/1999/xlink"    xsi:schemaLocation="http://pear.php.net/dtd/rest.package    http://pear.php.net/dtd/rest.package.xsd">
 <n>DB</n>
 <c>pear.php.net</c>
 <ca xlink:href="/rest/c/Database">Database</ca>
 <l>PHP License</l>
 <s>Database Abstraction Layer</s>
 <d>DB is a database abstraction layer providing:
* an OO-style query API
* portability features that make programs written for one DBMS work with other DBMS\'s
* a DSN (data source name) format for specifying database servers
* prepare/execute (bind) emulation for databases that don\'t support it natively
* a result object for each query response
* portable error codes
* sequence emulation
* sequential and non-sequential row fetching as well as bulk fetching
* formats fetched rows as associative arrays, ordered arrays or objects
* row limit support
* transactions support
* table information interface
* DocBook and phpDocumentor API documentation

DB layers itself on top of PHP\'s existing
database extensions.

Drivers for the following extensions pass
the complete test suite and provide
interchangeability when all of DB\'s
portability options are enabled:

  fbsql, ibase, informix, msql, mssql,
  mysql, mysqli, oci8, odbc, pgsql,
  sqlite and sybase.

There is also a driver for the dbase
extension, but it can\'t be used
interchangeably because dbase doesn\'t
support many standard DBMS features.

DB is compatible with both PHP 4 and PHP 5.</d>
 <r xlink:href="/rest/r/db"/>
<dc>pear.php.net</dc>
<dp> MDB2</dp>
</p>', 'text/xml');
$pearweb->addRESTConfig("http://pear.php.net/rest/r/db/1.7.10.xml", '<?xml version="1.0" encoding="UTF-8" ?>
<r xmlns="http://pear.php.net/dtd/rest.release"
    xmlns:xsi="http://www.w3.org/2001/XMLSchema-instance"
    xmlns:xlink="http://www.w3.org/1999/xlink"
    xsi:schemaLocation="http://pear.php.net/dtd/rest.release
    http://pear.php.net/dtd/rest.release.xsd">
 <p xlink:href="/rest/p/db">DB</p>
 <c>pear.php.net</c>
 <v>1.7.10</v>
 <st>stable</st>
 <l>PHP License</l>
 <m>aharvey</m>
 <s>Database Abstraction Layer</s>
 <d>DB is a database abstraction layer providing:
* an OO-style query API
* portability features that make programs written for one DBMS work with other DBMS\'s
* a DSN (data source name) format for specifying database servers
* prepare/execute (bind) emulation for databases that don\'t support it natively
* a result object for each query response
* portable error codes
* sequence emulation
* sequential and non-sequential row fetching as well as bulk fetching
* formats fetched rows as associative arrays, ordered arrays or objects
* row limit support
* transactions support
* table information interface
* DocBook and phpDocumentor API documentation

DB layers itself on top of PHP\'s existing
database extensions.

Drivers for the following extensions pass
the complete test suite and provide
interchangeability when all of DB\'s
portability options are enabled:

  fbsql, ibase, informix, msql, mssql,
  mysql, mysqli, oci8, odbc, pgsql,
  sqlite and sybase.

There is also a driver for the dbase
extension, but it can\'t be used
interchangeably because dbase doesn\'t
support many standard DBMS features.

DB is compatible with both PHP 4 and PHP 5.</d>
 <da>2007-03-20 05:25:28</da>
 <n>This release of DB adds basic support for the BIT type within MySQL 5 when
using the mysqli driver.

mysqli:
* Added a type map for BIT fields.  Bug 10211.</n>
 <f>131946</f>
 <g>http://pear.php.net/get/DB-1.7.10</g>
 <x xlink:href="package.1.7.10.xml"/>
</r>', 'text/xml');
$pearweb->addRESTConfig("http://pear.php.net/rest/r/db/deps.1.7.10.txt", 'a:1:{s:8:"required";a:3:{s:3:"php";a:1:{s:3:"min";s:5:"4.2.0";}s:13:"pearinstaller";a:1:{s:3:"min";s:7:"1.4.0b1";}s:7:"package";a:3:{s:4:"name";s:4:"PEAR";s:7:"channel";s:12:"pear.php.net";s:3:"min";s:5:"1.0b1";}}}', 'text/xml');
$pearweb->addRESTConfig("http://pear.php.net/rest/r/mdb/allreleases.xml", '<?xml version="1.0" encoding="UTF-8" ?>
<a xmlns="http://pear.php.net/dtd/rest.allreleases"
    xmlns:xsi="http://www.w3.org/2001/XMLSchema-instance" xmlns:xlink="http://www.w3.org/1999/xlink"     xsi:schemaLocation="http://pear.php.net/dtd/rest.allreleases
    http://pear.php.net/dtd/rest.allreleases.xsd">
 <p>MDB</p>
 <c>pear.php.net</c>
 <r><v>1.3.0</v><s>stable</s></r>
 <r><v>1.2</v><s>stable</s></r>
 <r><v>1.1.4</v><s>stable</s></r>
 <r><v>1.1.4RC6</v><s>devel</s></r>
 <r><v>1.1.4RC5</v><s>devel</s></r>
 <r><v>1.1.4RC4</v><s>devel</s></r>
 <r><v>1.1.4RC3</v><s>devel</s></r>
 <r><v>1.1.4RC2</v><s>devel</s></r>
 <r><v>1.1.4-RC1</v><s>devel</s></r>
 <r><v>1.1.3</v><s>stable</s></r>
 <r><v>1.1.3-RC2</v><s>devel</s></r>
 <r><v>1.1.3-RC1</v><s>devel</s></r>
 <r><v>1.1.2</v><s>stable</s></r>
 <r><v>1.1.2-RC2</v><s>devel</s></r>
 <r><v>1.1.2-RC1</v><s>devel</s></r>
 <r><v>1.1.1</v><s>stable</s></r>
 <r><v>1.1.0pl1</v><s>devel</s></r>
 <r><v>1.1.0</v><s>devel</s></r>
 <r><v>1.0.1RC1</v><s>devel</s></r>
 <r><v>1.0</v><s>stable</s></r>
 <r><v>1.0_RC4</v><s>devel</s></r>
 <r><v>1.0_RC3</v><s>devel</s></r>
 <r><v>1.0_RC2</v><s>devel</s></r>
 <r><v>1.0_RC1</v><s>devel</s></r>
 <r><v>0.9.11</v><s>devel</s></r>
 <r><v>0.9.10</v><s>devel</s></r>
 <r><v>0.9.9</v><s>beta</s></r>
 <r><v>0.9.8</v><s>beta</s></r>
 <r><v>0.9.7.1</v><s>beta</s></r>
 <r><v>0.9.7</v><s>beta</s></r>
 <r><v>0.9.6</v><s>beta</s></r>
 <r><v>0.9.5</v><s>beta</s></r>
 <r><v>0.9.4</v><s>beta</s></r>
 <r><v>0.9.3</v><s>beta</s></r>
 <r><v>0.9.2</v><s>beta</s></r>
 <r><v>0.9.1</v><s>beta</s></r>
 <r><v>0.9</v><s>beta</s></r>
</a>', 'text/xml');
$pearweb->addRESTConfig("http://pear.php.net/rest/p/mdb/info.xml", '<?xml version="1.0" encoding="UTF-8" ?>
<p xmlns="http://pear.php.net/dtd/rest.package"    xmlns:xsi="http://www.w3.org/2001/XMLSchema-instance"
    xmlns:xlink="http://www.w3.org/1999/xlink"    xsi:schemaLocation="http://pear.php.net/dtd/rest.package    http://pear.php.net/dtd/rest.package.xsd">
 <n>MDB</n>
 <c>pear.php.net</c>
 <ca xlink:href="/rest/c/Database">Database</ca>
 <l>BSD style</l>
 <s>database abstraction layer</s>
 <d>PEAR MDB is a merge of the PEAR DB and Metabase php database abstraction layers.
It provides a common API for all support RDBMS. The main difference to most
other DB abstraction packages is that MDB goes much further to ensure
portability. Among other things MDB features:
* An OO-style query API
* A DSN (data source name) or array format for specifying database servers
* Datatype abstraction and on demand datatype conversion
* Portable error codes
* Sequential and non sequential row fetching as well as bulk fetching
* Ordered array and associative array for the fetched rows
* Prepare/execute (bind) emulation
* Sequence emulation
* Replace emulation
* Limited Subselect emulation
* Row limit support
* Transactions support
* Large Object support
* Index/Unique support
* Module Framework to load advanced functionality on demand
* Table information interface
* RDBMS management methods (creating, dropping, altering)
* RDBMS independent xml based schema definition management
* Altering of a DB from a changed xml schema
* Reverse engineering of xml schemas from an existing DB (currently only MySQL)
* Full integration into the PEAR Framework
* Wrappers for the PEAR DB and Metabase APIs
* PHPDoc API documentation
Currently supported RDBMS:
MySQL
PostGreSQL
Oracle
Frontbase
Querysim
Interbase/Firebird
MSSQL</d>
 <r xlink:href="/rest/r/mdb"/>
<dc>pear.php.net</dc>
<dp href="/rest/p/mdb2"> MDB2</dp>
</p>', 'text/xml');
$pearweb->addRESTConfig("http://pear.php.net/rest/r/mdb/1.3.0.xml", '<?xml version="1.0" encoding="UTF-8" ?>
<r xmlns="http://pear.php.net/dtd/rest.release"
    xmlns:xsi="http://www.w3.org/2001/XMLSchema-instance"
    xmlns:xlink="http://www.w3.org/1999/xlink"
    xsi:schemaLocation="http://pear.php.net/dtd/rest.release
    http://pear.php.net/dtd/rest.release.xsd">
 <p xlink:href="/rest/p/mdb">MDB</p>
 <c>pear.php.net</c>
 <v>1.3.0</v>
 <st>stable</st>
 <l>BSD style</l>
 <m>lsmith</m>
 <s>database abstraction layer</s>
 <d>PEAR MDB is a merge of the PEAR DB and Metabase php database abstraction layers.
It provides a common API for all support RDBMS. The main difference to most
other DB abstraction packages is that MDB goes much further to ensure
portability. Among other things MDB features:
* An OO-style query API
* A DSN (data source name) or array format for specifying database servers
* Datatype abstraction and on demand datatype conversion
* Portable error codes
* Sequential and non sequential row fetching as well as bulk fetching
* Ordered array and associative array for the fetched rows
* Prepare/execute (bind) emulation
* Sequence emulation
* Replace emulation
* Limited Subselect emulation
* Row limit support
* Transactions support
* Large Object support
* Index/Unique support
* Module Framework to load advanced functionality on demand
* Table information interface
* RDBMS management methods (creating, dropping, altering)
* RDBMS independent xml based schema definition management
* Altering of a DB from a changed xml schema
* Reverse engineering of xml schemas from an existing DB (currently only MySQL)
* Full integration into the PEAR Framework
* Wrappers for the PEAR DB and Metabase APIs
* PHPDoc API documentation
Currently supported RDBMS:
MySQL
PostGreSQL
Oracle
Frontbase
Querysim
Interbase/Firebird
MSSQL
</d>
 <da>2004-04-22 07:26:53</da>
 <n>MDB requires PHP 4.2 from now on.
MDB:
- fixed PHP5 compatibility issue in MDB::isError()
all drivers:
- added quoteIdentifier() method
- added sequence_col_name option to make the column name inside sequence
  emulation tables configurable
- renamed toString() to __toString() in order to take advantage of new PHP5
  goodness and made it public
- unified the native error raising methods (tested on oracle, pgsql, mysql and ibase)
- fixed bug #1159 which would break index handling in getTableFieldDefinition()
  if not in portability mode
MDB_ibase:
- fixed several bugs in the buffering code
- fixed NULL management
- fixed replace()
MDB_oci8:
- fixed several bugs in the buffering code
- added native currId() implementation
MDB_Manager_oci8:
- added listTables() and listTableFields()
MDB_mysql:
- added quoteIdentifier() method
MDB_fbsql:
- removed broken implementations of currId()
MDB_mssql:
- removed broken implementations of currId()
- added quoteIdentifier() method
MDB_Manager_mysql:
- fixed mysql 4.0.13 issue in createSequence()
- several fixes to ensure the correct case is used when fetching data
  without the portability flag setting enabled
MDB_Manager_mssql:
- added listTables() and listTableFields()
- added getTableFieldDefinition() (still alpha quality)
test suite:
- added several test and applied PHP5 compatibility fixes
- fixed a wrong assumption in the fetchmode bug test
- moved set_time_limit() call to the setup script to be easier to customize
</n>
 <f>218957</f>
 <g>http://pear.php.net/get/MDB-1.3.0</g>
 <x xlink:href="package.1.3.0.xml"/>
</r>', 'text/xml');
$pearweb->addRESTConfig("http://pear.php.net/rest/r/mdb/deps.1.3.0.txt", 'a:3:{i:1;a:3:{s:4:"type";s:3:"php";s:3:"rel";s:2:"ge";s:7:"version";s:5:"4.2.0";}i:2;a:4:{s:4:"type";s:3:"pkg";s:3:"rel";s:2:"ge";s:7:"version";s:5:"1.0b1";s:4:"name";s:4:"PEAR";}i:3;a:3:{s:4:"type";s:3:"pkg";s:3:"rel";s:3:"has";s:4:"name";s:10:"XML_Parser";}}', 'text/xml');
$pearweb->addRESTConfig("http://pear.php.net/rest/r/mdb2/allreleases.xml", '<?xml version="1.0" encoding="UTF-8" ?>
<a xmlns="http://pear.php.net/dtd/rest.allreleases"
    xmlns:xsi="http://www.w3.org/2001/XMLSchema-instance" xmlns:xlink="http://www.w3.org/1999/xlink"     xsi:schemaLocation="http://pear.php.net/dtd/rest.allreleases
    http://pear.php.net/dtd/rest.allreleases.xsd">
 <p>MDB2</p>
 <c>pear.php.net</c>
 <r><v>2.4.0</v><s>stable</s></r>
 <r><v>2.3.0</v><s>stable</s></r>
 <r><v>2.2.2</v><s>stable</s></r>
 <r><v>2.2.1</v><s>stable</s></r>
 <r><v>2.2.0</v><s>stable</s></r>
 <r><v>2.1.0</v><s>stable</s></r>
 <r><v>2.0.3</v><s>stable</s></r>
 <r><v>2.0.2</v><s>stable</s></r>
 <r><v>2.0.1</v><s>stable</s></r>
 <r><v>2.0.0</v><s>stable</s></r>
 <r><v>2.0.0RC5</v><s>beta</s></r>
 <r><v>2.0.0RC4</v><s>beta</s></r>
 <r><v>2.0.0RC3</v><s>beta</s></r>
 <r><v>2.0.0RC2</v><s>beta</s></r>
 <r><v>2.0.0RC1</v><s>beta</s></r>
 <r><v>2.0.0beta6</v><s>beta</s></r>
 <r><v>2.0.0beta5</v><s>beta</s></r>
 <r><v>2.0.0beta4</v><s>beta</s></r>
 <r><v>2.0.0beta3</v><s>beta</s></r>
 <r><v>2.0.0beta2</v><s>beta</s></r>
 <r><v>2.0.0beta1</v><s>alpha</s></r>
 <r><v>2.0.0alpha1</v><s>alpha</s></r>
</a>', 'text/xml');
$pearweb->addRESTConfig("http://pear.php.net/rest/p/mdb2/info.xml", '<?xml version="1.0" encoding="UTF-8" ?>
<p xmlns="http://pear.php.net/dtd/rest.package"    xmlns:xsi="http://www.w3.org/2001/XMLSchema-instance"
    xmlns:xlink="http://www.w3.org/1999/xlink"    xsi:schemaLocation="http://pear.php.net/dtd/rest.package    http://pear.php.net/dtd/rest.package.xsd">
 <n>MDB2</n>
 <c>pear.php.net</c>
 <ca xlink:href="/rest/c/Database">Database</ca>
 <l>BSD License</l>
 <s>database abstraction layer</s>
 <d>PEAR MDB2 is a merge of the PEAR DB and Metabase php database abstraction layers.

It provides a common API for all supported RDBMS. The main difference to most
other DB abstraction packages is that MDB2 goes much further to ensure
portability. MDB2 provides most of its many features optionally that
can be used to construct portable SQL statements:
* Object-Oriented API
* A DSN (data source name) or array format for specifying database servers
* Datatype abstraction and on demand datatype conversion
* Various optional fetch modes to fix portability issues
* Portable error codes
* Sequential and non sequential row fetching as well as bulk fetching
* Ability to make buffered and unbuffered queries
* Ordered array and associative array for the fetched rows
* Prepare/execute (bind) named and unnamed placeholder emulation
* Sequence/autoincrement emulation
* Replace emulation
* Limited sub select emulation
* Row limit emulation
* Transactions/savepoint support
* Large Object support
* Index/Unique Key/Primary Key support
* Pattern matching abstraction
* Module framework to load advanced functionality on demand
* Ability to read the information schema
* RDBMS management methods (creating, dropping, altering)
* Reverse engineering schemas from an existing database
* SQL function call abstraction
* Full integration into the PEAR Framework
* PHPDoc API documentation</d>
 <r xlink:href="/rest/r/mdb2"/>
</p>', 'text/xml');
$pearweb->addRESTConfig("http://pear.php.net/rest/r/mdb2/2.4.0.xml", '<?xml version="1.0" encoding="UTF-8" ?>
<r xmlns="http://pear.php.net/dtd/rest.release"
    xmlns:xsi="http://www.w3.org/2001/XMLSchema-instance"
    xmlns:xlink="http://www.w3.org/1999/xlink"
    xsi:schemaLocation="http://pear.php.net/dtd/rest.release
    http://pear.php.net/dtd/rest.release.xsd">
 <p xlink:href="/rest/p/mdb2">MDB2</p>
 <c>pear.php.net</c>
 <v>2.4.0</v>
 <st>stable</st>
 <l>BSD License</l>
 <m>quipo</m>
 <s>database abstraction layer</s>
 <d>PEAR MDB2 is a merge of the PEAR DB and Metabase php database abstraction layers.

It provides a common API for all supported RDBMS. The main difference to most
other DB abstraction packages is that MDB2 goes much further to ensure
portability. MDB2 provides most of its many features optionally that
can be used to construct portable SQL statements:
* Object-Oriented API
* A DSN (data source name) or array format for specifying database servers
* Datatype abstraction and on demand datatype conversion
* Various optional fetch modes to fix portability issues
* Portable error codes
* Sequential and non sequential row fetching as well as bulk fetching
* Ability to make buffered and unbuffered queries
* Ordered array and associative array for the fetched rows
* Prepare/execute (bind) named and unnamed placeholder emulation
* Sequence/autoincrement emulation
* Replace emulation
* Limited sub select emulation
* Row limit emulation
* Transactions/savepoint support
* Large Object support
* Index/Unique Key/Primary Key support
* Pattern matching abstraction
* Module framework to load advanced functionality on demand
* Ability to read the information schema
* RDBMS management methods (creating, dropping, altering)
* Reverse engineering schemas from an existing database
* SQL function call abstraction
* Full integration into the PEAR Framework
* PHPDoc API documentation</d>
 <da>2007-03-13 16:53:44</da>
 <n>- propagate errors in getTableFieldDefinition() in the Reverse module
- internally use MDB2::classExists() wrapper instead of directly calling class_exists()
- fixed bug #9502: query result misbehaves when the number of returned columns
  is greater than the number of passed types
- fixed bug #9748: Table name is not quoted in Extended.php buildManipSQL()
- fixed bug #9800: when the php extension for the driver fails to load, the
  error is not propagated correctly and the script dies
- propagate errors in the Datatype module
- implemented guid() in the Function module [globally unique identifier]
  (thanks to mario dot adam at schaeffler dot com)
- fixed bug #4854: Oracle Easy Connect syntax only works with array DSN
- fixed bug #10105: inTransaction() was returning an incorrect value after a call
  to disconnect() or __destruct()
- implemented a fallback mechanism within getTableIndexDefinition() and
  getTableConstraintDefinition() in the Reverse module to ignore the \'idxname_format\'
  option and use the index name as provided in case of failure before returning
  an error
- added a \'nativetype_map_callback\' option to map native data declarations back to
  custom data types (thanks to Andrew Hill).
- fixed bug #10234 and bug #10233: MDB2_Driver_Datatype_Common::mapNativeDatatype()
  must ensure that it returns the correct length value, or null
- added support for TEMPORARY tables (patch by Andrew Hill)
- phpdoc fixes
- fixed tests to be compatible with PHP4
- added new tests, including some MDB2 internals tests by Andrew Hill and Monique Szpak

open todo items:
- handle autoincrement fields in alterTable()
- add length handling to LOB reverse engineering
- add EXPLAIN abstraction
- add cursor support along the lines of PDO (Request #3660 etc.)
- add PDO based drivers, especially a driver to support SQLite 3 (Request #6907)
- add support to export/import in CSV format
- add more functions to the Function module (MD5(), IFNULL(), LENGTH() etc.)
- add support for database/table/row LOCKs
- add support for FOREIGN KEYs and CHECK (ENUM as possible mysql fallback) constraints
- generate STATUS file from test suite results and allow users to submit test results
- add support for full text index creation and querying
- add tests to check if the RDBMS specific handling with portability options
  disabled behaves as expected
- handle implicit commits (like for DDL) in any affected driver (mysql, sqlite..)
- add a getTableFieldsDefinitions() method to be used in tableInfo()
- drop ILIKE from matchPattern() and instead add a second parameter to
  handle case sensitivity with arbitrary operators
- add charset and collation support to field declaration in all drivers
- handle LOBs in buffered result sets (Request #8793)</n>
 <f>118961</f>
 <g>http://pear.php.net/get/MDB2-2.4.0</g>
 <x xlink:href="package.2.4.0.xml"/>
</r>', 'text/xml');
$pearweb->addRESTConfig("http://pear.php.net/rest/r/mdb2/deps.2.4.0.txt", 'a:2:{s:8:"required";a:3:{s:3:"php";a:1:{s:3:"min";s:5:"4.3.2";}s:13:"pearinstaller";a:1:{s:3:"min";s:7:"1.4.0b1";}s:7:"package";a:3:{s:4:"name";s:4:"PEAR";s:7:"channel";s:12:"pear.php.net";s:3:"min";s:5:"1.3.6";}}s:5:"group";a:9:{i:0;a:2:{s:7:"attribs";a:2:{s:4:"hint";s:29:"Frontbase SQL driver for MDB2";s:4:"name";s:5:"fbsql";}s:10:"subpackage";a:3:{s:4:"name";s:17:"MDB2_Driver_fbsql";s:7:"channel";s:12:"pear.php.net";s:3:"min";s:5:"0.3.0";}}i:1;a:2:{s:7:"attribs";a:2:{s:4:"hint";s:34:"Interbase/Firebird driver for MDB2";s:4:"name";s:5:"ibase";}s:10:"subpackage";a:3:{s:4:"name";s:17:"MDB2_Driver_ibase";s:7:"channel";s:12:"pear.php.net";s:3:"min";s:5:"1.4.0";}}i:2;a:2:{s:7:"attribs";a:2:{s:4:"hint";s:21:"MySQL driver for MDB2";s:4:"name";s:5:"mysql";}s:10:"subpackage";a:3:{s:4:"name";s:17:"MDB2_Driver_mysql";s:7:"channel";s:12:"pear.php.net";s:3:"min";s:5:"1.4.0";}}i:3;a:2:{s:7:"attribs";a:2:{s:4:"hint";s:22:"MySQLi driver for MDB2";s:4:"name";s:6:"mysqli";}s:10:"subpackage";a:3:{s:4:"name";s:18:"MDB2_Driver_mysqli";s:7:"channel";s:12:"pear.php.net";s:3:"min";s:5:"1.4.0";}}i:4;a:2:{s:7:"attribs";a:2:{s:4:"hint";s:29:"MS SQL Server driver for MDB2";s:4:"name";s:5:"mssql";}s:10:"subpackage";a:3:{s:4:"name";s:17:"MDB2_Driver_mssql";s:7:"channel";s:12:"pear.php.net";s:3:"min";s:5:"1.2.0";}}i:5;a:2:{s:7:"attribs";a:2:{s:4:"hint";s:22:"Oracle driver for MDB2";s:4:"name";s:4:"oci8";}s:10:"subpackage";a:3:{s:4:"name";s:16:"MDB2_Driver_oci8";s:7:"channel";s:12:"pear.php.net";s:3:"min";s:5:"1.4.0";}}i:6;a:2:{s:7:"attribs";a:2:{s:4:"hint";s:26:"PostgreSQL driver for MDB2";s:4:"name";s:5:"pgsql";}s:10:"subpackage";a:3:{s:4:"name";s:17:"MDB2_Driver_pgsql";s:7:"channel";s:12:"pear.php.net";s:3:"min";s:5:"1.4.0";}}i:7;a:2:{s:7:"attribs";a:2:{s:4:"hint";s:24:"Querysim driver for MDB2";s:4:"name";s:8:"querysim";}s:10:"subpackage";a:3:{s:4:"name";s:20:"MDB2_Driver_querysim";s:7:"channel";s:12:"pear.php.net";s:3:"min";s:5:"0.6.0";}}i:8;a:2:{s:7:"attribs";a:2:{s:4:"hint";s:23:"SQLite2 driver for MDB2";s:4:"name";s:6:"sqlite";}s:10:"subpackage";a:3:{s:4:"name";s:18:"MDB2_Driver_sqlite";s:7:"channel";s:12:"pear.php.net";s:3:"min";s:5:"1.4.0";}}}}', 'text/xml');
$pearweb->addRESTConfig("http://pear.php.net/rest/r/auth_radius/allreleases.xml", '<?xml version="1.0" encoding="UTF-8" ?>
<a xmlns="http://pear.php.net/dtd/rest.allreleases"
    xmlns:xsi="http://www.w3.org/2001/XMLSchema-instance" xmlns:xlink="http://www.w3.org/1999/xlink"     xsi:schemaLocation="http://pear.php.net/dtd/rest.allreleases
    http://pear.php.net/dtd/rest.allreleases.xsd">
 <p>Auth_RADIUS</p>
 <c>pear.php.net</c>
 <r><v>1.0.5</v><s>stable</s></r>
 <r><v>1.0.4</v><s>stable</s></r>
 <r><v>1.0.3</v><s>stable</s></r>
 <r><v>1.0.2</v><s>stable</s></r>
 <r><v>1.0.1</v><s>stable</s></r>
 <r><v>1.0</v><s>stable</s></r>
</a>', 'text/xml');
$pearweb->addRESTConfig("http://pear.php.net/rest/p/auth_radius/info.xml", '<?xml version="1.0" encoding="UTF-8" ?>
<p xmlns="http://pear.php.net/dtd/rest.package"    xmlns:xsi="http://www.w3.org/2001/XMLSchema-instance"
    xmlns:xlink="http://www.w3.org/1999/xlink"    xsi:schemaLocation="http://pear.php.net/dtd/rest.package    http://pear.php.net/dtd/rest.package.xsd">
 <n>Auth_RADIUS</n>
 <c>pear.php.net</c>
 <ca xlink:href="/rest/c/Authentication">Authentication</ca>
 <l>BSD</l>
 <s>Wrapper Classes for the RADIUS PECL.</s>
 <d>This package provides wrapper-classes for the RADIUS PECL.
There are different Classes for the different authentication methods.
If you are using CHAP-MD5 or MS-CHAP you need also the Crypt_CHAP package.
If you are using MS-CHAP you need also the mhash and mcrypt extension.</d>
 <r xlink:href="/rest/r/auth_radius"/>
</p>', 'text/xml');
$pearweb->addRESTConfig("http://pear.php.net/rest/r/auth_radius/1.0.5.xml", '<?xml version="1.0" encoding="UTF-8" ?>
<r xmlns="http://pear.php.net/dtd/rest.release"
    xmlns:xsi="http://www.w3.org/2001/XMLSchema-instance"
    xmlns:xlink="http://www.w3.org/1999/xlink"
    xsi:schemaLocation="http://pear.php.net/dtd/rest.release
    http://pear.php.net/dtd/rest.release.xsd">
 <p xlink:href="/rest/p/auth_radius">Auth_RADIUS</p>
 <c>pear.php.net</c>
 <v>1.0.5</v>
 <st>stable</st>
 <l>BSD</l>
 <m>mbretter</m>
 <s>Wrapper Classes for the RADIUS PECL.</s>
 <d>This package provides wrapper-classes for the RADIUS PECL.
There are different Classes for the different authentication methods.
If you are using CHAP-MD5 or MS-CHAP you need also the Crypt_CHAP package.
If you are using MS-CHAP you need also the mhash and mcrypt extension.

</d>
 <da>2006-08-18 13:19:16</da>
 <n>* BugFix: RADIUS_CLASS attribute should be of type string

</n>
 <f>8042</f>
 <g>http://pear.php.net/get/Auth_RADIUS-1.0.5</g>
 <x xlink:href="package.1.0.5.xml"/>
</r>', 'text/xml');
$pearweb->addRESTConfig("http://pear.php.net/rest/r/auth_radius/deps.1.0.5.txt", 'a:1:{i:1;a:4:{s:4:"type";s:3:"pkg";s:3:"rel";s:2:"ge";s:7:"version";s:5:"1.2.4";s:4:"name";s:6:"radius";}}', 'text/xml');
$pearweb->addRESTConfig("http://pear.php.net/rest/r/file_smbpasswd/allreleases.xml", '<?xml version="1.0" encoding="UTF-8" ?>
<a xmlns="http://pear.php.net/dtd/rest.allreleases"
    xmlns:xsi="http://www.w3.org/2001/XMLSchema-instance" xmlns:xlink="http://www.w3.org/1999/xlink"     xsi:schemaLocation="http://pear.php.net/dtd/rest.allreleases
    http://pear.php.net/dtd/rest.allreleases.xsd">
 <p>File_SMBPasswd</p>
 <c>pear.php.net</c>
 <r><v>1.0.2</v><s>stable</s></r>
 <r><v>1.0.1</v><s>stable</s></r>
 <r><v>1.0.0</v><s>stable</s></r>
 <r><v>0.9.0</v><s>beta</s></r>
</a>', 'text/xml');
$pearweb->addRESTConfig("http://pear.php.net/rest/p/file_smbpasswd/info.xml", '<?xml version="1.0" encoding="UTF-8" ?>
<p xmlns="http://pear.php.net/dtd/rest.package"    xmlns:xsi="http://www.w3.org/2001/XMLSchema-instance"
    xmlns:xlink="http://www.w3.org/1999/xlink"    xsi:schemaLocation="http://pear.php.net/dtd/rest.package    http://pear.php.net/dtd/rest.package.xsd">
 <n>File_SMBPasswd</n>
 <c>pear.php.net</c>
 <ca xlink:href="/rest/c/File+Formats">File Formats</ca>
 <l>BSD</l>
 <s>Class for managing SAMBA style password files.</s>
 <d>With this package, you can maintain smbpasswd-files, usually used by SAMBA.</d>
 <r xlink:href="/rest/r/file_smbpasswd"/>
</p>', 'text/xml');
$pearweb->addRESTConfig("http://pear.php.net/rest/r/file_smbpasswd/1.0.2.xml", '<?xml version="1.0" encoding="UTF-8" ?>
<r xmlns="http://pear.php.net/dtd/rest.release"
    xmlns:xsi="http://www.w3.org/2001/XMLSchema-instance"
    xmlns:xlink="http://www.w3.org/1999/xlink"
    xsi:schemaLocation="http://pear.php.net/dtd/rest.release
    http://pear.php.net/dtd/rest.release.xsd">
 <p xlink:href="/rest/p/file_smbpasswd">File_SMBPasswd</p>
 <c>pear.php.net</c>
 <v>1.0.2</v>
 <st>stable</st>
 <l>BSD</l>
 <m>mbretter</m>
 <s>Class for managing SAMBA style password files.</s>
 <d>With this package, you can maintain smbpasswd-files, usually used by SAMBA.
</d>
 <da>2005-05-08 05:11:38</da>
 <n>* The Account Flags field had the wrong length.
</n>
 <f>4947</f>
 <g>http://pear.php.net/get/File_SMBPasswd-1.0.2</g>
 <x xlink:href="package.1.0.2.xml"/>
</r>', 'text/xml');
$pearweb->addRESTConfig("http://pear.php.net/rest/r/file_smbpasswd/deps.1.0.2.txt", 'a:2:{i:1;a:4:{s:4:"type";s:3:"pkg";s:3:"rel";s:2:"ge";s:7:"version";s:5:"1.0.0";s:4:"name";s:10:"Crypt_CHAP";}i:2;a:3:{s:4:"type";s:3:"ext";s:3:"rel";s:3:"has";s:4:"name";s:5:"mhash";}}', 'text/xml');
$pearweb->addRESTConfig("http://pear.php.net/rest/p/packages.xml", '<?xml version="1.0" encoding="UTF-8" ?>
<a xmlns="http://pear.php.net/dtd/rest.allpackages"
    xmlns:xsi="http://www.w3.org/2001/XMLSchema-instance"
    xmlns:xlink="http://www.w3.org/1999/xlink"
    xsi:schemaLocation="http://pear.php.net/dtd/rest.allpackages
    http://pear.php.net/dtd/rest.allpackages.xsd">
<c>pear.php.net</c>
 <p>Archive_Tar</p>
 <p>Archive_Zip</p>
 <p>Auth</p>
 <p>Auth_HTTP</p>
 <p>Auth_PrefManager</p>
 <p>Auth_PrefManager2</p>
 <p>Auth_RADIUS</p>
 <p>Auth_SASL</p>
 <p>Benchmark</p>
 <p>Cache</p>
 <p>Cache_Lite</p>
 <p>Calendar</p>
 <p>CodeGen</p>
 <p>CodeGen_MySQL</p>
 <p>CodeGen_MySQL_Plugin</p>
 <p>CodeGen_MySQL_UDF</p>
 <p>CodeGen_PECL</p>
 <p>Config</p>
 <p>Console_Color</p>
 <p>Console_Getargs</p>
 <p>Console_Getopt</p>
 <p>Console_ProgressBar</p>
 <p>Console_Table</p>
 <p>Contact_AddressBook</p>
 <p>Contact_Vcard_Build</p>
 <p>Contact_Vcard_Parse</p>
 <p>Crypt_Blowfish</p>
 <p>Crypt_CBC</p>
 <p>Crypt_CHAP</p>
 <p>Crypt_HMAC</p>
 <p>Crypt_RC4</p>
 <p>Crypt_RSA</p>
 <p>Crypt_Xtea</p>
 <p>Crypt_XXTEA</p>
 <p>Date</p>
 <p>Date_Holidays</p>
 <p>DB</p>
 <p>DBA</p>
 <p>DBA_Relational</p>
 <p>DB_ado</p>
 <p>DB_DataObject</p>
 <p>DB_DataObject_FormBuilder</p>
 <p>DB_ldap</p>
 <p>DB_ldap2</p>
 <p>DB_NestedSet</p>
 <p>DB_odbtp</p>
 <p>DB_Pager</p>
 <p>DB_QueryTool</p>
 <p>DB_Sqlite_Tools</p>
 <p>DB_Table</p>
 <p>Event_Dispatcher</p>
 <p>File</p>
 <p>File_Archive</p>
 <p>File_Bittorrent</p>
 <p>File_DICOM</p>
 <p>File_DNS</p>
 <p>File_Find</p>
 <p>File_Fortune</p>
 <p>File_Fstab</p>
 <p>File_Gettext</p>
 <p>File_HtAccess</p>
 <p>File_IMC</p>
 <p>File_MARC</p>
 <p>File_Ogg</p>
 <p>File_Passwd</p>
 <p>File_PDF</p>
 <p>File_SearchReplace</p>
 <p>File_SMBPasswd</p>
 <p>File_XSPF</p>
 <p>FSM</p>
 <p>Games_Chess</p>
 <p>Genealogy_Gedcom</p>
 <p>Gtk2_EntryDialog</p>
 <p>Gtk2_ExceptionDump</p>
 <p>Gtk2_FileDrop</p>
 <p>Gtk2_IndexedComboBox</p>
 <p>Gtk2_PHPConfig</p>
 <p>Gtk2_ScrollingLabel</p>
 <p>Gtk2_VarDump</p>
 <p>Gtk_FileDrop</p>
 <p>Gtk_MDB_Designer</p>
 <p>Gtk_ScrollingLabel</p>
 <p>Gtk_Styled</p>
 <p>Gtk_VarDump</p>
 <p>HTML_AJAX</p>
 <p>HTML_BBCodeParser</p>
 <p>HTML_Common</p>
 <p>HTML_Common2</p>
 <p>HTML_Crypt</p>
 <p>HTML_CSS</p>
 <p>HTML_Form</p>
 <p>HTML_Javascript</p>
 <p>HTML_Menu</p>
 <p>HTML_Page</p>
 <p>HTML_Page2</p>
 <p>HTML_Progress</p>
 <p>HTML_Progress2</p>
 <p>HTML_QuickForm</p>
 <p>HTML_QuickForm2</p>
 <p>HTML_QuickForm_advmultiselect</p>
 <p>HTML_QuickForm_altselect</p>
 <p>HTML_QuickForm_CAPTCHA</p>
 <p>HTML_QuickForm_Controller</p>
 <p>HTML_QuickForm_DHTMLRulesTableless</p>
 <p>HTML_QuickForm_ElementGrid</p>
 <p>HTML_QuickForm_Livesearch</p>
 <p>HTML_QuickForm_Renderer_Tableless</p>
 <p>HTML_QuickForm_SelectFilter</p>
 <p>HTML_Safe</p>
 <p>HTML_Select</p>
 <p>HTML_Select_Common</p>
 <p>HTML_Table</p>
 <p>HTML_Table_Matrix</p>
 <p>HTML_TagCloud</p>
 <p>HTML_Template_Flexy</p>
 <p>HTML_Template_IT</p>
 <p>HTML_Template_PHPLIB</p>
 <p>HTML_Template_Sigma</p>
 <p>HTML_Template_Xipe</p>
 <p>HTML_TreeMenu</p>
 <p>HTTP</p>
 <p>HTTP_Client</p>
 <p>HTTP_Download</p>
 <p>HTTP_FloodControl</p>
 <p>HTTP_Header</p>
 <p>HTTP_Request</p>
 <p>HTTP_Server</p>
 <p>HTTP_Session</p>
 <p>HTTP_Session2</p>
 <p>HTTP_SessionServer</p>
 <p>HTTP_Upload</p>
 <p>HTTP_WebDAV_Client</p>
 <p>HTTP_WebDAV_Server</p>
 <p>I18N</p>
 <p>I18Nv2</p>
 <p>I18N_UnicodeString</p>
 <p>Image_3D</p>
 <p>Image_Barcode</p>
 <p>Image_Canvas</p>
 <p>Image_Color</p>
 <p>Image_Color2</p>
 <p>Image_GIS</p>
 <p>Image_Graph</p>
 <p>Image_GraphViz</p>
 <p>Image_IPTC</p>
 <p>Image_MonoBMP</p>
 <p>Image_Puzzle</p>
 <p>Image_Remote</p>
 <p>Image_Text</p>
 <p>Image_Tools</p>
 <p>Image_Transform</p>
 <p>Image_WBMP</p>
 <p>Image_XBM</p>
 <p>Inline_C</p>
 <p>LiveUser</p>
 <p>LiveUser_Admin</p>
 <p>Log</p>
 <p>Mail</p>
 <p>Mail_IMAP</p>
 <p>Mail_IMAPv2</p>
 <p>Mail_Mbox</p>
 <p>Mail_Mime</p>
 <p>Mail_mimeDecode</p>
 <p>Mail_Queue</p>
 <p>Math_Basex</p>
 <p>Math_BigInteger</p>
 <p>Math_BinaryUtils</p>
 <p>Math_Complex</p>
 <p>Math_Derivative</p>
 <p>Math_Fibonacci</p>
 <p>Math_Finance</p>
 <p>Math_Fraction</p>
 <p>Math_Histogram</p>
 <p>Math_Integer</p>
 <p>Math_Matrix</p>
 <p>Math_Numerical_RootFinding</p>
 <p>Math_Polynomial</p>
 <p>Math_Quaternion</p>
 <p>Math_RPN</p>
 <p>Math_Stats</p>
 <p>Math_TrigOp</p>
 <p>Math_Vector</p>
 <p>MDB</p>
 <p>MDB2</p>
 <p>MDB2_Driver_fbsql</p>
 <p>MDB2_Driver_ibase</p>
 <p>MDB2_Driver_mssql</p>
 <p>MDB2_Driver_mysql</p>
 <p>MDB2_Driver_mysqli</p>
 <p>MDB2_Driver_oci8</p>
 <p>MDB2_Driver_pgsql</p>
 <p>MDB2_Driver_querysim</p>
 <p>MDB2_Driver_sqlite</p>
 <p>MDB2_Schema</p>
 <p>MDB2_Table</p>
 <p>MDB_QueryTool</p>
 <p>Message</p>
 <p>MIME_Type</p>
 <p>MP3_Id</p>
 <p>MP3_IDv2</p>
 <p>MP3_Playlist</p>
 <p>Net_CDDB</p>
 <p>Net_CheckIP</p>
 <p>Net_Curl</p>
 <p>Net_Cyrus</p>
 <p>Net_Dict</p>
 <p>Net_Dig</p>
 <p>Net_DIME</p>
 <p>Net_DNS</p>
 <p>Net_DNSBL</p>
 <p>Net_Finger</p>
 <p>Net_FTP</p>
 <p>Net_FTP2</p>
 <p>Net_GameServerQuery</p>
 <p>Net_Geo</p>
 <p>Net_GeoIP</p>
 <p>Net_Growl</p>
 <p>Net_HL7</p>
 <p>Net_Ident</p>
 <p>Net_IDNA</p>
 <p>Net_IMAP</p>
 <p>Net_IPv4</p>
 <p>Net_IPv6</p>
 <p>Net_IRC</p>
 <p>Net_LDAP</p>
 <p>Net_LMTP</p>
 <p>Net_MAC</p>
 <p>Net_Monitor</p>
 <p>Net_MPD</p>
 <p>Net_NNTP</p>
 <p>Net_Ping</p>
 <p>Net_POP3</p>
 <p>Net_Portscan</p>
 <p>Net_Server</p>
 <p>Net_Sieve</p>
 <p>Net_SmartIRC</p>
 <p>Net_SMPP</p>
 <p>Net_SMPP_Client</p>
 <p>Net_SMS</p>
 <p>Net_SMTP</p>
 <p>Net_Socket</p>
 <p>Net_Traceroute</p>
 <p>Net_URL</p>
 <p>Net_UserAgent_Detect</p>
 <p>Net_UserAgent_Mobile</p>
 <p>Net_Whois</p>
 <p>Net_Wifi</p>
 <p>Numbers_Roman</p>
 <p>Numbers_Words</p>
 <p>OLE</p>
 <p>OpenDocument</p>
 <p>Pager</p>
 <p>Pager_Sliding</p>
 <p>Payment_Clieop</p>
 <p>Payment_DTA</p>
 <p>Payment_Process</p>
 <p>PEAR</p>
 <p>pearweb</p>
 <p>pearweb_channelxml</p>
 <p>pearweb_phars</p>
 <p>PEAR_Command_Packaging</p>
 <p>PEAR_Delegator</p>
 <p>PEAR_ErrorStack</p>
 <p>PEAR_Frontend_Gtk</p>
 <p>PEAR_Frontend_Gtk2</p>
 <p>PEAR_Frontend_Web</p>
 <p>PEAR_Info</p>
 <p>PEAR_PackageFileManager</p>
 <p>PEAR_PackageFileManager_Frontend</p>
 <p>PEAR_PackageFileManager_Frontend_Web</p>
 <p>PEAR_PackageFileManager_GUI_Gtk</p>
 <p>PEAR_PackageUpdate</p>
 <p>PEAR_PackageUpdate_Gtk2</p>
 <p>PEAR_PackageUpdate_Web</p>
 <p>PEAR_RemoteInstaller</p>
 <p>PHPDoc</p>
 <p>PhpDocumentor</p>
 <p>PHP_Annotation</p>
 <p>PHP_Archive</p>
 <p>PHP_Beautifier</p>
 <p>PHP_CodeSniffer</p>
 <p>PHP_Compat</p>
 <p>PHP_CompatInfo</p>
 <p>PHP_Fork</p>
 <p>PHP_LexerGenerator</p>
 <p>PHP_Parser</p>
 <p>PHP_ParserGenerator</p>
 <p>PHP_Parser_DocblockParser</p>
 <p>PHP_Shell</p>
 <p>QA_Peardoc_Coverage</p>
 <p>RDF</p>
 <p>RDF_N3</p>
 <p>RDF_NTriple</p>
 <p>RDF_RDQL</p>
 <p>Science_Chemistry</p>
 <p>ScriptReorganizer</p>
 <p>Search_Mnogosearch</p>
 <p>Services_Amazon</p>
 <p>Services_Blogging</p>
 <p>Services_Delicious</p>
 <p>Services_DynDNS</p>
 <p>Services_Ebay</p>
 <p>Services_ExchangeRates</p>
 <p>Services_Google</p>
 <p>Services_Hatena</p>
 <p>Services_OpenSearch</p>
 <p>Services_Pingback</p>
 <p>Services_Technorati</p>
 <p>Services_Trackback</p>
 <p>Services_W3C_HTMLValidator</p>
 <p>Services_Weather</p>
 <p>Services_Webservice</p>
 <p>Services_Yahoo</p>
 <p>Services_YouTube</p>
 <p>SOAP</p>
 <p>SOAP_Interop</p>
 <p>Spreadsheet_Excel_Writer</p>
 <p>SQL_Parser</p>
 <p>Stream_SHM</p>
 <p>Stream_Var</p>
 <p>Structures_BibTex</p>
 <p>Structures_DataGrid</p>
 <p>Structures_DataGrid_DataSource_Array</p>
 <p>Structures_DataGrid_DataSource_CSV</p>
 <p>Structures_DataGrid_DataSource_DataObject</p>
 <p>Structures_DataGrid_DataSource_DB</p>
 <p>Structures_DataGrid_DataSource_DBQuery</p>
 <p>Structures_DataGrid_DataSource_DBTable</p>
 <p>Structures_DataGrid_DataSource_Excel</p>
 <p>Structures_DataGrid_DataSource_MDB2</p>
 <p>Structures_DataGrid_DataSource_RSS</p>
 <p>Structures_DataGrid_DataSource_XML</p>
 <p>Structures_DataGrid_Renderer_Console</p>
 <p>Structures_DataGrid_Renderer_CSV</p>
 <p>Structures_DataGrid_Renderer_Flexy</p>
 <p>Structures_DataGrid_Renderer_HTMLSortForm</p>
 <p>Structures_DataGrid_Renderer_HTMLTable</p>
 <p>Structures_DataGrid_Renderer_Pager</p>
 <p>Structures_DataGrid_Renderer_Smarty</p>
 <p>Structures_DataGrid_Renderer_XLS</p>
 <p>Structures_DataGrid_Renderer_XML</p>
 <p>Structures_DataGrid_Renderer_XUL</p>
 <p>Structures_Form</p>
 <p>Structures_Form_Gtk2</p>
 <p>Structures_Graph</p>
 <p>Structures_LinkedList</p>
 <p>System_Command</p>
 <p>System_Folders</p>
 <p>System_Mount</p>
 <p>System_ProcWatch</p>
 <p>System_SharedMemory</p>
 <p>System_Socket</p>
 <p>System_WinDrives</p>
 <p>Testing_Selenium</p>
 <p>Text_CAPTCHA</p>
 <p>Text_CAPTCHA_Numeral</p>
 <p>Text_Diff</p>
 <p>Text_Figlet</p>
 <p>Text_Highlighter</p>
 <p>Text_Huffman</p>
 <p>Text_LanguageDetect</p>
 <p>Text_Password</p>
 <p>Text_PathNavigator</p>
 <p>Text_Statistics</p>
 <p>Text_TeXHyphen</p>
 <p>Text_Wiki</p>
 <p>Text_Wiki_BBCode</p>
 <p>Text_Wiki_Cowiki</p>
 <p>Text_Wiki_Creole</p>
 <p>Text_Wiki_Doku</p>
 <p>Text_Wiki_Mediawiki</p>
 <p>Text_Wiki_Tiki</p>
 <p>Translation</p>
 <p>Translation2</p>
 <p>Tree</p>
 <p>UDDI</p>
 <p>Validate</p>
 <p>Validate_AR</p>
 <p>Validate_AT</p>
 <p>Validate_AU</p>
 <p>Validate_BE</p>
 <p>Validate_CA</p>
 <p>Validate_CH</p>
 <p>Validate_DE</p>
 <p>Validate_DK</p>
 <p>Validate_ES</p>
 <p>Validate_FI</p>
 <p>Validate_Finance</p>
 <p>Validate_Finance_CreditCard</p>
 <p>Validate_FR</p>
 <p>Validate_IN</p>
 <p>Validate_IS</p>
 <p>Validate_ISPN</p>
 <p>Validate_IT</p>
 <p>Validate_LV</p>
 <p>Validate_NL</p>
 <p>Validate_NZ</p>
 <p>Validate_PL</p>
 <p>Validate_ptBR</p>
 <p>Validate_UK</p>
 <p>Validate_US</p>
 <p>Validate_ZA</p>
 <p>Var_Dump</p>
 <p>VersionControl_SVN</p>
 <p>VFS</p>
 <p>XML_Beautifier</p>
 <p>XML_CSSML</p>
 <p>XML_DB_eXist</p>
 <p>XML_DTD</p>
 <p>XML_FastCreate</p>
 <p>XML_Feed_Parser</p>
 <p>XML_fo2pdf</p>
 <p>XML_FOAF</p>
 <p>XML_HTMLSax</p>
 <p>XML_HTMLSax3</p>
 <p>XML_image2svg</p>
 <p>XML_Indexing</p>
 <p>XML_MXML</p>
 <p>XML_NITF</p>
 <p>XML_Parser</p>
 <p>XML_Query2XML</p>
 <p>XML_RDDL</p>
 <p>XML_RPC</p>
 <p>XML_RPC2</p>
 <p>XML_RSS</p>
 <p>XML_SaxFilters</p>
 <p>XML_Serializer</p>
 <p>XML_sql2xml</p>
 <p>XML_Statistics</p>
 <p>XML_SVG</p>
 <p>XML_svg2image</p>
 <p>XML_Transformer</p>
 <p>XML_Tree</p>
 <p>XML_Util</p>
 <p>XML_Wddx</p>
 <p>XML_XPath</p>
 <p>XML_XSLT_Wrapper</p>
 <p>XML_XUL</p>
</a>', 'text/xml');
$pearweb->addRESTConfig("http://pear.php.net/rest/r/auth/allreleases.xml", '<?xml version="1.0" encoding="UTF-8" ?>
<a xmlns="http://pear.php.net/dtd/rest.allreleases"
    xmlns:xsi="http://www.w3.org/2001/XMLSchema-instance" xmlns:xlink="http://www.w3.org/1999/xlink"     xsi:schemaLocation="http://pear.php.net/dtd/rest.allreleases
    http://pear.php.net/dtd/rest.allreleases.xsd">
 <p>Auth</p>
 <c>pear.php.net</c>
 <r><v>1.3.0r3</v><s>beta</s></r>
 <r><v>1.3.0r2</v><s>beta</s></r>
 <r><v>1.3.0r1</v><s>beta</s></r>
 <r><v>1.2.3</v><s>stable</s></r>
 <r><v>1.2.2</v><s>stable</s></r>
 <r><v>1.2.1</v><s>stable</s></r>
 <r><v>1.2.0</v><s>stable</s></r>
 <r><v>1.1.1</v><s>stable</s></r>
 <r><v>1.1.0</v><s>stable</s></r>
 <r><v>1.0.2</v><s>stable</s></r>
 <r><v>1.0.1</v><s>stable</s></r>
 <r><v>1.0</v><s></s></r>
</a>', 'text/xml');
$pearweb->addRESTConfig("http://pear.php.net/rest/r/auth/1.3.0r3.xml", '<?xml version="1.0" encoding="UTF-8" ?>
<r xmlns="http://pear.php.net/dtd/rest.release"
    xmlns:xsi="http://www.w3.org/2001/XMLSchema-instance"
    xmlns:xlink="http://www.w3.org/1999/xlink"
    xsi:schemaLocation="http://pear.php.net/dtd/rest.release
    http://pear.php.net/dtd/rest.release.xsd">
 <p xlink:href="/rest/p/auth">Auth</p>
 <c>pear.php.net</c>
 <v>1.3.0r3</v>
 <st>stable</st>
 <l>PHP License</l>
 <m>aashley</m>
 <s>Creating an authentication system.</s>
 <d>The PEAR::Auth package provides methods for creating an authentication
system using PHP.

Currently it supports the following storage containers to read/write
the login data:

* All databases supported by the PEAR database layer
* All databases supported by the MDB database layer
* All databases supported by the MDB2 database layer
* Plaintext files
* LDAP servers
* POP3 servers
* IMAP servers
* vpopmail accounts
* RADIUS
* SAMBA password files
* SOAP (Using either PEAR SOAP package or PHP5 SOAP extension)
* PEAR website
* Kerberos V servers
* SAP servers</d>
 <da>2007-03-22 19:58:57</da>
 <n>* Added missing optional dependancy on PEAR Log to package.xml
* Fixed Bug #10125: Auth_Container_LDAP::fetchData only fetching attributes for
  first entry.</n>
 <f>54156</f>
 <g>http://pear.php.net/get/Auth-1.3.0r3</g>
 <x xlink:href="package.1.3.0r3.xml"/>
</r>', 'text/xml');
$pearweb->addRESTConfig("http://pear.php.net/rest/r/auth_http/allreleases.xml", '<?xml version="1.0" encoding="UTF-8" ?>
<a xmlns="http://pear.php.net/dtd/rest.allreleases"
    xmlns:xsi="http://www.w3.org/2001/XMLSchema-instance" xmlns:xlink="http://www.w3.org/1999/xlink"     xsi:schemaLocation="http://pear.php.net/dtd/rest.allreleases
    http://pear.php.net/dtd/rest.allreleases.xsd">
 <p>Auth_HTTP</p>
 <c>pear.php.net</c>
 <r><v>2.1.6RC1</v><s>beta</s></r>
 <r><v>2.1.4</v><s>stable</s></r>
 <r><v>2.1.3rc1</v><s>beta</s></r>
 <r><v>2.1.1</v><s>beta</s></r>
 <r><v>2.1.0</v><s>beta</s></r>
 <r><v>2.1.0RC2</v><s>beta</s></r>
 <r><v>2.1RC1</v><s>beta</s></r>
 <r><v>2.0</v><s>stable</s></r>
 <r><v>1.0.1</v><s>stable</s></r>
 <r><v>1.0</v><s>stable</s></r>
</a>', 'text/xml');
$pearweb->addRESTConfig("http://pear.php.net/rest/r/auth_http/2.1.6RC1.xml", '<?xml version="1.0" encoding="UTF-8" ?>
<r xmlns="http://pear.php.net/dtd/rest.release"
    xmlns:xsi="http://www.w3.org/2001/XMLSchema-instance"
    xmlns:xlink="http://www.w3.org/1999/xlink"
    xsi:schemaLocation="http://pear.php.net/dtd/rest.release
    http://pear.php.net/dtd/rest.release.xsd">
 <p xlink:href="/rest/p/auth_http">Auth_HTTP</p>
 <c>pear.php.net</c>
 <v>2.1.6RC1</v>
 <st>beta</st>
 <l>PHP License</l>
 <m>hirokawa</m>
 <s>HTTP authentication</s>
 <d>The PEAR::Auth_HTTP class provides methods for creating an HTTP
authentication system using PHP, that is similar to Apache\'s
realm-based .htaccess authentication.</d>
 <da>2005-04-23 00:00:04</da>
 <n>- Fixed bug #4047.
     - Fixed backward compatibility with PHP 4.x
     - Added PHP_AUTH_DIGEST support.</n>
 <f>9294</f>
 <g>http://pear.php.net/get/Auth_HTTP-2.1.6RC1</g>
 <x xlink:href="package.2.1.6RC1.xml"/>
</r>', 'text/xml');
$pearweb->addRESTConfig("http://pear.php.net/rest/p/auth_http/info.xml", '<?xml version="1.0" encoding="UTF-8" ?>
<p xmlns="http://pear.php.net/dtd/rest.package"    xmlns:xsi="http://www.w3.org/2001/XMLSchema-instance"
    xmlns:xlink="http://www.w3.org/1999/xlink"    xsi:schemaLocation="http://pear.php.net/dtd/rest.package    http://pear.php.net/dtd/rest.package.xsd">
 <n>Auth_HTTP</n>
 <c>pear.php.net</c>
 <ca xlink:href="/rest/c/Authentication">Authentication</ca>
 <l>PHP License</l>
 <s>HTTP authentication</s>
 <d>The PEAR::Auth_HTTP class provides methods for creating an HTTP
authentication system using PHP, that is similar to Apache\'s
realm-based .htaccess authentication.</d>
 <r xlink:href="/rest/r/auth_http"/>
</p>', 'text/xml');
$pearweb->addRESTConfig("http://pear.php.net/rest/r/auth_http/deps.2.1.6RC1.txt", 'a:1:{s:8:"required";a:3:{s:3:"php";a:2:{s:3:"min";s:5:"4.1.0";s:3:"max";s:5:"6.0.0";}s:13:"pearinstaller";a:1:{s:3:"min";s:7:"1.4.0a2";}s:7:"package";a:3:{s:4:"name";s:4:"Auth";s:7:"channel";s:12:"pear.php.net";s:3:"min";s:5:"1.2.0";}}}', 'text/xml');
$pearweb->addRESTConfig("http://pear.php.net/rest/p/auth/info.xml", '<?xml version="1.0" encoding="UTF-8" ?>
<p xmlns="http://pear.php.net/dtd/rest.package"    xmlns:xsi="http://www.w3.org/2001/XMLSchema-instance"
    xmlns:xlink="http://www.w3.org/1999/xlink"    xsi:schemaLocation="http://pear.php.net/dtd/rest.package    http://pear.php.net/dtd/rest.package.xsd">
 <n>Auth</n>
 <c>pear.php.net</c>
 <ca xlink:href="/rest/c/Authentication">Authentication</ca>
 <l>PHP License</l>
 <s>Creating an authentication system.</s>
 <d>The PEAR::Auth package provides methods for creating an authentication
system using PHP.

Currently it supports the following storage containers to read/write
the login data:

* All databases supported by the PEAR database layer
* All databases supported by the MDB database layer
* All databases supported by the MDB2 database layer
* Plaintext files
* LDAP servers
* POP3 servers
* IMAP servers
* vpopmail accounts
* RADIUS
* SAMBA password files
* SOAP (Using either PEAR SOAP package or PHP5 SOAP extension)
* PEAR website
* Kerberos V servers
* SAP servers</d>
 <r xlink:href="/rest/r/auth"/>
</p>', 'text/xml');
$pearweb->addRESTConfig("http://pear.php.net/rest/r/auth/deps.1.3.0r3.txt", 'a:7:{i:1;a:5:{s:4:"type";s:3:"pkg";s:3:"rel";s:2:"ge";s:7:"version";s:5:"0.9.5";s:8:"optional";s:3:"yes";s:4:"name";s:11:"File_Passwd";}i:2;a:5:{s:4:"type";s:3:"pkg";s:3:"rel";s:2:"ge";s:7:"version";s:3:"1.3";s:8:"optional";s:3:"yes";s:4:"name";s:8:"Net_POP3";}i:3;a:4:{s:4:"type";s:3:"pkg";s:3:"rel";s:3:"has";s:8:"optional";s:3:"yes";s:4:"name";s:2:"DB";}i:4;a:4:{s:4:"type";s:3:"pkg";s:3:"rel";s:3:"has";s:8:"optional";s:3:"yes";s:4:"name";s:3:"MDB";}i:5;a:4:{s:4:"type";s:3:"pkg";s:3:"rel";s:3:"has";s:8:"optional";s:3:"yes";s:4:"name";s:4:"MDB2";}i:6;a:4:{s:4:"type";s:3:"pkg";s:3:"rel";s:3:"has";s:8:"optional";s:3:"yes";s:4:"name";s:11:"Auth_RADIUS";}i:7;a:4:{s:4:"type";s:3:"pkg";s:3:"rel";s:3:"has";s:8:"optional";s:3:"yes";s:4:"name";s:14:"File_SMBPasswd";}}', 'text/xml');
$_test_dep->setPHPVersion('4.3.10');
$_test_dep->setPEARVersion('1.4.0a10');
$p1 = dirname(__FILE__)  . DIRECTORY_SEPARATOR .
    'packages'. DIRECTORY_SEPARATOR . 'Auth_HTTP-2.1.4.tgz';
$p2 = dirname(__FILE__)  . DIRECTORY_SEPARATOR .
    'packages'. DIRECTORY_SEPARATOR . 'Auth-1.3.0r3.tgz';
$p3 = dirname(__FILE__)  . DIRECTORY_SEPARATOR .
    'packages'. DIRECTORY_SEPARATOR . 'Auth_HTTP-2.1.6RC1.tgz';
$pearweb->addHtmlConfig('http://pear.php.net/get/Auth_HTTP-2.1.6RC1.tgz', $p3);
$res = $command->run('install', array(), array($p1, $p2));
$phpunit->assertNoErrors('setup install');
$fakelog->getDownload();
$fakelog->getLog();
$config->set('preferred_state', 'alpha');
test_PEAR_Command_Install::_reset_downloader();
$res = $command->run('upgrade', array(), array('Auth_HTTP'));

$dummy = null;
$dl = &$command->getDownloader($dummy, array());
echoFakelog($fakelog);
echo 'tests done';
?>
--CLEAN--
<?php
require_once dirname(dirname(__FILE__)) . '/teardown.php.inc';
?>
--EXPECTF--
3;pear/Auth_HTTP: Skipping required dependency "pear/Auth" version 1.3.0r3, already installed as version 1.3.0r3
3;Downloading "http://pear.php.net/get/Auth_HTTP-2.1.6RC1.tgz"
1;downloading Auth_HTTP-2.1.6RC1.tgz ...
1;Starting to download Auth_HTTP-2.1.6RC1.tgz (9,294 bytes)
1;.
1;.
1;...done: 9,294 bytes
3;adding to transaction: backup %s/PEAR_Command_Install/testinstallertemp/php/Auth/HTTP.php
3;adding to transaction: delete %s/PEAR_Command_Install/testinstallertemp/php/Auth/HTTP.php
3;adding to transaction: backup %s/PEAR_Command_Install/testinstallertemp/test/Auth_HTTP/tests/sample.sql
3;adding to transaction: delete %s/PEAR_Command_Install/testinstallertemp/test/Auth_HTTP/tests/sample.sql
3;adding to transaction: backup %s/PEAR_Command_Install/testinstallertemp/test/Auth_HTTP/tests/test_basic_simple.php
3;adding to transaction: delete %s/PEAR_Command_Install/testinstallertemp/test/Auth_HTTP/tests/test_basic_simple.php
3;adding to transaction: backup %s/PEAR_Command_Install/testinstallertemp/test/Auth_HTTP/tests/test_digest_simple.php
3;adding to transaction: delete %s/PEAR_Command_Install/testinstallertemp/test/Auth_HTTP/tests/test_digest_simple.php
3;adding to transaction: backup %s/PEAR_Command_Install/testinstallertemp/test/Auth_HTTP/tests/test_digest_get.php
3;adding to transaction: delete %s/PEAR_Command_Install/testinstallertemp/test/Auth_HTTP/tests/test_digest_get.php
3;adding to transaction: backup %s/PEAR_Command_Install/testinstallertemp/test/Auth_HTTP/tests/test_digest_post.php
3;adding to transaction: delete %s/PEAR_Command_Install/testinstallertemp/test/Auth_HTTP/tests/test_digest_post.php
3;+ cp %s/Auth_HTTP-2.1.6RC1/tests/sample.sql %s/PEAR_Command_Install/testinstallertemp/test/Auth_HTTP/tests/.tmpsample.sql
2;md5sum ok: %s/PEAR_Command_Install/testinstallertemp/test/Auth_HTTP/tests/sample.sql
3;adding to transaction: chmod 6%d4 %s/PEAR_Command_Install/testinstallertemp/test/Auth_HTTP/tests/.tmpsample.sql
3;adding to transaction: rename %s/PEAR_Command_Install/testinstallertemp/test/Auth_HTTP/tests/.tmpsample.sql %s/PEAR_Command_Install/testinstallertemp/test/Auth_HTTP/tests/sample.sql 
3;adding to transaction: installed_as tests/sample.sql %s/PEAR_Command_Install/testinstallertemp/test/Auth_HTTP/tests/sample.sql %s/PEAR_Command_Install/testinstallertemp/test /Auth_HTTP/tests
3;+ cp %s/Auth_HTTP-2.1.6RC1/tests/test_basic_simple.php %s/PEAR_Command_Install/testinstallertemp/test/Auth_HTTP/tests/.tmptest_basic_simple.php
2;md5sum ok: %s/PEAR_Command_Install/testinstallertemp/test/Auth_HTTP/tests/test_basic_simple.php
3;adding to transaction: chmod 6%d4 %s/PEAR_Command_Install/testinstallertemp/test/Auth_HTTP/tests/.tmptest_basic_simple.php
3;adding to transaction: rename %s/PEAR_Command_Install/testinstallertemp/test/Auth_HTTP/tests/.tmptest_basic_simple.php %s/PEAR_Command_Install/testinstallertemp/test/Auth_HTTP/tests/test_basic_simple.php 
3;adding to transaction: installed_as tests/test_basic_simple.php %s/PEAR_Command_Install/testinstallertemp/test/Auth_HTTP/tests/test_basic_simple.php %s/PEAR_Command_Install/testinstallertemp/test /Auth_HTTP/tests
3;+ cp %s/Auth_HTTP-2.1.6RC1/tests/test_digest_get.php %s/PEAR_Command_Install/testinstallertemp/test/Auth_HTTP/tests/.tmptest_digest_get.php
2;md5sum ok: %s/PEAR_Command_Install/testinstallertemp/test/Auth_HTTP/tests/test_digest_get.php
3;adding to transaction: chmod 6%d4 %s/PEAR_Command_Install/testinstallertemp/test/Auth_HTTP/tests/.tmptest_digest_get.php
3;adding to transaction: rename %s/PEAR_Command_Install/testinstallertemp/test/Auth_HTTP/tests/.tmptest_digest_get.php %s/PEAR_Command_Install/testinstallertemp/test/Auth_HTTP/tests/test_digest_get.php 
3;adding to transaction: installed_as tests/test_digest_get.php %s/PEAR_Command_Install/testinstallertemp/test/Auth_HTTP/tests/test_digest_get.php %s/PEAR_Command_Install/testinstallertemp/test /Auth_HTTP/tests
3;+ cp %s/Auth_HTTP-2.1.6RC1/tests/test_digest_post.php %s/PEAR_Command_Install/testinstallertemp/test/Auth_HTTP/tests/.tmptest_digest_post.php
2;md5sum ok: %s/PEAR_Command_Install/testinstallertemp/test/Auth_HTTP/tests/test_digest_post.php
3;adding to transaction: chmod 6%d4 %s/PEAR_Command_Install/testinstallertemp/test/Auth_HTTP/tests/.tmptest_digest_post.php
3;adding to transaction: rename %s/PEAR_Command_Install/testinstallertemp/test/Auth_HTTP/tests/.tmptest_digest_post.php %s/PEAR_Command_Install/testinstallertemp/test/Auth_HTTP/tests/test_digest_post.php 
3;adding to transaction: installed_as tests/test_digest_post.php %s/PEAR_Command_Install/testinstallertemp/test/Auth_HTTP/tests/test_digest_post.php %s/PEAR_Command_Install/testinstallertemp/test /Auth_HTTP/tests
3;+ cp %s/Auth_HTTP-2.1.6RC1/tests/test_digest_simple.php %s/PEAR_Command_Install/testinstallertemp/test/Auth_HTTP/tests/.tmptest_digest_simple.php
2;md5sum ok: %s/PEAR_Command_Install/testinstallertemp/test/Auth_HTTP/tests/test_digest_simple.php
3;adding to transaction: chmod 6%d4 %s/PEAR_Command_Install/testinstallertemp/test/Auth_HTTP/tests/.tmptest_digest_simple.php
3;adding to transaction: rename %s/PEAR_Command_Install/testinstallertemp/test/Auth_HTTP/tests/.tmptest_digest_simple.php %s/PEAR_Command_Install/testinstallertemp/test/Auth_HTTP/tests/test_digest_simple.php 
3;adding to transaction: installed_as tests/test_digest_simple.php %s/PEAR_Command_Install/testinstallertemp/test/Auth_HTTP/tests/test_digest_simple.php %s/PEAR_Command_Install/testinstallertemp/test /Auth_HTTP/tests
3;+ cp %s/Auth_HTTP-2.1.6RC1/Auth_HTTP.php %s/PEAR_Command_Install/testinstallertemp/php/Auth/.tmpHTTP.php
2;md5sum ok: %s/PEAR_Command_Install/testinstallertemp/php/Auth/HTTP.php
3;adding to transaction: chmod 6%d4 %s/PEAR_Command_Install/testinstallertemp/php/Auth/.tmpHTTP.php
3;adding to transaction: rename %s/PEAR_Command_Install/testinstallertemp/php/Auth/.tmpHTTP.php %s/PEAR_Command_Install/testinstallertemp/php/Auth/HTTP.php 
3;adding to transaction: installed_as Auth_HTTP.php %s/PEAR_Command_Install/testinstallertemp/php/Auth/HTTP.php %s/PEAR_Command_Install/testinstallertemp/php /Auth
3;adding to transaction: removebackup %s/PEAR_Command_Install/testinstallertemp/php/Auth/HTTP.php
3;adding to transaction: removebackup %s/PEAR_Command_Install/testinstallertemp/test/Auth_HTTP/tests/sample.sql
3;adding to transaction: removebackup %s/PEAR_Command_Install/testinstallertemp/test/Auth_HTTP/tests/test_basic_simple.php
3;adding to transaction: removebackup %s/PEAR_Command_Install/testinstallertemp/test/Auth_HTTP/tests/test_digest_simple.php
3;adding to transaction: removebackup %s/PEAR_Command_Install/testinstallertemp/test/Auth_HTTP/tests/test_digest_get.php
3;adding to transaction: removebackup %s/PEAR_Command_Install/testinstallertemp/test/Auth_HTTP/tests/test_digest_post.php
2;about to commit 36 file operations for Auth_HTTP
3;+ backup %s/PEAR_Command_Install/testinstallertemp/php/Auth/HTTP.php to %s/PEAR_Command_Install/testinstallertemp/php/Auth/HTTP.php.bak
3;+ rm %s/PEAR_Command_Install/testinstallertemp/php/Auth/HTTP.php
3;+ backup %s/PEAR_Command_Install/testinstallertemp/test/Auth_HTTP/tests/sample.sql to %s/PEAR_Command_Install/testinstallertemp/test/Auth_HTTP/tests/sample.sql.bak
3;+ rm %s/PEAR_Command_Install/testinstallertemp/test/Auth_HTTP/tests/sample.sql
3;+ backup %s/PEAR_Command_Install/testinstallertemp/test/Auth_HTTP/tests/test_basic_simple.php to %s/PEAR_Command_Install/testinstallertemp/test/Auth_HTTP/tests/test_basic_simple.php.bak
3;+ rm %s/PEAR_Command_Install/testinstallertemp/test/Auth_HTTP/tests/test_basic_simple.php
3;+ backup %s/PEAR_Command_Install/testinstallertemp/test/Auth_HTTP/tests/test_digest_simple.php to %s/PEAR_Command_Install/testinstallertemp/test/Auth_HTTP/tests/test_digest_simple.php.bak
3;+ rm %s/PEAR_Command_Install/testinstallertemp/test/Auth_HTTP/tests/test_digest_simple.php
3;+ backup %s/PEAR_Command_Install/testinstallertemp/test/Auth_HTTP/tests/test_digest_get.php to %s/PEAR_Command_Install/testinstallertemp/test/Auth_HTTP/tests/test_digest_get.php.bak
3;+ rm %s/PEAR_Command_Install/testinstallertemp/test/Auth_HTTP/tests/test_digest_get.php
3;+ backup %s/PEAR_Command_Install/testinstallertemp/test/Auth_HTTP/tests/test_digest_post.php to %s/PEAR_Command_Install/testinstallertemp/test/Auth_HTTP/tests/test_digest_post.php.bak
3;+ rm %s/PEAR_Command_Install/testinstallertemp/test/Auth_HTTP/tests/test_digest_post.php
3;+ chmod 6%d4 %s/PEAR_Command_Install/testinstallertemp/test/Auth_HTTP/tests/.tmpsample.sql
3;+ mv %s/PEAR_Command_Install/testinstallertemp/test/Auth_HTTP/tests/.tmpsample.sql %s/PEAR_Command_Install/testinstallertemp/test/Auth_HTTP/tests/sample.sql
3;+ chmod 6%d4 %s/PEAR_Command_Install/testinstallertemp/test/Auth_HTTP/tests/.tmptest_basic_simple.php
3;+ mv %s/PEAR_Command_Install/testinstallertemp/test/Auth_HTTP/tests/.tmptest_basic_simple.php %s/PEAR_Command_Install/testinstallertemp/test/Auth_HTTP/tests/test_basic_simple.php
3;+ chmod 6%d4 %s/PEAR_Command_Install/testinstallertemp/test/Auth_HTTP/tests/.tmptest_digest_get.php
3;+ mv %s/PEAR_Command_Install/testinstallertemp/test/Auth_HTTP/tests/.tmptest_digest_get.php %s/PEAR_Command_Install/testinstallertemp/test/Auth_HTTP/tests/test_digest_get.php
3;+ chmod 6%d4 %s/PEAR_Command_Install/testinstallertemp/test/Auth_HTTP/tests/.tmptest_digest_post.php
3;+ mv %s/PEAR_Command_Install/testinstallertemp/test/Auth_HTTP/tests/.tmptest_digest_post.php %s/PEAR_Command_Install/testinstallertemp/test/Auth_HTTP/tests/test_digest_post.php
3;+ chmod 6%d4 %s/PEAR_Command_Install/testinstallertemp/test/Auth_HTTP/tests/.tmptest_digest_simple.php
3;+ mv %s/PEAR_Command_Install/testinstallertemp/test/Auth_HTTP/tests/.tmptest_digest_simple.php %s/PEAR_Command_Install/testinstallertemp/test/Auth_HTTP/tests/test_digest_simple.php
3;+ chmod 6%d4 %s/PEAR_Command_Install/testinstallertemp/php/Auth/.tmpHTTP.php
3;+ mv %s/PEAR_Command_Install/testinstallertemp/php/Auth/.tmpHTTP.php %s/PEAR_Command_Install/testinstallertemp/php/Auth/HTTP.php
3;+ rm backup of %s/PEAR_Command_Install/testinstallertemp/php/Auth/HTTP.php (%s/PEAR_Command_Install/testinstallertemp/php/Auth/HTTP.php.bak)
3;+ rm backup of %s/PEAR_Command_Install/testinstallertemp/test/Auth_HTTP/tests/sample.sql (%s/PEAR_Command_Install/testinstallertemp/test/Auth_HTTP/tests/sample.sql.bak)
3;+ rm backup of %s/PEAR_Command_Install/testinstallertemp/test/Auth_HTTP/tests/test_basic_simple.php (%s/PEAR_Command_Install/testinstallertemp/test/Auth_HTTP/tests/test_basic_simple.php.bak)
3;+ rm backup of %s/PEAR_Command_Install/testinstallertemp/test/Auth_HTTP/tests/test_digest_simple.php (%s/PEAR_Command_Install/testinstallertemp/test/Auth_HTTP/tests/test_digest_simple.php.bak)
3;+ rm backup of %s/PEAR_Command_Install/testinstallertemp/test/Auth_HTTP/tests/test_digest_get.php (%s/PEAR_Command_Install/testinstallertemp/test/Auth_HTTP/tests/test_digest_get.php.bak)
3;+ rm backup of %s/PEAR_Command_Install/testinstallertemp/test/Auth_HTTP/tests/test_digest_post.php (%s/PEAR_Command_Install/testinstallertemp/test/Auth_HTTP/tests/test_digest_post.php.bak)
2;successfully committed 36 file operations
3;adding to transaction: rmdir %s/PEAR_Command_Install/testinstallertemp/test/Auth_HTTP/tests
3;adding to transaction: rmdir %s/PEAR_Command_Install/testinstallertemp/test/Auth_HTTP
3;adding to transaction: rmdir %s/PEAR_Command_Install/testinstallertemp/php/Auth
2;about to commit 3 file operations for Auth_HTTP
2;successfully committed 3 file operations
array (
  'info' => 
  array (
    'data' => 'upgrade ok: channel://pear.php.net/Auth_HTTP-2.1.6RC1',
  ),
  'cmd' => 'upgrade',
)
tests done
