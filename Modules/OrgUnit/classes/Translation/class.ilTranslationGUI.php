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
 ********************************************************************
 */
/* Copyright (c) 1998-2013 ILIAS open source, Extended GPL, see docs/LICENSE */

/**
 * Class ilTranslationGUI
 * Based on methods of ilObjCategoryGUI
 * @author            Oskar Truffer <ot@studer-raimann.ch>
 * @author            Martin Studer <ms@studer-raimann.ch>
 */
class ilTranslationGUI
{
    protected ilCtrl $ctrl;
    public ilGlobalTemplateInterface $tpl;
    protected ilAccessHandler $ilAccess;
    protected ilLanguage $lng;
    protected object $parent_gui;
    protected object $object;

    public function __construct(object $ilObjOrgUnitGUI)
    {
        global $DIC;
        $main_tpl = $DIC->ui()->mainTemplate();

        $ilAccess = $DIC['ilAccess'];
        $this->tpl = $DIC->ui()->mainTemplate();
        $this->ctrl = $DIC->ctrl();
        $this->lng = $DIC->language();
        $this->parent_gui = $ilObjOrgUnitGUI;
        $this->object = $ilObjOrgUnitGUI->getObject();
        $this->ilAccess = $ilAccess;

        if (!$ilAccess->checkAccess('write', '', $this->object->getRefId())) {
            $this->tpl->setOnScreenMessage('failure', $this->lng->txt("permission_denied"), true);
            $this->ctrl->redirect($this->parent_gui, "");
        }
    }

    public function executeCommand(): void
    {
        $cmd = $this->ctrl->getCmd();
        $this->$cmd();
    }

    public function editTranslations(bool $a_get_post_values = false, bool $a_add = false): void
    {
        $this->lng->loadLanguageModule($this->object->getType());

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
            $data = $this->object->getTranslations();
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
    public function saveTranslations(): void
    {
        // default language set?
        if (!isset($_POST["default"])) {
            $this->tpl->setOnScreenMessage('failure', $this->lng->txt("msg_no_default_language"));
            $this->editTranslations(true);
        }

        // all languages set?
        if (array_key_exists("", $_POST["lang"])) {
            $this->tpl->setOnScreenMessage('failure', $this->lng->txt("msg_no_language_selected"));
            $this->editTranslations(true);
        }

        // no single language is selected more than once?
        if (count(array_unique($_POST["lang"])) < count($_POST["lang"])) {
            $this->tpl->setOnScreenMessage('failure', $this->lng->txt("msg_multi_language_selected"));

            $this->editTranslations(true);
        }

        // save the stuff
        $this->object->removeTranslations();
        foreach ($_POST["title"] as $k => $v) {
            $translations = $this->object->getTranslations();

            if (array_key_exists($_POST["lang"][$k], $translations)) {
                $this->object->updateTranslation(
                    ilUtil::stripSlashes($v),
                    ilUtil::stripSlashes($_POST["desc"][$k]),
                    ilUtil::stripSlashes($_POST["lang"][$k]),
                    ($_POST["default"] == $k) ? 1 : 0
                );
            } else {
                $this->object->addTranslation(
                    ilUtil::stripSlashes($v),
                    ilUtil::stripSlashes($_POST["desc"][$k]),
                    ilUtil::stripSlashes($_POST["lang"][$k]),
                    ($_POST["default"] == $k) ? 1 : 0
                );
            }
        }

        $this->tpl->setOnScreenMessage('success', $this->lng->txt("msg_obj_modified"), true);
        $this->ctrl->redirect($this, "editTranslations");
    }

    /**
     * Add a translation
     */
    public function addTranslation(): void
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
    public function deleteTranslations(): void
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

                    $this->editTranslations();
                }
            }
        }
        $this->saveTranslations();
    }
}
