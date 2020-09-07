<?php

/* Copyright (c) 1998-2016 ILIAS open source, Extended GPL, see docs/LICENSE */

include_once("./Services/Table/classes/class.ilTable2GUI.php");

/**
 * TableGUI class for collecting foreign terms
 *
 * @author Alex Killing <alex.killing@gmx.de>
 * @version $Id$
 *
 * @ingroup ModulesGlossary
 */
class ilGlossaryForeignTermTableGUI extends ilTable2GUI
{
    /**
     * @var ilCtrl
     */
    protected $ctrl;

    /**
     * @var ilLanguage
     */
    protected $lng;

    /**
     * @var ilObjGlossary
     */
    protected $glossary;

    /**
     * ilGlossaryForeignTermTableGUI constructor.
     *
     * @param object $a_parent_obj
     * @param string $a_parent_cmd
     * @param ilObjGlossary $a_glossary
     */
    public function __construct($a_parent_obj, $a_parent_cmd, ilObjGlossary $a_glossary)
    {
        global $DIC;

        $this->lng = $DIC->language();
        $this->ctrl = $DIC->ctrl();
        $this->glossary = $a_glossary;
        
        parent::__construct($a_parent_obj, $a_parent_cmd);
        $terms = $this->glossary->getTermList();

        $this->setData($terms);
        $this->setTitle($this->glossary->getTitle() . ": " . $this->lng->txt("glo_select_terms"));
        
        $this->addColumn("", "", "1");
        $this->addColumn($this->lng->txt("glo_term"));
        
        $this->setFormAction($this->ctrl->getFormAction($a_parent_obj));
        $this->setRowTemplate("tpl.glo_foreign_term_row.html", "Modules/Glossary");

        $this->addMultiCommand("copyTerms", $this->lng->txt("glo_copy_terms"));
        $this->addMultiCommand("referenceTerms", $this->lng->txt("glo_reference_terms"));

        //$this->addCommandButton("", $lng->txt(""));
    }
    
    /**
     * Fill table row
     */
    protected function fillRow($a_set)
    {
        $this->tpl->setVariable("TERM", $a_set["term"]);
        $this->tpl->setVariable("TERM_ID", $a_set["id"]);
    }
}
