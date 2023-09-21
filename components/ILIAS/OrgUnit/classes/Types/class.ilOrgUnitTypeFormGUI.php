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
 * Class ilOrgUnitTypeFormGUI
 * @author Stefan Wanzenried <sw@studer-raimann.ch>
 */
class ilOrgUnitTypeFormGUI extends ilPropertyFormGUI
{
    protected ilOrgUnitType $type;
    //protected ilObjectGUI $parent_gui;
    protected ilOrgUnitTypeGUI $parent_gui;

    public function __construct(ilOrgUnitTypeGUI $parent_gui, ilOrgUnitType $type)
    {
        global $DIC;
        $this->parent_gui = $parent_gui;
        $this->type = $type;
        //$this->tpl =  $DIC->ui()->mainTemplate();
        $this->global_tpl =  $DIC->ui()->mainTemplate();
        $this->ctrl = $DIC->ctrl();
        $this->lng = $DIC->language();
        $this->lng->loadLanguageModule('meta');
        $this->initForm();
        $this->http = $DIC->http();
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
        $title = $this->type->getId() ? $this->lng->txt('orgu_type_edit') : $this->lng->txt('orgu_type_add');
        $this->setTitle($title);
        $item = new ilSelectInputGUI($this->lng->txt('default_language'), 'default_lang');
        $item->setValue($this->type->getDefaultLang());
        $languages = $this->lng->getInstalledLanguages();
        $options = array();
        foreach ($languages as $lang_code) {
            $options[$lang_code] = $this->lng->txt("meta_l_{$lang_code}");
        }
        $item->setOptions($options);
        $item->setRequired(true);
        $this->addItem($item);

        foreach ($languages as $lang_code) {
            $this->addTranslationInputs($lang_code);
        }

        if ($this->type->getId()) {
            $this->addCommandButton('update', $this->lng->txt('save'));
        } else {
            $this->addCommandButton('create', $this->lng->txt('create'));
        }
    }

    /**
     * Check validity of form and pass values from form to object
     */
    private function fillObject(): bool
    {
        $this->setValuesByPost();
        if (!$this->checkInput()) {
            return false;
        }

        $success = true;
        try {
            $this->type->setDefaultLang($this->getInput('default_lang'));
            foreach ($this->lng->getInstalledLanguages() as $lang_code) {
                $title = $this->getInput("title_{$lang_code}");
                $description = $this->getInput("description_{$lang_code}");
                $this->type->setTitle($title, $lang_code);
                $this->type->setDescription($description, $lang_code);
            }
        } catch (ilOrgUnitTypePluginException $e) {
            $this->global_tpl->setOnScreenMessage('failure', $e->getMessage());
            $success = false;
        }

        return $success;
    }

    /**
     * Add a text and textarea input per language
     */
    private function addTranslationInputs(string $a_lang_code): void
    {
        $a_lang_code = $a_lang_code ?? '';
        $section = new ilFormSectionHeaderGUI();
        $section->setTitle($this->lng->txt("meta_l_{$a_lang_code}"));
        $this->addItem($section);
        $item = new ilTextInputGUI($this->lng->txt('title'), "title_{$a_lang_code}");
        $item->setValue($this->type->getTitle($a_lang_code));
        $this->addItem($item);
        $item = new ilTextAreaInputGUI($this->lng->txt('description'), "description_{$a_lang_code}");
        $desc = $this->type->getDescription($a_lang_code) ?? '';
        $item->setValue($desc);
        $this->addItem($item);
    }
}
