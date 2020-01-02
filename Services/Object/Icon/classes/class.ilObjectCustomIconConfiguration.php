<?php
/* Copyright (c) 1998-2018 ILIAS open source, Extended GPL, see docs/LICENSE */

require_once 'Services/Object/Icon/interfaces/interface.ilCustomIconObjectConfiguration.php';

/**
 * Class ilObjectIconConfiguration
 */
class ilObjectCustomIconConfiguration implements \ilCustomIconObjectConfiguration
{
    /**
     * @return string[]
     */
    public function getSupportedFileExtensions() : array
    {
        return ['svg'];
    }

    /**
     * @return string
     */
    public function getTargetFileExtension() : string
    {
        return 'svg';
    }

    /**
     * @return string
     */
    public function getBaseDirectory() : string
    {
        return 'custom_icons';
    }

    /**
     * @return string
     */
    public function getSubDirectoryPrefix() : string
    {
        return 'obj_';
    }

    /**
     * @inheritdoc
     */
    public function getUploadPostProcessors() : array
    {
        return [];
    }
}
