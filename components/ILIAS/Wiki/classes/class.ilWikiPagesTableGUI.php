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

const IL_WIKI_ALL_PAGES = "all";
const IL_WIKI_NEW_PAGES = "new";
const IL_WIKI_POPULAR_PAGES = "popular";
const IL_WIKI_WHAT_LINKS_HERE = "what_links";
const IL_WIKI_ORPHANED_PAGES = "orphaned";

/**
 * TableGUI class for wiki pages table
 *
 * @author Alexander Killing <killing@leifos.de>
 */
class ilWikiPagesTableGUI extends ilTable2GUI
{
    protected \ILIAS\Wiki\Links\LinkManager $link_manager;
    protected string $requested_lang;
    protected string $lang;
    protected ilObjectTranslation $ot;
    protected \ILIAS\Wiki\Page\PageManager $pm;
    protected int $requested_ref_id;
    protected int $page_id = 0;
    protected int $wiki_id = 0;
    protected string $pg_list_mode = "";

    public function __construct(
        object $a_parent_obj,
        string $a_parent_cmd,
        int $a_wiki_id,
        string $a_mode = IL_WIKI_ALL_PAGES,
        int $a_page_id = 0,
        string $lang = "-"
    ) {
        global $DIC;
        $this->lang = ($lang == "")
            ? "-"
            : $lang;
        $service = $DIC->wiki()->internal();
        $gui = $service->gui();
        $domain = $service->domain();
        $this->ctrl = $gui->ctrl();
        $this->lng = $domain->lng();
        $this->requested_ref_id = $gui
            ->request()
            ->getRefId();
        $this->requested_lang = $gui->request()->getTranslation();
        $this->pm = $domain->page()->page($this->requested_ref_id);
        $this->link_manager = $domain->links($this->requested_ref_id);
        $this->ot = $domain->wiki()->translation($a_wiki_id);

        parent::__construct($a_parent_obj, $a_parent_cmd);
        $this->pg_list_mode = $a_mode;
        $this->wiki_id = $a_wiki_id;
        $this->page_id = $a_page_id;

        switch ($this->pg_list_mode) {
            case IL_WIKI_NEW_PAGES:
                $this->addColumn($this->lng->txt("created"), "created");
                $this->addColumn($this->lng->txt("wiki_page"), "title");
                $this->addLanguageColumn();
                $this->addColumn($this->lng->txt("wiki_created_by"), "user_sort");
                $this->setRowTemplate(
                    "tpl.table_row_wiki_new_page.html",
                    "Modules/Wiki"
                );
                break;

            case IL_WIKI_POPULAR_PAGES:
                $this->addColumn($this->lng->txt("wiki_page"), "title");
                $this->addLanguageColumn();
                $this->addColumn($this->lng->txt("wiki_page_hits"), "cnt");
                $this->setRowTemplate(
                    "tpl.table_row_wiki_popular_page.html",
                    "Modules/Wiki"
                );
                break;

            case IL_WIKI_ORPHANED_PAGES:
                $this->addColumn($this->lng->txt("wiki_page"), "title");
                $this->addLanguageColumn();
                $this->setRowTemplate(
                    "tpl.table_row_wiki_orphaned_page.html",
                    "Modules/Wiki"
                );
                break;

            default:
                $this->addColumn($this->lng->txt("wiki_page"), "title");
                $this->addColumn($this->lng->txt("wiki_last_changed"), "date");
                if ($this->pg_list_mode !== IL_WIKI_WHAT_LINKS_HERE) {
                    $this->addTranslationsColumn();
                }
                $this->addColumn($this->lng->txt("wiki_last_changed_by"), "user_sort");
                $this->setRowTemplate(
                    "tpl.table_row_wiki_page.html",
                    "Modules/Wiki"
                );
                break;
        }
        $this->setEnableHeader(true);
        $this->setFormAction($this->ctrl->getFormAction($a_parent_obj));
        $this->getPages();

        $this->setShowRowsSelector(true);

        switch ($this->pg_list_mode) {
            case IL_WIKI_WHAT_LINKS_HERE:
                $this->setTitle(
                    sprintf(
                        $this->lng->txt("wiki_what_links_to_page"),
                        ilWikiPage::lookupTitle($this->page_id)
                    )
                );
                break;

            default:
                $this->setTitle($this->lng->txt("wiki_" . $a_mode . "_pages"));
                break;
        }
    }

    protected function addLanguageColumn(): void
    {
        if ($this->ot->getContentActivated()) {
            $this->addColumn($this->lng->txt("language"));
        }
    }

    protected function addTranslationsColumn(): void
    {
        if ($this->ot->getContentActivated()) {
            $this->addColumn($this->lng->txt("wiki_translations"));
        }
    }

