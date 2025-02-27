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

namespace ILIAS\Export\ExportHandler\Table;

use ilCalendarSettings;
use ilCtrl;
use ilExportExportOptionXML;
use ILIAS\Data\Factory as ilDataFactory;
use ILIAS\Data\ObjectId;
use ILIAS\DI\UIServices as ilUIServices;
use ILIAS\Export\ExportHandler\Factory as ilExportHandler;
use ILIAS\Export\ExportHandler\I\Consumer\Context\HandlerInterface as ilExportHandlerConsumerContextInterface;
use ILIAS\Export\ExportHandler\I\Consumer\ExportOption\CollectionInterface as ilExportHandlerConsumerExportOptionCollectionInterface;
use ILIAS\Export\ExportHandler\I\Consumer\File\Identifier\CollectionInterface as ilExportHandlerConsumerFileIdentifierCollectionInterface;
use ILIAS\Export\ExportHandler\I\Table\HandlerInterface as ilExportHandlerTableInterface;
use ILIAS\Export\ExportHandler\I\Table\RowId\CollectionInterface as ilExportHandlerTableRowCollectionInterface;
use ILIAS\HTTP\Services as ilHTTPServices;
use ILIAS\Refinery\Factory as ilRefineryFactory;
use ILIAS\UI\Component\Table\Data as ilDataTable;
use ILIAS\UI\URLBuilder;
use ILIAS\UI\URLBuilderToken as ilURLBuilderToken;
use ilLanguage;
use ilObjUser;
use JetBrains\PhpStorm\NoReturn;

class Handler implements ilExportHandlerTableInterface
{
    protected const ALL_OBJECTS = "ALL_OBJECTS";
    protected const TABLE_COL_LNG_TYPE = 'exp_type';
    protected const TABLE_COL_LNG_FILE = 'exp_file';
    protected const TABLE_COL_LNG_SIZE = 'exp_size';
    protected const TABLE_COL_LNG_TIMESTAMP = 'exp_timestamp';
    protected const TABLE_COL_LNG_PUBLIC_ACCESS = 'exp_public_access';
    protected const TABLE_ID = "export";
    protected const ROW_ID = "row_ids";
    protected const TABLE_ACTION_ID = "table_action";
    protected const ACTION_DELETE = "delete";
    protected const ACTION_DOWNLOAD = "download";
    protected const ACTION_PUBLIC_ACCESS = "enable_pa";
    protected const ACTION_CONFIRM_DELETE = "delete_selected";

    protected ilUIServices $ui_services;
    protected ilHTTPServices $http_services;
    protected ilRefineryFactory $refinery;
    protected ilObjUser $user;
    protected ilCtrl $ctrl;
    protected ilLanguage $lng;
    protected ilExportHandler $export_handler;
    protected ilDataFactory $data_factory;
    protected URLBuilder $url_builder;
    protected ilURLBuilderToken $action_parameter_token;
    protected ilURLBuilderToken $row_id_token;
    protected ilExportHandlerConsumerExportOptionCollectionInterface $export_options;
    protected ilDataTable $table;
    protected ilExportHandlerConsumerContextInterface $context;

    public function __construct(
        ilUIServices $ui_services,
        ilHTTPServices $http_services,
        ilRefineryFactory $refinery,
        ilObjUser $user,
        ilLanguage $lng,
        ilCtrl $ctrl,
        ilExportHandler $export_handler,
        ilDataFactory $data_factory
    ) {
        $this->http_services = $http_services;
        $this->ui_services = $ui_services;
        $this->refinery = $refinery;
        $this->lng = $lng;
        $this->lng->loadLanguageModule("export");
        $this->user = $user;
        $this->ctrl = $ctrl;
        $this->export_handler = $export_handler;
        $this->data_factory = $data_factory;
    }

