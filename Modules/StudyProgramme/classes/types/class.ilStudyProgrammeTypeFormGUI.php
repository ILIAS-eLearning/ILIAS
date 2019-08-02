<?php
require_once('./Services/Form/classes/class.ilPropertyFormGUI.php');

/**
 * Class ilStudyProgrammeTypeFormGUI
 *
 * @author Stefan Wanzenried <sw@studer-raimann.ch>
 * @author Michael Herren <mh@studer-raimann.ch>
 */
class ilStudyProgrammeTypeFormGUI extends ilPropertyFormGUI {

    /**
     * @var ilStudyProgrammeType
     */
    protected $type_repository;

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


    public function __construct($parent_gui, ilStudyProgrammeTypeRepository $type_repository) {
        global $DIC;
        $tpl = $DIC['tpl'];
        $ilCtrl = $DIC['ilCtrl'];
        $lng = $DIC['lng'];
        $this->parent_gui = $parent_gui;
        $this->type_repository = $type_repository;
        $this->tpl = $tpl;
        $this->ctrl = $ilCtrl;
        $this->lng = $lng;
        $this->lng->loadLanguageModule('meta');
        $this->lng->loadLanguageModule('prg');
	    
        $this->initForm();
    }


    /**
     * Save object (create or update)
     *
     * @return bool
     */
    public function saveObject(ilStudyProgrammeType $type) {
        if (!$this->fillObject($type)) {
            return false;
        }
        try {
            $this->type_repository->updateType($type);
            return true;
        } catch (ilException $e) {
            ilUtil::sendFailure($e->getMessage());
            return false;
        }
    }

    protected function initForm()
    {
        $this->setFormAction($this->ctrl->getFormAction($this->parent_gui));
        $item = new ilSelectInputGUI($this->lng->txt('default_language'), 'default_lang');
        $languages = $this->lng->getInstalledLanguages();
        $options = array();
        foreach ($languages as $lang_code) {
            $options[$lang_code] = $this->lng->txt("meta_l_{$lang_code}");
        }
        $item->setOptions($options);
        $item->setRequired(true);
        $this->addItem($item);
    }

    /**
     * Add all fields to the form
     */
    public function fillFormUpdate(ilStudyProgrammeType $type)
    {
        $title = $this->lng->txt('prg_type_edit');
        $this->setTitle($title);
        $languages = $this->lng->getInstalledLanguages();
        $type_default = $type->getDefaultLang();
        $item = $this->getItemByPostVar('default_lang');
        if(in_array($type_default, $languages)) {
            $item->setValue($type_default);
        } else {
            $item->setValue($this->lng->getDefaultLanguage());
        }

        foreach ($languages as $lang_code) {
            $this->addTranslationInputs($lang_code, $type);
        }

        $this->addCommandButton('update', $this->lng->txt('save'));
        $this->addCommandButton('cancel', $this->lng->txt('cancel'));
    }


    public function fillFormCreate()
    {
        $title = $this->lng->txt('prg_type_add');
        $this->setTitle($title);
        $languages = $this->lng->getInstalledLanguages();
        $item = $this->getItemByPostVar('default_lang');
        $item->setValue($this->lng->getDefaultLanguage());

        foreach ($languages as $lang_code) {
            $this->addTranslationInputs($lang_code);
        }

        $this->addCommandButton('create', $this->lng->txt('save'));
        $this->addCommandButton('cancel', $this->lng->txt('cancel'));
    }

    /**
     * Check validity of form and pass values from form to object
     *
     * @return bool
     */
    public function fillObject(ilStudyProgrammeType $type) {
        $this->setValuesByPost();
        if (!$this->checkInput()) {
            return null;
        }

        try {
            $type->setDefaultLang($this->getInput('default_lang'));
            foreach ($this->lng->getInstalledLanguages() as $lang_code) {
                $title = $this->getInput("title_{$lang_code}");
                $description = $this->getInput("description_{$lang_code}");
                $type->setTitle($title, $lang_code);
                $type->setDescription($description, $lang_code);
            }
        } catch (ilStudyProgrammeTypePluginException $e) {
            ilUtil::sendFailure($e->getMessage());
            return null;
        }
        return $type;
    }

    /**
     * Add a text and textarea input per language
     *
     * @param $a_lang_code
     */
    protected function addTranslationInputs($a_lang_code, ilStudyProgrammeType $type = null) {
        $section = new ilFormSectionHeaderGUI();
        $section->setTitle($this->lng->txt("meta_l_{$a_lang_code}"));
        $this->addItem($section);
        $item = new ilTextInputGUI($this->lng->txt('title'), "title_{$a_lang_code}");
        $item->setValue($type ? $type->getTitle($a_lang_code) : '');
        $this->addItem($item);
        $item = new ilTextAreaInputGUI($this->lng->txt('description'), "description_{$a_lang_code}");
        $item->setValue($type ? $type->getDescription($a_lang_code) : '');
        $this->addItem($item);
    }


}