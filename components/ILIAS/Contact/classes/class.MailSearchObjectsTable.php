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

use ILIAS\Data\Factory as DataFactory;
use ILIAS\Data\Order;
use ILIAS\Data\Range;
use ILIAS\HTTP\GlobalHttpState;
use ILIAS\UI\Component\Table\Column\Column;
use ILIAS\UI\Component\Table\Data;
use ILIAS\UI\Component\Table\DataRetrieval;
use ILIAS\UI\Component\Table\DataRowBuilder;
use ILIAS\UI\Factory;
use ILIAS\UI\URLBuilder;
use Psr\Http\Message\RequestInterface;
use Psr\Http\Message\ServerRequestInterface;

class MailSearchObjectsTable implements DataRetrieval
{
    private readonly ServerRequestInterface|RequestInterface $request;
    private readonly DataFactory $data_factory;
    private int $num_hidden_members = 0;
    private bool $mailing_allowed = false;
    /** @var list<array<string, mixed>>|null */
    private ?array $records = null;

    public function __construct(
        private readonly ilObjUser $user,
        private readonly ilMailSearchObjectGUI $parent_gui,
        private readonly ilCtrl $ctrl,
        private readonly ilLanguage $lng,
        private readonly Factory $ui_factory,
        GlobalHttpState $http,
        private readonly ilTree $tree,
    ) {
        $this->request = $http->request();
        $this->data_factory = new DataFactory();

        $this->lng->loadLanguageModule('crs');
        $this->lng->loadLanguageModule('buddysystem');
    }

    public function setMailingAllowed(bool $mailing_allowed): void
    {
        $this->mailing_allowed = $mailing_allowed;
    }

    public function getComponent(): Data
    {
        return $this->ui_factory->table()
            ->data(
                $this,
                $this->parent_gui->getSearchTableTitle(),
                $this->getColumns(),
            )
            ->withActions($this->getActions())
            ->withRequest($this->request);
    }

    public function getNumHiddenMembers(): int
    {
        return $this->num_hidden_members;
    }

    public function getRows(
        DataRowBuilder $row_builder,
        array $visible_column_ids,
        Range $range,
        Order $order,
        mixed $additional_viewcontrol_data,
        mixed $filter_data,
        mixed $additional_parameters,
    ): Generator {
        $records = $this->getRecords($range, $order);

        foreach ($records as $record) {
            $row_id = (string) $record['obj_id'];
            yield $row_builder->buildDataRow($row_id, $record);
        }
    }

    public function getTotalRowCount(
        mixed $additional_viewcontrol_data,
        mixed $filter_data,
        mixed $additional_parameters,
    ): ?int {
        $this->initRecords();

        return count($this->records);
    }

    /**
     * @return array<string, Column>
     */
    private function getColumns(): array
    {
        return [
            'obj_title' => $this->ui_factory->table()
                ->column()
                ->text($this->parent_gui->getSearchTableTitle())
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

        $uri = $this->data_factory->uri(
            ILIAS_HTTP_PATH . '/' . $this->ctrl->getLinkTarget(
                $this->parent_gui,
                ilMailSearchObjectGUI::CMD_HANDLE_MAIL_SEARCH_OBJECT_ACTIONS,
            ),
        );

        $url_builder = new URLBuilder($uri);
        [
            $url_builder,
            $action_parameter_token_copy,
            $row_id_token,
        ] =
            $url_builder->acquireParameters(
                $query_params_namespace,
                'action',
                'obj_ids',
            );

        $actions = [];

        if ($this->parent_gui->getContext() === ilMailSearchObjectGUI::CONTEXT_MAIL && $this->isMailingAllowed()) {
            $actions[ilMailSearchObjectGUI::ACTION_MAIL_OBJECTS] = $this->ui_factory->table()->action()->standard(
                $this->lng->txt('mail_members'),
                $url_builder->withParameter($action_parameter_token_copy, ilMailSearchObjectGUI::ACTION_MAIL_OBJECTS),
                $row_id_token,
            );
        } elseif ($this->parent_gui->getContext() === ilMailSearchObjectGUI::CONTEXT_WORKSPACE) {
            $actions[ilMailSearchObjectGUI::ACTION_SHARE_OBJECTS] = $this->ui_factory->table()->action()->standard(
                $this->lng->txt('wsp_share_with_members'),
                $url_builder->withParameter($action_parameter_token_copy, ilMailSearchObjectGUI::ACTION_SHARE_OBJECTS),
                $row_id_token,
            );
        }

        $actions[ilMailSearchObjectGUI::ACTION_SHOW_MEMBERS] = $this->ui_factory->table()->action()->standard(
            $this->lng->txt('mail_list_members'),
            $url_builder->withParameter($action_parameter_token_copy, ilMailSearchObjectGUI::ACTION_SHOW_MEMBERS),
            $row_id_token,
        );

        return $actions;
    }

    private function initRecords(): void
    {
        if ($this->records !== null) {
            return;
        }

        $this->records = [];
        $counter = 0;

        $objs_ids = ilParticipants::_getMembershipByType($this->user->getId(), [$this->parent_gui->getObjectType()]);
        if ($objs_ids === []) {
            return;
        }

        $this->num_hidden_members = 0;
        foreach ($objs_ids as $obj_id) {
            $object = $this->getCurrentObject($obj_id);

            $has_untrashed_references = ilObject::_hasUntrashedReference($object->getId());
            $can_send_mails = ilParticipants::canSendMailToMembers(
                $object->getRefId(),
                $this->user->getId(),
                ilMailGlobalServices::getMailObjectRefId(),
            );

            if ($has_untrashed_references && ($can_send_mails || $this->parent_gui->doesExposeMembers($object))) {
                $participants = ilParticipants::getInstance($object->getRefId());

                $usr_ids = array_filter(
                    $participants->getParticipants(),
                    ilObjUser::_lookupActive(...),
                );

                $hidden_members = false;
                if (!$object->getShowMembers()) {
                    ++$this->num_hidden_members;
                    $hidden_members = true;
                }

                $this->records[$counter]['obj_id'] = $object->getId();
                $this->records[$counter]['obj_title'] = $object->getTitle();
                $this->records[$counter]['obj_cnt_members'] = count($usr_ids);
                $this->records[$counter]['obj_path'] = $this->getObjectPath($object);
                $this->records[$counter]['hidden_members'] = $hidden_members;

                ++$counter;
            }
        }
    }

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

    /**
     * @return list<array<string, mixed>>
     */
    private function sortedRecords(Order $order): array
    {
        $records = $this->records;
        [$order_field, $order_direction] = $order->join([], fn($ret, $key, $value) => [$key, $value]);
        $is_numeric = $this->numericOrdering($order_field);

        return ilArrayUtil::stableSortArray($records, $order_field, strtolower($order_direction), $is_numeric);
    }

    /**
     * @return list<array<string, mixed>>
     */
    private function getRecords(Range $range, Order $order): array
    {
        $this->initRecords();

        $records = $this->sortedRecords($order);

        return $this->limitRecords($records, $range);
    }

    /**
     * @param list<array<string, mixed>> $records
     * @return list<array<string, mixed>>
     */
    private function limitRecords(array $records, Range $range): array
    {
        return array_slice($records, $range->getStart(), $range->getLength());
    }

    private function numericOrdering(string $field): bool
    {
        return $field === 'obj_cnt_members';
    }
}
