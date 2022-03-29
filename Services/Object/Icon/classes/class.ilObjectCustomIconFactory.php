<?php declare(strict_types=1);

/* Copyright (c) 1998-2021 ILIAS open source, GPLv3, see LICENSE */

use ILIAS\Filesystem\Filesystem;
use ILIAS\FileUpload\FileUpload;

class ilObjectCustomIconFactory
{
    protected Filesystem $webDirectory;
    protected FileUpload $uploadService;
    protected ilObjectDataCache $objectCache;

    public function __construct(
        Filesystem $webDirectory,
        FileUpload $uploadService,
        ilObjectDataCache $objectCache
    ) {
        $this->webDirectory = $webDirectory;
        $this->uploadService = $uploadService;
        $this->objectCache = $objectCache;
    }

    public function getConfigurationByType(string $type) : ilCustomIconObjectConfiguration
    {
        switch ($type) {
            case 'grp':
            case 'root':
            case 'cat':
            case 'fold':
            case 'crs':
            case 'prg':
                $configuration = new ilContainerCustomIconConfiguration();
                break;

            default:
                $configuration = new ilObjectCustomIconConfiguration();
                break;
        }

        return $configuration;
    }

    public function getByObjId(int $objId, string $objType = '') : ilObjectCustomIcon
    {
        if ($objType === '') {
            $objType = $this->objectCache->lookupType($objId);
        }

        return new ilObjectCustomIconImpl(
            $this->webDirectory,
            $this->uploadService,
            $this->getConfigurationByType($objType),
            $objId
        );
    }

    public function getPresenterByObjId(int $objId, string $objType) : ilObjectCustomIconPresenter
    {
        if ($objType === '') {
            $objType = $this->objectCache->lookupType($objId);
        }

        switch ($objType) {
            case 'catr':
            case 'grpr':
            case 'crsr':
                $presenter = new ilObjectReferenceCustomIconPresenter($objId, $this);
                $presenter->init();
                break;

            default:
                $presenter = new ilObjectCustomIconPresenterImpl($this->getByObjId($objId, $objType));
                break;

        }

        return $presenter;
    }
}
