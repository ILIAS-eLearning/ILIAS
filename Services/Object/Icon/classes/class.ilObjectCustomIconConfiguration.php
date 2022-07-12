<?php declare(strict_types=1);

/**
 * This file is part of ILIAS, a powerful learning management system
 * published by ILIAS open source e-Learning e.V.
 *
 * ILIAS is licensed with the GPL-3.0,
 * see https://www.gnu.org/licenses/gpl-3.0.en.html
 * You should have received a copy of said license along with the
 * source code, too.
 *
 * If this is not the case or you just want to try ILIAS, you'll find
 * us at:
 * https://www.ilias.de
 * https://github.com/ILIAS-eLearning
 *
 *********************************************************************/
 
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
