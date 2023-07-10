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

/**
 * GUI class for object translation handling.
 * @author Alexander Killing <killing@leifos.de>
 * @ilCtrl_IsCalledBy ilMultilingualismeGUI: ilDidacticTemplateSettingsGUI
 */
class ilMultilingualismGUI
{
    protected \ILIAS\Multilingualism\StandardGUIRequest $request;
    protected ilLanguage $lng;
    protected ilCtrl $ctrl;
    protected ilGlobalTemplateInterface $tpl;
    protected ilToolbarGUI $toolbar;
    protected ilObjUser $user;
    protected ilMultilingualism $obj_trans;
    protected bool $title_descr_only = true;
    protected string $start_title = "";
    protected string $start_description = "";

    public function __construct(
        int $a_obj_id,
        string $a_type
    ) {
        global $DIC;

        $this->toolbar = $DIC->toolbar();
        $this->user = $DIC->user();
        $this->lng = $DIC->language();
        $this->lng->loadLanguageModule('obj');
        $this->ctrl = $DIC->ctrl();
        $this->tpl = $DIC->ui()->mainTemplate();

        $this->obj_trans = ilMultilingualism::getInstance($a_obj_id, $a_type);
        $this->request = new \ILIAS\Multilingualism\StandardGUIRequest(
            $DIC->http(),
            $DIC->refinery()
        );
    }

    /**
     * Set enable title/description only mode
     */
    public function setTitleDescrOnlyMode(bool $a_val): void
    {
        $this->title_descr_only = $a_val;
    }

    /**
     * Get enable title/description only mode
     */
    public function getTitleDescrOnlyMode(): bool
    {
        return $this->title_descr_only;
    }

    public function executeCommand(): void
    {
        $next_class = $this->ctrl->getNextClass($this);

        switch ($next_class) {
            default:
                $cmd = $this->ctrl->getCmd("listTranslations");
                if (in_array($cmd, array("listTranslations", "saveTranslations",
                    "addTranslation", "deleteTranslations", "activateContentMultilinguality",
                    "confirmRemoveLanguages", "removeLanguages", "confirmDeactivateContentMultiLang", "saveLanguages",
                    "saveContentTranslationActivation", "deactivateContentMultiLang", "addLanguages"))) {
                    $this->$cmd();
                }
                break;
        }
    }

    public function listTranslations(
        bool $a_get_post_values = false,
        bool $a_add = false
    ): void {
        $this->lng->loadLanguageModule("translation");


        $this->addToolbar();

        $titles = $this->request->getTitles();
        $langs = $this->request->getLanguages();
        $descs = $this->request->getDescriptions();
        $default = $this->request->getDefault();

        $table = new ilMultilingualismTableGUI(
            $this,
            "listTranslations",
            true,
            "Translation"
        );
        if ($a_get_post_values) {
            $vals = array();
            foreach ($titles as $k => $v) {
                $vals[] = array("title" => $v,
                    "desc" => $descs[$k],
                    "lang" => $langs[$k],
                    "default" => ($default == $k));
            }
            $table->setData($vals);
        } else {
            $k = 0;
            $data = $this->obj_trans->getLanguages();
            foreach ($data as $k => $v) {
                $data[$k]["default"] = $v["lang_default"];
                $data[$k]["desc"] = $v["description"];
                $data[$k]["lang"] = $v["lang_code"];
            }
            if ($a_add) {
                $data["Fobject"][++$k]["title"] = "";
            }
            $table->setData($data);
        }
        $this->tpl->setContent($table->getHTML());
    }

    public function addToolbar(): void
    {
        $ilToolbar = $this->toolbar;
        if ($this->getTitleDescrOnlyMode()) {
            $ilToolbar->addButton(
                $this->lng->txt("obj_add_languages"),
                $this->ctrl->getLinkTarget($this, "addLanguages")
            );
        }
    }

