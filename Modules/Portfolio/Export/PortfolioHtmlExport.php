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

namespace ILIAS\Portfolio\Export;

use ILIAS\Blog\Export\BlogHtmlExport;
use ilFileUtils;

/**
 * Portfolio HTML export
 *
 * @author killing@leifos.de
 */
class PortfolioHtmlExport
{
    protected \ilObjPortfolio $portfolio;
    protected \ilObjPortfolioBaseGUI $portfolio_gui;
    protected string $export_dir;
    protected string $sub_dir;
    protected string $target_dir;
    protected \ILIAS\GlobalScreen\Services $global_screen;
    protected \ILIAS\Services\Export\HTML\Util $export_util;
    protected \ilCOPageHTMLExport $co_page_html_export;
    protected \ilLanguage $lng;
    protected array $tabs = [];
    protected array  $export_material;
    protected string $active_tab;
    protected bool $include_comments = false;
    protected bool $print_version = false;
    protected \ILIAS\Style\Content\Object\ObjectFacade $content_style_domain;

    public function __construct(
        \ilObjPortfolioBaseGUI $portfolio_gui
    ) {
        global $DIC;

        $this->portfolio_gui = $portfolio_gui;
        /** @var \ilObjPortfolio $portfolio */
        $portfolio = $portfolio_gui->getObject();
        $this->portfolio = $portfolio;


        $this->global_screen = $DIC->globalScreen();
        $this->lng = $DIC->language();

        $this->global_screen->tool()->context()->current()->addAdditionalData(
            \ilHTMLExportViewLayoutProvider::HTML_EXPORT_RENDERING,
            true
        );

        $this->content_style_domain = $DIC
            ->contentStyle()
            ->domain()->styleForObjId($this->portfolio->getId());
    }

    protected function init(): void
    {
        $this->export_dir = \ilExport::_getExportDirectory($this->portfolio->getId(), "html", "prtf");
        $this->sub_dir = $this->portfolio->getType() . "_" . $this->portfolio->getId();
        if ($this->print_version) {
            $this->sub_dir .= "print";
        }
        $this->target_dir = $this->export_dir . "/" . $this->sub_dir;
        $this->export_util = new \ILIAS\Services\Export\HTML\Util($this->export_dir, $this->sub_dir);
        $this->co_page_html_export = new \ilCOPageHTMLExport($this->target_dir);
    }

    public function includeComments(bool $a_include_comments): void
    {
        $this->include_comments = $a_include_comments;
    }

    public function setPrintVersion(bool $print_version): void
    {
        $this->print_version = $print_version;
    }

    /**
     * Initialize directories
     */
    protected function initDirectories(): void
    {
        // create export file
        \ilExport::_createExportDirectory($this->portfolio->getId(), "html", "prtf");

        // initialize temporary target directory
        ilFileUtils::delDir($this->target_dir);
        ilFileUtils::makeDir($this->target_dir);
    }

    /**
     * Export banner
     */
    protected function exportBanner(): void
    {
        // banner
        $prfa_set = new \ilSetting("prfa");
        if ($prfa_set->get("banner")) {
            $banner = $this->portfolio->getImageFullPath();
            if ($banner) { // #16096
                copy($banner, $this->target_dir . "/" . basename($banner));
            }
        }
        // page element: profile picture
        \ilObjUser::copyProfilePicturesToDirectory($this->portfolio->getOwner(), $this->target_dir);
        /*
        $ppic = \ilObjUser::_getPersonalPicturePath($this->portfolio->getOwner(), "big", true, true);
        if ($ppic) {
            $ppic = array_shift(explode("?", $ppic));
            copy($ppic, $this->target_dir . "/" . basename($ppic));
        }
        // header image: profile picture
        $ppic = \ilObjUser::_getPersonalPicturePath($this->portfolio->getOwner(), "xsmall", true, true);
        if ($ppic) {
            $ppic = array_shift(explode("?", $ppic));
            copy($ppic, $this->target_dir . "/" . basename($ppic));
        }*/
    }


