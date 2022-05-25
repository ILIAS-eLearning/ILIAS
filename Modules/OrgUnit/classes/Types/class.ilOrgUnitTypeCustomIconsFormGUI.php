<?php

/**
 * Class ilOrgUnitTypeFormGUI
 * @author Stefan Wanzenried <sw@studer-raimann.ch>
 * @author Fabian Schmid <fs@studer-raimann.ch>
 */
class ilOrgUnitTypeCustomIconsFormGUI extends ilPropertyFormGUI
{
    protected ilObjectGUI $parent_gui;
    protected ilOrgUnitType $type;
    protected ilCtrl $ctrl;
    protected ilLanguage $lng;

    public function __construct(ilObjectGUI $parent_gui, ilOrgUnitType $type)
    {
        global $DIC;
        parent::__construct();
        $this->parent_gui = $parent_gui;
        $this->type = $type;
        $this->tpl =  $DIC->ui()->mainTemplate();
        $this->ctrl = $DIC->ctrl();
        $this->lng = $DIC->language();
        $this->lng->loadLanguageModule('meta');
        $this->initForm();
    }

    /**
     * Save object (create or update)
     */
    public function saveObject(): bool
    {
        if (!$this->fillObject()) {
            return false;
        }
        try {
            $this->type->save();

            return true;
        } catch (ilException $e) {
            $this->global_tpl->setOnScreenMessage('failure', $e->getMessage());

            return false;
        }
    }

    /**
     * Add all fields to the form
     */
    private function initForm(): void
    {
        $this->setFormAction($this->ctrl->getFormAction($this->parent_gui));
        $this->setTitle($this->lng->txt('orgu_type_custom_icon'));
        $item = new ilImageFileInputGUI($this->lng->txt('icon') . ' 32x32 px', 'icon');
        $item->setSuffixes(array('svg'));
        $item->setInfo($this->lng->txt('orgu_type_custom_icon_info'));
        if (is_file($this->type->getIconPath(true))) {
            $item->setImage($this->type->getIconPath(true));
        }
        $this->addItem($item);
        $this->addCommandButton('updateCustomIcons', $this->lng->txt('save'));
    }

    /**
     * Check validity of form and pass values from form to object
     * @return bool
     */
    private function fillObject(): bool
    {
        $this->setValuesByPost();
        if (!$this->checkInput()) {
            return false;
        }
        $file_data = (array) $this->getInput('icon');
        /** @var ilImageFileInputGUI $item */
        $item = $this->getItemByPostVar('icon');
        try {
            if (isset($file_data['name']) && $file_data['name']) {
                $this->type->removeIconFile();
                $this->type->setIcon($file_data['name']);
                $this->type->processAndStoreIconFile($file_data);
            } else {
                if ($item->getDeletionFlag()) {
                    $this->type->removeIconFile();
                    $this->type->setIcon('');
                }
            }
        } catch (ilException $e) {
            $this->global_tpl->setOnScreenMessage('failure', $this->lng->txt('orgu_type_msg_error_custom_icon'));

            return false;
        }

        return true;
    }
}
