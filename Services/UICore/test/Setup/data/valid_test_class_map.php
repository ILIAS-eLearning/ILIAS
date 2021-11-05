<?php

$test_dir = dirname(__FILE__, 3);

// the returned data is valid due to the paths matching
// the ILIAS naming scheme 'class.<ClassName>GUI.php'.

return array(
    'ilCtrlBaseClassTestGUI' => $test_dir . '/GUI/class.ilCtrlBaseClassTestGUI.php',
    'ilCtrlCommandClass1TestGUI' => $test_dir . '/GUI/class.ilCtrlCommandClass1TestGUI.php',
    'ilCtrlCommandClass2TestGUI' => $test_dir . '/GUI/class.ilCtrlCommandClass2TestGUI.php',
);
