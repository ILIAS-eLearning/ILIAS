<?php

declare(strict_types=1);

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
 ********************************************************************
 */

use ILIAS\Data;
use ILIAS\UI;
use Psr\Http\Message\ServerRequestInterface;

class ForumModeratorsTable
{
    private ilForumModerators $forum_moderators;
    protected \ilCtrl $ctrl;
    protected \ilLanguage $lng;
    protected UI\Factory $ui_factory;
    protected ServerRequestInterface $request;
    protected Data\Factory $data_factory;

    public function __construct(ilForumModerators $forum_moderators)
    {
        global $DIC;

        $this->ctrl = $DIC->ctrl();
        $this->lng = $DIC->language();
        $this->ui_factory = $DIC->ui()->factory();
        $this->request = $DIC->http()->request();
        $this->data_factory = new Data\Factory();
        $this->forum_moderators = $forum_moderators;
    }

    public function getComponent(): UI\Component\Table\Data
    {
        $columns = $this->getColumns();
        $actions = $this->getActions();
        $data_retrieval = $this->getDataRetrieval();

        $table = $this->ui_factory->table()
                                  ->data($this->lng->txt('frm_moderators'), $columns, $data_retrieval)
                                  ->withActions($actions)
                                  ->withRequest($this->request);

        return $table;
    }

    protected function getColumns(): array
    {
        $columns = [
            'usr_id' => $this->ui_factory->table()->column()->number('User ID')
                                         ->withIsSortable(false),

            'login' => $this->ui_factory->table()->column()->text($this->lng->txt('login'))
                                        ->withIsSortable(true),

            'firstname' => $this->ui_factory->table()->column()->text($this->lng->txt('firstname'))
                                            ->withIsSortable(true),

            'lastname' => $this->ui_factory->table()->column()->text($this->lng->txt('lastname'))
                                           ->withIsSortable(true),
        ];

        return $columns;
    }

    protected function getActions(): array
    {
        $query_params_namespace = ['frm_moderators_table'];

        $uri_detach = $this->data_factory->uri(
            ILIAS_HTTP_PATH . '/' . $this->ctrl->getLinkTargetByClass('ilforummoderatorsgui', 'detachModeratorRole')
        );

        $url_builder_detach = new UI\URLBuilder($uri_detach);
        list(
            $url_builder_detach, $action_parameter_token_copy, $row_id_token_detach
            ) =
            $url_builder_detach->acquireParameters(
                $query_params_namespace,
                'action',
                'usr_ids'
            );

        $actions = [
            'detachModeratorRole' => $this->ui_factory->table()->action()->multi(
                $this->lng->txt('remove'),
                $url_builder_detach->withParameter($action_parameter_token_copy, 'detachModeratorRole'),
                $row_id_token_detach
            ),
        ];
        return $actions;
    }

    protected function getDataRetrieval(): UI\Component\Table\DataRetrieval
    {
        $data_retrieval = new class($this->forum_moderators) implements UI\Component\Table\DataRetrieval {

            public function __construct(
                protected \ilForumModerators $forum_moderators
            ) {
            }

            public function getRows(
                UI\Component\Table\DataRowBuilder $row_builder,
                array $visible_column_ids,
                Data\Range $range,
                Data\Order $order,
                ?array $filter_data,
                ?array $additional_parameters
            ): \Generator {
                $records = $this->getRecords($range);

                foreach ($records as $idx => $record) {
                    $row_id = (string) $record['usr_id'];
                    yield $row_builder->buildDataRow($row_id, $record);
                }
            }

            public function getTotalRowCount(
                ?array $filter_data,
                ?array $additional_parameters
            ): ?int {
                return count($this->getRecords());
            }

            protected function getRecords(Data\Range $range = null): array
            {
                $records = [];
                $i = 0;
                $entries = $this->forum_moderators->getCurrentModerators();
                $num = count($entries);
                foreach ($entries as $usr_id) {
                    /** @var ilObjUser $user */
                    $user = ilObjectFactory::getInstanceByObjId($usr_id, false);
                    if (!($user instanceof ilObjUser)) {
                        $this->oForumModerators->detachModeratorRole($usr_id);
                        continue;
                    }

                    if ($num > 1) {
                        $records[$i]['usr_id'] = $user->getId();
                    } else {
                        $records[$i]['usr_id'] = 0;
                    }
                    $records[$i]['login'] = $user->getLogin();
                    $records[$i]['firstname'] = $user->getFirstname();
                    $records[$i]['lastname'] = $user->getLastname();
                    ++$i;
                }

                if ($range) {
                    $records = $this->limitRecords($records, $range);
                }

                return $records;
            }

            protected function orderRecords(array $records, Data\Order $order): array
            {
                [$aspect, $direction] = $order->join("", function ($i, $k, $v) {
                    return [$k, $v];
                });
                usort($records, static function (array $a, array $b) use ($aspect): int {
                    if (!isset($a[$aspect]) && !isset($b[$aspect])) {
                        return 0;
                    }
                    if (!isset($a[$aspect])) {
                        return -1;
                    }
                    if (!isset($b[$aspect])) {
                        return 1;
                    }
                    if (is_numeric($a[$aspect]) || is_bool($a[$aspect])) {
                        return $a[$aspect] <=> $b[$aspect];
                    }
                    if (is_array($a[$aspect])) {
                        return $a[$aspect] <=> $b[$aspect];
                    }
                    if ($a[$aspect] instanceof \ILIAS\UI\Component\Link\Link) {
                        return $a[$aspect]->getLabel() <=> $b[$aspect]->getLabel();
                    }

                    return strcmp($a[$aspect], $b[$aspect]);
                });

                if ($direction === $order::DESC) {
                    $records = array_reverse($records);
                }
                return $records;
            }

            protected function limitRecords(array $records, Data\Range $range): array
            {
                $records = array_slice($records, $range->getStart(), $range->getLength());

                return $records;
            }
        };

        return $data_retrieval;
    }

}
