<?php

/* Copyright (c) 1998-2014 ILIAS open source, Extended GPL, see docs/LICENSE */

/**
 * GUI class for object translation handling.
 *
 * @author Alex Killing <alex.killing@gmx.de>
 * @version $Id$
 * @ingroup ServicesObject
 *
 * @ilCtrl_IsCalledBy ilTranslationGUI: ilDidacticTemplateSettingsGUI
 */
class ilMultilingualismGUI
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
    protected $start_title = "";
    protected $start_description = "";

    /**
     * ilTranslationGUI constructor.
     * @param int $a_obj_id
     * @param stirng $a_type
     */
    public function __construct($a_obj_id, $a_type)
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

        include_once("./Services/Multilingualism/classes/class.ilMultilingualism.php");
        $this->obj_trans = ilMultilingualism::getInstance($a_obj_id, $a_type);
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
        $this->lng->loadLanguageModule("translation");


        $this->addToolbar();

        include_once("./Services/Multilingualism/classes/class.ilMultilingualismTableGUI.php");
        $table = new ilMultilingualismTableGUI(
            $this,
            "listTranslations",
            true,
            "Translation"
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
            if ($a_add) {
                $data["Fobject"][++$k]["title"] = "";
            }
            $table->setData($data);
        }
        $this->tpl->setContent($table->getHTML());
    }

    public function addToolbar()
    {
        $ilToolbar = $this->toolbar;
        if ($this->getTitleDescrOnlyMode()) {
            $ilToolbar->addButton(
                $this->lng->txt("obj_add_languages"),
                $this->ctrl->getLinkTarget($this, "addLanguages")
            );
        }
    }

    /**
     * Save translations
     */
    public function saveTranslations()
    {
        // default language set?
        if (!isset($_POST["default"])) {
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

            $this->obj_trans->addLanguage(
                ilUtil::stripSlashes($_POST["lang"][$k]),
                ilUtil::stripSlashes($v),
                ilUtil::stripSlashes($_POST["desc"][$k]),
                $is_default
            );
        }
        $this->obj_trans->save();

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
     * Add language
     */
    public function addLanguages(ilPropertyFormGUI $form = null)
    {
        $tpl = $this->tpl;

        if (!$form instanceof ilPropertyFormGUI) {
            $form = $this->getMultiLangForm(true);
        }
        $tpl->setContent($form->getHTML());
    }

    /**
     * Save languages
     */
    public function saveLanguages()
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
            ilUtil::sendInfo($lng->txt("msg_obj_modified"), true);
            $ilCtrl->redirect($this, "listTranslations");
        } else {
            $form->setValuesByPost();
            ilUtil::sendFailure($lng->txt('err_check_input'));
            $this->addLanguages($form);
        }
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

    /**
     * @param string $a_title
     * @param string $a_description
     */
    public function setStartValues($a_title, $a_description)
    {
        $this->start_title = $a_title;
        $this->start_description = $a_description;
    }
}
