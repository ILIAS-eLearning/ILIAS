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

require_once __DIR__ . "/../../../../../../vendor/composer/vendor/autoload.php";

use ILIAS\Setup\Artifact\BuildArtifactObjective;
use ILIAS\Setup\ImplementationOfInterfaceFinder;
use ILIAS\Setup\Artifact\ArrayArtifact;
use ILIAS\Setup\Artifact;

/**
 * Class ilCtrlSecurityArtifactObjective
 *
 * @author Thibeau Fuhrer <thf@studer-raimann.ch>
 */
class ilCtrlSecurityArtifactObjective extends BuildArtifactObjective
{

    public function getArtifactName(): string
    {
        return "ctrl_security";
    }


    /**
     * @inheritDoc
     */
    public function build(): Artifact
    {
        $finder = new ImplementationOfInterfaceFinder();

        $security_information = [];
        foreach ($finder->getMatchingClassNames(ilCtrlSecurityInterface::class) as $class) {
            try {
                $reflection = new ReflectionClass($class);

                /** @var $gui_object ilCtrlSecurityInterface */
                $gui_object = $reflection->newInstanceWithoutConstructor();

                $security_information[strtolower($class)] = [
                    ilCtrlStructureInterface::KEY_UNSAFE_COMMANDS => $gui_object->getUnsafeGetCommands(),
                    ilCtrlStructureInterface::KEY_SAFE_COMMANDS => $gui_object->getSafePostCommands(),
                ];
            } catch (ReflectionException $e) {
                continue;
            }
        }

        return new ArrayArtifact($security_information);
    }
}
