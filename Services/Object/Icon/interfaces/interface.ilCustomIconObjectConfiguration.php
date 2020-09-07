<?php
/* Copyright (c) 1998-2018 ILIAS open source, Extended GPL, see docs/LICENSE */

interface ilCustomIconObjectConfiguration
{
    /**
     * @return string[]
     */
    public function getSupportedFileExtensions() : array;

    /**
     * @return string
     */
    public function getTargetFileExtension() : string;

    /**
     * @return string
     */
    public function getBaseDirectory() : string;

    /**
     * @return string
     */
    public function getSubDirectoryPrefix() : string;

    /**
     * A collection of post processors which are invoked if a new icon has been uploaded
     * @return ilObjectCustomIconUploadPostProcessor[]
     */
    public function getUploadPostProcessors() : array;
}
