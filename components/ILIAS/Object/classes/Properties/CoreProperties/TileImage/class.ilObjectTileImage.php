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

namespace ILIAS\Object\Properties\CoreProperties\TileImage;

use ILIAS\Object\Properties\ObjectTypeSpecificProperties\ilObjectTypeSpecificPropertyProviders;
use ILIAS\ResourceStorage\Services as ResourceStorageServices;
use ILIAS\ResourceStorage\Identification\ResourceIdentification;
use ILIAS\Filesystem\Stream\Stream;
use ILIAS\Filesystem\Util\LegacyPathHelper;
use ILIAS\UI\Component\Image\Factory as ImageFactory;
use ILIAS\UI\Component\Image\Image;

class ilObjectTileImage
{
    protected string $ext = '';

    public function __construct(
        private int $object_id,
        private ?string $obj_type,
        private ?string $rid,
        private ImageFactory $image_factory,
        private ResourceStorageServices $storage_services,
        private ilObjectTileImageStakeholder $storage_stakeholder,
        private ilObjectTileImageFlavourDefinition $flavour_definition,
        private ?ilObjectTypeSpecificPropertyProviders $object_type_specific_property_providers
    ) {
    }

    public function getRid(): ?string
    {
        return $this->rid;
    }

    public function withRid(?string $rid): self
    {
        $clone = clone $this;
        $clone->rid = $rid;
        return $clone;
    }

    public function getImage(): Image
    {
        if ($this->object_type_specific_property_providers !== null &&
            (
                $specific_tile_image = $this->object_type_specific_property_providers->getObjectTypeSpecificTileImage(
                    $this->object_id,
                    $this->image_factory,
                    $this->storage_services
                )
            ) !== null) {
            return $specific_tile_image;
        }

        if ($this->rid !== null
            && $this->rid !== ''
            && ($resource = $this->storage_services->manage()->find($this->rid)) !== null
            && ($from_IRSS = $this->getImageFromIRSS($resource)) !== null
        ) {
            return $from_IRSS;
        }

        if ($this->exists()) {
            return $this->image_factory->responsive($this->getFullPath(), '');
        }

        $path = \ilUtil::getImagePath('cont_tile/cont_tile_default_' . $this->obj_type . '.svg');
        if (is_file($path)) {
            return $this->image_factory->responsive($path, '');
        }
        return $this->image_factory->responsive(\ilUtil::getImagePath('cont_tile/cont_tile_default.svg'), '');
    }

    private function getImageFromIRSS(ResourceIdentification $resource): ?Image
    {
        $flavour = $this->storage_services->flavours()->get($resource, $this->flavour_definition);
        $urls = $this->storage_services->consume()->flavourUrls($flavour)->getURLsAsArray();
        if($urls === []) {
            return null;
        }

        $available_widths = $this->flavour_definition->getWidths();
        array_pop($available_widths);

        if(!isset($urls[count($available_widths)])) {
            return null;
        }

        $image = $this->image_factory->responsive($urls[count($available_widths)], '');
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

    /**
     *
     * @deprecated 11: This is only here for the Legacy Input and will be removed
     * with ILIAS 11.
     */
    public function getSrcUrlForLegacyForm(): string
    {
        if ($this->rid !== null && $this->rid !== '') {
            $resource = $this->storage_services->manage()->find($this->rid);
            if ($resource === null) {
                return '';
            }

            $flavour = $this->storage_services->flavours()->get($resource, $this->flavour_definition);
            $urls = $this->storage_services->consume()->flavourUrls($flavour)->getURLsAsArray(false);

            return array_pop($urls) ?? '';
        }

        if (!$this->exists()) {
            return '';
        }
        return $this->getFullPath();
    }

    public function deleteLegacyTileImage(): void
    {
        if ($this->exists()) {
            unlink(ILIAS_ABSOLUTE_PATH . DIRECTORY_SEPARATOR . $this->getFullPath());
            rmdir(dirname(ILIAS_ABSOLUTE_PATH . DIRECTORY_SEPARATOR . $this->getFullPath()));
            \ilContainer::_deleteContainerSettings($this->object_id, 'tile_image');
        }
    }

    public function cloneFor(int $new_object_id): self
    {
        $clone = clone $this;
        $clone->object_id = $new_object_id;

        if ($this->rid !== null) {
            $i = $this->storage_services->manage()->clone($this->rid);
            $clone->rid = $i->serialize();
        }

        return $clone;
    }

    private function exists(): bool
    {
        if (!\ilContainer::_lookupContainerSetting($this->object_id, 'tile_image', '0')) {
            return false;
        }

        return is_file($this->getFullPath());
    }

    private function getFullPath(): string
    {
        if ($this->ext === '') {
            $this->ext = \ilContainer::_lookupContainerSetting($this->object_id, 'tile_image');
        }

        return implode(
            DIRECTORY_SEPARATOR,
            [
                \ilFileUtils::getWebspaceDir(),
                'obj_data',
                'tile_image',
                'tile_image_' . $this->object_id,
                'tile_image.' . $this->ext
            ]
        );
    }

    public function createFromImportDir(string $source_dir): void
    {
        $sourceFS = LegacyPathHelper::deriveFilesystemFrom($source_dir);
        $sourceDir = LegacyPathHelper::createRelativePath($source_dir);
        $sourceList = $sourceFS->listContents($sourceDir, true);

        foreach ($sourceList as $item) {
            if ($item->isDir()) {
                continue;
            }

            $path = $source_dir . DIRECTORY_SEPARATOR . basename($item->getPath());
            $stream = new Stream(fopen($path, 'r'));
            $i = $this->storage_services->manage()->stream($stream, $this->storage_stakeholder, 'Tile Image');
            $this->rid = $i->serialize();
        }
    }
}
