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
use ILIAS\UI\Factory;

class MailSearchObjectMembershipsTable implements UI\Component\Table\DataRetrieval
{
    /** @var array<string, string> */
    private readonly array $mode;
    private readonly Data\Factory $data_factory;
    private ServerRequestInterface|\Psr\Http\Message\RequestInterface $request;
    /** @var list<array<string, mixed>>|null */
    private ?array $records = null;
    private bool $buddysystem_enabled;
    private bool $mailing_allowed = false;

    private function isMailingAllowed(): bool
    {
        return $this->mailing_allowed;
    }

    public function setMailingAllowed(bool $mailing_allowed): void
    {
        $this->mailing_allowed = $mailing_allowed;
    }

    private function isBuddysystemEnabled(): bool
    {
        return $this->buddysystem_enabled;
    }

    /**
     * @param int[] $obj_ids
     */
    public function __construct(
        private readonly array $obj_ids,
        private readonly string $type,
        private readonly string $context,
        private readonly int $current_user_id,
        private readonly ilCtrl $ctrl,
        private readonly ilLanguage $lng,
        private readonly Factory $ui_factory,
        \ILIAS\HTTP\GlobalHttpState $http,
        private readonly ilObjectDataCache $object_data_cache
    ) {
        $this->request = $http->request();
        $this->data_factory = new Data\Factory();

        $this->lng->loadLanguageModule('crs');
        $this->lng->loadLanguageModule('wsp');
        $this->lng->loadLanguageModule('buddysystem');

        $mode = [];
        if ($this->type === 'crs') {
            $mode['checkbox'] = 'search_crs';
            $mode['short'] = 'crs';
            $mode['long'] = 'course';
            $mode['lng_type'] = $this->lng->txt('course');
            $mode['view'] = 'crs_members';
        } elseif ($type === 'grp') {
            $mode['checkbox'] = 'search_grp';
            $mode['short'] = 'grp';
            $mode['long'] = 'group';
            $mode['lng_type'] = $this->lng->txt('group');
            $mode['view'] = 'grp_members';
        }

        $this->mode = $mode;
        $this->buddysystem_enabled = ilBuddySystem::getInstance()->isEnabled();
    }

    public function getComponent(): UI\Component\Table\Data
    {
        $columns = $this->getColumns();
        $actions = $this->getActions();

        return $this->ui_factory->table()
                                ->data(
                                    $this->lng->txt('members'),
                                    $columns,
                                    $this
                                )
                                ->withActions($actions)
                                ->withRequest($this->request);
    }

    /**
     * @return array<string, UI\Component\Table\Column\Column>
     */
    private function getColumns(): array
    {
        $columns = [
            'members_login' => $this->ui_factory->table()
                                                ->column()
                                                ->text($this->lng->txt('login'))
                                                ->withIsSortable(true),
            'members_name' => $this->ui_factory->table()
                                               ->column()
                                               ->text($this->lng->txt('name'))
                                               ->withIsSortable(true),
            'members_crs_grp' => $this->ui_factory->table()
                                                  ->column()
                                                  ->text($this->lng->txt($this->mode['long']))
                                                  ->withIsSortable(true)
        ];

        if ($this->isBuddysystemEnabled()) {
            $columns['status'] = $this->ui_factory->table()
                                                  ->column()
                                                  ->text($this->lng->txt('buddy_tbl_filter_state'))
                                                  ->withIsSortable(true);
        }

        return $columns;
    }

    /**
     * @return array<string, ILIAS\UI\Component\Table\Action\Standard>
     */
    private function getActions(): array
    {
        $query_params_namespace = ['contact', 'mailinglist', 'search'];
        $exec_class = $this->type == 'crs' ? ilMailSearchCoursesGUI::class : ilMailSearchGroupsGUI::class;

        $uri = $this->data_factory->uri(
            ILIAS_HTTP_PATH . '/' . $this->ctrl->getLinkTargetByClass(
                $exec_class,
                'handleMailSearchObjectActions'
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
            'members_ids'
        );

        $actions = [];
        if ($this->context === 'mail') {
            if ($this->isMailingAllowed()) {
                $actions['mail'] = $this->ui_factory->table()->action()->standard(
                    $this->lng->txt('mail_members'),
                    $url_builder->withParameter($action_parameter_token_copy, 'mailMembers'),
                    $row_id_token
                );
            }
        } elseif ($this->context === 'wsp') {
            $actions['share'] = $this->ui_factory->table()->action()->standard(
                $this->lng->txt('wsp_share_with_members'),
                $url_builder->withParameter($action_parameter_token_copy, 'shareMembers'),
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

            foreach ($this->obj_ids as $obj_id) {
                $members_obj = ilParticipants::getInstanceByObjId($obj_id);

                $usr_ids = array_map(
                    'intval',
                    ilUtil::_sortIds($members_obj->getParticipants(), 'usr_data', 'lastname', 'usr_id')
                );
                foreach ($usr_ids as $usr_id) {
                    $user = new ilObjUser($usr_id);
                    if (!$user->getActive()) {
                        continue;
                    }

                    $fullname = '';
                    if (in_array(ilObjUser::_lookupPref($user->getId(), 'public_profile'), ['g', 'y'])) {
                        $fullname = $user->getLastname() . ', ' . $user->getFirstname();
                    }

                    $this->records[$counter]['members_id'] = $user->getId();
                    $this->records[$counter]['members_login'] = $user->getLogin();
                    $this->records[$counter]['members_name'] = $fullname;
                    $this->records[$counter]['members_crs_grp'] = $this->object_data_cache->lookupTitle((int) $obj_id);
                    $this->records[$counter]['obj_id'] = $obj_id;

                    if ('mail' === $this->context && $this->isBuddysystemEnabled()) {
                        $relation = ilBuddyList::getInstanceByGlobalUser()->getRelationByUserId($user->getId());
                        $state_name = ilStr::convertUpperCamelCaseToUnderscoreCase($relation->getState()->getName());
                        $this->records[$counter]['status'] = '';
                        if ($user->getId() !== $this->current_user_id) {
                            if ($relation->isOwnedByActor()) {
                                $this->records[$counter]['status'] = $this->lng->txt(
                                    'buddy_bs_state_' . $state_name . '_a'
                                );
                            } else {
                                $this->records[$counter]['status'] = $this->lng->txt(
                                    'buddy_bs_state_' . $state_name . '_p'
                                );
                            }
                        }
                    }
                    ++$counter;
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
            $row_id = (string) $record['members_id'];
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
