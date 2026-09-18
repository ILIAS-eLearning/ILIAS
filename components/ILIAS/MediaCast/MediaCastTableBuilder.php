<?php

/**
 * This file is part of ILIAS, a powerful learning management system
 * published by ILIAS open source e-Learning e.V.
 *
 * ILIAS is licensed with the GPL-3.0,
 * see https://www.gnu.org/licenses/gpl-3.0.en.html
 * You should have received a copy of said license along with
 * the source code, too.
 *
 * If this is not the case or you just want to try ILIAS, you'll find
 * us at:
 * https://www.ilias.de
 * https://github.com/ILIAS-eLearning
 *
 *********************************************************************/

declare(strict_types=1);

namespace ILIAS\MediaCast;

use ILIAS\Repository\RetrievalInterface;
use ILIAS\Repository\Table\CommonTableBuilder;
use ILIAS\Repository\Table\TableAdapterGUI;

class MediaCastTableBuilder extends CommonTableBuilder
{
    public function __construct(
        protected InternalDomainService $domain,
        protected InternalGUIService $gui,
        protected \ilObjMediaCast $media_cast,
        protected bool $edit_order,
        object $parent_gui,
        string $parent_cmd
    ) {
        parent::__construct($parent_gui, $parent_cmd);
    }

    protected function getId(): string
    {
        return 'mcst_items';
    }

    protected function getTitle(): string
    {
        return $this->domain->lng()->txt($this->edit_order ? 'mcst_media_cast' : 'mcst_items');
    }

    protected function getNamespace(): string
    {
        return 'mcst';
    }

    protected function getRetrieval(): RetrievalInterface
    {
        return new MediaCastRetrieval($this->media_cast);
    }

    protected function getOrderingCommand(): string
    {
        return $this->edit_order ? 'saveOrder' : '';
    }

    protected function activeAction(string $action, array $data_row): bool
    {
        if ($action === 'determinePlaytimeObject') {
            return !($data_row['is_image'] ?? false);
        }

        return true;
    }

    protected function transformRow(array $data_row): array
    {
        $mob = new \ilObjMediaObject((int) $data_row['mob_id']);
        $med = $mob->getMediaItem('Standard');
        $format = $med?->getFormat() ?? '';

        $data = [
            'id' => (int) $data_row['id'],
            'title' => (string) ($data_row['title'] ?? ''),
            'type' => $format !== '' ? $format : '-',
            'size' => $this->getSize($mob),
            'playtime' => ($data_row['playtime'] ?? '') !== '00:00:00'
                ? (string) ($data_row['playtime'] ?? '')
                : '-',
            'creation_date' => \ilDatePresentation::formatDate(
                new \ilDateTime((string) ($data_row['creation_date'] ?? ''), IL_CAL_DATETIME)
            ),
            'update_date' => $this->getUpdateDate($data_row),
            'preview' => $this->getPreview($mob, (string) ($data_row['title'] ?? '')),
        ];

        return $data;
    }

    protected function build(TableAdapterGUI $table): TableAdapterGUI
    {
        $lng = $this->domain->lng();

        $table = $table
            ->textColumn('title', $lng->txt('title'))
            ->textColumn('type', $lng->txt('type'))
            ->textColumn('size', $lng->txt('size'))
            ->textColumn('playtime', $lng->txt('mcst_play_time'))
            ->textColumn('creation_date', $lng->txt('created'))
            ->textColumn('update_date', $lng->txt('last_update'));

        if ($this->media_cast->getViewMode() !== \ilObjMediaCast::VIEW_PODCAST) {
            $table = $table->textColumn('preview', $lng->txt('preview'));
        }

        $table = $table
            ->singleAction('editCastItemObject', $lng->txt('edit'))
            ->singleAction('showCastItemObject', $lng->txt('show'), true);

        if ($this->media_cast->getViewMode() !== \ilObjMediaCast::VIEW_IMG_GALLERY) {
            $table = $table->singleAction(
                'determinePlaytimeObject',
                $lng->txt('mcst_det_playtime')
            );
        }
        if ($this->media_cast->getDownloadable()) {
            $table = $table->singleAction('downloadItemObject', $lng->txt('download'));
        }

        return $table->singleAction(
            'confirmItemDeletionObject',
            $lng->txt('delete'),
            true
        );
    }

    protected function getSize(\ilObjMediaObject $mob): string
    {
        $file = \ilObjMediaObject::_lookupItemPath($mob->getId(), false, false, 'Standard');
        if (!is_file($file)) {
            return '-';
        }

        return sprintf('%.1f MB', filesize($file) / 1024 / 1024);
    }

    protected function getUpdateDate(array $data_row): string
    {
        if (($data_row['update_date'] ?? '') === ($data_row['creation_date'] ?? '')) {
            return '-';
        }

        return \ilDatePresentation::formatDate(
            new \ilDateTime((string) ($data_row['update_date'] ?? ''), IL_CAL_DATETIME)
        );
    }

    protected function getPreview(\ilObjMediaObject $mob, string $title): string
    {
        $preview = $mob->getVideoPreviewPic();
        if ($preview === '') {
            return '';
        }

        $image = $this->gui->ui()->factory()->image()->responsive($preview, $title);
        return str_replace(
            '<img ',
            "<img style='max-width:150px;' ",
            $this->gui->ui()->renderer()->render($image)
        );
    }
}
