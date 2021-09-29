<?php

require_once 'Services/UICore/classes/Setup/class.ilCtrlStructureReader.php';

$r = new ilCtrlStructureReader();
$a = $r->readStructure();

exit;