<?php

/* Copyright (c) 1998-2018 ILIAS open source, Extended GPL, see docs/LICENSE */

/**
 * Object service
 *
 * @author killing@leifos.de
 * @ingroup ServiceObject
 */
class ilObjectService implements ilObjectServiceInterface
{
    /**

    /**
     * @var ilLanguage
     */
    protected $lng;

    /**
     * @var ilSetting
     */
    protected $settings;

    /**
     * @var \ILIAS\Filesystem\Filesystems
     */
    protected $filesystem;

    /**
     * Constructor
     * @param ilLanguage $lng
     */
    public function __construct(ilLanguage $lng, ilSetting $settings, \ILIAS\Filesystem\Filesystems $filesystem, \ILIAS\FileUpload\FileUpload $upload)
    {
        $this->lng = $lng;
        $this->settings = $settings;
        $this->filesystem = $filesystem;
        $this->upload = $upload;
    }

    /**
     * Get language object
     *
     * @return ilLanguage
     */
    public function language()
    {
        return $this->lng;
    }

    /**
     * Get settings object
     *
     * @return ilSetting
     */
    public function settings()
    {
        return $this->settings;
    }

    /**
     * Get filesystems
     * @return \ILIAS\Filesystem\Filesystems
     */
    public function filesystem()
    {
        return $this->filesystem;
    }

    /**
     * Get filesystems
     * @return \ILIAS\FileUpload\FileUpload
     */
    public function upload()
    {
        return $this->upload;
    }

    /**
     * @inheritdoc
     */
    public function commonSettings() : ilObjectCommonSettings
    {
        return new ilObjectCommonSettings($this);
    }
}
