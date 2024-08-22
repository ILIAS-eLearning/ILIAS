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

namespace ILIAS\Contact\MemberSearch;

use ILIAS\Data;
use ILIAS\UI;
use Psr\Http\Message\ServerRequestInterface;
use ilArrayUtil;
use ilLanguage;
use ilCtrl;
use ilMailMemberSearchDataProvider;
use ILIAS\UI\Component\Table\Column\Column;

class MailMemberSearchTable implements UI\Component\Table\DataRetrieval
{
    private readonly ServerRequestInterface $request;
    private readonly Data\Factory $data_factory;
    /** @var list<array<string, mixed>>|null */
    private ?array $records = null;

    public function __construct(
        private readonly int $ref_id,
        private readonly ilMailMemberSearchDataProvider $provider,
        private readonly ilCtrl $ctrl,
        private readonly ilLanguage $lng,
        private readonly \ILIAS\UI\Factory $ui_factory,
        \ILIAS\HTTP\GlobalHttpState $http
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
            ->data(
                $this->lng->txt('members'),
                $columns,
                $this
            )
            ->withId(self::class . '_' . $this->ref_id)
            ->withActions($actions)
            ->withRequest($this->request);
    }

    /**
     * @return array<string, Column>
     */
    private function getColumns(): array
    {
        return [
            'login' => $this->ui_factory
                ->table()
                ->column()
                ->text($this->lng->txt('login'))
                ->withIsSortable(true),
            'name' => $this->ui_factory
                ->table()
                ->column()
                ->text($this->lng->txt('name'))
                ->withIsSortable(true),
            'role' => $this->ui_factory
                ->table()
                ->column()
                ->text($this->lng->txt('role'))
                ->withIsSortable(true),
        ];
    }

    /**
     * @return array<string, \ILIAS\UI\Component\Table\Action\Action>
     */
    private function getActions(): array
    {
        $query_params_namespace = ['contact', 'search', 'members'];

        $uri = $this->data_factory->uri(
            ILIAS_HTTP_PATH . '/' . $this->ctrl->getLinkTargetByClass(
                \ilMailMemberSearchGUI::class,
                'handleSearchMembersActions'
            )
        );

        $url_builder = new UI\URLBuilder($uri);
        [
            $url_builder,
            $action_parameter_token_copy,
            $row_id_token
        ] = $url_builder->acquireParameters(
            $query_params_namespace,
            'action',
            'user_ids'
        );

        return [
            'sendMailToSelectedUsers' => $this->ui_factory->table()->action()->multi(
                $this->lng->txt('mail_members'),
                $url_builder->withParameter($action_parameter_token_copy, 'sendMailToSelectedUsers'),
                $row_id_token
            ),
        ];
    }

    private function initRecords(): void
    {
        if ($this->records === null) {
            $this->records = [];
            $i = 0;
            $entries = $this->provider->getData();
            if ($entries !== []) {
                foreach ($entries as $entry) {
                    $this->records[$i]['user_id'] = (int) $entry['user_id'];
                    $this->records[$i]['login'] = $entry['login'];
                    $this->records[$i]['name'] = $entry['name'];
                    $this->records[$i]['role'] = $entry['role'];
                    ++$i;
                }
            }
        }
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
            $row_id = (string) $record['user_id'];
            yield $row_builder->buildDataRow($row_id, $record);
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
     * @return list<array<string, mixed>>array
     */
    private function sortedRecords(Data\Order $order): array
    {
        $records = $this->records;
        [$order_field, $order_direction] = $order->join([], fn($ret, $key, $value) => [$key, $value]);

        return ilArrayUtil::stableSortArray($records, $order_field, strtolower($order_direction), false);
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
