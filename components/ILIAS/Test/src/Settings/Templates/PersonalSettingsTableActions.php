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

namespace ILIAS\Test\Settings\Templates;

use ILIAS\Language\Language;
use ILIAS\Test\RequestDataCollector;
use ILIAS\Test\ResponseHandler;
use ILIAS\UI\Component\Modal\Modal;
use ILIAS\UI\Renderer as UIRenderer;
use ILIAS\UI\Factory as UIFactory;
use ILIAS\UI\URLBuilder;
use ILIAS\UI\URLBuilderToken;

class PersonalSettingsTableActions
{
    public const string ROW_ID_PARAMETER = 't_id';
    public const string ACTION_PARAMETER = 'action';
    public const string ACTION_TYPE_PARAMETER = 'action_type';
    public const string SUBMIT_ACTION = 'submitTableAction';

    /**
     * @param array<string, TableAction> $actions
     */
    public function __construct(
        private readonly RequestDataCollector $test_request,
        private readonly ResponseHandler $test_response,
        private readonly UIRenderer $ui_renderer,
        private readonly UIFactory $ui_factory,
        private readonly Language $lng,
        private readonly \ilObjUser $user,
        private readonly PersonalSettingsRepository $repository,
        private readonly array $actions
    ) {
    }

    public function getActions(
        URLBuilder $url_builder,
        URLBuilderToken $row_id_token,
        URLBuilderToken $action_token,
        URLBuilderToken $action_type_token
    ): array {
        return array_map(fn(TableAction $action) => $action->buildTableAction(
            $url_builder,
            $row_id_token,
            $action_token,
            $action_type_token
        ), $this->actions);
    }

    public function getAction(string $action_id): ?TableAction
    {
        return $this->actions[$action_id] ?? null;
    }

    public function perform(
        URLBuilder $url_builder,
        URLBuilderToken $row_id_token,
        URLBuilderToken $action_token,
        URLBuilderToken $action_type_token
    ): ?Modal {
        $selection_ids = $this->test_request->getMultiSelectionIds($row_id_token->getName());

        $selection = $selection_ids === 'ALL_OBJECTS'
            ? $this->repository->getForUser()
            : $this->repository->getByIds($selection_ids);

        if ($selection === []) {
            return $this->fail('personal_settings_invalid_selection');
        }

        if (!$this->checkAccess($selection)) {
            return $this->fail('no_permission');
        }

        $action = $this->getAction($this->test_request->strVal($action_token->getName()));
        try {
            $url_builder = $url_builder
               ->withParameter($row_id_token, $selection_ids)
               ->withParameter($action_token, $action->getActionId())
               ->withParameter($action_type_token, self::SUBMIT_ACTION);

            return match ($this->test_request->strVal($action_type_token->getName())) {
                self::SUBMIT_ACTION => $this->submit(
                    $action,
                    $url_builder,
                    $selection,
                ),
                default => $this->showModal(
                    $action,
                    $url_builder,
                    $selection,
                )
            };
        } catch (\InvalidArgumentException $e) {
            return $this->fail($e->getMessage());
        }
    }

    private function submit(
        TableAction $action,
        URLBuilder $url_builder,
        array $selection,
    ): ?Modal {
        return $action->onSubmit(
            $url_builder,
            $this->test_request->getRequest(),
            $selection,
        );
    }

    private function showModal(
        TableAction $action,
        URLBuilder $url_builder,
        array $selection,
    ): null {
        $this->test_response->sendAsync(
            $this->ui_renderer->renderAsync(
                $action->buildModal(
                    $url_builder,
                    $selection,
                )
            )
        );
        return null;
    }

    /**
     * @param PersonalSettingsTemplate[] $selection
     */
    private function checkAccess(array $selection): bool
    {
        foreach ($selection as $template) {
            if ($this->user->getId() !== $template->getUserId()) {
                return false;
            }
        }
        return true;
    }

    private function fail(string $message_key): null
    {
        $this->test_response->sendAsync(
            $this->ui_renderer->renderAsync(
                $this->ui_factory->messageBox()->failure($this->lng->txt($message_key))
            )
        );
        return null;
    }
}
