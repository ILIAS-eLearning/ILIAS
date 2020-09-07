<?php
/*
        +-----------------------------------------------------------------------------+
        | ILIAS open source                                                           |
        +-----------------------------------------------------------------------------+
        | Copyright (c) 1998-2006 ILIAS open source, University of Cologne            |
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

include_once('./Services/Table/classes/class.ilTable2GUI.php');

/**
*
* @author Helmut Schottmüller <ilias@aurealis.de>
* @version $Id$
*
* @ingroup ModulesSurveyQuestionPool
*/

class ilSurveyQuestionsTableGUI extends ilTable2GUI
{
    /**
     * @var ilRbacReview
     */
    protected $rbacreview;

    /**
     * @var ilObjUser
     */
    protected $user;

    protected $editable = true;
    protected $writeAccess = false;
    
    /**
     * Constructor
     *
     * @access public
     * @param
     * @return
     */
    public function __construct($a_parent_obj, $a_parent_cmd, $a_write_access = false)
    {
        global $DIC;

        $this->rbacreview = $DIC->rbac()->review();
        $this->user = $DIC->user();
        $this->setId("spl");
        $this->setPrefix('q_id'); // #16982
        
        parent::__construct($a_parent_obj, $a_parent_cmd);

        $lng = $DIC->language();
        $ilCtrl = $DIC->ctrl();

        $this->lng = $lng;
        $this->ctrl = $ilCtrl;
    
        $this->setWriteAccess($a_write_access);

        //$qplSetting = new ilSetting("spl");
            
        //$this->setFormName('questionbrowser');
        //$this->setStyle('table', 'fullwidth');
        
        if ($this->getWriteAccess()) {
            $this->addColumn('', '', '1%');
        }
        
        $this->addColumn($this->lng->txt("title"), 'title', '');
        $this->addColumn($this->lng->txt("obligatory"), "");
        
        foreach ($this->getSelectedColumns() as $c) {
            if (strcmp($c, 'description') == 0) {
                $this->addColumn($this->lng->txt("description"), 'description', '');
            }
            if (strcmp($c, 'type') == 0) {
                $this->addColumn($this->lng->txt("question_type"), 'type', '');
            }
            if (strcmp($c, 'author') == 0) {
                $this->addColumn($this->lng->txt("author"), 'author', '');
            }
            if (strcmp($c, 'created') == 0) {
                $this->addColumn($this->lng->txt("create_date"), 'created', '');
            }
            if (strcmp($c, 'updated') == 0) {
                $this->addColumn($this->lng->txt("last_update"), 'tstamp', '');
            }
        }
        
        $this->addColumn("", "");
        
        if ($this->getWriteAccess()) {
            $this->setSelectAllCheckbox('q_id');
        
            $this->addMultiCommand('copy', $this->lng->txt('copy'));
            $this->addMultiCommand('move', $this->lng->txt('move'));
            $this->addMultiCommand('exportQuestion', $this->lng->txt('export'));
            $this->addMultiCommand('deleteQuestions', $this->lng->txt('delete'));
            
            if (array_key_exists("spl_clipboard", $_SESSION)) {
                $this->addCommandButton('paste', $this->lng->txt('paste'));
            }
            
            $this->addCommandButton("saveObligatory", $this->lng->txt("spl_save_obligatory_state"));
        }


        $this->setRowTemplate("tpl.il_svy_qpl_questions_row.html", "Modules/SurveyQuestionPool");

        $this->setFormAction($this->ctrl->getFormAction($a_parent_obj, $a_parent_cmd));
        $this->setDefaultOrderField("title");
        $this->setDefaultOrderDirection("asc");
        
        $this->setShowRowsSelector(true);
        
        //$this->enable('sort');
        //$this->enable('header');
        //$this->enable('select_all');
        $this->setFilterCommand('filterQuestionBrowser');
        $this->setResetCommand('resetfilterQuestionBrowser');
        
        $this->initFilter();
    }

