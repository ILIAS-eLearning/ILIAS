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

namespace ILIAS\Setup\Objective;

use ILIAS\Setup;

/**
 * Verify that the setip is called from ILIAS root directory.
 */
class CalledFromRootObjective implements Setup\Objective
{
    public function getHash(): string
    {
        return hash("sha256", self::class);
    }

    public function getLabel(): string
    {
        return "Verify that the setup is called from ilias' root-directory.";
    }

    public function isNotable(): bool
    {
        return true;
    }

    public function getPreconditions(Setup\Environment $environment): array
    {
        return [];
    }

    public function achieve(Setup\Environment $environment): Setup\Environment
    {
        $ilroot = dirname(__DIR__, 5);
        if (getcwd() !== $ilroot) {
            $msg = "Please run the setup from ILIAS root - "
                . "there are components using relative pathes.";
            throw new Setup\UnachievableException($msg);
        }
        return $environment;
    }

    public function isApplicable(Setup\Environment $environment): bool
    {
        return true;
    }
}
