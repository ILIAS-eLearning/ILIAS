<?php
/* Copyright (c) 1998-2018 ILIAS open source, Extended GPL, see docs/LICENSE */

/**
 * Class ilTermsOfServiceAcceptanceHistoryTableGUI
 * @author Michael Jansen <mjansen@databay.de>
 */
class ilTermsOfServiceAcceptanceHistoryTableGUI extends \ilTermsOfServiceTableGUI
{
	/** @var ILIAS\UI\Factory */
	protected $uiFactory;

	/** @var ILIAS\UI\Renderer */
	protected $uiRenderer;

	/**
	 * ilTermsOfServiceAcceptanceHistoryTableGUI constructor.
	 * @param \ilTermsOfServiceControllerEnabled $controller
	 * @param string $command
	 * @param \ILIAS\UI\Factory $uiFactory
	 * @param \ILIAS\UI\Renderer $uiRenderer
	 */
	public function __construct(
		\ilTermsOfServiceControllerEnabled $controller,
		string $command,
		ILIAS\UI\Factory $uiFactory,
		ILIAS\UI\Renderer $uiRenderer
	) {
		$this->uiFactory = $uiFactory;
		$this->uiRenderer = $uiRenderer;

		$this->setId('tos_acceptance_history');
		$this->setFormName('tos_acceptance_history');

		parent::__construct($controller, $command);

		$this->setTitle($this->lng->txt('tos_acceptance_history'));
		$this->setFormAction($this->ctrl->getFormAction($controller, 'applyAcceptanceHistoryFilter'));

		$this->setDefaultOrderDirection('DESC');
		$this->setDefaultOrderField('ts');
		$this->setExternalSorting(true);
		$this->setExternalSegmentation(true);

		\iljQueryUtil::initjQuery();
		\ilYuiUtil::initPanel();
		\ilYuiUtil::initOverlay();

		$this->setShowRowsSelector(true);

		$this->setRowTemplate('tpl.tos_acceptance_history_table_row.html', 'Services/TermsOfService');

		$this->initFilter();
		$this->setFilterCommand('applyAcceptanceHistoryFilter');
		$this->setResetCommand('resetAcceptanceHistoryFilter');
	}

	/**
	 * @inheritdoc
	 */
	protected function getColumnDefinition(): array 
	{
		$i = 0;

		return [
			++$i => [
				'field' => 'ts',
				'txt' => $this->lng->txt('tos_acceptance_datetime'),
				'default' => true,
				'optional' => false,
				'sortable' => true
			],
			++$i => [
				'field' => 'login',
				'txt' => $this->lng->txt('login'),
				'default' => true,
				'optional' => false,
				'sortable' => true
			],
			++$i => [
				'field' => 'firstname',
				'txt' => $this->lng->txt('firstname'),
				'default' => false,
				'optional' => true,
				'sortable' => true
			],
			++$i => [
				'field' => 'lastname',
				'txt' => $this->lng->txt('lastname'),
				'default' => false,
				'optional' => true,
				'sortable' => true
			],
			++$i => [
				'field' => 'src',
				'txt' => $this->lng->txt('tos_agreement_document'),
				'default' => true,
				'optional' => false,
				'sortable' => true
			],
		];
	}

	/**
	 * @inheritdoc
	 */
	protected function formatCellValue(string $column, array $row): string 
	{
		if ('ts' === $column) {
			return \ilDatePresentation::formatDate(new \ilDateTime($row[$column], IL_CAL_UNIX));
		} else if ('src' === $column) {
			$modal = $this->uiFactory
				->modal()
				->lightbox([
					new class($row) implements \ILIAS\UI\Component\Modal\LightboxPage
					{
						protected $row = [];
						public function __construct(array $row)
						{
							$this->row = $row;
						}
						public function getTitle()
						{
							// TODO: Or $this->lng->txt('tos_agreement_document')
							return $this->row['src'];
						}
						public function getDescription()
						{
							return '';
						}
						public function getComponent()
						{
							return new \ILIAS\UI\Implementation\Component\Legacy\Legacy($this->row['text']);
						}
					}
				]);

			$titleLink = $this->uiFactory
				->button()
				->shy($row[$column], '#')
				->withOnClick($modal->getShowSignal());
			return $this->uiRenderer->render([$titleLink, $modal]);
		}

		return parent::formatCellValue($column, $row);
	}

	/**
	 * @inheritdoc
	 */
	public function numericOrdering($column)
	{
		if ('ts' === $column) {
			return true;
		}

		return false;
	}

	/**
	 * @inheritdoc
	 */
	public function initFilter()
	{
		$ul = new ilTextInputGUI(
			$this->lng->txt('login') . '/' . $this->lng->txt('email') . '/' . $this->lng->txt('name'),
			'query'
		);
		$ul->setDataSource($this->ctrl->getLinkTarget($this->getParentObject(), 'addUserAutoComplete', '', true));
		$ul->setSize(20);
		$ul->setSubmitFormOnEnter(true);
		$this->addFilterItem($ul);
		$ul->readFromSession();
		$this->filter['query'] = $ul->getValue();

		$this->tpl->addJavaScript("./Services/Form/js/Form.js");
		$duration = new \ilDateDurationInputGUI($this->lng->txt('tos_period'), 'period');
		$duration->setRequired(true);
		$duration->setStartText($this->lng->txt('tos_period_from'));
		$duration->setEndText($this->lng->txt('tos_period_until'));
		$duration->setStart(new \ilDateTime(strtotime('-1 year', time()), IL_CAL_UNIX));
		$duration->setEnd(new \ilDateTime(time(), IL_CAL_UNIX));
		$duration->setShowTime(true);
		$this->addFilterItem($duration, true);
		$duration->readFromSession();
		$this->optional_filter['period'] = $duration->getValue();
	}
}
