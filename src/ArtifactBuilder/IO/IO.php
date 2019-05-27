<?php namespace ILIAS\ArtifactBuilder\IO;

/**
 * Interface IO
 *
 * @author Fabian Schmid <fs@studer-raimann.ch>
 */
interface IO
{

    /**
     * @param string $output
     */
    public function write(string $output) : void;
}
