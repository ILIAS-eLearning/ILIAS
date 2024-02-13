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

use ILIAS\Setup\Artifact\BuildArtifactObjective;
use ILIAS\Setup\ImplementationOfInterfaceFinder;
use ILIAS\Setup\Artifact\ArrayArtifact;
use ILIAS\Setup\Artifact;

/**
 * Class ilCtrlSecurityArtifactObjective
 *
 * @author Thibeau Fuhrer <thf@studer-raimann.ch>
 */
class ilCtrlBaseClassArtifactObjective extends BuildArtifactObjective
{

    public function getArtifactName(): string
    {
        return "ctrl_base_classes";
    }

    /**
     * @inheritDoc
     */
    public function build(): Artifact
    {
        $finder = new ImplementationOfInterfaceFinder();

        $base_classes = [];
        foreach ($finder->getMatchingClassNames(ilCtrlBaseClassInterface::class) as $base_class) {
            $base_classes[] = strtolower($base_class);
        }

        return new ArrayArtifact($base_classes);
    }
}
