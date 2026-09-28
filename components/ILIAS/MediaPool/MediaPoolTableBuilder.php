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

namespace ILIAS\MediaPool;

use ILIAS\AdvancedMetaData\Services\ServicesInterface;
use ILIAS\MediaObjects\Thumbs\ThumbsGUI;
use ILIAS\Repository\Filter\FilterAdapterGUI;
use ILIAS\Repository\RetrievalInterface;
use ILIAS\Repository\Table\CommonTableBuilder;
use ILIAS\Repository\Table\TableAdapterGUI;

class MediaPoolTableBuilder extends CommonTableBuilder
{
    protected \ilTree $tree;
    protected int $current_folder;
    protected ?FilterAdapterGUI $filter = null;

    public function __construct(
        protected InternalDomainService $domain,
        protected InternalGUIService $gui,
        protected ThumbsGUI $thumbs_gui,
        protected MediaPoolRepository $media_pool_repository,
        protected ServicesInterface $advanced_metadata,
        protected \ilObjMediaPool $media_pool,
        protected string $folder_par,
        protected string $mode,
        protected bool $all_objects,
        protected ?string $filter_command,
        protected ?string $reset_command,
        protected string $insert_command,
        object $parent_gui,
        string $parent_cmd,
        protected ?string $filter_title = null
    ) {
        $this->tree = \ilObjMediaPool::_getPoolTree($media_pool->getId());
        $request = $this->gui->standardRequest();
        $current_folder = $this->domain->clipboard()->getFolder();
        $requested_folder = $request->getFolderId($folder_par);

        if ($requested_folder > 0) {
            $this->current_folder = $requested_folder;
        } elseif ($current_folder > 0 && $this->tree->isInTree($current_folder)) {
            $this->current_folder = $current_folder;
        } else {
            $this->current_folder = $this->tree->getRootId();
        }
        $this->domain->clipboard()->setFolder($this->current_folder);

        parent::__construct($parent_gui, $parent_cmd);
    }

    protected function getId(): string
    {
        return $this->all_objects ? 'mepall' : 'mepfold';
    }

    protected function getTitle(): string
    {
        return $this->mode === 'edit'
            ? ''
            : $this->domain->lng()->txt('mep_choose_from_mep');
    }

    protected function getRetrieval(): RetrievalInterface
    {
        return new MediaPoolTableRetrieval(
            $this->media_pool,
            $this->current_folder,
            $this->mode,
            $this->all_objects,
            $this->media_pool_repository,
            $this->advanced_metadata,
            $this->getFilterData()
        );
    }

    protected function transformRow(array $data_row): array
    {
        $ctrl = $this->gui->ctrl();
        $factory = $this->gui->ui()->factory();
        $type = (string) ($data_row['type'] ?? '');
        $child = (int) ($data_row['child'] ?? $data_row['obj_id'] ?? 0);
        $title = htmlspecialchars((string) ($data_row['title'] ?? ''), ENT_QUOTES, 'UTF-8');

        if ($type === 'fold') {
            $ctrl->setParameter($this->parent_gui, $this->folder_par, $child);
            $ctrl->setParameter($this->parent_gui, 'id', $child);
            $target = $ctrl->getLinkTarget($this->parent_gui, $this->parent_cmd);
            $ctrl->setParameter($this->parent_gui, $this->folder_par, $this->current_folder);
            $title = $this->gui->ui()->renderer()->render(
                $factory->link()->standard(
                    $title,
                    htmlspecialchars($target, ENT_QUOTES, 'UTF-8')
                )
            );
        } elseif (!in_array($this->mode, ['select', 'selectsingle', 'selectc'], true)) {
            $ctrl->setParameterByClass('ilobjmediapoolgui', 'mepitem_id', $child);
            $title = '<a href="#" onclick="il.MediaPool.preview(\'' . $child . '\'); return false;">' .
                $title . '</a>';
        }

        $thumbnail = $this->getIcon($factory, $type);
        if ($type === 'mob') {
            $mob_id = (int) ($data_row['foreign_id'] ?? 0);
            $thumbnail = $this->thumbs_gui->getThumbHtml($mob_id);
            $mob = new \ilObjMediaObject($mob_id);
            $title .= \ilObjMediaObjectGUI::_getMediaInfoHTML($mob);
        }

        $data_row['id'] = $child;
        $data_row['thumbnail'] = $thumbnail;
        $data_row['title'] = $title;

        return $data_row;
    }

