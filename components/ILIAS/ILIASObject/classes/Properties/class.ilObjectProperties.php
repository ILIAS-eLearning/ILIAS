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

use ILIAS\Object\Properties\CoreProperties\TileImage\ilObjectPropertyTileImage;
use ILIAS\MetaData\Services\ServicesInterface as LOMServices;
use ILIAS\MetaData\Elements\Data\Type as LOMType;

class ilObjectProperties
{
    public function __construct(
        private ilObjectCoreProperties $core_properties,
        private ilObjectCorePropertiesRepository $core_properties_repository,
        private ilObjectAdditionalProperties $additional_properties,
        private ilObjectAdditionalPropertiesRepository $additional_properties_repository,
        private LOMServices $lom_services
    ) {
    }

    public function storeCoreProperties(): void
    {
        $this->core_properties_repository->store($this->core_properties);
        $this->updateMetadataForTitleAndDescription(
            $this->core_properties->getPropertyTitleAndDescription()->getTitle(),
            $this->core_properties->getPropertyTitleAndDescription()->getDescription()
        );
    }

    public function getOwner(): int
    {
        return $this->core_properties->getOwner();
    }

    public function withOwner(int $owner): self
    {
        $clone = clone $this;
        $clone->core_properties = $this->core_properties->withOwner($owner);
        return $clone;
    }

    public function getImportId(): string
    {
        return $this->core_properties->getImportId();
    }

    public function withImportId(string $import_id): self
    {
        $clone = clone $this;
        $clone->core_properties = $this->core_properties->withImportId($import_id);
        return $clone;
    }

    public function getPropertyTitleAndDescription(): ilObjectPropertyTitleAndDescription
    {
        return $this->core_properties->getPropertyTitleAndDescription();
    }

    public function withPropertyTitleAndDescription(
        ilObjectPropertyTitleAndDescription $property_title_and_description
    ): self {
        $clone = clone $this;
        $clone->core_properties = $this->core_properties
            ->withPropertyTitleAndDescription($property_title_and_description);
        return $clone;
    }

    public function storePropertyTitleAndDescription(
        ilObjectPropertyTitleAndDescription $property_title_and_description
    ): void {
        $this->core_properties = $this->core_properties_repository->store(
            $this->core_properties
            ->withPropertyTitleAndDescription($property_title_and_description)
        );
        $this->updateMetadataForTitleAndDescription(
            $property_title_and_description->getTitle(),
            $property_title_and_description->getLongDescription()
        );
    }

    public function getPropertyIsOnline(): ilObjectPropertyIsOnline
    {
        return $this->core_properties->getPropertyIsOnline();
    }

    public function storePropertyIsOnline(ilObjectPropertyIsOnline $property_is_online): void
    {
        $this->core_properties = $this->core_properties_repository->store(
            $this->core_properties->withPropertyIsOnline($property_is_online)
        );
    }

    public function withPropertyIsOnline(
        ilObjectPropertyIsOnline $property_is_online
    ): self {
        $clone = clone $this;
        $clone->core_properties = $this->core_properties
            ->withPropertyIsOnline($property_is_online);
        return $clone;
    }

    public function getPropertyTitleAndIconVisibility(): ilObjectProperty
    {
        return $this->additional_properties->getPropertyTitleAndIconVisibility();
    }

    public function storePropertyTitleAndIconVisibility(
        ilObjectPropertyTitleAndIconVisibility $property_title_and_icon_visibility
    ): void {
        $this->additional_properties = $this->additional_properties_repository->store(
            $this->additional_properties
            ->withPropertyTitleAndIconVisibility($property_title_and_icon_visibility)
        );
    }

    public function getPropertyHeaderActionVisibility(): ilObjectProperty
    {
        return $this->additional_properties->getPropertyHeaderActionVisibility();
    }

    public function storePropertyHeaderActionVisibility(
        ilObjectPropertyHeaderActionVisibility $property_header_action_visibility
    ): void {
        $this->additional_properties = $this->additional_properties_repository->store(
            $this->additional_properties
            ->withPropertyHeaderActionVisibility($property_header_action_visibility)
        );
    }

    public function getPropertyInfoTabVisibility(): ilObjectProperty
    {
        return $this->additional_properties->getPropertyInfoTabVisibility();
    }

    public function storePropertyInfoTabVisibility(
        ilObjectPropertyInfoTabVisibility $property_info_tab_visibility
    ): void {
        $this->additional_properties = $this->additional_properties_repository->store(
            $this->additional_properties
            ->withPropertyInfoTabVisibility($property_info_tab_visibility)
        );
    }

    public function getPropertyTileImage(): ilObjectPropertyTileImage
    {
        return $this->core_properties->getPropertyTileImage();
    }

    public function storePropertyTileImage(
        ilObjectPropertyTileImage $property_tile_image
    ): void {
        $this->core_properties = $this->core_properties_repository->store(
            $this->core_properties
            ->withPropertyTileImage($property_tile_image)
        );
    }

    public function getPropertyIcon(): ilObjectProperty
    {
        return $this->additional_properties->getPropertyIcon();
    }

    public function storePropertyIcon(
        ilObjectPropertyIcon $property_icon
    ): void {
        $this->additional_properties = $this->additional_properties_repository->store(
            $this->additional_properties
            ->withPropertyIcon($property_icon)
        );
    }

    private function updateMetadataForTitleAndDescription(
        string $title,
        string $description
    ): void {
        $paths = $this->lom_services->paths();
        $obj_id = $this->core_properties->getObjectId();
        $type = $this->core_properties->getType();

        /*
         * This is a hacky solution to distinguish between
         * objects with LOM support and without. In the future, proper
         * infrastructure to make that distinction should be added.
         */
        $title_data = $this->lom_services->read($obj_id, 0, $type, $paths->title())
            ->firstData($paths->title());
        if ($title_data->type() === LOMType::NULL) {
            return;
        }

        $manipulator = $this->lom_services->manipulate($obj_id, 0, $type)
            ->prepareCreateOrUpdate($paths->title(), $title);
        if ($description !== '') {
            $manipulator = $manipulator->prepareCreateOrUpdate($paths->firstDescription(), $description);
        } else {
            $manipulator = $manipulator->prepareDelete($paths->firstDescription());
        }
        $manipulator->execute();
    }
}
