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

namespace ILIAS\Portfolio\Export;

use ilFileUtils;
use ILIAS\Portfolio\InternalDomainService;
use ILIAS\components\Export\HTML\Util;

class PortfolioHtmlExport
{
    protected \ILIAS\components\Export\HTML\ExportCollector $collector;
    protected InternalDomainService $domain;
    protected \ilObjPortfolio $portfolio;
    protected \ilObjPortfolioBaseGUI $portfolio_gui;
    protected string $export_dir = "";
    protected string $sub_dir = "";
    protected string $target_dir = "";
    protected \ILIAS\GlobalScreen\Services $global_screen;
    protected Util $export_util;
    protected \ilCOPageHTMLExport $co_page_html_export;
    protected \ilLanguage $lng;
    protected array $tabs = [];
    protected array $export_material = [];
    protected string $active_tab = "";
    protected bool $include_comments = false;
    protected bool $print_version = false;
    protected \ILIAS\Style\Content\Object\ObjectFacade $content_style_domain;
    protected static bool $gs_initialized = false;

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

        if (!self::$gs_initialized) {
            $this->global_screen->tool()->context()->current()->addAdditionalData(
                \ilHTMLExportViewLayoutProvider::HTML_EXPORT_RENDERING,
                true
            );
            self::$gs_initialized = true;
        }

        $this->content_style_domain = $DIC
            ->contentStyle()
            ->domain()->styleForObjId($this->portfolio->getId());
        $this->domain = $DIC->portfolio()->internal()->domain();
    }

    protected function init(): void
    {
        global $DIC;

        $this->collector = $DIC->export()->domain()->html()->collector($this->portfolio->getId());
        $this->collector->init();
        $this->export_util = new Util("", "", $this->collector);
        $this->target_dir = $this->export_dir . "/" . $this->sub_dir;
        $this->co_page_html_export = new \ilCOPageHTMLExport($this->target_dir, null, 0, $this->collector);
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
     * Build export file
     */
    public function exportHtml(): void
    {
        $this->init();

        $this->export_util->exportSystemStyle(
            [
                "icon_prtf.svg"
            ]
        );
        $this->export_util->exportCOPageFiles(
            $this->content_style_domain->getEffectiveStyleId(),
            $this->portfolio->getType()
        );

        // export pages
        if ($this->print_version) {
            $this->exportHTMLPagesPrint();
        } else {
            $this->exportHTMLPages();
        }

        //$this->exportUserImages();
        //\ilObjUser::copyProfilePicturesToDirectory($this->portfolio->getOwner(), $this->target_dir);

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
                //copy($file, $this->target_dir . "/files/" . basename($file));
            }
        }

        $this->export_util->exportResourceFiles();
        $this->co_page_html_export->exportPageElements();

        //return $this->zipPackage();
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
            $this->tabs[$page["id"]] = $page["title"];
        }

        $has_index = false;
        foreach ($pages as $page) {
            if (\ilPortfolioPage::_exists("prtf", $page["id"])) {
                $this->active_tab = "user_page_" . $page["id"];

                $tpl = $this->getInitialisedTemplate();
                $tpl->setContent($this->renderPage((int) $page["id"]));
                $this->writeExportFile("prtf_" . $page["id"] . ".html", $tpl->printToString());
                $this->co_page_html_export->collectPageElements("prtf:pg", $page["id"]);

                if (!$has_index) {	// #20144
                    $this->collector->addString($tpl->printToString(), "index.html");
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
                $this->co_page_html_export->collectPageElements("prtf:pg", $page["id"]);
            }
        }

        // render print view
        $print_view = $this->portfolio_gui->getPrintView();
        $print_view->setOffline(true);
        $html = $print_view->renderPrintView();
        //file_put_contents($this->target_dir . "/index.html", $html);
        $this->collector->addString($html, "index.html");
    }

    /**
     * Get initialised template
     */
    public function getInitialisedTemplate(
        array $a_js_files = []
    ): \ilGlobalPageTemplate {
        global $DIC;

        $this->export_util->resetGlobalScreen();

        $location_stylesheet = \ilUtil::getStyleSheetLocation();
        $this->global_screen->layout()->meta()->addCss($location_stylesheet);
        $this->global_screen->layout()->meta()->addCss(
            \ilObjStyleSheet::getExportContentStylePath()
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
        /*
        if (is_file($file)) {
            return "";
        }*/

        //file_put_contents($file, $content);
        $this->collector->addString($content, $a_file);

        return $file;
    }

    /**
     * Render page
     */
    public function renderPage(
        int $a_post_id
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
            "components/ILIAS/Portfolio"
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

    public function deliver(string $filename, bool $remove = false): void
    {
        $this->collector->deliver($filename);
        $this->delete();
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
