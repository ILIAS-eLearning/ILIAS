<?php namespace ILIAS\ArtifactBuilder\Artifact;

/**
 * Class Artifact
 *
 * An Artifact is a very small piece of data storage, from one place is filled
 * with data and wants to store this data.
 *
 * @author Fabian Schmid <fs@studer-raimann.ch>
 */
interface Artifact
{

    /**
     * This method will be called from the source, which wants to save the artifact.
     * The artifact itself is responsible to save its data.
     */
    public function save() : void;
}
