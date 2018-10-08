--TEST--
PEAR_Installer->install() with cfg file role
--SKIPIF--
<?php
if (!getenv('PHP_PEAR_RUNTESTS')) {
    echo 'skip';
}
?>
--FILE--
<?php
require_once dirname(__FILE__) . DIRECTORY_SEPARATOR . 'setup.php.inc';
$_test_dep->setPEARVersion('1.7.0');
$_test_dep->setPHPVersion('4.3.11');
$c1 = dirname(__FILE__)  . DIRECTORY_SEPARATOR .
    'packages'. DIRECTORY_SEPARATOR . 'cfg1.xml';
$c2 = dirname(__FILE__)  . DIRECTORY_SEPARATOR .
    'packages'. DIRECTORY_SEPARATOR . 'cfg2.xml';
$c3 = dirname(__FILE__)  . DIRECTORY_SEPARATOR .
    'packages'. DIRECTORY_SEPARATOR . 'cfg3.xml';
$c4 = dirname(__FILE__)  . DIRECTORY_SEPARATOR .
    'packages'. DIRECTORY_SEPARATOR . 'cfg4.xml';
$dp = new test_PEAR_Downloader($fakelog, array(), $config);
$phpunit->assertNoErrors('after create');
$result = $dp->download(array($c1));
$after = $dp->getDownloadedPackages();
$phpunit->assertEquals(1, count($after), 'after getdp count');
$phpunit->assertEquals(array (), $fakelog->getLog(), 'log messages');
$phpunit->assertEquals(array (
), $fakelog->getDownload(), 'download callback messages');

$installer->setOptions($dp->getOptions());
$installer->sortPackagesForInstall($result);
$installer->setDownloadedPackages($result);
$phpunit->assertNoErrors('set of downloaded packages');
$ret = $installer->install($result[0], $dp->getOptions());
$phpunit->assertNoErrors('after install');
$phpunit->assertEquals(array (
  'attribs' =>
  array (
    'version' => '2.0',
    'xmlns' => 'http://pear.php.net/dtd/package-2.0',
    'xmlns:tasks' => 'http://pear.php.net/dtd/tasks-1.0',
    'xmlns:xsi' => 'http://www.w3.org/2001/XMLSchema-instance',
    'xsi:schemaLocation' => 'http://pear.php.net/dtd/tasks-1.0 http://pear.php.net/dtd/tasks-1.0.xsd http://pear.php.net/dtd/package-2.0 http://pear.php.net/dtd/package-2.0.xsd',
  ),
  'name' => 'PEAR',
  'channel' => 'pear.php.net',
  'summary' => 'PEAR Base System',
  'description' => 'The PEAR package contains:',
  'lead' =>
  array (
    'name' => 'Stig Bakken',
    'user' => 'ssb',
    'email' => 'stig@php.net',
    'active' => 'yes',
  ),
  'date' => '2004-09-30',
  'version' =>
  array (
    'release' => '1.4.0',
    'api' => '1.4.0',
  ),
  'stability' =>
  array (
    'release' => 'stable',
    'api' => 'stable',
  ),
  'license' =>
  array (
    'attribs' =>
    array (
      'uri' => 'http://www.php.net/license/3_0.txt',
    ),
    '_content' => 'PHP License',
  ),
  'notes' => 'Installer Roles/Tasks:',
  'contents' =>
  array (
    'dir' =>
    array (
      'attribs' =>
      array (
        'name' => '/',
      ),
      'file' =>
      array (
        'attribs' =>
        array (
          'name' => 'foo.php',
          'role' => 'cfg',
        ),
      ),
    ),
  ),
  'dependencies' =>
  array (
    'required' =>
    array (
      'php' =>
      array (
        'min' => '4.2',
      ),
      'pearinstaller' =>
      array (
        'min' => '1.7.0',
      ),
    ),
  ),
  'phprelease' => '',
  'filelist' =>
  array (
    'foo.php' =>
    array (
      'name' => 'foo.php',
      'role' => 'cfg',
      'md5sum' => '718d8596a14d123d83afb0d5d6d6fd96',
      'installed_as' => $temp_path . DIRECTORY_SEPARATOR . 'cfg' . DIRECTORY_SEPARATOR . 'PEAR' .
        DIRECTORY_SEPARATOR . 'foo.php',
    ),
  ),
  '_lastversion' => NULL,
  'dirtree' =>
  array (
    $temp_path . DIRECTORY_SEPARATOR . 'cfg' . DIRECTORY_SEPARATOR . 'PEAR' => true,
  ),
  'old' =>
  array (
    'version' => '1.4.0',
    'release_date' => '2004-09-30',
    'release_state' => 'stable',
    'release_license' => 'PHP License',
    'release_notes' => 'Installer Roles/Tasks:',
    'release_deps' =>
    array (
      0 =>
      array (
        'type' => 'php',
        'rel' => 'ge',
        'version' => '4.2',
        'optional' => 'no',
      ),
      1 =>
      array (
        'type' => 'pkg',
        'channel' => 'pear.php.net',
        'name' => 'PEAR',
        'rel' => 'ge',
        'version' => '1.7.0',
        'optional' => 'no',
      ),
    ),
    'maintainers' =>
    array (
      0 =>
      array (
        'name' => 'Stig Bakken',
        'email' => 'stig@php.net',
        'active' => 'yes',
        'handle' => 'ssb',
        'role' => 'lead',
      ),
    ),
  ),
  'xsdversion' => '2.0',
), $ret, 'return of install');
$phpunit->assertFileExists($temp_path . DIRECTORY_SEPARATOR . 'cfg' . DIRECTORY_SEPARATOR .  'PEAR' . DIRECTORY_SEPARATOR . 'foo.php',
    'installed file');
