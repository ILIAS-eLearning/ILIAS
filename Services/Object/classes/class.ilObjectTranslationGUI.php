<?php

/* Copyright (c) 1998-2014 ILIAS open source, Extended GPL, see docs/LICENSE */

/**
 * GUI class for object translation handling.
 *
 * @author Alex Killing <alex.killing@gmx.de>
 * @version $Id$
 * @ingroup ServicesObject
 */
class ilObjectTranslationGUI
{
    /**
     * @var ilLanguage
     */
    protected $lng;

    /**
     * @var ilCtrl
     */
    protected $ctrl;

    /**
     * @var ilTemplate
     */
    protected $tpl;

    /**
     * @var ilToolbarGUI
     */
    protected $toolbar;

    /**
     * @var ilObjUser
     */
    protected $user;

    protected $obj_trans;
    protected $title_descr_only = true;
    
    /**
     * Constructor
     */
    public function __construct($a_obj_gui)
    {
        global $DIC;

        $this->toolbar = $DIC->toolbar();
        $this->user = $DIC->user();
        $lng = $DIC->language();
        $ilCtrl = $DIC->ctrl();
        $tpl = $DIC["tpl"];

        $this->lng = $lng;
        $this->ctrl = $ilCtrl;
        $this->tpl = $tpl;
        $this->obj_gui = $a_obj_gui;
        $this->obj = $a_obj_gui->object;

        include_once("./Services/Object/classes/class.ilObjectTranslation.php");
        $this->obj_trans = ilObjectTranslation::getInstance($this->obj->getId());
    }

    /**
     * Set enable title/description only mode
     *
     * @param bool $a_val enable title/description only mode
     */
    public function setTitleDescrOnlyMode($a_val)
    {
        $this->title_descr_only = $a_val;
    }

    /**
     * Get enable title/description only mode
     *
     * @return bool enable title/description only mode
     */
    public function getTitleDescrOnlyMode()
    {
        return $this->title_descr_only;
    }

    /**
     * Execute command
     */
    public function executeCommand()
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

    /**
     * List translations
     */
    public function listTranslations($a_get_post_values = false, $a_add = false)
    {
        $ilToolbar = $this->toolbar;

        $this->lng->loadLanguageModule(ilObject::_lookupType($this->obj->getId()));


        if ($this->getTitleDescrOnlyMode() || $this->obj_trans->getContentActivated()) {
            $ilToolbar->addButton(
                $this->lng->txt("obj_add_languages"),
                $this->ctrl->getLinkTarget($this, "addLanguages")
            );
        }

        if ($this->getTitleDescrOnlyMode()) {
            if (!$this->obj_trans->getContentActivated()) {
                ilUtil::sendInfo($this->lng->txt("obj_multilang_title_descr_only"));
                $ilToolbar->addButton(
                    $this->lng->txt("obj_activate_content_lang"),
                    $this->ctrl->getLinkTarget($this, "activateContentMultilinguality")
                );
            } else {
                $ilToolbar->addButton(
                    $this->lng->txt("obj_deactivate_content_lang"),
                    $this->ctrl->getLinkTarget($this, "confirmDeactivateContentMultiLang")
                );
            }
        } else {
            if ($this->obj_trans->getContentActivated()) {
                $ilToolbar->addButton(
                    $this->lng->txt("obj_deactivate_multilang"),
                    $this->ctrl->getLinkTarget($this, "confirmDeactivateContentMultiLang")
                );
            } else {
                $ilToolbar->addButton(
                    $this->lng->txt("obj_activate_multilang"),
                    $this->ctrl->getLinkTarget($this, "activateContentMultilinguality")
                );
                return;
            }
        }

        include_once("./Services/Object/classes/class.ilObjectTranslation2TableGUI.php");
        $table = new ilObjectTranslation2TableGUI(
            $this,
            "listTranslations",
            true,
            "Translation",
            $this->obj_trans->getMasterLanguage()
        );
        if ($a_get_post_values) {
            $vals = array();
            foreach ($_POST["title"] as $k => $v) {
                $vals[] = array("title" => $v,
                    "desc" => $_POST["desc"][$k],
                    "lang" => $_POST["lang"][$k],
                    "default" => ($_POST["default"] == $k));
            }
            $table->setData($vals);
        } else {
            $data = $this->obj_trans->getLanguages();
            foreach ($data as $k => $v) {
                $data[$k]["default"] = $v["lang_default"];
                $data[$k]["desc"] = $v["description"];
                $data[$k]["lang"] = $v["lang_code"];
            }
            /*			if($a_add)
                        {
                            $data["Fobject"][++$k]["title"] = "";
                        }*/
            $table->setData($data);
        }
        $this->tpl->setContent($table->getHTML());
    }

