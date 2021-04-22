<?php

/* Copyright (c) 1998-2019 ILIAS open source, Extended GPL, see docs/LICENSE */

namespace ILIAS\Wiki\Export;

use ILIAS\User\Export\UserHtmlExport;/**
 * Wiki HTML exporter class
 * @author Alex Killing <alex.killing@gmx.de>
 */

class WikiHtmlExport
{
    /**
     * @var \ilDBInterface
     */
    protected $db;

    /**
     * @var \ilObjUser
     */
    protected $user;

    /**
     * @var \ilLanguage
     */
    protected $lng;

    /**
     * @var \ilTabsGUI
     */
    protected $tabs;

    /**
     * @var \ilObjWiki
     */
    protected $wiki;

    const MODE_DEFAULT = "html";
    const MODE_COMMENTS = "html_comments";
    const MODE_USER = "user_html";
    const MODE_USER_COMMENTS = "user_html_comments";

    /**
     * @var string
     */
    protected $mode = self::MODE_DEFAULT;

    /**
     * @var ilLogger
     */
    protected $log;

    /**
     * @var ilCOPageHTMLExport
     */
    protected $co_page_html_export;

    /**
     * @var string
     */
    protected $export_dir;

    /**
     * @var \ILIAS\GlobalScreen\Services
     */
    protected $global_screen;

    /**
     * @var \ilGlobalPageTemplate
     */
    protected $main_tpl;

    /**
     * @var \ilWikiUserHTMLExport
     */
    protected $user_html_exp;

