--TEST--
PEAR_ErrorStack callback, returns PEAR_ERRORSTACK_DIE
--SKIPIF--
<?php
if (!getenv('PHP_PEAR_RUNTESTS')) {
    echo 'skip';
}
?>
--FILE--
<?php
require_once dirname(__FILE__) . DIRECTORY_SEPARATOR . 'setup.php.inc';
function returnsdie($err)
{
    echo 'callback called';
    return PEAR_ERRORSTACK_DIE;
}
class Dielog
{
    var $info;
    function log($err)
    {
        echo 'logged';
    }
}
$stack->pushCallback('returnsdie');
$log = new Dielog;
$a = array(&$log, 'log');
$stack->setLogger($a);
$stack->push(1);
echo 'should not see this!';
?>
--EXPECT--
callback calledlogged
