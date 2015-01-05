<?php
/* Copyright (c) 1998-2014 ILIAS open source, Extended GPL, see docs/LICENSE */

require_once 'Services/Table/classes/class.ilTable2GUI.php';

/**
 * @author Michael Jansen <mjansen@databay.de>
 * @version $Id$
 * @ingroup ServicesMail
 */
class ilMailingListsTableGUI extends ilTable2GUI
{
	/**
	 * @var ilLanguage
	 */
	protected $lng;

	/**
	 * @var ilCtrl
	 */
	protected $ctrl;

	/**
	 * @param        $a_parent_obj
	 * @param string $a_parent_cmd
	 * @param string $a_template_context
	 */
	public function __construct($a_parent_obj, $a_parent_cmd = '', $a_template_context = '')
	{
		/**
		 * @var $lng    ilLanguage
		 * @var $ilCtrl ilCtrl
		 */
		global $lng, $ilCtrl;

		$this->lng  = $lng;
		$this->ctrl = $ilCtrl;

		$this->setId('show_mlng_lists_tbl');
		parent::__construct($a_parent_obj, $a_parent_cmd, $a_template_context);

		$this->setFormAction($this->ctrl->getFormAction($a_parent_obj), 'showForm');
		$this->setTitle($this->lng->txt('mail_mailing_lists'));
		$this->setRowTemplate('tpl.mail_mailing_lists_listrow.html', 'Services/Contact');
		$this->setDefaultOrderField('title');
		$this->setSelectAllCheckbox('ml_id');
		$this->setNoEntriesText($this->lng->txt('mail_search_no'));
		$this->addCommandButton('showForm', $this->lng->txt('add'));

		$this->initColumns();
	}

	protected function initColumns()
	{
		$this->addColumn('', 'check', '10%', true);
		$this->addColumn($this->lng->txt('title'), 'title', '30%');
		$this->addColumn($this->lng->txt('description'), 'description', '30%');
		$this->addColumn($this->lng->txt('members'), 'members', '20%');
		$this->addColumn($this->lng->txt('actions'), '', '10%');
	}
}