<?php
/* Copyright (c) 1998-2018 ILIAS open source, Extended GPL, see docs/LICENSE */

/**
 * Class ilTermsOfServiceDocumentTableGUI
 * @author Michael Jansen <mjansen@databay.de>
 */
class ilTermsOfServiceDocumentTableGUI extends \ilTermsOfServiceTableGUI
{
	/** @var ILIAS\UI\Factory */
	protected $uiFactory;

	/** @var ILIAS\UI\Renderer */
	protected $uiRenderer;

	/** @var $bool */
	protected $isEditable = false;

	/** @var int */
	protected $factor = 10;

	/** @var int */
	protected $i = 1;

	/**
	 * ilTermsOfServiceDocumentTableGUI constructor.
	 * @param \ilTermsOfServiceControllerEnabled $a_parent_obj
	 * @param string $command
	 * @param \ILIAS\UI\Factory $uiFactory
	 * @param \ILIAS\UI\Renderer $uiRenderer
	 * @param bool $isEditable
	 */
	public function __construct(
		\ilTermsOfServiceControllerEnabled $a_parent_obj,
		string $command,
		ILIAS\UI\Factory $uiFactory,
		ILIAS\UI\Renderer $uiRenderer,
		bool $isEditable = false
	) {
		$this->uiFactory = $uiFactory;
		$this->uiRenderer = $uiRenderer;
		$this->isEditable = $isEditable;

		$this->setId('tos_documents');
		$this->setFormName('tos_documents');

		parent::__construct($a_parent_obj, $command);

		$this->setTitle($this->lng->txt('tos_tbl_docs_title'));
		$this->setFormAction($this->ctrl->getFormAction($this->getParentObject(), $command));

		$this->setDefaultOrderDirection('ASC');
		$this->setDefaultOrderField('sorting');
		$this->setExternalSorting(true);
		$this->setExternalSegmentation(true);
		$this->setLimit(PHP_INT_MAX);

		$this->setRowTemplate('tpl.tos_documents_row.html', 'Services/TermsOfService');

		if ($this->isEditable) {
			$this->setSelectAllCheckbox('tos_id[]');
			$this->addMultiCommand('deleteDocuments', $this->lng->txt('delete'));
			$this->addCommandButton('saveDocumentSorting', $this->lng->txt('sorting_save'));
		}
	}

	/**
	 * @inheritdoc
	 */
	protected function getColumnDefinition(): array 
	{
		$i = 0;
		
		$columns = [];

		if ($this->isEditable) {
			$columns[++$i] = [
				'field' => 'chb',
				'txt' => '',
				'default' => true,
				'optional' => false,
				'sortable' => false,
				'is_checkbox' => true,
				'width' => '1%'
			];
			$columns[++$i] = [
				'field' => 'sorting',
				'txt' => $this->lng->txt('tos_tbl_docs_head_sorting'),
				'default' => true,
				'optional' => false,
				'sortable' => false,
				'width' => '5%'
			];
		}

		$columns[++$i] = [
			'field' => 'title',
			'txt' => $this->lng->txt('tos_tbl_docs_head_title'),
			'default' => true,
			'optional' => false,
			'sortable' => false,
			'width' => '25%'
		];

		$columns[++$i] = [
			'field' => 'creation_ts',
			'txt' => $this->lng->txt('tos_tbl_docs_head_created'),
			'default' => true,
			'optional' => true,
			'sortable' => false
		];

		$columns[++$i] = [
			'field' => 'modification_ts',
			'txt' => $this->lng->txt('tos_tbl_docs_head_last_change'),
			'default' => true,
			'optional' => false,
			'sortable' => false
		];

		$columns[++$i] = [
			'field' => 'criteria',
			'txt' => $this->lng->txt('tos_tbl_docs_head_criteria'),
			'default' => true,
			'optional' => false,
			'sortable' => false
		];

		if ($this->isEditable) {
			$columns[++$i] = [
				'field' => 'actions',
				'txt' => $this->lng->txt('actions'),
				'default' => true,
				'optional' => false,
				'sortable' => false,
				'width' => '10%'
			];
		};

		return $columns;
	}

	/**
	 * @param array $data
	 * @return array
	 */
	protected function preProcessData(array &$data)
	{
		foreach ($data['items'] as $key => $document) {
			/** @var ilTermsOfServiceDocument $document */

			$data['items'][$key] = [
				'id' => $document->getId(),
				'title' => $document->getTitle(),
				'creation_ts' => $document->getCreationTs(),
				'modification_ts' => $document->getModificationTs(),
				'text' => $document->getText(),
				'criteria' => '',
				'criteriaAssignments' => $document->getCriteria()
			];
		}
	}

