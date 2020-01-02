<?php
/*
    +-----------------------------------------------------------------------------+
    | ILIAS open source                                                           |
    +-----------------------------------------------------------------------------+
    | Copyright (c) 1998-2008 ILIAS open source, University of Cologne            |
    |                                                                             |
    | This program is free software; you can redistribute it and/or               |
    | modify it under the terms of the GNU General Public License                 |
    | as published by the Free Software Foundation; either version 2              |
    | of the License, or (at your option) any later version.                      |
    |                                                                             |
    | This program is distributed in the hope that it will be useful,             |
    | but WITHOUT ANY WARRANTY; without even the implied warranty of              |
    | MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the               |
    | GNU General Public License for more details.                                |
    |                                                                             |
    | You should have received a copy of the GNU General Public License           |
    | along with this program; if not, write to the Free Software                 |
    | Foundation, Inc., 59 Temple Place - Suite 330, Boston, MA  02111-1307, USA. |
    +-----------------------------------------------------------------------------+
*/

include_once("Services/Table/classes/class.ilTable2GUI.php");

/**
* TableGUI class for (broken) links in learning module
*
* @author Alex Killing <alex.killing@gmx.de>
* @version $Id$
*
* @ingroup ModulesLearningModule
*/
class ilLinksTableGUI extends ilTable2GUI
{
    public function __construct(
        $a_parent_obj,
        $a_parent_cmd,
        $a_lm_id,
        $a_lm_type
    ) {
        global $DIC;

        $this->ctrl = $DIC->ctrl();
        $this->lng = $DIC->language();
        $ilCtrl = $DIC->ctrl();
        $lng = $DIC->language();
        
        parent::__construct($a_parent_obj, $a_parent_cmd);
        
        $this->addColumn($lng->txt("pg"), "", "");
        $this->addColumn($lng->txt("cont_internal_links"), "", "");
        $this->setEnableHeader(true);
        $this->setFormAction($ilCtrl->getFormAction($a_parent_obj));
        $this->setRowTemplate(
            "tpl.links_table_row.html",
            "Modules/LearningModule"
        );
        $this->lm_id = $a_lm_id;
        $this->lm_type = $a_lm_type;
        $this->getLinks();
        
        $this->setTitle($lng->txt("cont_internal_links"));
    }
    
    /**
    * Get pages incl. links
    */
    public function getLinks()
    {
        $pages = ilLMPageObject::getPagesWithLinksList($this->lm_id, $this->lm_type);
        $this->setData($pages);
    }
    
