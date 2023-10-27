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

class ilObjectProperties
{
    public function __construct(
        private ilObjectCoreProperties $core_properties,
        private ilObjectCorePropertiesRepository $core_properties_repository,
        private ilObjectAdditionalProperties $additional_properties,
        private ilObjectAdditionalPropertiesRepository $additional_properties_repository,
        private ilMD $meta_data
    ) {
    }

    public function getPropertyTitleAndDescription(): ilObjectProperty
    {
        return $this->core_properties->getPropertyTitleAndDescription();
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
    ) {
        $general_metadata = $this->meta_data->getGeneral();
        $general_metadata->setTitle($title);

        // sets first description (maybe not appropriate)
        $general_metadata_ids = $general_metadata->getDescriptionIds();
        if ($general_metadata_ids !== []) {
            $general_metadata_description = $general_metadata_ids->getDescription($general_metadata_ids[0]);
            $general_metadata_description->setDescription($description);
            $general_metadata_description->update();
        }
        $general_metadata->update();
    }
}