    /**
     * Constructor
     *
     * @param \ilObjWiki $a_wiki
     */
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
    }
    
    /**
     * Set mode
     *
     * @param int $a_val MODE_DEFAULT|MODE_USER
     */
    public function setMode(string $a_val)
    {
        $this->mode = $a_val;
    }
    
    /**
     * Get mode
     *
     * @return int MODE_DEFAULT|MODE_USER
     */
    public function getMode() : string
    {
        return $this->mode;
    }

    /**
     * Build export file
     *
     * @return string
     * @throws \ilTemplateException
     * @throws \ilWikiExportException
     */
    public function buildExportFile()
    {
        $global_screen = $this->global_screen;
        $ilDB = $this->db;
        $ilUser = $this->user;

        $this->log->debug("buildExportFile...");
        //init the mathjax rendering for HTML export
        include_once './Services/MathJax/classes/class.ilMathJax.php';
        \ilMathJax::getInstance()->init(\ilMathJax::PURPOSE_EXPORT);

        if (in_array($this->getMode(), [self::MODE_USER, self::MODE_USER_COMMENTS])) {
            include_once("./Modules/Wiki/classes/class.ilWikiUserHTMLExport.php");
            $this->user_html_exp = new \ilWikiUserHTMLExport($this->wiki, $ilDB, $ilUser, ($this->getMode() == self::MODE_USER_COMMENTS));
        }

        $ascii_name = str_replace(" ", "_", \ilUtil::getASCIIFilename($this->wiki->getTitle()));

        // create export file
        include_once("./Services/Export/classes/class.ilExport.php");
        \ilExport::_createExportDirectory($this->wiki->getId(), $this->getMode(), "wiki");
        $exp_dir =
            \ilExport::_getExportDirectory($this->wiki->getId(), $this->getMode(), "wiki");

        if (in_array($this->getMode(), [self::MODE_USER, self::MODE_USER_COMMENTS])) {
            \ilUtil::delDir($exp_dir, true);
        }

        if (in_array($this->getMode(), [self::MODE_USER, self::MODE_USER_COMMENTS])) {
            $subdir = $ascii_name;
        } else {
            $subdir = $this->wiki->getType() . "_" . $this->wiki->getId();
        }
        $this->export_dir = $exp_dir . "/" . $subdir;

        $this->export_util = new \ILIAS\Services\Export\HTML\Util($exp_dir, $subdir);

        // initialize temporary target directory
        \ilUtil::delDir($this->export_dir);
        \ilUtil::makeDir($this->export_dir);

        $this->log->debug("export directory: " . $this->export_dir);


        $this->export_util->exportSystemStyle();
        $this->export_util->exportCOPageFiles($this->wiki->getStyleSheetId(), "wiki");

        $this->co_page_html_export = new \ilCOPageHTMLExport($this->export_dir);
        $this->co_page_html_export->setContentStyleId(\ilObjStyleSheet::getEffectiveContentStyleId(
            $this->wiki->getStyleSheetId(),
            "wiki"
        ));

        // export pages
        $this->log->debug("export pages");
        $global_screen->tool()->context()->current()->addAdditionalData(\ilHTMLExportViewLayoutProvider::HTML_EXPORT_RENDERING, true);
        $this->exportHTMLPages();
        $this->exportUserImages();

        $this->export_util->exportResourceFiles($global_screen, $this->export_dir);

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
            \ilUtil::zip($this->export_dir, $zip_file);
            \ilUtil::delDir($this->export_dir);
        }
        return $zip_file;
    }

    /**
     * Export all pages
     * @throws \ilTemplateException
     * @throws \ilWikiExportException
     */
    public function exportHTMLPages()
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
        $this->co_page_html_export->exportPageElements($this->updateUserHTMLStatusForPageElements);
    }

    /**
     * Export user images
     */
    protected function exportUserImages()
    {
        if (in_array($this->getMode(), [self::MODE_COMMENTS, self::MODE_USER_COMMENTS])) {
            $user_export = new \ILIAS\Notes\Export\UserImageExporter();
            $user_export->exportUserImagesForRepObjId($this->export_dir, $this->wiki->getId());
        }
    }

    /**
     * Callback for updating the export status during elements export (media objects, files, ...)
     *
     * @param
     */
    public function updateUserHTMLStatusForPageElements($a_total, $a_cnt)
    {
        if (in_array($this->getMode(), [self::MODE_USER, self::MODE_USER_COMMENTS])) {
            $this->user_html_exp->updateStatus((int) 50 + (50 / count($a_total) * $a_cnt), \ilWikiUserHTMLExport::RUNNING);
        }
    }


    /**
     * Export page html
     * @param $a_page_id
     * @throws \ilTemplateException
     * @throws \ilWikiExportException
     */
    public function exportPageHTML($a_page_id, \ilGlobalPageTemplate $tpl)
    {
        $this->log->debug("Export page:" . $a_page_id);
        $lng = $this->lng;
        $ilTabs = $this->tabs;

        $ilTabs->clearTargets();
        

        
        //$this->tpl->loadStandardTemplate();
        $file = $this->export_dir . "/wpg_" . $a_page_id . ".html";
        // return if file is already existing
        if (@is_file($file)) {
            $this->log->debug("file already exists");
            return;
        }

        // page
        $this->log->debug("init page gui");
        include_once("./Modules/Wiki/classes/class.ilWikiPageGUI.php");
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
        include_once("./Modules/Wiki/classes/class.ilWikiImportantPagesBlockGUI.php");
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
        if (!($fp = @fopen($file, "w+"))) {
            $this->log->error("Could not open " . $file . " for writing.");
            include_once("./Modules/Wiki/exceptions/class.ilWikiExportException.php");
            throw new \ilWikiExportException("Could not open \"" . $file . "\" for writing.");
        }

        // set file permissions
        $this->log->debug("set permissions");
        chmod($file, 0770);

        // write xml data into the file
        fwrite($fp, $content);

        // close file
        fclose($fp);

        if ($this->wiki->getStartPage() == $wpg_gui->getPageObject()->getTitle()) {
            copy($file, $this->export_dir . "/index.html");
        }
    }

    /**
     * Get user export file
     *
     * @return string
     */
    public function getUserExportFile()
    {
        include_once("./Services/Export/classes/class.ilExport.php");
        $exp_dir =
            \ilExport::_getExportDirectory($this->wiki->getId(), $this->getMode(), "wiki");
        $this->log->debug("dir: " . $exp_dir);
        if (!is_dir($exp_dir)) {
            return "";
        }
        foreach (new \DirectoryIterator($exp_dir) as $fileInfo) {
            $this->log->debug("file: " . $fileInfo->getFilename());
            if (pathinfo($fileInfo->getFilename(), PATHINFO_EXTENSION) == "zip") {
                $this->log->debug("return: " . $exp_dir . "/" . $fileInfo->getFilename());
                return $exp_dir . "/" . $fileInfo->getFilename();
            }
        }
        return "";
    }
}
