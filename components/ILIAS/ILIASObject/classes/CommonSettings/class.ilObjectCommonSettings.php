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
    private ?ilObject $object = null;

    public function __construct(
        private ilLanguage $language,
        private FileUpload $upload,
        private ResourceStorageServices $storage,
        private Services $http,
        private ilObjectTileImageStakeholder $stakeholder,
        private ilObjectTileImageFlavourDefinition $flavour
    ) {
    }

    public function getPropertyTitleAndIconVisibility(): ?ilObjectPropertyTitleAndIconVisibility
    {
        return $this->object?->getObjectProperties()->getPropertyTitleAndIconVisibility();
    }

    public function storePropertyTitleAndIconVisibility(
        ilObjectPropertyTitleAndIconVisibility $property_title_and_icon_visibility
    ): void {
        $this->object?->getObjectProperties()->storePropertyTitleAndIconVisibility($property_title_and_icon_visibility);
        $this->object?->flushObjectProperties();
    }

    public function getPropertyHeaderActionVisibility(): ?ilObjectPropertyHeaderActionVisibility
    {
        return $this->object?->getObjectProperties()->getPropertyHeaderActionVisibility();
    }

    public function storePropertyHeaderActionVisibility(
        ilObjectPropertyHeaderActionVisibility $property_header_action_visibility
    ): void {
        $this->object?->getObjectProperties()->storePropertyHeaderActionVisibility($property_header_action_visibility);
        $this->object?->flushObjectProperties();
    }

    public function getPropertyTileImage(): ?ilObjectPropertyTileImage
    {
        return $this->object?->getObjectProperties()->getPropertyTileImage();
    }

    public function storePropertyTileImage(
        ilObjectPropertyTileImage $property_tile_image
    ): void {
        $this->object?->getObjectProperties()->storePropertyTileImage($property_tile_image);
        $this->object?->flushObjectProperties();
    }

    public function getPropertyIcon(): ?ilObjectPropertyIcon
    {
        return $this->object?->getObjectProperties()->getPropertyIcon();
    }

    public function storePropertyIcon(
        ilObjectPropertyIcon $property_icon
    ): void {
        $this->object?->getObjectProperties()->storePropertyIcon($property_icon);
        $this->object?->flushObjectProperties();
    }

    /**
     *
     * @depricated 11: This function will be remove with ILIAS 11. Please use
     * the `ilObjectProperty::toForm()` for each Property(-Set) to get the corresponding
     * Form-Elements for the UIKitchensink-Forms.
     */
    public function legacyForm(ilPropertyFormGUI $form, ilObject $object): ilObjectCommonSettingFormAdapter
    {
        $this->object = $object;

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
