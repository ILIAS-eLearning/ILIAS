<?php
/* Copyright (c) 1998-2012 ILIAS open source, Extended GPL, see docs/LICENSE */

require_once 'Services/Table/classes/class.ilTable2GUI.php';
require_once 'Services/Calendar/classes/class.ilDatePresentation.php';

/**
 * @author  Richard Klees <richard.klees@concepts-and-training.de>
 * @ingroup ServicesMailing
 */
class ilAutoMailsTableGUI extends ilTable2GUI
{
	/**
	 * @param $a_parent_obj
	 * @param $a_parent_cmd
	 */
	public function __construct($a_parent_gui, $a_parent_cmd, $a_title, $a_description)
	{
		global $ilCtrl;

		$this->ctrl = $ilCtrl;

		// Call this immediately in constructor
		$this->setId('mail_attachments');

		$this->setDefaultOrderDirection('ASC');
		$this->setDefaultOrderField('scheduled_for');

		parent::__construct($a_parent_gui, $a_parent_cmd);

		$this->setTitle($a_title);
		$this->setDescription($a_description);

		if($a_title) {
			$this->setEnableTitle(true);
		}

		$this->setRowTemplate('tpl.auto_mail_table_row.html', 'Services/Mailing');

		$this->addColumn($this->lng->txt('auto_mail_title'), 'title');
		$this->addColumn($this->lng->txt('description'), 'description');
		$this->addColumn($this->lng->txt('last_send'), 'last_send');
		$this->addColumn($this->lng->txt('scheduled_for'), 'scheduled_for');
		$this->addColumn("", '');
	}

	/**
	 * @param array $data
	 */
	protected function fillRow(Array $data)
	{
		$this->tpl->setVariable("TXT_EMAIL", $this->formatValue('title', $data['title']));
		$this->tpl->setVariable("TXT_DESC", $this->formatValue('description', $data["description"]));
		$this->tpl->setVariable("TXT_EMAIL_LAST_SEND", $this->formatValue('last_send', $data["last_send"]));
		$this->tpl->setVariable("TXT_EMAIL_SCHEDULED_FOR", $this->formatValue('scheduled_for', $data["scheduled_for"]));
		$this->tpl->setVariable("TXT_PREVIEW", $this->lng->txt("preview"));
		$this->ctrl->setParameter($this->parent_obj, "auto_mail_id", $data["id"]);
		$this->tpl->setVariable("URL_PREVIEW", $this->ctrl->getLinkTarget($this->parent_obj, "previewAutoMail"));
		if($data["has_recipients"]) {
			$this->tpl->setCurrentBlock("send_button");
			$this->tpl->setVariable("TXT_SEND", $this->lng->txt("send"));
			$this->tpl->setVariable("URL_SEND", $this->ctrl->getLinkTarget($this->parent_obj, "sendAutoMail"));
			$this->tpl->parseCurrentBlock();
		}


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
			case 'last_send':
				if ($value) {
					return ilDatePresentation::formatDate($value);
				}
				else {
					return $this->lng->txt("not_send_yet");
				}
			case 'scheduled_for':
				if ($value and !$value->isNull()) {
					return ilDatePresentation::formatDate($value);
				}
				else {
					return $this->lng->txt("no_scheduled_sending");
				}
			default:
				return $value;
		}
	}
}
