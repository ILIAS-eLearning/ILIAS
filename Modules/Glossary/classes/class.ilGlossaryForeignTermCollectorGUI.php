<?php

/**
 * This file is part of ILIAS, a powerful learning management system
 * published by ILIAS open source e-Learning e.V.
 * ILIAS is licensed with the GPL-3.0,
 * see https://www.gnu.org/licenses/gpl-3.0.en.html
 * You should have received a copy of said license along with the
 * source code, too.
 * If this is not the case or you just want to try ILIAS, you'll find
 * us at:
 * https://www.ilias.de
 * https://github.com/ILIAS-eLearning
 */

/**
 * Collects terms (reference or copy) from other glossaries
 * @author Alexander Killing <killing@leifos.de>
 */
class ilGlossaryForeignTermCollectorGUI
{
    protected ilObjGlossaryGUI $glossary_gui;
    protected ilObjGlossary $glossary;
    protected int $fglo_ref_id;
    protected ilCtrl $ctrl;
    protected ilLanguage $lng;
    protected ilGlobalTemplateInterface $tpl;
    protected ilObjUser $user;

    protected function __construct(
        ilObjGlossaryGUI $a_glossary_gui
    ) {
        global $DIC;

        $this->tpl = $DIC["tpl"];
        $this->ctrl = $DIC->ctrl();
        $this->lng = $DIC->language();
        $this->user = $DIC->user();

        $this->glossary_gui = $a_glossary_gui;
        /** @var ilObjGlossary $glossary */
        $glossary = $a_glossary_gui->object;
        $this->glossary = $glossary;

        $this->fglo_ref_id = (int) $_GET["fglo_ref_id"];
        if ($this->fglo_ref_id > 0 && ilObject::_lookupType($this->fglo_ref_id, true) == "glo") {
            $this->foreign_glossary = new ilObjGlossary($this->fglo_ref_id, true);
        }

        $this->ctrl->saveParameter($this, "fglo_ref_id");
    }

    public static function getInstance(ilObjGlossaryGUI $a_glossary_gui) : self
    {
        return new self($a_glossary_gui);
    }

    public function executeCommand() : void
    {
        $next_class = $this->ctrl->getNextClass($this);
        $cmd = $this->ctrl->getCmd("showGlossarySelector");

        switch ($next_class) {
            default:
                if (in_array($cmd, array("showGlossarySelector", "setForeignGlossary", "showTerms", "copyTerms", "referenceTerms"))) {
                    $this->$cmd();
                }
        }
    }

    public function showGlossarySelector() : void
    {
        ilUtil::sendInfo($this->lng->txt("glo_select_source_glo"));
        $exp = new ilRepositorySelectorExplorerGUI(
            $this,
            "showGlossarySelector",
            $this,
            "setForeignGlossary",
            "fglo_ref_id"
        );
        $exp->setTypeWhiteList(array("root", "cat", "grp", "crs", "glo", "fold"));
        $exp->setClickableTypes(array("glo"));
        if (!$exp->handleCommand()) {
            $this->tpl->setContent($exp->getHTML());
        }
    }

    public function setForeignGlossary() : void
    {
        $ilCtrl = $this->ctrl;
        $lng = $this->lng;

        $ref_id = (int) $_GET["fglo_ref_id"];

        if ($ref_id == $this->glossary->getRefId()) {
            ilUtil::sendFailure($lng->txt("glo_please_select_other_glo"), true);
            $ilCtrl->redirect($this, "showGlossarySelector");
        }

        $ilCtrl->redirect($this, "showTerms");
    }

    public function showTerms() : void
    {
        $t = new ilGlossaryForeignTermTableGUI($this, "showTerms", $this->foreign_glossary);

        $this->tpl->setContent($t->getHTML());
    }
    
    public function copyTerms() : void
    {
        if (!is_array($_POST["term_id"])) {
            ilUtil::sendFailure($this->lng->txt("no_checkbox"), true);
            $this->ctrl->redirect($this, "showTerms");
        }
        $act = ilGlossaryAct::getInstance($this->glossary, $this->user);
        foreach ($_POST["term_id"] as $id) {
            $act->copyTerm($this->foreign_glossary, (int) $id);
        }
        ilUtil::sendSuccess($this->lng->txt("msg_obj_modified"), true);
        $this->ctrl->returnToParent($this);
    }

    public function referenceTerms() : void
    {
        if (!is_array($_POST["term_id"])) {
            ilUtil::sendFailure($this->lng->txt("no_checkbox"), true);
            $this->ctrl->redirect($this, "showTerms");
        }
        $act = ilGlossaryAct::getInstance($this->glossary, $this->user);
        $terms = array();
        foreach ($_POST["term_id"] as $id) {
            $terms[] = (int) $id;
        }
        $act->referenceTerms($this->foreign_glossary, $terms);
        
        ilUtil::sendSuccess($this->lng->txt("msg_obj_modified"), true);
        $this->ctrl->returnToParent($this);
    }
}
