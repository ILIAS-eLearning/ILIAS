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
	 * @var ilUserCertificateTableProvider
	 */
	private $userCertificateProvider;

	/**
	 * @var ilObjUser|ilUser
	 */
	private $user;

	/**
	 * @var array
	 */
	private $optionalFilter = array();

	/**
	 * @var array
	 */
	private $optionalColumns = array();

	/**
	 * @var array
	 */
	private $visibleOptionalColumns;

	/**
	 * @param ilUserCertificateTableProvider $userCertificateTableProvider
	 * @param ilObjUser $user
	 * @param $parentObject
	 * @param string $parentCommand
	 * @param string $templateContext
	 * @param ilCtrl|null $controller
	 */
	public function __construct(
		ilUserCertificateTableProvider $userCertificateTableProvider,
		ilObjUser $user,
		$parentObject,
		$parentCommand = '',
		$templateContext = '',
		ilCtrl $controller = null
	) {
		$this->setId('user_certificates_table');

		$this->setDefaultOrderDirection('DESC');
		$this->setDefaultOrderField('date');
		$this->setExternalSorting(false);
		$this->setExternalSegmentation(false);

		$this->user = $user;

		parent::__construct($parentObject, $parentCommand, $templateContext);

		$this->userCertificateProvider = $userCertificateTableProvider;

		if ($controller === null) {
			global $DIC;
			$controller = $DIC->ctrl();
		}
		$this->controller = $controller;

		$this->setTitle($this->lng->txt('user_certificates'));
		$this->setRowTemplate('tpl.user_certificate_row.html', 'Services/Certificate');

		$this->optionalColumns = (array)$this->getSelectableColumns();
		$this->visibleOptionalColumns = (array)$this->getSelectedColumns();

		foreach($this->visibleOptionalColumns as $column) {
			$this->addColumn($this->optionalColumns[$column]['txt'], $column);
		}

		$this->addColumn($this->lng->txt('title'), '', '');
		$this->addColumn($this->lng->txt('date'), 'date', '');
		$this->addColumn($this->lng->txt('action'), '', '');

		$this->setFormAction($this->controller->getFormAction($parentObject, $parentCommand));

		$this->initFilter();
		$this->setFilterCommand('applyCertificatesFilter');
		$this->setResetCommand('resetCertificatesFilter');

	}

	protected function fillRow($row)
	{
		$this->tpl->setVariable('TITLE', $row['title']);
		$this->tpl->setVariable('DATE', $row['date']);

		$this->controller->setParameter($this->getParentObject(), 'certificate_id', $row['id']);
		$link = $this->controller->getLinkTarget($this->getParentObject(), 'download');
		$this->controller->clearParameters($this->getParentObject());

		$this->tpl->setVariable('LINK', $link);

		$text = $this->lng->txt('download');
		$this->tpl->setVariable('LINK_TEXT', $text);

		foreach ($this->optionalColumns as $index => $definition) {
			if (!$this->isColumnVisible($index)) {
				continue;
			}

			$this->tpl->setCurrentBlock('optional_column');
			$value = $row[$index];
			if ((string)$value === '') {
				$this->tpl->touchBlock('optional_column');
			} else {
				$this->tpl->setVariable('OPTIONAL_COLUMN_VAL', $value);
			}

			$this->tpl->parseCurrentBlock();
		}
	}

	/**
	 * @return array
	 */
	public function getSelectableColumns()
	{
		$cols = array(
			'id' => array(
				'txt' => $this->lng->txt('id'),
				'default' => false
			)
		);

		return $cols;
	}


	public function populate()
	{
		if (!$this->getExternalSegmentation() && $this->getExternalSorting()) {
			$this->determineOffsetAndOrder(true);
		} else {
			if ($this->getExternalSegmentation() || $this->getExternalSorting()) {
				$this->determineOffsetAndOrder();
			}
		}

		$params = array();
		if ($this->getExternalSegmentation()) {
			$params['limit'] = $this->getLimit();
			$params['offset'] = $this->getOffset();
		}

		if ($this->getExternalSorting()) {
			$params['order_field'] = $this->getOrderField();
			$params['order_direction'] = $this->getOrderDirection();
		}

		$this->determineSelectedFilters();
		$filter = array();

		foreach ($this->optionalFilter as $key => $value) {
			if ($this->isFilterSelected($key)) {
				$filter[$key] = $value;
			}
		}

		$data = $this->userCertificateProvider->fetchDataSet(
			$this->user->getId(),
			$params,
			$filter
		);

		if (!count($data) && $this->getOffset() > 0 && $this->getExternalSegmentation()) {
			$this->resetOffset();
			$params['limit'] = $this->getLimit();
			$params['offset'] = $this->getOffset();
			$data = $this->getProvider()->getList($params, $filter);
		}

		$this->setData($data['items']);
		if ($this->getExternalSegmentation()) {
			$this->setMaxCount($data['cnt']);
		}
	}

	/**
	 * @param string $column
	 * @return bool
	 */
	private function isColumnVisible(string $column): bool 
	{
		if (array_key_exists($column, $this->optionalColumns) && !isset($this->visibleOptionalColumns[$column])) {
			return false;
		}

		return true;
	}
}
