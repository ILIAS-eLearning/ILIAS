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

use ilGlobalTemplateInterface;
use ILIAS\BookingManager\Common\HttpService;
use ILIAS\UI\Component\Modal\Modal;
use ILIAS\UI\URLBuilder;
use ILIAS\UI\URLBuilderToken;
use ILIAS\UI\Renderer as UIRenderer;

/**
 * @template RecordType
 */
trait TableActionModalTrait
{
    public const string SHOW_MODAL_ACTION = 'showModalAction';

    public const string SUBMIT_MODAL_ACTION = 'submitModalAction';

    private readonly HttpService $http;

    private readonly UIRenderer $ui_renderer;

    public function onExecute(
        URLBuilder $url_builder,
        URLBuilderToken $row_id_token,
        URLBuilderToken $action_token,
        URLBuilderToken $action_type_token
    ): ?Modal {
        return match($this->http->resolveRowParameter($action_type_token->getName())) {
            self::SUBMIT_MODAL_ACTION => $this->submit($url_builder, $row_id_token, $action_token, $action_type_token),
            default => $this->showModal($url_builder, $row_id_token, $action_token, $action_type_token),
        };
    }

    protected function showModal(
        URLBuilder $url_builder,
        URLBuilderToken $row_id_token,
        URLBuilderToken $action_token,
        URLBuilderToken $action_type_token,
    ): void {
        $selected_ids = $this->http->resolveRowParameters($row_id_token->getName());
        $all_records_selected = $selected_ids === HttpService::ALL_OBJECTS;

        if ($all_records_selected) {
            $selected_ids = null;
        } else {
            $selected_ids = is_string($selected_ids) ? [] : $selected_ids;
        }

        $selected_records = array_filter(
            $this->resolveRecords($selected_ids),
            fn(mixed $record): bool => $this->allowActionForRecord($record)
        );

        $this->http->sendAsync(
            $this->ui_renderer->renderAsync(
                $this->getModal(
                    $url_builder
                        ->withParameter($row_id_token, $all_records_selected ? HttpService::ALL_OBJECTS : ($selected_ids ?? []))
                        ->withParameter($action_token, $this->getActionId())
                        ->withParameter($action_type_token, self::SUBMIT_MODAL_ACTION),
                    $selected_records,
                    $all_records_selected
                )
            )
        );
    }

    protected function submit(
        URLBuilder $url_builder,
        URLBuilderToken $row_id_token,
        URLBuilderToken $action_token,
        URLBuilderToken $action_type_token,
    ): ?Modal {
        $selected_ids = $this->http->resolveRowParameters($row_id_token->getName());
        $all_records_selected = $selected_ids === HttpService::ALL_OBJECTS;

        if ($all_records_selected) {
            $selected_ids = null;
        } else {
            $selected_ids = is_string($selected_ids) ? [] : $selected_ids;
        }

        if (!$all_records_selected && $selected_ids === []) {
            $this->showErrorMessage($this->getSelectionErrorMessage());
            return null;
        }

        $selected_records = array_filter(
            $this->resolveRecords($selected_ids),
            fn(mixed $record): bool => $this->allowActionForRecord($record)
        );

        if ($selected_records === []) {
            $this->showErrorMessage($this->getSelectionErrorMessage());
            return null;
        }

        return $this->onSubmit(
            $url_builder
                ->withParameter($row_id_token, $all_records_selected ? HttpService::ALL_OBJECTS : $selected_ids)
                ->withParameter($action_token, $this->getActionId())
                ->withParameter($action_type_token, self::SUBMIT_MODAL_ACTION),
            $selected_records,
            $all_records_selected
        );
    }

    protected function showErrorMessage(string $message): void
    {
        $this->tpl->setOnScreenMessage(ilGlobalTemplateInterface::MESSAGE_TYPE_FAILURE, $message, true);
    }

    protected function showSuccessMessage(string $message): void
    {
        $this->tpl->setOnScreenMessage(ilGlobalTemplateInterface::MESSAGE_TYPE_SUCCESS, $message, true);
    }

    public function getSelectionErrorMessage(): ?string
    {
        return $this->lng->txt('no_valid_selection');
    }

    /**
     * @param list<RecordType> $selected_records
     */
    abstract protected function getModal(
        URLBuilder $url_builder,
        array $selected_records,
        bool $all_records_selected
    ): ?Modal;

    /**
     * @param list<RecordType> $selected_records
     */
    abstract protected function onSubmit(
        URLBuilder $url_builder,
        array $selected_records,
        bool $all_records_selected
    ): ?Modal;

    abstract protected function resolveRecords(?array $selected_ids = null): array;
}
