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
use ILIAS\Wiki\InternalDomainService;
use ILIAS\Wiki\InternalGUIService;

class RecentChangesTableBuilder extends CommonTableBuilder
{
    protected Translations $translations;

    public function __construct(
        protected InternalDomainService $domain,
        protected InternalGUIService $gui,
        protected int $ref_id,
        object $parent_gui,
        string $parent_cmd
    ) {
        $this->translations = $this->domain->wiki()->translation($ref_id);
        parent::__construct($parent_gui, $parent_cmd);
    }

    protected function getId(): string
    {
        return "wiki_recent_changes";
    }

    protected function getTitle(): string
    {
        return $this->domain->lng()->txt("wiki_recent_changes");
    }

    protected function getRetrieval(): RetrievalInterface
    {
        return $this->domain->page()->recentChangesRetrieval($this->ref_id);
    }

    protected function transformRow(array $data_row): array
    {
        $ctrl = $this->gui->ctrl();
        $ctrl->setParameterByClass("ilwikipagegui", "wpg_id", $data_row["id"]);
        $ctrl->setParameterByClass("ilwikipagegui", "transl", $data_row["lang"]);
        $ctrl->setParameterByClass("ilwikipagegui", "old_nr", $data_row["nr"] ?? "");

        $row = [
            "id" => $data_row["id"],
            "title" => $this->gui->ui()->factory()->link()->standard(
                $data_row["title"],
                $ctrl->getLinkTargetByClass("ilwikipagegui", "preview")
            ),
            "date" => new \DateTimeImmutable($data_row["date"]),
            "user_sort" => \ilUserUtil::getNamePresentation(
                $data_row["user"],
                true,
                true,
                $ctrl->getLinkTarget($this->parent_gui, $this->parent_cmd)
            )
        ];

        if ($this->translations->getContentTranslationActivated()) {
            $row["lang"] = $this->getLanguageLabel($data_row["lang"]);
        }

        return $row;
    }

    protected function build(TableAdapterGUI $table): TableAdapterGUI
    {
        $lng = $this->domain->lng();

        $table = $table
            ->dateColumn("date", $lng->txt("wiki_last_changed"), true)
            ->linkColumn("title", $lng->txt("wiki_page"), true);

        if ($this->translations->getContentTranslationActivated()) {
            $table = $table->textColumn("lang", $lng->txt("language"));
        }

        return $table->textColumn("user_sort", $lng->txt("wiki_last_changed_by"), true);
    }

    protected function getLanguageLabel(string $language): string
    {
        $language = $language === "-"
            ? $this->translations->getBaseLanguage()
            : $language;
        return $this->domain->lng()->txt("meta_l_" . $language);
    }
}
