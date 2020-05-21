<?php
/* Copyright (c) 1998-2010 ILIAS open source, Extended GPL, see docs/LICENSE */

include_once("./Services/Table/classes/class.ilTable2GUI.php");

/**
* TableGUI class for survey constraints
*
* @author Jörg Lützenkirchen <luetzenkirchen@leifos.com>
* @version $Id: class.ilFoundUsersTableGUI.php 20638 2009-07-19 08:14:34Z hschottm $
*
* @ingroup ModulesSurvey
*/
class SurveyConstraintsTableGUI extends ilTable2GUI
{
    protected $read_only; // [bool]
    protected $structure; // [array]
    
    public function __construct($a_parent_obj, $a_parent_cmd = "", ilObjSurvey $a_survey, $a_read_only)
    {
        global $DIC;

        $this->ctrl = $DIC->ctrl();
        $this->lng = $DIC->language();
        $ilCtrl = $DIC->ctrl();
        $lng = $DIC->language();
        
        $this->read_only = (bool) $a_read_only;
        
        parent::__construct($a_parent_obj, $a_parent_cmd);
        
        $this->setLimit(9999);
        $this->disable("numinfo");
        
        $this->setDescription($lng->txt("constraints_introduction"));
        
        if (!$this->read_only) {
            $this->addColumn("", "", 1);
        }
        
        $this->addColumn("", "", 1);
        $this->addColumn($lng->txt("constraints_list_of_entities"), "");
        $this->addColumn($lng->txt("existing_constraints"), "");
        
        $this->setFormAction($ilCtrl->getFormAction($a_parent_obj));
        $this->setRowTemplate("tpl.svy_constraints_row.html", "Modules/Survey");
        
        if (!$this->read_only) {
            $this->addMultiCommand("createConstraints", $lng->txt("constraint_add"));
            $this->setSelectAllCheckbox("includeElements");
        }
                        
        $this->initItems($a_survey);
    }
    
    protected function initItems(ilObjSurvey $a_survey)
    {
        $lng = $this->lng;
        
        $this->structure = array();
        $tbl_data = array();
        
        $survey_questions = $a_survey->getSurveyQuestions();
        
        $last_questionblock_id = 0;
        $counter = 1;
        foreach ($survey_questions as $data) {
            $title = $data["title"];
            $show = true;
            if ($data["questionblock_id"] > 0) {
                $title = $data["questionblock_title"];
                $type = $lng->txt("questionblock");
                if ($data["questionblock_id"] != $last_questionblock_id) {
                    $last_questionblock_id = $data["questionblock_id"];
                    $this->structure[$counter] = array();
                    array_push($this->structure[$counter], $data["question_id"]);
                } else {
                    array_push($this->structure[$counter - 1], $data["question_id"]);
                    $show = false;
                }
            } else {
                $this->structure[$counter] = array($data["question_id"]);
                $type = $lng->txt("question");
            }
            if ($show) {
                $id = $content = $parsed = $conjunction = null;
                
                if ($counter == 1) {
                    $content = $lng->txt("constraints_first_question_description");
                } else {
                    $constraints = $a_survey->getConstraints($data["question_id"]);
                    if (count($constraints)) {
                        $parsed = array();
                                                
                        foreach ($constraints as $constraint) {
                            $parsed[] = array(
                                "id" => $constraint["id"],
                                "title" => $survey_questions[$constraint["question"]]["title"] . " " .
                                    $constraint["short"] . " " .
                                    $constraint["valueoutput"]
                            );
                        }
                        
                        if (count($constraints) > 1) {
                            $conjunction = ($constraints[0]['conjunction'])
                                ? $lng->txt('conjunction_or_title')
                                : $lng->txt('conjunction_and_title');
                        }
                    }
                }
                if ($counter != 1) {
                    $id = $counter;
                }
                
                $icontype = "question.png";
                if ($data["questionblock_id"] > 0) {
                    $icontype = "questionblock.png";
                }
                
                $tbl_data[] = array(
                    "counter" => $counter,
                    "id" => $id,
                    "title" => $title,
                    "type" => $type,
                    "icon" => ilUtil::getImagePath($icontype, "Modules/Survey"),
                    "content" => $content,
                    "constraints" => $parsed,
                    "conjunction" => $conjunction
                );
                
                $counter++;
            }
        }
        
        $this->setData($tbl_data);
    }
    
    public function getStructure()
    {
        return $this->structure;
    }
    
    protected function fillRow($a_set)
    {
        $ilCtrl = $this->ctrl;
        $lng = $this->lng;
        
        // $ilCtrl->setParameterByClass("ilObjSurveyAdministrationGUI", "item_id", $a_set["usr_id"]);
        
        if (!$this->read_only) {
            if ($a_set["id"]) {
                $this->tpl->setVariable("ID", $a_set["id"]);
            } else {
                $this->tpl->touchBlock("checkbox");
            }
        }
                    
        $this->tpl->setVariable("COUNTER", $a_set["counter"]);
        $this->tpl->setVariable("TITLE", $a_set["title"]);
        $this->tpl->setVariable("TYPE", $a_set["type"]);
        $this->tpl->setVariable("ICON_HREF", $a_set["icon"]);
        $this->tpl->setVariable("ICON_ALT", $a_set["type"]);
        $this->tpl->setVariable("CONTENT", $a_set["content"]);
        
        if (is_array($a_set["constraints"])) {
            foreach ($a_set["constraints"] as $constraint) {
                if (!$this->read_only) {
                    $ilCtrl->setParameter($this->getParentObject(), "precondition", $constraint["id"]);
                    $ilCtrl->setParameter($this->getParentObject(), "start", $a_set["counter"]);
                    $url = $ilCtrl->getLinkTarget($this->getParentObject(), "editPrecondition");
                    $ilCtrl->setParameter($this->getParentObject(), "precondition", "");
                    $ilCtrl->setParameter($this->getParentObject(), "start", "");
                    $this->tpl->setVariable("TEXT_EDIT_PRECONDITION", $lng->txt("edit"));
                    $this->tpl->setVariable("EDIT_PRECONDITION", $url);
                                        
                    $ilCtrl->setParameter($this->getParentObject(), "precondition", $constraint["id"]);
                    $url = $ilCtrl->getLinkTarget($this->getParentObject(), "confirmDeleteConstraints");
                    $ilCtrl->setParameter($this->getParentObject(), "precondition", "");
                    $this->tpl->setVariable("TEXT_DELETE_PRECONDITION", $lng->txt("delete"));
                    $this->tpl->setVariable("DELETE_PRECONDITION", $url);
                }
                
                $this->tpl->setCurrentBlock("constraint");
                $this->tpl->setVariable("CONSTRAINT_TEXT", $constraint["title"]);
                $this->tpl->parseCurrentBlock();
            }
            
            if ($a_set["conjunction"]) {
                $this->tpl->setCurrentBlock("conjunction");
                $this->tpl->setVariable("TEXT_CONJUNCTION", $a_set["conjunction"]);
                $this->tpl->parseCurrentBlock();
            }
        }
    }
}
