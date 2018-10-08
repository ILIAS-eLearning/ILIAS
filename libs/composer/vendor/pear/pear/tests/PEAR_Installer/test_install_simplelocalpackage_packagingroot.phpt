--TEST--
PEAR_Installer->install() with simple local package.xml [packagingroot]
--SKIPIF--
<?php
if (!getenv('PHP_PEAR_RUNTESTS')) {
    echo 'skip';
}
?>
--FILE--
<?php
require_once dirname(__FILE__) . DIRECTORY_SEPARATOR . 'setup.php.inc';
$pathtopackagexml = dirname(__FILE__)  . DIRECTORY_SEPARATOR .
    'packages'. DIRECTORY_SEPARATOR . 'packagingroot.xml';
$dp = new test_PEAR_Downloader($fakelog, array('packagingroot' => $temp_path . DIRECTORY_SEPARATOR .
'installroot'), $config);
$phpunit->assertNoErrors('after create');
$result = $dp->download(array($pathtopackagexml));
$phpunit->assertEquals(1, count($result), 'return');
$phpunit->assertIsa('test_PEAR_Downloader_Package', $result[0], 'right class');
$phpunit->assertIsa('PEAR_PackageFile_v1', $pf = $result[0]->getPackageFile(), 'right kind of pf');
$phpunit->assertEquals('PEAR', $pf->getPackage(), 'right package');
$phpunit->assertEquals('pear.php.net', $pf->getChannel(), 'right channel');
$dlpackages = $dp->getDownloadedPackages();
$phpunit->assertEquals(1, count($dlpackages), 'downloaded packages count');
$phpunit->assertEquals(3, count($dlpackages[0]), 'internals package count');
$phpunit->assertEquals(array('file', 'info', 'pkg'), array_keys($dlpackages[0]), 'indexes');
$phpunit->assertEquals($pathtopackagexml,
    $dlpackages[0]['file'], 'file');
$phpunit->assertIsa('PEAR_PackageFile_v1',
    $dlpackages[0]['info'], 'info');
$phpunit->assertEquals('PEAR',
    $dlpackages[0]['pkg'], 'PEAR');
$after = $dp->getDownloadedPackages();
$phpunit->assertEquals(0, count($after), 'after getdp count');
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
  'provides' =>
  array (
  ),
  'filelist' =>
  array (
    'proot.php' =>
    array (
      'role' => 'php',
      'replacements' =>
      array (
        0 =>
        array (
          'type' => 'pear-config',
          'from' => '@test@',
          'to' => 'php_dir',
        ),
      ),
      'installed_as' => $temp_path . DIRECTORY_SEPARATOR . 'php' . DIRECTORY_SEPARATOR . 'proot.php',
    ),
  ),
  'xsdversion' => '1.0',
  'package' => 'PEAR',
  'summary' => 'PEAR Base System',
  'description' => 'The PEAR package contains:
 * the PEAR installer, for creating, distributing
   and installing packages
 * the alpha-quality PEAR_Exception PHP5 error handling mechanism
 * the beta-quality PEAR_ErrorStack advanced error handling mechanism
 * the PEAR_Error error handling mechanism
 * the OS_Guess class for retrieving info about the OS
   where PHP is running on
 * the System class for quick handling of common operations
   with files and directories
 * the PEAR base class
 
',
  'maintainers' =>
  array (
    array (
      'handle' => 'cellog',
      'role' => 'lead',
      'name' => 'Greg Beaver',
      'email' => 'cellog@php.net',
    ),
  ),
  'version' => '1.0.0',
  'release_date' => '2006-01-01',
  'release_license' => 'PHP License',
  'release_state' => 'stable',
  'release_notes' => 'test packagingroot option
',
  '_lastversion' => null,
  'dirtree' =>
  array (
    $php_dir => true,
  ),
), $ret, 'return of install');
$phpunit->assertFileExists($config->_prependPath($temp_path . DIRECTORY_SEPARATOR . 'php' . DIRECTORY_SEPARATOR . 'proot.php', $temp_path . DIRECTORY_SEPARATOR . 'installroot'),
    'installed file');
$phpunit->assertEquals("<?php \$a = '$php_dir'; ?>", file_get_contents($config->_prependPath($temp_path . DIRECTORY_SEPARATOR . 'php' . DIRECTORY_SEPARATOR . 'proot.php', $temp_path . DIRECTORY_SEPARATOR . 'installroot')), 'file contents');
$reg = &$config->getRegistry();
$info = $reg->packageInfo('PEAR');
$phpunit->assertNull($info, 'should not exist in default reg');
$reg = new PEAR_Registry($config->_prependPath($temp_path . DIRECTORY_SEPARATOR . 'php', $temp_path . DIRECTORY_SEPARATOR . 'installroot'));
$info = $reg->packageInfo('PEAR');
$phpunit->assertTrue(isset($info['_lastmodified']), 'lastmodified is set?');
unset($info['_lastmodified']);
$phpunit->assertEquals($ret, $info, 'test installation');
echo 'tests done';
?>
--CLEAN--
<?php
require_once dirname(__FILE__) . '/teardown.php.inc';
?>
--EXPECT--
tests done
