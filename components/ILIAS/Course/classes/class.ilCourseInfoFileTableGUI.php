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

use ILIAS\Data\Factory as ilDataFactory;
use ILIAS\DI\UIServices as ilUIServices;
use ILIAS\HTTP\Services as ilHTTPServices;
use ILIAS\Refinery\Factory as ilRefineryFactory;
use ILIAS\UI\Component\Table\Data as ilDataTable;
use ILIAS\UI\URLBuilder;
use ILIAS\UI\URLBuilderToken as ilURLBuilderToken;
use JetBrains\PhpStorm\NoReturn;

class ilCourseInfoFileTableGUI
{
    public const string TABLE_COL_FILENAME = 'filename';
    public const string TABLE_COL_FILESIZE = 'filesize';
    public const string TABLE_COL_FILETYPE = 'filetype';
    protected const string ALL_OBJECTS = "ALL_OBJECTS";
    protected const string TABLE_ACTION_CONFIRM_DELETE = 'confirm_delete';
    protected const string TABLE_ACTION_DELETE = 'delete';
    protected const string LNG_TABLE_COL_FILENAME = 'filename';
    protected const string LNG_TABLE_COL_FILESIZE = 'filesize';
    protected const string LNG_TABLE_COL_FILETYPE = 'filetype';
    protected const string LNG_TABLE_ACTION_CONFIRM_DELETE = 'delete';
    protected const string LNG_TABLE_TITLE = 'crs_info_download';
    protected const string TABLE_ID = "crsfltbl";
    protected const string ROW_ID = "row_ids";
    protected const string TABLE_ACTION_ID = "table_action";

    protected URLBuilder $url_builder;
    protected ilURLBuilderToken $action_parameter_token;
    protected ilURLBuilderToken $row_id_token;
    protected ilDataTable $table;

    public function __construct(
        protected ilCourseInfoFileTableDataRetrieval $data_retrieval,
        protected ilLanguage $lng,
        protected ilUIServices $ui_services,
        protected ilHTTPServices $http_services,
        protected ilRefineryFactory $refinery,
        protected ilCtrl $ctrl,
        protected ilDataFactory $data_factory
    ) {
    }

    protected function getColumns(): array
    {
        return [
            self::TABLE_COL_FILENAME => $this->ui_services->factory()->table()->column()->text(
                $this->lng->txt(self::TABLE_COL_FILENAME)
            ),
            self::TABLE_COL_FILESIZE => $this->ui_services->factory()->table()->column()->number(
                $this->lng->txt(self::TABLE_COL_FILESIZE)
            ),
            self::TABLE_COL_FILETYPE => $this->ui_services->factory()->table()->column()->text(
                $this->lng->txt(self::TABLE_COL_FILETYPE)
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
            self::TABLE_ACTION_CONFIRM_DELETE => $this->ui_services->factory()->table()->action()->multi(
                $this->lng->txt(self::LNG_TABLE_ACTION_CONFIRM_DELETE),
                $this->url_builder->withParameter($this->action_parameter_token, self::TABLE_ACTION_CONFIRM_DELETE),
                $this->row_id_token
            )->withAsync(true)
        ];
    }

    protected function initTable(): void
    {
        if (isset($this->table)) {
            return;
        }
        $this->table = $this->ui_services->factory()->table()->data(
            $this->data_retrieval,
            $this->lng->txt(self::LNG_TABLE_TITLE),
            $this->getColumns()
        )
            ->withId(self::TABLE_ID)
            ->withActions($this->getActions())
            ->withRequest($this->http_services->request());
    }

    protected function readIdsFromQuery(): array
    {
        $tokens = $this->http_services->wrapper()->query()->retrieve(
            $this->row_id_token->getName(),
            $this->refinery->custom()->transformation(fn($v) => $v)
        );
        return is_array($tokens) ? $tokens : [$tokens];
    }

    /**
     * @param array<int> $ids
     */
    protected function delete(array $ids): void
    {
        $this->data_retrieval->deleteFilesByIds($ids);
        $this->ui_services->mainTemplate()->setOnScreenMessage('success', $this->lng->txt('settings_saved'), true);
        $this->ctrl->redirectByClass(ilObjCourseGUI::class, 'editInfo');
    }

    #[NoReturn] protected function showDeleteModal(array $ids): void
    {
        $items = [];
        foreach ($ids as $id) {
            $items[] = $this->ui_services->factory()->modal()->interruptiveItem()->standard(
                $id . '',
                $this->data_retrieval->getFileTitle((int) $id)
            );
        }
        echo($this->ui_services->renderer()->renderAsync([
            $this->ui_services->factory()->modal()->interruptive(
                $this->lng->txt('confirm'),
                $this->lng->txt('info_delete_sure'),
                (string) $this->url_builder
                    ->withParameter(
                        $this->action_parameter_token,
                        self::TABLE_ACTION_DELETE
                    )->withParameter(
                        $this->row_id_token,
                        $ids
                    )->buildURI()
            )->withAffectedItems($items)
        ]));
        exit();
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
        $ids = [];
        if ($all_entries) {
            $ids = $this->data_retrieval->getAllFileIds();
        }
        if (!$all_entries) {
            $ids = $this->readIdsFromQuery();
        }
        if (is_null($ids[0]) || count($ids) === 0) {
            $ids = [];
        }
        switch ($action) {
            case self::TABLE_ACTION_CONFIRM_DELETE:
                $this->showDeleteModal($ids);
                break;
            case self::TABLE_ACTION_DELETE:
                $this->delete($ids);
                break;
        }
    }

    public function getHTML(): string
    {
        $this->initTable();
        return $this->ui_services->renderer()->render([$this->table]);
    }
}
