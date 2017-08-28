<?php
/* Copyright (c) 1998-2012 ILIAS open source, Extended GPL, see docs/LICENSE */

require_once 'Services/Table/classes/class.ilTable2GUI.php';

/**
 * @author  Michael Jansen <mjansen@databay.de>
 * @ingroup ServicesMail
 */
class ilMailAttachmentTableGUI extends ilTable2GUI
{
	/**
	 * @var \ilCtrl
	 */
	protected $ctrl;

	/**
	 * @param $a_parent_obj
	 * @param $a_parent_cmd
	 */
	public function __construct($a_parent_obj, $a_parent_cmd)
	{
		global $DIC;

		$this->ctrl = $DIC->ctrl();

		// Call this immediately in constructor
		$this->setId('mail_attachments');

		$this->setDefaultOrderDirection('ASC');
		$this->setDefaultOrderField('filename');

		parent::__construct($a_parent_obj, $a_parent_cmd);

		$this->setTitle($this->lng->txt('attachment'));
		$this->setNoEntriesText($this->lng->txt('marked_entries'));

		$this->setFormAction($this->ctrl->getFormAction($a_parent_obj, 'applyFilter'));

		$this->setSelectAllCheckbox('filename[]');

		$this->setRowTemplate('tpl.mail_attachment_row.html', 'Services/Mail');

		$this->addMultiCommand('saveAttachments', $this->lng->txt('adopt'));
		$this->addMultiCommand('deleteAttachments', $this->lng->txt('delete'));

		$this->addCommandButton('cancelSaveAttachments', $this->lng->txt('cancel'));

		$this->addColumn($this->lng->txt(''), '', '1px', true);
		$this->addColumn($this->lng->txt('mail_file_name'), 'filename');
		$this->addColumn($this->lng->txt('mail_file_size'), 'filesize');
		$this->addColumn($this->lng->txt('create_date'), 'filecreatedate');
		// Show all attachments on one page
		$this->setLimit(PHP_INT_MAX);
	}

	/**
	 * @inheritdoc
	 */
	protected function fillRow($a_set)
	{
		/**
		 * We need to encode this because of filenames with the following format: "anystring".txt (with ")
		 */
		$this->tpl->setVariable('VAL_CHECKBOX', ilUtil::formCheckbox($a_set['checked'], 'filename[]', urlencode($a_set['filename'])));
		$this->tpl->setVariable('VAL_FILENAME', $this->formatValue('filename', $a_set['filename']));
		$this->tpl->setVariable('VAL_FILESIZE', $this->formatValue('filesize', $a_set['filesize']));
		$this->tpl->setVariable('VAL_FILECREATEDATE', $this->formatValue('filecreatedate', $a_set['filecreatedate']));
	}

	/**
	 * @param string $column
	 * @return bool
	 */
	public function numericOrdering($column)
	{
		if($column == 'filesize' || $column == 'filecreatedate') return true;

		return false;
	}

	/**
	 * @param string $column
	 * @param string $value
	 * @return string
	 */
	protected function formatValue($column, $value)
	{
		switch($column)
		{
			case 'filecreatedate':
				return ilDatePresentation::formatDate(new ilDateTime($value, IL_CAL_UNIX));

			case 'filesize':
				return ilUtil::formatSize($value);

			default:
				return $value;
		}
	}
}
