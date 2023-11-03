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
 ********************************************************************
 */

use ILIAS\Setup;

class ilComponentBuildComponentInfoObjective extends Setup\Artifact\BuildArtifactObjective
{
    public function getArtifactPath(): string
    {
        return \ilArtifactComponentRepository::COMPONENT_DATA_PATH;
    }


    public function build(): Setup\Artifact
    {
        $processor = new \ilComponentInfoDefinitionProcessor();
        $reader = new \ilComponentDefinitionReader(
            $processor
        );

        $reader->purge();
        $reader->readComponentDefinitions();

        return new Setup\Artifact\ArrayArtifact($processor->getData());
    }
}
