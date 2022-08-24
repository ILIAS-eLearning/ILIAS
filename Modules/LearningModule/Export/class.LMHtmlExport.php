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

namespace ILIAS\LearningModule\Export;

use ILIAS\COPage\PageLinker;
use ilFileUtils;

/**
 * LM HTML Export
 *
 * @author Alexander Killing <killing@leifos.de>
 */
class LMHtmlExport
{
    protected \ILIAS\Services\Export\HTML\Util $export_util;
    protected \ilLogger $log;
    protected string $target_dir = "";
    protected string $sub_dir = "";
    protected string $export_dir = "";
    protected \ilObjLearningModule $lm;
    protected \ilGlobalTemplateInterface $main_tpl;
    protected \ilObjUser $user;
    protected \ilLocatorGUI $locator;
    protected \ilCOPageHTMLExport $co_page_html_export;
    protected string $export_format = "";
    protected \ilLMPresentationGUI $lm_gui;
    protected \ilObjectTranslation $obj_transl;
    protected string $lang = "";
    protected \ilSetting $lm_settings;
    protected array $offline_files = [];
    protected string $initial_user_language = "";
    protected string $initial_current_user_language = "";
    protected \ILIAS\GlobalScreen\Services $global_screen;
    protected \ILIAS\Style\Content\Object\ObjectFacade $content_style_domain;

    public function __construct(
        \ilObjLearningModule $lm,
        string $export_dir,
        string $sub_dir,
        string $export_format = "html",
        string $lang = ""
    ) {
        global $DIC;

        $this->locator = $DIC["ilLocator"];
        $this->user = $DIC->user();
        $this->lm = $lm;
        $this->export_dir = $export_dir;
        $this->sub_dir = $sub_dir;
        $this->lang = $lang;
        $this->target_dir = $export_dir . "/" . $sub_dir;
        $cs = $DIC->contentStyle();
        $this->content_style_domain = $cs->domain()->styleForRefId($this->lm->getRefId());
        $this->co_page_html_export = new \ilCOPageHTMLExport($this->target_dir, $this->getLinker(), $lm->getRefId());
        $this->co_page_html_export->setContentStyleId(
            $this->content_style_domain->getEffectiveStyleId()
        );
        $this->export_format = $export_format;

        // get learning module presentation gui class
        $this->lm_gui = new \ilLMPresentationGUI($export_format, ($lang == "all"), $this->target_dir, false);
        $this->obj_transl = \ilObjectTranslation::getInstance($lm->getId());

        $this->lm_settings = new \ilSetting("lm");

        $this->log = \ilLoggerFactory::getLogger("lm");

        $this->initial_user_language = $this->user->getLanguage();
        $this->initial_current_user_language = $this->user->getCurrentLanguage();

        $this->global_screen = $DIC->globalScreen();
        $this->export_util = new \ILIAS\Services\Export\HTML\Util($export_dir, $sub_dir);

        $this->setAdditionalContextData(\ilLMEditGSToolProvider::SHOW_TREE, false);
    }

    protected function getLinker(): PageLinker
    {
        return new \ilLMPresentationLinker(
            $this->lm,
            new \ilLMTree($this->lm->getId()),
            0,
            $this->lm->getRefId(),
            $this->lang,
            "",
            "",
            true,
            "html",
            false
        );
    }

    /**
     * @param mixed $data
     */
    protected function setAdditionalContextData(string $key, $data): void
    {
        $additional_data = $this->global_screen->tool()->context()->current()->getAdditionalData();
        if ($additional_data->exists($key)) {
            $additional_data->replace($key, $data);
        } else {
            $additional_data->add($key, $data);
        }
    }

    protected function resetUserLanguage(): void
    {
        $this->user->setLanguage($this->initial_user_language);
        $this->user->setCurrentLanguage($this->initial_current_user_language);
    }


    /**
     * Initialize directories
     */
    protected function initDirectories(): void
    {
        // initialize temporary target directory
        ilFileUtils::delDir($this->target_dir);
        ilFileUtils::makeDir($this->target_dir);
        foreach (["mobs", "files", "textimg", "style",
            "style/images", "content_style", "content_style", "content_style/images"] as $dir) {
            ilFileUtils::makeDir($this->target_dir . "/" . $dir);
        }
    }

