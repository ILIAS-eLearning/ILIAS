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

namespace ILIAS\Forum\Moderation;

use ILIAS\Data;
use ILIAS\UI;
use Psr\Http\Message\ServerRequestInterface;
use ilArrayUtil;
use ilObjUser;
use ilObjectFactory;
use ilForumModeratorsGUI;
use ilLanguage;
use ilForumModerators;
use ilCtrlInterface;

class ForumModeratorsTable implements UI\Component\Table\DataRetrieval
{
    protected ServerRequestInterface $request;
    protected Data\Factory $data_factory;
    /**
     * @var list<array<string, mixed>>|null
     */
    private ?array $records = null;

    public function __construct(
        private readonly ilForumModerators $forum_moderators,
        private readonly ilCtrlInterface $ctrl,
        private readonly ilLanguage $lng,
        \ILIAS\HTTP\Services $http,
        private readonly \ILIAS\UI\Factory $ui_factory
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
            ->data($this->lng->txt('frm_moderators'), $columns, $this)
            ->withId(self::class . '_' . $this->forum_moderators->getRefId())
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
            'firstname' => $this->ui_factory
                ->table()
                ->column()
                ->text($this->lng->txt('firstname'))
                ->withIsSortable(true),

            'lastname' => $this->ui_factory
                ->table()
                ->column()
                ->text($this->lng->txt('lastname'))
                ->withIsSortable(true),
        ];
    }

    /**
     * @return array<string, \ILIAS\UI\Component\Table\Action\Action>
     */
    protected function getActions(): array
    {
        $query_params_namespace = ['frm', 'moderators', 'table'];

        $uri_detach = $this->data_factory->uri(
            ILIAS_HTTP_PATH . '/' . $this->ctrl->getLinkTargetByClass(
                ilForumModeratorsGUI::class,
                'handleModeratorActions'
            )
        );

        $url_builder_detach = new UI\URLBuilder($uri_detach);
        [
            $url_builder_detach,
            $action_parameter_token_copy,
            $row_id_token_detach
        ] = $url_builder_detach->acquireParameters(
            $query_params_namespace,
            'action',
            'usr_ids'
        );

        return [
            'detachModeratorRole' => $this->ui_factory->table()->action()->single(
                $this->lng->txt('remove'),
                $url_builder_detach->withParameter($action_parameter_token_copy, 'detachModeratorRole'),
                $row_id_token_detach
            ),
        ];
    }

    private function initRecords(): void
    {
        if ($this->records === null) {
            $this->records = [];
            $i = 0;
            $entries = $this->forum_moderators->getCurrentModerators();
            foreach ($entries as $usr_id) {
                /** @var ilObjUser $user */
                $user = ilObjectFactory::getInstanceByObjId($usr_id, false);
                if (!($user instanceof ilObjUser)) {
                    $this->forum_moderators->detachModeratorRole($usr_id);
                    continue;
                }

                $this->records[$i]['usr_id'] = $user->getId();
                $this->records[$i]['login'] = $user->getLogin();
                $this->records[$i]['firstname'] = $user->getFirstname();
                $this->records[$i]['lastname'] = $user->getLastname();
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
            $row_id = (string) $record['usr_id'];
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
