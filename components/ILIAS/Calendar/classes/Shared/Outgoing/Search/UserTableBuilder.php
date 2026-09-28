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

namespace ILIAS\Calendar\Shared\Outgoing\Search;

use ILIAS\UI\Factory as UIFactory;
use ILIAS\UI\Component\Table\Data as DataTable;
use ilLanguage;
use ILIAS\HTTP\Services as HTTP;
use ILIAS\UI\URLBuilder;
use ILIAS\UI\URLBuilderToken;
use ILIAS\Calendar\Shared\TableBuilderInterface;

class UserTableBuilder implements TableBuilderInterface
{
    public const string NAME_COLUMN = 'name';
    public const string LOGIN_COLUMN = 'login';
    public const string SHARE_READ_ONLY_ACTION = 'shareAssign';
    public const string SHARE_EDITABLE_ACTION = 'shareAssignEditable';

    public function __construct(
        protected UserDataRetrieval $data_retrieval,
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
        $columns[self::NAME_COLUMN] = $this->ui_factory->table()->column()->text(
            $this->lng->txt('name')
        )->withIsSortable(true);
        $columns[self::LOGIN_COLUMN] = $this->ui_factory->table()->column()->text(
            $this->lng->txt('login')
        )->withIsSortable(true);

        $actions = [];
        $actions[self::SHARE_READ_ONLY_ACTION] = $this->ui_factory->table()->action()->standard(
            $this->lng->txt('cal_share_cal'),
            $url_builder->withParameter($action_token, self::SHARE_READ_ONLY_ACTION),
            $id_token
        );
        $actions[self::SHARE_EDITABLE_ACTION] = $this->ui_factory->table()->action()->standard(
            $this->lng->txt('cal_share_cal_editable'),
            $url_builder->withParameter($action_token, self::SHARE_EDITABLE_ACTION),
            $id_token
        );


        return $this->ui_factory->table()->data(
            $this->data_retrieval,
            $this->lng->txt('cal_share_search_usr_header'),
            $columns
        )->withActions($actions)->withRequest($this->http->request());
    }
}
