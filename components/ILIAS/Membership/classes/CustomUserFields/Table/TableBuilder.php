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

namespace ILIAS\Membership\CustomUserFields\Table;

use ILIAS\UI\Factory as UIFactory;
use ILIAS\UI\Component\Table\Data as DataTable;
use ilLanguage;
use ILIAS\HTTP\Services as HTTP;
use ILIAS\UI\URLBuilder;
use ilCtrl;
use ILIAS\Data\URI;
use ilObject;
use ILIAS\UI\URLBuilderToken;

class TableBuilder
{
    public const string COL_NAME = 'name';
    public const string COL_TYPE = 'type';
    public const string COL_REQUIRED = 'required';

    public function __construct(
        protected DataRetrieval $data_retrieval,
        protected ilLanguage $lng,
        protected UIFactory $ui_factory,
        protected HTTP $http,
        protected ilCtrl $ctrl,
        protected int $obj_id
    ) {
    }

    public function get(
        URLBuilder $url_builder,
        URLBuilderToken $id_token,
        URLBuilderToken $cmd_token
    ): DataTable {
        $columns = [];
        $columns[self::COL_NAME] = $this->ui_factory->table()->column()->text(
            $this->lng->txt('ps_cdf_name')
        )->withIsSortable(true);

        $columns[self::COL_TYPE] = $this->ui_factory->table()->column()->text(
            $this->lng->txt('ps_cdf_type')
        )->withIsSortable(true);

        $columns[self::COL_REQUIRED] = $this->ui_factory->table()->column()->boolean(
            $this->lng->txt('ps_cdf_required'),
            $this->ui_factory->symbol()->icon()->custom('assets/images/standard/icon_ok.svg', '', 'small'),
            $this->ui_factory->symbol()->icon()->custom('assets/images/standard/icon_not_ok.svg', '', 'small')
        )->withIsSortable(true);

        $actions = [];

        $actions['edit'] = $this->ui_factory->table()->action()->single(
            $this->lng->txt('edit'),
            $url_builder->withParameter($cmd_token, 'editField'),
            $id_token
        );

        $actions['delete'] = $this->ui_factory->table()->action()->standard(
            $this->lng->txt('delete'),
            $url_builder->withParameter($cmd_token, 'confirmDeleteFields'),
            $id_token
        );

        return $this->ui_factory->table()->data(
            $this->data_retrieval,
            $this->lng->txt(ilObject::_lookupType($this->obj_id) . '_custom_user_fields_table_title'),
            $columns
        )->withActions($actions)->withRequest($this->http->request())
            ->withId('membership_custom_user_fields_table');
    }
}
