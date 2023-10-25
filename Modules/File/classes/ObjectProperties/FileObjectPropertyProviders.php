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

use ILIAS\Object\Properties\ObjectTypeSpecificProperties\ilObjectTypeSpecificPropertyProviders;
use ILIAS\UI\Component\Symbol\Icon\Icon;
use ILIAS\UI\Component\Symbol\Icon\Factory as IconFactory;
use ILIAS\UI\Component\Image\Image;
use ILIAS\UI\Component\Image\Factory as ImageFactory;
use ILIAS\ResourceStorage\Services as StorageService;
use ILIAS\ResourceStorage\Flavour\Definition\CropToSquare;
use ILIAS\ResourceStorage\Flavour\Definition\FlavourDefinition;
use ILIAS\ResourceStorage\Flavour\Definition\PagesToExtract;

class FileObjectPropertyProviders implements ilObjectTypeSpecificPropertyProviders
{
    private bool $persist = true;
    private int $max_size = 512;
    private FlavourDefinition $crop_definition;
    private FlavourDefinition $extract_definition;

    public function __construct()
    {
        $this->crop_definition = new CropToSquare($this->persist, $this->max_size);
        $this->extract_definition = new PagesToExtract($this->persist, $this->max_size, 1, true);
    }

    public function getObjectTypeSpecificTileImage(
        int $obj_id,
        ImageFactory $factory,
        StorageService $irss
    ): ?Image {
        if (($flavour_path = $this->getCardImageFallbackPath(
            $obj_id,
            $irss
        )) !== '') {
            return $factory->responsive($flavour_path, '');
        }

        return null;
    }

    /**
     * @description Can be used to take preview flavours as card images
     */
    protected function getCardImageFallbackPath(
        int $obj_id,
        StorageService $irss
    ): string {
        $rid = $irss->manage()->find(ilObjFileAccess::getListGUIData($obj_id)['rid'] ?? '');
        if ($rid === null) {
            return '';
        }
        if ($irss->flavours()->possible($rid, $this->crop_definition)) {
            $url = $irss->consume()->flavourUrls(
                $irss->flavours()->get(
                    $rid,
                    $this->crop_definition
                )
            )->getURLs(false)->current();
            if ($url !== null) {
                return $url;
            }
        }
        if ($irss->flavours()->possible($rid, $this->extract_definition)) {
            $url = $irss->consume()->flavourUrls(
                $irss->flavours()->get(
                    $rid,
                    $this->extract_definition
                )
            )->getURLs(false)->current();
            if ($url !== null) {
                return $url;
            }
        }
        return '';
    }

    public function getObjectTypeSpecificCustomIcon(
        int $obj_id,
        IconFactory $icon_factory,
        StorageService $irss
    ): ?Icon {
        return null;
    }
}
