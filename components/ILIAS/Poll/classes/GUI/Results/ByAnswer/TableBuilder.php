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

namespace ILIAS\Poll\GUI\Results\ByAnswer;

use ILIAS\HTTP\Services as HTTPServices;
use ILIAS\UI\Component\Table\Data as DataTable;
use ILIAS\UI\Factory as UIFactory;
use ilLanguage;

class TableBuilder
{
    public const string TABLE_COL_ORDER = 'pos';
    public const string TABLE_COL_ANSWER = 'answer';
    public const string TABLE_COL_CURRENT_VOTES = 'votes';
    public const string TABLE_COL_CURRENT_PERCENTAGE = 'percentage';
    protected const string LNG_TABLE_COL_ORDER = 'poll_sortorder';
    protected const string LNG_TABLE_COL_ANSWER = 'poll_answer';
    protected const string LNG_TABLE_COL_CURRENT_VOTES = 'poll_absolute';
    protected const string LNG_TABLE_COL_CURRENT_PERCENTAGE = 'poll_percentage';
    protected const string TABLE_ID = 'pllnswrtbl';

    public function __construct(
        protected DataRetrieval $data_retrieval,
        protected UIFactory $ui_factory,
        protected ilLanguage $lng,
        protected HTTPServices $http_services
    ) {
    }

    protected function getColumns(): array
    {
        $columns = [
            self::TABLE_COL_ORDER => $this->ui_factory->table()->column()->number(
                $this->lng->txt(self::LNG_TABLE_COL_ORDER)
            ),
            self::TABLE_COL_ANSWER => $this->ui_factory->table()->column()->text(
                $this->lng->txt(self::LNG_TABLE_COL_ANSWER)
            )->withHighlight(true),
            self::TABLE_COL_CURRENT_VOTES => $this->ui_factory->table()->column()->number(
                $this->lng->txt(self::LNG_TABLE_COL_CURRENT_VOTES)
            ),
            self::TABLE_COL_CURRENT_PERCENTAGE => $this->ui_factory->table()->column()->number(
                $this->lng->txt(self::LNG_TABLE_COL_CURRENT_PERCENTAGE)
            )->withUnit('%')
        ];
        return $columns;
    }

    public function get(): DataTable
    {
        $title = $this->lng->txt("poll_question") . ": \"" . $this->data_retrieval->getQuestion() . "\"";
        return $this->ui_factory->table()->data(
            $this->data_retrieval,
            $title,
            $this->getColumns()
        )->withId(self::TABLE_ID)->withRequest($this->http_services->request());
    }
}
