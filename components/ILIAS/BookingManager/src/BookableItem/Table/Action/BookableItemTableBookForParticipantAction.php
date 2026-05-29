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

namespace ILIAS\BookingManager\BookableItem\Table\Action;

use ilBookingProcessWithoutScheduleGUI;
use ilBookingProcessWithScheduleGUI;
use ilCtrlInterface;
use ILIAS\BookingManager\Access\AccessManager;
use ILIAS\BookingManager\Common\HttpService;
use ILIAS\BookingManager\Common\Table\TableAction;
use ILIAS\Refinery\Factory as Refinery;
use ILIAS\UI\Component\Table\Action\Action;
use ILIAS\UI\Factory as UIFactory;
use ILIAS\UI\URLBuilder;
use ILIAS\UI\URLBuilderToken;
use ilLanguage;
use ilObjBookingPool;

class BookableItemTableBookForParticipantAction implements TableAction
{
    public const string ACTION_ID = 'book_for_participant';
    public const string ACTION_LABEL = 'book_assign_participants';

    public function __construct(
        private readonly UIFactory $ui_factory,
        private readonly ilLanguage $lng,
        private readonly ilCtrlInterface $ctrl,
        private readonly HttpService $http,
        private readonly Refinery $refinery,
        private readonly AccessManager $access,
        private readonly ilObjBookingPool $pool,
        private readonly int $ref_id,
        private readonly bool $active_management,
    ) {
    }

    public function getActionId(): string
    {
        return self::ACTION_ID;
    }

    public function getActionLabel(): string
    {
        return self::ACTION_LABEL;
    }

    public function isAvailable(): bool
    {
        if ($this->pool->getScheduleType() === ilObjBookingPool::TYPE_NO_SCHEDULE_PREFERENCES) {
            return false;
        }

        return $this->active_management && $this->access->canManageObjects($this->ref_id);
    }

    public function getTableAction(
        URLBuilder $url_builder,
        URLBuilderToken $row_id_token,
        URLBuilderToken $action_token,
        URLBuilderToken $action_type_token
    ): Action {
        return $this->ui_factory->table()->action()->single(
            $this->lng->txt(self::ACTION_LABEL),
            $url_builder
                ->withParameter($action_token, self::ACTION_ID)
                ->withParameter($action_type_token, 'redirect'),
            $row_id_token
        );
    }

    public function onExecute(
        URLBuilder $url_builder,
        URLBuilderToken $row_id_token,
        URLBuilderToken $action_token,
        URLBuilderToken $action_type_token
    ): mixed {
        $row_ids = $this->resolveRowIds($row_id_token->getName());
        if ($row_ids === []) {
            return null;
        }

        $first_parts = explode('_', $row_ids[0]);
        $primary_object_id = (int) $first_parts[0];
        if ($primary_object_id <= 0) {
            return null;
        }

        $target_class = $this->pool->getScheduleType() === ilObjBookingPool::TYPE_FIX_SCHEDULE
            ? ilBookingProcessWithScheduleGUI::class
            : ilBookingProcessWithoutScheduleGUI::class;

        $this->ctrl->setParameterByClass($target_class, 'object_id', (string) $primary_object_id);

        if (isset($first_parts[1], $first_parts[2])) {
            $this->ctrl->setParameterByClass(
                $target_class,
                'slot',
                $first_parts[1] . '_' . $first_parts[2]
            );
            $this->ctrl->setParameterByClass($target_class, 'seed', date('Y-m-d', (int) $first_parts[1]));
        }

        $this->ctrl->redirectByClass($target_class, 'assignParticipants');
        return null;
    }

    public function allowActionForRecord(mixed $record): bool
    {
        if (!is_array($record)) {
            return false;
        }

        if ($this->pool->getOverallLimit() && (int) $record['available'] <= 0) {
            return false;
        }

        return (bool) $record['is_available'];
    }

    /**
     * @return string[]
     */
    private function resolveRowIds(string $key): array
    {
        $value = $this->http->get(
            $key,
            $this->refinery->custom()->transformation(
                static function (mixed $raw): array {
                    if ($raw === null || $raw === '') {
                        return [];
                    }

                    if (is_array($raw)) {
                        return array_values(array_map('strval', $raw));
                    }

                    return [(string) $raw];
                }
            )
        ) ?? [];

        return array_values(array_filter($value, static fn(string $v): bool => $v !== ''));
    }
}
