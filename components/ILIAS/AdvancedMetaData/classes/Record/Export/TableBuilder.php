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

namespace ILIAS\AdvancedMetaData\Record\Export;

use ILIAS\UI\Factory as UIFactory;
use ILIAS\UI\Component\Table\Data as DataTable;
use ilLanguage;
use ILIAS\HTTP\Services as HTTP;
use ILIAS\UI\URLBuilder;
use ILIAS\UI\URLBuilderToken;
use ilObjUser;
use ILIAS\Data\Factory as DataFactory;
use ilCalendarSettings;
use ilCalendarUserSettings;

class TableBuilder
{
    public const string COLUMN_RECORDS = 'records';
    public const string COLUMN_DATE = 'date';
    public const string COLUMN_FILE_SIZE = 'file_size';

    public const string ACTION_DOWNLOAD = 'downloadFile';
    public const string ACTION_DELETE = 'confirmDeleteFiles';

    public function __construct(
        protected DataRetrieval $data_retrieval,
        protected ilLanguage $lng,
        protected UIFactory $ui_factory,
        protected HTTP $http,
        protected ilObjUser $user,
        protected DataFactory $data_factory,
        protected bool $has_write_permission
    ) {
    }

    public function get(
        URLBuilder $url_builder,
        URLBuilderToken $id_token,
        URLBuilderToken $action_token
    ): DataTable {
        $date_format = $this->user->getDateFormat();
        if (ilCalendarUserSettings::_getInstanceByUserId($this->user->getId())->getTimeFormat() == ilCalendarSettings::TIME_FORMAT_24) {
            $date_format = $this->data_factory->dateFormat()->withTime24($date_format);
        } else {
            $date_format = $this->data_factory->dateFormat()->withTime12($date_format);
        }

        $columns = [];
        $columns[self::COLUMN_RECORDS] = $this->ui_factory->table()->column()->listing(
            $this->lng->txt('md_adv_records')
        )->withIsSortable(true);

        $columns[self::COLUMN_DATE] = $this->ui_factory->table()->column()->date(
            $this->lng->txt('date'),
            $date_format
        )->withIsSortable(true);

        $columns[self::COLUMN_FILE_SIZE] = $this->ui_factory->table()->column()->text(
            $this->lng->txt('filesize')
        )->withIsSortable(true);

        $actions = [];
        $actions[self::ACTION_DOWNLOAD] = $this->ui_factory->table()->action()->single(
            $this->lng->txt('download'),
            $url_builder->withParameter($action_token, self::ACTION_DOWNLOAD),
            $id_token
        );

        if ($this->has_write_permission) {
            $actions[self::ACTION_DELETE] = $this->ui_factory->table()->action()->standard(
                $this->lng->txt('delete'),
                $url_builder->withParameter($action_token, self::ACTION_DELETE),
                $id_token
            );
        }

        return $this->ui_factory->table()->data(
            $this->data_retrieval,
            $this->lng->txt('md_record_export_table'),
            $columns
        )
            ->withActions($actions)
            ->withRequest($this->http->request());
    }
}
