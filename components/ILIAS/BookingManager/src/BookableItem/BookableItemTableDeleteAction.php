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

namespace ILIAS\BookingManager\BookableItem;

use ilBookingObject;
use ilBookingObjectGUI;
use ilBookingReservation;
use ilGlobalTemplateInterface;
use ILIAS\BookingManager\Access\AccessManager;
use ILIAS\BookingManager\Common\Table\TableAction;
use ILIAS\HTTP\Services as HttpServices;
use ILIAS\Refinery\Factory as Refinery;
use ILIAS\UI\Component\Modal\Modal;
use ILIAS\UI\Component\Table\Action\Action;
use ILIAS\UI\Factory as UIFactory;
use ILIAS\UI\URLBuilder;
use ILIAS\UI\URLBuilderToken;
use ilLanguage;

class BookableItemTableDeleteAction implements TableAction
{
    public const string ACTION_ID = 'delete_object';
    public const string SHOW_MODAL_ACTION = 'showModalAction';
    public const string SUBMIT_MODAL_ACTION = 'submitModalAction';

    public function __construct(
        private readonly UIFactory $ui_factory,
        private readonly ilLanguage $lng,
        private readonly AccessManager $access,
        private readonly ilGlobalTemplateInterface $tpl,
        private readonly HttpServices $http,
        private readonly Refinery $refinery,
        private readonly ilBookingObjectGUI $object_gui,
        private readonly int $ref_id,
        /** @var callable(): ?array */
        private $get_filter_data,
        private readonly BookableItemTableData $data
    ) {
    }

    public function getActionId(): string
    {
        return self::ACTION_ID;
    }

    public function isAvailable(): bool
    {
        return $this->object_gui->isManagementActivated()
            && $this->access->canManageObjects($this->ref_id);
    }

    public function getTableAction(
        URLBuilder $url_builder,
        URLBuilderToken $row_id_token,
        URLBuilderToken $action_token,
        URLBuilderToken $action_type_token
    ): Action {
        return $this->ui_factory->table()->action()->standard(
            $this->lng->txt('delete'),
            $url_builder
                ->withParameter($action_token, self::ACTION_ID)
                ->withParameter($action_type_token, self::SHOW_MODAL_ACTION),
            $row_id_token
        )->withAsync();
    }

    public function onExecute(
        URLBuilder $url_builder,
        URLBuilderToken $row_id_token,
        URLBuilderToken $action_token,
        URLBuilderToken $action_type_token
    ): mixed {
        $action_type = (string) $this->resolveStringParameter(
            $action_type_token->getName(),
            self::SHOW_MODAL_ACTION
        );
        if ($action_type === self::SUBMIT_MODAL_ACTION) {
            return $this->submit($url_builder, $row_id_token, $action_token, $action_type_token);
        }
        return $this->showDeleteModal($url_builder, $row_id_token, $action_token, $action_type_token);
    }

    public function allowActionForRecord(mixed $record): bool
    {
        if (!\is_array($record) || !isset($record['row'], $record['may_edit']) || !(bool) $record['may_edit']) {
            return false;
        }
        foreach ($this->data->reservationsForObject((int) $record['row']['booking_object_id']) as $i) {
            if ((int) $i['status'] !== ilBookingReservation::STATUS_CANCELLED) {
                return false;
            }
        }
        return true;
    }

    public function getSelectionErrorMessage(): ?string
    {
        return $this->lng->txt('no_valid_selection');
    }

    private function showDeleteModal(
        URLBuilder $url_builder,
        URLBuilderToken $row_id_token,
        URLBuilderToken $action_token,
        URLBuilderToken $action_type_token
    ): ?Modal {
        $this->lng->loadLanguageModule('book');
        $this->lng->loadLanguageModule('common');
        $row_ids = $this->readRowIdsFromRequest($row_id_token->getName());
        $row_ids = $this->expandAllObjectsPlaceholder($row_ids);
        if ($row_ids === []) {
            $this->tpl->setOnScreenMessage(
                ilGlobalTemplateInterface::MESSAGE_TYPE_INFO,
                $this->lng->txt('no_checkbox'),
                true
            );
            return null;
        }
        $records = $this->resolveDeletableObjectRecords($row_ids);
        if ($records === []) {
            $this->tpl->setOnScreenMessage(
                ilGlobalTemplateInterface::MESSAGE_TYPE_INFO,
                $this->getSelectionErrorMessage(),
                true
            );
            return null;
        }

        return $this->ui_factory->modal()->interruptive(
            $this->lng->txt('confirm'),
            $this->lng->txt('book_confirm_delete'),
            $url_builder
                ->withParameter($row_id_token, $row_ids)
                ->withParameter($action_token, self::ACTION_ID)
                ->withParameter($action_type_token, self::SUBMIT_MODAL_ACTION)
                ->buildURI()
                ->__toString()
        )->withAffectedItems(
            array_map(
                function (array $r) {
                    return $this->ui_factory->modal()->interruptiveItem()->standard(
                        (string) $r['booking_object_id'],
                        (string) $r['title']
                    );
                },
                $records
            )
        )->withActionButtonLabel($this->lng->txt('delete'));
    }

