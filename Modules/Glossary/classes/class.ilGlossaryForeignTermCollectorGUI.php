<?php

/* Copyright (c) 1998-2016 ILIAS open source, Extended GPL, see docs/LICENSE */

/**
 * Collects terms (reference or copy) from other glossaries
 *
 * @author Alex Killing <alex.killing@gmx.de>
 * @version $Id$
 * @ingroup ModulesGlossary
 */
class ilGlossaryForeignTermCollectorGUI
{
    /**
     * @var ilObjGlossaryGUI
     */
    protected $glossary_gui;

    /**
     * @var ilObjGlossary
     */
    protected $glossary;

    /**
     * @var int ref id of foreign glossary
     */
    protected $fglo_ref_id;

    /**
     * @var ilCtrl
     */
    protected $ctrl;

    /**
     * @var ilLanguage
     */
    protected $lng;

    /**
     * @var ilTemplate
     */
    protected $tpl;

    /**
     * @var ilObjUser
     */
    protected $user;

    /**
     * Constructor
     *
     * @param ilObjGlossaryGUI $a_glossary_gui
     */
    protected function __construct(ilObjGlossaryGUI $a_glossary_gui)
    {
        global $DIC;

        $this->tpl = $DIC["tpl"];
        $this->ctrl = $DIC->ctrl();
        $this->lng = $DIC->language();
        $this->user = $DIC->user();

        $this->glossary_gui = $a_glossary_gui;
        $this->glossary = $a_glossary_gui->object;

        $this->fglo_ref_id = (int) $_GET["fglo_ref_id"];
        if ($this->fglo_ref_id > 0 && ilObject::_lookupType($this->fglo_ref_id, true) == "glo") {
            $this->foreign_glossary = new ilObjGlossary($this->fglo_ref_id, true);
        }

        $this->ctrl->saveParameter($this, "fglo_ref_id");
    }

    /**
     * Get instance
     *
     * @param ilObjGlossaryGUI $a_glossary_gui
     * @return ilGlossaryForeignTermCollectorGUI
     */
    public static function getInstance(ilObjGlossaryGUI $a_glossary_gui)
    {
        return new self($a_glossary_gui);
    }

    /**
     * Execute command
     */
    public function executeCommand()
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

    /**
     * Add level resource
     */
    public function showGlossarySelector()
    {
        ilUtil::sendInfo($this->lng->txt("glo_select_source_glo"));
        include_once("./Services/Repository/classes/class.ilRepositorySelectorExplorerGUI.php");
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

    /**
     * Save level resource
     */
    public function setForeignGlossary()
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

    /**
     * Show Terms
     *
     * @param
     * @return
     */
    public function showTerms()
    {
        include_once("./Modules/Glossary/classes/class.ilGlossaryForeignTermTableGUI.php");
        $t = new ilGlossaryForeignTermTableGUI($this, "showTerms", $this->foreign_glossary);

        $this->tpl->setContent($t->getHTML());
    }
    
    /**
     * Copy terms
     *
     * @param
     * @return
     */
    public function copyTerms()
    {
        if (!is_array($_POST["term_id"])) {
            ilUtil::sendFailure($this->lng->txt("no_checkbox"), true);
            $this->ctrl->redirect($this, "showTerms");
        }
        include_once("./Modules/Glossary/classes/class.ilGlossaryAct.php");
        $act = ilGlossaryAct::getInstance($this->glossary, $this->user);
        foreach ($_POST["term_id"] as $id) {
            $act->copyTerm($this->foreign_glossary, (int) $id);
        }
        ilUtil::sendSuccess($this->lng->txt("msg_obj_modified"), true);
        $this->ctrl->returnToParent($this);
    }

    /**
     * Reference terms
     *
     * @param
     * @return
     */
    public function referenceTerms()
    {
        if (!is_array($_POST["term_id"])) {
            ilUtil::sendFailure($this->lng->txt("no_checkbox"), true);
            $this->ctrl->redirect($this, "showTerms");
        }
        include_once("./Modules/Glossary/classes/class.ilGlossaryAct.php");
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
