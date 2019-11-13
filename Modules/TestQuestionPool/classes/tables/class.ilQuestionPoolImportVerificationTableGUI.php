<?php
/* Copyright (c) 1998-2017 ILIAS open source, Extended GPL, see docs/LICENSE */

require_once 'Services/Table/classes/class.ilTable2GUI.php';

/**
 * Class ilQuestionPoolImportVerificationTableGUI
 */
class ilQuestionPoolImportVerificationTableGUI extends ilTable2GUI
{
	/**
	 * @inheritdoc
	 */
	public function __construct($a_parent_obj, $a_parent_cmd = "", $a_template_context = "")
	{
		$this->setId('qpl_imp_verify');
		parent::__construct($a_parent_obj, $a_parent_cmd, $a_template_context);

		$this->setOpenFormTag(false);
		$this->setCloseFormTag(false);
		$this->disable('sort');
		$this->setLimit(PHP_INT_MAX);
		$this->setSelectAllCheckbox('ident[]');

		$this->setRowTemplate('tpl.qpl_import_verification_row.html', 'Modules/TestQuestionPool');
		$this->addMultiCommand('importVerifiedFile', $this->lng->txt("import"));
		$this->addCommandButton('cancelImport', $this->lng->txt("cancel"));

		$this->initColumns();
	}

	/**
	 * 
	 */
	protected function initColumns()
	{
		$this->addColumn('', '', '1%', true);
		$this->addColumn($this->lng->txt('question_title'));
		$this->addColumn($this->lng->txt('question_type'));
	}

	/**
	 * @inheritdoc
	 */
	protected function fillRow($a_set)
	{
		$a_set['chb'] = ilUtil::formCheckbox(true, 'ident[]', $a_set['ident']);
		parent::fillRow($a_set);
	}
}