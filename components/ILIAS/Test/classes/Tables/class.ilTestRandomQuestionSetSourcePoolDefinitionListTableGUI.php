<?php

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

declare(strict_types=1);

use ILIAS\Test\Utilities\TitleColumnsBuilder;
use ILIAS\UI\Factory as UIFactory;
use ILIAS\UI\Renderer as UIRenderer;

/**
 *
 * @author	Björn Heyser <bheyser@databay.de>
 * @version	$Id$
 *
 * @package	Modules/Test
 */
class ilTestRandomQuestionSetSourcePoolDefinitionListTableGUI extends ilTable2GUI
{
    public const IDENTIFIER = 'tstRndPools';
    private bool $definitionEditModeEnabled = false;
    private bool $questionAmountColumnEnabled = false;
    private bool $showMappedTaxonomyFilter = false;
    private ?ilTestQuestionFilterLabelTranslater $taxonomyLabelTranslater = null;

    public function __construct(
        ilTestRandomQuestionSetConfigGUI $parent_obj,
        string $parent_cmd,
        private UIFactory $ui_factory,
        private UIRenderer $ui_renderer,
        private TitleColumnsBuilder $title_builder,
        private array $defined_order,
        private array $question_amount
    ) {
        parent::__construct($parent_obj, $parent_cmd);
    }

    public function setTaxonomyFilterLabelTranslater(ilTestQuestionFilterLabelTranslater $translater): void
    {
        $this->taxonomyLabelTranslater = $translater;
    }

    public function setDefinitionEditModeEnabled($definitionEditModeEnabled): void
    {
        $this->definitionEditModeEnabled = $definitionEditModeEnabled;
    }

    public function isDefinitionEditModeEnabled(): bool
    {
        return $this->definitionEditModeEnabled;
    }

    public function setQuestionAmountColumnEnabled(bool $questionAmountColumnEnabled): void
    {
        $this->questionAmountColumnEnabled = $questionAmountColumnEnabled;
    }

    public function isQuestionAmountColumnEnabled(): bool
    {
        return $this->questionAmountColumnEnabled;
    }

    public function setShowMappedTaxonomyFilter(bool $showMappedTaxonomyFilter): void
    {
        $this->showMappedTaxonomyFilter = $showMappedTaxonomyFilter;
    }

    public function fillRow(array $a_set): void
    {
        if ($this->isDefinitionEditModeEnabled()) {
            $this->tpl->setCurrentBlock('col_selection_checkbox');
            $this->tpl->setVariable('SELECTION_CHECKBOX_HTML', $this->getSelectionCheckboxHTML($a_set['def_id']));
            $this->tpl->parseCurrentBlock();

            $this->tpl->setCurrentBlock('col_actions');
            $this->tpl->setVariable('ACTIONS_HTML', $this->getActionsHTML($a_set['def_id']));
            $this->tpl->parseCurrentBlock();

            $this->tpl->setCurrentBlock('col_order_checkbox');
            $this->tpl->setVariable('ORDER_INPUT_HTML', $this->getDefinitionOrderInputHTML(
                $a_set['def_id'],
                $this->getOrderNumberForSequencePosition($a_set['sequence_position'])
            ));
            $this->tpl->parseCurrentBlock();
        }
        // fau: taxFilter/typeFilter - show sequence position to identify the filter in the database
        else {
            $this->tpl->setCurrentBlock('col_order_checkbox');
            $this->tpl->setVariable('ORDER_INPUT_HTML', $a_set['sequence_position']);
            $this->tpl->parseCurrentBlock();
        }
        // fau.

        if ($this->isQuestionAmountColumnEnabled()) {
            if ($this->isDefinitionEditModeEnabled()) {
                $questionAmountHTML = $this->getQuestionAmountInputHTML(
                    $a_set['def_id'],
                    $a_set['question_amount']
                );
            } else {
                $questionAmountHTML = $a_set['question_amount'];
            }

            $this->tpl->setCurrentBlock('col_question_amount');
            $this->tpl->setVariable('QUESTION_AMOUNT_INPUT_HTML', $questionAmountHTML);
            $this->tpl->parseCurrentBlock();
        }

        $this->tpl->setVariable(
            'SOURCE_POOL_LABEL',
            $this->ui_renderer->render(
                $this->title_builder->buildAccessCheckedQuestionpoolTitleAsLink(
                    $a_set['ref_id'],
                    $a_set['source_pool_label'],
                    true
                )
            )
        );
        // fau: taxFilter/typeFilter - set taxonomy/type filter label in a single coulumn each

        $this->tpl->setVariable('TAXONOMY_FILTER', $this->taxonomyLabelTranslater->getTaxonomyFilterLabel($a_set['taxonomy_filter'], '<br />'));
        $this->tpl->setVariable('LIFECYCLE_FILTER', $this->taxonomyLabelTranslater->getLifecycleFilterLabel($a_set['lifecycle_filter']));
        $this->tpl->setVariable('TYPE_FILTER', $this->taxonomyLabelTranslater->getTypeFilterLabel($a_set['type_filter']));
        // fau.
    }

