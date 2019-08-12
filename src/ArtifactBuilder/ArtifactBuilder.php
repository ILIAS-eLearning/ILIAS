<?php namespace ILIAS\ArtifactBuilder;

use ILIAS\ArtifactBuilder\Artifact\Artifact;

/**
 * Interface ArtifactBuilder
 *
 * ArtifactBuilder are being called to do their job (@see run) and then their
 *
 * @see     Artifact is requested (@see getArtifact). Requested @see Artifact will
 * then be called to save (@see Artifact::save()).
 *
 * @package ILIAS\ArtifactBuilder
 */
interface ArtifactBuilder
{

    /**
     * Implement everything you need to collect or compose your data you want to
     * have as an Artifact. Please note that there are NONE of the ILIAS
     * dependencies available at the moment. Create light-weight run methods.
     */
    public function run() : void;


    /**
     * @return Artifact
     * @see Artifact
     */
    public function getArtifact() : Artifact;
}
