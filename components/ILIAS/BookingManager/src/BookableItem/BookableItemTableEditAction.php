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
use ILIAS\BookingManager\HttpService;
use ILIAS\UI\Component\Table\Action\Action;
use ILIAS\UI\Factory as UIFactory;
use ILIAS\UI\URLBuilder;
use ILIAS\UI\URLBuilderToken;
use ilCtrlInterface;
use ilLanguage;

class BookableItemTableEditAction implements TableAction
{
    public const string ACTION_ID = 'edit_object';

    public function __construct(
        private readonly UIFactory $ui_factory,
        private readonly ilLanguage $lng,
        private readonly AccessManager $access,
        private readonly ilCtrlInterface $ctrl,
        private readonly HttpService $http_service,
        private readonly ilBookingObjectGUI $object_gui,
        private readonly int $ref_id,
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
        return $this->ui_factory->table()->action()->single(
            $this->lng->txt('edit'),
            $url_builder
                ->withParameter($action_token, self::ACTION_ID)
                ->withParameter($action_type_token, 'edit'),
            $row_id_token
        );
    }

    public function onExecute(
        URLBuilder $url_builder,
        URLBuilderToken $row_id_token,
        URLBuilderToken $action_token,
        URLBuilderToken $action_type_token
    ): mixed {
        $row_id = (string) $this->http_service->resolveRowParameter($row_id_token->getName());
        $p = BookableItemTableData::parseRowIdForBulk($row_id);
        if ($p === null) {
            $this->ctrl->redirect($this->object_gui, 'render');
            return null;
        }
        $this->ctrl->setParameter($this->object_gui, 'object_id', (string) $p['object_id']);
        $this->ctrl->redirect($this->object_gui, 'edit');
        return null;
    }

    public function allowActionForRecord(mixed $record): bool
    {
        return \is_array($record) && (bool) ($record['may_edit'] ?? false);
    }

    public function getSelectionErrorMessage(): ?string
    {
        return null;
    }
}
