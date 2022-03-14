<?php declare(strict_types=1);

/* Copyright (c) 1998-2021 ILIAS open source, GPLv3, see LICENSE */

/**
 * GUI class for object translation handling.
 *
 * @author Alex Killing <alex.killing@gmx.de>
 */
class ilObjectTranslationGUI
{
    protected ilToolbarGUI $toolbar;
    protected ilObjUser $user;
    protected ilLanguage $lng;
    protected ilCtrl $ctrl;
    protected ilGlobalTemplateInterface $tpl;
    protected ILIAS\HTTP\Wrapper\ArrayBasedRequestWrapper $post_wrapper;
    protected ILIAS\Refinery\Factory $refinery;

    protected ilObjectGUI $obj_gui;
    protected ilObject $obj;
    protected ilObjectTranslation $obj_trans;

    protected bool $title_descr_only = true;
    protected bool $fallback_lang_mode = true;

    public function __construct($obj_gui)
    {
        global $DIC;

        $this->toolbar = $DIC->toolbar();
        $this->user = $DIC->user();
        $this->lng = $DIC->language();
        $this->ctrl = $DIC->ctrl();
        $this->tpl = $DIC["tpl"];
        $this->post_wrapper = $DIC->http()->wrapper()->post();
        $this->refinery = $DIC->refinery();


        $this->obj_gui = $obj_gui;
        $this->obj = $obj_gui->getObject();

        $this->obj_trans = ilObjectTranslation::getInstance($this->obj->getId());
    }

    public function setTitleDescrOnlyMode(bool $val) : void
    {
        $this->title_descr_only = $val;
    }

    public function getTitleDescrOnlyMode() : bool
    {
        return $this->title_descr_only;
    }

    public function setEnableFallbackLanguage(bool $val) : void
    {
        $this->fallback_lang_mode = $val;
    }

    public function getEnableFallbackLanguage() : bool
    {
        return $this->fallback_lang_mode;
    }
    
    public function executeCommand() : void
    {
        $commands = [
            "listTranslations",
            "saveTranslations",
            "addTranslation",
            "deleteTranslations",
            "activateContentMultilinguality",
            "confirmRemoveLanguages",
            "removeLanguages",
            "confirmDeactivateContentMultiLang",
            "saveLanguages",
            "saveContentTranslationActivation",
            "deactivateContentMultiLang",
            "addLanguages",
            "setFallback"
        ];

        $this->ctrl->getNextClass($this);
        $cmd = $this->ctrl->getCmd("listTranslations");
        if (in_array($cmd, $commands)) {
            $this->$cmd();
        }
    }

    public function listTranslations(bool $get_post_values = false, bool $add = false) : void
    {
        $this->lng->loadLanguageModule(ilObject::_lookupType($this->obj->getId()));

        if ($this->getTitleDescrOnlyMode() || $this->obj_trans->getContentActivated()) {
            $this->toolbar->addButton(
                $this->lng->txt("obj_add_languages"),
                $this->ctrl->getLinkTarget($this, "addLanguages")
            );
        }

        if ($this->getTitleDescrOnlyMode()) {
            if (!$this->obj_trans->getContentActivated()) {
                $this->tpl->setOnScreenMessage('info', $this->lng->txt("obj_multilang_title_descr_only"));
                $this->toolbar->addButton(
                    $this->lng->txt("obj_activate_content_lang"),
                    $this->ctrl->getLinkTarget($this, "activateContentMultilinguality")
                );
            } else {
                $this->toolbar->addButton(
                    $this->lng->txt("obj_deactivate_content_lang"),
                    $this->ctrl->getLinkTarget($this, "confirmDeactivateContentMultiLang")
                );
            }
        } else {
            if ($this->obj_trans->getContentActivated()) {
                $this->toolbar->addButton(
                    $this->lng->txt("obj_deactivate_multilang"),
                    $this->ctrl->getLinkTarget($this, "confirmDeactivateContentMultiLang")
                );
            } else {
                $this->toolbar->addButton(
                    $this->lng->txt("obj_activate_multilang"),
                    $this->ctrl->getLinkTarget($this, "activateContentMultilinguality")
                );
                return;
            }
        }

        $table = new ilObjectTranslation2TableGUI(
            $this,
            "listTranslations",
            true,
            "Translation",
            $this->obj_trans->getMasterLanguage(),
            $this->fallback_lang_mode,
            $this->obj_trans->getFallbackLanguage()
        );
        if ($get_post_values) {
            $vals = [];
            foreach ($_POST["title"] as $k => $v) {
                $vals[] = [
                    "title" => $v,
                    "desc" => $_POST["desc"][$k],
                    "lang" => $_POST["lang"][$k],
                    "default" => ($_POST["default"] == $k)
                ];
            }
            $table->setData($vals);
        } else {
            $data = $this->obj_trans->getLanguages();
            foreach ($data as $k => $v) {
                $data[$k]["default"] = $v["lang_default"];
                $data[$k]["desc"] = $v["description"];
                $data[$k]["lang"] = $v["lang_code"];
            }
            $table->setData($data);
        }
        $this->tpl->setContent($table->getHTML());
    }

