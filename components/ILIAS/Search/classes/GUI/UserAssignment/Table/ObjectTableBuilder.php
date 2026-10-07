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

namespace ILIAS\Search\GUI\UserAssignment\Table;

use ILIAS\UI\Factory as UIFactory;
use ILIAS\UI\Component\Table\Data as DataTable;
use ilLanguage;
use ilObjUser;
use ILIAS\HTTP\Services as HTTP;
use ILIAS\UI\URLBuilder;
use ILIAS\UI\URLBuilderToken;

class ObjectTableBuilder
{
    public const string TITLE_COLUMN = 'title';
    public const string MEMBER_COUNT_COLUMN = 'count';
    public const string LIST_USERS_ACTION = 'listUsers';
    public const string ADD_ROLE_ACTION = 'addRole';

    public function __construct(
        protected string $search_type,
        protected bool $has_add_role_action,
        protected ObjectDataRetrieval $data_retrieval,
        protected ilLanguage $lng,
        protected UIFactory $ui_factory,
        protected HTTP $http,
        protected ilObjUser $user
    ) {
    }

    public function get(
        URLBuilder $url_builder,
        URLBuilderToken $id_token,
        URLBuilderToken $action_token
    ): DataTable {
        $columns = [];
        $columns[self::TITLE_COLUMN] =
            $this->ui_factory->table()->column()->text($this->lng->txt('title'))->withIsSortable(true);
        $columns[self::MEMBER_COUNT_COLUMN] =
            $this->ui_factory->table()->column()->number($this->lng->txt('members'))->withIsSortable(true);

        $actions = [];
        $actions[self::LIST_USERS_ACTION] = $this->ui_factory->table()->action()->multi(
            $this->lng->txt('grp_list_members'),
            $url_builder->withParameter($action_token, self::LIST_USERS_ACTION),
            $id_token
        );
        if ($this->has_add_role_action) {
            $actions[self::ADD_ROLE_ACTION] = $this->ui_factory->table()->action()->multi(
                $this->search_type === 'role' ? $this->lng->txt('add_role') : $this->lng->txt('add_member_role'),
                $url_builder->withParameter($action_token, self::ADD_ROLE_ACTION),
                $id_token
            );
        }

        return $this->ui_factory->table()->data(
            $this->data_retrieval,
            $this->lng->txt('search_results'),
            $columns
        )->withId('rep_search_obj_' . $this->user->getId())
         ->withActions($actions)
         ->withRequest($this->http->request());
    }
}
