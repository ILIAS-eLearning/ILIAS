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
 *********************************************************************/
 
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
    protected \ilBiblAdminFactoryFacadeInterface $facade;
    protected \ilBiblFieldInterface $field;
    private \ilGlobalTemplateInterface $main_tpl;


    /**
     * ilBiblTranslationGUI constructor.
     */
    public function __construct(ilBiblAdminFactoryFacadeInterface $facade, \ilBiblFieldInterface $field)
    {
        global $DIC;
        $this->main_tpl = $DIC->ui()->mainTemplate();
        $this->facade = $facade;
        $this->field = $field;
    }


    public function executeCommand() : void
    {
        $this->ctrl()->saveParameter($this, ilBiblAdminFieldGUI::FIELD_IDENTIFIER);
        switch ($this->ctrl()->getNextClass()) {
            default:
                $cmd = $this->ctrl()->getCmd(self::CMD_DEFAULT);
                $this->{$cmd}();
        }
    }


    protected function index() : void
    {
        $this->initToolbar();

        $table = new ilBiblTranslationTableGUI($this, $this->field, $this->facade->translationFactory());
        $this->tpl()->setContent($table->getHTML());
    }


    protected function initToolbar() : void
    {
        $this->toolbar()->addButton($this->lng()->txt("obj_add_languages"), $this->ctrl()
            ->getLinkTarget($this, self::CMD_ADD_LANGUAGE));
    }


    protected function saveTranslations() : void
    {
        $to_translate = (array) $this->http()->request()->getParsedBody()[self::P_TRANSLATIONS];
        foreach ($to_translate as $id => $data) {
            $translation = $this->facade->translationFactory()->findById($id);
            $translation->setTranslation($data['translation']);
            $translation->setDescription($data['description']);
            $translation->store();
        }
        $this->main_tpl->setOnScreenMessage('info', $this->lng()->txt('bibl_msg_translations_saved'), true);
        $this->cancel();
    }


    protected function deleteTranslations() : void
    {
        $to_delete = (array) $this->http()->request()->getParsedBody()[self::P_DELETE];
        foreach ($to_delete as $id) {
            $this->facade->translationFactory()->deleteById($id);
        }
        $this->main_tpl->setOnScreenMessage('info', $this->lng()->txt('bibl_msg_translations_deleted'), true);
        $this->cancel();
    }


    protected function addLanguages() : void
    {
        $form = $this->getLanguagesForm();

        $this->tpl()->setContent($form->getHTML());
    }


    protected function saveLanguages() : void
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
            $this->main_tpl->setOnScreenMessage('info', $this->lng()->txt("msg_obj_modified"), true);
            $this->cancel();
        }

        $this->main_tpl->setOnScreenMessage('failure', $this->lng()->txt('err_check_input'));
        $form->setValuesByPost();
        $this->tpl()->setContent($form->getHTML());
    }


    protected function getLanguagesForm() : \ilPropertyFormGUI
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
        $options = array("" => $this->lng()->txt("please_select")) + $options;
        $si = new ilSelectInputGUI($this->lng()->txt("obj_additional_langs"), "additional_langs");
        $si->setOptions($options);
        $si->setMulti(true);
        $form->addItem($si);

        $form->setTitle($this->lng()->txt("obj_add_languages"));
        $form->addCommandButton(self::CMD_SAVE_LANGUAGES, $this->lng()->txt("save"));
        $form->addCommandButton(self::CMD_DEFAULT, $this->lng()->txt("cancel"));

        return $form;
    }


    protected function cancel() : void
    {
        $this->ctrl()->redirect($this, self::CMD_DEFAULT);
    }
}
