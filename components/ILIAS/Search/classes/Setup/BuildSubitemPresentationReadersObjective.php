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

namespace ILIAS\Search\Setup;

use ILIAS\Setup\Artifact\BuildArtifactObjective as ilBuildArtifactObjective;
use ILIAS\Setup\Artifact as ilSetupArtifact;
use ILIAS\Setup\Artifact\ArrayArtifact as ilSetupArrayArtifact;
use ILIAS\Setup\ImplementationOfInterfaceFinder as ilSetupImplementationOfInterfaceFinder;
use ILIAS\Search\Presentation\Result\Subitem\PropertiesReader;

class BuildSubitemPresentationReadersObjective extends ilBuildArtifactObjective
{
    public function getArtifactName(): string
    {
        return "search_subitem_presentation_readers";
    }

    public function build(): ilSetupArtifact
    {
        $class_names = [];
        $finder = new ilSetupImplementationOfInterfaceFinder();
        $classes_by_type = [];
        foreach ($finder->getMatchingClassNames(PropertiesReader::class) as $class_name) {
            $classes_by_type[$class_name::type()] = $class_name;
        };
        return new ilSetupArrayArtifact($classes_by_type);
    }
}