    private function submit(
        URLBuilder $_url_builder,
        URLBuilderToken $row_id_token,
        URLBuilderToken $_action_token,
        URLBuilderToken $_action_type_token
    ): ?Modal {
        $this->lng->loadLanguageModule('book');
        $this->lng->loadLanguageModule('common');
        if (!$this->object_gui->isManagementActivated()
            || !$this->access->canManageObjects($this->ref_id)) {
            $this->tpl->setOnScreenMessage(
                ilGlobalTemplateInterface::MESSAGE_TYPE_FAILURE,
                $this->lng->txt('no_permission'),
                true
            );
            return null;
        }
        $row_ids = $this->readRowIdsFromRequest($row_id_token->getName());
        $row_ids = $this->expandAllObjectsPlaceholder($row_ids);
        if ($row_ids === []) {
            $this->tpl->setOnScreenMessage(
                ilGlobalTemplateInterface::MESSAGE_TYPE_FAILURE,
                $this->getSelectionErrorMessage(),
                true
            );
            return null;
        }
        $records = $this->resolveDeletableObjectRecords($row_ids);
        if ($records === []) {
            $this->tpl->setOnScreenMessage(
                ilGlobalTemplateInterface::MESSAGE_TYPE_FAILURE,
                $this->getSelectionErrorMessage(),
                true
            );
            return null;
        }
        foreach ($records as $r) {
            $oid = (int) $r['booking_object_id'];
            $obj = new ilBookingObject($oid);
            $obj->deleteReservationsAndCalEntries($oid);
            $obj->delete();
        }
        $this->tpl->setOnScreenMessage(
            ilGlobalTemplateInterface::MESSAGE_TYPE_SUCCESS,
            $this->lng->txt('book_object_deleted'),
            true
        );
        return null;
    }

    /**
     * @param list<string> $row_ids
     * @return list<array{booking_object_id: int, title: string}>
     */
    private function resolveDeletableObjectRecords(array $row_ids): array
    {
        $by_object = [];
        foreach ($row_ids as $row_id) {
            $p = BookableItemTableData::parseRowIdForBulk($row_id);
            if ($p === null) {
                continue;
            }
            $oid = (int) $p['object_id'];
            if (isset($by_object[$oid])) {
                continue;
            }
            if ($this->objectHasActiveReservations($oid)) {
                continue;
            }
            $by_object[$oid] = [
                'booking_object_id' => $oid,
                'title' => (new ilBookingObject($oid))->getTitle(),
            ];
        }
        return array_values($by_object);
    }

    private function objectHasActiveReservations(int $booking_object_id): bool
    {
        $res = ilBookingReservation::getList([$booking_object_id], 1000, 0, []);
        foreach ($res['data'] ?? [] as $row) {
            if ((int) $row['status'] !== ilBookingReservation::STATUS_CANCELLED) {
                return true;
            }
        }
        return false;
    }

    private function resolveStringParameter(string $name, string $default): string
    {
        $w = $this->http->wrapper();
        if ($w->query()->has($name)) {
            return (string) $w->query()->retrieve(
                $name,
                $this->refinery->kindlyTo()->string()
            );
        }
        if ($w->post()->has($name)) {
            return (string) $w->post()->retrieve(
                $name,
                $this->refinery->kindlyTo()->string()
            );
        }
        return $default;
    }

    /**
     * @return list<string>
     */
    private function readRowIdsFromRequest(string $param_name): array
    {
        $wrapper = $this->http->wrapper();
        if (!$wrapper->query()->has($param_name) && !$wrapper->post()->has($param_name)) {
            return [];
        }
        $bag = $wrapper->query()->has($param_name) ? $wrapper->query() : $wrapper->post();
        $tokens = $bag->retrieve(
            $param_name,
            $this->refinery->custom()->transformation(function ($v) {
                if (is_array($v)) {
                    return $v;
                }
                if ($v === null || $v === '') {
                    return [];
                }
                return [$v];
            })
        );
        if (!is_array($tokens)) {
            return $tokens ? [(string) $tokens] : [];
        }
        $out = [];
        foreach ($tokens as $t) {
            if ((string) $t !== '') {
                $out[] = (string) $t;
            }
        }
        return $out;
    }

    /**
     * @param list<string> $ids
     * @return list<string>
     */
    private function expandAllObjectsPlaceholder(array $ids): array
    {
        foreach ($ids as $id) {
            if ($id === BookableItemTable::ROW_ID_ALL_OBJECTS) {
                return $this->data->getAllRowIdStringsForFilter(
                    ($this->get_filter_data)()
                );
            }
        }
        return $ids;
    }
}