    private function getSelectionCheckboxHTML($source_pool_definitionId): string
    {
        return '<input type="checkbox" value="' . $source_pool_definitionId . '" name="src_pool_def_ids[]" />';
    }

    private function getDefinitionOrderInputHTML($srcPoolDefId, $defOrderNumber): string
    {
        return '<input type="text" size="2" value="' . $defOrderNumber . '" name="def_order[' . $srcPoolDefId . ']" />';
    }

    private function getQuestionAmountInputHTML($srcPoolDefId, $questionAmount): string
    {
        return '<input type="text" size="4" value="' . $questionAmount . '" name="quest_amount[' . $srcPoolDefId . ']" />';
    }

    private function getActionsHTML($source_pool_definitionId): string
    {
        $actions = [];
        $actions[] = $this->ui_factory->link()->standard($this->lng->txt('edit'), $this->getEditHref($source_pool_definitionId));
        $actions[] = $this->ui_factory->link()->standard($this->lng->txt('delete'), $this->getDeleteHref($source_pool_definitionId));
        $dropdown = $this->ui_factory->dropdown()->standard($actions)->withLabel($this->lng->txt('actions'));
        return $this->ui_renderer->render($dropdown);
    }

    private function getEditHref($source_pool_definitionId): string
    {
        $href = $this->ctrl->getLinkTarget(
            $this->parent_obj,
            ilTestRandomQuestionSetConfigGUI::CMD_SHOW_EDIT_SRC_POOL_DEF_FORM
        );

        $href = ilUtil::appendUrlParameterString($href, "src_pool_def_id=" . $source_pool_definitionId, true);

        return $href;
    }

    private function getDeleteHref($source_pool_definitionId): string
    {
        $href = $this->ctrl->getLinkTarget(
            $this->parent_obj,
            ilTestRandomQuestionSetConfigGUI::CMD_DELETE_SINGLE_SRC_POOL_DEF
        );

        $href = ilUtil::appendUrlParameterString($href, "src_pool_def_id=" . $source_pool_definitionId, true);

        return $href;
    }

    private function getOrderNumberForSequencePosition($sequencePosition)
    {
        return ($sequencePosition * 10);
    }

    private function getTaxonomyTreeLabel($taxonomyTreeId)
    {
        if (!$taxonomyTreeId) {
            return '';
        }

        return $this->taxonomyLabelTranslater->getTaxonomyTreeLabel($taxonomyTreeId);
    }

    private function getTaxonomyNodeLabel($taxonomyNodeId)
    {
        if (!$taxonomyNodeId) {
            return '';
        }

        return $this->taxonomyLabelTranslater->getTaxonomyNodeLabel($taxonomyNodeId);
    }

    public function build(): void
    {
        $this->setTableIdentifiers();

        $this->setTitle($this->lng->txt('tst_src_quest_pool_def_list_table'));

        $this->setRowTemplate("tpl.il_tst_rnd_quest_set_src_pool_def_row.html", "components/ILIAS/Test");

        $this->enable('header');
        $this->disable('sort');

        $this->enable('select_all');
        $this->setSelectAllCheckbox('src_pool_def_ids[]');

        $this->setExternalSegmentation(true);
        $this->setLimit(PHP_INT_MAX);

        $this->setFormAction($this->ctrl->getFormAction($this->parent_obj));

        $this->addCommands();
        $this->addColumns();
    }

    private function setTableIdentifiers(): void
    {
        $this->setId(self::IDENTIFIER);
        $this->setPrefix(self::IDENTIFIER);
        $this->setFormName(self::IDENTIFIER);
    }

