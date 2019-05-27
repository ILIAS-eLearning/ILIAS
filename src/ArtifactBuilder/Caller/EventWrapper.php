<?php namespace ILIAS\ArtifactBuilder\Caller;

/**
 * Interface EventWrapper
 *
 * @author Fabian Schmid <fs@studer-raimann.ch>
 */
interface EventWrapper
{

    /**
     * @return string
     */
    public function getName() : string;
}
