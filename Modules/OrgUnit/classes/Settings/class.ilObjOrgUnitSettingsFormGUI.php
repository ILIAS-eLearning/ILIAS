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
 ********************************************************************
 */

/**
 * Class ilObjOrgUnitSettingsFormGUI
 * @author Stefan Wanzenried <sw@studer-raimann.ch>
 */
class ilObjOrgUnitSettingsFormGUI extends ilPropertyFormGUI
{
    protected ilObjOrgUnit $obj_orgu;
    protected ilObjectGUI $parent_gui;

    public function __construct(ilObjectGUI $parent_gui, ilObjOrgUnit $obj_orgu)
    {
        global $DIC;

        parent::__construct();
        $tpl = $DIC['tpl'];
        $ilCtrl = $DIC['ilCtrl'];
        $lng = $DIC['lng'];
        $ilUser = $DIC['ilUser'];
        $this->parent_gui = $parent_gui;
        $this->obj_orgu = $obj_orgu;

        //$this->tpl = $tpl;
        $this->ctrl = $ilCtrl;
        $this->lng = $lng;
        $this->user = $ilUser;
        $this->initForm();
    }

    /**
     * Update object
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
    private function initForm() : void
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
     * @return bool
     */
    private function fillObject() : bool
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
    private function updateTranslation() : void
    {
        $translations = $this->obj_orgu->getTranslations();
        $lang_code_default = '';
        $lang_codes = array();
        foreach ($translations as $translation) {
            if ($translation['default']) {
                $lang_code_default = $translation['lang'];
            }
            $lang_codes[] = $translation['lang'];
        }
        $lang_code = (in_array($this->user->getLanguage(), $lang_codes, true)) ? $this->user->getLanguage() : $lang_code_default;
        $this->obj_orgu->updateTranslation($this->getInput('title'), $this->getInput('description'), $lang_code, 0);
    }
}
