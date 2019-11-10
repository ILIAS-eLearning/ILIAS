<?php

/* Copyright (c) 1998-2019 ILIAS open source, Extended GPL, see docs/LICENSE */

/**
 *
 * @author Helmut Schottmüller <ilias@aurealis.de>
 */
class ilSurveyQuestionblockbrowserTableGUI extends ilTable2GUI
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

		$this->setFormName('surveyquestionblockbrowser');
		$this->setStyle('table', 'fullwidth');
		$this->addColumn('','f','1%');
		$this->addColumn($this->lng->txt("title"),'title', '');
		$this->addColumn($this->lng->txt("contains"),'contains', '');
		$this->addColumn($this->lng->txt("obj_svy"),'svy', '');

		$this->setPrefix('cb');
		$this->setSelectAllCheckbox('cb');
		
		$this->addMultiCommand('insertQuestionblocks', $this->lng->txt('insert'));

		$this->setRowTemplate("tpl.il_svy_svy_questionblockbrowser_row.html", "Modules/Survey");

		$this->setFormAction($this->ctrl->getFormAction($a_parent_obj, $a_parent_cmd));
		$this->setDefaultOrderField("title");
		$this->setDefaultOrderDirection("asc");
		
		$this->enable('sort');
		$this->enable('header');
		$this->enable('select_all');
		$this->setFilterCommand('filterQuestionblockBrowser');
		$this->setResetCommand('resetfilterQuestionblockBrowser');
		
		$this->initFilter();
		$this->initData($a_object);
	}
	
	function initData($a_object)
	{
		$arrFilter = array();
		foreach ($this->getFilterItems() as $item)
		{
			if ($item->getValue() !== false)
			{
				$arrFilter[$item->getPostVar()] = $item->getValue();
			}
		}
		$data = $a_object->getQuestionblocksTable($arrFilter);
		
		$this->setData($data);
	}

	/**
	* Init filter
	*/
	function initFilter()
	{
		$lng = $this->lng;
		$rbacreview = $this->rbacreview;
		$ilUser = $this->user;
		
		// title
		$ti = new ilTextInputGUI($lng->txt("title"), "title");
		$ti->setMaxLength(64);
		$ti->setSize(20);
		$ti->setValidationRegexp('/^[^%]+$/is');
		$this->addFilterItem($ti);
		$ti->readFromSession();
		$this->filter["title"] = $ti->getValue();
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

		$this->tpl->setVariable('QUESTIONBLOCK_ID', $data["questionblock_id"]);
		$this->tpl->setVariable("TITLE", ilUtil::prepareFormOutput($data["title"]));
		$this->tpl->setVariable("CONTAINS", ilUtil::prepareFormOutput($data["contains"]));
		$this->tpl->setVariable("SVY", ilUtil::prepareFormOutput($data['svy']));
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
?>