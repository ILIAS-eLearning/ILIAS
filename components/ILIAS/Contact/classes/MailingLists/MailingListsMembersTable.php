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
use ilUserUtil;
use ilMailingListsGUI;
use ilLanguage;
use ilCtrl;
use ilMailingList;

class MailingListsMembersTable implements UI\Component\Table\DataRetrieval
{
    private readonly ServerRequestInterface $request;
    private readonly Data\Factory $data_factory;
    /** @var list<array<string, mixed>>|null */
    private ?array $records = null;

    public function __construct(
        private readonly ilMailingList $mailing_list,
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
                sprintf(
                    $this->lng->txt('mail_members_of_mailing_list'),
                    $this->mailing_list->getTitle()
                ),
                $columns,
                $this
            )
            ->withId(self::class . '_' . $this->mailing_list->getId())
            ->withActions($actions)
            ->withRequest($this->request);
    }

    /**
     * @return array<string, \ILIAS\UI\Component\Table\Column\Column>
     */
    private function getColumns(): array
    {
        return [
            'login' => $this->ui_factory
                ->table()
                ->column()
                ->text($this->lng->txt('login'))
                ->withIsSortable(true),
        ];
    }

    /**
     * @return array<string, \ILIAS\UI\Component\Table\Action\Action>
     */
    private function getActions(): array
    {
        $query_params_namespace = ['contact', 'mailinglist', 'members'];

        $uri = $this->data_factory->uri(
            ILIAS_HTTP_PATH . '/' . $this->ctrl->getLinkTargetByClass(
                ilMailingListsGUI::class,
                'handleMailingListMemberActions'
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
            'entry_ids'
        );

        return [
            'confirmDeleteMembers' => $this->ui_factory->table()->action()->multi(
                $this->lng->txt('delete'),
                $url_builder->withParameter($action_parameter_token_copy, 'confirmDeleteMembers'),
                $row_id_token
            ),
        ];
    }

    private function initRecords(): void
    {
        if ($this->records === null) {
            $this->records = [];
            $i = 0;
            $entries = $this->mailing_list->getAssignedEntries();
            if ($entries !== []) {
                $usr_ids = array_map(static fn(array $entry): int => (int) $entry['usr_id'], $entries);
                $names = ilUserUtil::getNamePresentation($usr_ids, false, false, '', false, false, false);

                foreach ($entries as $entry) {
                    $this->records[$i]['a_id'] = $entry['a_id'];
                    $this->records[$i]['user_id'] = $entry['usr_id'];
                    $this->records[$i]['login'] = $names[$entry['usr_id']];
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
            $row_id = (string) $record['a_id'];
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
