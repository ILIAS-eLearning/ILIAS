--TEST--
PEAR_PackageFile_Parser_v2->flattenFilelist() [single dir, with baseinstalldir]
--SKIPIF--
<?php
if (!getenv('PHP_PEAR_RUNTESTS')) {
    echo 'skip';
}
?>
--FILE--
<?php
require_once dirname(__FILE__) . DIRECTORY_SEPARATOR . 'setup.php.inc';
class testFilelist extends PEAR_PackageFile_v2
{
    function setContents($contents)
    {
        $this->_packageInfo['contents'] = $contents;
    }
}
$tester = new testFilelist;
$tester->setContents(array (
  'dir' => 
  array (
    'attribs' => 
    array (
      'name' => '/',
      'baseinstalldir' => 'Already',
    ),
    'file' => 
    array (
      'attribs' => 
      array (
        'name' => 'Flattened.php',
        'role' => 'php',
      ),
    ),
  ),
));
$tester->flattenFilelist();
$phpunit->assertEquals(array (
  'dir' => 
  array (
    'attribs' => 
    array (
      'name' => '/',
      'baseinstalldir' => 'Already',
    ),
    'file' => 
    array (
      'attribs' => 
      array (
        'name' => 'Flattened.php',
        'role' => 'php',
        'baseinstalldir' => 'Already',
      ),
    ),
  ),
), $tester->getContents(), 'flattened');
echo 'tests done';
?>
--CLEAN--
<?php
require_once dirname(__FILE__) . '/teardown.php.inc';
?>
--EXPECT--
tests done