    protected function getColumns(): array
    {
        if ((int) $this->user->getTimeFormat() === ilCalendarSettings::TIME_FORMAT_12) {
            $format = $this->data_factory->dateFormat()->withTime12($this->user->getDateFormat());
        } else {
            $format = $this->data_factory->dateFormat()->withTime24($this->user->getDateFormat());
        }
        return [
            self::TABLE_COL_TYPE => $this->ui_services->factory()->table()->column()->text(
                $this->lng->txt(self::TABLE_COL_LNG_TYPE)
            )->withHighlight(true),
            self::TABLE_COL_FILE => $this->ui_services->factory()->table()->column()->text(
                $this->lng->txt(self::TABLE_COL_LNG_FILE)
            )->withHighlight(true),
            self::TABLE_COL_SIZE => $this->ui_services->factory()->table()->column()->number(
                $this->lng->txt(self::TABLE_COL_LNG_SIZE)
            )
                ->withHighlight(true)
                ->withDecimals(4),
            self::TABLE_COL_TIMESTAMP => $this->ui_services->factory()->table()->column()->date(
                $this->lng->txt(self::TABLE_COL_LNG_TIMESTAMP),
                $format
            ),
            self::TABLE_COL_PUBLIC_ACCESS => $this->ui_services->factory()->table()->column()->statusIcon(
                $this->lng->txt(self::TABLE_COL_LNG_PUBLIC_ACCESS),
            )
        ];
    }

    protected function getActions(): array
    {
        $this->url_builder = new URLBuilder($this->data_factory->uri($this->http_services->request()->getUri()->__toString()));
        list($this->url_builder, $this->action_parameter_token, $this->row_id_token) =
            $this->url_builder->acquireParameters(
                ['datatable', self::TABLE_ID],
                self::TABLE_ACTION_ID,
                self::ROW_ID
            );
        return [
            self::ACTION_PUBLIC_ACCESS => $this->ui_services->factory()->table()->action()->single(
                $this->lng->txt('exp_toggle_public_access'),
                $this->url_builder->withParameter($this->action_parameter_token, self::ACTION_PUBLIC_ACCESS),
                $this->row_id_token
            ),
            self::ACTION_DOWNLOAD => $this->ui_services->factory()->table()->action()->single(
                $this->lng->txt('download'),
                $this->url_builder->withParameter($this->action_parameter_token, self::ACTION_DOWNLOAD),
                $this->row_id_token
            ),
            self::ACTION_DELETE => $this->ui_services->factory()->table()->action()->standard(
                $this->lng->txt('delete'),
                $this->url_builder->withParameter($this->action_parameter_token, self::ACTION_DELETE),
                $this->row_id_token
            )->withAsync()
        ];
    }

    /**
     * @param array<string, ilExportHandlerConsumerFileIdentifierCollectionInterface> $ids_sorted
     */
    #[NoReturn] protected function showDeleteModal(array $ids_sorted): void
    {
        $items = [];
        $ids = [];
        foreach ($ids_sorted as $export_option_id => $file_identifiers) {
            $export_option = $this->export_options->getById($export_option_id);
            foreach ($export_option->getFileSelection($this->context, $file_identifiers) as $file_info) {
                $table_row_id = $this->export_handler->table()->rowId()->handler()
                    ->withExportOptionId($export_option_id)
                    ->withFileIdentifier($file_info->getFileIdentifier());
                $ids[] = $table_row_id->getCompositId();
                $items[] = $this->ui_services->factory()->modal()->interruptiveItem()->standard(
                    $table_row_id->getCompositId(),
                    $file_info->getFileName()
                );
            }
        }
        echo($this->ui_services->renderer()->renderAsync([
            $this->ui_services->factory()->modal()->interruptive(
                $this->lng->txt('confirm'),
                $this->lng->txt('exp_really_delete'),
                (string) $this->url_builder
                    ->withParameter(
                        $this->action_parameter_token,
                        self::ACTION_CONFIRM_DELETE
                    )->withParameter(
                        $this->row_id_token,
                        $ids
                    )->buildURI()
            )->withAffectedItems($items)
        ]));
        exit();
    }