$fakelog->getLog();

$dp = new test_PEAR_Downloader($fakelog, array('upgrade' => true), $config);
$result = $dp->download(array($c2));
$after = $dp->getDownloadedPackages();
$phpunit->assertEquals(1, count($after), 'after getdp count');
$phpunit->assertEquals(array (), $fakelog->getLog(), 'log messages');
$phpunit->assertEquals(array (
), $fakelog->getDownload(), 'download callback messages');

$installer->setOptions($dp->getOptions());
$installer->sortPackagesForInstall($result);
$installer->setDownloadedPackages($result);
$phpunit->assertNoErrors('set of downloaded packages');
$ret = $installer->install($result[0], $dp->getOptions());
$phpunit->assertNoErrors('after install');
$phpunit->assertFileExists($temp_path . DIRECTORY_SEPARATOR . 'cfg' . DIRECTORY_SEPARATOR .  'PEAR' . DIRECTORY_SEPARATOR . 'foo.php',
    'installed file');
$phpunit->assertFileNotExists($temp_path . DIRECTORY_SEPARATOR . 'cfg' . DIRECTORY_SEPARATOR .  'PEAR' . DIRECTORY_SEPARATOR . 'foo.php.new-1.4.1',
    'installed file');
$fakelog->getLog();

$fp = fopen($temp_path . DIRECTORY_SEPARATOR . 'cfg' . DIRECTORY_SEPARATOR .  'PEAR' . DIRECTORY_SEPARATOR . 'foo.php', 'w');
fwrite($fp, 'fix it up - woo');
fclose($fp);

$dp = new test_PEAR_Downloader($fakelog, array('upgrade' => true), $config);
$result = $dp->download(array($c3));
$after = $dp->getDownloadedPackages();
$phpunit->assertEquals(1, count($after), 'after getdp count');
$phpunit->assertEquals(array (), $fakelog->getLog(), 'log messages');
$phpunit->assertEquals(array (
), $fakelog->getDownload(), 'download callback messages');

$installer->setOptions($dp->getOptions());
$installer->sortPackagesForInstall($result);
$installer->setDownloadedPackages($result);
$phpunit->assertNoErrors('set of downloaded packages');
$ret = $installer->install($result[0], $dp->getOptions());
$phpunit->assertNoErrors('after install');
$phpunit->assertFileExists($temp_path . DIRECTORY_SEPARATOR . 'cfg' . DIRECTORY_SEPARATOR .  'PEAR' . DIRECTORY_SEPARATOR . 'foo.php',
    'installed file');
