<?php

use ILIAS\Setup;

class ilEventHandlingBuildEventInfoObjective extends Setup\Artifact\BuildArtifactObjective
{
    public function getArtifactPath(): string
    {
        return \ilArtifactEventHandlingData::EVENT_HANDLING_DATA_PATH;
    }


    public function build(): Setup\Artifact
    {
        $processor = new \ilEventHandlingDefinitionProcessor();
        $reader = new \ilComponentDefinitionReader(
            $processor
        );

        $reader->purge();
        $reader->readComponentDefinitions();

        return new Setup\Artifact\ArrayArtifact($processor->getData());
    }
}