    /**
     * Build export file
     */
    public function exportHtml(): string
    {
        $this->init();
        $this->initDirectories();

        $this->export_util->exportSystemStyle();
        $this->export_util->exportCOPageFiles(
            $this->content_style_domain->getEffectiveStyleId(),
            $this->portfolio->getType()
        );

        $this->exportBanner();

        // export pages
        if ($this->print_version) {
            $this->exportHTMLPagesPrint();
        } else {
            $this->exportHTMLPages();
        }

        $this->exportUserImages();

        // add js/images/file to zip
        // note: only files are still used for certificate files
        $images = $files = $js_files = [];
        foreach ($this->export_material as $items) {
            $images = array_merge($images, $items["images"]);
            $files = array_merge($files, $items["files"]);
            $js_files = array_merge($js_files, $items["js"]);
        }
        foreach (array_unique($files) as $file) {
            if (is_file($file)) {
                copy($file, $this->target_dir . "/files/" . basename($file));
            }
        }

        $this->export_util->exportResourceFiles();
        $this->co_page_html_export->exportPageElements();

        return $this->zipPackage();
    }

    /**
     * Export user images
     */
    protected function exportUserImages(): void
    {
        if ($this->include_comments) {
            $user_export = new \ILIAS\Notes\Export\UserImageExporter();
            $user_export->exportUserImagesForRepObjId($this->target_dir, $this->portfolio->getId());
        }
    }

    /**
     * Zip
     *
     * @return string
     */
    public function zipPackage(): string
    {
        // zip it all
        $date = time();
        $zip_file = \ilExport::_getExportDirectory($this->portfolio->getId(), "html", "prtf") .
            "/" . $date . "__" . IL_INST_ID . "__" .
            $this->portfolio->getType() . "_" . $this->portfolio->getId() . ".zip";
        ilFileUtils::zip($this->target_dir, $zip_file);
        ilFileUtils::delDir($this->target_dir);

        return $zip_file;
    }


    /**
     * Export all pages
     * @throws \ILIAS\UI\NotImplementedException
     * @throws \ilException
     * @throws \ilTemplateException
     */
    public function exportHTMLPages(): void
    {
        $pages = \ilPortfolioPage::getAllPortfolioPages($this->portfolio->getId());

        $this->tabs = [];
        foreach ($pages as $page) {
            // substitute blog id with title
            if ($page["type"] == \ilPortfolioPage::TYPE_BLOG) {
                $page["title"] = \ilObjBlog::_lookupTitle((int) $page["title"]);
            }

            $this->tabs[$page["id"]] = $page["title"];
        }

        // for sub-pages, e.g. blog postings
        $tpl_callback = function (array $js_files = []): \ilGlobalPageTemplate {
            return $this->getInitialisedTemplate($js_files);
        };

        $has_index = false;
        foreach ($pages as $page) {
            if (\ilPortfolioPage::_exists("prtf", $page["id"])) {
                $this->active_tab = "user_page_" . $page["id"];

                if ($page["type"] == \ilPortfolioPage::TYPE_BLOG) {
                    $link_template = "prtf_" . $page["id"] . "_bl{TYPE}_{ID}.html";

                    $blog_gui = new \ilObjBlogGUI((int) $page["title"], \ilObject2GUI::WORKSPACE_OBJECT_ID);
                    $blog_export = new BlogHtmlExport($blog_gui, $this->export_dir, $this->sub_dir, false);
                    $blog_export->exportHTMLPages($link_template, $tpl_callback, $this->co_page_html_export, "prtf_" . $page["id"] . ".html");
                } else {
                    $tpl = $this->getInitialisedTemplate();
                    $tpl->setContent($this->renderPage($page["id"]));
                    $this->writeExportFile("prtf_" . $page["id"] . ".html", $tpl->printToString());
                    $this->co_page_html_export->collectPageElements("prtf:pg", $page["id"]);
                }

                if (!$has_index && is_file($this->target_dir . "/prtf_" . $page["id"] . ".html")) {	// #20144
                    copy(
                        $this->target_dir . "/prtf_" . $page["id"] . ".html",
                        $this->target_dir . "/index.html"
                    );
                    $has_index = true;
                }
            }
        }
    }

