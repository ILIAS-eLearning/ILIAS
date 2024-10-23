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

namespace ILIAS\Export\Setup;

use ILIAS\Setup\Artifact\BuildArtifactObjective as ilBuildArtifactObjective;
use ILIAS\Setup\Artifact as ilSetupArtifact;
use ILIAS\Setup\Artifact\ArrayArtifact as ilSetupArrayArtifact;
use ILIAS\Setup\ImplementationOfInterfaceFinder as ilSetupImplementationOfInterfaceFinder;
use ILIAS\Export\ExportHandler\I\Consumer\ExportOption\HandlerInterface as ilExportHandlerConsumerExportOptionInterface;

class BuildExportOptionsMapObjective extends ilBuildArtifactObjective
{
    public function getArtifactName(): string
    {
        return "export_options";
    }

    public function build(): ilSetupArtifact
    {
        $class_names = [];
        $interface = ilExportHandlerConsumerExportOptionInterface::class;
        $finder = new ilSetupImplementationOfInterfaceFinder();
        return new ilSetupArrayArtifact(iterator_to_array(
            $finder->getMatchingClassNames($interface)
        ));
    }
}
