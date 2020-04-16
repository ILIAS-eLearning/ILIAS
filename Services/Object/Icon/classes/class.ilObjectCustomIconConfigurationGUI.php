<?php
/* Copyright (c) 1998-2018 ILIAS open source, Extended GPL, see docs/LICENSE */

/**
 * Class ilObjectCustomIconConfigurationGUI
 */
class ilObjectCustomIconConfigurationGUI
{
    /** @var string */
    const DEFAULT_CMD = 'showForm';

    /** @var \ILIAS\DI\Container */
    protected $dic;

    /** @var \ilObject */
    protected $object;

    /** @var \ilObjectGUI|mixed */
    protected $parentGui;

    /**
     * @var string|null
     */
    protected $uploadFieldInformationText = null;

    /**
     * ilObjectCustomIconConfigurationGUI constructor.
     * @param \ILIAS\DI\Container $dic
     * @param \ilObjectGUI|mixed  $parentGui
     * @param ilObject            $object
     */
    public function __construct(\ILIAS\DI\Container $dic, $parentGui, \ilObject $object)
    {
        $this->dic = $dic;
        $this->parentGui = $parentGui;
        $this->object = $object;
    }

    /**
     * @param null|string $uploadFieldInformationText
     */
    public function setUploadFieldInformationText($uploadFieldInformationText)
    {
        $this->uploadFieldInformationText = $uploadFieldInformationText;
    }

    public function executeCommand()
    {
        $nextClass = $this->dic->ctrl()->getNextClass($this);
        $cmd = $this->dic->ctrl()->getCmd(self::DEFAULT_CMD);

        switch (true) {
            case method_exists($this, $cmd):
                $this->{$cmd}();
                break;

            default:
                $this->{self::DEFAULT_CMD}();
                break;
        }
    }

    /**
     * @param ilPropertyFormGUI|null $form
     */
    protected function showForm(\ilPropertyFormGUI $form = null)
    {
        if (!$form) {
            $form = $this->getForm();
        }

        $this->dic->ui()->mainTemplate()->setContent($form->getHTML());
    }

    /**
     * @return \ilPropertyFormGUI
     */
    protected function getForm() : \ilPropertyFormGUI
    {
        $this->dic->language()->loadLanguageModule('cntr');

        $form = new \ilPropertyFormGUI();
        $form->setFormAction($this->dic->ctrl()->getFormAction($this, 'saveForm'));
        $form->setTitle($this->dic->language()->txt('icon_settings'));

        $this->addSettingsToForm($form);

        $form->addCommandButton('saveForm', $this->dic->language()->txt('save'));

        return $form;
    }

    /**
     * Add settings to form
     *
     * @param ilPropertyFormGUI $form
     */
    public function addSettingsToForm(ilPropertyFormGUI $form)
    {
        /** @var \ilObjectCustomIconFactory $customIconFactory */
        $customIconFactory = $this->dic['object.customicons.factory'];
        $customIcon = $customIconFactory->getByObjId($this->object->getId(), $this->object->getType());

        $icon = new \ilImageFileInputGUI($this->dic->language()->txt('cont_custom_icon'), 'icon');
        if (is_string($this->uploadFieldInformationText)) {
            $icon->setInfo($this->uploadFieldInformationText);
        }
        $icon->setSuffixes($customIcon->getSupportedFileExtensions());
        $icon->setUseCache(false);
        if ($customIcon->exists()) {
            $icon->setImage($customIcon->getFullPath());
        } else {
            $icon->setImage('');
        }
        $form->addItem($icon);
    }

    /**
     *
     */
    protected function saveForm()
    {
        $form = $this->getForm();
        if ($form->checkInput()) {
            $this->saveIcon($form);

            ilUtil::sendSuccess($this->dic->language()->txt('msg_obj_modified'), true);
            $this->dic->ctrl()->redirect($this, 'showForm');
        }

        $form->setValuesByPost();
        $this->showForm($form);
    }

    /**
     * Save icon
     *
     * @param ilPropertyFormGUI $form
     */
    public function saveIcon(ilPropertyFormGUI $form)
    {
        /** @var \ilObjectCustomIconFactory $customIconFactory */
        $customIconFactory = $this->dic['object.customicons.factory'];
        $customIcon = $customIconFactory->getByObjId($this->object->getId(), $this->object->getType());

        /** @var \ilImageFileInputGUI $item */
        $fileData = (array) $form->getInput('icon');
        $item = $form->getItemByPostVar('icon');

        if ($item->getDeletionFlag()) {
            $customIcon->remove();
        }

        if ($fileData['tmp_name']) {
            $customIcon->saveFromHttpRequest();
        }
    }
}