    /**
     * Export all pages as one print version
     */
    public function exportHTMLPagesPrint(): void
    {
        // collect page elements
        $pages = \ilPortfolioPage::getAllPortfolioPages($this->portfolio->getId());
        foreach ($pages as $page) {
            if (\ilPortfolioPage::_exists("prtf", $page["id"])) {
                if ($page["type"] == \ilPortfolioPage::TYPE_BLOG) {
                    $blog_gui = new \ilObjBlogGUI((int) $page["title"], \ilObject2GUI::WORKSPACE_OBJECT_ID);
                    $blog_export = new BlogHtmlExport($blog_gui, $this->export_dir, $this->sub_dir, false);
                    $blog_export->collectAllPagesPageElements($this->co_page_html_export);
                } else {
                    $this->co_page_html_export->collectPageElements("prtf:pg", $page["id"]);
                }
            }
        }

        // render print view
        $print_view = $this->portfolio_gui->getPrintView();
        $print_view->setOffline(true);
        $html = $print_view->renderPrintView();
        file_put_contents($this->target_dir . "/index.html", $html);
    }

    /**
     * Get initialised template
     */
    public function getInitialisedTemplate(
        array $a_js_files = []
    ): \ilGlobalPageTemplate {
        global $DIC;

        $this->global_screen->layout()->meta()->reset();

        $location_stylesheet = \ilUtil::getStyleSheetLocation();
        $this->global_screen->layout()->meta()->addCss($location_stylesheet);
        $this->global_screen->layout()->meta()->addCss(
            \ilObjStyleSheet::getContentStylePath(
                $this->content_style_domain->getEffectiveStyleId()
            )
        );
        \ilPCQuestion::resetInitialState();

        // js files
        foreach ($a_js_files as $js_file) {
            $this->global_screen->layout()->meta()->addJs($js_file);
        }

        $tabs = $DIC->tabs();
        $tabs->clearTargets();
        $tabs->clearSubTabs();
        if (is_array($this->tabs)) {
            foreach ($this->tabs as $id => $caption) {
                $tabs->addTab("user_page_" . $id, $caption, "prtf_" . $id . ".html");
            }

            $tabs->activateTab($this->active_tab);
        }


        $tpl = new \ilGlobalPageTemplate($DIC->globalScreen(), $DIC->ui(), $DIC->http());

        $this->co_page_html_export->getPreparedMainTemplate($tpl);

        $tpl->setTitle($this->portfolio->getTitle());

        return $tpl;
    }

    /**
     * Write export file
     */
    public function writeExportFile(
        string $a_file,
        string $content
    ): string {
        $file = $this->target_dir . "/" . $a_file;
        // return if file is already existing
        if (is_file($file)) {
            return "";
        }

        file_put_contents($file, $content);

        return $file;
    }

    /**
     * Render page
     */
    public function renderPage(
        string $a_post_id
    ): string {
        // page
        $pgui = new \ilPortfolioPageGUI($this->portfolio->getId(), $a_post_id);
        $pgui->setOutputMode("offline");
        $pgui->setFullscreenLink("fullscreen.html"); // #12930 - see page.xsl
        $page_content = $pgui->showPage();

        $ep_tpl = new \ilTemplate(
            "tpl.export_page.html",
            true,
            true,
            "Modules/Portfolio"
        );

        $comments = ($this->include_comments)
            ? $pgui->getCommentsHTMLExport()
            : "";
        $ep_tpl->setVariable("PAGE_CONTENT", $page_content);
        $ep_tpl->setVariable("COMMENTS", $comments);

        $material = $pgui->getExportMaterial();
        $this->export_material[] = $material;

        return $ep_tpl->get();
    }
}
