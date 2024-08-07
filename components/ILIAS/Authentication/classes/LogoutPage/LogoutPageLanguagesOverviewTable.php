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

namespace ILIAS\Authentication\LogoutPage;

use ILIAS\Data;
use ILIAS\UI;
use ilArrayUtil;
use Psr\Http\Message\ServerRequestInterface;
use ilAuthLoginPageEditorSettings;
use ilLanguage;
use ilCtrl;
use ilAuthLogoutPageEditorSettings;

class LogoutPageLanguagesOverviewTable implements UI\Component\Table\DataRetrieval
{
    protected ServerRequestInterface $request;
    protected Data\Factory $data_factory;
    /**
     * @var list<array<string, mixed>>|null
     */
    private ?array $records = null;

    public function __construct(
        private readonly ilCtrl $ctrl,
        private readonly ilLanguage $lng,
        \ILIAS\HTTP\Services $http,
        private readonly \ILIAS\UI\Factory $ui_factory,
        private readonly \ILIAS\UI\Renderer $ui_renderer
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
            ->data($this->lng->txt('logout_pages'), $columns, $this)
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
                ->text($this->lng->txt('logout_page'))
                ->withIsSortable(false),
            'status_icon' => $this->ui_factory
                ->table()
                ->column()
                ->text($this->lng->txt('active'))
                ->withIsSortable(false)
        ];
    }

    /**
     * @return array<string, \ILIAS\UI\Component\Table\Action\Action>
     */
    protected function getActions(): array
    {
        $query_params_namespace = ['logoutpage', 'languages'];

        $overview_uri = $this->data_factory->uri(
            ILIAS_HTTP_PATH . '/' . $this->ctrl->getLinkTargetByClass(
                \ilAuthLogoutPageEditorGUI::class,
                'handleLogoutPageActions'
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
            'edit' => $this->ui_factory->table()->action()->single(
                $this->lng->txt('edit'),
                $overview_url_builder->withParameter($overview_action_parameter, 'edit'),
                $overview_row_id
            ),
            'activate' => $this->ui_factory->table()->action()->standard(
                $this->lng->txt('page_design_activate'),
                $overview_url_builder->withParameter($overview_action_parameter, 'activate'),
                $overview_row_id
            ),
            'deactivate' => $this->ui_factory->table()->action()->standard(
                $this->lng->txt('page_design_deactivate'),
                $overview_url_builder->withParameter($overview_action_parameter, 'deactivate'),
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
                $status = ilAuthLogoutPageEditorSettings::getInstance()->isIliasEditorEnabled(
                    $langkey
                );

                $this->records[$i]['status_icon'] = $this->getStatusIcon($status);
                $this->records[$i]['status'] = $status;
                $this->records[$i]['language'] = $this->lng->txt('meta_l_' . $langkey);

                ++$i;
            }
        }
    }

    private function getStatusIcon(bool $status): string
    {
        if ($status) {
            $icon = $this->ui_renderer->render(
                $this->ui_factory->symbol()->icon()->custom(
                    \ilUtil::getImagePath('standard/icon_ok.svg'),
                    $this->lng->txt('active')
                )
            );
        } else {
            $icon = $this->ui_renderer->render(
                $this->ui_factory->symbol()->icon()->custom(
                    \ilUtil::getImagePath('standard/icon_not_ok.svg'),
                    $this->lng->txt('inactive')
                )
            );
        }

        return $icon;
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
            $deactivate_action = (bool) $record['status'] == true ? 'activate' : 'deactivate';
            yield $row_builder->buildDataRow($row_id, $record)->withDisabledAction($deactivate_action);
        }
    }

    public function getTotalRowCount(
        ?array $filter_data,
        ?array $additional_parameters
    ): ?int {
        $this->initRecords();

        return count($this->records);
    }

    /**
     * @return list<array<string, mixed>>
     */
    private function sortedRecords(Data\Order $order): array
    {
        $records = $this->records;
        [$order_field, $order_direction] = $order->join([], fn($ret, $key, $value) => [$key, $value]);

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
        return array_slice($records, $range->getStart(), $range->getLength());
    }
}