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

namespace ILIAS\Wiki\Export;

use ILIAS\User\Export\UserHtmlExport;
use ilFileUtils;

/**
 * Wiki HTML exporter class
 * @author Alexander Killing <killing@leifos.de>
 */
class WikiHtmlExport
{
    public const MODE_DEFAULT = "html";
    public const MODE_COMMENTS = "html_comments";
    public const MODE_USER = "user_html";
    public const MODE_USER_COMMENTS = "user_html_comments";
    protected \ILIAS\Services\Export\HTML\Util $export_util;

    protected \ilDBInterface $db;
    protected \ilObjUser $user;
    protected \ilLanguage $lng;
    protected \ilTabsGUI $tabs;
    protected \ilObjWiki $wiki;
    protected string $mode = self::MODE_DEFAULT;
    protected \ilLogger $log;
    protected \ilCOPageHTMLExport $co_page_html_export;
    protected string $export_dir;
    protected \ILIAS\GlobalScreen\Services $global_screen;
    protected \ilGlobalTemplateInterface $main_tpl;
    protected \ilWikiUserHTMLExport $user_html_exp;
    protected \ILIAS\Style\Content\Object\ObjectFacade $content_style_domain;

    // has global context been initialized?
    protected static $context_init = false;

    public function __construct(\ilObjWiki $a_wiki)
    {
        global $DIC;

        $this->db = $DIC->database();
        $this->user = $DIC->user();
        $this->lng = $DIC->language();
        $this->tabs = $DIC->tabs();
        $this->wiki = $a_wiki;
        $this->log = \ilLoggerFactory::getLogger('wiki');
        $this->global_screen = $DIC->globalScreen();
        $this->main_tpl = $DIC->ui()->mainTemplate();
        $this->content_style_domain = $DIC
            ->contentStyle()
            ->domain()
            ->styleForRefId($a_wiki->getRefId());
    }

    public function setMode(
        string $a_val
    ): void {
        $this->mode = $a_val;
    }

    public function getMode(): string
    {
        return $this->mode;
    }

    /**
     * Build export file
     * @throws \ilTemplateException
     * @throws \ilWikiExportException
     */
    public function buildExportFile(bool $print_version = false): string
    {
        $global_screen = $this->global_screen;
        $ilDB = $this->db;
        $ilUser = $this->user;

        $this->log->debug("buildExportFile...");
        //init the mathjax rendering for HTML export
        \ilMathJax::getInstance()->init(\ilMathJax::PURPOSE_EXPORT);

        if (in_array($this->getMode(), [self::MODE_USER, self::MODE_USER_COMMENTS])) {
            $this->user_html_exp = new \ilWikiUserHTMLExport($this->wiki, $ilDB, $ilUser, ($this->getMode() === self::MODE_USER_COMMENTS));
        }

        $ascii_name = str_replace(" ", "_", ilFileUtils::getASCIIFilename($this->wiki->getTitle()));

        // create export file
        \ilExport::_createExportDirectory($this->wiki->getId(), $this->getMode(), "wiki");
        $exp_dir =
            \ilExport::_getExportDirectory($this->wiki->getId(), $this->getMode(), "wiki");

        if (in_array($this->getMode(), [self::MODE_USER, self::MODE_USER_COMMENTS])) {
            ilFileUtils::delDir($exp_dir, true);
        }

        if (in_array($this->getMode(), [self::MODE_USER, self::MODE_USER_COMMENTS])) {
            $subdir = $ascii_name;
        } else {
            $subdir = $this->wiki->getType() . "_" . $this->wiki->getId();
        }

        if ($print_version) {
            $subdir .= "print";
        }

        $this->export_dir = $exp_dir . "/" . $subdir;

        $this->export_util = new \ILIAS\Services\Export\HTML\Util($exp_dir, $subdir);

        // initialize temporary target directory
        ilFileUtils::delDir($this->export_dir);
        ilFileUtils::makeDir($this->export_dir);

        $this->log->debug("export directory: " . $this->export_dir);


        $this->export_util->exportSystemStyle();
        $eff_style_id = $this->content_style_domain->getEffectiveStyleId();
        $this->export_util->exportCOPageFiles($eff_style_id, "wiki");
        $this->co_page_html_export = new \ilCOPageHTMLExport($this->export_dir);
        $this->co_page_html_export->setContentStyleId($eff_style_id);

        // export pages
        $this->log->debug("export pages");
        if (!self::$context_init) {
            $global_screen->tool()->context()->current()->addAdditionalData(
                \ilHTMLExportViewLayoutProvider::HTML_EXPORT_RENDERING,
                true
            );
            self::$context_init = true;
        }
        if ($print_version) {
            $this->exportHTMLPagesPrint();
        } else {
            $this->exportHTMLPages();
        }
        $this->exportUserImages();

        $this->export_util->exportResourceFiles();

        $date = time();
        $zip_file_name = (in_array($this->getMode(), [self::MODE_USER, self::MODE_USER_COMMENTS]))
            ? $ascii_name . ".zip"
            : $date . "__" . IL_INST_ID . "__" . $this->wiki->getType() . "_" . $this->wiki->getId() . ".zip";

        // zip everything
        if (true) {
            // zip it all
            $zip_file = \ilExport::_getExportDirectory($this->wiki->getId(), $this->getMode(), "wiki") .
                "/" . $zip_file_name;
            $this->log->debug("zip: " . $zip_file);
            //var_dump($zip_file);
            //exit;
            $this->log->debug("zip, export dir: " . $this->export_dir);
            $this->log->debug("zip, export file: " . $zip_file);
            ilFileUtils::zip($this->export_dir, $zip_file);
            ilFileUtils::delDir($this->export_dir);
        }
        return $zip_file;
    }

