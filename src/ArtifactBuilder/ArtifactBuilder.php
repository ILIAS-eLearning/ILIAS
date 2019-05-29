<?php namespace ILIAS\ArtifactBuilder;

use ILIAS\ArtifactBuilder\Artifacts\Artifact;
use ILIAS\ArtifactBuilder\IO\IO;

/**
 * Interface ArtifactBuilder
 *
 * @package ILIAS\ArtifactBuilder
 */
interface ArtifactBuilder
{

    /**
     * @return ArtifactBuilder
     */
    public static function getInstance() : ArtifactBuilder;


    public function run() : void;


    /**
     * @param IO $IO
     */
    public function injectIO(IO $IO) : void;


    /**
     * @return IO
     */
    public function io() : IO;


    /**
     * @return Artifact
     */
    public function getArtifact() : Artifact;
}
