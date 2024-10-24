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

use ilAssQuestionLifecycle;
use ILIAS\UI\Component\Input\Field\Factory as FieldFactory;
use ILIAS\UI\Component\Input\Input;
use ILIAS\UI\Factory as UIFactory;
use ILIAS\UI\Component\Input\Container\Filter\Filter;
use ilLanguage;
use ilObjQuestionPool;
use ilUIService;
use Psr\Http\Message\ServerRequestInterface;

class QuestionsBrowserFilter
{
    public function __construct(
        private readonly ilUIService $ui_service,
        private readonly ilLanguage $lng,
        private readonly UIFactory $ui_factory,
        private readonly string $filter_id,
        private readonly string $parent_title
    ) {
    }

    public function getComponent(string $action, ServerRequestInterface $request): Filter
    {
        $filter_inputs = [];
        $is_input_initially_rendered = [];
        $field_factory = $this->ui_factory->input()->field();

        foreach ($this->getFields($field_factory) as $filter_id => $filter) {
            [$filter_inputs[$filter_id], $is_input_initially_rendered[$filter_id]] = $filter;
        }

        $component = $this->ui_service->filter()->standard(
            $this->filter_id,
            $action,
            $filter_inputs,
            $is_input_initially_rendered,
            true,
            true
        );

        return $request->getMethod() === 'POST' ? $component->withRequest($request) : $component;
    }

    /**
     * @param FieldFactory $input
     *
     * @return array<string, array<Input, bool>>
     */
    private function getFields(FieldFactory $input): array
    {
        $lifecycle_options = array_merge(
            ['' => $this->lng->txt('qst_lifecycle_filter_all')],
            ilAssQuestionLifecycle::getDraftInstance()->getSelectOptions($this->lng)
        );
        $yes_no_all_options = [
            '' => $this->lng->txt('resulttable_all'),
            'true' => $this->lng->txt('yes'),
            'false' => $this->lng->txt('no')
        ];

        return [
            'title' => [$input->text($this->lng->txt('tst_question_title')), true],
            'description' => [$input->text($this->lng->txt('description')), false],
            'type' => [$input->select($this->lng->txt('tst_question_type'), $this->resolveQuestionTypeFilterOptions()), true],
            'author' => [$input->text($this->lng->txt('author')), false],
            'lifecycle' => [$input->select($this->lng->txt('qst_lifecycle'), $lifecycle_options), false],
            'parent_title' => [$input->text($this->lng->txt($this->parent_title)), true],
            'taxonomy_title' => [$input->text($this->lng->txt('taxonomy_title')), false],
            'taxonomy_node_title' => [$input->text($this->lng->txt('taxonomy_node_title')), false],
            'feedback' => [$input->select($this->lng->txt('feedback'), $yes_no_all_options), false],
            'hints' => [$input->select($this->lng->txt('hints'), $yes_no_all_options), false],
        ];
    }

    private function resolveQuestionTypeFilterOptions(): array
    {
        $question_type_options = ['' => $this->lng->txt('filter_all_question_types')];

        foreach (ilObjQuestionPool::_getQuestionTypes() as $translation => $row) {
            $question_type_options[$row['type_tag']] = $translation;
        }
        return $question_type_options;
    }
}
