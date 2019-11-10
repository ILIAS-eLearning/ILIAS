<?php

/* Copyright (c) 1998-2019 ILIAS open source, Extended GPL, see docs/LICENSE */

/**
 *
 * @author Helmut Schottmüller <ilias@aurealis.de>
 */
class ilSurveyPhrasesTableGUI extends ilTable2GUI
{
	protected $confirmdelete;
	
	/**
	 * Constructor
	 *
	 * @access public
	 * @param
	 * @return
	 */
	public function __construct($a_parent_obj, $a_parent_cmd, $confirmdelete = false)
	{
		global $DIC;

		parent::__construct($a_parent_obj, $a_parent_cmd);

		$lng = $DIC->language();
		$ilCtrl = $DIC->ctrl();

		$this->lng = $lng;
		$this->ctrl = $ilCtrl;
		$this->confirmdelete = $confirmdelete;
	
		$this->setFormName('phrases');
		$this->setStyle('table', 'fullwidth');
		if (!$confirmdelete)
		{
			$this->addColumn('','f','1%');
		}
		$this->addColumn($this->lng->txt("phrase"),'phrase', '');
		$this->addColumn($this->lng->txt("answers"),'answers', '');

		if ($confirmdelete)
		{
			$this->addCommandButton('confirmDeletePhrase', $this->lng->txt('confirm'));
			$this->addCommandButton('cancelDeletePhrase', $this->lng->txt('cancel'));
		}
		else
		{
			$this->addMultiCommand('editPhrase', $this->lng->txt('edit'));
			$this->addMultiCommand('deletePhrase', $this->lng->txt('delete'));			
		}

		$this->setRowTemplate("tpl.il_svy_qpl_phrase_row.html", "Modules/SurveyQuestionPool");

		$this->setFormAction($this->ctrl->getFormAction($a_parent_obj, $a_parent_cmd));
		$this->setDefaultOrderField("phrase");
		$this->setDefaultOrderDirection("asc");
		
		if ($confirmdelete)
		{
			$this->disable('sort');
			$this->disable('select_all');
		}
		else
		{
			$this->setPrefix('phrase');
			$this->setSelectAllCheckbox('phrase');
			$this->enable('sort');
			$this->enable('select_all');
		}
		$this->enable('header');
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
		if (!$this->confirmdelete)
		{
			$this->tpl->setCurrentBlock('checkbox');
			$this->tpl->setVariable('CB_PHRASE_ID', $data["phrase_id"]);
			$this->tpl->parseCurrentBlock();
		}
		else
		{
			$this->tpl->setCurrentBlock('hidden');
			$this->tpl->setVariable('HIDDEN_PHRASE_ID', $data["phrase_id"]);
			$this->tpl->parseCurrentBlock();
		}
		$this->tpl->setVariable('PHRASE_ID', $data["phrase_id"]);
		$this->tpl->setVariable("PHRASE", $data["phrase"]);
		$this->tpl->setVariable("ANSWERS", $data["answers"]);
	}
}
?>