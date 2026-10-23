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

namespace ILIAS\LTI\View;

/**
 * Delegates to ilCtrl of the legacy environment, which is not part of the bootstrap yet.
 *
 * @author Fabian Schmid <fabian@sr.solutions>
 */
final class NavigationLegacyProxy implements Navigation
{
    public function getCommand(): string
    {
        global $DIC;

        return (string) $DIC->ctrl()->getCmd();
    }

    public function getLinkTarget(array $class_path, string $command, array $parameters = []): string
    {
        global $DIC;

        $ctrl = $DIC->ctrl();
        $class = end($class_path);
        foreach ($parameters as $name => $value) {
            $ctrl->setParameterByClass($class, $name, $value);
        }

        return $ctrl->getLinkTargetByClass($class_path, $command);
    }

    public function redirectToURL(string $url): void
    {
        global $DIC;

        $DIC->ctrl()->redirectToURL($url);
    }
}
