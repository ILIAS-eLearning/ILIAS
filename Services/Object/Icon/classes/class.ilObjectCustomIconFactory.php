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
