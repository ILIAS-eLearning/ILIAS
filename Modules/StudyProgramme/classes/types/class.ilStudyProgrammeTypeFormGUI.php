<?php
require_once('./Services/Form/classes/class.ilPropertyFormGUI.php');

/**
 * Class ilStudyProgrammeTypeFormGUI
 *
 * @author Stefan Wanzenried <sw@studer-raimann.ch>
 * @author Michael Herren <mh@studer-raimann.ch>
 */
class ilStudyProgrammeTypeFormGUI extends ilPropertyFormGUI
{

    /**
     * @var ilStudyProgrammeType
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


    public function __construct($parent_gui, ilStudyProgrammeType $type)
    {
        global $DIC;
        $tpl = $DIC['tpl'];
        $ilCtrl = $DIC['ilCtrl'];
        $lng = $DIC['lng'];
        $this->parent_gui = $parent_gui;
        $this->type = $type;
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
    public function saveObject()
    {
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
    protected function initForm()
    {
        $this->setFormAction($this->ctrl->getFormAction($this->parent_gui));
        $title = $this->type->getId() ? $this->lng->txt('prg_type_edit') : $this->lng->txt('prg_type_add');
        $this->setTitle($title);
        $item = new ilSelectInputGUI($this->lng->txt('default_language'), 'default_lang');
        $languages = $this->lng->getInstalledLanguages();
        $options = array();
        foreach ($languages as $lang_code) {
            $options[$lang_code] = $this->lng->txt("meta_l_{$lang_code}");
        }
        $item->setOptions($options);
        $type_default = $this->type->getDefaultLang();
        if (in_array($type_default, $languages)) {
            $item->setValue($type_default);
        } else {
            $item->setValue($this->lng->getDefaultLanguage());
        }
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
        $this->addCommandButton('cancel', $this->lng->txt('cancel'));
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

        $success = true;
        try {
            $this->type->setDefaultLang($this->getInput('default_lang'));
            foreach ($this->lng->getInstalledLanguages() as $lang_code) {
                $title = $this->getInput("title_{$lang_code}");
                $description = $this->getInput("description_{$lang_code}");
                $this->type->setTitle($title, $lang_code);
                $this->type->setDescription($description, $lang_code);
            }
        } catch (ilStudyProgrammeTypePluginException $e) {
            ilUtil::sendFailure($e->getMessage());
            $success = false;
        }
        return $success;
    }

    /**
     * Add a text and textarea input per language
     *
     * @param $a_lang_code
     */
    protected function addTranslationInputs($a_lang_code)
    {
        $section = new ilFormSectionHeaderGUI();
        $section->setTitle($this->lng->txt("meta_l_{$a_lang_code}"));
        $this->addItem($section);
        $item = new ilTextInputGUI($this->lng->txt('title'), "title_{$a_lang_code}");
        $item->setValue($this->type->getTitle($a_lang_code));
        $this->addItem($item);
        $item = new ilTextAreaInputGUI($this->lng->txt('description'), "description_{$a_lang_code}");
        $item->setValue($this->type->getDescription($a_lang_code));
        $this->addItem($item);
    }
}
