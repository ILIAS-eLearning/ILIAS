--TEST--
PEAR_PackageFile_Parser_v2->getTask
--SKIPIF--
<?php
if (!getenv('PHP_PEAR_RUNTESTS')) {
    echo 'skip';
}
?>
--FILE--
<?php
require_once dirname(__FILE__) . DIRECTORY_SEPARATOR . 'setup.php.inc';
class foolit extends PEAR_PackageFile_v2
{
    function setTasksNs($ns)
    {
        unset($this->_packageInfo['attribs']["xmlns:$this->_tasksNs"]);
        unset($this->_tasksNs);
        $this->_packageInfo['attribs']["xmlns:$ns"] = 'http://pear.php.net/dtd/tasks-1.0';
    }
}
$pathtopackagexml = dirname(__FILE__)  . DIRECTORY_SEPARATOR .
    'Parser'. DIRECTORY_SEPARATOR .
    'test_basicparse'. DIRECTORY_SEPARATOR . 'package2.xml';
$pf = $parser->parse(implode('', file($pathtopackagexml)), $pathtopackagexml);
$pf->flattenFilelist();
$phpunit->assertNoErrors('valid xml parse');
$phpunit->assertIsa('PEAR_PackageFile_v2', $pf, 'return of valid parse');
$foolit = new foolit;
$foolit->setConfig($config);
$foolit->fromArray($pf->getArray());
$ps = strtolower(substr(PHP_OS, 0, 3)) == 'win' ? ';' : PATH_SEPARATOR;
$d = DIRECTORY_SEPARATOR;
ini_set('include_path', dirname(__FILE__) . "${d}test_getTask" . $ps . ini_get('include_path'));
$phpunit->assertEquals('PEAR_Task_Gronk', $foolit->getTask('tasks:gronk'), 'tasks:gronk');

$foolit->setTasksNs('foo');
$phpunit->assertEquals('PEAR_Task_Gronk', $foolit->getTask('foo:gronk'), 'foo:gronk');
$phpunit->assertEquals('PEAR_Task_Foo_Gronk', $foolit->getTask('foo:foo-gronk'), 'foo:foo-gronk');
$phpunit->assertEquals('PEAR_Task_Replace', $foolit->getTask('foo:replace'), 'foo:replace');
$phpunit->assertFalse($foolit->getTask('foo:splut'), 'foo:splut');
$phpunit->assertFalse($foolit->getTask('groo:splut'), 'groo:splut');
echo 'tests done';
?>
--CLEAN--
<?php
require_once dirname(__FILE__) . '/teardown.php.inc';
?>
--EXPECT--
tests done
