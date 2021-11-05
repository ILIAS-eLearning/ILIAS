<?php

$test_dir = dirname(__FILE__, 3);

// the returned data is invalid due to the paths violating
// the ILIAS naming scheme 'class.<ClassName>GUI.php'.

return array(
    'ilCtrlBaseClassTestGUI' => $test_dir . '/GUI/ilCtrlBaseClassTestGUI.php',
    'ilCtrlCommandClass1TestGUI' => $test_dir . '/GUI/class.ilCtrlCommandClass1Test.php',
    'ilCtrlCommandClass2TestGUI' => $test_dir . '/GUI/ilCtrlCommandClass2TestGUI.php',
);
