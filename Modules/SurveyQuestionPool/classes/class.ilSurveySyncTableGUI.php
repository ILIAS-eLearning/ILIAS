<?php
/* Copyright (c) 2010 Leifos, GPL, see docs/LICENSE */

include_once("./Services/Table/classes/class.ilTable2GUI.php");

/**
 * Survey sync table GUI class
 *
 * @author Jörg Lützenkirchen <luetzenkirchen@leifos.de>
 * @version $Id$
 *
 * @ingroup ModulesSurveyQuestionPool
 */
class ilSurveySyncTableGUI extends ilTable2GUI
{
    /**
     * @var ilAccessHandler
     */
    protected $access;

    /**
     * @var ilTree
     */
    protected $tree;

    /**
     * Constructor
     *
     * @param object $a_parent_obj parent gui object
     * @param string $a_parent_cmd parent default command
     * @param ilSurveyQuestion $a_question
     */
    public function __construct($a_parent_obj, $a_parent_cmd, SurveyQuestion $a_question)
    {
        global $DIC;

        $this->ctrl = $DIC->ctrl();
        $this->lng = $DIC->language();
        $this->access = $DIC->access();
        $this->tree = $DIC->repositoryTree();
        $ilCtrl = $DIC->ctrl();
        $lng = $DIC->language();

        $this->question = $a_question;
        
        parent::__construct($a_parent_obj, $a_parent_cmd);

        $this->setId("il_svy_spl_sync");

        $this->setTitle($this->question->getTitle());
        $this->setDescription($lng->txt("survey_sync_question_copies_info"));
        
        $this->addCommandButton("synccopies", $lng->txt("survey_sync_question_copies"));
        $this->addCommandButton("cancelsync", $lng->txt("cancel"));

        // $this->setSelectAllCheckbox("id[]");
        $this->addColumn("", "", 1);
        $this->addColumn($lng->txt("title"), "");
            
        $this->setDefaultOrderField("title");
        $this->setDefaultOrderDirection("asc");

        $this->setFormAction($ilCtrl->getFormAction($a_parent_obj));
        $this->setRowTemplate("tpl.il_svy_qpl_sync.html", "Modules/SurveyQuestionPool");

        $this->importData();
    }

    /**
     * Import data from DB
     */
    protected function importData()
    {
        $ilAccess = $this->access;
        $lng = $this->lng;
        
        include_once "Modules/Survey/classes/class.ilObjSurvey.php";
        
        $table_data = array();
        foreach ($this->question->getCopyIds(true) as $survey_obj_id => $questions) {
            $survey_id = new ilObjSurvey($survey_obj_id, false);
            $survey_id->loadFromDB();
            $survey_id = $survey_id->getSurveyId();
            
            $ref_ids = ilObject::_getAllReferences($survey_obj_id);
            $message = "";
            
            // check permissions for "parent" survey
            $can_write = false;
            if (!ilObjSurvey::_hasDatasets($survey_id)) {
                foreach ($ref_ids as $ref_id) {
                    if ($ilAccess->checkAccess("edit", "", $ref_id)) {
                        $can_write = true;
                        break;
                    }
                }
            
                if (!$can_write) {
                    $message = $lng->txt("survey_sync_insufficient_permissions");
                }
            } else {
                $message = $lng->txt("survey_has_datasets_warning");
            }
                        
            $survey_title = ilObject::_lookupTitle($survey_obj_id);
            $survey_path = $this->buildPath($ref_ids);
            
            foreach ($questions as $question_id) {
                $title = SurveyQuestion::_getTitle($question_id);
                
                if (!$can_write) {
                    $question_id = null;
                }

                $table_data[] = array(
                    "id" => $question_id,
                    "title" => $title,
                    "path" => $survey_path,
                    "message" => $message
                    );
            }
        }

        $this->setData($table_data);
    }
    
    /**
     * Fill table row
     *
     * @param array $a_set data array
     */
    protected function fillRow($a_set)
    {
        $lng = $this->lng;
        $ilCtrl = $this->ctrl;
        
        $this->tpl->setVariable("TXT_PATH", $lng->txt("path"));
        
        if ($a_set["message"]) {
            $this->tpl->setCurrentBlock("message");
            $this->tpl->setVariable("TXT_MESSAGE", $a_set["message"]);
            $this->tpl->parseCurrentBlock();
        }
        
        // question
        if ($a_set["id"]) {
            $this->tpl->setCurrentBlock("checkbox");
            $this->tpl->setVariable("ID", $a_set["id"]);
            $this->tpl->parseCurrentBlock();
        }
        
        
        $this->tpl->setVariable("TITLE", $a_set["title"]);
        $this->tpl->setVariable("VALUE_PATH", implode("<br />", $a_set["path"]));
    }
    
    /**
     * Build path with deep-link
     *
     * @param	array	$ref_ids
     * @return	array
     */
    protected function buildPath($ref_ids)
    {
        $tree = $this->tree;
        $ilCtrl = $this->ctrl;

        include_once './Services/Link/classes/class.ilLink.php';
        
        if (!count($ref_ids)) {
            return false;
        }
        foreach ($ref_ids as $ref_id) {
            $path = "...";
            
            $counter = 0;
            $path_full = $tree->getPathFull($ref_id);
            if (sizeof($path_full)) {
                foreach ($path_full as $data) {
                    if (++$counter < (count($path_full) - 1)) {
                        continue;
                    }
                    $path .= " &raquo; ";
                    if ($ref_id != $data['ref_id']) {
                        $path .= $data['title'];
                    } else {
                        $path .= ('<a target="_top" href="' .
                                  ilLink::_getLink($data['ref_id'], $data['type']) . '">' .
                                  $data['title'] . '</a>');
                    }
                }
            }

            $result[] = $path;
        }
        return $result;
    }
}
