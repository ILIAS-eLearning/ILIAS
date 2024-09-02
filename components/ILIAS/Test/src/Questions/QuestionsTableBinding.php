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

use ILIAS\Test\Utilities\TitleColumnsBuilder;

use ILIAS\UI\Component\Table;
use ILIAS\UI\Component\Link;

class QuestionsTableBinding implements Table\OrderingBinding
{
    public function __construct(
        protected array $records,
        protected \ilLanguage $lng,
        protected \Closure $title_link_builder,
        protected TitleColumnsBuilder $title_builder,
        protected string $context,
        protected bool $editing_enabled,
    ) {
    }

    public function getRows(
        Table\OrderingRowBuilder $row_builder,
        array $visible_column_ids
    ): \Generator {
        foreach ($this->records as $record) {
            $record['title'] = $this->getTitleLink($record['title'], $record['question_id']);
            $record['type_tag'] = $this->lng->txt($record['type_tag']);
            $record['complete'] = (bool) $record['complete'];
            $record['lifecycle'] = \ilAssQuestionLifecycle::getInstance($record['lifecycle'])->getTranslation($this->lng) ?? '';
            $record['qpl'] = $this->title_builder->buildAccessCheckedQuestionpoolTitleAsLink(
                $record['orig_obj_fi']
            );

            $default_and_edit = !($this->context === QuestionsTable::CONTEXT_DEFAULT && $this->editing_enabled);
            yield $row_builder->buildOrderingRow((string) $record['question_id'], $record)
                ->withDisabledAction(QuestionsTable::ACTION_DELETE, $default_and_edit && $this->context !== QuestionsTable::CONTEXT_CORRECTIONS)
                ->withDisabledAction(QuestionsTable::ACTION_COPY, $default_and_edit)
                ->withDisabledAction(QuestionsTable::ACTION_ADD_TO_POOL, $default_and_edit)
                ->withDisabledAction(QuestionsTable::ACTION_PREVIEW, !($this->context === QuestionsTable::CONTEXT_DEFAULT))
                ->withDisabledAction(QuestionsTable::ACTION_CORRECTION, !($this->context === QuestionsTable::CONTEXT_CORRECTIONS))
                ->withDisabledAction(QuestionsTable::ACTION_EDIT_QUESTION, $default_and_edit)
                ->withDisabledAction(QuestionsTable::ACTION_EDIT_PAGE, $default_and_edit)
                ->withDisabledAction(QuestionsTable::ACTION_FEEDBACK, $default_and_edit)
                ->withDisabledAction(QuestionsTable::ACTION_HINTS, $default_and_edit);
        }
    }

    private function getTitleLink(string $title, int $question_id): Link\Standard
    {
        $f = $this->title_link_builder;
        return $f($title, $question_id);
    }
}