    /**
     * @param array<string, ilExportHandlerConsumerFileIdentifierCollectionInterface> $ids_sorted
     */
    protected function deleteItems(array $ids_sorted): void
    {
        $object_id = $this->data_factory->objId($this->context->exportObject()->getId());
        foreach ($ids_sorted as $export_option_id => $file_identifiers) {
            $export_option = $this->export_options->getById($export_option_id);
            if (
                $this->export_handler->publicAccess()->handler()->hasPublicAccessFile($object_id) and
                $this->export_handler->publicAccess()->handler()->getPublicAccessFileExportOptionId($object_id) === $export_option->getExportOptionId() and
                in_array($this->export_handler->publicAccess()->handler()->getPublicAccessFileIdentifier($object_id), $file_identifiers->toStringArray())
            ) {
                $this->export_handler->publicAccess()->handler()->removePublicAccessFile($object_id);
            }
            $export_option->onDeleteFiles(
                $this->context,
                $file_identifiers
            );
        }
    }

    /**
     * @param array<string, ilExportHandlerConsumerFileIdentifierCollectionInterface> $ids_sorted
     */
    protected function markAsPublicAccess(array $ids_sorted): void
    {
        $pa_repository = $this->export_handler->publicAccess()->repository()->handler();
        $pa_repository_element_factory = $this->export_handler->publicAccess()->repository()->element();
        $pa_repository_key_factory = $this->export_handler->publicAccess()->repository()->key();
        $pa_repository_values_factory = $this->export_handler->publicAccess()->repository()->values();
        $obj_id = $this->data_factory->objId($this->context->exportObject()->getId());
        foreach ($ids_sorted as $export_option_id => $file_identifiers) {
            $export_option = $this->export_options->getById($export_option_id);
            $type_allowed = $export_option->isPublicAccessPossible();
            foreach ($export_option->getFileSelection($this->context, $file_identifiers) as $file_info) {
                $key = $pa_repository_key_factory->handler()
                    ->withObjectId($obj_id);
                if ($file_info->getPublicAccessEnabled() and $pa_repository->hasElement($key)) {
                    $pa_repository->deleteElement($key);
                    continue;
                }
                if (
                    !$export_option->isPublicAccessPossible() or
                    !$file_info->getPublicAccessPossible() or
                    !$type_allowed
                ) {
                    continue;
                }
                $values = $pa_repository_values_factory->handler()
                    ->withIdentification($file_info->getFileIdentifier())
                    ->withExportOptionId($export_option->getExportOptionId());
                $element = $pa_repository_element_factory->handler()
                    ->withKey($key)
                    ->withValues($values);
                $pa_repository->storeElement($element);
            }
        }
        $this->ctrl->redirect($this->context->exportGUIObject(), $this->context->exportGUIObject()::CMD_LIST_EXPORT_FILES);
    }

    /**
     * @param array<string, ilExportHandlerConsumerFileIdentifierCollectionInterface> $ids_sorted
     */
    protected function downloadItems(array $ids_sorted): void
    {
        foreach ($ids_sorted as $export_option_id => $file_identifiers) {
            $export_option = $this->export_options->getById($export_option_id);
            $export_option->onDownloadFiles(
                $this->context,
                $file_identifiers
            );
        }
    }

    protected function initTable(): void
    {
        if (isset($this->table)) {
            return;
        }
        $this->table = $this->ui_services->factory()->table()->data(
            $this->lng->txt("exp_export_files"),
            $this->getColumns(),
            $this->export_handler->table()->dataRetrieval()
                ->withExportOptions($this->export_options)
                ->withExportObject($this->context->exportObject())
                ->withExportGUI($this->context->exportGUIObject())
        )
            ->withId(self::TABLE_ID)
            ->withActions($this->getActions())
            ->withRequest($this->http_services->request());
    }

