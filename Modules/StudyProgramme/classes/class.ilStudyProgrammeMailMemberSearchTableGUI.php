<?php

/* Copyright (c) 2019 Daniel Weise <daniel.weise@concepts-and-training.de> Extended GPL, see docs/LICENSE */

declare(strict_types=1);

class ilStudyProgrammeMailMemberSearchTableGUI extends ilTable2GUI
{
	/**
	 * @var ilCtrl
	 */
	protected $ctrl;

	/**
	 * @var ilLanguage
	 */
	protected $lng;

	/**
	 * @inheritdoc
	 */
	public function __construct($a_parent_obj, $a_parent_cmd = "")
	{
		global $DIC;

		$this->ctrl = $DIC['ilCtrl'];
		$this->lng  = $DIC['lng'];

		$obj_id = ilObject::_lookupObjectId($a_parent_obj->ref_id);
		$this->setId('mmsearch_'.$obj_id);
		parent::__construct($a_parent_obj, $a_parent_cmd);
		$this->lng->loadLanguageModule('prg');
		$this->setTitle($this->lng->txt('members'));

		$this->setFormAction($this->ctrl->getFormAction($a_parent_obj));
		$this->ctrl->clearParameters($a_parent_obj);

		$this->setRowTemplate('tpl.mail_member_search_row.html', 'Modules/StudyProgramme');

		// setup columns
		$this->addColumn('', '', '1%', true);
		$this->addColumn($this->lng->txt('login'), 'login', '22%');
		$this->addColumn($this->lng->txt('name'), 'name', '22%');

		$this->setSelectAllCheckbox('user_ids[]');
		$this->setShowRowsSelector(true);

		$this->addMultiCommand('sendMailToSelectedUsers', $this->lng->txt('mail_members'));
		$this->addCommandButton('cancel', $this->lng->txt('cancel'));
	}

	/**
	 * @param array $a_set
	 */
	public function fillRow($a_set)
	{
		foreach($a_set as $key => $value)
		{
			$this->tpl->setVariable(strtoupper($key), $value);
		}
	}
}
