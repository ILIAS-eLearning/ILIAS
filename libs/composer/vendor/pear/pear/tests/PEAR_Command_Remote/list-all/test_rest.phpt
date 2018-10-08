--TEST--
list-all command (REST-based channel)
--SKIPIF--
<?php
if (!getenv('PHP_PEAR_RUNTESTS')) {
    echo 'skip';
}
?>
--FILE--
<?php

require_once dirname(dirname(__FILE__)) . DIRECTORY_SEPARATOR . 'setup.php.inc';
$reg = &$config->getRegistry();
$pf = new PEAR_PackageFile_v1;
$pf->setConfig($config);
$pf->setPackage('Archive_Zip');
$pf->setSummary('foo');
$pf->setDate(date('Y-m-d'));
$pf->setDescription('foo');
$pf->setVersion('1.0.0');
$pf->setState('stable');
$pf->setLicense('PHP License');
$pf->setNotes('foo');
$pf->addMaintainer('lead', 'cellog', 'Greg', 'cellog@php.net');
$pf->addFile('', 'foo.dat', array('role' => 'data'));
$pf->addPhpDep('4.0.0', 'ge');
$pf->validate();
$phpunit->assertNoErrors('setup');
$reg->addPackage2($pf);
$chan = $reg->getChannel('pear.php.net');
$chan->setBaseURL('REST1.0', 'http://pear.php.net/rest/');
$reg->updateChannel($chan);
$pearweb->addRESTConfig("http://pear.php.net/rest/p/packages.xml", '<?xml version="1.0" ?>
<a xmlns="http://pear.php.net/dtd/rest.allpackages"
    xsi:schemaLocation="http://pear.php.net/dtd/rest.allpackages
    http://pear.php.net/dtd/rest.allpackages.xsd">
<c>pear.php.net</c>
 <p>Archive_Tar</p>
 <p>Archive_Zip</p>
 <p>Auth</p>
 <p>Net_FTP</p>
 <p>PEAR</p>
 <p>PHP_Archive</p>
</a>', 'text/xml');
$pearweb->addRESTConfig("http://pear.php.net/rest/p/archive_tar/info.xml", '<?xml version="1.0"?>
<p xmlns="http://pear.php.net/dtd/rest.package"
    xsi:schemaLocation="http://pear.php.net/dtd/rest.package
    http://pear.php.net/dtd/rest.package.xsd">
 <n>Archive_Tar</n>
 <c>pear.php.net</c>
 <ca xlink:href="/rest/c/File+Formats">File Formats</ca>
 <l>PHP License</l>
 <s>Tar file management class</s>
 <d>This class provides handling of tar files in PHP.
It supports creating, listing, extracting and adding to tar files.
Gzip support is available if PHP has the zlib extension built-in or
loaded. Bz2 compression is also supported with the bz2 extension loaded.</d>
 <r xlink:href="/rest/r/archive_tar"/>
</p>', 'text/xml');
$pearweb->addRESTConfig("http://pear.php.net/rest/r/archive_tar/allreleases.xml", '<?xml version="1.0"?>
<a xmlns="http://pear.php.net/dtd/rest.allreleases"
    xsi:schemaLocation="http://pear.php.net/dtd/rest.allreleases
    http://pear.php.net/dtd/rest.allreleases.xsd">
 <p>Archive_Tar</p>
 <c>pear.php.net</c>
 <r><v>1.3.1</v><s>stable</s></r>
 <r><v>1.3.0</v><s>stable</s></r>
 <r><v>1.2</v><s>stable</s></r>
 <r><v>1.1</v><s>stable</s></r>
 <r><v>1.0</v><s>stable</s></r>
 <r><v>0.10-b1</v><s>beta</s></r>
 <r><v>0.9</v><s>stable</s></r>
 <r><v>0.4</v><s>stable</s></r>
 <r><v>0.3</v><s>stable</s></r>
</a>', 'text/xml');
$pearweb->addRESTConfig("http://pear.php.net/rest/r/archive_tar/deps.1.3.1.txt", 'b:0;', 'text/plain');
$pearweb->addRESTConfig("http://pear.php.net/rest/p/archive_zip/info.xml", '<?xml version="1.0"?>
<p xmlns="http://pear.php.net/dtd/rest.package"
    xsi:schemaLocation="http://pear.php.net/dtd/rest.package
    http://pear.php.net/dtd/rest.package.xsd">
 <n>Archive_Zip</n>
 <c>pear.php.net</c>
 <ca xlink:href="/rest/c/File+Formats">File Formats</ca>
 <l>PHP License</l>
 <s>Zip file management class</s>
 <d>This class provides handling of zip files in PHP.
It supports creating, listing, extracting and adding to zip files.</d>
 <r xlink:href="/rest/r/archive_zip"/>
</p>', 'text/xml');
$pearweb->addRESTConfig("http://pear.php.net/rest/r/archive_zip/allreleases.xml", false, false);
$pearweb->addRESTConfig("http://pear.php.net/rest/p/auth/info.xml", '<?xml version="1.0"?>
<p xmlns="http://pear.php.net/dtd/rest.package"
    xsi:schemaLocation="http://pear.php.net/dtd/rest.package
    http://pear.php.net/dtd/rest.package.xsd">
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
* SOAP</d>
 <r xlink:href="/rest/r/auth"/>
</p>', 'text/xml');
$pearweb->addRESTConfig("http://pear.php.net/rest/r/auth/allreleases.xml", '<?xml version="1.0"?>
<a xmlns="http://pear.php.net/dtd/rest.allreleases"
    xsi:schemaLocation="http://pear.php.net/dtd/rest.allreleases
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
$pearweb->addRESTConfig("http://pear.php.net/rest/r/auth/deps.1.3.0r3.txt", 'a:7:{i:1;a:5:{s:4:"type";s:3:"pkg";s:3:"rel";s:2:"ge";s:7:"version";s:5:"0.9.5";s:8:"optional";s:3:"yes";s:4:"name";s:11:"File_Passwd";}i:2;a:5:{s:4:"type";s:3:"pkg";s:3:"rel";s:2:"ge";s:7:"version";s:3:"1.3";s:8:"optional";s:3:"yes";s:4:"name";s:8:"Net_POP3";}i:3;a:4:{s:4:"type";s:3:"pkg";s:3:"rel";s:3:"has";s:8:"optional";s:3:"yes";s:4:"name";s:2:"DB";}i:4;a:4:{s:4:"type";s:3:"pkg";s:3:"rel";s:3:"has";s:8:"optional";s:3:"yes";s:4:"name";s:3:"MDB";}i:5;a:4:{s:4:"type";s:3:"pkg";s:3:"rel";s:3:"has";s:8:"optional";s:3:"yes";s:4:"name";s:4:"MDB2";}i:6;a:4:{s:4:"type";s:3:"pkg";s:3:"rel";s:3:"has";s:8:"optional";s:3:"yes";s:4:"name";s:11:"Auth_RADIUS";}i:7;a:4:{s:4:"type";s:3:"pkg";s:3:"rel";s:3:"has";s:8:"optional";s:3:"yes";s:4:"name";s:14:"File_SMBPasswd";}}', 'text/plain');
$pearweb->addRESTConfig("http://pear.php.net/rest/p/net_ftp/info.xml", '<?xml version="1.0"?>
<p xmlns="http://pear.php.net/dtd/rest.package"
    xsi:schemaLocation="http://pear.php.net/dtd/rest.package
    http://pear.php.net/dtd/rest.package.xsd">
 <n>Net_FTP</n>
 <c>pear.php.net</c>
 <ca xlink:href="/rest/c/Networking">Networking</ca>
 <l>PHP License</l>
 <s>Net_FTP provides an OO interface to the PHP FTP functions and some more advanced features in addition.</s>
 <d>Net_FTP allows you to communicate with FTP servers in a more comfortable way
than the native FTP functions of PHP do. The class implements everything nativly
supported by PHP and additionally features like recursive up- and downloading,
dircreation and chmodding. It although implements an observer pattern to allow
for example the view of a progress bar.</d>
 <r xlink:href="/rest/r/net_ftp"/>
</p>', 'text/xml');
$pearweb->addRESTConfig("http://pear.php.net/rest/r/net_ftp/allreleases.xml", '<?xml version="1.0"?>
<a xmlns="http://pear.php.net/dtd/rest.allreleases"
    xsi:schemaLocation="http://pear.php.net/dtd/rest.allreleases
    http://pear.php.net/dtd/rest.allreleases.xsd">
 <p>Net_FTP</p>
 <c>pear.php.net</c>
 <r><v>1.3.1</v><s>stable</s></r>
 <r><v>1.3.0</v><s>stable</s></r>
 <r><v>1.3.0RC2</v><s>beta</s></r>
 <r><v>1.3.0RC1</v><s>stable</s></r>
 <r><v>1.3.0beta4</v><s>beta</s></r>
 <r><v>1.3.0beta3</v><s>beta</s></r>
 <r><v>1.3.0beta2</v><s>beta</s></r>
 <r><v>1.3.0beta1</v><s>beta</s></r>
 <r><v>1.2</v><s>stable</s></r>
 <r><v>1.1</v><s>stable</s></r>
 <r><v>1.0</v><s>stable</s></r>
 <r><v>0.9</v><s>beta</s></r>
 <r><v>0.5</v><s>alpha</s></r>
</a>', 'text/xml');
$pearweb->addRESTConfig("http://pear.php.net/rest/r/net_ftp/deps.1.3.1.txt", 'a:1:{s:8:"required";a:3:{s:3:"php";a:2:{s:3:"min";s:5:"4.2.0";s:3:"max";s:5:"6.0.0";}s:13:"pearinstaller";a:1:{s:3:"min";s:7:"1.4.0a1";}s:9:"extension";a:1:{s:4:"name";s:3:"ftp";}}}', 'text/plain');
$pearweb->addRESTConfig("http://pear.php.net/rest/p/pear/info.xml", '<?xml version="1.0"?>
<p xmlns="http://pear.php.net/dtd/rest.package"
    xsi:schemaLocation="http://pear.php.net/dtd/rest.package
    http://pear.php.net/dtd/rest.package.xsd">
 <n>PEAR</n>
 <c>pear.php.net</c>
 <ca xlink:href="/rest/c/PEAR">PEAR</ca>
 <l>PHP License</l>
 <s>PEAR Base System</s>
 <d>The PEAR package contains:
 * the PEAR installer, for creating, distributing
   and installing packages
 * the alpha-quality PEAR_Exception PHP5 error handling mechanism
 * the beta-quality PEAR_ErrorStack advanced error handling mechanism
 * the PEAR_Error error handling mechanism
 * the OS_Guess class for retrieving info about the OS
   where PHP is running on
 * the System class for quick handling of common operations
   with files and directories
 * the PEAR base class</d>
 <r xlink:href="/rest/r/pear"/>
</p>', 'text/xml');
$pearweb->addRESTConfig("http://pear.php.net/rest/r/pear/allreleases.xml", '<?xml version="1.0"?>
<a xmlns="http://pear.php.net/dtd/rest.allreleases"
    xsi:schemaLocation="http://pear.php.net/dtd/rest.allreleases
    http://pear.php.net/dtd/rest.allreleases.xsd">
 <p>PEAR</p>
 <c>pear.php.net</c>
 <r><v>1.4.0a11</v><s>alpha</s></r>
 <r><v>1.4.0a10</v><s>alpha</s></r>
 <r><v>1.4.0a9</v><s>alpha</s></r>
 <r><v>1.4.0a8</v><s>alpha</s></r>
 <r><v>1.4.0a7</v><s>alpha</s></r>
 <r><v>1.4.0a6</v><s>alpha</s></r>
 <r><v>1.4.0a5</v><s>alpha</s></r>
 <r><v>1.4.0a4</v><s>alpha</s></r>
 <r><v>1.4.0a3</v><s>alpha</s></r>
 <r><v>1.4.0a2</v><s>alpha</s></r>
 <r><v>1.4.0a1</v><s>alpha</s></r>
 <r><v>1.3.5</v><s>stable</s></r>
 <r><v>1.3.4</v><s>stable</s></r>
 <r><v>1.3.3.1</v><s>stable</s></r>
 <r><v>1.3.3</v><s>stable</s></r>
 <r><v>1.3.1</v><s>stable</s></r>
 <r><v>1.3</v><s>stable</s></r>
 <r><v>1.3b6</v><s>beta</s></r>
 <r><v>1.3b5</v><s>beta</s></r>
 <r><v>1.3b3</v><s>beta</s></r>
 <r><v>1.3b2</v><s>beta</s></r>
 <r><v>1.3b1</v><s>beta</s></r>
 <r><v>1.2.1</v><s>stable</s></r>
 <r><v>1.2</v><s>stable</s></r>
 <r><v>1.2b5</v><s>beta</s></r>
 <r><v>1.2b4</v><s>beta</s></r>
 <r><v>1.2b3</v><s>beta</s></r>
 <r><v>1.2b2</v><s>beta</s></r>
 <r><v>1.2b1</v><s>beta</s></r>
 <r><v>1.1</v><s>stable</s></r>
 <r><v>1.0.1</v><s>stable</s></r>
 <r><v>1.0</v><s>stable</s></r>
 <r><v>1.0b3</v><s>stable</s></r>
 <r><v>1.0b2</v><s>stable</s></r>
 <r><v>1.0b1</v><s>stable</s></r>
 <r><v>0.91-dev</v><s>beta</s></r>
 <r><v>0.90</v><s>beta</s></r>
 <r><v>0.11</v><s>beta</s></r>
 <r><v>0.10</v><s>beta</s></r>
 <r><v>0.9</v><s>stable</s></r>
</a>', 'text/xml');
$pearweb->addRESTConfig("http://pear.php.net/rest/r/pear/deps.1.4.0a11.txt", 'a:3:{s:8:"required";a:4:{s:3:"php";a:2:{s:3:"min";s:3:"4.2";s:3:"max";s:5:"6.0.0";}s:13:"pearinstaller";a:1:{s:3:"min";s:7:"1.4.0a1";}s:7:"package";a:3:{i:0;a:5:{s:4:"name";s:11:"Archive_Tar";s:7:"channel";s:12:"pear.php.net";s:3:"min";s:3:"1.1";s:11:"recommended";s:5:"1.3.1";s:7:"exclude";s:5:"1.3.0";}i:1;a:4:{s:4:"name";s:14:"Console_Getopt";s:7:"channel";s:12:"pear.php.net";s:3:"min";s:3:"1.2";s:11:"recommended";s:3:"1.2";}i:2;a:4:{s:4:"name";s:7:"XML_RPC";s:7:"channel";s:12:"pear.php.net";s:3:"min";s:8:"1.2.0RC1";s:11:"recommended";s:5:"1.2.2";}}s:9:"extension";a:2:{i:0;a:1:{s:4:"name";s:3:"xml";}i:1;a:1:{s:4:"name";s:4:"pcre";}}}s:8:"optional";a:1:{s:7:"package";a:2:{i:0;a:3:{s:4:"name";s:17:"PEAR_Frontend_Web";s:7:"channel";s:12:"pear.php.net";s:3:"min";s:5:"0.5.0";}i:1;a:3:{s:4:"name";s:17:"PEAR_Frontend_Gtk";s:7:"channel";s:12:"pear.php.net";s:3:"min";s:5:"0.4.0";}}}s:5:"group";a:3:{i:0;a:2:{s:7:"attribs";a:2:{s:4:"hint";s:59:"adds the ability to install packages to a remote ftp server";s:4:"name";s:13:"remoteinstall";}s:7:"package";a:4:{s:4:"name";s:7:"Net_FTP";s:7:"channel";s:12:"pear.php.net";s:3:"min";s:8:"1.3.0RC1";s:11:"recommended";s:5:"1.3.0";}}i:1;a:2:{s:7:"attribs";a:2:{s:4:"hint";s:26:"PEAR\'s web-based installer";s:4:"name";s:12:"webinstaller";}s:7:"package";a:3:{s:4:"name";s:17:"PEAR_Frontend_Web";s:7:"channel";s:12:"pear.php.net";s:3:"min";s:5:"0.5.0";}}i:2;a:2:{s:7:"attribs";a:2:{s:4:"hint";s:30:"PEAR\'s PHP-GTK-based installer";s:4:"name";s:12:"gtkinstaller";}s:7:"package";a:3:{s:4:"name";s:17:"PEAR_Frontend_Gtk";s:7:"channel";s:12:"pear.php.net";s:3:"min";s:5:"0.4.0";}}}}', 'text/plain');
$pearweb->addRESTConfig("http://pear.php.net/rest/p/php_archive/info.xml", '<?xml version="1.0"?>
<p xmlns="http://pear.php.net/dtd/rest.package"
    xsi:schemaLocation="http://pear.php.net/dtd/rest.package
    http://pear.php.net/dtd/rest.package.xsd">
 <n>PHP_Archive</n>
 <c>pear.php.net</c>
 <ca xlink:href="/rest/c/PHP">PHP</ca>
 <l>PHP License</l>
 <s>Create and Use PHP Archive files</s>
 <d>PHP_Archive allows you to create a single .phar file containing an entire application.</d>
 <r xlink:href="/rest/r/php_archive"/>
</p>', 'text/xml');
$pearweb->addRESTConfig("http://pear.php.net/rest/r/php_archive/allreleases.xml", '<?xml version="1.0"?>
<a xmlns="http://pear.php.net/dtd/rest.allreleases"
    xsi:schemaLocation="http://pear.php.net/dtd/rest.allreleases
    http://pear.php.net/dtd/rest.allreleases.xsd">
 <p>PHP_Archive</p>
 <c>pear.php.net</c>
 <r><v>0.5.0</v><s>alpha</s></r>
</a>', 'text/xml');
$pearweb->addRESTConfig("http://pear.php.net/rest/r/php_archive/deps.0.5.0.txt", 'a:3:{i:1;a:4:{s:4:"type";s:3:"php";s:3:"rel";s:2:"ge";s:7:"version";s:5:"4.3.0";s:8:"optional";s:2:"no";}i:2;a:5:{s:4:"type";s:3:"pkg";s:3:"rel";s:2:"ge";s:7:"version";s:5:"1.3.5";s:8:"optional";s:2:"no";s:4:"name";s:4:"PEAR";}i:3;a:5:{s:4:"type";s:3:"pkg";s:3:"rel";s:2:"ge";s:7:"version";s:5:"1.3.1";s:8:"optional";s:2:"no";s:4:"name";s:11:"Archive_Tar";}}', 'text/plain');
$command->run('list-all', array(), array());
$phpunit->assertEquals(array (
  0 =>
  array (
    0 => 'http://pear.php.net/rest/p/packages.xml',
    1 => '200',
  ),
  array (
    0 => 'http://pear.php.net/rest/p/archive_tar/info.xml',
    1 => '200',
  ),
  array (
    0 => 'http://pear.php.net/rest/r/archive_tar/allreleases.xml',
    1 => '200',
  ),
  array (
    0 => 'http://pear.php.net/rest/r/archive_tar/deps.1.3.1.txt',
    1 => '200',
  ),
  array (
    0 => 'http://pear.php.net/rest/p/archive_zip/info.xml',
    1 => '200',
  ),
  array (
    0 => 'http://pear.php.net/rest/r/archive_zip/allreleases.xml',
    1 => '404',
  ),
  6 =>
  array (
    0 => 'http://pear.php.net/rest/p/auth/info.xml',
    1 => '200',
  ),
  7 =>
  array (
    0 => 'http://pear.php.net/rest/r/auth/allreleases.xml',
    1 => '200',
  ),
  8 =>
  array (
    0 => 'http://pear.php.net/rest/r/auth/deps.1.3.0r3.txt',
    1 => '200',
  ),
  9 =>
  array (
    0 => 'http://pear.php.net/rest/p/net_ftp/info.xml',
    1 => '200',
  ),
  10 =>
  array (
    0 => 'http://pear.php.net/rest/r/net_ftp/allreleases.xml',
    1 => '200',
  ),
  11 =>
  array (
    0 => 'http://pear.php.net/rest/r/net_ftp/deps.1.3.1.txt',
    1 => '200',
  ),
  12 =>
  array (
    0 => 'http://pear.php.net/rest/p/pear/info.xml',
    1 => '200',
  ),
  13 =>
  array (
    0 => 'http://pear.php.net/rest/r/pear/allreleases.xml',
    1 => '200',
  ),
  14 =>
  array (
    0 => 'http://pear.php.net/rest/r/pear/deps.1.4.0a11.txt',
    1 => '200',
  ),
  15 =>
  array (
    0 => 'http://pear.php.net/rest/p/php_archive/info.xml',
    1 => '200',
  ),
  16 =>
  array (
    0 => 'http://pear.php.net/rest/r/php_archive/allreleases.xml',
    1 => '200',
  ),
  17 =>
  array (
    0 => 'http://pear.php.net/rest/r/php_archive/deps.0.5.0.txt',
    1 => '200',
  ),
), $pearweb->getRESTCalls(), 'REST calls');
$phpunit->assertEquals(array (
  array (
    0 => 'Retrieving data...0%',
    1 => true,
  ),
  array (
    0 => '.',
    1 => false,
  ),
  array (
    0 => '.',
    1 => false,
  ),
  array (
    0 => '.',
    1 => false,
  ),
  array (
    0 => '.',
    1 => false,
  ),
  array (
    0 => '50%',
    1 => false,
  ),
  array (
    'info' =>
    array (
      'caption' => 'All packages [Channel pear.php.net]:',
      'border' => true,
      'headline' =>
      array (
        0 => 'Package',
        1 => 'Latest',
        2 => 'Local',
      ),
      'channel' => 'pear.php.net',
      'data' =>
      array (
        'File Formats' =>
        array (
          0 =>
          array (
            0 => 'pear/Archive_Tar',
            1 => '1.3.1',
            2 => NULL,
            3 => 'Tar file management class',
            4 =>
            array (
            ),
          ),
        ),
        'Authentication' =>
        array (
          0 =>
          array (
            0 => 'pear/Auth',
            1 => '1.3.0r3',
            2 => NULL,
            3 => 'Creating an authentication system.',
            4 =>
            array (
              0 =>
              array (
                'type' => 'pkg',
                'rel' => 'ge',
                'version' => '0.9.5',
                'optional' => 'yes',
                'name' => 'File_Passwd',
              ),
              1 =>
              array (
                'type' => 'pkg',
                'rel' => 'ge',
                'version' => '1.3',
                'optional' => 'yes',
                'name' => 'Net_POP3',
              ),
              2 =>
              array (
                'type' => 'pkg',
                'rel' => 'has',
                'optional' => 'yes',
                'name' => 'DB',
              ),
              3 =>
              array (
                'type' => 'pkg',
                'rel' => 'has',
                'optional' => 'yes',
                'name' => 'MDB',
              ),
              4 =>
              array (
                'type' => 'pkg',
                'rel' => 'has',
                'optional' => 'yes',
                'name' => 'MDB2',
              ),
              5 =>
              array (
                'type' => 'pkg',
                'rel' => 'has',
                'optional' => 'yes',
                'name' => 'Auth_RADIUS',
              ),
              6 =>
              array (
                'type' => 'pkg',
                'rel' => 'has',
                'optional' => 'yes',
                'name' => 'File_SMBPasswd',
              ),
            ),
          ),
        ),
        'Networking' =>
        array (
          0 =>
          array (
            0 => 'pear/Net_FTP',
            1 => '1.3.1',
            2 => NULL,
            3 => 'Net_FTP provides an OO interface to the PHP FTP functions and some more advanced features in addition.',
            4 =>
            array (
              0 =>
              array (
                'type' => 'pkg',
                'channel' => 'pear.php.net',
                'name' => 'PEAR',
                'rel' => 'ge',
                'version' => '1.4.0a1',
                'optional' => 'no',
              ),
            ),
          ),
        ),
        'PEAR' =>
        array (
          0 =>
          array (
            0 => 'pear/PEAR',
            1 => '1.4.0a11',
            2 => NULL,
            3 => 'PEAR Base System',
            4 =>
            array (
              0 =>
              array (
                'type' => 'pkg',
                'channel' => 'pear.php.net',
                'name' => 'PEAR',
                'rel' => 'ge',
                'version' => '1.4.0a1',
                'optional' => 'no',
              ),
              1 =>
              array (
                'type' => 'pkg',
                'channel' => 'pear.php.net',
                'name' => 'Archive_Tar',
                'rel' => 'ge',
                'version' => '1.1',
                'optional' => 'no',
              ),
              2 =>
              array (
                'type' => 'pkg',
                'channel' => 'pear.php.net',
                'name' => 'Console_Getopt',
                'rel' => 'ge',
                'version' => '1.2',
                'optional' => 'no',
              ),
              3 =>
              array (
                'type' => 'pkg',
                'channel' => 'pear.php.net',
                'name' => 'XML_RPC',
                'rel' => 'ge',
                'version' => '1.2.0RC1',
                'optional' => 'no',
              ),
              4 =>
              array (
                'type' => 'pkg',
                'channel' => 'pear.php.net',
                'name' => 'PEAR_Frontend_Web',
                'rel' => 'ge',
                'version' => '0.5.0',
                'optional' => 'yes',
              ),
              5 =>
              array (
                'type' => 'pkg',
                'channel' => 'pear.php.net',
                'name' => 'PEAR_Frontend_Gtk',
                'rel' => 'ge',
                'version' => '0.4.0',
                'optional' => 'yes',
              ),
            ),
          ),
        ),
        'PHP' =>
        array (
          0 =>
          array (
            0 => 'pear/PHP_Archive',
            1 => '0.5.0',
            2 => NULL,
            3 => 'Create and Use PHP Archive files',
            4 =>
            array (
              0 =>
              array (
                'type' => 'pkg',
                'rel' => 'ge',
                'version' => '1.3.5',
                'optional' => 'no',
                'name' => 'PEAR',
              ),
              1 =>
              array (
                'type' => 'pkg',
                'rel' => 'ge',
                'version' => '1.3.1',
                'optional' => 'no',
                'name' => 'Archive_Tar',
              ),
            ),
          ),
        ),
        'Local' =>
        array (
          0 =>
          array (
            0 => 'pear/Archive_Zip',
            1 => '',
            2 => '1.0.0',
            3 => 'foo',
            4 =>
            array (
              0 =>
              array (
                'type' => 'php',
                'rel' => 'ge',
                'version' => '4.0.0',
              ),
            ),
          ),
        ),
      ),
    ),
    'cmd' => 'list-all',
  ),
)
, $fakelog->getLog(), 'log');
echo 'tests done';
?>
--CLEAN--
<?php
require_once dirname(dirname(__FILE__)) . '/teardown.php.inc';
?>
--EXPECT--
tests done
