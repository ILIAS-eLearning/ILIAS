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
use ILIAS\Object\Properties\CoreProperties\TileImage\ilObjectTileImageFlavourDefinition;
use ILIAS\UI\Component\Symbol\Icon\Icon;
use ILIAS\UI\Component\Symbol\Icon\Factory as IconFactory;
use ILIAS\UI\Component\Image\Image;
use ILIAS\UI\Component\Image\Factory as ImageFactory;
use ILIAS\ResourceStorage\Services as StorageService;
use ILIAS\ResourceStorage\Identification\ResourceIdentification;
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
        $this->crop_definition = new ilObjectTileImageFlavourDefinition();
        $this->extract_definition = new PagesToExtract($this->persist, $this->max_size, 1, true);
    }

    public function getObjectTypeSpecificTileImage(
        int $obj_id,
        ImageFactory $factory,
        StorageService $irss
    ): ?Image {
        $rid = $irss->manage()->find(ilObjFileAccess::getListGUIData($obj_id)['rid'] ?? '');
        if ($rid === null) {
            return null;
        }
        if ($irss->flavours()->possible($rid, $this->crop_definition)) {
            return $this->getImageFromIRSS($rid, $irss, $factory);
        }
        if ($irss->flavours()->possible($rid, $this->extract_definition)) {
            $url = $irss->consume()->flavourUrls(
                $irss->flavours()->get(
                    $rid,
                    $this->extract_definition
                )
            )->getURLs(false)->current();
            if ($url !== null) {
                return $factory->responsive($url, '');
            }
        }

        return null;
    }

    private function getImageFromIRSS(
        ResourceIdentification $resource,
        StorageService $irss,
        ImageFactory $factory
    ): Image {
        $flavour = $irss->flavours()->get($resource, $this->crop_definition);
        $urls = $irss->consume()->flavourUrls($flavour)->getURLsAsArray();

        $available_widths = $this->crop_definition->getWidths();
        array_pop($available_widths);

        $image = $factory->responsive($urls[count($available_widths)], '');
        return array_reduce(
            $available_widths,
            function ($carry, $size) use ($urls) {
                $image = $carry['image']->withAdditionalHighResSource($urls[$carry['counter']], $size / 2);
                $counter = ++$carry['counter'];
                return [
                    'image' => $image,
                    'counter' => $counter
                ];
            },
            ['image' => $image, 'counter' => 0]
        )['image'];
    }

    public function getObjectTypeSpecificCustomIcon(
        int $obj_id,
        IconFactory $icon_factory,
        StorageService $irss
    ): ?Icon {
        return null;
    }
}
