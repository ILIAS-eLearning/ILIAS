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

namespace ILIAS\Poll\GUI\Results\ByUser;

use ILIAS\HTTP\Services as HTTPServices;
use ILIAS\UI\Component\Table\Data as DataTable;
use ILIAS\UI\Factory as UIFactory;
use ilLanguage;

class TableBuilder
{
    public const string TABLE_COL_LOGIN = 'login';
    public const string TABLE_COL_LASTNAME = 'lastname';
    public const string TABLE_COL_FIRSTNAME = 'firstname';
    public const string TABLE_COL_ANSWER_PREFIX = 'answer';
    public const string LNG_TABLE_COL_LOGIN = 'login';
    public const string LNG_TABLE_COL_LASTNAME = 'lastname';
    public const string LNG_TABLE_COL_FIRSTNAME = 'firstname';
    protected const string TABLE_ID = 'pllusrtbl';

    public function __construct(
        protected DataRetrieval $data_retrieval,
        protected UIFactory $ui_factory,
        protected ilLanguage $lng,
        protected HTTPServices $http_services
    ) {
    }

    public function getColumns(): array
    {
        $columns = [
            self::TABLE_COL_LOGIN => $this->ui_factory->table()->column()->text(
                $this->lng->txt(self::LNG_TABLE_COL_LOGIN)
            )->withHighlight(true),
            self::TABLE_COL_FIRSTNAME => $this->ui_factory->table()->column()->text(
                $this->lng->txt(self::LNG_TABLE_COL_FIRSTNAME)
            ),
            self::TABLE_COL_LASTNAME => $this->ui_factory->table()->column()->text(
                $this->lng->txt(self::LNG_TABLE_COL_LASTNAME)
            )
        ];

        foreach ($this->data_retrieval->getAnswers() as $answer) {
            $columns[self::TABLE_COL_ANSWER_PREFIX . (int) ($answer["id"] ?? 0)] = $this->ui_factory->table()->column()->statusIcon(
                (string) ($answer["answer"] ?? '')
            );
        }
        return $columns;
    }

    public function get(): DataTable
    {
        $title = $this->lng->txt("poll_question") . ": \"" . $this->data_retrieval->getPollQuestion() . "\"";
        return $this->ui_factory->table()->data(
            $this->data_retrieval,
            $title,
            $this->getColumns()
        )->withId(self::TABLE_ID)->withRequest($this->http_services->request());
    }
}
