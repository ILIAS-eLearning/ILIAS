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
 
use ILIAS\Filesystem\Filesystems;
use ILIAS\FileUpload\FileUpload;

class ilObjectService implements ilObjectServiceInterface
{
    protected ilLanguage $lng;
    protected ilSetting $settings;
    protected Filesystems $filesystem;
    protected FileUpload $upload;

    public function __construct(
        ilLanguage $lng,
        ilSetting $settings,
        Filesystems $filesystem,
        FileUpload $upload
    ) {
        $this->lng = $lng;
        $this->settings = $settings;
        $this->filesystem = $filesystem;
        $this->upload = $upload;
    }

    public function language() : ilLanguage
    {
        return $this->lng;
    }

    public function settings() : ilSetting
    {
        return $this->settings;
    }

    public function filesystem() : Filesystems
    {
        return $this->filesystem;
    }

    public function upload() : FileUpload
    {
        return $this->upload;
    }

    public function commonSettings() : ilObjectCommonSettings
    {
        return new ilObjectCommonSettings($this);
    }
}
