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

namespace ILIAS\AdvancedMetaData\Types\Group\ComplexOptions\Table;

use ILIAS\UI\Factory as UIFactory;
use ILIAS\UI\Component\Table\Data as DataTable;
use ilLanguage;
use ILIAS\HTTP\Services as HTTP;
use ILIAS\UI\URLBuilder;
use ilAdvancedMDFieldDefinitionGroupBased;
use ilAdvancedMDSettingsGUI;
use ilCtrl;
use ILIAS\Data\URI;

/**
 * @ilCtrl_Calls ilAdvancedMDSettingsGUI: ilPropertyFormGUI
 */
class TableBuilder
{
    public function __construct(
        protected DataRetrieval $data_retrieval,
        protected ilLanguage $lng,
        protected UIFactory $ui_factory,
        protected HTTP $http,
        protected ilCtrl $ctrl,
        protected ilAdvancedMDFieldDefinitionGroupBased $definition
    ) {
    }

    public function get(): DataTable
    {
        $columns = [];
        $columns['option'] = $this->ui_factory->table()->column()->text(
            $this->lng->txt('option')
        )->withIsSortable(true);

        foreach ($this->definition->getTitles() as $element => $title) {
            $columns[$element] = $this->ui_factory->table()->column()->text(
                $title
            )->withIsSortable(true);
        }

        $actions = [];
        list($url_builder, $id_token) = $this->getTableActionURLBuilder();

        $actions['edit'] = $this->ui_factory->table()->action()->single(
            $this->lng->txt('edit'),
            $url_builder,
            $id_token
        );

        return $this->ui_factory->table()->data(
            $this->data_retrieval,
            $this->definition->getTitle(),
            $columns
        )->withActions($actions)->withRequest($this->http->request())
            ->withId('advanced_metadata_address_field_options_table');
    }

    public function getTableActionURLBuilder(): array
    {
        $link = ILIAS_HTTP_PATH . '/' . $this->ctrl->getLinkTargetByClass(
            ilAdvancedMDSettingsGUI::class,
            'editComplexOption'
        );
        $url_builder = new URLBuilder(new URI($link));

        return $url_builder->acquireParameters(['advmd'], 'oid_table');
    }
}
