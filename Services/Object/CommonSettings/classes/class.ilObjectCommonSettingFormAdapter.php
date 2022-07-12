<?php declare(strict_types=1);

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
 
class ilObjectCommonSettingFormAdapter implements ilObjectCommonSettingFormAdapterInterface
{
    protected ilObjectService $service;
    protected ilObject $object;
    protected ?ilPropertyFormGUI $legacy_form;

    public function __construct(ilObjectService $service, ilObject $object, ilPropertyFormGUI $legacy_form = null)
    {
        $this->service = $service;
        $this->legacy_form = $legacy_form;
        $this->object = $object;

        $lng = $this->service->language();
        $lng->loadLanguageModule('obj');
        $lng->loadLanguageModule('cntr');
    }

    public function addIcon() : ?ilPropertyFormGUI
    {
        global $DIC;

        if (!is_null($this->legacy_form) && $this->service->settings()->get('custom_icons')) {
            // we do not clone for legacy forms, since initEditCustomForm relies on 'call by reference' behaviour
            //$this->legacy_form = clone $this->legacy_form;
            $gui = new ilObjectCustomIconConfigurationGUI($DIC, null, $this->object);
            $gui->addSettingsToForm($this->legacy_form);
        }

        return $this->legacy_form;
    }

    public function saveIcon() : void
    {
        global $DIC;

        if (!is_null($this->legacy_form) && $this->service->settings()->get('custom_icons')) {
            $this->legacy_form = clone $this->legacy_form;
            $gui = new ilObjectCustomIconConfigurationGUI($DIC, null, $this->object);
            $gui->saveIcon($this->legacy_form);
        }
    }

    public function addTileImage() : ?ilPropertyFormGUI
    {
        $lng = $this->service->language();
        $tile_image_fac = $this->service->commonSettings()->tileImage();

        if (!is_null($this->legacy_form)) {
            // we do not clone for legacy forms, since initEditCustomForm relies on 'call by reference' behaviour
            //$this->legacy_form = clone $this->legacy_form;

            $tile_image = $tile_image_fac->getByObjId($this->object->getId());
            $timg = new ilImageFileInputGUI($lng->txt('obj_tile_image'), 'tile_image');
            $timg->setInfo($lng->txt('obj_tile_image_info'));
            $timg->setSuffixes($tile_image_fac->getSupportedFileExtensions());
            $timg->setUseCache(false);
            if ($tile_image->exists()) {
                $timg->setImage($tile_image->getFullPath());
            } else {
                $timg->setImage('');
            }
            $this->legacy_form->addItem($timg);
        }

        return $this->legacy_form;
    }

    public function saveTileImage() : void
    {
        $tile_image_fac = $this->service->commonSettings()->tileImage();

        if (!is_null($this->legacy_form)) {
            $tile_image = $tile_image_fac->getByObjId($this->object->getId());

            /** @var ilImageFileInputGUI $item */
            $item = $this->legacy_form->getItemByPostVar('tile_image');
            if ($item && $item->getDeletionFlag()) {
                $tile_image->delete();
            }

            $file_data = $this->legacy_form->getInput('tile_image');
            if (isset($file_data['tmp_name']) && $file_data['tmp_name']) {
                $tile_image->saveFromHttpRequest($file_data['tmp_name']);
            }
        }
    }

    public function addTitleIconVisibility() : ilPropertyFormGUI
    {
        $hide = new ilCheckboxInputGUI(
            $this->service->language()->txt('obj_show_title_and_icon'),
            'show_header_icon_and_title'
        );
        $hide->setValue('1');
        $hide->setChecked(
            !((bool) ilContainer::_lookupContainerSetting($this->object->getId(), 'hide_header_icon_and_title'))
        );
        $this->legacy_form->addItem($hide);

        return $this->legacy_form;
    }

    public function saveTitleIconVisibility() : void
    {
        if (!is_null($this->legacy_form)) {
            // hide icon/title
            ilContainer::_writeContainerSetting(
                $this->object->getId(),
                'hide_header_icon_and_title',
                (string) !$this->legacy_form->getInput('show_header_icon_and_title')
            );
        }
    }

    public function addTopActionsVisibility() : ilPropertyFormGUI
    {
        $hide = new ilCheckboxInputGUI(
            $this->service->language()->txt('obj_show_header_actions'),
            'show_top_actions'
        );
        $hide->setValue('1');
        $hide->setChecked(
            !((bool) ilContainer::_lookupContainerSetting($this->object->getId(), 'hide_top_actions'))
        );
        $this->legacy_form->addItem($hide);

        return $this->legacy_form;
    }

    public function saveTopActionsVisibility() : void
    {
        if (!is_null($this->legacy_form)) {
            // hide icon/title
            ilContainer::_writeContainerSetting(
                $this->object->getId(),
                'hide_top_actions',
                (string) !$this->legacy_form->getInput('show_top_actions')
            );
        }
    }
}
