<?php

use ILIAS\Modules\OrgUnit\ARHelper\DIC;

/**
 * Class ilMMItemTranslationGUI
 *
 * @author Fabian Schmid <fs@studer-raimann.ch>
 */
class ilMMItemTranslationGUI
{
    use DIC;
    const P_TRANSLATIONS = 'translations';
    const P_DELETE = 'delete';
    const CMD_ADD_LANGUAGE = "addLanguages";
    const CMD_SAVE_LANGUAGES = "saveLanguages";
    const CMD_SAVE_TRANSLATIONS = "saveTranslations";
    const CMD_DELETE_TRANSLATIONS = "deleteTranslations";
    const CMD_DEFAULT = 'index';
    const IDENTIFIER = 'identifier';
    /**
     * @var ilMMItemRepository
     */
    private $repository;
    /**
     * @var ilMMItemFacadeInterface
     */
    private $item_facade;


    /**
     * ilMMItemTranslationGUI constructor.
     *
     * @param ilMMItemFacadeInterface $item_facade
     */
    public function __construct(ilMMItemFacadeInterface $item_facade, ilMMItemRepository $repository)
    {
        $this->item_facade = $item_facade;
        $this->repository = $repository;
        $this->lng()->loadLanguageModule("mme");
    }


    public function executeCommand()
    {
        $this->ctrl()->saveParameter($this, self::IDENTIFIER);
        switch ($this->ctrl()->getNextClass()) {
            default:
                $cmd = $this->ctrl()->getCmd(self::CMD_DEFAULT);
                $this->{$cmd}();
        }
    }


    protected function index()
    {
        $this->initToolbar();

        $table = new ilMMItemTranslationTableGUI($this, $this->item_facade);
        $this->tpl()->setContent($table->getHTML());
    }


    protected function initToolbar()
    {
        $this->toolbar()->addButton(
            $this->lng()->txt("add_languages"),
            $this->ctrl()
            ->getLinkTarget($this, self::CMD_ADD_LANGUAGE)
        );
    }


    protected function saveTranslations()
    {
        $to_translate = (array) $this->http()->request()->getParsedBody()[self::P_TRANSLATIONS];
        foreach ($to_translate as $id => $data) {
            /**
             * @var $translation ilMMItemTranslationStorage
             */
            $translation = ilMMItemTranslationStorage::find($id);
            $translation->setTranslation($data['translation']);
            $translation->update();
        }
        $this->repository->clearCache();
        ilUtil::sendInfo($this->lng()->txt('msg_translations_saved'), true);
        $this->cancel();
    }


    protected function deleteTranslations()
    {
        $to_delete = (array) $this->http()->request()->getParsedBody()[self::P_DELETE];
        foreach ($to_delete as $id) {
            ilMMItemTranslationStorage::find($id)->delete();
        }
        $this->repository->updateItem($this->item_facade);
        ilUtil::sendInfo($this->lng()->txt('msg_translations_deleted'), true);
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
                    ilMMItemTranslationStorage::storeTranslation($this->item_facade->identification(), $language_key, "");
                }
            }
            $this->repository->updateItem($this->item_facade);
            ilUtil::sendInfo($this->lng()->txt("msg_languages_added"), true);
            $this->cancel();
        }

        ilUtil::sendFailure($this->lng()->txt('err_check_input'));
        $form->setValuesByPost();
        $this->tpl()->setContent($form->getHTML());
    }


    /**
     * @return ilPropertyFormGUI
     * @throws ilFormException
     */
    protected function getLanguagesForm()
    {
        $form = new ilPropertyFormGUI();
        $form->setFormAction($this->ctrl()->getFormAction($this));

        // additional languages
        $options = ilMDLanguageItem::_getLanguages();
        $options = array("" => $this->lng()->txt("please_select")) + $options;
        $si = new ilSelectInputGUI($this->lng()->txt("additional_langs"), "additional_langs");
        $si->setOptions($options);
        $si->setMulti(true);
        $form->addItem($si);

        $form->setTitle($this->lng()->txt("add_languages"));
        $form->addCommandButton(self::CMD_SAVE_LANGUAGES, $this->lng()->txt("save"));
        $form->addCommandButton(self::CMD_DEFAULT, $this->lng()->txt("cancel"));

        return $form;
    }


    protected function cancel()
    {
        $this->ctrl()->redirect($this, self::CMD_DEFAULT);
    }
}
