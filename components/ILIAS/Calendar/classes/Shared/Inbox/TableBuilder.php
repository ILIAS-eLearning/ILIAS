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

namespace ILIAS\Calendar\Shared\Inbox;

use ILIAS\UI\Factory as UIFactory;
use ILIAS\UI\Component\Table\Data as DataTable;
use ilLanguage;
use ILIAS\HTTP\Services as HTTP;
use ILIAS\UI\URLBuilder;
use ILIAS\UI\URLBuilderToken;
use ILIAS\Calendar\Shared\TableBuilderInterface;
use ilObjUser;
use ILIAS\Data\Factory as DataFactory;
use ilCalendarSettings;

class TableBuilder implements TableBuilderInterface
{
    public const string NAME_COLUMN = 'name';
    public const string OWNER_COLUMN = 'owner';
    public const string APPOINTMENTS_COUNT_COLUMN = 'appointments_count';
    public const string CREATED_ON_COLUMN = 'created_on';
    public const string ACCEPT_INVITATION_ACTION = 'acceptShared';
    public const string DECLINE_INVITATION_ACTION = 'declineShared';

    public function __construct(
        protected DataRetrieval $data_retrieval,
        protected ilLanguage $lng,
        protected UIFactory $ui_factory,
        protected HTTP $http,
        protected ilObjUser $user,
        protected DataFactory $data_factory
    ) {
    }

    public function get(
        URLBuilder $url_builder,
        URLBuilderToken $id_token,
        URLBuilderToken $action_token
    ): DataTable {
        $date_format = $this->user->getDateFormat();
        if ($this->user->getTimeFormat() == ilCalendarSettings::TIME_FORMAT_24) {
            $date_format = $this->data_factory->dateFormat()->withTime24($date_format);
        } else {
            $date_format = $this->data_factory->dateFormat()->withTime12($date_format);
        }

        $columns = [];
        $columns[self::NAME_COLUMN] = $this->ui_factory->table()->column()->text(
            $this->lng->txt('name')
        )->withIsSortable(true);
        $columns[self::OWNER_COLUMN] = $this->ui_factory->table()->column()->text(
            $this->lng->txt('owner')
        )->withIsSortable(true);
        $columns[self::APPOINTMENTS_COUNT_COLUMN] = $this->ui_factory->table()->column()->number(
            $this->lng->txt('cal_apps')
        )->withIsSortable(true);
        $columns[self::CREATED_ON_COLUMN] = $this->ui_factory->table()->column()->date(
            $this->lng->txt('create_date'),
            $date_format
        )->withIsSortable(true);

        $actions = [];
        $actions[self::ACCEPT_INVITATION_ACTION] = $this->ui_factory->table()->action()->standard(
            $this->lng->txt('cal_share_accept'),
            $url_builder->withParameter($action_token, self::ACCEPT_INVITATION_ACTION),
            $id_token
        );
        $actions[self::DECLINE_INVITATION_ACTION] = $this->ui_factory->table()->action()->standard(
            $this->lng->txt('cal_share_decline'),
            $url_builder->withParameter($action_token, self::DECLINE_INVITATION_ACTION),
            $id_token
        );

        return $this->ui_factory->table()->data(
            $this->data_retrieval,
            $this->lng->txt('cal_shared_calendars'),
            $columns
        )->withActions($actions)->withRequest($this->http->request());
    }
}
