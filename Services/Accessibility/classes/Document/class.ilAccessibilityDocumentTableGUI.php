<?php
/* Copyright (c) 1998-2019 ILIAS open source, Extended GPL, see docs/LICENSE */

use ILIAS\UI\Factory;
use ILIAS\UI\Renderer;

/**
 * Class ilAccessibilityDocumentTableGUI
 */
class ilAccessibilityDocumentTableGUI extends ilAccessibilityTableGUI
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

    /** @var int */
    protected $numRenderedCriteria = 0;

    /** @var ilAccessibilityCriterionTypeFactoryInterface */
    protected $criterionTypeFactory;

    /** @var ILIAS\UI\Component\Component[] */
    protected $uiComponents = [];

    /**
     * ilAccessibilityDocumentTableGUI constructor.
     * @param ilAccessibilityControllerEnabled             $a_parent_obj
     * @param string                                        $command
     * @param ilAccessibilityCriterionTypeFactoryInterface $criterionTypeFactory
     * @param Factory                             $uiFactory
     * @param Renderer                            $uiRenderer
     * @param bool                                          $isEditable
     */
    public function __construct(
        ilAccessibilityControllerEnabled $a_parent_obj,
        string $command,
        ilAccessibilityCriterionTypeFactoryInterface $criterionTypeFactory,
        ILIAS\UI\Factory $uiFactory,
        ILIAS\UI\Renderer $uiRenderer,
        bool $isEditable = false
    ) {
        $this->criterionTypeFactory = $criterionTypeFactory;
        $this->uiFactory = $uiFactory;
        $this->uiRenderer = $uiRenderer;
        $this->isEditable = $isEditable;

        $this->setId('acc_documents');
        $this->setFormName('acc_documents');

        parent::__construct($a_parent_obj, $command);

        $this->setTitle($this->lng->txt('acc_tbl_docs_title'));
        $this->setFormAction($this->ctrl->getFormAction($this->getParentObject(), $command));

        $this->setDefaultOrderDirection('ASC');
        $this->setDefaultOrderField('sorting');
        $this->setExternalSorting(true);
        $this->setExternalSegmentation(true);
        $this->setLimit(PHP_INT_MAX);

        $this->setRowTemplate('tpl.acc_documents_row.html', 'Services/Accessibility');

        if ($this->isEditable) {
            $this->setSelectAllCheckbox('acc_id[]');
            $this->addMultiCommand('deleteDocuments', $this->lng->txt('delete'));
            $this->addCommandButton('saveDocumentSorting', $this->lng->txt('sorting_save'));
        }
    }

    /**
     * @inheritdoc
     */
    protected function getColumnDefinition() : array
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
        }

        $columns[++$i] = [
            'field' => 'sorting',
            'txt' => $this->lng->txt('acc_tbl_docs_head_sorting'),
            'default' => true,
            'optional' => false,
            'sortable' => false,
            'width' => '5%'
        ];

        $columns[++$i] = [
            'field' => 'title',
            'txt' => $this->lng->txt('acc_tbl_docs_head_title'),
            'default' => true,
            'optional' => false,
            'sortable' => false,
            'width' => '25%'
        ];

        $columns[++$i] = [
            'field' => 'creation_ts',
            'txt' => $this->lng->txt('acc_tbl_docs_head_created'),
            'default' => true,
            'optional' => true,
            'sortable' => false
        ];

        $columns[++$i] = [
            'field' => 'modification_ts',
            'txt' => $this->lng->txt('acc_tbl_docs_head_last_change'),
            'default' => true,
            'optional' => true,
            'sortable' => false
        ];

        $columns[++$i] = [
            'field' => 'criteria',
            'txt' => $this->lng->txt('acc_tbl_docs_head_criteria'),
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
    protected function preProcessData(array &$data) : void
    {
        foreach ($data['items'] as $key => $document) {
            /** @var ilAccessibilityDocument $document */

            $data['items'][$key] = [
                'id' => $document->getId(),
                'title' => $document->getTitle(),
                'creation_ts' => $document->getCreationTs(),
                'modification_ts' => $document->getModificationTs(),
                'text' => $document->getText(),
                'criteria' => '',
                'criteriaAssignments' => $document->criteria()
            ];
        }
    }

    /**
     * @inheritdoc
     * @throws ilDateTimeException
     * @throws ilAccessibilityCriterionTypeNotFoundException
     */
    protected function formatCellValue(string $column, array $row) : string
    {
        if (in_array($column, ['creation_ts', 'modification_ts'])) {
            return \ilDatePresentation::formatDate(new \ilDateTime($row[$column], IL_CAL_UNIX));
        } elseif ('sorting' === $column) {
            return $this->formatSorting($row);
        } elseif ('title' === $column) {
            return $this->formatTitle($column, $row);
        } elseif ('actions' === $column) {
            return $this->formatActionsDropDown($column, $row);
        } elseif ('chb' === $column) {
            return \ilUtil::formCheckbox(false, 'acc_id[]', $row['id']);
        } elseif ('criteria' === $column) {
            return $this->formatCriterionAssignments($column, $row);
        }

        return parent::formatCellValue($column, $row);
    }

    /**
     * @param string $column
     * @param array  $row
     * @return string
     */
    protected function formatActionsDropDown(string $column, array $row) : string
    {
        if (!$this->isEditable) {
            return '';
        }

        $this->ctrl->setParameter($this->getParentObject(), 'acc_id', $row['id']);

        $editBtn = $this->uiFactory
            ->button()
            ->shy(
                $this->lng->txt('edit'),
                $this->ctrl->getLinkTarget($this->getParentObject(), 'showEditDocumentForm')
            );

        $deleteModal = $this->uiFactory
            ->modal()
            ->interruptive(
                $this->lng->txt('acc_doc_delete'),
                $this->lng->txt('acc_sure_delete_documents_s'),
                $this->ctrl->getFormAction($this->getParentObject(), 'deleteDocument')
            );

        $deleteBtn = $this->uiFactory
            ->button()
            ->shy($this->lng->txt('delete'), '#')
            ->withOnClick($deleteModal->getShowSignal());

        $this->uiComponents[] = $deleteModal;

        $attachCriterionBtn = $this->uiFactory
            ->button()
            ->shy(
                $this->lng->txt('acc_tbl_docs_action_add_criterion'),
                $this->ctrl->getLinkTarget($this->getParentObject(), 'showAttachCriterionForm')
            );

        $this->ctrl->setParameter($this->getParentObject(), 'acc_id', null);

        $dropDown = $this->uiFactory
            ->dropdown()
            ->standard([$editBtn, $deleteBtn, $attachCriterionBtn])
            ->withLabel($this->lng->txt('actions'));

        return $this->uiRenderer->render([$dropDown]);
    }

    /**
     * @param string $column
     * @param array  $row
     * @return string
     * @throws ilAccessibilityCriterionTypeNotFoundException
     */
    protected function formatCriterionAssignments(string $column, array $row) : string
    {
        $items = [];

        if (0 === count($row['criteriaAssignments'])) {
            return $this->lng->txt('acc_tbl_docs_cell_not_criterion');
        }

        foreach ($row['criteriaAssignments'] as $criterion) {
            /** @var $criterion ilAccessibilityDocumentCriterionAssignment */

            $this->ctrl->setParameter($this->getParentObject(), 'acc_id', $row['id']);
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
                    $this->lng->txt('acc_doc_detach_crit_confirm_title'),
                    $this->lng->txt('acc_doc_sure_detach_crit'),
                    $this->ctrl->getFormAction($this->getParentObject(), 'detachCriterionAssignment')
                );

            $deleteBtn = $this->uiFactory
                ->button()
                ->shy($this->lng->txt('delete'), '#')
                ->withOnClick($deleteModal->getShowSignal());

            $dropDown = $this->uiFactory
                ->dropdown()
                ->standard([$editBtn, $deleteBtn]);

            $criterionType = $this->criterionTypeFactory->findByTypeIdent($criterion->getCriterionId(), true);
            $typeGui = $criterionType->ui($this->lng);

            $items[implode(' ', [
                $typeGui->getIdentPresentation(),
                ($this->isEditable ? $this->uiRenderer->render($dropDown) : '')
            ])] =
                $this->uiFactory->legacy(
                    $this->uiRenderer->render(
                        $typeGui->getValuePresentation(
                            $criterion->getCriterionValue(),
                            $this->uiFactory
                        )
                    )
                );

            if ($this->isEditable) {
                $this->uiComponents[] = $deleteModal;
            }

            $this->ctrl->setParameter($this->getParentObject(), 'acc_id', null);
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
     * @param array  $row
     * @return string
     */
    protected function formatTitle(string $column, array $row) : string
    {
        $modal = $this->uiFactory
            ->modal()
            ->lightbox([$this->uiFactory->modal()->lightboxTextPage($row['text'], $row['title'])]);

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
    protected function formatSorting(array $row) : string
    {
        $value = ($this->i++) * $this->factor;
        if (!$this->isEditable) {
            return (string) $value;
        }

        $sortingField = new ilNumberInputGUI('', 'sorting[' . $row['id'] . ']');
        $sortingField->setValue((string) $value);
        $sortingField->setMaxLength(4);
        $sortingField->setSize(2);

        return $sortingField->render();
    }

    /**
     * @inheritDoc
     */
    public function getHTML()
    {
        return parent::getHTML() . $this->uiRenderer->render($this->uiComponents);
    }
}
