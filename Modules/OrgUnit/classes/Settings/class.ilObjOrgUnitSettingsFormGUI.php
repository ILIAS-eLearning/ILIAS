<?php

/**
 * Class ilObjOrgUnitSettingsFormGUI
 *
 * @author Stefan Wanzenried <sw@studer-raimann.ch>
 */
class ilObjOrgUnitSettingsFormGUI extends ilPropertyFormGUI
{

    /**
     * @var ilObjOrgUnit
     */
    protected $obj_orgu;

    /**
     * @var ilTemplate
     */
    protected $tpl;

    /**
     * @var ilLanguage
     */
    protected $lng;

    /**
     * @var ilCtrl
     */
    protected $ctrl;

    /**
     * @var ilObjUser
     */
    protected $user;

    /**
     * @var
     */
    protected $parent_gui;


    public function __construct($parent_gui, ilObjOrgUnit $obj_orgu)
    {
        global $DIC;
        $tpl = $DIC['tpl'];
        $ilCtrl = $DIC['ilCtrl'];
        $lng = $DIC['lng'];
        $ilUser = $DIC['ilUser'];
        $this->parent_gui = $parent_gui;
        $this->obj_orgu = $obj_orgu;
        $this->tpl = $tpl;
        $this->ctrl = $ilCtrl;
        $this->lng = $lng;
        $this->user = $ilUser;
        $this->initForm();
    }


    /**
     * Update object
     *
     * @return bool
     */
    public function saveObject()
    {
        if (!$this->fillObject()) {
            return false;
        }
        $this->obj_orgu->update();
        $this->updateTranslation();
        return true;
    }

    /**
     * Add all fields to the form
     */
    protected function initForm()
    {
        $this->setFormAction($this->ctrl->getFormAction($this->parent_gui));
        $this->setTitle($this->lng->txt('orgu_settings'));

        $item = new ilTextInputGUI($this->lng->txt('title'), 'title');
        $item->setRequired(true);
        $item->setValue($this->obj_orgu->getTitle());
        $this->addItem($item);

        $item = new ilTextAreaInputGUI($this->lng->txt('description'), 'description');
        $item->setValue($this->obj_orgu->getDescription());
        $this->addItem($item);

        $item = new ilFormSectionHeaderGUI();
        $item->setTitle($this->lng->txt('orgu_type'));
        $this->addItem($item);
        $types = ilOrgUnitType::getAllTypes();
        $options = array(0 => '');
        /** @var ilOrgUnitType $type */
        foreach ($types as $type) {
            $options[$type->getId()] = $type->getTitle();
        }
        asort($options);
        $item = new ilSelectInputGUI($this->lng->txt('orgu_type'), 'orgu_type');
        $item->setOptions($options);
        $item->setValue($this->obj_orgu->getOrgUnitTypeId());
        $this->addItem($item);

        $item = new ilFormSectionHeaderGUI();
        $item->setTitle($this->lng->txt('ext_id'));
        $this->addItem($item);

        $item = new ilTextInputGUI($this->lng->txt('ext_id'), 'ext_id');
        $item->setValue($this->obj_orgu->getImportId());
        $this->addItem($item);

        $this->addCommandButton('updateSettings', $this->lng->txt('save'));
    }

    /**
     * Check validity of form and pass values from form to object
     *
     * @return bool
     */
    protected function fillObject()
    {
        $this->setValuesByPost();
        if (!$this->checkInput()) {
            return false;
        }
        $this->obj_orgu->setOrgUnitTypeId($this->getInput('orgu_type'));
        $this->obj_orgu->setImportId($this->getInput('ext_id'));
        $this->obj_orgu->setTitle($this->getInput('title'));
        $this->obj_orgu->setDescription($this->getInput('description'));
        return true;
    }

    /**
     * Update title and description for the default language of translation
     */
    protected function updateTranslation()
    {
        $translations = $this->obj_orgu->getTranslations();
        $lang_code_default = '';
        $lang_codes = array();
        foreach ($translations['Fobject'] as $translation) {
            if ($translation['lang_default']) {
                $lang_code_default = $translation['lang'];
            }
            $lang_codes[] = $translation['lang'];
        }
        $lang_code = (in_array($this->user->getLanguage(), $lang_codes)) ? $this->user->getLanguage() : $lang_code_default;
        $this->obj_orgu->updateTranslation($this->getInput('title'), $this->getInput('description'), $lang_code, 0);
    }
}