    /**
    * Init filter
    */
    public function initFilter()
    {
        $lng = $this->lng;
        $rbacreview = $this->rbacreview;
        $ilUser = $this->user;
        
        // title
        include_once("./Services/Form/classes/class.ilTextInputGUI.php");
        $ti = new ilTextInputGUI($lng->txt("title"), "title");
        $ti->setMaxLength(64);
        $ti->setSize(20);
        $ti->setValidationRegexp('/^[^%]+$/is');
        $this->addFilterItem($ti);
        $ti->readFromSession();
        $this->filter["title"] = $ti->getValue();
        
        // description
        $ti = new ilTextInputGUI($lng->txt("description"), "description");
        $ti->setMaxLength(64);
        $ti->setSize(20);
        $ti->setValidationRegexp('/^[^%]+$/is');
        $this->addFilterItem($ti);
        $ti->readFromSession();
        $this->filter["description"] = $ti->getValue();
        
        // author
        $ti = new ilTextInputGUI($lng->txt("author"), "author");
        $ti->setMaxLength(64);
        $ti->setSize(20);
        $ti->setValidationRegexp('/^[^%]+$/is');
        $this->addFilterItem($ti);
        $ti->readFromSession();
        $this->filter["author"] = $ti->getValue();
        
        // questiontype
        include_once("./Services/Form/classes/class.ilSelectInputGUI.php");
        include_once("./Modules/SurveyQuestionPool/classes/class.ilObjSurveyQuestionPool.php");
        $types = ilObjSurveyQuestionPool::_getQuestionTypes();
        $options = array();
        $options[""] = $lng->txt('filter_all_question_types');
        foreach ($types as $translation => $row) {
            $options[$row['type_tag']] = $translation;
        }

        $si = new ilSelectInputGUI($this->lng->txt("question_type"), "type");
        $si->setOptions($options);
        $this->addFilterItem($si);
        $si->readFromSession();
        $this->filter["type"] = $si->getValue();
    }

    public function getSelectableColumns()
    {
        $lng = $this->lng;
        $cols["description"] = array(
            "txt" => $lng->txt("description"),
            "default" => true
        );
        $cols["type"] = array(
            "txt" => $lng->txt("question_type"),
            "default" => true
        );
        $cols["author"] = array(
            "txt" => $lng->txt("author"),
            "default" => true
        );
        $cols["created"] = array(
            "txt" => $lng->txt("create_date"),
            "default" => true
        );
        $cols["updated"] = array(
            "txt" => $lng->txt("last_update"),
            "default" => true
        );
        return $cols;
    }

