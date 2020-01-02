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
* @ingroup ModulesSurvey
*/

class ilSurveyQuestionbrowserTableGUI extends ilTable2GUI
{
    /**
     * @var ilRbacReview
     */
    protected $rbacreview;

    /**
     * @var ilObjUser
     */
    protected $user;

    /**
     * @var ilAccessHandler
     */
    protected $access;

    protected $editable = true;
    protected $writeAccess = false;
    protected $browsercolumns = array();
    protected $questionpools = null;
    
    /**
     * Constructor
     *
     * @access public
     * @param
     * @return
     */
    public function __construct($a_parent_obj, $a_parent_cmd, $a_object, $a_write_access = false)
    {
        global $DIC;

        $this->rbacreview = $DIC->rbac()->review();
        $this->user = $DIC->user();
        $this->access = $DIC->access();
        parent::__construct($a_parent_obj, $a_parent_cmd);

        $lng = $DIC->language();
        $ilCtrl = $DIC->ctrl();

        $this->lng = $lng;
        $this->ctrl = $ilCtrl;
    
        $this->setWriteAccess($a_write_access);

        $this->setFormName('surveyquestionbrowser');
        $this->setStyle('table', 'fullwidth');
        $this->addColumn('', 'f', '1%');
        $this->addColumn($this->lng->txt("title"), 'title', '');
        $this->addColumn('', 'preview', '');
        $this->addColumn($this->lng->txt("description"), 'description', '');
        $this->addColumn($this->lng->txt("question_type"), 'ttype', '');
        $this->addColumn($this->lng->txt("author"), 'author', '');
        $this->addColumn($this->lng->txt("create_date"), 'created', '');
        $this->addColumn($this->lng->txt("last_update"), 'updated', '');
        $this->addColumn($this->lng->txt("obj_spl"), 'spl', '');

        $this->setPrefix('q_id');
        $this->setSelectAllCheckbox('q_id');
        
        $this->addMultiCommand('insertQuestions', $this->lng->txt('insert'));

        $this->setRowTemplate("tpl.il_svy_svy_questionbrowser_row.html", "Modules/Survey");

        $this->setFormAction($this->ctrl->getFormAction($a_parent_obj, $a_parent_cmd));
        $this->setDefaultOrderField("title");
        $this->setDefaultOrderDirection("asc");
        include_once "./Modules/SurveyQuestionPool/classes/class.ilObjSurveyQuestionPool.php";
        $this->questionpools = ilObjSurveyQuestionPool::_getAvailableQuestionpools(true, false, true);
        
        $this->enable('sort');
        $this->enable('header');
        $this->enable('select_all');
        $this->setFilterCommand('filterQuestionBrowser');
        $this->setResetCommand('resetfilterQuestionBrowser');

        $this->initFilter();
        $this->initData($a_object);
    }
    
    public function initData($a_object)
    {
        $arrFilter = array();
        foreach ($this->getFilterItems() as $item) {
            if ($item->getValue() !== false) {
                $arrFilter[$item->getPostVar()] = $item->getValue();
            }
        }
        $data = $a_object->getQuestionsTable($arrFilter);
        
        // translate pools for proper sorting
        if (sizeof($data)) {
            $pools = $this->getQuestionPools();
            foreach ($data as $idx => $row) {
                $data[$idx]["spl"] = $pools[$row["obj_fi"]];
            }
        }
        
        $this->setData($data);
    }
    
    public function getQuestionPools()
    {
        return $this->questionpools;
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
        $ti = new ilTextInputGUI($lng->txt("survey_question_title"), "title");
        $ti->setMaxLength(64);
        $ti->setValidationRegexp('/^[^%]+$/is');
        $ti->setSize(20);
        $this->addFilterItem($ti);
        $ti->readFromSession();
        $this->filter["title"] = $ti->getValue();
        
        // description
        $ti = new ilTextInputGUI($lng->txt("description"), "description");
        $ti->setMaxLength(64);
        $ti->setValidationRegexp('/^[^%]+$/is');
        $ti->setSize(20);
        $this->addFilterItem($ti);
        $ti->readFromSession();
        $this->filter["description"] = $ti->getValue();
        
        // author
        $ti = new ilTextInputGUI($lng->txt("author"), "author");
        $ti->setMaxLength(64);
        $ti->setValidationRegexp('/^[^%]+$/is');
        $ti->setSize(20);
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
        
        
        // questionpool text
        $ti = new ilTextInputGUI($lng->txt("survey_question_pool_title"), "spl_txt");
        $ti->setMaxLength(64);
        $ti->setSize(20);
        $this->addFilterItem($ti);
        $ti->readFromSession();
        $this->filter["spl_txt"] = $ti->getValue();
        
        // questionpool select
        $options = array();
        $options[""] = $lng->txt('filter_all_questionpools');
        natcasesort($this->questionpools);
        foreach ($this->questionpools as $obj_id => $title) {
            $options[$obj_id] = $title;
        }
        $si = new ilSelectInputGUI($this->lng->txt("survey_available_question_pools"), "spl");
        $si->setOptions($options);
        $this->addFilterItem($si);
        $si->readFromSession();
        $this->filter["type"] = $si->getValue();
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
        $ilUser = $this->user;
        $ilAccess = $this->access;
        
        $this->tpl->setVariable('QUESTION_ID', $data["question_id"]);
        $this->tpl->setVariable("QUESTION_TITLE", ilUtil::prepareFormOutput($data["title"]));

        $this->tpl->setVariable("TXT_PREVIEW", $this->lng->txt("preview"));
        $guiclass = strtolower($data['type_tag']) . "gui";
        $this->ctrl->setParameterByClass($guiclass, "q_id", $data["question_id"]);
        $this->tpl->setVariable("LINK_PREVIEW", "ilias.php?baseClass=ilObjSurveyQuestionPoolGUI&amp;ref_id=" . $data["ref_id"] . "&amp;cmd=preview&amp;preview=" . $data["question_id"]);

        $this->tpl->setVariable("QUESTION_DESCRIPTION", ilUtil::prepareFormOutput((strlen($data["description"])) ? $data["description"] : ""));
        $this->tpl->setVariable("QUESTION_TYPE", $data["ttype"]);
        $this->tpl->setVariable("QUESTION_AUTHOR", ilUtil::prepareFormOutput($data["author"]));
        $this->tpl->setVariable("QUESTION_CREATED", ilDatePresentation::formatDate(new ilDate($data['created'], IL_CAL_UNIX)));
        $this->tpl->setVariable("QUESTION_UPDATED", ilDatePresentation::formatDate(new ilDate($data["tstamp"], IL_CAL_UNIX)));
        $this->tpl->setVariable("QPL", ilUtil::prepareFormOutput($data["spl"]));
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
