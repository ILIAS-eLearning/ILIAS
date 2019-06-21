<?php namespace ILIAS\ArtifactBuilder;

use ILIAS\ArtifactBuilder\Artifact\Artifact;

/**
 * Interface ArtifactBuilder
 *
 * @package ILIAS\ArtifactBuilder
 */
interface ArtifactBuilder
{

    public function run() : void;


    /**
     * @return Artifact
     */
    public function getArtifact() : Artifact;
}