    /**
     * Export all pages
     * @throws \ilTemplateException
     * @throws \ilWikiExportException
     */
    public function exportHTMLPages(): void
    {
        global $DIC;

        $pages = \ilWikiPage::getAllWikiPages($this->wiki->getId());

        $cnt = 0;

        foreach ($pages as $page) {
            $tpl = new \ilGlobalPageTemplate($DIC->globalScreen(), $DIC->ui(), $DIC->http());
            $this->co_page_html_export->getPreparedMainTemplate($tpl);
            $this->log->debug("page: " . $page["id"]);
            if (\ilWikiPage::_exists("wpg", $page["id"])) {
                $this->log->debug("export page");
                $this->exportPageHTML($page["id"], $tpl);
                $this->log->debug("collect page elements");
                $this->co_page_html_export->collectPageElements("wpg:pg", $page["id"]);
            }

            if (in_array($this->getMode(), [self::MODE_USER, self::MODE_USER_COMMENTS])) {
                $cnt++;
                $this->log->debug("update status: " . $cnt);
                $this->user_html_exp->updateStatus((int) (50 / count($pages) * $cnt), \ilWikiUserHTMLExport::RUNNING);
            }
        }
        $this->co_page_html_export->exportPageElements(
            function (int $total, int $cnt): void {
                $this->updateUserHTMLStatusForPageElements($total, $cnt);
            }
        );
    }

    /**
     * Export all pages as one print version
     */
    public function exportHTMLPagesPrint(): void
    {
        // collect page elements
        $pages = \ilWikiPage::getAllWikiPages($this->wiki->getId());
        foreach ($pages as $page) {
            if (\ilWikiPage::_exists("wpg", $page["id"])) {
                $this->co_page_html_export->collectPageElements("wpg:pg", $page["id"]);
            }
        }
        $this->co_page_html_export->exportPageElements();

        // render print view
        $wiki_gui = new \ilObjWikiGUI([], $this->wiki->getRefId(), true);
        $print_view = $wiki_gui->getPrintView();
        $print_view->setOffline(true);
        $html = $print_view->renderPrintView();
        file_put_contents($this->export_dir . "/index.html", $html);
    }