$phpunit->assertEquals(md5('fix it up - woo'), md5_file($temp_path . DIRECTORY_SEPARATOR . 'cfg' . DIRECTORY_SEPARATOR .  'PEAR' . DIRECTORY_SEPARATOR . 'foo.php'), 'md51');
$phpunit->assertFileExists($temp_path . DIRECTORY_SEPARATOR . 'cfg' . DIRECTORY_SEPARATOR .  'PEAR' . DIRECTORY_SEPARATOR . 'foo.php.new-1.4.2',
    'installed file');
$phpunit->assertEquals(md5_file(dirname(__FILE__) . DIRECTORY_SEPARATOR . 'packages' .
    DIRECTORY_SEPARATOR . 'foo.php'), md5_file($temp_path . DIRECTORY_SEPARATOR . 'cfg' . DIRECTORY_SEPARATOR .  'PEAR' . DIRECTORY_SEPARATOR . 'foo.php.new-1.4.2'), 'md52');
$fakelog->getLog();

$dp = new test_PEAR_Downloader($fakelog, array('upgrade' => true), $config);
$result = $dp->download(array($c4));
$after = $dp->getDownloadedPackages();
$phpunit->assertEquals(1, count($after), 'after getdp count');
$phpunit->assertEquals(array (), $fakelog->getLog(), 'log messages');
$phpunit->assertEquals(array (
), $fakelog->getDownload(), 'download callback messages');

$installer->setOptions($dp->getOptions());
$installer->sortPackagesForInstall($result);
$installer->setDownloadedPackages($result);
$phpunit->assertNoErrors('set of downloaded packages');
$ret = $installer->install($result[0], $dp->getOptions());
$phpunit->assertNoErrors('after install');
$arr = $fakelog->getLog();
$phpunit->assertEquals(array (
    0 => 0,
    1 => 'WARNING: configuration file ' . $cfg_dir . DIRECTORY_SEPARATOR . 'PEAR' . DIRECTORY_SEPARATOR . 'foo.php is being installed as ' . $cfg_dir . DIRECTORY_SEPARATOR . 'PEAR' . DIRECTORY_SEPARATOR . 'foo.php.new-1.4.3, you should manually merge in changes to the existing configuration file',
  ), $arr[2], 'after install log');
$phpunit->assertFileExists($temp_path . DIRECTORY_SEPARATOR . 'cfg' . DIRECTORY_SEPARATOR .  'PEAR' . DIRECTORY_SEPARATOR . 'foo.php',
    'installed file');
$phpunit->assertEquals(md5('fix it up - woo'), md5_file($temp_path . DIRECTORY_SEPARATOR . 'cfg' . DIRECTORY_SEPARATOR .  'PEAR' . DIRECTORY_SEPARATOR . 'foo.php'), 'md51');
$phpunit->assertFileNotExists($temp_path . DIRECTORY_SEPARATOR . 'cfg' . DIRECTORY_SEPARATOR .  'PEAR' . DIRECTORY_SEPARATOR . 'foo.php.new-1.4.2',
    'installed file should be erased');
$phpunit->assertFileExists($temp_path . DIRECTORY_SEPARATOR . 'cfg' . DIRECTORY_SEPARATOR .  'PEAR' . DIRECTORY_SEPARATOR . 'foo.php.new-1.4.3',
    'installed file');
$phpunit->assertEquals(md5_file(dirname(__FILE__) . DIRECTORY_SEPARATOR . 'packages' .
    DIRECTORY_SEPARATOR . 'foo.php'), md5_file($temp_path . DIRECTORY_SEPARATOR . 'cfg' . DIRECTORY_SEPARATOR .  'PEAR' . DIRECTORY_SEPARATOR . 'foo.php.new-1.4.3'), 'md52');$fakelog->getLog();

echo 'tests done';
?>
--CLEAN--
<?php
require_once dirname(__FILE__) . '/teardown.php.inc';
?>
--EXPECT--
tests done