    protected function build(TableAdapterGUI $table): TableAdapterGUI
    {
        $lng = $this->domain->lng();
        $table = $table
            ->filterData($this->getFilterData())
            ->textColumn('thumbnail', $lng->txt('mep_thumbnail'))
            ->textColumn('title', $this->getTitleColumnTitle(), true);

        if ($this->all_objects) {
            $advanced_metadata = $this->advanced_metadata
                ->forSubObjects('mep', $this->media_pool->getRefId(), 'mob')
                ->inDataTable();
            foreach ($advanced_metadata->getColumns() as $key => $column) {
                $table->column($key, $column);
            }
        }

        if ($this->all_objects && $this->mode === 'edit') {
            $table = $table
                ->multiAction('copyToClipboard', $lng->txt('cont_copy_to_clipboard'))
                ->multiAction('move', $lng->txt('move'))
                ->multiAction('confirmRemove', $lng->txt('remove'))
                ->singleRedirectAction(
                    'editObject',
                    $lng->txt('edit'),
                    [\ilObjMediaObjectGUI::class],
                    'edit',
                    'mepitem_id'
                )
                ->singleRedirectAction(
                    'editPage',
                    $lng->txt('edit'),
                    [\ilMediaPoolPageGUI::class],
                    'edit',
                    'mepitem_id'
                );
        } elseif ($this->mode === 'edit') {
            $table = $table
                ->multiAction('copyToClipboard', $lng->txt('cont_copy_to_clipboard'))
                ->multiAction('move', $lng->txt('move'))
                ->multiAction('confirmRemove', $lng->txt('remove'))
                ->singleRedirectAction(
                    'editFolder',
                    $lng->txt('edit'),
                    [get_class($this->parent_gui)],
                    'editFolder',
                    'mepitem_id'
                )
                ->singleRedirectAction(
                    'editObject',
                    $lng->txt('edit'),
                    [\ilObjMediaObjectGUI::class],
                    'edit',
                    'mepitem_id'
                )
                ->singleRedirectAction(
                    'editPage',
                    $lng->txt('edit'),
                    [\ilMediaPoolPageGUI::class],
                    'edit',
                    'mepitem_id'
                );
        } elseif ($this->mode === 'selectsingle') {
            $table = $table->multiAction('selectObjectReference', $lng->txt('cont_select'));
        } else {
            $table = $table->multiAction($this->insert_command, $lng->txt('insert'));
        }

        return $table;
    }

    protected function activeAction(string $action, array $data_row): bool
    {
        $type = (string) ($data_row['type'] ?? '');
        if ($action === 'editFolder') {
            return $type === 'fold';
        }
        if ($action === 'editObject') {
            return $type === 'mob';
        }
        if ($action === 'editPage') {
            return $type === 'pg';
        }
        if ($this->mode === 'select' && $type !== 'mob') {
            return false;
        }
        if ($this->mode === 'selectc' && $type !== 'pg') {
            return false;
        }
        if ($this->mode === 'selectsingle' && $type !== 'mob') {
            return false;
        }
        if ($this->mode === 'edit' && $this->all_objects && $type !== 'mob') {
            return $action !== 'editObject';
        }
        return true;
    }

    public function render(): string
    {
        $html = '';
        if ($this->filter !== null) {
            $html .= $this->filter->render();
        }
        if (!$this->all_objects && $this->current_folder !== $this->tree->getRootId()) {
            $locator = $this->gui->locator();
            foreach ($this->tree->getPathFull($this->current_folder) as $path) {
                $this->gui->ctrl()->setParameter($this->parent_gui, $this->folder_par, $path['child']);
                $title = htmlspecialchars((string) $path['title'], ENT_QUOTES, 'UTF-8');
                if ($path['child'] == $this->tree->getRootId()) {
                    $title = \ilObject::_lookupTitle($this->media_pool->getId());
                }
                $locator->addItem($title, $this->gui->ctrl()->getLinkTarget($this->parent_gui, $this->parent_cmd));
            }
            $html .= '<div class="small" style="margin-bottom: 7px;">' . $locator->getHTML() . '</div>';
        }
        $html .= $this->table->render();
        $html .= \ilObjMediaPoolGUI::getPreviewModalHTML(
            $this->media_pool->getRefId(),
            $this->gui->ui()->mainTemplate()
        );
        return $html;
    }

    protected function getFilterData(): array
    {
        if (!$this->all_objects || $this->filter_command === null) {
            return [];
        }
        return $this->getFilter()->getData() ?? [];
    }

    protected function getFilter(): FilterAdapterGUI
    {
        if ($this->filter === null) {
            $mset = new \ilSetting('mobs');
            $options = ['' => $this->domain->lng()->txt('mep_all')];
            if ($mset->get('mep_activate_pages')) {
                $options['mob'] = $this->domain->lng()->txt('mep_mob');
                $options['pg'] = $this->domain->lng()->txt('mep_mpg');
            }
            $options = array_merge($options, $this->media_pool->getUsedFormats());
            $this->filter = $this->gui->filter(
                'mep_filter',
                [get_class($this->parent_gui)],
                $this->filter_command ?? $this->parent_cmd
            );
            $this->filter
                ->text('title', $this->domain->lng()->txt('title'), true, $this->filter_title)
                ->text('keyword', $this->domain->lng()->txt('meta_keyword'))
                ->text('caption', $this->domain->lng()->txt('cont_caption'))
                ->select('format', $this->domain->lng()->txt('mep_format'), $options);

            $advanced_metadata = $this->advanced_metadata
                ->forSubObjects('mep', $this->media_pool->getRefId(), 'mob')
                ->inFilter();
            foreach ($advanced_metadata->getFilterInputs() as $key => $input) {
                $this->filter->input($key, $input, false);
            }
        }
        return $this->filter;
    }

    protected function getTitleColumnTitle(): string
    {
        return $this->domain->lng()->txt('mep_title_and_description');
    }

    protected function getIcon(\ILIAS\UI\Factory $factory, string $type): string
    {
        $path = $type === 'fold' ? 'standard/icon_fold.svg' : 'standard/icon_pg.svg';
        return $this->gui->ui()->renderer()->render(
            $factory->symbol()->icon()->custom(
                \ilUtil::getImagePath($path),
                $this->domain->lng()->txt($type === 'fold' ? 'folder' : 'page')
            )->withSize('large')
        );
    }
}
