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
use ILIAS\Data\ObjectId;
use ILIAS\Data\Order;
use ILIAS\Data\Range;
use ILIAS\HTTP\GlobalHttpState;
use ILIAS\Refinery\Factory as Refinery;
use ILIAS\UI\Component\Table\Column\Column;
use ILIAS\UI\Component\Table\Data;
use ILIAS\UI\Component\Table\DataRetrieval;
use ILIAS\UI\Component\Table\DataRowBuilder;
use ILIAS\UI\Factory;
use ILIAS\UI\URLBuilder;
use Psr\Http\Message\RequestInterface;
use Psr\Http\Message\ServerRequestInterface;

class MailSearchObjectMembershipsTable implements DataRetrieval
{
    private readonly DataFactory $data_factory;
    private readonly ServerRequestInterface|RequestInterface $request;
    /** @var list<array<string, mixed>>|null */
    private ?array $records = null;
    private bool $buddysystem_enabled;
    private bool $mailing_allowed = false;

    /**
     * @param int[] $obj_ids
     */
    public function __construct(
        private readonly array $obj_ids,
        private readonly ilMailSearchObjectGUI $parent_gui,
        private readonly int $current_user_id,
        private readonly ilCtrl $ctrl,
        private readonly ilLanguage $lng,
        private readonly Factory $ui_factory,
        private readonly GlobalHttpState $http,
        private readonly Refinery $refinery,
        private readonly ilObjectDataCache $object_data_cache,
    ) {
        $this->request = $this->http->request();
        $this->data_factory = new DataFactory();

        $this->lng->loadLanguageModule('crs');
        $this->lng->loadLanguageModule('wsp');
        $this->lng->loadLanguageModule('buddysystem');

        $this->buddysystem_enabled = ilBuddySystem::getInstance()->isEnabled();
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
                $this->lng->txt('members'),
                $this->getColumns(),
            )
            ->withActions($this->getActions())
            ->withRequest($this->request);
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
            $row_id = (string) $record['members_id'];
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

    private function isMailingAllowed(): bool
    {
        return $this->mailing_allowed;
    }

    private function isBuddysystemEnabled(): bool
    {
        return $this->buddysystem_enabled;
    }

    /**
     * @return array<string, Column>
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
                ->text($this->parent_gui->getObjectTypeLabel())
                ->withIsSortable(true),
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
            $obj_ids_token,
        ] =
            $url_builder->acquireParameters(
                $query_params_namespace,
                'action',
                'members_ids',
                'obj_ids',
            );

        $url_builder = $url_builder->withParameter(
            $obj_ids_token,
            $this->objIdsForActionUrl(),
        );

        $actions = [];
        if ($this->parent_gui->getContext() === ilMailSearchObjectGUI::CONTEXT_MAIL && $this->isMailingAllowed()) {
            $actions[ilMailSearchObjectGUI::ACTION_MAIL_MEMBERS] = $this->ui_factory->table()->action()->standard(
                $this->lng->txt('mail_members'),
                $url_builder->withParameter($action_parameter_token_copy, ilMailSearchObjectGUI::ACTION_MAIL_MEMBERS),
                $row_id_token,
            );
        } elseif ($this->parent_gui->getContext() === ilMailSearchObjectGUI::CONTEXT_WORKSPACE) {
            $actions[ilMailSearchObjectGUI::ACTION_SHARE_MEMBERS] = $this->ui_factory->table()->action()->standard(
                $this->lng->txt('wsp_share_with_members'),
                $url_builder->withParameter($action_parameter_token_copy, ilMailSearchObjectGUI::ACTION_SHARE_MEMBERS),
                $row_id_token,
            );
        }

        return $actions;
    }

    /**
     * @return string|list<string>
     */
    private function objIdsForActionUrl(): string|array
    {
        return $this->http->wrapper()->query()->retrieve(
            'contact_mailinglist_search_obj_ids',
            $this->refinery->byTrying([
                $this->refinery->custom()->transformation(
                    static function (mixed $value): array {
                        if ($value === ['ALL_OBJECTS']) {
                            return ['ALL_OBJECTS'];
                        }
                        throw new Exception('not all objects');
                    }
                ),
                $this->refinery->always(implode(',', array_map(strval(...), $this->obj_ids))),
            ])
        );
    }

    private function initRecords(): void
    {
        if ($this->records !== null) {
            return;
        }

        $this->records = [];
        $counter = 0;

        foreach ($this->obj_ids as $obj_id) {
            $ref_id = new ObjectId($obj_id)->toReferenceIds()[0]->toInt();
            $members_obj = ilParticipants::getInstance($ref_id);

            $usr_ids = array_map(
                intval(...),
                ilUtil::_sortIds($members_obj->getParticipants(), 'usr_data', 'lastname', 'usr_id'),
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

                $this->records[$counter] = [
                    'members_id' => $user->getId(),
                    'members_login' => $user->getLogin(),
                    'members_name' => $fullname,
                    'members_crs_grp' => $this->object_data_cache->lookupTitle($obj_id),
                    'obj_id' => $obj_id,
                ];

                if ($this->parent_gui->getContext() === ilMailSearchObjectGUI::CONTEXT_MAIL && $this->isBuddysystemEnabled()) {
                    $relation = ilBuddyList::getInstanceByGlobalUser()->getRelationByUserId($user->getId());
                    $state_name = ilStr::convertUpperCamelCaseToUnderscoreCase($relation->getState()->getName());
                    $this->records[$counter]['status'] = '';
                    if ($user->getId() !== $this->current_user_id) {
                        $this->records[$counter]['status'] = $this->lng->txt(
                            "buddy_bs_state_$state_name" . ($relation->isOwnedByActor() ? '_a' : '_p'),
                        );
                    }
                }
                ++$counter;
            }
        }
    }

    /**
     * @return list<array<string, mixed>>
     */
    private function sortedRecords(Order $order): array
    {
        $records = $this->records;
        [$order_field, $order_direction] = $order->join([], static fn($ret, $key, $value): array => [$key, $value]);

        return ilArrayUtil::stableSortArray($records, $order_field, strtolower($order_direction));
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
}
