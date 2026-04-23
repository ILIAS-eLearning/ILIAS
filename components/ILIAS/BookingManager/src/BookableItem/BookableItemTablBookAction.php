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

use ilBookingObjectGUI;
use ILIAS\BookingManager\Access\AccessManager;
use ILIAS\BookingManager\Common\Table\TableAction;
use ILIAS\UI\Component\Table\Action\Action;
use ILIAS\UI\Factory as UIFactory;
use ILIAS\UI\URLBuilder;
use ILIAS\UI\URLBuilderToken;
use ilLanguage;
use ILIAS\HTTP\Services as HttpServices;
use ILIAS\Refinery\Factory as Refinery;

class BookableItemTableBookAction implements TableAction
{
    public const string ACTION_ID = 'book';

    public function __construct(
        private readonly UIFactory $ui_factory,
        private readonly ilLanguage $lng,
        private readonly AccessManager $access,
        private readonly ilBookingObjectGUI $object_gui,
        private readonly HttpServices $http,
        private readonly Refinery $refinery,
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
        return $this->access->canManageOwnReservations($this->ref_id);
    }

    public function getTableAction(
        URLBuilder $url_builder,
        URLBuilderToken $row_id_token,
        URLBuilderToken $action_token,
        URLBuilderToken $action_type_token
    ): Action {
        return $this->ui_factory->table()->action()->standard(
            $this->lng->txt('book_bulk_book'),
            $url_builder->withParameter(
                $action_token,
                self::ACTION_ID
            ),
            $row_id_token
        )->withAsync();
    }

    public function onExecute(
        URLBuilder $url_builder,
        URLBuilderToken $row_id_token,
        URLBuilderToken $action_token,
        URLBuilderToken $action_type_token
    ): mixed {
        $this->object_gui->outputBulkBookModal(
            $this->expandAllObjectsPlaceholder($this->readRowIdsFromRequest($row_id_token->getName()))
        );
        return null;
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

    public function allowActionForRecord(mixed $record): bool
    {
        if (!\is_array($record) || !isset($record['row'], $record['current_user_bookings'])) {
            return false;
        }
        return $this->data->rowStillAllowsUserBulkBook(
            $record['row'],
            (int) $record['current_user_bookings']
        );
    }

    public function getSelectionErrorMessage(): ?string
    {
        return null;
    }
}
