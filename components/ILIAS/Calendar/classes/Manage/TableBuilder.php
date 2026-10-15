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

namespace ILIAS\Calendar\Manage;

use ILIAS\UI\Factory as UIFactory;
use ILIAS\UI\Component\Table\Data as DataTable;
use ilLanguage;
use ILIAS\HTTP\Services as HTTP;
use ILIAS\UI\URLBuilder;
use ILIAS\UI\URLBuilderToken;

class TableBuilder
{
    public const string TYPE_COLUMN = 'type';
    public const string TITLE_COLUMN = 'title';

    public const string ACTION_EDIT = 'edit';
    public const string ACTION_IMPORT = 'import';
    public const string ACTION_SHARE = 'share';
    public const string ACTION_SYNCHRONIZE = 'synchronize';
    public const string ACTION_DELETE = 'delete';

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

        $actions = [];
        $actions[self::ACTION_EDIT] = $this->ui_factory->table()->action()->standard(
            $this->lng->txt('settings'),
            $url_builder->withParameter($action_token, self::ACTION_EDIT),
            $id_token
        );
        $actions[self::ACTION_IMPORT] = $this->ui_factory->table()->action()->standard(
            $this->lng->txt('cal_import_appointments'),
            $url_builder->withParameter($action_token, self::ACTION_IMPORT),
            $id_token
        );
        $actions[self::ACTION_SHARE] = $this->ui_factory->table()->action()->standard(
            $this->lng->txt('cal_share'),
            $url_builder->withParameter($action_token, self::ACTION_SHARE),
            $id_token
        );
        $actions[self::ACTION_SYNCHRONIZE] = $this->ui_factory->table()->action()->standard(
            $this->lng->txt('cal_cal_synchronize'),
            $url_builder->withParameter($action_token, self::ACTION_SYNCHRONIZE),
            $id_token
        );
        $actions[self::ACTION_DELETE] = $this->ui_factory->table()->action()->standard(
            $this->lng->txt('delete'),
            $url_builder->withParameter($action_token, self::ACTION_DELETE),
            $id_token
        );

        return $this->ui_factory->table()->data(
            $this->data_retrieval,
            $this->lng->txt('cal_manage'),
            $columns
        )->withActions($actions)->withRequest($this->http->request())->withId('calmng');
    }
}
