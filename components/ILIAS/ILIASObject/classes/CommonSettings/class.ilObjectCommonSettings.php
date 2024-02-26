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

use ILIAS\FileUpload\FileUpload;
use ILIAS\ResourceStorage\Services as ResourceStorageServices;
use ILIAS\Object\Properties\CoreProperties\TileImage\ilObjectPropertyTileImage;
use ILIAS\Object\Properties\CoreProperties\TileImage\ilObjectTileImageStakeholder;
use ILIAS\Object\Properties\CoreProperties\TileImage\ilObjectTileImageFlavourDefinition;
use ILIAS\HTTP\Services;

/**
 * @deprecated 11 This class will be removed with ILIAS 11. Please use
 * `ilObjectProperties` instead.
 */
class ilObjectCommonSettings
{
    private ilObjectAdditionalProperties $additional_properties;
    private ilObjectCoreProperties $core_properties;

    public function __construct(
        private ilLanguage $language,
        private FileUpload $upload,
        private ResourceStorageServices $storage,
        private Services $http,
        private ilObjectTileImageStakeholder $stakeholder,
        private ilObjectTileImageFlavourDefinition $flavour,
        private ilObjectCorePropertiesRepository $core_properties_repository,
        private ilObjectAdditionalPropertiesRepository $additional_properties_repository
    ) {
    }

    public function getPropertyTitleAndIconVisibility(): ilObjectPropertyTitleAndIconVisibility
    {
        return $this->additional_properties->getPropertyTitleAndIconVisibility();
    }

    public function storePropertyTitleAndIconVisibility(
        ilObjectPropertyTitleAndIconVisibility $property_title_and_icon_visibility
    ): void {
        $this->additional_properties = $this->additional_properties
            ->withPropertyTitleAndIconVisibility($property_title_and_icon_visibility);
        $this->additional_properties_repository->store($this->additional_properties);
    }

    public function getPropertyHeaderActionVisibility(): ilObjectPropertyHeaderActionVisibility
    {
        return $this->additional_properties->getPropertyHeaderActionVisibility();
    }

    public function storePropertyHeaderActionVisibility(
        ilObjectPropertyHeaderActionVisibility $property_header_action_visibility
    ): void {
        $this->additional_properties = $this->additional_properties
            ->withPropertyHeaderActionVisibility($property_header_action_visibility);
        $this->additional_properties_repository->store($this->additional_properties);
    }

    public function getPropertyTileImage(): ilObjectPropertyTileImage
    {
        return $this->core_properties->getPropertyTileImage();
    }

    public function storePropertyTileImage(
        ilObjectPropertyTileImage $property_tile_image
    ): void {
        $this->core_properties = $this->core_properties
            ->withPropertyTileImage($property_tile_image);
        $this->core_properties_repository->store($this->core_properties);
    }

    public function getPropertyIcon(): ilObjectPropertyIcon
    {
        return $this->additional_properties->getPropertyIcon();
    }

    public function storePropertyIcon(
        ilObjectPropertyIcon $property_icon
    ): void {
        $this->additional_properties = $this->additional_properties
            ->withPropertyIcon($property_icon);
        $this->additional_properties_repository->store($this->additional_properties);
    }

    /**
     *
     * @depricated 11: This function will be remove with ILIAS 11. Please use
     * the `ilObjectProperty::toForm()` for each Property(-Set) to get the corresponding
     * Form-Elements for the UIKitchensink-Forms.
     */
    public function legacyForm(ilPropertyFormGUI $form, ilObject $object): ilObjectCommonSettingFormAdapter
    {
        $this->additional_properties = $this->additional_properties_repository->getFor($object->getId());
        $this->core_properties = $this->core_properties_repository->getFor($object->getId());

        return new ilObjectCommonSettingFormAdapter(
            $this->language,
            $this->upload,
            $this->storage,
            $this->stakeholder,
            $this->flavour,
            $this,
            $this->http,
            $form
        );
    }
}
