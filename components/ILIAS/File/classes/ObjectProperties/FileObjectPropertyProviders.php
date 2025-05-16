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

use ILIAS\ILIASObject\Properties\ObjectTypeSpecificProperties\ObjectTypeSpecificPropertyProviders;
use ILIAS\ILIASObject\Properties\CoreProperties\TileImage\FlavourDefinition as TileImageFlavourDefinition;
use ILIAS\UI\Component\Symbol\Icon\Custom as CustomIcon;
use ILIAS\UI\Component\Symbol\Icon\Factory as IconFactory;
use ILIAS\UI\Component\Image\Image;
use ILIAS\UI\Component\Image\Factory as ImageFactory;
use ILIAS\ResourceStorage\Services as StorageService;
use ILIAS\ResourceStorage\Flavour\Flavour;
use ILIAS\ResourceStorage\Flavour\Definition\FlavourDefinition;
use ILIAS\components\File\Preview\Settings;
use ILIAS\Modules\File\Preview\SettingsFactory;
use ILIAS\File\Icon\IconDatabaseRepository;

class FileObjectPropertyProviders implements ObjectTypeSpecificPropertyProviders
{
    private FlavourDefinition $crop_definition;
    private FlavourDefinition $extract_definition;
    private Settings $settings;
    private IconDatabaseRepository $icons;
    private ilObjFileInfoRepository $info;

    public function __construct()
    {
        $this->crop_definition = new TileImageFlavourDefinition();
        $this->extract_definition = new FirstPageToTileImageFlavourDefinition();
        $this->settings = (new SettingsFactory())->getSettings();
        $this->info = new ilObjFileInfoRepository();
        $this->icons = new IconDatabaseRepository();
    }

    public function getObjectTypeSpecificTileImage(
        int $obj_id,
        ImageFactory $factory,
        StorageService $irss
    ): ?Image {
        if (!$this->settings->hasTilePreviews()) {
            return null;
        }

        $rid = (new ilObjFileInfoRepository())->getByObjectId($obj_id)->getRID();

        if ($rid === null) {
            return null;
        }
        if ($irss->flavours()->possible($rid, $this->crop_definition)) {
            $flavour = $irss->flavours()->get($rid, $this->crop_definition);
            return $this->getImageFromIRSS($irss, $factory, $flavour);
        }
        if ($irss->flavours()->possible($rid, $this->extract_definition)) {
            $flavour = $irss->flavours()->get($rid, $this->extract_definition);
            return $this->getImageFromIRSS($irss, $factory, $flavour);
        }

        return null;
    }

    private function getImageFromIRSS(
        StorageService $irss,
        ImageFactory $factory,
        Flavour $flavour
    ): ?Image {
        $urls = $irss->consume()->flavourUrls($flavour)->getURLsAsArray();

        if ($urls === []) {
            return null;
        }

        $available_widths = $this->crop_definition->getWidths();
        array_pop($available_widths);

        if (!isset($urls[count($available_widths)])) {
            return null;
        }

        $image = $factory->responsive($urls[count($available_widths)], '');
        return array_reduce(
            $available_widths,
            function (array $carry, $size) use ($urls): array {
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

    public function getObjectTypeSpecificIcon(
        int $obj_id,
        IconFactory $icon_factory,
        StorageService $irss
    ): ?CustomIcon {
        $info = $this->info->getByObjectId($obj_id);
        $path = $this->icons->getIconFilePathBySuffix($info->getSuffix());
        if (($path !== '' && $path !== '0')) {
            return $icon_factory->custom($path, $info->getSuffix());
        }

        return null;
    }
}
