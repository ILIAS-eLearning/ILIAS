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
use ILIAS\FileUpload\DTO\UploadResult;
use ILIAS\ResourceStorage\Services as ResourceStorageServices;
use ILIAS\Object\Properties\CoreProperties\TileImage\ilObjectTileImageStakeholder;
use ILIAS\Object\Properties\CoreProperties\TileImage\ilObjectTileImageFlavourDefinition;

/**
 *
 * @deprecated 11: This class will be remove with ILIAS 11. Please use the
 * corresponding implementation of `ilObjectProperty` instead.
 */
class ilObjectCommonSettingFormAdapter implements ilObjectCommonSettingFormAdapterInterface
{
    public function __construct(
        private ilLanguage $language,
        private FileUpload $upload,
        private ResourceStorageServices $storage,
        private ilObjectTileImageStakeholder $stakeholder,
        private ilObjectTileImageFlavourDefinition $flavour,
        private ilObjectCommonSettings $common_settings,
        private ?ilPropertyFormGUI $legacy_form = null
    ) {
        $this->language->loadLanguageModule('obj');
        $this->language->loadLanguageModule('cntr');
    }

    public function addIcon(): ?ilPropertyFormGUI
    {
        $icon = $this->common_settings->getPropertyIcon()->toLegacyForm($this->language);
        if (!is_null($this->legacy_form) && $icon !== null) {
            $this->legacy_form->addItem($icon);
        }

        return $this->legacy_form;
    }

    public function saveIcon(): void
    {
        if (is_null($this->legacy_form)) {
            return;
        }

        $item = $this->legacy_form->getItemByPostVar('icon');
        if ($item && $item->getDeletionFlag()) {
            $this->common_settings->storePropertyIcon(
                $this->common_settings->getPropertyIcon()->withDeletedFlag()
            );
            return;
        }

        $file_data = (array) $this->legacy_form->getInput('icon');
        if (isset($file_data['tmp_name']) && $file_data['tmp_name']) {
            $tempfile = ilFileUtils::ilTempnam();
            if (!$this->upload->hasBeenProcessed()) {
                $this->upload->process();
            }

            rename($file_data['tmp_name'], $tempfile);

            $this->common_settings->storePropertyIcon(
                $this->common_settings->getPropertyIcon()->withTempFileName(basename($tempfile))
            );
        }
    }

    public function addTileImage(): ?ilPropertyFormGUI
    {
        if (!is_null($this->legacy_form)) {
            $timg = $this->common_settings->getPropertyTileImage()->toLegacyForm($this->language);
            $this->legacy_form->addItem($timg);
        }

        return $this->legacy_form;
    }

    public function saveTileImage(): void
    {
        if (is_null($this->legacy_form)) {
            return;
        }

        $item = $this->legacy_form->getItemByPostVar('tile_image');
        if ($item && $item->getDeletionFlag()) {
            $this->common_settings->storePropertyTileImage(
                $this->common_settings->getPropertyTileImage()->withDeletedFlag()
            );
            return;
        }

        $this->upload->process();
        $result_array = $this->upload->getResults();
        $result = end($result_array);

        if (!($result instanceof UploadResult) || !$result->isOK()) {
            return;
        }

        if ($item->getValue() === null || $item->getValue() === '') {
            $i = $this->storage->manage()->upload($result, $this->stakeholder);
            $this->storage->flavours()->ensure($i, $this->flavour);
            $new_tile_image = $this->common_settings->getPropertyTileImage()
                ->getTileImage()->withRid($i->serialize());
            $this->common_settings->storePropertyTileImage(
                $this->common_settings->getPropertyTileImage()->withTileImage($new_tile_image)
            );
        } else {
            $i = $this->storage->manage()->find($item->getValue());
            $this->storage->manage()->replaceWithUpload(
                $i,
                $result,
                $this->stakeholder
            );
        }
    }

    public function addTitleIconVisibility(): ilPropertyFormGUI
    {
        $title_and_icon_visibility_input = $this->common_settings->getPropertyTitleAndIconVisibility()
            ->toLegacyForm($this->language);
        $this->legacy_form->addItem($title_and_icon_visibility_input);

        return $this->legacy_form;
    }

    public function saveTitleIconVisibility(): void
    {
        if (is_null($this->legacy_form)) {
            return;
        }

        $this->common_settings->storePropertyTitleAndIconVisibility(
            new ilObjectPropertyTitleAndIconVisibility((bool) $this->legacy_form->getInput('show_header_icon_and_title'))
        );
    }

    public function addTopActionsVisibility(): ilPropertyFormGUI
    {
        $top_actions_visibility_input = $this->common_settings->getPropertyHeaderActionVisibility()
            ->toLegacyForm($this->language);
        $this->legacy_form->addItem($top_actions_visibility_input);

        return $this->legacy_form;
    }

    public function saveTopActionsVisibility(): void
    {
        if (is_null($this->legacy_form)) {
            return;
        }

        $this->common_settings->storePropertyHeaderActionVisibility(
            new ilObjectPropertyHeaderActionVisibility((bool) $this->legacy_form->getInput('show_top_actions'))
        );
    }
}
