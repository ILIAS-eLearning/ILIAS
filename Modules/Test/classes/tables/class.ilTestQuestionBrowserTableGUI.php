<?php
/* Copyright (c) 1998-2013 ILIAS open source, Extended GPL, see docs/LICENSE */

require_once './Services/Table/classes/class.ilTable2GUI.php';

/**
 *
 * @author Helmut Schottmüller <ilias@aurealis.de>
 * 
 * @version $Id$
 *
 * @ingroup ModulesGroup
 */
class ilTestQuestionBrowserTableGUI extends ilTable2GUI
{
	protected $writeAccess = false;

	/**
	 * Constructor
	 *
	 * @param 			$a_parent_obj
	 * @param string 	$a_parent_cmd
	 * @param int 		$a_ref_id
	 * @param bool   	$a_write_access
	 *
	 * @return \ilTestQuestionBrowserTableGUI
	 */
	public function __construct($a_parent_obj, $a_parent_cmd, $a_ref_id, $a_write_access = false)
	{
		$this->setId('qst_browser_' . $a_ref_id);
		parent::__construct($a_parent_obj, $a_parent_cmd);

		global $lng, $ilCtrl;

		$this->lng = $lng;
		$this->ctrl = $ilCtrl;
	
		$this->setWriteAccess($a_write_access);
		
		$this->setFormName('questionbrowser');
		$this->setStyle('table', 'fullwidth');
		$this->addColumn('','','1%', true);
		$this->addColumn($this->lng->txt("tst_question_title"),'title', '');
		$this->addColumn($this->lng->txt("description"),'description', '');
		$this->addColumn($this->lng->txt("tst_question_type"),'ttype', '');
		$this->addColumn($this->lng->txt("author"),'author', '');
		$this->addColumn($this->lng->txt("create_date"),'created', '');
		$this->addColumn($this->lng->txt("last_update"),'tstamp', '');  // name of col is proper "updated" but in data array the key is "tstamp"
		$this->addColumn($this->lng->txt("qpl"),'qpl', '');
		$this->addColumn($this->lng->txt("working_time"),'working_time', '');
		if ($this->getWriteAccess())
		{
			$this->addMultiCommand('insertQuestions', $this->lng->txt('insert'));
		}
	
		$this->setSelectAllCheckbox('q_id');
		$this->setRowTemplate("tpl.il_as_tst_question_browser_row.html", "Modules/Test");

		$this->setFormAction($this->ctrl->getFormAction($a_parent_obj, $a_parent_cmd));
		$this->setDefaultOrderField("title");
		$this->setDefaultOrderDirection("asc");
		
		$this->enable('sort');
		$this->enable('header');
		$this->enable('select_all');
		$this->setFilterCommand('filterAvailableQuestions');
		$this->setResetCommand('resetfilterAvailableQuestions');
		$this->initFilter();
	}

	/**
	 * Init filter
	 */
	function initFilter()
	{
		global $lng;
		
		// title
		include_once("./Services/Form/classes/class.ilTextInputGUI.php");
		$ti = new ilTextInputGUI($lng->txt("tst_qbt_filter_question_title"), "title");
		$ti->setMaxLength(64);
		$ti->setSize(20);
		$ti->setValidationRegexp('/(^[^%]+$)|(^$)/is');
		$this->addFilterItem($ti);
		$ti->readFromSession();
		$this->filter["title"] = $ti->getValue();
		
		// description
		$ti = new ilTextInputGUI($lng->txt("description"), "description");
		$ti->setMaxLength(64);
		$ti->setSize(20);
		$ti->setValidationRegexp('/(^[^%]+$)|(^$)/is');
		$this->addFilterItem($ti);
		$ti->readFromSession();
		$this->filter["description"] = $ti->getValue();
		
		// questiontype
		include_once("./Services/Form/classes/class.ilSelectInputGUI.php");
		include_once("./Modules/TestQuestionPool/classes/class.ilObjQuestionPool.php");
		$types = ilObjQuestionPool::_getQuestionTypes();
		$options = array();
		$options[""] = $lng->txt('filter_all_question_types');
		foreach ($types as $translation => $row)
		{
			$options[$row['type_tag']] = $translation;
		}

		$si = new ilSelectInputGUI($this->lng->txt("question_type"), "type");
		$si->setOptions($options);
		$this->addFilterItem($si);
		$si->readFromSession();
		$this->filter["type"] = $si->getValue();
		
		// author
		$ti = new ilTextInputGUI($lng->txt("author"), "author");
		$ti->setMaxLength(64);
		$ti->setSize(20);
		$this->addFilterItem($ti);
		$ti->setValidationRegexp('/(^[^%]+$)|(^$)/is');
		$ti->readFromSession();
		$this->filter["author"] = $ti->getValue();
		
		// question pool
		$ti = new ilTextInputGUI($lng->txt("qpl"), "qpl");
		$ti->setMaxLength(64);
		$ti->setSize(20);
		$ti->setValidationRegexp('/(^[^%]+$)|(^$)/is');
		$this->addFilterItem($ti);
		$ti->readFromSession();
		$this->filter["qpl"] = $ti->getValue();
	
	}

	/**
	 * fill row
	 *
	 * @param array $data
	 *
	 * @return void
	 */
	public function fillRow($data)
	{
		$this->tpl->setVariable("QUESTION_ID", $data["question_id"]);
		$this->tpl->setVariable("QUESTION_TITLE", $data["title"]);
		$this->tpl->setVariable("QUESTION_COMMENT", $data["description"]);
		include_once "./Modules/TestQuestionPool/classes/class.assQuestion.php";
		$this->tpl->setVariable("QUESTION_TYPE", assQuestion::_getQuestionTypeName($data["type_tag"]));
		$this->tpl->setVariable("QUESTION_AUTHOR", $data["author"]);
		$this->tpl->setVariable("QUESTION_CREATED", ilDatePresentation::formatDate(new ilDate($data['created'],IL_CAL_UNIX)));
		$this->tpl->setVariable("QUESTION_UPDATED", ilDatePresentation::formatDate(new ilDate($data["tstamp"],IL_CAL_UNIX)));
		$this->tpl->setVariable("QUESTION_POOL", $data['qpl']);
		$this->tpl->setVariable("WORKING_TIME", $data['working_time']);
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