    protected function getLanguageIterator(): \Iterator
    {
        return new class ($this->lang, $this->obj_transl) implements \Iterator {
            private int $position = 0;
            /** @var string[] */
            private array $langs = [];

            public function __construct(
                string $lang,
                \ilObjectTranslation $obj_transl
            ) {
                $this->position = 0;
                if ($lang != "all") {
                    $this->langs = [$lang];
                } else {
                    foreach ($obj_transl->getLanguages() as $otl) {
                        $this->langs[] = $otl["lang_code"];
                    }
                }
            }

            public function rewind(): void
            {
                $this->position = 0;
            }

            public function current(): string
            {
                return $this->langs[$this->position];
            }

            public function key(): int
            {
                return $this->position;
            }

            public function next(): void
            {
                ++$this->position;
            }

            public function valid(): bool
            {
                return isset($this->langs[$this->position]);
            }
        };
    }

    protected function initLanguage(
        \ilObjUser $user,
        \ilLMPresentationGUI $lm_gui,
        string $lang
    ): void {
        $user_lang = $user->getLanguage();

        if ($lang != "") {
            $user->setLanguage($lang);
            $user->setCurrentLanguage($lang);
        } else {
            $user->setLanguage($user_lang);
            $user->setCurrentLanguage($user_lang);
        }

        if ($lang != "") {
            if ($lang == $this->obj_transl->getMasterLanguage()) {
                $lm_gui->lang = "";
            } else {
                $lm_gui->lang = $lang;
            }
        }
    }

    protected function initGlobalScreen(): void
    {
        // set global
        $this->global_screen->tool()->context()->current()->addAdditionalData(
            \ilLMHtmlExportViewLayoutProvider::LM_HTML_EXPORT_RENDERING,
            true
        );
    }


    /**
     * @param bool $zip perform a zip at the end
     */
    public function exportHTML(bool $zip = true): void
    {
        $this->initGlobalScreen();
        $this->initDirectories();

        $this->export_util->exportSystemStyle();
        $this->export_util->exportCOPageFiles($this->content_style_domain->getEffectiveStyleId(), "lm");

        $lang_iterator = $this->getLanguageIterator();

        // iterate all languages
        foreach ($lang_iterator as $lang) {
            $this->initLanguage($this->user, $this->lm_gui, $lang);
            $this->exportHTMLPages();
        }

        // keep this for some time for reference...
        /*
        $services_dir = $a_target_dir."/Services";
        ilUtil::makeDir($services_dir);
        $media_service_dir = $services_dir."/MediaObjects";
        ilUtil::makeDir($media_service_dir);
        $flv_dir = $a_target_dir."/".ilPlayerUtil::getFlashVideoPlayerDirectory();
        ilUtil::makeDirParents($flv_dir);
        $mp3_dir = $media_service_dir."/flash_mp3_player";
        ilUtil::makeDir($mp3_dir);
        ilPlayerUtil::copyPlayerFilesToTargetDirectory($flv_dir);
        ilExplorerBaseGUI::createHTMLExportDirs($a_target_dir);
        ilPlayerUtil::copyPlayerFilesToTargetDirectory($flv_dir);

        // js files
        ilUtil::makeDir($a_target_dir.'/js');
        ilUtil::makeDir($a_target_dir.'/js/yahoo');
        ilUtil::makeDir($a_target_dir.'/css');
        foreach (self::getSupplyingExportFiles($a_target_dir) as $f)
        {
            if ($f["source"] != "")
            {
                ilUtil::makeDirParents(dirname($f["target"]));
                copy($f["source"], $f["target"]);
            }
        }*/

        // template workaround: reset of template
        /*
                $tpl = new ilGlobalTemplate("tpl.main.html", true, true);
                $tpl->setVariable("LOCATION_STYLESHEET",$location_stylesheet);
                $tpl->addBlockFile("CONTENT", "content", "tpl.adm_content.html");
        */

        $this->resetUserLanguage();

        $this->addSupplyingExportFiles();

        $this->export_util->exportResourceFiles();

        $this->co_page_html_export->exportPageElements();

        // zip everything
        if ($zip) {
            $this->zipPackage();
        }
    }

    /**
     * Zip everything, zip file will be in
     * $this->export_dir, $this->target_dir (sub-dir in export dir) will be deleted
     */
    protected function zipPackage(): void
    {
        if ($this->lang == "") {
            $zip_target_dir = $this->lm->getExportDirectory("html");
        } else {
            $zip_target_dir = $this->lm->getExportDirectory("html_" . $this->lang);
            ilFileUtils::makeDir($zip_target_dir);
        }

        // zip it all
        $date = time();
        $zip_file = $zip_target_dir . "/" . $date . "__" . IL_INST_ID . "__" .
            $this->lm->getType() . "_" . $this->lm->getId() . ".zip";
        ilFileUtils::zip($this->target_dir, $zip_file);
        ilFileUtils::delDir($this->target_dir);
    }


