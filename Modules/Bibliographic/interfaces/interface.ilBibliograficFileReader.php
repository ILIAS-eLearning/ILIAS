<?php

/**
 * Interface ilBibliograficFileReader
 *
 * @author Fabian Schmid <fs@studer-raimann.ch>
 */
interface ilBibliograficFileReader
{

    /**
     * @param $path_to_file
     * @return bool
     */
    public function readContent($path_to_file);


    /**
     * @return array
     */
    public function parseContent();
}
