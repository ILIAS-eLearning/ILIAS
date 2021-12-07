<?php declare(strict_types=1);

/* Copyright (c) 1998-2018 ILIAS open source, Extended GPL, see docs/LICENSE */

class ilObjectService implements ilObjectServiceInterface
{
    protected ilLanguage $lng;
    protected ilSetting $settings;
    protected \ILIAS\Filesystem\Filesystems $filesystem;

    public function __construct(
        ilLanguage $lng,
        ilSetting $settings,
        \ILIAS\Filesystem\Filesystems $filesystem,
        \ILIAS\FileUpload\FileUpload $upload
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

    public function filesystem() : \ILIAS\Filesystem\Filesystems
    {
        return $this->filesystem;
    }

    public function upload() : \ILIAS\FileUpload\FileUpload
    {
        return $this->upload;
    }

    public function commonSettings() : ilObjectCommonSettings
    {
        return new ilObjectCommonSettings($this);
    }
}
