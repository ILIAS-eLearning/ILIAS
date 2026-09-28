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

namespace ILIAS\Blog\Export;

use ilFileUtils;
use ILIAS\components\Export\HTML\Util;

class BlogHtmlExport
{
    protected \ILIAS\Blog\Posting\PostingList $posting_list;
    protected \ILIAS\Blog\Permission\PermissionManager $perm;
    /**
     * @var int|mixed
     */
    protected mixed $id_type;
    protected int $blog_id;
    protected ExportManager $exp_manager;
    protected ?\ILIAS\Blog\Settings\Settings $settings;
    protected \ILIAS\Blog\InternalGUIService $gui;
    protected \ILIAS\Blog\Posting\PostingManager $posting_manager;
    protected \ILIAS\components\Export\HTML\ExportCollector $collector;
    protected string $export_dir;
    protected string $sub_dir;
    protected string $target_dir;
    protected \ILIAS\GlobalScreen\Services $global_screen;
    protected Util $export_util;
    protected \ilCOPageHTMLExport $co_page_html_export;
    protected \ilLanguage $lng;
    protected \ilTabsGUI $tabs;
    protected array $items;
    protected array $keywords;
    protected bool $include_comments = false;
    protected bool $print_version = false;
    protected static bool $export_key_set = false;
    protected \ILIAS\Style\Content\Object\ObjectFacade $content_style_domain;

    public function __construct(
        protected int $node_id,
        protected int $owner_id,
        protected bool $is_repository,
        string $exp_dir,
        string $sub_dir,
        bool $set_export_key = true
    ) {
        global $DIC;

        $blog_service = $DIC->blog()->internal();
        $domain = $blog_service->domain();

        if ($is_repository) {
            $this->blog_id = \ilObject::_lookupObjId($this->node_id);
        } else {
            $this->blog_id = $domain->getObjectIdForWspId($this->node_id);
        }

        $this->id_type = ($this->is_repository)
            ? \ilObjBlogGUI::REPOSITORY_NODE_ID
            : \ilObjBlogGUI::WORKSPACE_NODE_ID;

        $this->perm = $domain->perm(
            $this->node_id,
            $this->id_type,
            $domain->user()->getId(),
            $this->owner_id
        );

        $this->gui = $blog_service->gui();
        $keyword_manager = $blog_service->domain()->keywords();
        $this->settings = $blog_service->domain()->blogSettings()->getByObjId($this->blog_id);

        $this->collector = $DIC->export()->domain()->html()->collector($this->blog_id);
        $this->collector->init();

        $this->sub_dir = $sub_dir;
        $this->target_dir = $exp_dir . "/" . $sub_dir;

        $this->global_screen = $DIC->globalScreen();
        $this->export_util = new Util("", "", $this->collector);
        $this->co_page_html_export = new \ilCOPageHTMLExport($this->target_dir, null, 0, $this->collector);
        $this->tabs = $DIC->tabs();
        $this->lng = $DIC->language();

        $this->posting_list = $blog_service->domain()->postingList($this->blog_id);
        $this->items = $this->posting_list->getPostingsGroupedByMonth();
        $this->keywords = $keyword_manager->getKeywords($this->blog_id, false);
        if ($set_export_key && !self::$export_key_set) {
            self::$export_key_set = true;
            $this->global_screen->tool()->context()->current()->addAdditionalData(
                \ilHTMLExportViewLayoutProvider::HTML_EXPORT_RENDERING,
                true
            );
        }

        $cs = $DIC->contentStyle();
        if ($this->is_repository) {
            $this->content_style_domain = $cs->domain()->styleForRefId($this->node_id);
        } else {
            $this->content_style_domain = $cs->domain()->styleForObjId($this->blog_id);
        }
        $this->posting_manager = $blog_service->domain()->posting();
        $this->exp_manager = $blog_service->domain()->export()->manager();
    }
    protected function init(): void
    {
    }

    public function setPrintVersion(bool $print_version): void
    {
        $this->print_version = $print_version;
    }

    public function includeComments(
        bool $a_include_comments
    ): void {
        $this->include_comments = $a_include_comments;
    }

    protected function initDirectories(): void
    {
        // initialize temporary target directory
        ilFileUtils::delDir($this->target_dir);
        ilFileUtils::makeDir($this->target_dir);
    }