    /**
     * Add supplying export files
     */
    protected function addSupplyingExportFiles(): void
    {
        foreach ($this->getSupplyingExportFiles() as $f) {
            if ($f["source"] != "") {
                if ($f["type"] == "js") {
                    $this->global_screen->layout()->meta()->addJs($f["source"]);
                }
                if ($f["type"] == "css") {
                    $this->global_screen->layout()->meta()->addCss($f["source"]);
                }
            }
        }
    }

    /**
     * @todo modularize!
     */
    protected function getSupplyingExportFiles(string $a_target_dir = "."): array
    {
        $scripts = array(
            array("source" => \ilYuiUtil::getLocalPath('yahoo/yahoo-min.js'),
                "target" => $a_target_dir . '/js/yahoo/yahoo-min.js',
                "type" => "js"),
            array("source" => \ilYuiUtil::getLocalPath('yahoo-dom-event/yahoo-dom-event.js'),
                "target" => $a_target_dir . '/js/yahoo/yahoo-dom-event.js',
                "type" => "js"),
            array("source" => \ilYuiUtil::getLocalPath('animation/animation-min.js'),
                "target" => $a_target_dir . '/js/yahoo/animation-min.js',
                "type" => "js"),
            array("source" => './Services/Accordion/js/accordion.js',
                "target" => $a_target_dir . '/js/accordion.js',
                "type" => "js"),
            array("source" => './Services/Accordion/css/accordion.css',
                "target" => $a_target_dir . '/css/accordion.css',
                "type" => "css"),
            array("source" => './Modules/Scorm2004/scripts/questions/pure.js',
                "target" => $a_target_dir . '/js/pure.js',
                "type" => "js"),
            array("source" => './Modules/Scorm2004/scripts/questions/question_handling.js',
                "target" => $a_target_dir . '/js/question_handling.js',
                "type" => "js"),
            array("source" => './Modules/Scorm2004/templates/default/question_handling.css',
                "target" => $a_target_dir . '/css/question_handling.css',
                "type" => "css"),
            array("source" => './Modules/TestQuestionPool/templates/default/test_javascript.css',
                "target" => $a_target_dir . '/css/test_javascript.css',
                "type" => "css"),
            array("source" => \ilExplorerBaseGUI::getLocalExplorerJsPath(),
                "target" => $a_target_dir . "/" . \ilExplorerBaseGUI::getLocalExplorerJsPath(),
                "type" => "js"),
            array("source" => \ilExplorerBaseGUI::getLocalJsTreeJsPath(),
                "target" => $a_target_dir . "/" . \ilExplorerBaseGUI::getLocalJsTreeJsPath(),
                "type" => "js"),
            array("source" => \ilExplorerBaseGUI::getLocalJsTreeCssPath(),
                "target" => $a_target_dir . "/" . \ilExplorerBaseGUI::getLocalJsTreeCssPath(),
                "type" => "css"),
            array("source" => './Modules/LearningModule/js/LearningModule.js',
                "target" => $a_target_dir . '/js/LearningModule.js',
                "type" => "js")
        );

        $mathJaxSetting = new \ilSetting("MathJax");
        $use_mathjax = (bool) $mathJaxSetting->get("enable");
        if ($use_mathjax) {
            $scripts[] = array("source" => "",
                "target" => $mathJaxSetting->get("path_to_mathjax"),
                "type" => "js");
        }

        // auto linking js
        foreach (\ilLinkifyUtil::getLocalJsPaths() as $p) {
            if (is_int(strpos($p, "ExtLink"))) {
                $scripts[] = array("source" => $p,
                    "target" => $a_target_dir . '/js/ilExtLink.js',
                    "type" => "js");
            }
            if (is_int(strpos($p, "linkify"))) {
                $scripts[] = array("source" => $p,
                    "target" => $a_target_dir . '/js/linkify.js',
                    "type" => "js");
            }
        }

        // check, why these do not come with the gs meta collector
        $scripts[] = [
            "source" => "src/UI/templates/js/MainControls/dist/mainbar.js",
            "type" => "js"
        ];
        $scripts[] = [
            "source" => "src/UI/templates/js/MainControls/metabar.js",
            "type" => "js"
        ];
        $scripts[] = [
            "source" => "src/UI/templates/js/MainControls/slate.js",
            "type" => "js"
        ];
        $scripts[] = [
            "source" => "src/UI/templates/js/Page/stdpage.js",
            "type" => "js"
        ];
        $scripts[] = [
            "source" => "src/GlobalScreen/Client/dist/GS.js",
            "type" => "js"
        ];

        return $scripts;
    }



