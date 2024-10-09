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

namespace ILIAS\Contact\MailingLists;

use ILIAS\Data;
use ILIAS\UI;
use Psr\Http\Message\ServerRequestInterface;
use ilArrayUtil;
use ilMailingList;
use ilMailingListsGUI;
use ilLanguage;
use ilCtrl;
use ilMailingLists;

class MailingListsTable implements UI\Component\Table\DataRetrieval
{
    private readonly ServerRequestInterface $request;
    private readonly Data\Factory $data_factory;
    private bool $mailing_allowed = false;
    /**  @var list<array<string, mixed>>|null */
    private ?array $records = null;

    public function __construct(
        private readonly ilMailingLists $mailing_lists,
        private readonly ilCtrl $ctrl,
        private readonly ilLanguage $lng,
        private readonly \ILIAS\UI\Factory $ui_factory,
        \ILIAS\HTTP\GlobalHttpState $http
    ) {
        $this->request = $http->request();
        $this->data_factory = new Data\Factory();
    }

    private function isMailingAllowed(): bool
    {
        return $this->mailing_allowed;
    }

    public function setMailingAllowed(bool $mailing_allowed): void
    {
        $this->mailing_allowed = $mailing_allowed;
    }

    public function getComponent(): UI\Component\Table\Data
    {
        $columns = $this->getColumns();
        $actions = $this->getActions();

        return $this->ui_factory
            ->table()
            ->data(
                $this->lng->txt('mail_mailing_lists'),
                $columns,
                $this
            )
            ->withId(self::class)
            ->withActions($actions)
            ->withRequest($this->request);
    }

    /**
     * @return array<string, \ILIAS\UI\Component\Table\Column\Column>
     */
    private function getColumns(): array
    {
        return [
            'title' => $this->ui_factory
                ->table()
                ->column()
                ->text($this->lng->txt('title'))
                ->withIsSortable(true),
            'description' => $this->ui_factory
                ->table()
                ->column()
                ->text($this->lng->txt('description'))
                ->withIsSortable(true),
            'members' => $this->ui_factory
                ->table()
                ->column()
                ->text($this->lng->txt('members'))
                ->withIsSortable(true),
        ];
    }

    /**
     * @return array<string, \ILIAS\UI\Component\Table\Action\Action>
     */
    private function getActions(): array
    {
        $query_params_namespace = ['contact', 'mailinglist', 'list'];

        $uri = $this->data_factory->uri(
            ILIAS_HTTP_PATH . '/' . $this->ctrl->getLinkTargetByClass(
                ilMailingListsGUI::class,
                'handleMailingListActions'
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
            'ml_ids'
        );

        $actions = [
            'confirmDelete' => $this->ui_factory->table()->action()->multi(
                $this->lng->txt('delete'),
                $url_builder->withParameter($action_parameter_token_copy, 'confirmDelete'),
                $row_id_token
            ),
            'showForm' => $this->ui_factory->table()->action()->single(
                $this->lng->txt('edit'),
                $url_builder->withParameter($action_parameter_token_copy, 'showForm'),
                $row_id_token
            ),
            'showMembersList' => $this->ui_factory->table()->action()->single(
                $this->lng->txt('members'),
                $url_builder->withParameter($action_parameter_token_copy, 'showMembersList'),
                $row_id_token
            )
        ];

        if ($this->isMailingAllowed()) {
            $actions['mailToList'] = $this->ui_factory->table()->action()->standard(
                $this->lng->txt('send_mail_to'),
                $url_builder->withParameter($action_parameter_token_copy, 'mailToList'),
                $row_id_token
            );
        }

        return $actions;
    }

    private function initRecords(): void
    {
        if ($this->records === null) {
            $this->records = [];
            $counter = 0;
            $entries = $this->mailing_lists->getAll();

            foreach ($entries as $entry) {
                if ($entry->getMode() === ilMailingList::MODE_TEMPORARY) {
                    continue;
                }

                $this->records[$counter]['ml_id'] = $entry->getId();
                $this->records[$counter]['title'] = $entry->getTitle() . ' [#il_ml_' . $entry->getId() . ']';
                $this->records[$counter]['description'] = $entry->getDescription() ?? '';
                $this->records[$counter]['members'] = count($entry->getAssignedEntries());

                ++$counter;
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
            $row_id = (string) $record['ml_id'];
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
