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

namespace ILIAS\Calendar\Shared\Outgoing;

use ILIAS\UI\Factory as UIFactory;
use ILIAS\UI\Component\Table\Data as DataTable;
use ilLanguage;
use ILIAS\HTTP\Services as HTTP;
use ILIAS\UI\URLBuilder;
use ILIAS\UI\URLBuilderToken;
use ILIAS\Calendar\Shared\TableBuilderInterface;

class TableBuilder implements TableBuilderInterface
{
    public const string TYPE_COLUMN = 'type';
    public const string TITLE_COLUMN = 'title';
    public const string DESCRIPTION_COLUMN = 'description';
    public const string WRITABLE_COLUMN = 'writable';
    public const string DEASSIGN_ACTION = 'shareDeassign';

    public function __construct(
        protected DataRetrieval $data_retrieval,
        protected ilLanguage $lng,
        protected UIFactory $ui_factory,
        protected HTTP $http
    ) {
    }

    public function get(
        URLBuilder $url_builder,
        URLBuilderToken $id_token,
        URLBuilderToken $action_token
    ): DataTable {
        $columns = [];
        $columns[self::TYPE_COLUMN] = $this->ui_factory->table()->column()->statusIcon(
            $this->lng->txt('type')
        )->withIsSortable(true);
        $columns[self::TITLE_COLUMN] = $this->ui_factory->table()->column()->text(
            $this->lng->txt('title')
        )->withIsSortable(true);
        $columns[self::DESCRIPTION_COLUMN] = $this->ui_factory->table()->column()->text(
            $this->lng->txt('description')
        )->withIsSortable(false);
        $columns[self::WRITABLE_COLUMN] = $this->ui_factory->table()->column()->status(
            $this->lng->txt('cal_shared_access_table_col')
        )->withIsSortable(true);

        $actions = [];
        $actions[self::DEASSIGN_ACTION] = $this->ui_factory->table()->action()->standard(
            $this->lng->txt('cal_unshare_cal'),
            $url_builder->withParameter($action_token, self::DEASSIGN_ACTION),
            $id_token
        );

        return $this->ui_factory->table()->data(
            $this->data_retrieval,
            $this->lng->txt('cal_cal_shared_with'),
            $columns
        )->withActions($actions)->withRequest($this->http->request());
    }
}