    public function saveTranslations(): void
    {
        $default = $this->request->getDefault();
        $langs = $this->request->getLanguages();
        $titles = $this->request->getTitles();
        $descs = $this->request->getDescriptions();

        // default language set?
        if ($default === "") {
            $this->tpl->setOnScreenMessage('failure', $this->lng->txt("msg_no_default_language"));
            $this->listTranslations(true);
            return;
        }

        // all languages set?
        if (array_key_exists("", $langs)) {
            $this->tpl->setOnScreenMessage('failure', $this->lng->txt("msg_no_language_selected"));
            $this->listTranslations(true);
            return;
        }

        // no single language is selected more than once?
        if (count(array_unique($langs)) < count($langs)) {
            $this->tpl->setOnScreenMessage('failure', $this->lng->txt("msg_multi_language_selected"));
            $this->listTranslations(true);
            return;
        }

        // save the stuff
        $this->obj_trans->setLanguages(array());

        foreach ($titles as $k => $v) {
            // update object data if default
            $is_default = ($default == $k);

            $this->obj_trans->addLanguage(
                $langs[$k],
                $v,
                $descs[$k],
                $is_default
            );
        }
        $this->obj_trans->save();

        $this->tpl->setOnScreenMessage('success', $this->lng->txt("msg_obj_modified"), true);
        $this->ctrl->redirect($this, "listTranslations");
    }

    public function deleteTranslations(): void
    {
        $default = $this->request->getDefault();
        $langs = $this->request->getLanguages();
        $titles = $this->request->getTitles();
        $descs = $this->request->getDescriptions();
        $check = $this->request->getCheck();

        foreach ($titles as $k => $v) {
            if ($check[$k]) {
                // default translation cannot be deleted
                if ($k != $default) {
                    unset($titles[$k], $descs[$k], $langs[$k]);
                } else {
                    $this->tpl->setOnScreenMessage('failure', $this->lng->txt("msg_no_default_language"));
                    $this->listTranslations();
                    return;
                }
            }
        }
        $this->saveTranslations();
    }

    ////
    //// Content translation
    ////

    /**
     * Get multi language form
     */
    public function getMultiLangForm(bool $a_add = false): ilPropertyFormGUI
    {
        $lng = $this->lng;
        $ilCtrl = $this->ctrl;
        $ilUser = $this->user;

        $form = new ilPropertyFormGUI();

        // master language
        if (!$a_add) {
            $options = ilMDLanguageItem::_getLanguages();
            $si = new ilSelectInputGUI($lng->txt("obj_master_lang"), "master_lang");
            $si->setOptions($options);
            $si->setValue($ilUser->getLanguage());
            $form->addItem($si);
        }

        // additional languages
        if ($a_add) {
            $options = ilMDLanguageItem::_getLanguages();
            $options = array("" => $lng->txt("please_select")) + $options;
            $si = new ilSelectInputGUI($lng->txt("obj_additional_langs"), "additional_langs");
            $si->setOptions($options);
            $si->setMulti(true);
            $form->addItem($si);
        }

        if ($a_add) {
            $form->setTitle($lng->txt("obj_add_languages"));
            $form->addCommandButton("saveLanguages", $lng->txt("save"));
        } else {
            if ($this->getTitleDescrOnlyMode()) {
                $form->setTitle($lng->txt("obj_activate_content_lang"));
            } else {
                $form->setTitle($lng->txt("obj_activate_multilang"));
            }
            $form->addCommandButton("saveContentTranslationActivation", $lng->txt("save"));
        }
        $form->addCommandButton("listTranslations", $lng->txt("cancel"));
        $form->setFormAction($ilCtrl->getFormAction($this));

        return $form;
    }