    /**
    * Standard Version of Fill Row. Most likely to
    * be overwritten by derived class.
    */
    protected function fillRow($a_set)
    {
        $lng = $this->lng;
        $ilCtrl = $this->ctrl;

        $this->tpl->setVariable("TXT_PAGE_TITLE", $a_set["title"]);
        $ilCtrl->setParameterByClass(
            "illmpageobjectgui",
            "obj_id",
            $a_set["obj_id"]
        );
        $this->tpl->setVariable(
            "HREF_PAGE",
            $ilCtrl->getLinkTargetByClass("illmpageobjectgui", "edit")
        );
        
        include_once("./Modules/LearningModule/classes/class.ilLMPage.php");
        $page_object = new ilLMPage($a_set["obj_id"]);
        $page_object->buildDom();
        $int_links = $page_object->getInternalLinks();
        
        foreach ($int_links as $link) {
            $target = $link["Target"];
            if (substr($target, 0, 4) == "il__") {
                $target_arr = explode("_", $target);
                $target_id = $target_arr[count($target_arr) - 1];
                $type = $link["Type"];
                
                switch ($type) {
                    case "PageObject":
                        $this->tpl->setCurrentBlock("link");
                        $this->tpl->setVariable("TXT_LINK_TYPE", $lng->txt("pg"));
                        if (ilLMObject::_exists($target_id)) {
                            $lm_id = ilLMObject::_lookupContObjID($target_id);
                            $add_str = ($lm_id != $this->lm_id)
                                ? " (" . ilObject::_lookupTitle($lm_id) . ")"
                                : "";
                            $this->tpl->setVariable(
                                "TXT_LINK_TITLE",
                                ilLMObject::_lookupTitle($target_id) . $add_str
                            );
                        } else {
                            $this->tpl->setVariable(
                                "TXT_MISSING",
                                "<b>" . $lng->txt("cont_target_missing") . " [" . $target_id . "]" . "</b>"
                            );
                        }
                        $this->tpl->parseCurrentBlock();
                        break;
                        
                    case "StructureObject":
                        $this->tpl->setCurrentBlock("link");
                        $this->tpl->setVariable("TXT_LINK_TYPE", $lng->txt("st"));
                        if (ilLMObject::_exists($target_id)) {
                            $lm_id = ilLMObject::_lookupContObjID($target_id);
                            $add_str = ($lm_id != $this->lm_id)
                                ? " (" . ilObject::_lookupTitle($lm_id) . ")"
                                : "";
                            $this->tpl->setVariable(
                                "TXT_LINK_TITLE",
                                ilLMObject::_lookupTitle($target_id) . $add_str
                            );
                        } else {
                            $this->tpl->setVariable(
                                "TXT_MISSING",
                                "<b>" . $lng->txt("cont_target_missing") . " [" . $target_id . "]" . "</b>"
                            );
                        }
                        $this->tpl->parseCurrentBlock();
                        break;

                    case "GlossaryItem":
                        $this->tpl->setCurrentBlock("link");
                        $this->tpl->setVariable("TXT_LINK_TYPE", $lng->txt("cont_term"));
                        include_once("./Modules/Glossary/classes/class.ilGlossaryTerm.php");
                        if (ilGlossaryTerm::_exists($target_id)) {
                            $this->tpl->setVariable(
                                "TXT_LINK_TITLE",
                                ilGlossaryTerm::_lookGlossaryTerm($target_id)
                            );
                        } else {
                            $this->tpl->setVariable(
                                "TXT_MISSING",
                                "<b>" . $lng->txt("cont_target_missing") . " [" . $target_id . "]" . "</b>"
                            );
                        }
                        $this->tpl->parseCurrentBlock();
                        break;

                    case "MediaObject":
                        $this->tpl->setCurrentBlock("link");
                        $this->tpl->setVariable("TXT_LINK_TYPE", $lng->txt("mob"));
                        if (ilObject::_exists($target_id)) {
                            $this->tpl->setVariable(
                                "TXT_LINK_TITLE",
                                ilObject::_lookupTitle($target_id)
                            );
                        } else {
                            $this->tpl->setVariable(
                                "TXT_MISSING",
                                "<b>" . $lng->txt("cont_target_missing") . " [" . $target_id . "]" . "</b>"
                            );
                        }
                        $this->tpl->parseCurrentBlock();
                        break;

                    case "RepositoryItem":
                        $this->tpl->setCurrentBlock("link");
                        $this->tpl->setVariable("TXT_LINK_TYPE", $lng->txt("cont_repository_item"));
                        $obj_type = ilObject::_lookupType($target_id, true);
                        $obj_id = ilObject::_lookupObjId($target_id);
                        if (ilObject::_exists($obj_id)) {
                            $this->tpl->setVariable(
                                "TXT_LINK_TITLE",
                                ilObject::_lookupTitle($obj_id) . " (" .
                                $lng->txt(("obj_" . $obj_type))
                                . ")"
                            );
                        } else {
                            $this->tpl->setVariable(
                                "TXT_MISSING",
                                "<b>" . $lng->txt("cont_target_missing") . " [" . $target_id . "]" . "</b>"
                            );
                        }
                        $this->tpl->parseCurrentBlock();
                        break;

                }
            } else {
                $type = $link["Type"];
                
                switch ($type) {
                    case "PageObject":
                        $this->tpl->setVariable("TXT_LINK_TYPE", $lng->txt("pg"));
                        break;
                    case "StructureObject":
                        $this->tpl->setVariable("TXT_LINK_TYPE", $lng->txt("st"));
                        break;
                    case "GlossaryItem":
                        $this->tpl->setVariable("TXT_LINK_TYPE", $lng->txt("cont_term"));
                        break;
                    case "MediaObject":
                        $this->tpl->setVariable("TXT_LINK_TYPE", $lng->txt("mob"));
                        break;
                    case "RepositoryItem":
                        $this->tpl->setVariable("TXT_LINK_TYPE", $lng->txt("cont_repository_item"));
                        break;
                }
                
                $this->tpl->setCurrentBlock("link");
                $this->tpl->setVariable(
                    "TXT_MISSING",
                    "<b>" . $lng->txt("cont_target_missing") . " [" . $target . "]" . "</b>"
                );
                $this->tpl->parseCurrentBlock();
            }
        }
    }
}
