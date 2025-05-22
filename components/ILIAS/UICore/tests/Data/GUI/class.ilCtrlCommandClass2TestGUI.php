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

/**
 * Class ilCtrlCommandClass2TestGUI
 *
 * @author Thibeau Fuhrer <thf@studer-raimann.ch>
 *
 * @ilCtrl_IsCalledBy ilCtrlCommandClass2TestGUI: ilCtrlCommandClass1TestGUI
 */
class ilCtrlCommandClass2TestGUI
{
    public function executeCommand(): string
    {
        return self::class;
    }

    public function getHTML(?array $args = null): string
    {
        if (!empty($args)) {
            return $args[array_key_first($args)];
        }

        return 'foo';
    }
}
