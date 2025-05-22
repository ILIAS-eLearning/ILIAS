<?php

/**
 * This file is part of ILIAS, a powerful learning management system
 * published by ILIAS open source e-Learning e.V.
 *
 * ILIAS is licensed with the GPL-3.0,
 * see https://www.gnu.org/licenses/gpl-3.0.en.html
 * You should have received a copy of said license along with the
 * source code, too.
 *
 * If this is not the case or you just want to try ILIAS, you'll find
 * us at:
 * https://www.ilias.de
 * https://github.com/ILIAS-eLearning
 *
 *********************************************************************/

declare(strict_types=1);

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
