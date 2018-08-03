<?php


class ilUserCertificateTableGUI extends ilTable2GUI
{
	/**
	 * @var ilCtrl
	 */
	private $controller;

	/**
	 * ilCertificateUserTableGUI constructor.
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
		parent::__construct($parentObject, $parentCommand, $templateContext);

		if ($controller === null) {
			global $DIC;
			$controller = $DIC->ctrl();
		}
		$this->controller = $controller;

		$this->setId('user_certificates_table');

		$this->setTitle($this->lng->txt('user_certificates'));
		$this->setRowTemplate('tpl.user_certificate_row.html', 'Services/Certificate');

		$this->addColumn($this->lng->txt('id'), '','');
		$this->addColumn($this->lng->txt('title'), '', '');
		$this->addColumn($this->lng->txt('date'), '', '');
		$this->addColumn($this->lng->txt('action'), '', '');
	}

	protected function fillRow($dataSet)
	{
		$this->tpl->setVariable('ID',  $dataSet['id']);
		$this->tpl->setVariable('TITLE', $dataSet['title']);
		$this->tpl->setVariable('DATE', $dataSet['date']);

//		$link = $this->ctrl->getLinkTargetByClass('ilusercertificategui', 'download');
//		$this->ctrl->setParameterByClass('ilusercertificategui', 'user_certificate_id', $dataSet['id']);
//		$this->tpl->setVariable('LINK', $link);

		$text = $this->lng->txt('download');
		$this->tpl->setVariable('LINK_TEXT', $text);
	}
}
