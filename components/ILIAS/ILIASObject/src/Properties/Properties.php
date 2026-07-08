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

namespace ILIAS\ILIASObject\Properties;

use ILIAS\ILIASObject\Properties\AdditionalProperties\Simple\HeaderActionVisibility;
use ILIAS\ILIASObject\Properties\AdditionalProperties\Simple\InfoTabVisibility;
use ILIAS\ILIASObject\Properties\AdditionalProperties\Simple\TitleAndIconVisibility;
use ILIAS\ILIASObject\Properties\AdditionalProperties\AdditionalProperties;
use ILIAS\ILIASObject\Properties\AdditionalProperties\Repository as AdditionalPropertiesRepository;
use ILIAS\ILIASObject\Properties\AdditionalProperties\Icon\Icon;
use ILIAS\ILIASObject\Properties\CoreProperties\CoreProperties;
use ILIAS\ILIASObject\Properties\CoreProperties\Online;
use ILIAS\ILIASObject\Properties\CoreProperties\TitleAndDescription;
use ILIAS\ILIASObject\Properties\CoreProperties\Repository as CorePropertiesRepository;
use ILIAS\ILIASObject\Properties\CoreProperties\TileImage\Property as PropertyTileImage;
use ILIAS\ILIASObject\Properties\Translations\CachedRepository as TranslationsRepository;
use ILIAS\ILIASObject\Properties\Translations\Translations;
use ILIAS\MetaData\Services\ServicesInterface as LOMServices;
use ILIAS\MetaData\Elements\Data\Type as LOMType;

class Properties
{
    public function __construct(
        private CoreProperties $core_properties,
        private CorePropertiesRepository $core_properties_repository,
        private AdditionalProperties $additional_properties,
        private AdditionalPropertiesRepository $additional_properties_repository,
        private ?Translations $translations,
        private TranslationsRepository $translations_repository,
        private LOMServices $lom_services
    ) {
    }

    public function storeCoreProperties(): void
    {
        $this->core_properties_repository->store($this->core_properties);
        $this->updateMetadataForTitleAndDescription(
            $this->core_properties->getPropertyTitleAndDescription()->getTitle(),
            $this->core_properties->getPropertyTitleAndDescription()->getLongDescription()
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

    public function getPropertyTitleAndDescription(): TitleAndDescription
    {
        return $this->core_properties->getPropertyTitleAndDescription();
    }

    public function withPropertyTitleAndDescription(
        TitleAndDescription $property_title_and_description
    ): self {
        $clone = clone $this;
        $clone->core_properties = $this->core_properties
            ->withPropertyTitleAndDescription($property_title_and_description);
        return $clone;
    }

    public function storePropertyTitleAndDescription(
        TitleAndDescription $property_title_and_description
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

    public function getPropertyIsOnline(): Online
    {
        return $this->core_properties->getPropertyIsOnline();
    }

    public function storePropertyIsOnline(Online $property_is_online): void
    {
        $this->core_properties = $this->core_properties_repository->store(
            $this->core_properties->withPropertyIsOnline($property_is_online)
        );
    }

    public function withPropertyIsOnline(
        Online $property_is_online
    ): self {
        $clone = clone $this;
        $clone->core_properties = $this->core_properties
            ->withPropertyIsOnline($property_is_online);
        return $clone;
    }

    public function getPropertyTitleAndIconVisibility(): Property
    {
        return $this->additional_properties->getPropertyTitleAndIconVisibility();
    }

    public function storePropertyTitleAndIconVisibility(
        TitleAndIconVisibility $property_title_and_icon_visibility
    ): void {
        $this->additional_properties = $this->additional_properties_repository->store(
            $this->additional_properties
            ->withPropertyTitleAndIconVisibility($property_title_and_icon_visibility)
        );
    }

    public function getPropertyHeaderActionVisibility(): Property
    {
        return $this->additional_properties->getPropertyHeaderActionVisibility();
    }

    public function storePropertyHeaderActionVisibility(
        HeaderActionVisibility $property_header_action_visibility
    ): void {
        $this->additional_properties = $this->additional_properties_repository->store(
            $this->additional_properties
            ->withPropertyHeaderActionVisibility($property_header_action_visibility)
        );
    }

    public function getPropertyInfoTabVisibility(): Property
    {
        return $this->additional_properties->getPropertyInfoTabVisibility();
    }

    public function storePropertyInfoTabVisibility(
        InfoTabVisibility $property_info_tab_visibility
    ): void {
        $this->additional_properties = $this->additional_properties_repository->store(
            $this->additional_properties
            ->withPropertyInfoTabVisibility($property_info_tab_visibility)
        );
    }

    public function getPropertyTileImage(): PropertyTileImage
    {
        return $this->core_properties->getPropertyTileImage();
    }

    public function storePropertyTileImage(
        PropertyTileImage $property_tile_image
    ): void {
        $this->core_properties = $this->core_properties_repository->store(
            $this->core_properties
            ->withPropertyTileImage($property_tile_image)
        );
    }

    public function getPropertyIcon(): Property
    {
        return $this->additional_properties->getPropertyIcon();
    }

    public function storePropertyIcon(
        Icon $property_icon
    ): void {
        $this->additional_properties = $this->additional_properties_repository->store(
            $this->additional_properties
            ->withPropertyIcon($property_icon)
        );
    }

    public function getPropertyTranslations(): Translations
    {
        return $this->translations;
    }

    public function storePropertyTranslations(
        Translations $translations
    ): void {
        $this->translations = $this->translations_repository->store($translations);
    }

    public function deletePropertyTranslations(): void
    {
        $this->translations_repository->delete($this->translations->getObjId());
    }

    public function clonePropertyTranslations(int $new_obj_id): Translations
    {
        return $this->translations_repository->store(
            $this->translations->copy($new_obj_id)
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
