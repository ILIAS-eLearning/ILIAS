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

class MailSearchObjectsTable implements UI\Component\Table\DataRetrieval
{
    /** @var array<string, string> */
    private readonly array $mode;
    private int $num_hidden_members = 0;
    private ServerRequestInterface|\Psr\Http\Message\RequestInterface $request;
    private readonly Data\Factory $data_factory;
    private bool $mailing_allowed = false;
    /** @var list<array<string, mixed>>|null */
    private ?array $records = null;

    private function getCurrentObject(int $obj_id): ilObjCourse|ilObjGroup
    {
        /** @var ilObjCourse|ilObjGroup $object */
        $object = ilObjectFactory::getInstanceByObjId($obj_id);

        $ref_ids = array_keys(ilObject::_getAllReferences($object->getId()));
        $ref_id = $ref_ids[0];
        $object->setRefId($ref_id);
        return $object;
    }

    private function getObjectPath(ilObjGroup|ilObjCourse $object): string
    {
        $path_arr = $this->tree->getPathFull($object->getRefId(), $this->tree->getRootId());
        $path = '';
        foreach ($path_arr as $data) {
            if ($path !== '') {
                $path .= ' -> ';
            }
            $path .= $data['title'];
        }

        return $path;
    }

    private function isMailingAllowed(): bool
    {
        return $this->mailing_allowed;
    }

    public function setMailingAllowed(bool $mailing_allowed): void
    {
        $this->mailing_allowed = $mailing_allowed;
    }

    public function __construct(
        private readonly ilObjUser $user,
        private readonly string $type,
        private readonly string $context,
        private readonly ilCtrl $ctrl,
        private readonly ilLanguage $lng,
        private readonly Factory $ui_factory,
        \ILIAS\HTTP\GlobalHttpState $http,
        private readonly ilTree $tree,
        private readonly ilRbacSystem $rbac_system
    ) {
        $this->request = $http->request();
        $this->data_factory = new Data\Factory();

        $this->lng->loadLanguageModule('crs');
        $this->lng->loadLanguageModule('buddysystem');

        $mode = [];
        if ($this->type === 'crs') {
            $mode['short'] = 'crs';
            $mode['long'] = 'course';
            $mode['checkbox'] = 'search_crs';
            $mode['tableprefix'] = 'crstable';
            $mode['lng_mail'] = $this->lng->txt('mail_my_courses');
            $mode['view'] = 'myobjects';
        } elseif ($type === 'grp') {
            $mode['short'] = 'grp';
            $mode['long'] = 'group';
            $mode['checkbox'] = 'search_grp';
            $mode['tableprefix'] = 'grptable';
            $mode['lng_mail'] = $this->lng->txt('mail_my_groups');
            $mode['view'] = 'myobjects';
        }
        $this->mode = $mode;
    }

    public function getComponent(): UI\Component\Table\Data
    {
        $columns = $this->getColumns();
        $actions = $this->getActions();

        return $this->ui_factory->table()
                                ->data(
                                    $this->mode['lng_mail'],
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
        return [
            'obj_title' => $this->ui_factory->table()
                                            ->column()
                                            ->text($this->mode['lng_mail'])
                                            ->withIsSortable(true),
            'obj_path' => $this->ui_factory->table()
                                           ->column()
                                           ->text($this->lng->txt('path'))
                                           ->withIsSortable(true),
            'obj_cnt_members' => $this->ui_factory->table()
                                                  ->column()
                                                  ->number($this->lng->txt('obj_count_members'))
                                                  ->withIsSortable(true),
        ];
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
        ] =
            $url_builder->acquireParameters(
                $query_params_namespace,
                'action',
                'obj_ids'
            );

        $actions = [];
        if ($this->context === 'mail') {
            if ($this->isMailingAllowed()) {
                $actions['mail'] = $this->ui_factory->table()->action()->standard(
                    $this->lng->txt('mail_members'),
                    $url_builder->withParameter($action_parameter_token_copy, 'mailObjects'),
                    $row_id_token
                );
            }
        } elseif ($this->context === 'wsp') {
            $actions['share'] = $this->ui_factory->table()->action()->standard(
                $this->lng->txt('wsp_share_with_members'),
                $url_builder->withParameter($action_parameter_token_copy, 'shareObjects'),
                $row_id_token
            );
        }
        $actions['showMembers'] = $this->ui_factory->table()->action()->standard(
            $this->lng->txt('mail_list_members'),
            $url_builder->withParameter($action_parameter_token_copy, 'showMembers'),
            $row_id_token
        );

        return $actions;
    }

    private function initRecords(): void
    {
        if ($this->records === null) {
            $this->records = [];
            $counter = 0;

            $objs_ids = ilParticipants::_getMembershipByType($this->user->getId(), [$this->type]);
            if ($objs_ids !== []) {
                $this->num_hidden_members = 0;
                foreach ($objs_ids as $obj_id) {
                    $object = $this->getCurrentObject($obj_id);

                    $has_untrashed_references = ilObject::_hasUntrashedReference($object->getId());
                    $can_send_mails = ilParticipants::canSendMailToMembers(
                        $object->getRefId(),
                        $this->user->getId(),
                        ilMailGlobalServices::getMailObjectRefId()
                    );

                    if ($has_untrashed_references && ($can_send_mails || $this->doesExposeMembers($object))) {
                        $member_list_enabled = $object->getShowMembers();
                        $participants = ilParticipants::getInstanceByObjId($object->getId());
                        $usr_ids = $participants->getParticipants();

                        foreach ($usr_ids as $key => $usr_id) {
                            $is_active = ilObjUser::_lookupActive($usr_id);
                            if (!$is_active) {
                                unset($usr_ids[$key]);
                            }
                        }
                        $usr_ids = array_values($usr_ids);

                        $hiddenMembers = false;
                        if (!$member_list_enabled) {
                            ++$this->num_hidden_members;
                            $hiddenMembers = true;
                        }

                        $path = $this->getObjectPath($object);

                        $this->records[$counter]['obj_id'] = $object->getId();
                        $this->records[$counter]['obj_title'] = $object->getTitle();
                        $this->records[$counter]['obj_cnt_members'] = count($usr_ids);
                        $this->records[$counter]['obj_path'] = $path;
                        $this->records[$counter]['hidden_members'] = $hiddenMembers;

                        ++$counter;
                    }
                }
            }
        }
    }

    public function getNumHiddenMembers(): int
    {
        return $this->num_hidden_members;
    }

    private function doesExposeMembers(ilObject $object): bool
    {
        $isOffline = true;
        $showMemberListEnabled = true;

        if ($object->getType() === 'crs' && method_exists($object, 'isActivated')) {
            $isOffline = !$object->isActivated();
        }

        if (method_exists($object, 'getShowMembers')) {
            $showMemberListEnabled = (bool) $object->getShowMembers();
        }

        $isPrivilegedUser = $this->rbac_system->checkAccess('write', $object->getRefId());

        return (!$isOffline && $showMemberListEnabled) || $isPrivilegedUser;
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
            $row_id = (string) $record['obj_id'];
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
        $is_numeric = $this->numericOrdering($order_field);

        return ilArrayUtil::stableSortArray($records, $order_field, strtolower($order_direction), $is_numeric);
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

    private function numericOrdering(string $field): bool
    {
        return $field === 'obj_cnt_members';
    }
}
