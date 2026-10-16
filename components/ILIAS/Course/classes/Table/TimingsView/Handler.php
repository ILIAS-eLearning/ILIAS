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

namespace ILIAS\Course\Table\TimingsView;

use ILIAS\Course\I\Table\TimingsView\DataRetrievalInterface;
use ILIAS\Course\I\Table\TimingsView\HandlerInterface;
use ILIAS\DI\UIServices;
use ILIAS\HTTP\Services as HTTPServices;
use ILIAS\UI\Component\Table\Data as DataTable;
use ilLanguage;

class Handler implements HandlerInterface
{
    protected const string LNG_TABLE_COL_TITLE = 'title';
    protected const string LNG_TABLE_COL_SUGGESTION_START = 'crs_timings_short_start_end';
    protected const string LNG_TABLE_COL_SUGGESTION_END = 'crs_timings_short_end';
    protected const string LNG_TABLE_COL_TIMINGS_MODIFIED = 'crs_timings_changed';
    protected const string LNG_TABLE_COL_TIMINGS_PLANNED_START = 'crs_timings_sug_begin';
    protected const string LNG_TABLE_COL_TIMINGS_PLANNED_END = 'crs_timings_sug_end';
    protected const string LNG_TABLE_TITLE = 'timings_of';
    protected const string TABLE_ID = 'tmgsvw';

    protected DataTable $table;

    public function __construct(
        protected readonly UIServices $ui,
        protected readonly HTTPServices $http,
        protected readonly DataRetrievalInterface $data_retrieval,
        protected readonly ilLanguage $lng,
    ) {
    }

    final protected function getColumns(): array
    {

        return [
            self::TABLE_COL_TITLE => $this->ui->factory()->table()->column()->link(
                $this->lng->txt(self::LNG_TABLE_COL_TITLE)
            ),
            self::TABLE_COL_SUGGESTION_START => $this->ui->factory()->table()->column()->date(
                $this->lng->txt(self::LNG_TABLE_COL_SUGGESTION_START),
                $this->data_retrieval->getDateFormat()
            ),
            self::TABLE_COL_SUGGESTION_END => $this->ui->factory()->table()->column()->date(
                $this->lng->txt(self::LNG_TABLE_COL_SUGGESTION_END),
                $this->data_retrieval->getDateFormat()
            ),
            self::TABLE_COL_TIMINGS_MODIFIED => $this->ui->factory()->table()->column()->statusIcon(
                $this->lng->txt(self::LNG_TABLE_COL_TIMINGS_MODIFIED)
            ),
            self::TABLE_COL_TIMINGS_PLANNED_START => $this->ui->factory()->table()->column()->date(
                $this->lng->txt(self::LNG_TABLE_COL_TIMINGS_PLANNED_START),
                $this->data_retrieval->getDateFormat()
            ),
            self::TABLE_COL_TIMINGS_PLANNED_END => $this->ui->factory()->table()->column()->date(
                $this->lng->txt(self::LNG_TABLE_COL_TIMINGS_PLANNED_END),
                $this->data_retrieval->getDateFormat()
            )
        ];
    }

    final protected function buildTableTitle(): string
    {
        return $this->lng->txt(self::LNG_TABLE_TITLE) . ': ' . $this->data_retrieval->getUsername();
    }

    final protected function initTable(): void
    {
        if (isset($this->table)) {
            return;
        }
        $this->table = $this->ui->factory()->table()->data(
            $this->data_retrieval,
            $this->buildTableTitle(),
            $this->getColumns(),
        )
            ->withId(self::TABLE_ID)
            ->withRequest($this->http->request());
    }

    final public function getHTML(): string
    {
        $this->initTable();
        return $this->ui->renderer()->render([$this->table]);
    }
}
