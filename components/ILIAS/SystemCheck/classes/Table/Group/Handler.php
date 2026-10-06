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

namespace ILIAS\SystemCheck\Table\Group;

use ILIAS\Data\Factory as DataFactory;
use ILIAS\DI\UIServices;
use ILIAS\HTTP\Services as HTTPServices;
use ILIAS\SystemCheck\I\Table\Group\DataRetrievalInterface;
use ILIAS\SystemCheck\I\Table\Group\HandlerInterface;
use ILIAS\UI\Component\Table\Data as DataTable;
use ilLanguage;

readonly class Handler implements HandlerInterface
{
    protected const string TABLE_ID = "sc_groups";
    public const string TABLE_COL_TITLE = 'title';
    public const string TABLE_COL_DESCRIPTION = 'description';
    public const string TABLE_COL_LAST_UPDATE = 'last_update';
    public const string TABLE_COL_SOLVED_TASKS = 'sysc_completed_num';
    public const string TABLE_COL_UNSOLVED_TASKS = 'sysc_failed_num';
    protected const string LNG_TABLE_COL_TITLE = 'title';
    protected const string LNG_TABLE_COL_DESCRIPTION = 'description';
    protected const string LNG_TABLE_COL_LAST_UPDATE = 'last_update';
    protected const string LNG_TABLE_COL_SOLVED_TASKS = 'sysc_completed_num';
    protected const string LNG_TABLE_COL_UNSOLVED_TASKS = 'sysc_failed_num';
    protected const string LNG_TABLE_TITLE = 'sysc_overview';
    protected DataTable $table;

    public function __construct(
        protected DataFactory $data_factory,
        protected UIServices $ui,
        protected ilLanguage $lng,
        protected HTTPServices $http,
        protected DataRetrievalInterface $data_retrieval
    ) {
    }

    final protected function getColumns(): array
    {
        return [
            self::TABLE_COL_TITLE => $this->ui->factory()->table()->column()->link(
                $this->lng->txt(self::LNG_TABLE_COL_TITLE)
            )->withHighlight(true)->withIsSortable(true),
            self::TABLE_COL_DESCRIPTION => $this->ui->factory()->table()->column()->text(
                $this->lng->txt(self::LNG_TABLE_COL_DESCRIPTION)
            )->withHighlight(true)->withIsSortable(true),
            self::TABLE_COL_LAST_UPDATE => $this->ui->factory()->table()->column()->text(
                $this->lng->txt(self::LNG_TABLE_COL_LAST_UPDATE)
            )->withHighlight(true)->withIsSortable(true),
            self::TABLE_COL_SOLVED_TASKS => $this->ui->factory()->table()->column()->number(
                $this->lng->txt(self::LNG_TABLE_COL_SOLVED_TASKS)
            )->withHighlight(true)->withIsSortable(true),
            self::TABLE_COL_UNSOLVED_TASKS => $this->ui->factory()->table()->column()->number(
                $this->lng->txt(self::LNG_TABLE_COL_UNSOLVED_TASKS)
            )->withHighlight(true)->withIsSortable(true)
        ];
    }

    final protected function getTable(): DataTable
    {
        if (!isset($this->table)) {
            $this->table = $this->ui->factory()->table()->data(
                $this->data_retrieval,
                $this->lng->txt(self::LNG_TABLE_TITLE),
                $this->getColumns(),
            )->withId(self::TABLE_ID)->withRequest($this->http->request());
        }
        return $this->table;
    }

    final public function getHTML(): string
    {
        return $this->ui->renderer()->render([$this->getTable()]);
    }
}
