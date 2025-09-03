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

namespace ILIAS\Questions\Presentation;

use ILIAS\Questions\Question\Persistence\Repository;
use ILIAS\Data\Range;
use ILIAS\Data\Order;
use ILIAS\UI\Component\Table;
use ILIAS\UI\URLBuilder;
use ILIAS\UI\URLBuilderToken;
use ILIAS\UI\Factory as UIFactory;
use ILIAS\UI\Component\Input\Container\Filter\Standard as Filter;

class QuestionsTable implements Table\DataRetrieval
{
    public function __construct(
        private readonly UIFactory $ui_factory,
        private readonly \ilUIService $ui_service,
        private readonly \ilLanguage $lng,
        private readonly Repository $questions_repository,
        private readonly URLBuilder $url_builder,
        private readonly URLBuilderToken $action_token,
        private readonly URLBuilderToken $row_id_token
    ) {
        $lng->loadLanguageModule('qpl');
    }

    public function getTable(): Table\Data
    {
        return $this->ui_factory->table()->data(
            $this,
            $this->lng->txt('questions'),
            $this->getColums(),
        );
    }

    public function getFilter(string $action): Filter
    {
        $question_type_options = [
            '' => $this->lng->txt('filter_all_question_types')
        ];

        foreach ($this->questions_repository->getAvailableAnswerTypes() as $class => $type) {
            $question_type_options[$class] = $type->getLabel($this->lng);
        }

        $field_factory = $this->ui_factory->input()->field();
        $filter_inputs = [
            'title' => $field_factory->text($this->lng->txt('title')),
            'contains_type' => $field_factory->select($this->lng->txt('contains_type'), $question_type_options),
        ];

        $active = array_fill(0, count($filter_inputs), true);

        $filter = $this->ui_service->filter()->standard(
            'question_table_filter_id',
            $action,
            $filter_inputs,
            $active,
            true,
            true
        );
        return $filter;
    }


    public function getColums(): array
    {
        $f = $this->ui_factory->table()->column();

        return [
            'title' => $f->link($this->lng->txt('title')),
            'type' => $f->text($this->lng->txt('question_type'))->withIsOptional(true, true),
        ];
    }

    public function getRows(
        Table\DataRowBuilder $row_builder,
        array $visible_column_ids,
        Range $range,
        Order $order,
        ?array $filter_data,
        ?array $additional_parameters
    ): \Generator {
        foreach ($this->questions_repository->getAllQuestions() as $question) {
            yield $question->toTableRow(
                $row_builder,
                $this->ui_factory,
                $this->url_builder,
                $this->row_id_token
            );
        }
    }

    public function getTotalRowCount(
        ?array $filter_data,
        ?array $additional_parameters
    ): ?int {
        return 0;
    }
}
