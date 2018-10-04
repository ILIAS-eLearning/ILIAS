--TEST--
PEAR_PackageFile_Parser_v2->flattenFilelist()
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
    ),
    'file' => 
    array (
      'attribs' => 
      array (
        'name' => 'Already/Flattened.php',
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
    ),
    'file' => 
    array (
      'attribs' => 
      array (
        'name' => 'Already/Flattened.php',
        'role' => 'php',
      ),
    ),
  ),
), $tester->getContents(), 'already flattened 1');
$tester->setContents(array (
  'dir' => 
  array (
    'attribs' => 
    array (
      'name' => '/',
    ),
    'file' => 
    array (
      array(
        'attribs' => 
        array (
          'name' => 'Already/Flattened.php',
          'role' => 'php',
        ),
      ),
      array(
        'attribs' => 
        array (
          'name' => 'Already/Flattened2.php',
          'role' => 'php',
        ),
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
    ),
    'file' => 
    array (
      array(
        'attribs' => 
        array (
          'name' => 'Already/Flattened.php',
          'role' => 'php',
        ),
      ),
      array(
        'attribs' => 
        array (
          'name' => 'Already/Flattened2.php',
          'role' => 'php',
        ),
      ),
    ),
  ),
), $tester->getContents(), 'already flattened 2');

$tester->setContents(array (
  'dir' => 
  array (
    'attribs' => 
    array (
      'name' => '/',
    ),
    'dir' =>
    array (
      'attribs' =>
      array (
        'name' => 'Next',
      ),
      'file' => 
      array(
        'attribs' => 
        array (
          'name' => 'Willbe/Flattened.php',
          'role' => 'php',
        ),
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
    ),
    'file' => 
    array (
      'attribs' => 
      array (
        'name' => 'Next/Willbe/Flattened.php',
        'role' => 'php',
      ),
    ),
  ),
), $tester->getContents(), 'not flattened simple');

$tester->setContents(array (
  'dir' => 
  array (
    'attribs' => 
    array (
      'name' => '/',
    ),
    'dir' =>
    array (
      'attribs' =>
      array (
        'name' => 'Next',
      ),
      'file' => 
      array (
        array(
          'attribs' => 
          array (
            'name' => 'Willbe/Flattened.php',
            'role' => 'php',
          ),
        ),
        array(
          'attribs' => 
          array (
            'name' => 'Willbe/Flattened2.php',
            'role' => 'php',
          ),
        ),
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
    ),
    'file' => 
    array (
      0 => 
      array (
        'attribs' => 
        array (
          'name' => 'Next/Willbe/Flattened.php',
          'role' => 'php',
        ),
      ),
      1 => 
      array (
        'attribs' => 
        array (
          'name' => 'Next/Willbe/Flattened2.php',
          'role' => 'php',
        ),
      ),
    ),
  ),
), $tester->getContents(), 'not flattened simple 2');

$tester->setContents(array (
  'dir' => 
  array (
    'attribs' => 
    array (
      'name' => '/',
    ),
    'dir' =>
    array (
      'attribs' =>
      array (
        'name' => 'Next',
      ),
      'dir' =>
      array (
        'attribs' =>
        array (
          'baseinstalldir' => 'Fluh',
          'name' => 'Grom',
        ),
        'dir' =>
        array (
          'attribs' =>
          array (
            'name' => 'dork'
          ),
          'file' =>
          array (
            'attribs' =>
            array (
              'name' => 'Furm.php',
              'role' => 'php',
              'tasks:replace' =>
              array(
                'attribs' =>
                array (
                  'from' => '1',
                  'to' => 'version',
                  'type' => 'package-info',
                )
              )
            ),
          ),
        ),
        'file' =>
        array (
          'attribs' =>
          array (
            'name' => 'Boop.dta',
            'role' => 'data',
          ),
        ),
      ),
      'file' => 
      array (
        array(
          'attribs' => 
          array (
            'name' => 'Willbe/Flattened.php',
            'role' => 'php',
          ),
        ),
        array(
          'attribs' => 
          array (
            'name' => 'Willbe/Flattened2.php',
            'role' => 'php',
          ),
        ),
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
    ),
    'file' => 
    array (
      0 => 
      array (
        'attribs' => 
        array (
          'name' => 'Next/Grom/dork/Furm.php',
          'role' => 'php',
          'tasks:replace' => 
          array (
            'attribs' => 
            array (
              'from' => '1',
              'to' => 'version',
              'type' => 'package-info',
            ),
          ),
          'baseinstalldir' => 'Fluh',
        ),
      ),
      1 => 
      array (
        'attribs' => 
        array (
          'name' => 'Next/Grom/Boop.dta',
          'role' => 'data',
          'baseinstalldir' => 'Fluh',
        ),
      ),
      2 => 
      array (
        'attribs' => 
        array (
          'name' => 'Next/Willbe/Flattened.php',
          'role' => 'php',
        ),
      ),
      3 => 
      array (
        'attribs' => 
        array (
          'name' => 'Next/Willbe/Flattened2.php',
          'role' => 'php',
        ),
      ),
    ),
  ),
), $tester->getContents(), 'complex');
echo 'tests done';
?>
--CLEAN--
<?php
require_once dirname(__FILE__) . '/teardown.php.inc';
?>
--EXPECT--
tests done
