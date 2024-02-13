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
use ILIAS\Setup\Artifact\ArrayArtifact;
use ILIAS\Setup\Artifact;

/**
 * Class ilCtrlStructureArtifactObjective
 *
 * @author Fabian Schmid <fs@studer-raimann.ch>
 * @author Thibeau Fuhrer <thf@studer-raimann.ch>
 */
class ilCtrlStructureArtifactObjective extends BuildArtifactObjective
{

    public function getArtifactName(): string
    {
        return "ctrl_structure";
    }


    /**
     * @inheritDoc
     */
    public function build(): Artifact
    {
        $ilias_path = dirname(__FILE__, 7);
        $class_map = require $ilias_path . "/vendor/composer/vendor/composer/autoload_classmap.php";

        return new ArrayArtifact(
            (new ilCtrlStructureReader(
                new ilCtrlArrayIterator($class_map),
                new ilCtrlStructureCidGenerator()
            ))->readStructure()
        );
    }
}
