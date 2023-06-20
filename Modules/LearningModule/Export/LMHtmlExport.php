<?php

/* Copyright (c) 1998-2019 ILIAS open source, Extended GPL, see docs/LICENSE */

namespace ILIAS\LearningModule\Export;

use ILIAS\COPage\PageLinker;

/**
 * LM HTML Export
 *
 * @author killing@leifos.de
 */
class LMHtmlExport
{
    /**
     * @var \ilTemplate
     */
    protected $main_tpl;

    /**
     * @var \ilObjUser
     */
    protected $user;

    /**
     * @var \ilLocatorGUI
     */
    protected $locator;

    /**
     * @var \ilCOPageHTMLExport
     */
    protected $co_page_html_export;

    /**
     * @var string
     */
    protected $export_format;

    /**
     * @var \ilLMPresentationGUI
     */
    protected $lm_gui;

    /**
     * @var \ilObjectTranslation
     */
    protected $obj_transl;

    /**
     * @var string
     */
    protected $lang;

    /**
     * @var \ilSetting
     */
    protected $lm_settings;

    /**
     * @var array
     */
    protected $offline_files = [];

    /**
     * @var string
     */
    protected $initial_user_language;

    /**
     * @var string
     */
    protected $initial_current_user_language;

    /**
     * @var \ILIAS\GlobalScreen\Services
     */
    protected $global_screen;

    /**
     * Constructor
     */
    public function __construct(
        \ilObjLearningModule $lm,
        $export_dir,
        $sub_dir,
        $export_format = "html",
        $lang = ""
    ) {
        global $DIC;

        $this->locator = $DIC["ilLocator"];
        $this->user = $DIC->user();
        $this->lm = $lm;
        $this->export_dir = $export_dir;
        $this->sub_dir = $sub_dir;
        $this->lang = $lang;
        $this->target_dir = $export_dir . "/" . $sub_dir;
        $this->co_page_html_export = new \ilCOPageHTMLExport($this->target_dir, $this->getLinker(), $lm->getRefId());
        $this->co_page_html_export->setContentStyleId(\ilObjStyleSheet::getEffectiveContentStyleId(
            $this->lm->getStyleSheetId(),
            "lm"
        ));
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

    /**
     * Get linker
     *
     * @param
     * @return
     */
    protected function getLinker() : PageLinker
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
     * Set additional context data
     *
     * @param $key
     * @param $data
     */
    protected function setAdditionalContextData($key, $data)
    {
        $additional_data = $this->global_screen->tool()->context()->current()->getAdditionalData();
        if ($additional_data->exists($key)) {
            $additional_data->replace($key, $data);
        } else {
            $additional_data->add($key, $data);
        }
    }


    /**
     * Reset user language
     */
    protected function resetUserLanguage()
    {
        $this->user->setLanguage($this->initial_user_language);
        $this->user->setCurrentLanguage($this->initial_current_user_language);
    }

    
    /**
     * Initialize directories
     */
    protected function initDirectories()
    {
        // initialize temporary target directory
        \ilUtil::delDir($this->target_dir);
        \ilUtil::makeDir($this->target_dir);
        foreach (["mobs", "files", "textimg", "style",
            "style/images", "content_style", "content_style", "content_style/images"] as $dir) {
            \ilUtil::makeDir($this->target_dir . "/" . $dir);
        }
    }


    /**
     * Get language Iterator
     *
     * @param
     * @return
     */
    protected function getLanguageIterator() : \Iterator
    {
        return new class($this->lang, $this->obj_transl) implements \Iterator {
            /**
             * @var int
             */
            private $position = 0;

            /**
             * @var string[]
             */
            private $langs = [];

            /**
             * @param string $lang
             * @param \ilObjectTranslation $obj_transl
             */
            public function __construct(string $lang, \ilObjectTranslation $obj_transl)
            {
                $this->position = 0;
                if ($lang != "all") {
                    $this->langs = [$lang];
                } else {
                    foreach ($obj_transl->getLanguages() as $otl) {
                        $this->langs[] = $otl["lang_code"];
                    }
                }
            }

            public function rewind()
            {
                $this->position = 0;
            }

            public function current()
            {
                return $this->langs[$this->position];
            }

            public function key()
            {
                return $this->position;
            }

            public function next()
            {
                ++$this->position;
            }

            public function valid()
            {
                return isset($this->langs[$this->position]);
            }
        };
    }

    /**
     * Init language
     *
     * @param \ilObjUser $user
     * @param \ilLMPresentationGUI $lm_gui
     * @param string $lang
     */
    protected function initLanguage(\ilObjUser $user, \ilLMPresentationGUI $lm_gui, string $lang)
    {
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

    /**
     * Init global screen
     */
    protected function initGlobalScreen()
    {
        // set global
        $this->global_screen->tool()->context()->current()->addAdditionalData(
            \ilLMHtmlExportViewLayoutProvider::LM_HTML_EXPORT_RENDERING,
            true
        );
    }


    /**
     * export html package
     * @param bool $zip
     */
    public function exportHTML($zip = true)
    {
        $this->initGlobalScreen();
        $this->initDirectories();

        $this->export_util->exportSystemStyle();
        $this->export_util->exportCOPageFiles($this->lm->getStyleSheetId(), "lm");

        $lang_iterator = $this->getLanguageIterator();

        // iterate all languages
        foreach ($lang_iterator as $lang) {
            $this->initLanguage($this->user, $this->lm_gui, $lang);
            $this->exportHTMLPages();
        }

        // export flv/mp3 player
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
     * Zip everything
     */
    protected function zipPackage()
    {
        if ($this->lang == "") {
            $zip_target_dir = $this->lm->getExportDirectory("html");
        } else {
            $zip_target_dir = $this->lm->getExportDirectory("html_" . $this->lang);
            \ilUtil::makeDir($zip_target_dir);
        }

        // zip it all
        $date = time();
        $zip_file = $zip_target_dir . "/" . $date . "__" . IL_INST_ID . "__" .
            $this->lm->getType() . "_" . $this->lm->getId() . ".zip";
        \ilUtil::zip($this->target_dir, $zip_file);
        \ilUtil::delDir($this->target_dir);
    }


    /**
     * Add supplying export files
     */
    protected function addSupplyingExportFiles()
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
     * Get supplying export files
     *
     * @param string $a_target_dir
     * @return array
     */
    protected function getSupplyingExportFiles($a_target_dir = ".")
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
        $use_mathjax = $mathJaxSetting->get("enable");
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
    public function exportHTMLPages()
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


    /**
     * Get initialised template
     * @return \ilGlobalPageTemplate
     */
    protected function getInitialisedTemplate() : \ilGlobalPageTemplate
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
     * Init page
     * @param int $lm_page_id
     * @param string $frame
     */
    protected function initScreen(int $lm_page_id, string $frame)
    {
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
     * export page html
     */
    public function exportPageHTML(
        $lm_page_id,
        $is_first = false,
        $lang = "-",
        $frame = "",
        $exp_id_map = []
    ) {
        global $DIC;

        $target_dir = $this->target_dir;

        $lang_suffix = "";
        if (!in_array($lang, ["-", ""]) && $this->lang == "all") {
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
        if (@is_file($file)) {
            return;
        }

        $content = $this->lm_gui->layout("main.xml", false);

        // write xml data into the file
        $fp = @fopen($file, "w+");
        fwrite($fp, $content);

        // close file
        fclose($fp);

        if ($is_first && $frame == "") {
            copy($file, $target_dir . "/index" . $lang_suffix . ".html");
        }
    }
}
