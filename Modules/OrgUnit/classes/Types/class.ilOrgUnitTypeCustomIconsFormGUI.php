<?php
require_once('./Services/Form/classes/class.ilPropertyFormGUI.php');

/**
 * Class ilOrgUnitTypeFormGUI
 *
 * @author Stefan Wanzenried <sw@studer-raimann.ch>
 */
class ilOrgUnitTypeCustomIconsFormGUI extends ilPropertyFormGUI {

    /**
     * @var ilOrgUnitType
     */
    protected $type;

    /**
     * @var ilTemplate
     */
    protected $tpl;

    /**
     * @var
     */
    protected $lng;

    /**
     * @var ilCtrl
     */
    protected $ctrl;

    /**
     * @var
     */
    protected $parent_gui;


    public function __construct($parent_gui, ilOrgUnitType $type) {
        global $tpl, $ilCtrl, $lng;
        $this->parent_gui = $parent_gui;
        $this->type = $type;
        $this->tpl = $tpl;
        $this->ctrl = $ilCtrl;
        $this->lng = $lng;
        $this->lng->loadLanguageModule('meta');
        $this->initForm();
    }


    /**
     * Save object (create or update)
     *
     * @return bool
     */
    public function saveObject() {
        if (!$this->fillObject()) {
            return false;
        }
        try {
            $this->type->save();
            return true;
        } catch (ilException $e) {
            ilUtil::sendFailure($e->getMessage());
            return false;
        }
    }

    /**
     * Add all fields to the form
     */
    protected function initForm() {
        $this->setFormAction($this->ctrl->getFormAction($this->parent_gui));
        $this->setTitle($this->lng->txt('orgu_type_custom_icon'));
        $item = new ilImageFileInputGUI($this->lng->txt('icon') . ' 32x32 px', 'icon');
        $item->setInfo($this->lng->txt('orgu_type_custom_icon_info'));
        if (is_file($this->type->getIconPath(true))) {
            $item->setImage($this->type->getIconPath(true));
        }
        $this->addItem($item);
        $this->addCommandButton('updateCustomIcons', $this->lng->txt('save'));
    }

    /**
     * Check validity of form and pass values from form to object
     *
     * @return bool
     */
    protected function fillObject() {
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
            } else if ($item->getDeletionFlag()) {
                $this->type->removeIconFile();
                $this->type->setIcon('');
            }
        } catch (ilException $e) {
            ilUtil::sendFailure($this->lng->txt('orgu_type_msg_error_custom_icon'));
            return false;
        }
        return true;
    }

}