<?php

declare(strict_types=1);

/* Copyright (c) 2021 Thibeau Fuhrer <thf@studer-raimann.ch> Extended GPL, see docs/LICENSE */

$data_dir = dirname(__DIR__);

// the returned data is valid due to the paths matching
// the ILIAS naming scheme 'class.<ClassName>GUI.php'.

return array(
    'ilCtrlBaseClass1TestGUI' => $data_dir . '/GUI/class.ilCtrlBaseClass1TestGUI.php',
    'ilCtrlBaseClass2TestGUI' => $data_dir . '/GUI/class.ilCtrlBaseClass2TestGUI.php',
    'ilCtrlCommandClass1TestGUI' => $data_dir . '/GUI/class.ilCtrlCommandClass1TestGUI.php',
    'ilCtrlCommandClass2TestGUI' => $data_dir . '/GUI/class.ilCtrlCommandClass2TestGUI.php',
    'ILIAS\\Tests\\Ctrl\\ilCtrlNamespacedTestGUI' => $data_dir . '/GUI/class.ilCtrlNamespacedTestGUI.php',
);
