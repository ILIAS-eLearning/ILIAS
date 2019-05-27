<?php namespace ILIAS\ArtifactBuilder\Event;

use ILIAS\ArtifactBuilder\Artifacts\Artifact;
use ILIAS\ArtifactBuilder\Caller\EventWrapper;
use ILIAS\ArtifactBuilder\IO\IO;

/**
 * Interface EventHandler
 *
 * @author Fabian Schmid <fs@studer-raimann.ch>
 */
interface EventHandler
{

    public function run() : void;


    /**
     * @return EventWrapper
     */
    public function getEvent() : EventWrapper;


    /**
     * @return IO
     */
    public function io() : IO;


    /**
     * @return Artifact
     */
    public function getArtifact() : Artifact;
}
