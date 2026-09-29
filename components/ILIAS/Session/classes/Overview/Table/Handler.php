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

namespace ILIAS\Session\Overview\Table;

use ilCalendarSettings;
use ilLanguage;
use ILIAS\Data\Factory as DataFactory;
use ILIAS\DI\UIServices;
use ILIAS\UI\Component\Table\Data as DataTable;
use ILIAS\HTTP\Services as HTTPServices;

class Handler
{
    protected const string TABLE_ID = "sess";
    public const string TABLE_COL_NAME = 'name';
    public const string TABLE_COL_LOGIN = 'login';

    public function __construct(
        protected readonly DataFactory $data_factory,
        protected readonly UIServices $ui,
        protected readonly ilLanguage $lng,
        protected readonly HTTPServices $http,
        protected readonly DataRetrieval $data_retrieval
    ) {
    }

    protected function getColumns(): array
    {
        $columns = [
            self::TABLE_COL_NAME => $this->ui->factory()->table()->column()->text(
                $this->lng->txt('name')
            )->withHighlight(true)->withIsSortable(true),
            self::TABLE_COL_LOGIN => $this->ui->factory()->table()->column()->text(
                $this->lng->txt('login')
            )->withHighlight(true)->withIsSortable(true),
        ];
        foreach ($this->data_retrieval->getEventColumnNames() as $event_column_id => $event_column_name) {
            $columns[$event_column_id] = $this->ui->factory()->table()->column()->statusIcon($event_column_name)
                                                                                ->withIsSortable(true);
        }
        return $columns;
    }

    protected function getTable(): DataTable
    {
        return $this->ui->factory()->table()->data(
            $this->data_retrieval,
            $this->lng->txt('event_overview'),
            $this->getColumns(),
        )->withId(self::TABLE_ID)->withRequest($this->http->request());
    }

    public function getHTML(): string
    {
        return $this->ui->renderer()->render([$this->getTable()]);
    }
}
