<?php

/* Copyright (c) 1998-2017 ILIAS open source, Extended GPL, see docs/LICENSE */

include_once("./Services/Table/classes/class.ilTable2GUI.php");

/**
 * TableGUI class for lm short titles
 *
 * @author Alex Killing <killing@leifos.de>
 *
 * @ingroup ModulesLearningModule
 */
class ilLMEditShortTitlesTableGUI extends ilTable2GUI
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
     * @var ilTemplate
     */
    protected $tpl;

    /**
     * @var ilLanguage
     */
    protected $lng;

    /**
     * Constructor
     */
    public function __construct($a_parent_obj, $a_parent_cmd, $a_lm, $a_lang)
    {
        global $DIC;

        $this->ctrl = $DIC->ctrl();
        $this->lm = $a_lm;
        $this->tpl = $DIC["tpl"];
        $this->lng = $DIC->language();
        $this->lang = $a_lang;

        $this->setId("lm_short_title");

        parent::__construct($a_parent_obj, $a_parent_cmd);
        include_once("./Modules/LearningModule/classes/class.ilLMObject.php");
        $this->setData(ilLMObject::getShortTitles($this->lm->getId(), $this->lang));
        $this->setTitle($this->lng->txt("cont_short_titles"));
        
        $this->addColumn($this->lng->txt("title"));
        $this->addColumn($this->lng->txt("cont_short_title"));
        
        $this->setFormAction($this->ctrl->getFormAction($a_parent_obj));
        $this->setRowTemplate("tpl.short_title_row.html", "Modules/LearningModule");

        $this->addCommandButton("save", $this->lng->txt("save"));
        //$this->setMaxCount(9999);
    }
    
    /**
     * Fill table row
     */
    protected function fillRow($a_set)
    {
        $this->tpl->setVariable("TITLE", $a_set["title"]);
        $this->tpl->setVariable("DEFAULT_TITLE", $a_set["default_title"]);
        $this->tpl->setVariable("DEFAULT_SHORT_TITLE", $a_set["default_short_title"]);
        $this->tpl->setVariable("ID", $a_set["obj_id"]);
        $this->tpl->setVariable("SHORT_TITLE", ilUtil::prepareFormOutput($a_set["short_title"]));
    }
}