    /**
     * fill row
     *
     * @access public
     * @param
     * @return
     */
    public function fillRow($data)
    {
        include_once "./Modules/SurveyQuestionPool/classes/class.SurveyQuestion.php";
        include_once "./Modules/SurveyQuestionPool/classes/class.SurveyQuestionGUI.php";
        $class = strtolower(SurveyQuestionGUI::_getGUIClassNameForId($data["question_id"]));
        $guiclass = $class . "GUI";
        $this->ctrl->setParameterByClass(strtolower($guiclass), "q_id", $data["question_id"]);
        
        if ($this->getEditable()) {
            $url_edit = $this->ctrl->getLinkTargetByClass(strtolower($guiclass), "editQuestion");
            
            $this->tpl->setCurrentBlock("title_link_bl");
            $this->tpl->setVariable("QUESTION_TITLE_LINK", $data["title"]);
            $this->tpl->setVariable("URL_TITLE", $url_edit);
            $this->tpl->parseCurrentBlock();
        } else {
            $this->tpl->setCurrentBlock("title_nolink_bl");
            $this->tpl->setVariable("QUESTION_TITLE", $data["title"]);
            $this->tpl->parseCurrentBlock();
        }
        
        if ($data["complete"] == 0) {
            $this->tpl->setCurrentBlock("qpl_warning");
            $this->tpl->setVariable("IMAGE_WARNING", ilUtil::getImagePath("icon_alert.svg"));
            $this->tpl->setVariable("ALT_WARNING", $this->lng->txt("warning_question_not_complete"));
            $this->tpl->setVariable("TITLE_WARNING", $this->lng->txt("warning_question_not_complete"));
            $this->tpl->parseCurrentBlock();
        }
        
        foreach ($this->getSelectedColumns() as $c) {
            if (strcmp($c, 'description') == 0) {
                $this->tpl->setCurrentBlock('description');
                $this->tpl->setVariable("QUESTION_COMMENT", (strlen($data["description"])) ? $data["description"] : "&nbsp;");
                $this->tpl->parseCurrentBlock();
            }
            if (strcmp($c, 'type') == 0) {
                $this->tpl->setCurrentBlock('type');
                $this->tpl->setVariable("QUESTION_TYPE", SurveyQuestion::_getQuestionTypeName($data["type_tag"]));
                $this->tpl->parseCurrentBlock();
            }
            if (strcmp($c, 'author') == 0) {
                $this->tpl->setCurrentBlock('author');
                $this->tpl->setVariable("QUESTION_AUTHOR", $data["author"]);
                $this->tpl->parseCurrentBlock();
            }
            if (strcmp($c, 'created') == 0) {
                $this->tpl->setCurrentBlock('created');
                $this->tpl->setVariable("QUESTION_CREATED", ilDatePresentation::formatDate(new ilDate($data['created'], IL_CAL_UNIX)));
                $this->tpl->parseCurrentBlock();
            }
            if (strcmp($c, 'updated') == 0) {
                $this->tpl->setCurrentBlock('updated');
                $this->tpl->setVariable("QUESTION_UPDATED", ilDatePresentation::formatDate(new ilDate($data["tstamp"], IL_CAL_UNIX)));
                $this->tpl->parseCurrentBlock();
            }
        }
        
        // actions
        include_once "Services/UIComponent/AdvancedSelectionList/classes/class.ilAdvancedSelectionListGUI.php";
        $list = new ilAdvancedSelectionListGUI();
        $list->setId($data["question_id"]);
        $list->setListTitle($this->lng->txt("actions"));
        if ($url_edit) {
            $list->addItem($this->lng->txt("edit"), "", $url_edit);
        }
        $list->addItem($this->lng->txt("preview"), "", $this->ctrl->getLinkTargetByClass(strtolower($guiclass), "preview"));
        $this->tpl->setVariable("ACTION", $list->getHTML());
        $this->tpl->parseCurrentBlock();
            
        // obligatory
        if ($this->getEditable()) {
            $checked = $data["obligatory"] ? " checked=\"checked\"" : "";
            $obligatory = "<input type=\"checkbox\" name=\"obligatory_" .
                $data["question_id"] . "\" value=\"1\"" . $checked . " />";
        } elseif ($data["obligatory"]) {
            $obligatory = "<img src=\"" . ilUtil::getImagePath("obligatory.png", "Modules/Survey") .
                "\" alt=\"" . $this->lng->txt("question_obligatory") .
                "\" title=\"" . $this->lng->txt("question_obligatory") . "\" />";
        }
        $this->tpl->setVariable("OBLIGATORY", $obligatory);
                    
        if ($this->getWriteAccess()) {
            $this->tpl->setVariable('CBOX_ID', $data["question_id"]);
        }
        $this->tpl->setVariable('QUESTION_ID', $data["question_id"]);
    }
    
    public function setEditable($value)
    {
        $this->editable = $value;
    }
    
    public function getEditable()
    {
        return $this->editable;
    }

    public function setWriteAccess($value)
    {
        $this->writeAccess = $value;
    }
    
    public function getWriteAccess()
    {
        return $this->writeAccess;
    }
}