    public function getPages(): void
    {
        $pages = array();
        $this->setDefaultOrderField("title");

        switch ($this->pg_list_mode) {
            case IL_WIKI_WHAT_LINKS_HERE:
                foreach ($this->link_manager->getLinksToPage($this->page_id, $this->lang) as $pi) {
                    $pages[] = [
                        "date" => $pi->getLastChange(),
                        "id" => $pi->getId(),
                        "user" => $pi->getLastChangedUser(),
                        "title" => $pi->getTitle(),
                        "lang" => $pi->getLanguage()
                    ];
                }
                break;

            case IL_WIKI_ALL_PAGES:
                foreach ($this->pm->getAllPagesInfo() as $pi) {
                    $pages[] = [
                        "date" => $pi->getLastChange(),
                        "id" => $pi->getId(),
                        "user" => $pi->getLastChangedUser(),
                        "title" => $pi->getTitle()
                    ];
                }
                break;

            case IL_WIKI_NEW_PAGES:
                $this->setDefaultOrderField("created");
                $this->setDefaultOrderDirection("desc");
                //$pages = ilWikiPage::getNewWikiPages($this->wiki_id);
                foreach ($this->pm->getNewPages() as $pi) {
                    $pages[] = [
                        "created" => $pi->getCreated(),
                        "id" => $pi->getId(),
                        "user" => $pi->getCreateUser(),
                        "title" => $pi->getTitle(),
                        "lang" => $pi->getLanguage()
                    ];
                }
                break;

            case IL_WIKI_POPULAR_PAGES:
                $this->setDefaultOrderField("cnt");
                $this->setDefaultOrderDirection("desc");
                //$pages = ilWikiPage::getPopularPages($this->wiki_id);
                foreach ($this->pm->getPopularPages() as $pi) {
                    $pages[] = [
                        "id" => $pi->getId(),
                        "title" => $pi->getTitle(),
                        "lang" => $pi->getLanguage(),
                        "cnt" => $pi->getViewCnt(),
                    ];
                }
                break;

            case IL_WIKI_ORPHANED_PAGES:
                foreach ($this->pm->getOrphanedPages() as $pi) {
                    $pages[] = [
                        "id" => $pi->getId(),
                        "title" => $pi->getTitle(),
                        "date" => $pi->getLastChange()
                    ];
                }
                break;
        }

        if ($pages) {
            // enable sorting
            foreach (array_keys($pages) as $idx) {
                if (isset($pages[$idx]["user"])) {
                    $pages[$idx]["user_sort"] = ilUserUtil::getNamePresentation($pages[$idx]["user"], false, false);
                }
            }
        }

        $this->setData($pages);
    }

    public function numericOrdering(string $a_field): bool
    {
        if ($a_field === "cnt") {
            return true;
        }
        return false;
    }

    protected function fillRow(array $a_set): void
    {
        $ilCtrl = $this->ctrl;

        if ($this->pg_list_mode === IL_WIKI_NEW_PAGES) {
            if ($this->ot->getContentActivated() && $this->pg_list_mode !== IL_WIKI_WHAT_LINKS_HERE) {
                $l = $a_set["lang"] === "-"
                    ? $this->ot->getMasterLanguage()
                    : $a_set["lang"];
                $this->tpl->setCurrentBlock("lang");
                $this->tpl->setVariable("LANG", $this->lng->txt("meta_l_" . $l));
                $this->tpl->parseCurrentBlock();
            }
            $this->tpl->setVariable("TXT_PAGE_TITLE", $a_set["title"]);
            $this->tpl->setVariable(
                "DATE",
                ilDatePresentation::formatDate(new ilDateTime($a_set["created"], IL_CAL_DATETIME))
            );
        } elseif ($this->pg_list_mode === IL_WIKI_POPULAR_PAGES) {
            if ($this->ot->getContentActivated()) {
                $l = $a_set["lang"] === "-"
                    ? $this->ot->getMasterLanguage()
                    : $a_set["lang"];
                $this->tpl->setCurrentBlock("lang");
                $this->tpl->setVariable("LANG", $this->lng->txt("meta_l_" . $l));
                $this->tpl->parseCurrentBlock();
            }
            $this->tpl->setVariable("TXT_PAGE_TITLE", $a_set["title"]);
            $this->tpl->setVariable("HITS", $a_set["cnt"]);
        } else {
            $this->tpl->setVariable("TXT_PAGE_TITLE", $a_set["title"]);
            $this->tpl->setVariable(
                "DATE",
                ilDatePresentation::formatDate(new ilDateTime($a_set["date"], IL_CAL_DATETIME))
            );
            if ($this->ot->getContentActivated() && $this->pg_list_mode !== IL_WIKI_WHAT_LINKS_HERE) {
                $this->tpl->setCurrentBlock("lang");
                $this->tpl->setVariable("LANG", implode(", ", $this->pm->getLanguages($a_set["id"])));
                $this->tpl->parseCurrentBlock();
            }
        }
        $this->tpl->setVariable(
            "HREF_PAGE",
            $this->pm->getPermaLink($a_set["id"], $a_set["lang"] ?? "")
        );

        // user name
        $this->tpl->setVariable(
            "TXT_USER",
            ilUserUtil::getNamePresentation(
                $a_set["user"] ?? 0,
                true,
                true,
                $ilCtrl->getLinkTarget($this->getParentObject(), $this->getParentCmd())
            )
        );
    }
}
