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

class RoleTableBuilder implements TableBuilderInterface
{
    public const string ROLE_COLUMN = 'role';
    public const string DESCRIPTION_COLUMN = 'description';
    public const string ASSIGNED_COLUMN = 'assigned';
    public const string SHARE_READ_ONLY_ACTION = 'shareAssignRoles';
    public const string SHARE_EDITABLE_ACTION = 'shareAssignRolesEditable';

    public function __construct(
        protected RoleDataRetrieval $data_retrieval,
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
        $columns[self::ROLE_COLUMN] = $this->ui_factory->table()->column()->text(
            $this->lng->txt('obj_role')
        )->withIsSortable(true);
        $columns[self::DESCRIPTION_COLUMN] = $this->ui_factory->table()->column()->text(
            $this->lng->txt('description')
        )->withIsSortable(false);
        $columns[self::ASSIGNED_COLUMN] = $this->ui_factory->table()->column()->number(
            $this->lng->txt('assigned_members')
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
            $this->lng->txt('cal_share_search_role_header'),
            $columns
        )->withActions($actions)->withRequest($this->http->request());
    }
}
