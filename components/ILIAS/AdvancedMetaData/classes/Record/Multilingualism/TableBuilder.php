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

namespace ILIAS\AdvancedMetaData\Record\Multilingualism;

use ILIAS\UI\Factory as UIFactory;
use ILIAS\UI\Component\Table\Data as DataTable;
use ilLanguage;
use ILIAS\HTTP\Services as HTTP;
use ILIAS\UI\URLBuilder;
use ILIAS\UI\URLBuilderToken;
use ilCtrl;
use ILIAS\Data\URI;
use ILIAS\UI\Component\Symbol\Icon\Icon;
use ILIAS\UI\Component\Symbol\Glyph\Glyph;
use ilAdvancedMDRecordTranslationGUI;
use ILIAS\Data\Range;
use ILIAS\Data\Factory as DataFactory;


class TableBuilder
{
    public const string COL_LANGUAGE = 'language';
    public const string COL_DEFAULT = 'default';
    public const string COL_ACTIVE = 'active';
    public const string COL_INSTALLED = 'installed';

    public const string ACTION_ACTIVATE = 'activate';
    public const string ACTION_DEACTIVATE = 'deactivate';
    public const string ACTION_SET_DEFAULT = 'setDefault';

    public function __construct(
        protected DataRetrieval $data_retrieval,
        protected ilLanguage $lng,
        protected UIFactory $ui_factory,
        protected HTTP $http,
        protected ilCtrl $ctrl,
        protected DataFactory $data_factory
    ) {
    }

    public function get(): DataTable
    {
        [$url_builder, $id_token, $action_token] = $this->getTableActionURLBuilder();

        $columns = [];
        $columns[self::COL_LANGUAGE] = $this->ui_factory->table()->column()->text(
            $this->lng->txt('md_adv_record_lng_table_lng')
        )->withIsSortable(true);

        $icon_ok = $this->ui_factory->symbol()->icon()->custom('assets/images/standard/icon_ok.svg', 'ok');
        $icon_not_ok = $this->ui_factory->symbol()->icon()->custom('assets/images/standard/icon_not_ok.svg', 'not ok');

        $columns[self::COL_DEFAULT] = $this->ui_factory->table()->column()->boolean(
            $this->lng->txt('md_adv_record_lng_table_default'),
            $icon_ok,
            $icon_not_ok
        );

        $columns[self::COL_ACTIVE] = $this->ui_factory->table()->column()->boolean(
            $this->lng->txt('md_adv_record_lng_table_active'),
            $icon_ok,
            $icon_not_ok
        );

        $columns[self::COL_INSTALLED] = $this->ui_factory->table()->column()->boolean(
            $this->lng->txt('md_adv_record_lng_table_inst'),
            $icon_ok,
            $icon_not_ok
        );

        $actions = [];
        $actions[self::ACTION_ACTIVATE] = $this->ui_factory->table()->action()->standard(
            $this->lng->txt('md_adv_record_lng_table_activate'),
            $url_builder->withParameter($action_token, self::ACTION_ACTIVATE),
            $id_token
        );

        $actions[self::ACTION_DEACTIVATE] = $this->ui_factory->table()->action()->standard(
            $this->lng->txt('md_adv_record_lng_table_deactivate'),
            $url_builder->withParameter($action_token, self::ACTION_DEACTIVATE),
            $id_token
        );

        $actions[self::ACTION_SET_DEFAULT] = $this->ui_factory->table()->action()->single(
            $this->lng->txt('md_adv_record_lng_table_set_default'),
            $url_builder->withParameter($action_token, self::ACTION_SET_DEFAULT),
            $id_token
        );

        return $this->ui_factory->table()->data(
            $this->data_retrieval,
            $this->lng->txt('md_adv_record_lng_table'),
            $columns
        )->withActions($actions)
            ->withRequest($this->http->request())
            ->withId('advanced_metadata_record_translations_table');
    }

    public function getTableActionURLBuilder(): array
    {
        $link = ILIAS_HTTP_PATH . '/' . $this->ctrl->getLinkTargetByClass(
            ilAdvancedMDRecordTranslationGUI::class,
            'handleTableAction'
        );
        $url_builder = new URLBuilder($this->data_factory->uri($link));

        return $url_builder->acquireParameters(['advmd_ml'], 'table_ids', 'table_action');
    }
}
