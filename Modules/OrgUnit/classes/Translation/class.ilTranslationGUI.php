<?php
/* Copyright (c) 1998-2013 ILIAS open source, Extended GPL, see docs/LICENSE */

/**
 * Class ilTranslationGUI
 *
 * Based on methods of ilObjCategoryGUI
 *
 * @author            Oskar Truffer <ot@studer-raimann.ch>
 * @author            Martin Studer <ms@studer-raimann.ch>
 *
 */
class ilTranslationGUI
{

    /**
     * @var ilCtrl
     */
    protected $ctrl;
    /**
     * @var ilTemplate
     */
    public $tpl;
    /**
     * @var ilAccessHandler
     */
    protected $ilAccess;
    /**
     * @var ilLanguage
     */
    protected $lng;
    /**
     * @var ilObjOrgUnitGui
     */
    protected $ilObjOrgUnitGui;
    /**
     * @var ilObjOrgUnit
     */
    protected $ilObjectOrgUnit;


    public function __construct(ilObjOrgUnitGUI $ilObjOrgUnitGUI)
    {
        global $DIC;
        $main_tpl = $DIC->ui()->mainTemplate();
        $tpl = $DIC['tpl'];
        $ilCtrl = $DIC['ilCtrl'];
        $ilDB = $DIC['ilDB'];
        $lng = $DIC['lng'];
        $ilAccess = $DIC['ilAccess'];
        /**
         * @var $tpl    ilTemplate
         * @var $ilCtrl ilCtrl
         * @var $ilDB   ilDB
         */
        $this->tpl = $tpl;
        $this->ctrl = $ilCtrl;
        $this->lng = $lng;
        $this->ilObjOrgUnitGui = $ilObjOrgUnitGUI;
        $this->ilObjectOrgUnit = $ilObjOrgUnitGUI->object;
        $this->ilAccess = $ilAccess;

        if (!$ilAccess->checkAccess('write', '', $this->ilObjectOrgUnit->getRefId())) {
            $main_tpl->setOnScreenMessage('failure', $this->lng->txt("permission_denied"), true);
            $this->ctrl->redirect($this->parent_gui, "");
        }
    }


    public function executeCommand()
    {
        $cmd = $this->ctrl->getCmd();
        $this->$cmd();
    }


    public function editTranslations($a_get_post_values = false, $a_add = false)
    {
        $this->lng->loadLanguageModule($this->ilObjectOrgUnit->getType());

        $table = new ilObjectTranslationTableGUI(
            $this,
            "editTranslations",
            true,
            "Translation"
        );
        if ($a_get_post_values) {
            $vals = array();
            foreach ($_POST["title"] as $k => $v) {
                $vals[] = array(
                    "title" => $v,
                    "desc" => $_POST["desc"][$k],
                    "lang" => $_POST["lang"][$k],
                    "default" => ($_POST["default"] == $k),
                );
            }
            $table->setData($vals);
        } else {
            $data = $this->ilObjectOrgUnit->getTranslations();
            if ($a_add) {
                $data[]["title"] = "";
            }
            $table->setData($data);
        }
        $this->tpl->setContent($table->getHTML());
    }


    /**
     * Save title and translations
     */
    public function saveTranslations()
    {
        // default language set?
        if (!isset($_POST["default"])) {
            $this->tpl->setOnScreenMessage('failure', $this->lng->txt("msg_no_default_language"));

            return $this->editTranslations(true);
        }

        // all languages set?
        if (array_key_exists("", $_POST["lang"])) {
            $this->tpl->setOnScreenMessage('failure', $this->lng->txt("msg_no_language_selected"));

            return $this->editTranslations(true);
        }

        // no single language is selected more than once?
        if (count(array_unique($_POST["lang"])) < count($_POST["lang"])) {
            $this->tpl->setOnScreenMessage('failure', $this->lng->txt("msg_multi_language_selected"));

            return $this->editTranslations(true);
        }

        // save the stuff
        $this->ilObjectOrgUnit->removeTranslations();
        foreach ($_POST["title"] as $k => $v) {
            $translations = $this->ilObjectOrgUnit->getTranslations();

            if (array_key_exists($_POST["lang"][$k], $translations)) {
                $this->ilObjectOrgUnit->updateTranslation(
                    ilUtil::stripSlashes($v),
                    ilUtil::stripSlashes($_POST["desc"][$k]),
                    ilUtil::stripSlashes($_POST["lang"][$k]),
                    ($_POST["default"] == $k)?1:0
                );
            } else {
                $this->ilObjectOrgUnit->addTranslation(
                    ilUtil::stripSlashes($v),
                    ilUtil::stripSlashes($_POST["desc"][$k]),
                    ilUtil::stripSlashes($_POST["lang"][$k]),
                    ($_POST["default"] == $k)?1:0
                );
            }
        }

        $this->tpl->setOnScreenMessage('success', $this->lng->txt("msg_obj_modified"), true);
        $this->ctrl->redirect($this, "editTranslations");
    }


    /**
     * Add a translation
     */
    public function addTranslation()
    {
        if ($_POST["title"]) {
            $k = max(array_keys($_POST["title"]));
            $k++;
            $_POST["title"][$k] = "";
            $this->editTranslations(true);
        } else {
            $this->editTranslations(false, true);
        }
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
                    $this->tpl->setOnScreenMessage('failure', $this->lng->txt("msg_no_default_language"));

                    return $this->editTranslations();
                }
            }
        }
        $this->saveTranslations();
    }
}
