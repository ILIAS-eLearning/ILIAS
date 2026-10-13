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

namespace ILIAS\News\NewsItem\Table\Action;

use ilCtrl;
use ILIAS\News\Access\NewsAccess;
use ILIAS\News\Common\HttpService;
use ILIAS\News\Common\Table\TableAction;
use ILIAS\UI\Component\Table\Action\Action;
use ILIAS\UI\Factory;
use ILIAS\UI\URLBuilder;
use ILIAS\UI\URLBuilderToken;
use ilLanguage;
use ilNewsItem;
use ilNewsItemGUI;

class EditNewsItemTableAction implements TableAction
{
    public const string ACTION_ID = 'edit';

    public function __construct(
        private readonly Factory $ui_factory,
        private readonly ilLanguage $lng,
        private readonly NewsAccess $news_access,
        private readonly ilCtrl $ctrl,
        private readonly HttpService $http_service
    ) {
    }

    public function getActionId(): string
    {
        return self::ACTION_ID;
    }

    public function getActionLabel(): string
    {
        return $this->lng->txt('edit');
    }

    public function isAvailable(): bool
    {
        return $this->news_access->canAccessManageOverview();
    }

    public function getTableAction(
        URLBuilder $url_builder,
        URLBuilderToken $row_id_token,
        URLBuilderToken $action_token,
        URLBuilderToken $action_type_token
    ): Action {
        return $this->ui_factory->table()->action()->single(
            $this->getActionLabel(),
            $url_builder->withParameter($action_token, $this->getActionId()),
            $row_id_token
        );
    }

    public function allowActionForRecord(mixed $record): bool
    {
        $id = (int) ($record['id'] ?? 0);
        if ($id <= 0) {
            return false;
        }

        return $this->news_access->canEdit(new ilNewsItem($id));
    }

    public function onExecute(
        URLBuilder $url_builder,
        URLBuilderToken $row_id_token,
        URLBuilderToken $action_token,
        URLBuilderToken $action_type_token
    ): mixed {
        $selected = $this->http_service->resolveRowParameters($row_id_token->getName());
        if (!isset($selected[0])) {
            return null;
        }

        $this->ctrl->setParameterByClass(ilNewsItemGUI::class, 'news_item_id', $selected[0]);
        $this->ctrl->redirectByClass(ilNewsItemGUI::class, 'editNewsItem');
        return null;
    }
}
