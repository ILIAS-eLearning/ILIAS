<?php

/* Copyright (c) 1998-2017 ILIAS open source, Extended GPL, see docs/LICENSE */

class ilLMEditShortTitlesGUI
{
    /**
     * @var ilCtrl
     */
    protected $ctrl;

    /**
     * @var ilObjLearningModule
     */
    protected $lm;

    /**
     * @var ilObjLearningModuleGUI
     */
    protected $lm_gui;

    /**
     * @var ilTemplate
     */
    protected $tpl;

    /**
     * @var ilLanguage
     */
    protected $lng;

    /**
     * Learning module
     *
     * @param ilObjLearningModule $a_lm learning module
     */
    public function __construct(ilObjLearningModuleGUI $a_lm_gui)
    {
        global $DIC;

        $this->ctrl = $DIC->ctrl();
        $this->lm = $a_lm_gui->object;
        $this->lm_gui = $a_lm_gui;
        $this->tpl = $DIC["tpl"];
        $this->lng = $DIC->language();

        $this->lang = ($_GET["transl"] == "")
            ? "-"
            : $_GET["transl"];
    }

    /**
     * Execute command
     */
    public function executeCommand()
    {
        $next_class = $this->ctrl->getNextClass($this);
        $cmd = $this->ctrl->getCmd("listShortTitles");

        switch ($next_class) {
            default:
                if (in_array($cmd, array("listShortTitles", "save"))) {
                    $this->$cmd();
                }
        }
    }

    /**
     * List short titles
     */
    public function listShortTitles()
    {
        ilUtil::sendInfo($this->lng->txt("cont_short_title_info"));
        $ml_head = ilObjContentObjectGUI::getMultiLangHeader($this->lm->getId(), $this->lm_gui, "short_titles");
        include_once("./Modules/LearningModule/classes/class.ilLMEditShortTitlesTableGUI.php");
        $tab = new ilLMEditShortTitlesTableGUI($this, "listShortTitles", $this->lm, $this->lang);
        $this->tpl->setContent($ml_head . $tab->getHTML());
    }

    /**
     * Save short titles
     */
    public function save()
    {
        if (is_array($_POST["short_title"])) {
            foreach ($_POST["short_title"] as $id => $title) {
                if (ilLMObject::_lookupContObjID($id) == $this->lm->getId()) {
                    ilLMObject::writeShortTitle($id, ilUtil::stripSlashes($title), $this->lang);
                }
            }
        }
        ilUtil::sendSuccess($this->lng->txt("msg_obj_modified"), true);
        $this->ctrl->redirect($this, "listShortTitles");
    }
}