    /**
     * Export HTML
     * @throws \ILIAS\UI\NotImplementedException
     * @throws \ilTemplateException
     */
    public function exportHTML(): void
    {
        $this->initDirectories();

        $this->export_util->exportSystemStyle(
            [
                "icon_blog.svg"
            ]
        );

        $this->export_util->exportCOPageFiles(
            $this->content_style_domain->getEffectiveStyleId(),
            "blog"
        );
        // export pages
        if ($this->print_version) {
            $this->exportHTMLPagesPrint();
        } else {
            $this->exportHTMLPages();
        }
        /*
                // export comments user images
                $this->exportUserImages();
        */
        $this->export_util->exportResourceFiles();
        $this->co_page_html_export->exportPageElements();
    }

    protected function exportUserImages(): void
    {
        if ($this->include_comments) {
            $user_export = new \ILIAS\Notes\Export\UserImageExporter();
            $user_export->exportUserImagesForRepObjId($this->target_dir, $this->blog_id);
        }
    }

    public function renderList(
        array $items,
        string $month = "",
        string $a_cmd = "preview",
        string $a_link_template = "",
        bool $a_show_inactive = false,
        string $a_export_directory = ""
    ): string {
        return $this->gui->posting()->postingList(
            $this->blog_id,
            $this->perm,
            $month,
            $this->node_id,
            $this->id_type,
        )->render(
            $items,
            $a_cmd,
            $a_link_template,
            $a_show_inactive,
            $a_export_directory
        );
    }


    /**
     * Export all pages (note: this one is called from the portfolio html export!)
     * @throws \ILIAS\UI\NotImplementedException
     * @throws \ilTemplateException
     */
    public function exportHTMLPages(
        ?string $a_link_template = null,
        ?\Closure $a_tpl_callback = null,
        ?\ilCOPageHTMLExport $a_co_page_html_export = null,
        string $a_index_name = "index.html"
    ): void {
        if (!$a_link_template) {
            $a_link_template = "bl{TYPE}_{ID}.html";
        }

        if ($a_co_page_html_export) {
            $this->co_page_html_export = $a_co_page_html_export;
        }

        // lists

        // global nav
        $nav = $this->gui->navigation()->sideBar(
            null,
            $this->gui->navigation()->exportLink(
                $a_link_template,
                []
            ),
            $this->settings
        )->render(
            $this->items
        );

        // month list
        $has_index = false;
        foreach (array_keys($this->items) as $month) {
            $list = $this->renderList($this->items[$month], $month, "render", $a_link_template, false, $this->target_dir);

            if (!$list) {
                continue;
            }

            if (!$a_tpl_callback) {
                $tpl = $this->getInitialisedTemplate();
            } else {
                $tpl = $a_tpl_callback();
            }

            $file = $this->exp_manager->buildExportLink($a_link_template, "list", $month, $this->keywords);
            $file = $this->writeExportFile($file, $tpl, $list, $nav);

            if (!$has_index) {
                $file = $this->writeExportFile($a_index_name, $tpl, $list, $nav);
                $has_index = true;
            }
        }

        // keywords
        foreach (array_keys($this->keywords) as $keyword) {
            $list_items = $this->posting_list->getByKeyword($keyword);
            $list = $this->renderList($list_items, "", "render", $a_link_template, false, $this->target_dir);

            if (!$list) {
                continue;
            }

            if (!$a_tpl_callback) {
                $tpl = $this->getInitialisedTemplate();
            } else {
                $tpl = $a_tpl_callback();
            }

            $file = $this->exp_manager->buildExportLink($a_link_template, "keyword", $keyword, $this->keywords);
            $file = $this->writeExportFile($file, $tpl, $list, $nav);
        }


        // single postings

        $pages = $this->posting_manager->getAllPostings($this->blog_id, 0);
        foreach ($pages as $page) {
            $page_id = $page->getId();
            if (\ilBlogPosting::_exists("blp", $page_id)) {
                $blp_gui = new \ilBlogPostingGUI(0, null, $page_id);
                $blp_gui->setOutputMode("offline");
                $blp_gui->setFullscreenLink("fullscreen.html"); // #12930 - see page.xsl
                $blp_gui->add_date = true;
                $page_content = $blp_gui->showPage();

                $back = $this->exp_manager->buildExportLink(
                    $a_link_template,
                    "list",
                    substr($page->getCreated()->get(IL_CAL_DATE), 0, 7),
                    $this->keywords
                );

                $file = $this->exp_manager->buildExportLink($a_link_template, "posting", (string) $page_id, $this->keywords);

                if (!$a_tpl_callback) {
                    $tpl = $this->getInitialisedTemplate();
                } else {
                    $tpl = $a_tpl_callback();
                }

                $comments = ($this->include_comments)
                    ? $blp_gui->getCommentsHTMLExport()
                    : "";

                // posting nav
                $nav = $this->gui->navigation()->sideBar(
                    null,
                    $this->gui->navigation()->exportLink(
                        $a_link_template,
                        []
                    ),
                    $this->settings
                )->render(
                    $this->items,
                    false,
                    $page_id
                );

                $this->writeExportFile($file, $tpl, $page_content, $nav, (bool) $back, $comments);

                $this->co_page_html_export->collectPageElements("blp:pg", $page_id);
            }
        }

        if (!$has_index) {
            if (!$a_tpl_callback) {
                $tpl = $this->getInitialisedTemplate();
            } else {
                $tpl = $a_tpl_callback();
            }
            $file = $this->writeExportFile($a_index_name, $tpl, "", $nav);
        }
    }

