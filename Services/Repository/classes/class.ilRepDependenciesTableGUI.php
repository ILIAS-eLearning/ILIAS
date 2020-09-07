<?php

/* Copyright (c) 1998-2009 ILIAS open source, Extended GPL, see docs/LICENSE */

include_once("./Services/Table/classes/class.ilTable2GUI.php");

/**
 * name table
 *
 * @author Alex Killing <alex.killing@gmx.de>
 * @version $Id$
 *
 * @ingroup Services
 */
class ilRepDependenciesTableGUI extends ilTable2GUI
{
    /**
     * @var ilCtrl
     */
    protected $ctrl;

    /**
     * @var ilAccessHandler
     */
    protected $access;

    
    /**
    * Constructor
    */
    public function __construct($a_deps)
    {
        global $DIC;

        $this->ctrl = $DIC->ctrl();
        $this->lng = $DIC->language();
        $this->access = $DIC->access();
        $ilCtrl = $DIC->ctrl();
        $lng = $DIC->language();
        $ilAccess = $DIC->access();
        $lng = $DIC->language();
        
        parent::__construct(null, "");
        $lng->loadLanguageModule("rep");

        $this->setTitle($lng->txt("rep_dependencies"));
        $this->setLimit(9999);
        
        $this->addColumn($this->lng->txt("rep_object_to_delete"));
        $this->addColumn($this->lng->txt("rep_dependent_object"));
        $this->addColumn($this->lng->txt("rep_dependency"));
        
        $this->setEnableHeader(true);
        //$this->setFormAction($ilCtrl->getFormAction($a_parent_obj));
        $this->setRowTemplate("tpl.rep_dep_row.html", "Services/Repository");
        $this->disable("footer");
        $this->setEnableTitle(true);

        $deps = array();
        foreach ($a_deps as $id => $d) {
            foreach ($d as $id2 => $ms) {
                foreach ($ms as $m) {
                    $deps[] = array("dep_obj" => $id2, "del_obj" => $id, "message" => $m);
                }
            }
        }
        $this->setData($deps);
    }
    
    /**
     * Fill table row
     */
    protected function fillRow($a_set)
    {
        $lng = $this->lng;
        $this->tpl->setVariable(
            "TXT_DEP_OBJ",
            $lng->txt("obj_" . ilObject::_lookupType($a_set["dep_obj"])) . ": " . ilObject::_lookupTitle($a_set["dep_obj"])
        );
        $this->tpl->setVariable(
            "TXT_DEL_OBJ",
            $lng->txt("obj_" . ilObject::_lookupType($a_set["del_obj"])) . ": " . ilObject::_lookupTitle($a_set["del_obj"])
        );
        $this->tpl->setVariable("TXT_MESS", $a_set["message"]);
    }
}
