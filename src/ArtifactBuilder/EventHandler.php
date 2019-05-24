<?php namespace ILIAS\ArtifactBuilder;

use ILIAS\ArtifactBuilder\Artifacts\Artifact;
use ILIAS\ArtifactBuilder\IO\IOInterface;

/**
 * Interface EventHandler
 *
 * @author Fabian Schmid <fs@studer-raimann.ch>
 */
interface EventHandler
{

    public function run() : void;


    /**
     * @return IOInterface
     */
    public function io() : IOInterface;


    /**
     * @return Artifact
     */
    public function getArtifact() : Artifact;
}
