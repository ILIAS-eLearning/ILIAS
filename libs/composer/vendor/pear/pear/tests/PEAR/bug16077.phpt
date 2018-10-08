--TEST--
Bug #16077: PEAR5::getStaticProperty does not return a reference to the property
--FILE--
<?php
require_once 'PEAR.php';

$skiptrace = &PEAR::getStaticProperty('PEAR_Error', 'skiptrace');
$skiptrace = true;
var_dump(PEAR::getStaticProperty('PEAR_Error', 'skiptrace'));
?>
--EXPECT--
bool(true)
