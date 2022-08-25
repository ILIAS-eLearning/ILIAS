<?php

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
