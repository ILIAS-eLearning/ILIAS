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

namespace ILIAS\Test\Table;

use DateTimeImmutable;
use ilCtrl;
use ilCtrlException;
use ILIAS\UI\Component\Table\Action\Action;
use ILIAS\UI\Component\Table\Data;
use ILIAS\UI\Factory as UIFactory;
use ILIAS\Data\Factory as DataFactory;
use ILIAS\UI\URLBuilder;
use ilLanguage;
use ilLegacyFormElementsUtil;
use TestPersonalDefaultSettingsGUI;

class TestPersonalDefaultSettingsTable extends TestTable
{
    public function __construct(
        private readonly ilLanguage $lng,
        private readonly UIFactory $ui_factory,
        private readonly ilCtrl $ctrl,
        private readonly TestPersonalDefaultSettingsGUI $gui,
        private readonly int $parent_obj_id,
        private readonly array $row_data
    ) {
    }

    protected function collectRecords(?array $filter_data, ?array $additional_parameters): array
    {
        $data = [];
        foreach ($this->row_data as $record) {
            $data[] = [
                'test_defaults_id' => $record['test_defaults_id'],
                'name' => $record['name'],
                'checkbox' => ilLegacyFormElementsUtil::formCheckbox(false, 'chb_defaults[]', (string) $record['test_defaults_id']),
                'tstamp' => $record['tstamp']
            ];
        }
        return $data;
    }


    protected function transformRecord(string $row_id, array $record): array
    {
        $record['tstamp'] = (new DateTimeImmutable())->setTimestamp($record['tstamp']);

        return $record;
    }

    /**
     * @throws ilCtrlException
     */
    public function getComponent(): Data
    {
        return $this->ui_factory
            ->table()
            ->data($this->lng->txt('tst_defaults_available'), $this->getColumns(), $this)
            ->withId('tst_pers_def_set_' . $this->parent_obj_id)
            ->withActions($this->getActions());
    }

    protected function getColumns(): array
    {
        $column_factory = $this->ui_factory->table()->column();
        $data_factory = new DataFactory();
        $dateFormat = $data_factory->dateFormat()->withTime24($data_factory->dateFormat()->germanShort());

        return [
            'name' => $column_factory->text($this->lng->txt('title')),
            'tstamp' => $column_factory->date($this->lng->txt('date'), $dateFormat)
        ];
    }

    /**
     * @throws ilCtrlException
     */
    protected function getActions(): array
    {
        return [
            TestPersonalDefaultSettingsGUI::DELETE_CMD => $this->buildAction(TestPersonalDefaultSettingsGUI::DELETE_CMD, 'delete', ['ids']),
            TestPersonalDefaultSettingsGUI::APPLY_CMD => $this->buildAction(TestPersonalDefaultSettingsGUI::APPLY_CMD, 'apply_def_settings_to_tst', ['ids'], true)
        ];
    }

    protected function getRowID(array $record): string
    {
        return (string) $record['test_defaults_id'];
    }

    /**
     * @throws ilCtrlException
     */
    private function buildAction(string $act, string $lang_var, array $query_parameters = [], bool $single = false, bool $multi = false, bool $async = false): Action
    {
        $data_factory = new DataFactory();

        [
            $url_builder,
            $action_parameter_token,
            $row_id_token,
        ] = (new URLBuilder(
            $data_factory->uri(ILIAS_HTTP_PATH . '/' . $this->ctrl->getLinkTarget($this->gui, $act))
        ))->acquireParameters(
            ['test_defaults'],
            $act,
            ...$query_parameters,
        );

        $actionType = $single ? 'single' : ($multi ? 'multi' : 'standard');

        return $this
            ->ui_factory
            ->table()
            ->action()
            ->$actionType(
                $this->lng->txt($lang_var),
                $url_builder->withParameter($action_parameter_token, $act),
                $row_id_token,
            )
            ->withAsync($async)
        ;
    }
}