    public function saveTranslations() : void
    {
        // default language set?
        if (!isset($_POST["default"]) && $this->obj_trans->getMasterLanguage() == "") {
            $this->tpl->setOnScreenMessage('failure', $this->lng->txt("msg_no_default_language"));
            $this->listTranslations(true);
            return;
        }

        // all languages set?
        if (array_key_exists("", $_POST["lang"])) {
            $this->tpl->setOnScreenMessage('failure', $this->lng->txt("msg_no_language_selected"));
            $this->listTranslations(true);
            return;
        }

        // no single language is selected more than once?
        if (count(array_unique($_POST["lang"])) < count($_POST["lang"])) {
            $this->tpl->setOnScreenMessage('failure', $this->lng->txt("msg_multi_language_selected"));
            $this->listTranslations(true);
            return;
        }

        // save the stuff
        $this->obj_trans->setLanguages(array());

        foreach ($_POST["title"] as $k => $v) {
            // update object data if default
            $is_default = ($_POST["default"] == $k);

            // ensure master language is set as default
            if ($this->obj_trans->getMasterLanguage() != "") {
                $is_default = ($this->obj_trans->getMasterLanguage() == $_POST["lang"][$k]);
            }
            if ($is_default) {
                $this->obj->setTitle(ilUtil::stripSlashes($v));
                $this->obj->setDescription(ilUtil::stripSlashes($_POST["desc"][$k]));
            }

            $this->obj_trans->addLanguage(
                ilUtil::stripSlashes($_POST["lang"][$k]),
                ilUtil::stripSlashes($v),
                ilUtil::stripSlashes($_POST["desc"][$k]),
                $is_default
            );
        }
        $this->obj_trans->save();
        if (method_exists($this->obj, "setObjectTranslation")) {
            $this->obj->setObjectTranslation($this->obj_trans);
        }
        $this->obj->update();

        $this->tpl->setOnScreenMessage('success', $this->lng->txt("msg_obj_modified"), true);
        $this->ctrl->redirect($this, "listTranslations");
    }

    public function deleteTranslations() : void
    {
        $titles = $this->post_wrapper->retrieve(
            "title",
            $this->refinery->kindlyTo()->dictOf($this->refinery->kindlyTo()->string())
        );
        foreach ($titles as $k => $v) {
            $check = $this->post_wrapper->retrieve(
                "check",
                $this->refinery->kindlyTo()->dictOf($this->refinery->kindlyTo()->string())
            );
            if ($check[$k]) {
                // default translation cannot be deleted
                if (
                    !$this->post_wrapper->has("default") ||
                    $k != $this->post_wrapper->retrieve("default", $this->refinery->kindlyTo()->string())
                ) {
                    unset($_POST["title"][$k]);
                    unset($_POST["desc"][$k]);
                    unset($_POST["lang"][$k]);
                } else {
                    $this->tpl->setOnScreenMessage('failure', $this->lng->txt("msg_no_default_language"));
                    $this->listTranslations();
                    return;
                }
            }
        }
        $this->saveTranslations();
    }

    /**
     * Activate multi language (-> master language selection)
     */
    public function activateContentMultilinguality() : void
    {
        $this->tpl->setOnScreenMessage('info', $this->lng->txt("obj_select_master_lang"));
        $form = $this->getMultiLangForm();
        $this->tpl->setContent($form->getHTML());
    }

    public function getMultiLangForm(bool $add = false) : ilPropertyFormGUI
    {
        $form = new ilPropertyFormGUI();

        // master language
        if (!$add) {
            $options = ilMDLanguageItem::_getLanguages();
            $si = new ilSelectInputGUI($this->lng->txt("obj_master_lang"), "master_lang");
            $si->setOptions($options);
            $si->setValue($this->user->getLanguage());
            $form->addItem($si);
        }

        // additional languages
        if ($add) {
            $options = ilMDLanguageItem::_getLanguages();
            $options = array("" => $this->lng->txt("please_select")) + $options;
            $si = new ilSelectInputGUI($this->lng->txt("obj_additional_langs"), "additional_langs");
            $si->setOptions($options);
            $si->setMulti(true);
            $form->addItem($si);
        }

        if ($add) {
            $form->setTitle($this->lng->txt("obj_add_languages"));
            $form->addCommandButton("saveLanguages", $this->lng->txt("save"));
        } else {
            if ($this->getTitleDescrOnlyMode()) {
                $form->setTitle($this->lng->txt("obj_activate_content_lang"));
            } else {
                $form->setTitle($this->lng->txt("obj_activate_multilang"));
            }
            $form->addCommandButton("saveContentTranslationActivation", $this->lng->txt("save"));
        }
        $form->addCommandButton("listTranslations", $this->lng->txt("cancel"));
        $form->setFormAction($this->ctrl->getFormAction($this));

        return $form;
    }

