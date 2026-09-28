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

use ILIAS\Repository\RetrievalInterface;
use ILIAS\Repository\Table\CommonTableBuilder;
use ILIAS\Repository\Table\TableAdapterGUI;
use ILIAS\Wiki\InternalDomainService;
use ILIAS\Wiki\InternalGUIService;
use ILIAS\ILIASObject\Properties\Translations\Translations;

class PagesTableBuilder extends CommonTableBuilder
{
    public const MODE_ALL_PAGES = "all";
    public const MODE_NEW_PAGES = "new";
    public const MODE_POPULAR_PAGES = "popular";
    public const MODE_WHAT_LINKS_HERE = "what_links";
    public const MODE_ORPHANED_PAGES = "orphaned";

    protected Translations $translations;
    protected PageManager $page_manager;

    public function __construct(
        protected InternalDomainService $domain,
        protected InternalGUIService $gui,
        protected int $ref_id,
        protected string $mode,
        protected int $page_id,
        protected string $lang,
        object $parent_gui,
        string $parent_cmd
    ) {
        $this->page_manager = $this->domain->page()->page($ref_id);
        $this->translations = $this->domain->wiki()->translation($ref_id);
        parent::__construct($parent_gui, $parent_cmd);
    }

    protected function getId(): string
    {
        return "wiki_pages_" . $this->mode;
    }

    protected function getTitle(): string
    {
        if ($this->mode === self::MODE_WHAT_LINKS_HERE) {
            return sprintf(
                $this->domain->lng()->txt("wiki_what_links_to_page"),
                \ilWikiPage::lookupTitle($this->page_id)
            );
        }

        return $this->domain->lng()->txt("wiki_" . $this->mode . "_pages");
    }

    protected function getRetrieval(): RetrievalInterface
    {
        return $this->domain->page()->pagesRetrieval(
            $this->ref_id,
            $this->mode,
            $this->page_id,
            $this->lang
        );
    }

    protected function transformRow(array $data_row): array
    {
        $row = [
            "id" => $data_row["id"],
            "title" => $this->gui->ui()->factory()->link()->standard(
                $data_row["title"],
                $this->page_manager->getPermaLink($data_row["id"], $data_row["lang"] ?? "")
            )
        ];

        if ($this->mode === self::MODE_NEW_PAGES) {
            $row["created"] = new \DateTimeImmutable($data_row["created"]);
            if ($this->translations->getContentTranslationActivated()) {
                $row["lang"] = $this->getLanguageLabel($data_row["lang"]);
            }
        } elseif ($this->mode === self::MODE_POPULAR_PAGES) {
            $row["cnt"] = $data_row["cnt"];
            if ($this->translations->getContentTranslationActivated()) {
                $row["lang"] = $this->getLanguageLabel($data_row["lang"]);
            }
        } elseif ($this->mode !== self::MODE_WHAT_LINKS_HERE) {
            $row["date"] = new \DateTimeImmutable($data_row["date"]);
            if ($this->translations->getContentTranslationActivated()) {
                $row["languages"] = $this->getTranslations($data_row["id"]);
            }
        } else {
            $row["date"] = new \DateTimeImmutable($data_row["date"]);
        }

        if (isset($data_row["user"])) {
            $row["user_sort"] = \ilUserUtil::getNamePresentation(
                $data_row["user"],
                true,
                true,
                $this->gui->ctrl()->getLinkTarget($this->parent_gui, $this->parent_cmd)
            );
        }

        return $row;
    }

    protected function build(TableAdapterGUI $table): TableAdapterGUI
    {
        $lng = $this->domain->lng();
        $translation_active = $this->translations->getContentTranslationActivated();

        switch ($this->mode) {
            case self::MODE_NEW_PAGES:
                $table = $table
                    ->dateColumn("created", $lng->txt("created"), true)
                    ->linkColumn("title", $lng->txt("wiki_page"), true);
                if ($translation_active) {
                    $table = $table->textColumn("lang", $lng->txt("language"));
                }
                return $table->textColumn("user_sort", $lng->txt("wiki_created_by"), true);

            case self::MODE_POPULAR_PAGES:
                $table = $table
                    ->linkColumn("title", $lng->txt("wiki_page"), true);
                if ($translation_active) {
                    $table = $table->textColumn("lang", $lng->txt("language"));
                }
                return $table->textColumn("cnt", $lng->txt("wiki_page_hits"), true);

            case self::MODE_ORPHANED_PAGES:
                $table = $table->linkColumn("title", $lng->txt("wiki_page"), true);
                if ($translation_active) {
                    $table = $table->textColumn("languages", $lng->txt("language"));
                }
                return $table;

            default:
                $table = $table
                    ->linkColumn("title", $lng->txt("wiki_page"), true)
                    ->dateColumn("date", $lng->txt("wiki_last_changed"), true);
                if ($translation_active && $this->mode !== self::MODE_WHAT_LINKS_HERE) {
                    $table = $table->textColumn("languages", $lng->txt("wiki_translations"));
                }
                return $table->textColumn("user_sort", $lng->txt("wiki_last_changed_by"), true);
        }
    }

    protected function getLanguageLabel(string $language): string
    {
        $language = $language === "-"
            ? $this->translations->getBaseLanguage()
            : $language;
        return $this->domain->lng()->txt("meta_l_" . $language);
    }

    protected function getTranslations(int $page_id): string
    {
        $languages = array_filter(
            $this->page_manager->getLanguages($page_id),
            fn(string $language): bool => in_array(
                $language,
                array_map(
                    fn($language): string => $language->getLanguageCode(),
                    $this->translations->getLanguages()
                ),
                true
            )
        );

        return implode(", ", $languages);
    }
}
