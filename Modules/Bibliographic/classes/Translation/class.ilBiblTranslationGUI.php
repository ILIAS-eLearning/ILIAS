<?php

use ILIAS\Modules\OrgUnit\ARHelper\DIC;

/**
 * Class ilBiblTranslationGUI
 *
 * @author Benjamin Seglias   <bs@studer-raimann.ch>
 * @author Fabian Schmid <fs@studer-raimann.ch>
 */
class ilBiblTranslationGUI
{
    use DIC;
    const P_TRANSLATIONS = 'translations';
    const P_DELETE = 'delete';
    const CMD_ADD_LANGUAGE = "addLanguages";
    const CMD_SAVE_LANGUAGES = "saveLanguages";
    const CMD_SAVE_TRANSLATIONS = "saveTranslations";
    const CMD_DELETE_TRANSLATIONS = "deleteTranslations";
    const CMD_DEFAULT = 'index';
    /**
     * @var \ilBiblAdminFactoryFacadeInterface
     */
    protected $facade;
    /**
     * @var \ilBiblFieldInterface
     */
    protected $field;


    /**
     * ilBiblTranslationGUI constructor.
     *
     * @param \ilBiblAdminFactoryFacadeInterface $facade
     * @param \ilBiblFieldInterface              $field
     */
    public function __construct(ilBiblAdminFactoryFacadeInterface $facade, \ilBiblFieldInterface $field)
    {
        $this->facade = $facade;
        $this->field = $field;
    }


    public function executeCommand()
    {
        $this->ctrl()->saveParameter($this, ilBiblAdminFieldGUI::FIELD_IDENTIFIER);
        switch ($this->ctrl()->getNextClass()) {
            default:
                $cmd = $this->ctrl()->getCmd(self::CMD_DEFAULT);
                $this->{$cmd}();
        }
    }


    protected function index()
    {
        $this->initToolbar();

        $table = new ilBiblTranslationTableGUI($this, $this->field, $this->facade->translationFactory());
        $this->tpl()->setContent($table->getHTML());
    }


    protected function initToolbar()
    {
        $this->toolbar()->addButton($this->lng()->txt("obj_add_languages"), $this->ctrl()
                                                                                 ->getLinkTarget($this, self::CMD_ADD_LANGUAGE));
    }


    protected function saveTranslations()
    {
        $to_translate = (array) $this->http()->request()->getParsedBody()[self::P_TRANSLATIONS];
        foreach ($to_translate as $id => $data) {
            $translation = $this->facade->translationFactory()->findById($id);
            $translation->setTranslation($data['translation']);
            $translation->setDescription($data['description']);
            $translation->store();
        }
        ilUtil::sendInfo($this->lng()->txt('bibl_msg_translations_saved'), true);
        $this->cancel();
    }


    protected function deleteTranslations()
    {
        $to_delete = (array) $this->http()->request()->getParsedBody()[self::P_DELETE];
        foreach ($to_delete as $id) {
            $this->facade->translationFactory()->deleteById($id);
        }
        ilUtil::sendInfo($this->lng()->txt('bibl_msg_translations_deleted'), true);
        $this->cancel();
    }


    protected function addLanguages()
    {
        $form = $this->getLanguagesForm();

        $this->tpl()->setContent($form->getHTML());
    }


    protected function saveLanguages()
    {
        $form = $this->getLanguagesForm();
        if ($form->checkInput()) {
            $ad = $form->getInput("additional_langs");
            if (is_array($ad)) {
                foreach ($ad as $language_key) {
                    $this->facade->translationFactory()
                                 ->findArCreateInstanceForFieldAndlanguage($this->field, $language_key);
                }
            }
            ilUtil::sendInfo($this->lng()->txt("msg_obj_modified"), true);
            $this->cancel();
        }

        ilUtil::sendFailure($this->lng()->txt('err_check_input'));
        $form->setValuesByPost();
        $this->tpl()->setContent($form->getHTML());
    }


    /**
     * @return \ilPropertyFormGUI
     */
    protected function getLanguagesForm()
    {
        $form = new ilPropertyFormGUI();
        $form->setFormAction($this->ctrl()->getFormAction($this));

        // master language
        //		$options = ilMDLanguageItem::_getLanguages();
        //		$si = new ilSelectInputGUI($this->lng()->txt("obj_master_lang"), "master_lang");
        //		$si->setOptions($options);
        //		$si->setValue($this->user()->getLanguage());
        //		$form->addItem($si);

        // additional languages
        $options = ilMDLanguageItem::_getLanguages();
        $options = array( "" => $this->lng()->txt("please_select") ) + $options;
        $si = new ilSelectInputGUI($this->lng()->txt("obj_additional_langs"), "additional_langs");
        $si->setOptions($options);
        $si->setMulti(true);
        $form->addItem($si);

        $form->setTitle($this->lng()->txt("obj_add_languages"));
        $form->addCommandButton(self::CMD_SAVE_LANGUAGES, $this->lng()->txt("save"));
        $form->addCommandButton(self::CMD_DEFAULT, $this->lng()->txt("cancel"));

        return $form;
    }


    protected function cancel()
    {
        $this->ctrl()->redirect($this, self::CMD_DEFAULT);
    }
}
