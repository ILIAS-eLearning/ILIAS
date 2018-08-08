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
			/** ilTermsOfServiceDocument $document */

			// TODO: Get all relevant data
			$data['items'][$key] = [
				'id' => $document->getId(),
				'title' => $document->getTitle(),
				'modification_ts' => $document->getModificationTs(),
				'content' => '12345'
			];
		}
	}


	/**
	 * @inheritdoc
	 */
	protected function prepareRow(array &$row)
	{
		$row['chb'] = \ilUtil::formCheckbox(
			false,
			'tos_id[]',
			$row['id']
		);

		$row['criteria'] = '';

		if ($this->isEditable) {
			$this->ctrl->setParameter($this->getParentObject(), 'tos_id', $row['id']);

			$actions = new \ilAdvancedSelectionListGUI();
			$actions->setId('tos_doc_' . $row['id']);
			$actions->setListTitle($this->lng->txt('actions'));
			$actions->addItem(
				$this->lng->txt('edit'),
				'',
				$this->ctrl->getLinkTarget($this->getParentObject(), 'showEditDocumentForm')
			);
			$actions->addItem(
				$this->lng->txt('tos_tbl_docs_action_edit_criteria'),
				'',
				$this->ctrl->getLinkTarget($this->getParentObject(), 'showCriteria')
			);
			$actions->addItem(
				$this->lng->txt('delete'),
				'',
				$this->ctrl->getLinkTarget($this->getParentObject(), 'deleteDocuments')
			);
			$row['actions'] = $actions->getHtml();

			$this->ctrl->setParameter($this->getParentObject(), 'tos_id', null);
		}

		parent::prepareRow($row);
	}

	/**
	 * @inheritdoc
	 */
	protected function formatCellValue(string $column, array $row): string 
	{
		if ('modification_ts' === $column) {
			return \ilDatePresentation::formatDate(new \ilDateTime($row[$column], IL_CAL_UNIX));
		} else if ('sorting' === $column) {
			$sortingField = new \ilTextInputGUI('', 'sorting[' . $row['id'] . ']');
			$sortingField->setValue(($this->i++) * $this->factor);
			$sortingField->setMaxLength(5);
			$sortingField->setSize(4);

			return $sortingField->render('toolbar');
		} else if ('title' === $column) {
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
							return $this->row['title'];
						}
						public function getDescription()
						{
							return '';
						}
						public function getComponent()
						{
							return new \ILIAS\UI\Implementation\Component\Legacy\Legacy($this->row['content']);
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
}