    /**
     * @return array<string, ilExportHandlerConsumerFileIdentifierCollectionInterface>
     */
    protected function readIdsFromExportOptions(): array
    {
        $ids_sorted = [];
        $key = $this->export_handler->repository()->key()->handler()
            ->withObjectId($this->data_factory->objId($this->context->exportObject()->getId()));
        $key_collection = $this->export_handler->repository()->key()->collection()
            ->withElement($key);
        $elements = $this->export_handler->repository()->handler()->getElements($key_collection);
        foreach ($this->export_options as $export_option) {
            $export_option_id = $export_option->getExportOptionId();
            $ids_sorted[$export_option_id] = $this->export_handler->consumer()->file()->identifier()->collection();
            foreach ($export_option->getFiles($this->context) as $file_info) {
                $file_identifier = $this->export_handler->consumer()->file()->identifier()->handler()
                    ->withIdentifier($file_info->getFileIdentifier());
                $ids_sorted[$export_option_id] = $ids_sorted[$export_option_id]
                    ->withElement($file_identifier);
            }
        }
        return $ids_sorted;
    }

    /**
     * @return array<string, ilExportHandlerConsumerFileIdentifierCollectionInterface>
     */
    protected function readIdsFromQuery(): array
    {
        $tokens = $this->http_services->wrapper()->query()->retrieve(
            $this->row_id_token->getName(),
            $this->refinery->custom()->transformation(fn($v) => $v)
        );
        $composit_ids = is_array($tokens) ? $tokens : [$tokens];
        $ids_sorted = [];
        foreach ($composit_ids as $composit_id) {
            $table_row_id = $this->export_handler->table()->rowId()->handler()
                ->withCompositId($composit_id);
            $file_identifier = $this->export_handler->consumer()->file()->identifier()->handler()
                ->withIdentifier($table_row_id->getFileIdentifier());
            $export_option = $this->export_options->getById($table_row_id->getExportOptionId());
            if (!isset($ids_sorted[$table_row_id->getExportOptionId()])) {
                $ids_sorted[$table_row_id->getExportOptionId()] = $this->export_handler->consumer()->file()->identifier()->collection();
            }
            $ids_sorted[$table_row_id->getExportOptionId()] = $ids_sorted[$table_row_id->getExportOptionId()]
                ->withElement($file_identifier);
        }
        return $ids_sorted;
    }

    public function handleCommands(): void
    {
        $this->initTable();
        if (!$this->http_services->wrapper()->query()->has($this->action_parameter_token->getName())) {
            return;
        }
        $action = $this->http_services->wrapper()->query()->retrieve(
            $this->action_parameter_token->getName(),
            $this->refinery->to()->string()
        );
        $tokens = $this->http_services->wrapper()->query()->retrieve(
            $this->row_id_token->getName(),
            $this->refinery->custom()->transformation(fn($v) => $v)
        );
        $all_entries = ($tokens[0] ?? "") === self::ALL_OBJECTS;
        $ids_sorted = [];
        if ($all_entries) {
            $ids_sorted = $this->readIdsFromExportOptions();
        }
        if (!$all_entries) {
            $ids_sorted = $this->readIdsFromQuery();
        }
        switch ($action) {
            case self::ACTION_PUBLIC_ACCESS:
                $this->markAsPublicAccess($ids_sorted);
                break;
            case self::ACTION_DOWNLOAD:
                $this->downloadItems($ids_sorted);
                break;
            case self::ACTION_DELETE:
                $this->showDeleteModal($ids_sorted);
                break;
            case self::ACTION_CONFIRM_DELETE:
                $this->deleteItems($ids_sorted);
                break;
        }
    }

    public function getHTML(): string
    {
        $this->initTable();
        return $this->ui_services->renderer()->render([$this->table]);
    }

    public function withExportOptions(
        ilExportHandlerConsumerExportOptionCollectionInterface $export_options
    ): ilExportHandlerTableInterface {
        $clone = clone $this;
        $clone->export_options = $export_options;
        return $clone;
    }

    public function withContext(ilExportHandlerConsumerContextInterface $context): ilExportHandlerTableInterface
    {
        $clone = clone $this;
        $clone->context = $context;
        return $clone;
    }
}