    /**
     * Confirm page translation creation
     */
    public function confirmDeactivateContentMultiLang(): void
    {
        $ilCtrl = $this->ctrl;
        $tpl = $this->tpl;
        $lng = $this->lng;

        $cgui = new ilConfirmationGUI();
        $cgui->setFormAction($ilCtrl->getFormAction($this));
        if ($this->getTitleDescrOnlyMode()) {
            $cgui->setHeaderText($lng->txt("obj_deactivate_content_transl_conf"));
        } else {
            $cgui->setHeaderText($lng->txt("obj_deactivate_multilang_conf"));
        }

        $cgui->setCancel($lng->txt("cancel"), "listTranslations");
        $cgui->setConfirm($lng->txt("confirm"), "deactivateContentMultiLang");
        $tpl->setContent($cgui->getHTML());
    }

    /**
     * Add language
     */
    public function addLanguages(ilPropertyFormGUI $form = null): void
    {
        $tpl = $this->tpl;

        if (!$form instanceof ilPropertyFormGUI) {
            $form = $this->getMultiLangForm(true);
        }
        $tpl->setContent($form->getHTML());
    }

    public function saveLanguages(): void
    {
        $ilCtrl = $this->ctrl;
        $lng = $this->lng;

        ilLoggerFactory::getLogger('otpl')->debug('Save languages');

        $form = $this->getMultiLangForm(true);
        if ($form->checkInput()) {
            $ad = $form->getInput("additional_langs");

            ilLoggerFactory::getLogger('otpl')->dump($ad);

            if (is_array($ad)) {
                foreach ($ad as $l) {
                    if ($l != "") {
                        $std = false;

                        //if no other language is set, set this one as standard
                        if (!count($this->obj_trans->getLanguages())) {
                            $std = true;
                        }

                        $this->obj_trans->addLanguage($l, $this->start_title, $this->start_description, $std);
                    }
                }
            }
            $this->obj_trans->save();
            $this->tpl->setOnScreenMessage('info', $lng->txt("msg_obj_modified"), true);
            $ilCtrl->redirect($this, "listTranslations");
        } else {
            $form->setValuesByPost();
            $this->tpl->setOnScreenMessage('failure', $lng->txt('err_check_input'));
            $this->addLanguages($form);
        }
    }

    /**
     * Confirm remove languages
     */
    public function confirmRemoveLanguages(): void
    {
        $ilCtrl = $this->ctrl;
        $tpl = $this->tpl;
        $lng = $this->lng;

        $lng->loadLanguageModule("meta");
        $langs = $this->request->getLanguages();

        if (count($langs) === 0) {
            $this->tpl->setOnScreenMessage('info', $lng->txt("no_checkbox"), true);
            $ilCtrl->redirect($this, "listTranslations");
        } else {
            $cgui = new ilConfirmationGUI();
            $cgui->setFormAction($ilCtrl->getFormAction($this));
            $cgui->setHeaderText($lng->txt("obj_conf_delete_lang"));
            $cgui->setCancel($lng->txt("cancel"), "listTranslations");
            $cgui->setConfirm($lng->txt("remove"), "removeLanguages");

            foreach ($langs as $i) {
                $cgui->addItem("lang[]", $i, $lng->txt("meta_l_" . $i));
            }

            $tpl->setContent($cgui->getHTML());
        }
    }

    /**
     * Remove languages
     */
    public function removeLanguages(): void
    {
        $lng = $this->lng;
        $ilCtrl = $this->ctrl;

        $post_langs = $this->request->getLanguages();

        if (count($post_langs) > 0) {
            $langs = $this->obj_trans->getLanguages();
            foreach ($langs as $k => $l) {
                if (in_array($l, $post_langs)) {
                    $this->obj_trans->removeLanguage($l);
                }
            }
            $this->obj_trans->save();
            $this->tpl->setOnScreenMessage('info', $lng->txt("msg_obj_modified"), true);
        }
        $ilCtrl->redirect($this, "listTranslations");
    }

    public function setStartValues(
        string $a_title,
        string $a_description
    ): void {
        $this->start_title = $a_title;
        $this->start_description = $a_description;
    }
}
