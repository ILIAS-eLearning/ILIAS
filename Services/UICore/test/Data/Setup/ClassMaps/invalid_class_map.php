<?php declare(strict_types = 1);

/* Copyright (c) 2021 Thibeau Fuhrer <thf@studer-raimann.ch> Extended GPL, see docs/LICENSE */

$data_dir = dirname(__DIR__, 2);

// the returned data is invalid due to the paths violating
// the ILIAS naming scheme 'class.<ClassName>GUI.php'.

return array(
    'ilCtrlBaseClassTestGUI' => $data_dir . '/GUI/ilCtrlBaseClassTestGUI.php',
    'ilCtrlCommandClass1TestGUI' => $data_dir . '/GUI/class.ilCtrlCommandClass1Test.php',
    'ilCtrlCommandClass2TestGUI' => $data_dir . '/GUI/ilCtrlCommandClass2TestGUI.php',
);