    private function addCommands(): void
    {
        if ($this->isDefinitionEditModeEnabled()) {
            $this->addMultiCommand(ilTestRandomQuestionSetConfigGUI::CMD_DELETE_MULTI_SRC_POOL_DEFS, $this->lng->txt('delete'));
            $this->addCommandButton(ilTestRandomQuestionSetConfigGUI::CMD_SAVE_SRC_POOL_DEF_LIST, $this->lng->txt('save'));
        }
    }

    private function addColumns(): void
    {
        if ($this->isDefinitionEditModeEnabled()) {
            $this->addColumn('', 'select', '1%', true);
            $this->addColumn('', 'order', '1%', true);
        } else {
            $this->addColumn($this->lng->txt("position"));
        }

        $this->addColumn($this->lng->txt("tst_source_question_pool"), 'source_question_pool', '');
        $this->addColumn($this->lng->txt("tst_filter_taxonomy") . ' / ' . $this->lng->txt("tst_filter_tax_node"), 'tst_filter_taxonomy', '');
        #$this->addColumn($this->lng->txt("tst_filter_taxonomy"),'tst_filter_taxonomy', '');
        #$this->addColumn($this->lng->txt("tst_filter_tax_node"),'tst_filter_tax_node', '');
        $this->addColumn($this->lng->txt("qst_lifecycle"), 'tst_filter_lifecycle', '');
        $this->addColumn($this->lng->txt("tst_filter_question_type"), 'tst_filter_question_type', '');

        if ($this->isQuestionAmountColumnEnabled()) {
            $this->addColumn($this->lng->txt("tst_question_amount"), 'tst_question_amount', '');
        }

        if ($this->isDefinitionEditModeEnabled()) {
            $this->addColumn($this->lng->txt("actions"), 'actions', '');
        }
    }

    public function init(ilTestRandomQuestionSetSourcePoolDefinitionList $source_pool_definition_list): void
    {
        $rows = [];

        foreach ($source_pool_definition_list as $source_pool_definition) {
            $set = [];

            $set['def_id'] = $source_pool_definition->getId();
            $set['sequence_position'] = $source_pool_definition->getSequencePosition();
            $set['source_pool_label'] = $source_pool_definition->getPoolTitle();
            // fau: taxFilter/typeFilter - get the type and taxonomy filter for display
            if ($this->showMappedTaxonomyFilter) {
                // mapped filter will be used after synchronisation
                $set['taxonomy_filter'] = $source_pool_definition->getMappedTaxonomyFilter();
            } else {
                // original filter will be used before synchronisation
                $set['taxonomy_filter'] = $source_pool_definition->getOriginalTaxonomyFilter();
            }
            #$set['filter_taxonomy'] = $source_pool_definition->getMappedFilterTaxId();
            #$set['filter_tax_node'] = $source_pool_definition->getMappedFilterTaxNodeId();
            $set['lifecycle_filter'] = $source_pool_definition->getLifecycleFilter();
            $set['type_filter'] = $source_pool_definition->getTypeFilter();
            // fau.
            $set['question_amount'] = $source_pool_definition->getQuestionAmount();
            $set['ref_id'] = $source_pool_definition->getPoolRefId();
            $rows[] = $set;
        }

        $this->setData($rows);
    }

    public function applySubmit(ilTestRandomQuestionSetSourcePoolDefinitionList $source_pool_definition_list): void
    {
        foreach ($source_pool_definition_list as $source_pool_definition) {
            $order_number = $this->fetchOrderNumberParameter($source_pool_definition);
            $source_pool_definition->setSequencePosition($order_number);

            $source_pool_definition->setQuestionAmount(null);
            if ($this->isQuestionAmountColumnEnabled()) {
                $question_amount = $this->fetchQuestionAmountParameter($source_pool_definition);
                $source_pool_definition->setQuestionAmount($question_amount);
            }
        }
    }

    private function fetchOrderNumberParameter(ilTestRandomQuestionSetSourcePoolDefinition $definition): int
    {
        return array_key_exists($definition->getId(), $this->defined_order) ? (int) $this->defined_order[$definition->getId()] : 0;
    }

    private function fetchQuestionAmountParameter(ilTestRandomQuestionSetSourcePoolDefinition $definition): int
    {
        return array_key_exists($definition->getId(), $this->question_amount) ? (int) $this->question_amount[$definition->getId()] : 0;
    }
}
