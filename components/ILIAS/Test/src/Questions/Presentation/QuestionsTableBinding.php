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

namespace ILIAS\Test\Questions\Presentation;

use ILIAS\Test\Utilities\TitleColumnsBuilder;

use ILIAS\UI\Factory as UIFactory;
use ILIAS\UI\Component\Table;
use ILIAS\UI\Component\Link;
use ILIAS\UI\URLBuilder;
use ILIAS\Language\Language;

class QuestionsTableBinding implements Table\OrderingBinding
{
    public function __construct(
        protected readonly array $records,
        protected readonly Language $lng,
        protected readonly UIFactory $ui_factory,
        protected readonly TitleColumnsBuilder $title_builder,
        protected readonly URLBuilder $url_builder,
        protected readonly string $row_id_token,
        protected readonly bool $editing_enabled
    ) {
    }

    public function getRows(
        Table\OrderingRowBuilder $row_builder,
        array $visible_column_ids
    ): \Generator {
        foreach ($this->records as $record) {
            $row = $record->getAsQuestionsTableRow(
                $this->lng,
                $this->ui_factory,
                $row_builder,
                $this->title_builder,
                $this->url_builder,
                $this->row_id_token
            );
            yield $row->withDisabledAction(QuestionsTable::ACTION_DELETE, $this->editing_enabled)
                ->withDisabledAction(QuestionsTable::ACTION_COPY, $this->editing_enabled)
                ->withDisabledAction(QuestionsTable::ACTION_ADD_TO_POOL, $this->editing_enabled)
                ->withDisabledAction(QuestionsTable::ACTION_EDIT_QUESTION, $this->editing_enabled)
                ->withDisabledAction(QuestionsTable::ACTION_EDIT_PAGE, $this->editing_enabled)
                ->withDisabledAction(QuestionsTable::ACTION_FEEDBACK, $this->editing_enabled)
                ->withDisabledAction(QuestionsTable::ACTION_HINTS, $this->editing_enabled);
        }
    }
}
