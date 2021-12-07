<?php declare(strict_types=1);

/* Copyright (c) 1998-2018 ILIAS open source, Extended GPL, see docs/LICENSE */

class ilObjectCustomIconConfigurationGUI
{
    private const DEFAULT_CMD = 'showForm';

    protected \ILIAS\DI\Container $dic;
    protected ilObject $object;
    /** @var \ilObjectGUI|mixed */
    protected $parentGui;
    protected ?string $uploadFieldInformationText = null;

    public function __construct(\ILIAS\DI\Container $dic, $parentGui, ilObject $object)
    {
        $this->dic = $dic;
        $this->parentGui = $parentGui;
        $this->object = $object;
    }

    public function setUploadFieldInformationText(?string $uploadFieldInformationText) : void
    {
        $this->uploadFieldInformationText = $uploadFieldInformationText;
    }

    public function executeCommand() : void
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

    protected function showForm(?ilPropertyFormGUI $form = null) : void
    {
        if (null === $form) {
            $form = $this->getForm();
        }

        $this->dic->ui()->mainTemplate()->setContent($form->getHTML());
    }

    protected function getForm() : ilPropertyFormGUI
    {
        $this->dic->language()->loadLanguageModule('cntr');

        $form = new ilPropertyFormGUI();
        $form->setFormAction($this->dic->ctrl()->getFormAction($this, 'saveForm'));
        $form->setTitle($this->dic->language()->txt('icon_settings'));

        $this->addSettingsToForm($form);

        $form->addCommandButton('saveForm', $this->dic->language()->txt('save'));

        return $form;
    }

    public function addSettingsToForm(ilPropertyFormGUI $form) : void
    {
        /** @var ilObjectCustomIconFactory $customIconFactory */
        $customIconFactory = $this->dic['object.customicons.factory'];
        $customIcon = $customIconFactory->getByObjId($this->object->getId(), $this->object->getType());

        $icon = new ilImageFileInputGUI($this->dic->language()->txt('cont_custom_icon'), 'icon');
        if (is_string($this->uploadFieldInformationText) && $this->uploadFieldInformationText !== '') {
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

    protected function saveForm() : void
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

    public function saveIcon(ilPropertyFormGUI $form) : void
    {
        /** @var ilObjectCustomIconFactory $customIconFactory */
        $customIconFactory = $this->dic['object.customicons.factory'];
        $customIcon = $customIconFactory->getByObjId($this->object->getId(), $this->object->getType());

        /** @var ilImageFileInputGUI $item */
        $fileData = (array) $form->getInput('icon');
        $item = $form->getItemByPostVar('icon');

        if ($item && $item->getDeletionFlag()) {
            $customIcon->remove();
        }

        if (isset($fileData['tmp_name']) && $fileData['tmp_name']) {
            $customIcon->saveFromHttpRequest();
        }
    }
}
