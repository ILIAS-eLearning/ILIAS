<?php
/* Copyright (c) 1998-2018 ILIAS open source, Extended GPL, see docs/LICENSE */

use ILIAS\UI\Factory;
use ILIAS\UI\Renderer;

/**
 * Class ilTermsOfServiceAcceptanceHistoryTableGUI
 * @author Michael Jansen <mjansen@databay.de>
 */
class ilTermsOfServiceAcceptanceHistoryTableGUI extends \ilTermsOfServiceTableGUI
{
    /** @var Factory */
    protected $uiFactory;

    /** @var Renderer */
    protected $uiRenderer;

    /** @var int */
    protected $numRenderedCriteria = 0;

    /** @var \ilTermsOfServiceCriterionTypeFactoryInterface */
    protected $criterionTypeFactory;

    /**
     * ilTermsOfServiceAcceptanceHistoryTableGUI constructor.
     * @param \ilTermsOfServiceControllerEnabled $controller
     * @param string $command
     * @param \ilTermsOfServiceCriterionTypeFactoryInterface $criterionTypeFactory
     * @param Factory $uiFactory
     * @param Renderer $uiRenderer
     */
    public function __construct(
        \ilTermsOfServiceControllerEnabled $controller,
        string $command,
        \ilTermsOfServiceCriterionTypeFactoryInterface $criterionTypeFactory,
        Factory $uiFactory,
        Renderer $uiRenderer
    ) {
        $this->criterionTypeFactory = $criterionTypeFactory;
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
    protected function getColumnDefinition() : array
    {
        $i = 0;

        return [
            ++$i => [
                'field' => 'ts',
                'txt' => $this->lng->txt('tos_tbl_hist_head_acceptance_date'),
                'default' => true,
                'optional' => false,
                'sortable' => true
            ],
            ++$i => [
                'field' => 'login',
                'txt' => $this->lng->txt('tos_tbl_hist_head_login'),
                'default' => true,
                'optional' => false,
                'sortable' => true
            ],
            ++$i => [
                'field' => 'firstname',
                'txt' => $this->lng->txt('tos_tbl_hist_head_firstname'),
                'default' => false,
                'optional' => true,
                'sortable' => true
            ],
            ++$i => [
                'field' => 'lastname',
                'txt' => $this->lng->txt('tos_tbl_hist_head_lastname'),
                'default' => false,
                'optional' => true,
                'sortable' => true
            ],
            ++$i => [
                'field' => 'title',
                'txt' => $this->lng->txt('tos_tbl_hist_head_document'),
                'default' => true,
                'optional' => false,
                'sortable' => true
            ],
            ++$i => [
                'field' => 'criteria',
                'txt' => $this->lng->txt('tos_tbl_hist_head_criteria'),
                'default' => false,
                'optional' => true,
                'sortable' => false
            ],
        ];
    }

    /**
     * @inheritdoc
     */
    protected function formatCellValue(string $column, array $row) : string
    {
        if ('ts' === $column) {
            return \ilDatePresentation::formatDate(new \ilDateTime($row[$column], IL_CAL_UNIX));
        } elseif ('title' === $column) {
            return $this->formatTitle($column, $row);
        } elseif ('criteria' === $column) {
            return $this->formatCriterionAssignments($column, $row);
        }

        return parent::formatCellValue($column, $row);
    }

    /**
     * @return string
     */
    protected function getUniqueCriterionListingAttribute() : string
    {
        return '<span class="ilNoDisplay">' . ($this->numRenderedCriteria++) . '</span>';
    }

    /**
     * @param string $column
     * @param array $row
     * @return string
     */
    protected function formatCriterionAssignments(string $column, array $row) : string
    {
        $items = [];

        $criteria = new \ilTermsOfServiceAcceptanceHistoryCriteriaBag($row['criteria']);

        if (0 === count($criteria)) {
            return $this->lng->txt('tos_tbl_hist_cell_not_criterion');
        }

        foreach ($criteria as $criterion) {
            $criterionType = $this->criterionTypeFactory->findByTypeIdent($criterion['id'], true);
            $typeGui = $criterionType->ui($this->lng);

            $items[
                $typeGui->getIdentPresentation() .
                $this->getUniqueCriterionListingAttribute()
            ] = $typeGui->getValuePresentation(
                new \ilTermsOfServiceCriterionConfig($criterion['value']),
                $this->uiFactory
            );
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
        $duration->setAllowOpenIntervals(true);
        $duration->setShowTime(true);
        $duration->setStartText($this->lng->txt('tos_period_from'));
        $duration->setEndText($this->lng->txt('tos_period_until'));
        $duration->setStart(new \ilDateTime(null, IL_CAL_UNIX));
        $duration->setEnd(new \ilDateTime(null, IL_CAL_UNIX));
        $this->addFilterItem($duration, true);
        $duration->readFromSession();
        $this->optional_filter['period'] = $duration->getValue();
    }
}
