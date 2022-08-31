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

namespace ILIAS\Blog\Export;

use ilFileUtils;

/**
 * Blog HTML export
 *
 * @author Alexander Killing <killing@leifos.de>
 */
class BlogHtmlExport
{
    protected \ilObjBlog $blog;
    protected \ilObjBlogGUI $blog_gui;
    protected string $export_dir;
    protected string $sub_dir;
    protected string $target_dir;
    protected \ILIAS\GlobalScreen\Services $global_screen;
    protected \ILIAS\Services\Export\HTML\Util $export_util;
    protected \ilCOPageHTMLExport $co_page_html_export;
    protected \ilLanguage $lng;
    protected \ilTabsGUI $tabs;
    protected array $items;
    protected static array $keyword_export_map;
    protected array $keywords;
    protected bool $include_comments = false;
    protected bool $print_version = false;
    protected static bool $export_key_set = false;
    protected \ILIAS\Style\Content\Object\ObjectFacade $content_style_domain;

    public function __construct(
        \ilObjBlogGUI $blog_gui,
        string $exp_dir,
        string $sub_dir,
        bool $set_export_key = true
    ) {
        global $DIC;

        $this->blog_gui = $blog_gui;
        /** @var \ilObjBlog $blog */
        $blog = $blog_gui->getObject();
        $this->blog = $blog;
        $this->export_dir = $exp_dir;
        $this->sub_dir = $sub_dir;
        $this->target_dir = $exp_dir . "/" . $sub_dir;

        $this->global_screen = $DIC->globalScreen();
        $this->export_util = new \ILIAS\Services\Export\HTML\Util($exp_dir, $sub_dir);
        $this->co_page_html_export = new \ilCOPageHTMLExport($this->target_dir);
        $this->tabs = $DIC->tabs();
        $this->lng = $DIC->language();

        $this->items = $this->blog_gui->getItems();
        $this->keywords = $this->blog_gui->getKeywords(false);
        if ($set_export_key && !self::$export_key_set) {
            self::$export_key_set = true;
            $this->global_screen->tool()->context()->current()->addAdditionalData(
                \ilHTMLExportViewLayoutProvider::HTML_EXPORT_RENDERING,
                true
            );
        }

        $cs = $DIC->contentStyle();
        if ($this->blog_gui->getIdType() === \ilObject2GUI::REPOSITORY_NODE_ID) {
            $this->content_style_domain = $cs->domain()->styleForRefId($this->blog->getRefId());
        } else {
            $this->content_style_domain = $cs->domain()->styleForObjId($this->blog->getId());
        }
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
    public function exportHTML(): string
    {
        $this->initDirectories();
        $this->export_util->exportSystemStyle();
        $this->export_util->exportCOPageFiles(
            $this->content_style_domain->getEffectiveStyleId(),
            "blog"
        );

        // export banner
        $this->exportBanner();

        // export pages
        if ($this->print_version) {
            $this->exportHTMLPagesPrint();
        } else {
            $this->exportHTMLPages();
        }

        // export comments user images
        $this->exportUserImages();

        $this->export_util->exportResourceFiles();
        $this->co_page_html_export->exportPageElements();

        return $this->zipPackage();
    }

    protected function exportUserImages(): void
    {
        if ($this->include_comments) {
            $user_export = new \ILIAS\Notes\Export\UserImageExporter();
            $user_export->exportUserImagesForRepObjId($this->target_dir, $this->blog->getId());
        }
    }

    protected function exportBanner(): void
    {
        // banner / profile picture
        $blga_set = new \ilSetting("blga");
        if ($blga_set->get("banner")) {
            $banner = $this->blog->getImageFullPath();
            if ($banner) {
                copy($banner, $this->target_dir . "/" . basename($banner));
            }
        }
        // page element: profile picture
        \ilObjUser::copyProfilePicturesToDirectory($this->blog->getOwner(), $this->target_dir);
    }

    public function zipPackage(): string
    {
        // zip it all
        $date = time();
        $type = ($this->include_comments)
            ? "html_comments"
            : "html";
        $zip_file = \ilExport::_getExportDirectory($this->blog->getId(), $type, "blog") .
            "/" . $date . "__" . IL_INST_ID . "__" .
            $this->blog->getType() . "_" . $this->blog->getId() . ".zip";
        ilFileUtils::zip($this->target_dir, $zip_file);
        ilFileUtils::delDir($this->target_dir);
        return $zip_file;
    }

    /**
     * Export all pages (note: this one is called from the portfolio html export!)
     * @throws \ILIAS\UI\NotImplementedException
     * @throws \ilTemplateException
     */
    public function exportHTMLPages(
        string $a_link_template = null,
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
        $nav = $this->blog_gui->renderNavigation("", "", $a_link_template);

        // month list
        $has_index = false;

        foreach (array_keys($this->items) as $month) {
            $list = $this->blog_gui->renderList($this->items[$month], "render", $a_link_template, false, $this->target_dir);

            if (!$list) {
                continue;
            }

            if (!$a_tpl_callback) {
                $tpl = $this->getInitialisedTemplate();
            } else {
                $tpl = $a_tpl_callback();
            }

            $file = self::buildExportLink($a_link_template, "list", $month, $this->keywords);
            $file = $this->writeExportFile($file, $tpl, $list, $nav);

            if (!$has_index) {
                copy($file, $this->target_dir . "/" . $a_index_name);
                $has_index = true;
            }
        }

        // keywords
        foreach (array_keys($this->blog_gui->getKeywords(false)) as $keyword) {
            $list_items = $this->blog_gui->filterItemsByKeyword($this->items, $keyword);
            $list = $this->blog_gui->renderList($list_items, "render", $a_link_template, false, $this->target_dir);

            if (!$list) {
                continue;
            }

            if (!$a_tpl_callback) {
                $tpl = $this->getInitialisedTemplate();
            } else {
                $tpl = $a_tpl_callback();
            }

            $file = self::buildExportLink($a_link_template, "keyword", $keyword, $this->keywords);
            $file = $this->writeExportFile($file, $tpl, $list, $nav);
        }


        // single postings

        $pages = \ilBlogPosting::getAllPostings($this->blog->getId(), 0);
        foreach ($pages as $page) {
            if (\ilBlogPosting::_exists("blp", $page["id"])) {
                $blp_gui = new \ilBlogPostingGUI(0, null, $page["id"]);
                $blp_gui->setOutputMode("offline");
                $blp_gui->setFullscreenLink("fullscreen.html"); // #12930 - see page.xsl
                $blp_gui->add_date = true;
                $page_content = $blp_gui->showPage();

                $back = self::buildExportLink(
                    $a_link_template,
                    "list",
                    substr($page["created"]->get(IL_CAL_DATE), 0, 7),
                    $this->keywords
                );

                $file = self::buildExportLink($a_link_template, "posting", $page["id"], $this->keywords);

                if (!$a_tpl_callback) {
                    $tpl = $this->getInitialisedTemplate();
                } else {
                    $tpl = $a_tpl_callback();
                }

                $comments = ($this->include_comments)
                    ? $blp_gui->getCommentsHTMLExport()
                    : "";

                // posting nav
                $nav = $this->blog_gui->renderNavigation(
                    "",
                    "",
                    $a_link_template,
                    false,
                    $page["id"]
                );

                $this->writeExportFile($file, $tpl, $page_content, $nav, $back, $comments);

                $this->co_page_html_export->collectPageElements("blp:pg", $page["id"]);
            }
        }
    }

    /**
     * Export all pages as one print version
     */
    public function exportHTMLPagesPrint(): void
    {
        $this->collectAllPagesPageElements($this->co_page_html_export);

        // render print view
        $print_view = $this->blog_gui->getPrintView();
        $print_view->setOffline(true);
        $html = $print_view->renderPrintView();
        file_put_contents($this->target_dir . "/index.html", $html);
    }


    public function collectAllPagesPageElements(\ilCOPageHTMLExport $co_page_html_export): void
    {
        $pages = \ilBlogPosting::getAllPostings($this->blog->getId(), 0);
        foreach ($pages as $page) {
            if (\ilBlogPosting::_exists("blp", $page["id"])) {
                $co_page_html_export->collectPageElements("blp:pg", $page["id"]);
            }
        }
    }

    /**
     * Build static export link
     */
    public static function buildExportLink(
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
                if (!isset(self::$keyword_export_map)) {
                    self::$keyword_export_map = array_flip(array_keys($keywords));
                }
                $a_id = (string) (self::$keyword_export_map[$a_id] ?? "");
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
        global $DIC;

        $this->global_screen->layout()->meta()->reset();

        $location_stylesheet = \ilUtil::getStyleSheetLocation();
        $this->global_screen->layout()->meta()->addCss($location_stylesheet);
        $this->global_screen->layout()->meta()->addCss(
            \ilObjStyleSheet::getContentStylePath($this->content_style_domain->getEffectiveStyleId())
        );
        \ilPCQuestion::resetInitialState();

        $tabs = $DIC->tabs();
        $tabs->clearTargets();
        $tabs->clearSubTabs();
        if ($a_back_url) {
            $tabs->setBackTarget($this->lng->txt("back"), $a_back_url);
        }
        $tpl = new \ilGlobalPageTemplate($DIC->globalScreen(), $DIC->ui(), $DIC->http());

        $this->co_page_html_export->getPreparedMainTemplate($tpl);

        $this->blog_gui->renderFullscreenHeader($tpl, $this->blog->getOwner(), true);

        return $tpl;
    }


    /**
     * Write HTML to file
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
        // return if file is already existing
        if (is_file($file)) {
            return "";
        }

        // export template: page content
        $ep_tpl = new \ilTemplate(
            "tpl.export_page.html",
            true,
            true,
            "Modules/Blog"
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
        file_put_contents($file, $content);

        return $file;
    }
}
