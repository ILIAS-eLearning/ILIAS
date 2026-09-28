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
use ILIAS\News\Common\Table\TableActionModalTrait;
use ILIAS\UI\Component\Modal\Modal;
use ilGlobalTemplateInterface;

class DeleteNewsItemTableAction implements TableAction
{
    use TableActionModalTrait;

    public const string ID = 'delete';

    public function __construct(
        private readonly Factory $ui_factory,
        private readonly ilLanguage $lng,
        private readonly ilCtrl $ctrl,
        private readonly NewsAccess $news_access,
        private readonly HttpService $http,
        private readonly ilGlobalTemplateInterface $tpl,
        private readonly array $records,
    ) {
    }

    public function getActionId(): string
    {
        return self::ID;
    }

    public function getActionLabel(): string
    {
        return $this->lng->txt('delete');
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
        )->withAsync(true);
    }

    public function allowActionForRecord(mixed $record): bool
    {
        $id = (int) ($record['id'] ?? 0);
        if ($id <= 0) {
            return false;
        }

        return $this->news_access->canDelete(new ilNewsItem($id));
    }

    protected function getModal(URLBuilder $url_builder, array $selected_records, bool $all_records_selected): ?Modal
    {
        $affected_items = [];
        /** @var string $news_id */
        foreach (array_column($selected_records, 'id') as $news_id) {
            $legacy_item = new ilNewsItem((int) $news_id);
            if (!$this->news_access->canDelete($legacy_item)) {
                continue;
            }

            $affected_items[] = $this->ui_factory->modal()->interruptiveItem()->keyValue(
                $news_id,
                $this->lng->txt('news_news_item_title'),
                $legacy_item->getTitle()
            );
        }

        if ($affected_items === []) {
            return $this->ui_factory->modal()->roundtrip(
                $this->lng->txt('info'),
                $this->ui_factory->messageBox()->failure($this->lng->txt('no_checkbox'))
            )->withCancelButtonLabel($this->lng->txt('close'));
        }

        return $this->ui_factory->modal()->interruptive(
            $this->lng->txt('delete'),
            $this->lng->txt('info_delete_sure'),
            $url_builder->buildURI()->__toString()
        )->withAffectedItems($affected_items);
    }

    protected function onSubmit(URLBuilder $url_builder, array $selected_records, bool $all_records_selected): ?Modal
    {
        if (!$this->news_access->canAccessManageOverview()) {
            return null;
        }

        $deleted_count = 0;
        $failed_count = 0;
        foreach ($selected_records as $record) {
            $news_id = (int) $record['id'] ?? 0;
            if ($news_id <= 0) {
                $failed_count++;
                continue;
            }

            $news = new ilNewsItem($news_id);
            if (!$this->news_access->canDelete($news)) {
                $failed_count++;
                continue;
            }

            try {
                $news->delete();
                $deleted_count++;
            } catch (\Exception) {
                $failed_count++;
            }
        }

        if ($deleted_count > 0) {
            $this->showSuccessMessage($this->lng->txt('deleted'));
        }

        if ($failed_count > 0) {
            $this->showErrorMessage($this->lng->txt('msg_obj_already_deleted'));
        }

        $this->ctrl->redirectByClass(ilNewsItemGUI::class, 'editNews');
        return null;
    }

    protected function resolveRecords(?array $selected_ids = null): array
    {
        if ($selected_ids === null) {
            return $this->records;
        }

        return array_filter(
            $this->records,
            static fn(array $record): bool => in_array($record['id'], $selected_ids, false)
        );
    }
}
