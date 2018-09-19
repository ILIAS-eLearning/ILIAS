<?php
/* Copyright (c) 1998-2018 ILIAS open source, Extended GPL, see docs/LICENSE */

/**
 * @ilCtrl_Calls ilUserCertificateTableGUI: ilUserCertificateGUI

 * @ingroup ServicesCertificate
 *
 * @author  Niels Theen <ntheen@databay.de>
 */
class ilUserCertificateTableGUI extends ilTable2GUI
{
	/**
	 * @var ilCtrl
	 */
	private $controller;

	/**
	 * @param $parentObject
	 * @param string $parentCommand
	 * @param string $templateContext
	 * @param ilCtrl|null $controller
	 */
	public function __construct(
		$parentObject,
		$parentCommand = '',
		$templateContext = '',
		ilCtrl $controller = null
	) {
		$this->setId('user_certificates_table');

		parent::__construct($parentObject, $parentCommand, $templateContext);

		if ($controller === null) {
			global $DIC;
			$controller = $DIC->ctrl();
		}
		$this->controller = $controller;

		$this->setTitle($this->lng->txt('user_certificates'));
		$this->setRowTemplate('tpl.user_certificate_row.html', 'Services/Certificate');

		$this->addColumn($this->lng->txt('id'), '','');
		$this->addColumn($this->lng->txt('title'), '', '');
		$this->addColumn($this->lng->txt('date'), '', '');
		$this->addColumn($this->lng->txt('action'), '', '');

		$this->enable('select_all');
	}

	protected function fillRow($a_set)
	{
		$this->setSelectAllCheckbox('conditions');

		$this->tpl->setCurrentBlock('row');

		$this->tpl->setVariable('ID',  $a_set['id']);
		$this->tpl->setVariable('TITLE', $a_set['title']);
		$this->tpl->setVariable('DATE', $a_set['date']);

		$this->controller->setParameter($this->getParentObject(), 'certificate_id', $dataSet['id']);
		$link = $this->controller->getLinkTarget($this->getParentObject(), 'download');
		$this->controller->clearParameters($this->getParentObject());

		$this->tpl->setVariable('LINK', $link);

		$text = $this->lng->txt('download');
		$this->tpl->setVariable('LINK_TEXT', $text);
		$this->tpl->parseCurrentBlock();
	}
}
