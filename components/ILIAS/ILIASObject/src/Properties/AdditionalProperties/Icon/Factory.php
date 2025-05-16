<?php

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

declare(strict_types=1);

namespace ILIAS\ILIASObject\Properties\AdditionalProperties\Icon;

use ILIAS\Filesystem\Filesystem;
use ILIAS\FileUpload\FileUpload;

class Factory
{
    public function __construct(
        private readonly Filesystem $webDirectory,
        private readonly FileUpload $uploadService,
        private readonly \ilObjectDataCache $objectCache
    ) {
    }

    public function getConfigurationByType(string $type): Configuration
    {
        switch ($type) {
            case 'grp':
            case 'root':
            case 'cat':
            case 'fold':
            case 'crs':
            case 'prg':
                $configuration = new ContainerConfiguration();
                break;

            default:
                $configuration = new Configuration();
                break;
        }

        return $configuration;
    }

    public function getByObjId(int $objId, string $objType = ''): Custom
    {
        if ($objType === '') {
            $objType = $this->objectCache->lookupType($objId);
        }

        return new Custom(
            $this->webDirectory,
            $this->uploadService,
            $this->getConfigurationByType($objType),
            $objId
        );
    }

    public function getPresenterByObjId(int $objId, string $objType): Presenter
    {
        if ($objType === '') {
            $objType = $this->objectCache->lookupType($objId);
        }

        switch ($objType) {
            case 'catr':
            case 'grpr':
            case 'crsr':
                $presenter = new ObjectReferenceCustomIconPresenter($objId, $this);
                $presenter->init();
                break;

            default:
                $presenter = new ObjectCustomIconPresenter($this->getByObjId($objId, $objType));
                break;
        }

        return $presenter;
    }
}