    /**
     * export all pages of learning module to html file
     */
    public function exportHTMLPages(): void
    {
        $lm = $this->lm;
        $lm_gui = $this->lm_gui;
        $lang = $lm_gui->lang;
        $all_languages = ($this->lang == "all");
        $lm_set = $this->lm_settings;

        $ilLocator = $this->locator;

        $pages = \ilLMPageObject::getPageList($lm->getId());

        $lm_tree = $lm->getLMTree();
        $first_page = $lm_tree->fetchSuccessorNode($lm_tree->getRootId(), "pg");
        $first_page_id = $first_page["child"];

        // iterate all learning module pages
        $mobs = [];
        $int_links = [];
        $this->offline_files = [];

        // get html export id mapping

        $exp_id_map = array();

        if ($lm_set->get("html_export_ids")) {
            foreach ($pages as $page) {
                $exp_id = \ilLMPageObject::getExportId($this->lm->getId(), $page["obj_id"]);
                if (trim($exp_id) != "") {
                    $exp_id_map[$page["obj_id"]] = trim($exp_id);
                }
            }
        }

        if ($lang == "") {
            $lang = "-";
        }

        reset($pages);
        foreach ($pages as $page) {
            if (\ilLMPage::_exists($this->lm->getType(), $page["obj_id"])) {
                $ilLocator->clearItems();
                $this->exportPageHTML($page["obj_id"], ($first_page_id == $page["obj_id"]), $lang, "", $exp_id_map);
                $this->co_page_html_export->collectPageElements("lm:pg", $page["obj_id"], $lang);
            }
        }
    }

    protected function getInitialisedTemplate(): \ilGlobalPageTemplate
    {
        global $DIC;

        $tabs = $DIC->tabs();

        $tabs->clearTargets();
        $tabs->clearSubTabs();
        $tpl = new \ilGlobalPageTemplate($DIC->globalScreen(), $DIC->ui(), $DIC->http());

        $this->co_page_html_export->getPreparedMainTemplate($tpl);

        return $tpl;
    }


    /**
     * Init global screen and learning module presentation gui for page
     */
    protected function initScreen(
        int $lm_page_id,
        string $frame
    ): void {
        $this->global_screen->layout()->meta()->reset();

        // load style sheet depending on user's settings
        $location_stylesheet = \ilUtil::getStyleSheetLocation();
        $this->global_screen->layout()->meta()->addCss($location_stylesheet);

        $this->addSupplyingExportFiles();

        // template workaround: reset of template
        $tpl = $this->getInitialisedTemplate();
        \ilPCQuestion::resetInitialState();

        $params = [
            "obj_id" => $lm_page_id,
            "ref_id" => $this->lm->getRefId(),
            "frame" => $frame
        ];

        $this->lm_gui->initByRequest($params);

        $this->setAdditionalContextData(\ilLMGSToolProvider::LM_QUERY_PARAMS, $params);
        $this->setAdditionalContextData(\ilLMGSToolProvider::LM_OFFLINE, true);

        $this->lm_gui->injectTemplate($tpl);
    }


    /**
     * export single page to file
     */
    public function exportPageHTML(
        int $lm_page_id,
        bool $is_first = false,
        string $lang = "-",
        string $frame = "",
        array $exp_id_map = []
    ): void {
        $target_dir = $this->target_dir;

        $lang_suffix = "";
        if (!in_array($lang, ["-", ""]) && $this->lang === "all") {
            $lang_suffix = "_" . $lang;
        }

        // Init template, lm_gui
        $this->initScreen($lm_page_id, $frame);

        if ($frame == "") {
            if (is_array($exp_id_map) && isset($a_exp_id_map[$lm_page_id])) {
                $file = $target_dir . "/lm_pg_" . $exp_id_map[$lm_page_id] . $lang_suffix . ".html";
            } else {
                $file = $target_dir . "/lm_pg_" . $lm_page_id . $lang_suffix . ".html";
            }
        } else {
            if ($frame != "toc") {
                $file = $target_dir . "/frame_" . $lm_page_id . "_" . $frame . $lang_suffix . ".html";
            } else {
                $file = $target_dir . "/frame_" . $frame . $lang_suffix . ".html";
            }
        }

        // return if file is already existing
        if (is_file($file)) {
            return;
        }

        $content = $this->lm_gui->layout("main.xml", false);

        // write xml data into the file
        $fp = fopen($file, "w+");
        fwrite($fp, $content);

        // close file
        fclose($fp);

        if ($is_first && $frame == "") {
            copy($file, $target_dir . "/index" . $lang_suffix . ".html");
        }
    }
}