    /**
     * Save translations
     */
    public function saveTranslations()
    {
        // default language set?
        if (!isset($_POST["default"]) && $this->obj_trans->getMasterLanguage() == "") {
            ilUtil::sendFailure($this->lng->txt("msg_no_default_language"));
            $this->listTranslations(true);
            return;
        }

        // all languages set?
        if (array_key_exists("", $_POST["lang"])) {
            ilUtil::sendFailure($this->lng->txt("msg_no_language_selected"));
            $this->listTranslations(true);
            return;
        }

        // no single language is selected more than once?
        if (count(array_unique($_POST["lang"])) < count($_POST["lang"])) {
            ilUtil::sendFailure($this->lng->txt("msg_multi_language_selected"));
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

        ilUtil::sendSuccess($this->lng->txt("msg_obj_modified"), true);
        $this->ctrl->redirect($this, "listTranslations");
    }

    /**
     * Remove translation
     */
    public function deleteTranslations()
    {
        foreach ($_POST["title"] as $k => $v) {
            if ($_POST["check"][$k]) {
                // default translation cannot be deleted
                if ($k != $_POST["default"]) {
                    unset($_POST["title"][$k]);
                    unset($_POST["desc"][$k]);
                    unset($_POST["lang"][$k]);
                } else {
                    ilUtil::sendFailure($this->lng->txt("msg_no_default_language"));
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
     * Activate multi language (-> master language selection)
     */
    public function activateContentMultilinguality()
    {
        $tpl = $this->tpl;
        $lng = $this->lng;

        ilUtil::sendInfo($lng->txt("obj_select_master_lang"));

        $form = $this->getMultiLangForm();
        $tpl->setContent($form->getHTML());
    }

    /**
     * Get multi language form
     */
    public function getMultiLangForm($a_add = false)
    {
        $tpl = $this->tpl;
        $lng = $this->lng;
        $ilCtrl = $this->ctrl;
        $ilUser = $this->user;

        include_once("Services/Form/classes/class.ilPropertyFormGUI.php");
        $form = new ilPropertyFormGUI();

        // master language
        if (!$a_add) {
            include_once("./Services/MetaData/classes/class.ilMDLanguageItem.php");
            $options = ilMDLanguageItem::_getLanguages();
            $si = new ilSelectInputGUI($lng->txt("obj_master_lang"), "master_lang");
            $si->setOptions($options);
            $si->setValue($ilUser->getLanguage());
            $form->addItem($si);
        }

        // additional languages
        if ($a_add) {
            include_once("./Services/MetaData/classes/class.ilMDLanguageItem.php");
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
            $form->addCommandButton("listTranslations", $lng->txt("cancel"));
        } else {
            if ($this->getTitleDescrOnlyMode()) {
                $form->setTitle($lng->txt("obj_activate_content_lang"));
            } else {
                $form->setTitle($lng->txt("obj_activate_multilang"));
            }
            $form->addCommandButton("saveContentTranslationActivation", $lng->txt("save"));
            $form->addCommandButton("listTranslations", $lng->txt("cancel"));
        }
        $form->setFormAction($ilCtrl->getFormAction($this));

        return $form;
    }

    /**
     * Save content translation activation
     */
    public function saveContentTranslationActivation()
    {
        $ilCtrl = $this->ctrl;

        //		include_once("./Services/COPage/classes/class.ilPageMultiLang.php");

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

        $ilCtrl->redirect($this, "listTranslations");
    }

    /**
     * Confirm page translation creation
     */
    public function confirmDeactivateContentMultiLang()
    {
        $ilCtrl = $this->ctrl;
        $tpl = $this->tpl;
        $lng = $this->lng;

        include_once("./Services/Utilities/classes/class.ilConfirmationGUI.php");
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
     * Deactivate multilanguage
     */
    public function deactivateContentMultiLang()
    {
        $lng = $this->lng;
        $ilCtrl = $this->ctrl;

        if (!$this->getTitleDescrOnlyMode()) {
            $this->obj_trans->setMasterLanguage("");
            $this->obj_trans->setLanguages(array());
            $this->obj_trans->save();
        }
        $this->obj_trans->deactivateContentTranslation();
        if ($this->getTitleDescrOnlyMode()) {
            ilUtil::sendSuccess($lng->txt("obj_cont_transl_deactivated"), true);
        } else {
            ilUtil::sendSuccess($lng->txt("obj_multilang_deactivated"), true);
        }


        $ilCtrl->redirect($this, "listTranslations");
    }

    /**
     * Add language
     */
    public function addLanguages()
    {
        $tpl = $this->tpl;

        $form = $this->getMultiLangForm(true);
        $tpl->setContent($form->getHTML());
    }

    /**
     * Save languages
     */
    public function saveLanguages()
    {
        $ilCtrl = $this->ctrl;
        $lng = $this->lng;
        $tpl = $this->tpl;

        $form = $this->getMultiLangForm(true);
        if ($form->checkInput()) {
            $ad = $form->getInput("additional_langs");
            if (is_array($ad)) {
                $ml = $this->obj_trans->getMasterLanguage();
                foreach ($ad as $l) {
                    if ($l != $ml && $l != "") {
                        $this->obj_trans->addLanguage($l, false, "", "");
                    }
                }
            }
            $this->obj_trans->save();
            ilUtil::sendInfo($lng->txt("msg_obj_modified"), true);
            $ilCtrl->redirect($this, "listTranslations");
        }
        
        ilUtil::sendFailure($this->lng->txt('err_check_input'));
        $form->setValuesByPost();
        $tpl->setContent($form->getHTML());
    }

    /**
     * Confirm remove languages
     */
    public function confirmRemoveLanguages()
    {
        $ilCtrl = $this->ctrl;
        $tpl = $this->tpl;
        $lng = $this->lng;

        $lng->loadLanguageModule("meta");

        if (!is_array($_POST["lang"]) || count($_POST["lang"]) == 0) {
            ilUtil::sendInfo($lng->txt("no_checkbox"), true);
            $ilCtrl->redirect($this, "listTranslations");
        } else {
            include_once("./Services/Utilities/classes/class.ilConfirmationGUI.php");
            $cgui = new ilConfirmationGUI();
            $cgui->setFormAction($ilCtrl->getFormAction($this));
            $cgui->setHeaderText($lng->txt("obj_conf_delete_lang"));
            $cgui->setCancel($lng->txt("cancel"), "listTranslations");
            $cgui->setConfirm($lng->txt("remove"), "removeLanguages");

            foreach ($_POST["lang"] as $i) {
                $cgui->addItem("lang[]", $i, $lng->txt("meta_l_" . $i));
            }

            $tpl->setContent($cgui->getHTML());
        }
    }

    /**
     * Remove languages
     */
    public function removeLanguages()
    {
        $lng = $this->lng;
        $ilCtrl = $this->ctrl;

        if (is_array($_POST["lang"])) {
            $langs = $this->obj_trans->getLanguages();
            foreach ($langs as $k => $l) {
                if (in_array($l, $_POST["lang"])) {
                    $this->obj_trans->removeLanguage();
                }
            }
            $this->obj_trans->save();
            ilUtil::sendInfo($lng->txt("msg_obj_modified"), true);
        }
        $ilCtrl->redirect($this, "listTranslations");
    }
}
