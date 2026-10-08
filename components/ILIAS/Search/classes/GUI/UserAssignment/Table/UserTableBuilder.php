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
use ilUserSearchOptions;
use ilRbacReview;
use ILIAS\HTTP\Services as HTTP;
use ILIAS\UI\URLBuilder;
use ILIAS\UI\URLBuilderToken;

class UserTableBuilder
{
    public const string ADD_USER_ACTION = 'addUser';
    public const string ADD_FOR_OPTION_ACTION = 'addUserForOption';

    public function __construct(
        protected bool $admin_mode,
        protected array $add_options,
        protected UserDataRetrieval $data_retrieval,
        protected ilLanguage $lng,
        protected UIFactory $ui_factory,
        protected HTTP $http,
        protected ilObjUser $user,
        protected ilRbacReview $review
    ) {
    }

    public function get(
        URLBuilder $url_builder,
        URLBuilderToken $id_token,
        URLBuilderToken $action_token,
        URLBuilderToken $add_option_token
    ): DataTable {
        $columns = [];
        $selectable_columns = $this->getSelectableColumns($this->admin_mode);
        foreach ($selectable_columns as $column_key => $column_info) {
            $initially_visible = in_array($column_key, ['login', 'firstname', 'lastname']);
            if ($column_key === 'login' && $this->admin_mode) {
                $columns[$column_key] = $this->ui_factory->table()->column()->link($column_info['txt'] ?? '')
                                                         ->withIsSortable(true)
                                                         ->withIsOptional(true, $initially_visible);
                continue;
            }
            $columns[$column_key] = $this->ui_factory->table()->column()->text($column_info['txt'] ?? '')
                                                     ->withIsSortable(true)
                                                     ->withIsOptional(true, $initially_visible);
        }

        $actions = [];
        foreach ($this->add_options as $add_option_id => $add_option_label) {
            $add_action_url_builder = $url_builder->withParameter($action_token, self::ADD_FOR_OPTION_ACTION)
                                                  ->withParameter($add_option_token, (string) $add_option_id);
            $actions[] = $this->ui_factory->table()->action()->multi(
                $add_option_label,
                $add_action_url_builder,
                $id_token
            );
        }
        if ($actions === []) {
            $actions[] = $this->ui_factory->table()->action()->multi(
                $this->lng->txt('btn_add'),
                $url_builder->withParameter($action_token, self::ADD_USER_ACTION),
                $id_token
            );
        }

        return $this->ui_factory->table()->data(
            $this->data_retrieval,
            $this->lng->txt('search_results'),
            $columns
        )->withId('rep_search_' . $this->user->getId())
         ->withAdditionalParameters(array_keys($selectable_columns))
         ->withActions($actions)
         ->withRequest($this->http->request());
    }

    protected function getSelectableColumns(bool $admin_mode): array
    {
        $columns = ilUserSearchOptions::getSelectableColumnInfo($this->review->isAssigned($this->user->getId(), SYSTEM_ROLE_ID));
        if ($admin_mode) {
            // #11293
            $columns['access_until'] = ['txt' => $this->lng->txt('access_until')];
            $columns['last_login'] = ['txt' => $this->lng->txt('last_login')];
        }
        return $columns;
    }
}
