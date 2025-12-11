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

namespace ILIAS\BookingManager\Common\Table;

use ilCtrlInterface;
use ilGlobalTemplateInterface;
use ILIAS\BookingManager\HttpService;
use ILIAS\Refinery\Factory as Refinery;
use ILIAS\UI\Component\Modal\Modal;
use ILIAS\UI\Component\Table\Action\Action;
use ILIAS\UI\Component\Table\DataRow;
use ILIAS\UI\Factory as UIFactory;
use ILIAS\UI\Renderer as UIRenderer;
use ILIAS\UI\URLBuilder;
use ILIAS\UI\URLBuilderToken;
use ilLanguage;

class TableActions
{
    /**
     * @param array<string, TableAction> $actions
     */
    public function __construct(
        protected readonly ilCtrlInterface $ctrl,
        protected readonly ilLanguage $lng,
        protected readonly ilGlobalTemplateInterface $tpl,
        protected readonly UIFactory $ui_factory,
        protected readonly UIRenderer $ui_renderer,
        protected readonly Refinery $refinery,
        protected readonly HttpService $http_service,
        protected readonly array $actions
    ) {
    }

    public function getEnabledActions(
        URLBuilder $url_builder,
        URLBuilderToken $row_id_token,
        URLBuilderToken $action_token,
        URLBuilderToken $action_type_token
    ): array {
        return array_filter(
            array_map(
                function (TableAction $action) use (
                    $url_builder,
                    $row_id_token,
                    $action_token,
                    $action_type_token
                ): ?Action {
                    if (!$action->isAvailable()) {
                        return null;
                    }

                    return $action->getTableAction(
                        $url_builder,
                        $row_id_token,
                        $action_token,
                        $action_type_token
                    );
                },
                $this->actions
            )
        );
    }

    public function execute(
        URLBuilder $url_builder,
        URLBuilderToken $row_id_token,
        URLBuilderToken $action_token,
        URLBuilderToken $action_type_token
    ): ?Modal {
        if (!$this->http_service->has($action_token->getName())) {
            return null;
        }

        $action_id = $this->http_service->resolveRowParameter($action_token->getName());

        if ($action_id === null || !isset($this->actions[$action_id])) {
            return null;
        }

        $action = $this->actions[$action_id];
        $response = $action->onExecute(
            $url_builder,
            $row_id_token,
            $action_token,
            $action_type_token
        );

        if ($response instanceof Modal) {
            $this->http_service->sendAsync(
                $this->ui_renderer->renderAsync(
                    $response
                )
            );
        }

        return null;
    }

    public function onDataRow(DataRow $row, mixed $record): DataRow
    {
        return array_reduce(
            array_keys($this->actions),
            fn(DataRow $c, string $v): DataRow => $this->actions[$v]->allowActionForRecord($record)
                ? $c
                : $c->withDisabledAction($v),
            $row
        );
    }
}
