<?php

/* Copyright (c) 1998-2019 ILIAS open source, Extended GPL, see docs/LICENSE */

namespace ILIAS\Portfolio\Export;

use ILIAS\Blog\Export\BlogHtmlExport;

/**
 * Portfolio HTML export
 *
 * @author killing@leifos.de
 */
class PortfolioHtmlExport
{
    /**
     * @var \ilObjPortfolio
     */
    protected $portfolio;

    /**
     * @var \ilObjPortfolioBaseGUI
     */
    protected $portfolio_gui;

    /**
     * @var string
     */
    protected $export_dir;

    /**
     * @var string
     */
    protected $sub_dir;

    /**
     * @var string
     */
    protected $target_dir;

    /**
     * @var \ILIAS\GlobalScreen\Services
     */
    protected $global_screen;

    /**
     * @var \ILIAS\Services\Export\HTML\Util
     */
    protected $export_util;

    /**
     * @var \ilCOPageHTMLExport
     */
    protected $co_page_html_export;

    /**
     * @var \ilLanguage
     */
    protected $lng;

    /**
     * @var \ilTabsGUI
     */
    protected $tabs;

    /**
     * @var array files sub array contains certificate files
     */
    protected $export_material;

    /**
     * @var string
     */
    protected $active_tab;

    /**
     * constructor
     * @param \ilObjPortfolioBaseGUI $portfolio_gui
     */
    public function __construct(\ilObjPortfolioBaseGUI $portfolio_gui)
    {
        global $DIC;

        $this->portfolio_gui = $portfolio_gui;
        $this->portfolio = $portfolio_gui->object;

        $this->export_dir = \ilExport::_getExportDirectory($this->portfolio->getId(), "html", "prtf");
        $this->sub_dir = $this->portfolio->getType() . "_" . $this->portfolio->getId();
        $this->target_dir = $this->export_dir . "/" . $this->sub_dir;

        $this->global_screen = $DIC->globalScreen();
        $this->export_util = new \ILIAS\Services\Export\HTML\Util($this->export_dir, $this->sub_dir);
        $this->co_page_html_export = new \ilCOPageHTMLExport($this->target_dir);
        $this->tabs = $DIC->tabs();
        $this->lng = $DIC->language();

        $this->global_screen->tool()->context()->current()->addAdditionalData(
            \ilHTMLExportViewLayoutProvider::HTML_EXPORT_RENDERING,
            true
        );
    }

    /**
     * Initialize directories
     */
    protected function initDirectories()
    {
        // create export file
        \ilExport::_createExportDirectory($this->portfolio->getId(), "html", "prtf");

        // initialize temporary target directory
        \ilUtil::delDir($this->target_dir);
        \ilUtil::makeDir($this->target_dir);
    }

    /**
     * Export banner
     */
    protected function exportBanner()
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
    public function exportHtml()
    {
        $this->initDirectories();

        $this->export_util->exportSystemStyle();
        $this->export_util->exportCOPageFiles(
            $this->portfolio->getStyleSheetId(),
            $this->portfolio->getType()
        );

        $this->exportBanner();

        // export pages
        $this->exportHTMLPages();

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
     * Zip
     *
     * @return string
     */
    public function zipPackage() : string
    {
        // zip it all
        $date = time();
        $zip_file = \ilExport::_getExportDirectory($this->portfolio->getId(), "html", "prtf") .
            "/" . $date . "__" . IL_INST_ID . "__" .
            $this->portfolio->getType() . "_" . $this->portfolio->getId() . ".zip";
        \ilUtil::zip($this->target_dir, $zip_file);
        \ilUtil::delDir($this->target_dir);

        return $zip_file;
    }


    /**
     * Export all pages
     * @throws \ILIAS\UI\NotImplementedException
     * @throws \ilException
     * @throws \ilTemplateException
     */
    public function exportHTMLPages()
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
        $tpl_callback = [$this, "getInitialisedTemplate"];

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

                if (!$has_index) {
                    if (is_file($this->target_dir . "/prtf_" . $page["id"] . ".html")) {	// #20144
                        copy(
                            $this->target_dir . "/prtf_" . $page["id"] . ".html",
                            $this->target_dir . "/index.html"
                        );
                        $has_index = true;
                    }
                }
            }
        }
    }

    /**
     * Get initialised template
     * @return \ilGlobalPageTemplate
     */
    public function getInitialisedTemplate(array $a_js_files = []) : \ilGlobalPageTemplate
    {
        global $DIC;

        $this->global_screen->layout()->meta()->reset();

        $location_stylesheet = \ilUtil::getStyleSheetLocation();
        $this->global_screen->layout()->meta()->addCss($location_stylesheet);
        $this->global_screen->layout()->meta()->addCss(
            \ilObjStyleSheet::getContentStylePath($this->portfolio->getStyleSheetId())
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
    public function writeExportFile($a_file, $content)
    {
        $file = $this->target_dir . "/" . $a_file;
        // return if file is already existing
        if (@is_file($file)) {
            return;
        }

        file_put_contents($file, $content);

        return $file;
    }

    /**
     * Render page
     * @param string $a_post_id
     * @return string
     * @throws \ilTemplateException
     */
    public function renderPage(string $a_post_id)
    {
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
        $ep_tpl->setVariable("PAGE_CONTENT", $page_content);

        $material = $pgui->getExportMaterial();
        $this->export_material[] = $material;

        return $ep_tpl->get();
    }
}
