<?php

namespace ILIAS\UI\Component\Input\Field;

/**
 * This describes select field.
 */
interface File extends Input
{

    /**
     * @param int $size_in_bytes
     *
     * @return File
     */
    public function withMaxFileSize(int $size_in_bytes) : File;


    /**
     * @return int
     */
    public function getMaxFileFize() : int;
}