	/**
	 * @inheritdoc
	 */
	protected function formatCellValue(string $column, array $row): string 
	{
		if (in_array($column, ['creation_ts', 'modification_ts'])) {
			return \ilDatePresentation::formatDate(new \ilDateTime($row[$column], IL_CAL_UNIX));
		} else if ('sorting' === $column) {
			return $this->formatSorting($row);
		} else if ('title' === $column) {
			return $this->formatTitle($column, $row);
		} else if ('actions' === $column) {
			return $this->formatActionsDropDown($column, $row);
		} else if ('chb' === $column) {
			return \ilUtil::formCheckbox(false, 'tos_id[]', $row['id']);
		} else if ('criteria' === $column) {
			return $this->formatCriterionAssignments($column, $row);
		}

		return parent::formatCellValue($column, $row);
	}

	/**
	 * @param string $column
	 * @param array $row
	 * @return string
	 */
	protected function formatActionsDropDown(string $column, array $row): string
	{
		if (!$this->isEditable) {
			return '';
		}

		$this->ctrl->setParameter($this->getParentObject(), 'tos_id', $row['id']);

		$editBtn = $this->uiFactory
			->button()
			->shy(
				$this->lng->txt('edit'),
				$this->ctrl->getLinkTarget($this->getParentObject(), 'showEditDocumentForm')
			);

		$deleteBtn = $this->uiFactory
			->button()
			->shy(
				$this->lng->txt('delete'),
				$this->ctrl->getLinkTarget($this->getParentObject(), 'deleteDocuments')
			);

		$attachCriterionBtn = $this->uiFactory
			->button()
			->shy(
				$this->lng->txt('tos_tbl_docs_action_add_criterion'),
				$this->ctrl->getLinkTarget($this->getParentObject(), 'showAttachCriterionForm')
			);

		$this->ctrl->setParameter($this->getParentObject(), 'tos_id', null);

		$dropDown = $this->uiFactory
			->dropdown()
			->standard([$editBtn, $deleteBtn, $attachCriterionBtn])
			->withLabel($this->lng->txt('actions'));

		return $this->uiRenderer->render([$dropDown]);
	}

	/**
	 * @param string $column
	 * @param array $row
	 * @return string
	 */
	protected function formatCriterionAssignments(string $column, array $row): string
	{
		$items = [];

		if (0 === count($row['criteriaAssignments'])) {
			return $this->lng->txt('tos_tbl_docs_cell_not_criterion');
		}

		foreach ($row['criteriaAssignments'] as $criterion) {
			/** @var $criterion \ilTermsOfServiceDocumentCriterionAssignment */

			$this->ctrl->setParameter($this->getParentObject(), 'tos_id', $row['id']);
			$this->ctrl->setParameter($this->getParentObject(), 'crit_id', $criterion->getId());

			$editBtn = $this->uiFactory
				->button()
				->shy(
					$this->lng->txt('edit'),
					$this->ctrl->getLinkTarget($this->getParentObject(), 'showChangeCriterionForm')
				);

			$deleteModal = $this->uiFactory
				->modal()
				->interruptive(
					$this->lng->txt('tos_doc_detach_crit_confirm_title'),
					$this->lng->txt('tos_doc_sure_detach_crit'),
					$this->ctrl->getFormAction($this->getParentObject(), 'detachCriterionAssignment')
				);

			$deleteBtn = $this->uiFactory
				->button()
				->shy(
					$this->lng->txt('delete'),
					'#'
				)->withOnClick($deleteModal->getShowSignal());

			$dropDown = $this->uiFactory
				->dropdown()
				->standard([$editBtn, $deleteBtn]);

			// TODO: Fetch Key/Value from criterion definition (to be implemented)
			$items[$criterion->getCriterionId() . ' (Id:  ' . $criterion->getId() . ')'] = 
				$this->uiFactory->legacy(implode('', [
					$criterion->getCriterionValue() .
					$this->uiRenderer->render([$dropDown, $deleteModal])
				]));

			$this->ctrl->setParameter($this->getParentObject(), 'tos_id', null);
			$this->ctrl->setParameter($this->getParentObject(), 'crit_id', null);
		}

		$criteriaList = $this->uiFactory
			->listing()
			->descriptive($items);

		return $this->uiRenderer->render([
			$criteriaList
		]);
	}

	/**
	 * @param string $column
	 * @param array $row
	 * @return string
	 */
	protected function formatTitle(string $column, array $row): string
	{
		$modal = $this->uiFactory
			->modal()
			->lightbox([new ilTermsOfServiceDocumentLightboxPage($row['title'], $row['text'])]);

		$titleLink = $this->uiFactory
			->button()
			->shy($row[$column], '#')
			->withOnClick($modal->getShowSignal());
		return $this->uiRenderer->render([$titleLink, $modal]);
	}

	/**
	 * @param array $row
	 * @return string
	 */
	protected function formatSorting(array $row): string
	{
		$sortingField = new \ilTextInputGUI('', 'sorting[' . $row['id'] . ']');
		$sortingField->setValue(($this->i++) * $this->factor);
		$sortingField->setMaxLength(5);
		$sortingField->setSize(4);

		return $sortingField->render('toolbar');
	}
}