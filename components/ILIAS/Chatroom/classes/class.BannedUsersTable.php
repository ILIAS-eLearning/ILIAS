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

use ILIAS\Data;
use ILIAS\UI;
use Psr\Http\Message\ServerRequestInterface;
use ILIAS\HTTP\Services;

class BannedUsersTable
{
    protected UI\Factory $ui_factory;
    protected ServerRequestInterface $request;
    protected Data\Factory $data_factory;

    public function __construct(
        private readonly array $banned_users,
        private readonly \ilCtrl $ctrl,
        private readonly \ilLanguage $lng,
        private readonly Services $http
    ) {
        global $DIC;

        $this->ui_factory = $DIC->ui()->factory();
        $this->request = $this->http->request();
        $this->data_factory = new Data\Factory();
    }

    public function getComponent(): UI\Component\Table\Data
    {
        $columns = $this->getColumns();
        $actions = $this->getActions();
        $data_retrieval = $this->getDataRetrieval();

        return $this->ui_factory->table()
                                ->data($this->lng->txt('ban_table_title'), $columns, $data_retrieval)
                                ->withActions($actions)
                                ->withRequest($this->request);
    }

    protected function getColumns(): array
    {
        return [
            'login' => $this->ui_factory->table()->column()->text($this->lng->txt('login'))
                                        ->withIsSortable(true),

            'firstname' => $this->ui_factory->table()->column()->text($this->lng->txt('firstname'))
                                            ->withIsSortable(true),

            'lastname' => $this->ui_factory->table()->column()->text($this->lng->txt('lastname'))
                                           ->withIsSortable(true),

            'timestamp' => $this->ui_factory->table()->column()->text($this->lng->txt('chtr_ban_ts_tbl_head'))
                                            ->withIsSortable(true),

            'actor' => $this->ui_factory->table()->column()->text($this->lng->txt('chtr_ban_actor_tbl_head'))
                                        ->withIsSortable(true),
        ];
    }

    protected function getActions(): array
    {
        $query_params_namespace = ['chat', 'ban', 'table'];

        $uri_detach = $this->data_factory->uri(
            ILIAS_HTTP_PATH . '/' . $this->ctrl->getLinkTargetByClass(ilObjChatroomgui::class, 'ban-handleTableActions')
        );

        $url_builder_detach = new UI\URLBuilder($uri_detach);
        list(
            $url_builder_detach, $action_parameter_token_copy, $row_id_token_detach
            ) =
            $url_builder_detach->acquireParameters(
                $query_params_namespace,
                'action',
                'user_ids'
            );

        return [
            'delete' => $this->ui_factory->table()->action()->multi(
                $this->lng->txt('unban'),
                $url_builder_detach->withParameter($action_parameter_token_copy, 'delete'),
                $row_id_token_detach
            ),
        ];
    }

    protected function getDataRetrieval(): UI\Component\Table\DataRetrieval
    {
        $data_retrieval = new class($this->banned_users) implements UI\Component\Table\DataRetrieval {

            private ?array $records = null;

            public function __construct(protected array $banned_users)
            {
            }

            private function initRecords(): void
            {
                if ($this->records === null) {
                    $this->records = [];
                    $i = 0;
                    $entries = $this->banned_users;

                    foreach ($entries as $entry) {
                        $user_id = $entry['user_id'];
                        /** @var ilObjUser $user */
                        $user = ilObjectFactory::getInstanceByObjId($user_id, false);
                        if (!($user instanceof ilObjUser)) {
                            continue;
                        }

                        $this->records[$i]['user_id'] = $user->getId();
                        $this->records[$i]['login'] = $user->getLogin();
                        $this->records[$i]['firstname'] = $user->getFirstname();
                        $this->records[$i]['lastname'] = $user->getLastname();
                        if (is_numeric($entry['timestamp']) && $entry['timestamp'] > 0) {
                            $this->records[$i]['timestamp'] = ilDatePresentation::formatDate(
                                new ilDateTime($entry['timestamp'], IL_CAL_UNIX)
                            );
                        }

                        $this->records[$i]['actor'] = $entry['actor'];
                        ++$i;
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
                return count((array) $this->records);
            }

            /**
             * @todo change this workaround, if there is a general decision about the sorting strategy
             */
            private function sortedRecords(Data\Order $order): array
            {
                $records = $this->records;
                [$order_field, $order_direction] = $order->join([], fn($ret, $key, $value) => [$key, $value]);
                return ilArrayUtil::stableSortArray($records, $order_field, strtolower($order_direction));
            }

            private function getRecords(Data\Range $range, Data\Order $order): array
            {
                $this->initRecords();
                $records = $this->sortedRecords($order);
                return $this->limitRecords($records, $range);
            }

            private function limitRecords(array $records, Data\Range $range): array
            {
                return array_slice($records, $range->getStart(), $range->getLength());
            }
        };

        return $data_retrieval;
    }

}
