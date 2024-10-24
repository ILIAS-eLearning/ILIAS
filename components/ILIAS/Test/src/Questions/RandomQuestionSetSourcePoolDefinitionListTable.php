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

namespace ILIAS\Test\Questions;

use ILIAS\Data\Factory as DataFactory;
use ILIAS\UI\Component\Link\Link;
use ILIAS\UI\Component\Table\OrderingBinding;
use ILIAS\UI\Component\Table\OrderingRowBuilder;
use ILIAS\UI\Factory as UIFactory;
use ILIAS\UI\Implementation\Component\Table\Column\Column;
use ilTestRandomQuestionSetSourcePoolDefinitionList as ilPoolDefinitionList;

/**
 * Class RandomQuestionSetSourcePoolDefinitionListTable
 */
class RandomQuestionSetSourcePoolDefinitionListTable implements OrderingBinding
{
    protected array $data = [];
    protected bool $editable = false;
    protected bool $show_amount = false;
    protected bool $show_mapped_taxonomy_filter = false;


    /**
     * @param \ilLanguage $lng
     * @param UIFactory $ui_factory
     * @param DataFactory $data_factory
     * @param \ilTestQuestionFilterLabelTranslater $translater
     * @param \Closure(int, string): Link $createShowPoolLink
     */
    public function __construct(
        protected readonly \ilLanguage $lng,
        protected readonly UIFactory $ui_factory,
        protected readonly DataFactory $data_factory,
        protected readonly \ilTestQuestionFilterLabelTranslater $translater,
        protected readonly \Closure $createShowPoolLink
    ) {
    }

    /**
     * @param OrderingRowBuilder $row_builder
     * @param array $visible_column_ids
     * @return \Generator
     */
    public function getRows(OrderingRowBuilder $row_builder, array $visible_column_ids): \Generator
    {
        $createShowPoolLink = $this->createShowPoolLink;
        foreach ($this->data as $qp) {
            $record = [
                'sequence_position' => (int) $qp['sequence_position'],
                'source_pool_label' => $createShowPoolLink($qp['ref_id'], $qp['source_pool_label']),
                'taxonomy_filter' => $this->translater->getTaxonomyFilterLabel($qp['taxonomy_filter'], '<br />'),
                'lifecycle_filter' => $this->translater->getLifecycleFilterLabel($qp['lifecycle_filter']),
                'type_filter' => $this->translater->getTypeFilterLabel($qp['type_filter']),
                'question_amount' => $this->getAmountCellContent($qp['def_id'], $qp['question_amount'])
            ];
            yield $row_builder->buildOrderingRow((string) $qp['def_id'], $record);
        }

    }

    /**
     * @param int $def_id
     * @param int|null $amount
     * @return string
     */
    protected function getAmountCellContent(int $def_id, ?int $amount): string
    {
        return $this->editable
            ? '<input type="text" size="4" value="' . $amount . '" name="quest_amount[' . $def_id . ']" />'
            : (string) $amount;
    }

    /**
     * @param ilPoolDefinitionList $source_pool_definition_list
     */
    public function setData(ilPoolDefinitionList $source_pool_definition_list): void
    {
        $data = [];

        foreach ($source_pool_definition_list as $source_pool_definition) {
            $set = [];

            $set['def_id'] = $source_pool_definition->getId();
            $set['sequence_position'] = $source_pool_definition->getSequencePosition();
            $set['source_pool_label'] = $source_pool_definition->getPoolTitle();
            // fau: taxFilter/typeFilter - get the type and taxonomy filter for display
            if ($this->show_mapped_taxonomy_filter) {
                // mapped filter will be used after synchronisation
                $set['taxonomy_filter'] = $source_pool_definition->getMappedTaxonomyFilter();
            } else {
                // original filter will be used before synchronisation
                $set['taxonomy_filter'] = $source_pool_definition->getOriginalTaxonomyFilter();
            }
            $set['lifecycle_filter'] = $source_pool_definition->getLifecycleFilter();
            $set['type_filter'] = $source_pool_definition->getTypeFilter();
            // fau.
            $set['question_amount'] = $source_pool_definition->getQuestionAmount();
            $set['ref_id'] = $source_pool_definition->getPoolRefId();
            $data[] = $set;
        }

        usort($data, function ($a, $b) {
            return $a['sequence_position'] <=> $b['sequence_position'];
        });

        $this->data = $data;
    }

    /**
     * @param bool $editable
     */
    public function setEditable(bool $editable): void
    {
        $this->editable = $editable;
    }

    /**
     * @param bool $show_amount
     */
    public function setShowAmount(bool $show_amount): void
    {
        $this->show_amount = $show_amount;
    }

    /**
     * @return bool
     */
    public function showAmount(): bool
    {
        return $this->show_amount;
    }

    /**
     * @param bool $show_filter
     */
    public function setShowMappedTaxonomyFilter(bool $show_filter): void
    {
        $this->show_mapped_taxonomy_filter = $show_filter;
    }

    /**
     * @return array<Column>
     */
    public function getColumns(): array
    {
        $column_factory = $this->ui_factory->table()->column();
        $columns_definition = [
            'sequence_position' => $column_factory->number($this->lng->txt('position'))->withUnit('.'),
            'source_pool_label' => $column_factory->link($this->lng->txt('tst_source_question_pool')),
            'taxonomy_filter' => $column_factory->text($this->lng->txt('tst_filter_taxonomy') . ' / ' . $this->lng->txt('tst_filter_tax_node')),
            'lifecycle_filter' => $column_factory->text($this->lng->txt('qst_lifecycle')),
            'type_filter' => $column_factory->text($this->lng->txt('tst_filter_question_type')),
            'question_amount' => $column_factory->text($this->lng->txt('tst_question_amount')),
        ];

        $columns_conditions = [
            'sequence_position' => !$this->editable,
            'question_amount' => $this->show_amount,
        ];

        return array_filter($columns_definition, function ($key) use ($columns_conditions) {
            return !isset($columns_conditions[$key]) || $columns_conditions[$key];
        }, ARRAY_FILTER_USE_KEY);
    }
}
