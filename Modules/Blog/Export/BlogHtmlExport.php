<?php

/* Copyright (c) 1998-2019 ILIAS open source, Extended GPL, see docs/LICENSE */

namespace ILIAS\Blog\Export;

/**
 * Blog HTML export
 *
 * @author killing@leifos.de
 */
class BlogHtmlExport
{
    /**
     * @var \ilObjBlog
     */
    protected $blog;

    /**
     * @var \ilObjBlogGUI
     */
    protected $blog_gui;

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
     * @var array
     */
    protected $items;

    /**
     * @var array
     */
    protected static $keyword_export_map;

    /**
     * @var array
     */
    protected $keywords;

    /**
     * constructor
     * @param \ilObjBlogGUI $blog_gui
     * @param string $exp_dir
     * @param string $sub_dir
     */
    public function __construct(\ilObjBlogGUI $blog_gui, string $exp_dir, string $sub_dir, bool $set_export_key = true)
    {
        global $DIC;

        $this->blog_gui = $blog_gui;
        $this->blog = $blog_gui->object;
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
        if ($set_export_key) {
            $this->global_screen->tool()->context()->current()->addAdditionalData(
                \ilHTMLExportViewLayoutProvider::HTML_EXPORT_RENDERING,
                true
            );
        }
    }

    /**
     * Initialize directories
     */
    protected function initDirectories()
    {
        // initialize temporary target directory
        \ilUtil::delDir($this->target_dir);
        \ilUtil::makeDir($this->target_dir);
    }

    /**
     * Export HTML
     *
     * @return string
     * @throws \ILIAS\UI\NotImplementedException
     * @throws \ilTemplateException
     */
    public function exportHTML()
    {
        $this->initDirectories();
        $this->export_util->exportSystemStyle();
        $this->export_util->exportCOPageFiles($this->blog->getStyleSheetId(), "blog");

        // export banner
        $this->exportBanner();

        // export pages
        $this->exportHTMLPages();

        $this->export_util->exportResourceFiles();
        $this->co_page_html_export->exportPageElements();

        return $this->zipPackage();
    }

    /**
     * Export banner
     */
    protected function exportBanner()
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

    /**
     * Zip
     *
     * @return string
     */
    public function zipPackage()
    {
        // zip it all
        $date = time();
        $zip_file = \ilExport::_getExportDirectory($this->blog->getId(), "html", "blog") .
            "/" . $date . "__" . IL_INST_ID . "__" .
            $this->blog->getType() . "_" . $this->blog->getId() . ".zip";
        \ilUtil::zip($this->target_dir, $zip_file);
        \ilUtil::delDir($this->target_dir);

        return $zip_file;
    }

    /**
     * Export all pages (note: this one is called from the portfolio html export!)
     * @param null $a_link_template
     * @param null $a_tpl_callback
     * @param null $a_co_page_html_export
     * @param string $a_index_name
     * @throws \ILIAS\UI\NotImplementedException
     * @throws \ilTemplateException
     */
    public function exportHTMLPages($a_link_template = null, $a_tpl_callback = null, $a_co_page_html_export = null, $a_index_name = "index.html")
    {
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
                $tpl = call_user_func($a_tpl_callback);
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
            $this->keyword = $keyword;
            $list_items = $this->blog_gui->filterItemsByKeyword($this->items, $keyword);
            $list = $this->blog_gui->renderList($list_items, "render", $a_link_template, false, $this->target_dir);

            if (!$list) {
                continue;
            }

            if (!$a_tpl_callback) {
                $tpl = $this->getInitialisedTemplate();
            } else {
                $tpl = call_user_func($a_tpl_callback);
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
                    $tpl = call_user_func($a_tpl_callback);
                }

                // posting nav
                $nav = $this->blog_gui->renderNavigation(
                    "",
                    "",
                    $a_link_template,
                    false,
                    $page["id"]
                );

                $this->writeExportFile($file, $tpl, $page_content, $nav, $back);

                $this->co_page_html_export->collectPageElements("blp:pg", $page["id"]);
            }
        }
    }

    /**
     * Build static export link
     *
     * @param string $a_template
     * @param string $a_type
     * @param mixed $a_id
     * @return string
     */
    public static function buildExportLink($a_template, $a_type, $a_id, $keywords)
    {
        switch ($a_type) {
            case "list":
                $a_type = "m";
                break;
                break;

            case "keyword":
                if (!isset(self::$keyword_export_map)) {
                    self::$keyword_export_map = array_flip(array_keys($keywords));
                }
                $a_id = self::$keyword_export_map[$a_id];
                $a_type = "k";
                break;

            default:
                $a_type = "p";
                break;
        }

        $link = str_replace("{TYPE}", $a_type, $a_template);
        return str_replace("{ID}", $a_id, $link);
    }

    /**
     * Get initialised template
     * @return \ilGlobalPageTemplate
     */
    protected function getInitialisedTemplate($a_back_url = "") : \ilGlobalPageTemplate
    {
        global $DIC;

        $this->global_screen->layout()->meta()->reset();

        $location_stylesheet = \ilUtil::getStyleSheetLocation();
        $this->global_screen->layout()->meta()->addCss($location_stylesheet);
        $this->global_screen->layout()->meta()->addCss(
            \ilObjStyleSheet::getContentStylePath($this->blog->getStyleSheetId())
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
     * @param string $a_file
     * @param \ilGlobalPageTemplate $a_tpl
     * @param string $a_content
     * @param string $a_right_content
     * @param bool $a_back
     * @return string|void
     * @throws \ILIAS\UI\NotImplementedException
     * @throws \ilTemplateException
     */
    protected function writeExportFile(string $a_file, \ilGlobalPageTemplate $a_tpl, string $a_content, string $a_right_content = "", bool $a_back = false)
    {
        $file = $this->target_dir . "/" . $a_file;
        // return if file is already existing
        if (@is_file($file)) {
            return;
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
        } else {
            $ep_tpl->setVariable("LIST", $a_content);
        }
        unset($a_content);
        $a_tpl->setContent($ep_tpl->get());
        unset($ep_tpl);

        // template: right content
        if ($a_right_content) {
            $a_tpl->setRightContent($a_right_content);
            unset($a_right_content);
        }

        $content = $a_tpl->printToString();

        // open file
        file_put_contents($file, $content);

        return $file;
    }
}
