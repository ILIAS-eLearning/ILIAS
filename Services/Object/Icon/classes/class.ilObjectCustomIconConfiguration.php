<?php declare(strict_types=1);

/* Copyright (c) 1998-2021 ILIAS open source, GPLv3, see LICENSE */

class ilObjectCustomIconConfiguration implements ilCustomIconObjectConfiguration
{
    /**
     * @return string[]
     */
    public function getSupportedFileExtensions() : array
    {
        return ['svg'];
    }

    public function getTargetFileExtension() : string
    {
        return 'svg';
    }

    public function getBaseDirectory() : string
    {
        return 'custom_icons';
    }

    public function getSubDirectoryPrefix() : string
    {
        return 'obj_';
    }

    public function getUploadPostProcessors() : array
    {
        return [];
    }
}
