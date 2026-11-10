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

namespace AdvancedMetadata\Field\Table;

use ILIAS\UI\Factory as UIFactory;
use ILIAS\UI\Component\Table\Ordering as OrderingTable;
use ilLanguage;
use ILIAS\HTTP\Services as HTTP;
use ILIAS\UI\URLBuilder;
use ILIAS\UI\URLBuilderToken;
use ILIAS\Data\Factory as DataFactory;

class TableBuilder
{
    public const string TITLE_COLUMN = 'title';
    public const string TYPE_COLUMN = 'type';
    public const string CONFIGURATION_COLUMN = 'configuration';
    public const string SEARCHABLE_COLUMN = 'searchable';

    public const string EDIT_FIELD_ACTION = 'editField';
    public const string DELETE_ACTION = 'confirmDeleteFields';
    public const string SAVE_ACTION = 'updateFieldPositions'; // action for reordering

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
    ): OrderingTable {
        $has_write_access = $this->data_retrieval->hasWriteAccess();
        $has_position_access = $this->data_retrieval->hasPositionAccess();

        $columns = [];
        $columns[self::TITLE_COLUMN] = $this->ui_factory->table()->column()->text(
            $this->lng->txt('title')
        )->withIsSortable(true);
        $columns[self::TYPE_COLUMN] = $this->ui_factory->table()->column()->text(
            $this->lng->txt('type')
        );
        $columns[self::CONFIGURATION_COLUMN] = $this->ui_factory->table()->column()->listing(
            $this->lng->txt('configuration')
        );
        $columns[self::SEARCHABLE_COLUMN] = $this->ui_factory->table()->column()->boolean(
            $this->lng->txt('md_adv_searchable'),
            $this->ui_factory->symbol()->icon()->custom('assets/images/standard/icon_ok.svg', '', 'small'),
            $this->ui_factory->symbol()->icon()->custom('assets/images/standard/icon_not_ok.svg', '', 'small')
        );

        $actions = [];
        $actions[self::EDIT_FIELD_ACTION] = $this->ui_factory->table()->action()->single(
            $this->lng->txt('edit'),
            $url_builder->withParameter($action_token, self::EDIT_FIELD_ACTION),
            $id_token
        );

        $actions[self::DELETE_ACTION] = $this->ui_factory->table()->action()->multi(
            $this->lng->txt('delete'),
            $url_builder->withParameter($action_token, self::DELETE_ACTION),
            $id_token
        );

        $target = $url_builder->withParameter($action_token, self::SAVE_ACTION)->buildURI();
        $table = $this->ui_factory->table()->ordering(
            $this->data_retrieval,
            $target,
            $this->lng->txt('md_adv_field_table'),
            $columns
        )->withRequest($this->http->request());

        if (count($actions) > 0 && $has_write_access) {
            $table = $table->withActions($actions);
        }

        if (!$has_position_access) {
            $table = $table->withOrderingDisabled(true);
        }

        return $table;
    }
}