    /**
     * Export user images
     */
    protected function exportUserImages(): void
    {
        if (in_array($this->getMode(), [self::MODE_COMMENTS, self::MODE_USER_COMMENTS])) {
            $user_export = new \ILIAS\Notes\Export\UserImageExporter();
            $user_export->exportUserImagesForRepObjId($this->export_dir, $this->wiki->getId());
        }
    }

    /**
     * Callback for updating the export status during elements export (media objects, files, ...)
     */
    public function updateUserHTMLStatusForPageElements(
        int $a_total,
        int $a_cnt
    ): void {
        if (in_array($this->getMode(), [self::MODE_USER, self::MODE_USER_COMMENTS])) {
            $this->user_html_exp->updateStatus(50 + (50 / $a_total * $a_cnt), \ilWikiUserHTMLExport::RUNNING);
        }
    }


    /**
     * Export page html
     * @throws \ilWikiExportException
     */
    public function exportPageHTML(
        int $a_page_id,
        \ilGlobalPageTemplate $tpl
    ): void {
        $this->log->debug("Export page:" . $a_page_id);
        $lng = $this->lng;
        $ilTabs = $this->tabs;

        $ilTabs->clearTargets();

        //$this->tpl->loadStandardTemplate();
        $file = $this->export_dir . "/wpg_" . $a_page_id . ".html";
        // return if file is already existing
        if (is_file($file)) {
            $this->log->debug("file already exists");
            return;
        }

        // page
        $this->log->debug("init page gui");
        $wpg_gui = new \ilWikiPageGUI($a_page_id);
        $wpg_gui->setOutputMode("offline");
        $page_content = $wpg_gui->showPage();

        // export template: page content
        $this->log->debug("init page gui-" . $this->getMode() . "-");
        $ep_tpl = new \ilTemplate(
            "tpl.export_page.html",
            true,
            true,
            "Modules/Wiki"
        );
        $ep_tpl->setVariable("PAGE_CONTENT", $page_content);

        $comments = (in_array($this->getMode(), [self::MODE_USER_COMMENTS, self::MODE_COMMENTS]))
            ? $wpg_gui->getCommentsHTMLExport()
            : "";
        $ep_tpl->setVariable("COMMENTS", $comments);

        // export template: right content
        $bl = new \ilWikiImportantPagesBlockGUI();
        $tpl->setRightContent($bl->getHTML(true));


        $this->log->debug("set title");
        $tpl->setTitle($this->wiki->getTitle());
        $tpl->setTitleIcon(
            \ilUtil::getImagePath("icon_wiki.svg"),
            $lng->txt("obj_wiki")
        );

        $tpl->setContent($ep_tpl->get());
        $content = $tpl->printToString();

        // open file
        $this->log->debug("write file: " . $file);
        if (!($fp = fopen($file, 'wb+'))) {
            $this->log->error("Could not open " . $file . " for writing.");
            throw new \ilWikiExportException("Could not open \"" . $file . "\" for writing.");
        }

        // set file permissions
        $this->log->debug("set permissions");
        chmod($file, 0770);

        // write xml data into the file
        fwrite($fp, $content);

        // close file
        fclose($fp);

        if ($this->wiki->getStartPage() === $wpg_gui->getPageObject()->getTitle()) {
            copy($file, $this->export_dir . "/index.html");
        }
    }

    /**
     * Get user export file
     */
    public function getUserExportFile(): string
    {
        $exp_dir =
            \ilExport::_getExportDirectory($this->wiki->getId(), $this->getMode(), "wiki");
        $this->log->debug("dir: " . $exp_dir);
        if (!is_dir($exp_dir)) {
            return "";
        }
        foreach (new \DirectoryIterator($exp_dir) as $fileInfo) {
            $this->log->debug("file: " . $fileInfo->getFilename());
            if (pathinfo($fileInfo->getFilename(), PATHINFO_EXTENSION) === "zip") {
                $this->log->debug("return: " . $exp_dir . "/" . $fileInfo->getFilename());
                return $exp_dir . "/" . $fileInfo->getFilename();
            }
        }
        return "";
    }
}
