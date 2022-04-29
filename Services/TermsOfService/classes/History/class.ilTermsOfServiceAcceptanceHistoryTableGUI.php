<?php declare(strict_types=1);

/**
 * This file is part of ILIAS, a powerful learning management system
 * published by ILIAS open source e-Learning e.V.
 *
 * ILIAS is licensed with the GPL-3.0,
 * see https://www.gnu.org/licenses/gpl-3.0.en.html
 * You should have received a copy of said license along with the
 * source code, too.
 *
 * If this is not the case or you just want to try ILIAS, you'll find
 * us at:
 * https://www.ilias.de
 * https://github.com/ILIAS-eLearning
 *
 *********************************************************************/

use ILIAS\UI\Factory;
use ILIAS\UI\Renderer;

/**
 * Class ilTermsOfServiceAcceptanceHistoryTableGUI
 * @author Michael Jansen <mjansen@databay.de>
 */
class ilTermsOfServiceAcceptanceHistoryTableGUI extends ilTermsOfServiceTableGUI
{
    protected Factory $uiFactory;
    protected Renderer $uiRenderer;
    protected int $numRenderedCriteria = 0;
    protected ilTermsOfServiceCriterionTypeFactoryInterface $criterionTypeFactory;

    public function __construct(
        ilTermsOfServiceControllerEnabled $controller,
        string $command,
        ilTermsOfServiceCriterionTypeFactoryInterface $criterionTypeFactory,
        Factory $uiFactory,
        Renderer $uiRenderer,
        ilGlobalTemplateInterface $globalTemplate
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

        iljQueryUtil::initjQuery($globalTemplate);
        ilYuiUtil::initPanel(false, $globalTemplate);
        ilYuiUtil::initOverlay($globalTemplate);
        $globalTemplate->addJavaScript('./Services/Form/js/Form.js');

        $this->setShowRowsSelector(true);

        $this->setRowTemplate('tpl.tos_acceptance_history_table_row.html', 'Services/TermsOfService');

        $this->initFilter();
        $this->setFilterCommand('applyAcceptanceHistoryFilter');
        $this->setResetCommand('resetAcceptanceHistoryFilter');
    }

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

    protected function formatCellValue(string $column, array $row) : string
    {
        if ('ts' === $column) {
            return ilDatePresentation::formatDate(new ilDateTime($row[$column], IL_CAL_UNIX));
        } elseif ('title' === $column) {
            return $this->formatTitle($column, $row);
        } elseif ('criteria' === $column) {
            return $this->formatCriterionAssignments($column, $row);
        }

        return parent::formatCellValue($column, $row);
    }

    protected function getUniqueCriterionListingAttribute() : string
    {
        return '<span class="ilNoDisplay">' . ($this->numRenderedCriteria++) . '</span>';
    }

    protected function formatCriterionAssignments(string $column, array $row) : string
    {
        $items = [];

        $criteria = new ilTermsOfServiceAcceptanceHistoryCriteriaBag($row['criteria']);

        if (0 === count($criteria)) {
            return $this->lng->txt('tos_tbl_hist_cell_not_criterion');
        }

        foreach ($criteria as $criterion) {
            $criterionType = $this->criterionTypeFactory->findByTypeIdent($criterion['id'], true);
            $typeGui = $criterionType->ui($this->lng);

            $items[$typeGui->getIdentPresentation() .
            $this->getUniqueCriterionListingAttribute()] = $typeGui->getValuePresentation(
                new ilTermsOfServiceCriterionConfig($criterion['value']),
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

    public function numericOrdering(string $a_field) : bool
    {
        return 'ts' === $a_field;
    }

    public function initFilter() : void
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

        $duration = new ilDateDurationInputGUI($this->lng->txt('tos_period'), 'period');
        $duration->setAllowOpenIntervals(true);
        $duration->setShowTime(true);
        $duration->setStartText($this->lng->txt('tos_period_from'));
        $duration->setEndText($this->lng->txt('tos_period_until'));
        $duration->setStart(new ilDateTime(null, IL_CAL_UNIX));
        $duration->setEnd(new ilDateTime(null, IL_CAL_UNIX));
        $this->addFilterItem($duration, true);
        $duration->readFromSession();
        $this->optional_filter['period'] = $duration->getValue();
    }
}