    /**
     * Export all pages as one print version
     */
    public function exportHTMLPagesPrint(): void
    {
        $this->collectAllPagesPageElements($this->co_page_html_export);

        // render print view
        $print_view = $this->gui->presentation()->getPrintView(
            $this->node_id,
            $this->id_type === \ilObjBlogGUI::REPOSITORY_NODE_ID
        );
        $print_view->setOffline(true);
        $html = $print_view->renderPrintView();
        $this->collector->addString($html, "index.html");
    }


    public function collectAllPagesPageElements(\ilCOPageHTMLExport $co_page_html_export): void
    {
        $page_ids = $this->posting_manager->getAllPostingIds($this->blog_id, 0);
        foreach ($page_ids as $page_id) {
            if (\ilBlogPosting::_exists("blp", $page_id)) {
                $co_page_html_export->collectPageElements("blp:pg", $page_id);
            }
        }
    }

    /**
     * Build static export link
     */
    public function buildExportLink(
        string $a_template,
        string $a_type,
        string $a_id,
        array $keywords
    ): string {
        switch ($a_type) {
            case "list":
                $a_type = "m";
                break;

            case "keyword":
                $map = array_flip(array_keys($keywords));
                $a_id = (string) ($map[$a_id] ?? "");
                $a_type = "k";
                break;

            default:
                $a_type = "p";
                break;
        }

        return str_replace(array("{TYPE}", "{ID}"), array($a_type, $a_id), $a_template);
    }

    /**
     * Get initialised template
     */
    protected function getInitialisedTemplate(
        string $a_back_url = ""
    ): \ilGlobalPageTemplate {
        $this->export_util->resetGlobalScreen();

        $location_stylesheet = \ilUtil::getStyleSheetLocation();
        $this->global_screen->layout()->meta()->addCss($location_stylesheet);
        $this->global_screen->layout()->meta()->addCss(
            \ilObjStyleSheet::getExportContentStylePath()
        );
        \ilPCQuestion::resetInitialState();

        $tabs = $this->tabs;
        $tabs->clearTargets();
        $tabs->clearSubTabs();
        if ($a_back_url) {
            $tabs->setBackTarget($this->lng->txt("back"), $a_back_url);
        }

        $tpl = new \ilGlobalPageTemplate(
            $this->global_screen,
            $this->gui->ui(),
            $this->gui->http()
        );

        $this->co_page_html_export->getPreparedMainTemplate($tpl);

        $context = $this->gui->blogContext(
            $this->node_id,
            $this->id_type,
            $this->blog_id,
            "",
            null,
            $this->perm
        );

        $this->gui->presentation()->presentationGUI(
            $context,
            $this->content_style_domain,
        )->renderFullscreenHeader($tpl, $this->owner_id, true);

        return $tpl;
    }

    /**
     * Write HTML to file
     * @throws \ilTemplateException
     */
    protected function writeExportFile(
        string $a_file,
        \ilGlobalPageTemplate $a_tpl,
        string $a_content,
        string $a_right_content = "",
        bool $a_back = false,
        string $comments = ""
    ): string {
        $file = $this->target_dir . "/" . $a_file;

        // export template: page content
        $ep_tpl = new \ilTemplate(
            "tpl.export_page.html",
            true,
            true,
            "components/ILIAS/Blog"
        );
        if ($a_back) {
            $ep_tpl->setVariable("PAGE_CONTENT", $a_content);
            $ep_tpl->setVariable("COMMENTS", $comments);
        } else {
            $ep_tpl->setVariable("LIST", $a_content);
        }
        $a_tpl->setContent($ep_tpl->get());
        unset($ep_tpl);

        // template: right content
        if ($a_right_content) {
            $a_tpl->setRightContent($a_right_content);
        }

        $content = $a_tpl->printToString();

        // open file
        $this->collector->addString($content, $a_file);

        return $file;
    }

    public function delete(): void
    {
        $this->collector->delete();
    }

    public function getFilePath(): string
    {
        return $this->collector->getFilePath();
    }

}
