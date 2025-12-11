<?php

namespace ILIAS\BookingManager\Common;

use ILIAS\BookingManager\HttpService;
use ILIAS\UI\Component\Modal\Modal;
use ILIAS\UI\URLBuilder;
use ILIAS\UI\URLBuilderToken;

/**
 * @property HttpService $http_service;
 * @template RecordType
 */
trait TableActionModalTrait
{
    public const SHOW_MODAL_ACTION = 'showModalAction';
    public const SUBMIT_MODAL_ACTION = 'submitModalAction';

    public function onExecute(
        URLBuilder $url_builder,
        URLBuilderToken $row_id_token,
        URLBuilderToken $action_token,
        URLBuilderToken $action_type_token
    ): ?Modal {
        return match($this->http_service->resolveRowParameter($action_type_token->getName())) {
            self::SUBMIT_MODAL_ACTION => $this->submit($url_builder, $row_id_token, $action_token, $action_type_token),
            default => $this->showModal($url_builder, $row_id_token, $action_token, $action_type_token),
        };
    }

    protected function showModal(
        URLBuilder $url_builder,
        URLBuilderToken $row_id_token,
        URLBuilderToken $action_token,
        URLBuilderToken $action_type_token,
    ): ?Modal {
        $selected_ids = $this->http_service->resolveRowParameters($row_id_token->getName());

        $selected_records = $selected_ids === [] ? [] : array_filter(
            $this->resolveRecords($selected_ids === 'ALL_OBJECTS' ? [] : $selected_ids),
            static fn(array $record): bool => !isset($record['is_used']) ? true : !$record['is_used']
        );

        return $this->getModal(
            $url_builder
                ->withParameter($row_id_token, $selected_ids)
                ->withParameter($action_token, $this->getActionId())
                ->withParameter($action_type_token, self::SUBMIT_MODAL_ACTION),
            $selected_records,
            false
        );
    }

    protected function submit(
        URLBuilder $url_builder,
        URLBuilderToken $row_id_token,
        URLBuilderToken $action_token,
        URLBuilderToken $action_type_token,
    ): ?Modal {
        $selected_ids = $this->http_service->resolveRowParameters($row_id_token->getName());

        if ($selected_ids === []) {
            $this->showErrorMessage($this->getSelectionErrorMessage());
            return null;
        }

        $selected_records = array_filter(
            $this->resolveRecords($selected_ids === 'ALL_OBJECTS' ? [] : $selected_ids),
            static fn(array $record): bool => !isset($record['is_used']) ? true : !$record['is_used']
        );

        return $this->onSubmit(
            $url_builder
                ->withParameter($row_id_token, $selected_ids)
                ->withParameter($action_token, $this->getActionId())
                ->withParameter($action_type_token, self::SUBMIT_MODAL_ACTION),
            $selected_records,
            false
        );
    }

    protected function showErrorMessage(string $message): void
    {
        $this->tpl->setOnScreenMessage(\ilGlobalTemplateInterface::MESSAGE_TYPE_FAILURE, $message, true);
    }

    protected function showSuccessMessage(string $message): void
    {
        $this->tpl->setOnScreenMessage(\ilGlobalTemplateInterface::MESSAGE_TYPE_SUCCESS, $message, true);
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

    abstract protected function resolveRecords(array $selected_ids): array;
}
