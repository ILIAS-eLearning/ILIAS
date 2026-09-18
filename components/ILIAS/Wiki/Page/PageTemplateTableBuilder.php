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

namespace ILIAS\Wiki\Page;

use ILIAS\ILIASObject\Properties\Translations\Translations;
use ILIAS\Repository\RetrievalInterface;
use ILIAS\Repository\Table\CommonTableBuilder;
use ILIAS\Repository\Table\TableAdapterGUI;

class PageTemplateTableBuilder extends CommonTableBuilder
{
    protected PageManager $pm;
    protected Translations $translations;

    public function __construct(
        protected \ILIAS\Wiki\InternalDomainService $domain,
        protected \ILIAS\Wiki\InternalGUIService $gui,
        protected int $wiki_id,
        object $parent_gui,
        string $parent_cmd
    ) {
        $ref_id = $this->gui->request()->getRefId();
        $this->translations = $this->domain->wiki()->translation($ref_id);
        $this->pm = $this->domain->page()->page($ref_id);

        parent::__construct($parent_gui, $parent_cmd);
    }

    protected function getId(): string
    {
        return 'wiki_page_templates_' . $this->wiki_id;
    }

    protected function getTitle(): string
    {
        return '';
    }

    protected function getRetrieval(): RetrievalInterface
    {
        return $this->domain->page()->pageTemplateRetrieval($this->wiki_id);
    }

    protected function transformRow(array $data_row): array
    {
        $lng = $this->domain->lng();

        return [
            'id' => $data_row['id'],
            'title' => $data_row['title'],
            'translations' => $this->translations->getContentTranslationActivated()
                ? implode(', ', $this->pm->getLanguages((int) $data_row['wpage_id']))
                : '',
            'new_pages' => $data_row['new_pages'] ? $lng->txt('yes') : $lng->txt('no'),
            'add_to_page' => $data_row['add_to_page'] ? $lng->txt('yes') : $lng->txt('no')
        ];
    }

    protected function build(TableAdapterGUI $table): TableAdapterGUI
    {
        $lng = $this->domain->lng();

        $table = $table
            ->textColumn('title', $lng->txt('title'), true);

        if ($this->translations->getContentTranslationActivated()) {
            $table = $table->textColumn(
                'translations',
                $lng->txt('wiki_translations')
            );
        }

        return $table
            ->textColumn('new_pages', $lng->txt('wiki_templ_new_pages'))
            ->textColumn('add_to_page', $lng->txt('wiki_templ_add_to_page'))
            ->singleAction('toggleNewPages', $lng->txt('wiki_templ_new_pages'))
            ->singleAction('toggleAddToPage', $lng->txt('wiki_templ_add_to_page'))
            ->multiAction(
                'confirmRemove',
                $lng->txt('wiki_remove_template_status'),
                true
            );
    }
}
