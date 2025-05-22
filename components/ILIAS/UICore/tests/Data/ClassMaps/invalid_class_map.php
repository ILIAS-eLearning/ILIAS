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

$data_dir = dirname(__DIR__, 3);

// the returned data is invalid due to the paths violating
// the ILIAS naming scheme 'class.<ClassName>GUI.php'.

return array(
    'ilCtrlBaseClass1TestGUI' => $data_dir . '/GUI/ilCtrlBaseClass1TestGUI.php',
    'ilCtrlCommandClass1TestGUI' => $data_dir . '/GUI/class.ilCtrlCommandClass1Test.php',
    'ilCtrlCommandClass2TestGUI' => $data_dir . '/GUI/ilCtrlCommandClass2TestGUI.php',
);
