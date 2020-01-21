<?php

/* Copyright (c) 1998-2018 ILIAS open source, Extended GPL, see docs/LICENSE */

/**
 * Common settings form adapter. Helps to add and save common object settings for repository objects.
 *
 * @author killing@leifos.de
 * @ingroup ServicesObject
 */
class ilObjectCommonSettingFormAdapter implements ilObjectCommonSettingFormAdapterInterface
{
    /**
     * @var ilObjectService
     */
    protected $service;

    /**
     * @var ilPropertyFormGUI
     */
    protected $legacy_form;

    /**
     * @var ilObject
     */
    protected $object;

    /**
     * Constructor
     */
    public function __construct(ilObjectService $service, ilObject $object, ilPropertyFormGUI $legacy_form = null)
    {
        $this->service = $service;
        $this->legacy_form = $legacy_form;
        $this->object = $object;
    }

    /**
     * @inheritdoc
     */
    public function addIcon() : ilPropertyFormGUI
    {
        global $DIC;

        if ($this->service->settings()->get('custom_icons')) {
            if (!is_null($this->legacy_form)) {
                // we do not clone for legacy forms, since initEditCustomForm relies on "call by reference" behaviour
                //$this->legacy_form = clone $this->legacy_form;
                require_once 'Services/Object/Icon/classes/class.ilObjectCustomIconConfigurationGUI.php';
                $gui = new \ilObjectCustomIconConfigurationGUI($DIC, null, $this->object);
                $gui->addSettingsToForm($this->legacy_form);
            }
        }
        return $this->legacy_form;
    }

    /**
     * @inheritdoc
     */
    public function saveIcon()
    {
        global $DIC;

        if ($this->service->settings()->get('custom_icons')) {
            if (!is_null($this->legacy_form)) {
                $this->legacy_form = clone $this->legacy_form;
                require_once 'Services/Object/Icon/classes/class.ilObjectCustomIconConfigurationGUI.php';
                $gui = new \ilObjectCustomIconConfigurationGUI($DIC, null, $this->object);
                $gui->saveIcon($this->legacy_form);
            }
        }
    }

    /**
     * @inheritdoc
     */
    public function addTileImage() : ilPropertyFormGUI
    {
        $lng = $this->service->language();
        $lng->loadLanguageModule("obj");
        $tile_image_fac = $this->service->commonSettings()->tileImage();

        if (!is_null($this->legacy_form)) {
            // we do not clone for legacy forms, since initEditCustomForm relies on "call by reference" behaviour
            //$this->legacy_form = clone $this->legacy_form;

            $tile_image = $tile_image_fac->getByObjId($this->object->getId());
            $timg = new \ilImageFileInputGUI($lng->txt('obj_tile_image'), 'tile_image');
            $timg->setInfo($lng->txt('obj_tile_image_info'));
            $timg->setSuffixes($tile_image_fac->getSupportedFileExtensions());
            $timg->setUseCache(false);
            if ($tile_image->exists()) {
                $timg->setImage($tile_image->getFullPath());
            } else {
                $timg->setImage('');
            }
            $this->legacy_form->addItem($timg);

            /*
            $file = new ilFileStandardDropzoneInputGUI($lng->txt('obj_tile_image'), 'tile_image');
            $file->setRequired(false);
            $file->setSuffixes($tile_image_fac->getSupportedFileExtensions());
            $this->legacy_form->addItem($file);*/
        }

        return $this->legacy_form;
    }

    /**
     * @inheritdoc
     */
    public function saveTileImage()
    {
        $tile_image_fac = $this->service->commonSettings()->tileImage();

        if (!is_null($this->legacy_form)) {
            $tile_image = $tile_image_fac->getByObjId($this->object->getId());

            /** @var \ilImageFileInputGUI $item */
            $item = $this->legacy_form->getItemByPostVar('tile_image');
            if ($item->getDeletionFlag()) {
                $tile_image->delete();
            }

            $file_data = $this->legacy_form->getInput('tile_image');
            if ($file_data['tmp_name']) {
                $tile_image->saveFromHttpRequest($file_data['tmp_name']);
            }
        }
    }

    /**
     * @inheritdoc
     */
    public function addTitleIconVisibility() : ilPropertyFormGUI
    {
        $lng = $this->service->language();
        $hide = new ilCheckboxInputGUI($lng->txt("obj_show_title_and_icon"), "show_header_icon_and_title");
        $hide->setChecked(!ilContainer::_lookupContainerSetting($this->object->getId(), "hide_header_icon_and_title"));
        $this->legacy_form->addItem($hide);
        return $this->legacy_form;
    }

    /**
     * @inheritdoc
     */
    public function saveTitleIconVisibility()
    {
        if (!is_null($this->legacy_form)) {
            // hide icon/title
            ilContainer::_writeContainerSetting(
                $this->object->getId(),
                "hide_header_icon_and_title",
                !$this->legacy_form->getInput("show_header_icon_and_title")
            );
        }
    }

    /**
     * @inheritdoc
     */
    public function addTopActionsVisibility() : ilPropertyFormGUI
    {
        $lng = $this->service->language();
        $hide = new ilCheckboxInputGUI($lng->txt("obj_show_header_actions"), "show_top_actions");
        $hide->setChecked(!ilContainer::_lookupContainerSetting($this->object->getId(), "hide_top_actions"));
        $this->legacy_form->addItem($hide);
        return $this->legacy_form;
    }

    /**
     * @inheritdoc
     */
    public function saveTopActionsVisibility()
    {
        if (!is_null($this->legacy_form)) {
            // hide icon/title
            ilContainer::_writeContainerSetting(
                $this->object->getId(),
                "hide_top_actions",
                !$this->legacy_form->getInput("show_top_actions")
            );
        }
    }
}