    public function saveContentTranslationActivation() : void
    {
        $form = $this->getMultiLangForm();
        if ($form->checkInput()) {
            $ml = $form->getInput("master_lang");
            $this->obj_trans->setMasterLanguage($ml);
            $this->obj_trans->addLanguage(
                $ml,
                $this->obj->getTitle(),
                $this->obj->getDescription(),
                true
            );
            $this->obj_trans->setDefaultTitle($this->obj->getTitle());
            $this->obj_trans->setDefaultDescription($this->obj->getDescription());
            $this->obj_trans->save();
        }

        $this->ctrl->redirect($this, "listTranslations");
    }

    public function confirmDeactivateContentMultiLang() : void
    {
        $cgui = new ilConfirmationGUI();
        $cgui->setFormAction($this->ctrl->getFormAction($this));
        if ($this->getTitleDescrOnlyMode()) {
            $cgui->setHeaderText($this->lng->txt("obj_deactivate_content_transl_conf"));
        } else {
            $cgui->setHeaderText($this->lng->txt("obj_deactivate_multilang_conf"));
        }

        $cgui->setCancel($this->lng->txt("cancel"), "listTranslations");
        $cgui->setConfirm($this->lng->txt("confirm"), "deactivateContentMultiLang");
        $this->tpl->setContent($cgui->getHTML());
    }

    public function deactivateContentMultiLang() : void
    {
        if (!$this->getTitleDescrOnlyMode()) {
            $this->obj_trans->setMasterLanguage("");
            $this->obj_trans->setLanguages(array());
            $this->obj_trans->save();
        }
        $this->obj_trans->deactivateContentTranslation();
        if ($this->getTitleDescrOnlyMode()) {
            $this->tpl->setOnScreenMessage('success', $this->lng->txt("obj_cont_transl_deactivated"), true);
        } else {
            $this->tpl->setOnScreenMessage('success', $this->lng->txt("obj_multilang_deactivated"), true);
        }

        $this->ctrl->redirect($this, "listTranslations");
    }

    public function addLanguages() : void
    {
        $form = $this->getMultiLangForm(true);
        $this->tpl->setContent($form->getHTML());
    }

    public function saveLanguages() : void
    {
        $form = $this->getMultiLangForm(true);
        if ($form->checkInput()) {
            $ad = $form->getInput("additional_langs");
            if (is_array($ad)) {
                $ml = $this->obj_trans->getMasterLanguage();
                foreach ($ad as $l) {
                    if ($l != $ml && $l != "") {
                        $this->obj_trans->addLanguage($l, "", "", false);
                    }
                }
            }
            $this->obj_trans->save();
            $this->tpl->setOnScreenMessage('info', $this->lng->txt("msg_obj_modified"), true);
            $this->ctrl->redirect($this, "listTranslations");
        }
        
        $this->tpl->setOnScreenMessage('failure', $this->lng->txt('err_check_input'));
        $form->setValuesByPost();
        $this->tpl->setContent($form->getHTML());
    }

    public function confirmRemoveLanguages() : void
    {
        $this->lng->loadLanguageModule("meta");

        if (!is_array($_POST["lang"]) || count($_POST["lang"]) == 0) {
            $this->tpl->setOnScreenMessage('info', $this->lng->txt("no_checkbox"), true);
            $this->ctrl->redirect($this, "listTranslations");
        } else {
            $cgui = new ilConfirmationGUI();
            $cgui->setFormAction($this->ctrl->getFormAction($this));
            $cgui->setHeaderText($this->lng->txt("obj_conf_delete_lang"));
            $cgui->setCancel($this->lng->txt("cancel"), "listTranslations");
            $cgui->setConfirm($this->lng->txt("remove"), "removeLanguages");

            foreach ($_POST["lang"] as $i) {
                $cgui->addItem("lang[]", $i, $this->lng->txt("meta_l_" . $i));
            }

            $this->tpl->setContent($cgui->getHTML());
        }
    }

    public function removeLanguages() : void
    {
        die("test");
        if (is_array($_POST["lang"])) {
            $langs = $this->obj_trans->getLanguages();
            foreach ($langs as $l) {
                if (in_array($l, $_POST["lang"])) {
                    $this->obj_trans->removeLanguage($l);
                }
            }
            $this->obj_trans->save();
            $this->tpl->setOnScreenMessage('info', $this->lng->txt("msg_obj_modified"), true);
        }
        $this->ctrl->redirect($this, "listTranslations");
    }

    public function setFallback() : void
    {
        // default language set?
        if (!isset($_POST["check"]) || count($_POST["check"]) !== 1) {
            $this->tpl->setOnScreenMessage('failure', $this->lng->txt("obj_select_one_language"));
            $this->listTranslations(true);
            return;
        }

        $fallback_lang = $_POST["lang"][key($_POST["check"])];
        if ($fallback_lang != $this->obj_trans->getFallbackLanguage()) {
            $this->obj_trans->setFallbackLanguage($fallback_lang);
        } else {
            $this->obj_trans->setFallbackLanguage("");
        }
        $this->obj_trans->save();
        $this->tpl->setOnScreenMessage('info', $this->lng->txt("msg_obj_modified"), true);
        $this->ctrl->redirect($this, "listTranslations");
    }
}
