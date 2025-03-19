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

namespace ILIAS\components\Authentication\Pages;

use ILIAS\Data;
use ILIAS\UI;
use ilArrayUtil;
use Psr\Http\Message\ServerRequestInterface;
use ilLanguage;
use ilCtrlInterface;
use ilAuthPageEditorSettings;

class AuthPageLanguagesOverviewTable implements UI\Component\Table\DataRetrieval
{
    public const ACTIVATE = 'activate';
    public const DEACTIVATE = 'deactivate';
    public const EDIT = 'edit';

    private ServerRequestInterface $request;
    private Data\Factory $data_factory;
    /**
     * @var list<array<string, mixed>>|null
     */
    private ?array $records = null;

    public function __construct(
        private readonly ilCtrlInterface $ctrl,
        private readonly ilLanguage $lng,
        \ILIAS\HTTP\Services $http,
        private readonly \ILIAS\UI\Factory $ui_factory,
        private readonly \ILIAS\UI\Renderer $ui_renderer,
        private readonly AuthPageEditorContext $context
    ) {
        $this->request = $http->request();
        $this->data_factory = new Data\Factory();
    }

    public function getComponent(): UI\Component\Table\Data
    {
        $columns = $this->getColumns();
        $actions = $this->getActions();

        return $this->ui_factory
            ->table()
            ->data($this, $this->lng->txt($this->context->pageLanguageIdentifier(true)), $columns)
            ->withId(self::class . '_' . $this->context->value)
            ->withOrder(new \ILIAS\Data\Order('language', \ILIAS\Data\Order::ASC))
            ->withActions($actions)
            ->withRequest($this->request);
    }

    /**
     * @return array<string, \ILIAS\UI\Component\Table\Column\Column>
     */
    private function getColumns(): array
    {
        return [
            'language' => $this->ui_factory
                ->table()
                ->column()
                ->text($this->lng->txt($this->context->pageLanguageIdentifier()))
                ->withIsSortable(true),
            'status_icon' => $this->ui_factory
                ->table()
                ->column()
                ->statusIcon($this->lng->txt('active'))
                ->withIsSortable(true)
        ];
    }

    /**
     * @return array<string, \ILIAS\UI\Component\Table\Action\Action>
     */
    protected function getActions(): array
    {
        $query_params_namespace = ['authpage', 'languages'];

        $overview_uri = $this->data_factory->uri(
            ILIAS_HTTP_PATH . '/' . $this->ctrl->getLinkTargetByClass(
                \ilAuthPageEditorGUI::class,
                \ilAuthPageEditorGUI::LANGUAGE_TABLE_ACTIONS_COMMAND
            )
        );

        $overview_url_builder = new UI\URLBuilder($overview_uri);
        [
            $overview_url_builder,
            $overview_action_parameter,
            $overview_row_id
        ] = $overview_url_builder->acquireParameters(
            $query_params_namespace,
            'action',
            'key'
        );

        return [
            self::EDIT => $this->ui_factory->table()->action()->single(
                $this->lng->txt('edit'),
                $overview_url_builder->withParameter($overview_action_parameter, self::EDIT),
                $overview_row_id
            ),
            self::ACTIVATE => $this->ui_factory->table()->action()->standard(
                $this->lng->txt('page_design_activate'),
                $overview_url_builder->withParameter($overview_action_parameter, self::ACTIVATE),
                $overview_row_id
            ),
            self::DEACTIVATE => $this->ui_factory->table()->action()->standard(
                $this->lng->txt('page_design_deactivate'),
                $overview_url_builder->withParameter($overview_action_parameter, self::DEACTIVATE),
                $overview_row_id
            )
        ];
    }

    private function initRecords(): void
    {
        if ($this->records === null) {
            $this->records = [];
            $i = 0;
            $entries = $this->lng->getInstalledLanguages();
            foreach ($entries as $langkey) {
                $this->records[$i]['key'] = $langkey;
                $this->records[$i]['id'] = ilLanguage::lookupId($langkey);
                $status = ilAuthPageEditorSettings::getInstance($this->context)->isIliasEditorEnabled(
                    $langkey
                );

                $this->records[$i]['status'] = $status;
                $this->records[$i]['status_icon'] = $this->getStatusIcon($status);
                $this->records[$i]['language'] = $this->lng->txt('meta_l_' . $langkey);

                ++$i;
            }
        }
    }

    private function getStatusIcon(bool $status): \ILIAS\UI\Component\Symbol\Icon\Icon
    {
        if ($status) {
            return $this->ui_factory->symbol()->icon()->custom(
                \ilUtil::getImagePath('standard/icon_ok.svg'),
                $this->lng->txt('active')
            );
        }

        return $this->ui_factory->symbol()->icon()->custom(
            \ilUtil::getImagePath('standard/icon_not_ok.svg'),
            $this->lng->txt('inactive')
        );
    }

    public function getRows(
        UI\Component\Table\DataRowBuilder $row_builder,
        array $visible_column_ids,
        Data\Range $range,
        Data\Order $order,
        ?array $filter_data,
        ?array $additional_parameters
    ): \Generator {
        $records = $this->getRecords($range, $order);

        foreach ($records as $record) {
            $row_id = (string) $record['key'];
            $deactivate_action = (bool) $record['status'] === true ? self::ACTIVATE : self::DEACTIVATE;
            yield $row_builder->buildDataRow($row_id, $record)->withDisabledAction($deactivate_action);
        }
    }

    public function getTotalRowCount(
        ?array $filter_data,
        ?array $additional_parameters
    ): ?int {
        $this->initRecords();

        return \count($this->records);
    }

    /**
     * @return list<array<string, mixed>>
     */
    private function sortedRecords(Data\Order $order): array
    {
        $records = $this->records;
        [$order_field, $order_direction] = $order->join([], fn($ret, $key, $value) => [$key, $value]);

        if ($order_field === 'status_icon') {
            $order_field = 'status';
        }

        return ilArrayUtil::stableSortArray($records, $order_field, strtolower($order_direction));
    }

    /**
     * @return list<array<string, mixed>>
     */
    private function getRecords(Data\Range $range, Data\Order $order): array
    {
        $this->initRecords();

        $records = $this->sortedRecords($order);

        return $this->limitRecords($records, $range);
    }

    /**
     * @param list<array<string, mixed>> $records
     * @return list<array<string, mixed>>
     */
    private function limitRecords(array $records, Data\Range $range): array
    {
        return \array_slice($records, $range->getStart(), $range->getLength());
    }
}
