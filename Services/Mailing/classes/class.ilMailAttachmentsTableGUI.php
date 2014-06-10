<?php
/* Copyright (c) 1998-2012 ILIAS open source, Extended GPL, see docs/LICENSE */

require_once 'Services/Table/classes/class.ilTable2GUI.php';
require_once 'Services/Calendar/classes/class.ilDatePresentation.php';

/**
 * @author  Richard Klees <richard.klees@concepts-and-training.de>
 * @ingroup ServicesMailing
 */
class ilMailAttachmentsTableGUI extends ilTable2GUI
{
	/**
	 * @param $a_parent_obj
	 * @param $a_parent_cmd
	 */
	public function __construct($a_parent_obj, $a_parent_cmd)
	{
		global $ilCtrl;

		$this->ctrl = $ilCtrl;

		// Call this immediately in constructor
		$this->setId('mail_attachments');

		$this->setDefaultOrderDirection('ASC');
		$this->setDefaultOrderField('name');

		parent::__construct($a_parent_obj, $a_parent_cmd);

		$this->setTitle($this->lng->txt('attachments'));

		$this->setRowTemplate('tpl.mail_attachment_table_row.html', 'Services/Mailing');

		$this->addColumn($this->lng->txt('filename'), 'name');
		$this->addColumn($this->lng->txt('size'), 'size');
		$this->addColumn($this->lng->txt('last_modified'), 'last_modified');
		$this->addColumn("", '');
	}

	/**
	 * @param array $file_data
	 */
	protected function fillRow(Array $file_data)
	{
		/**
		 * We need to encode this because of filenames with the following format: "anystring".txt (with ")
		 */
		$this->ctrl->setParameter($this->parent_obj, "filename", $file_data["name"]);
		$this->tpl->setVariable("VAL_DELIVERY_URL", $this->ctrl->getLinkTarget($this->parent_obj, "deliverAttachment"));
		$this->ctrl->clearParameters($this->parent_obj);
		$this->tpl->setVariable('VAL_NAME', $this->formatValue('name', $file_data['name']));
		$this->tpl->setVariable('VAL_SIZE', $this->formatValue('size', $file_data['size']));
		$this->tpl->setVariable('VAL_LASTMODIFIED', $this->formatValue('last_modified', $file_data['last_modified']));
		$this->tpl->setVariable('VAL_ACTION_NAME', $this->lng->txt("delete"));
		$this->ctrl->setParameter($this->parent_obj, "filename", $file_data["name"]);
		$this->tpl->setVariable('VAL_ACTION_URL', $this->ctrl->getLinkTarget($this->parent_obj, "confirmRemoveAttachment"));
		$this->ctrl->clearParameters($this->parent_obj);
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
			case 'last_modified':
				return ilDatePresentation::formatDate(new ilDateTime($value, IL_CAL_UNIX));

			case 'size':
				return ilFormat::formatSize($value);

			default:
				return $value;
		}
	}
}
