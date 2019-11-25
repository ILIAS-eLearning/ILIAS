<?php

/* Copyright (c) 1998-2019 ILIAS open source, Extended GPL, see docs/LICENSE */

namespace ILIAS\Modules\LearningModule\Export;

use ILIAS\Tools\Maintainers\Iterator;

/**
 * LM HTML Export
 *
 * @author killing@leifos.de
 */
class HTMLExport
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
    protected $offline_mobs = [];

    /**
     * @var array
     */
    protected $offline_int_links = [];

    /**
     * @var array
     */
    protected $offline_files = [];

    /**
     * @var array
     */
    protected $q_ids = [];

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
    public function __construct(\ilObjLearningModule $lm,
        $export_dir,
        $sub_dir,
        $export_format = "html",
        $lang = "")
    {
        global $DIC;

        $this->locator = $DIC["ilLocator"];
        $this->user = $DIC->user();
        $this->lm = $lm;
        $this->export_dir = $export_dir;
        $this->sub_dir = $sub_dir;
        $this->target_dir = $export_dir."/".$sub_dir;
        $this->co_page_html_export = new \ilCOPageHTMLExport($this->target_dir);
        $this->export_format = $export_format;
        $this->lang = $lang;

        // get learning module presentation gui class
        $_GET["cmd"] = "nop";
        $_GET["transl"] = "";
        $this->lm_gui = new \ilLMPresentationGUI($export_format, ($lang == "all"), $this->target_dir, false);
        $this->obj_transl = \ilObjectTranslation::getInstance($lm->getId());

        $this->lm_settings = new \ilSetting("lm");

        $this->log = \ilLoggerFactory::getLogger("lm");

        $this->initial_user_language = $this->user->getLanguage();
        $this->initial_current_user_language = $this->user->getCurrentLanguage();

        $this->global_screen = $DIC->globalScreen();
        $this->export_util = new \ILIAS\Services\Export\HTML\Util();
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
            \ilUtil::makeDir($this->target_dir."/".$dir);
        }
    }

    /**
     * Init MathJax
     */
    protected function initMathJax()
    {
        // init the mathjax rendering for HTML export
        \ilMathJax::getInstance()->init(\ilMathJax::PURPOSE_EXPORT);
    }

    /**
     * Export system style
     */
    protected function exportSystemStyle()
    {
        // system style html exporter
        include_once("./Services/Style/System/classes/class.ilSystemStyleHTMLExport.php");
        $sys_style_html_export = new \ilSystemStyleHTMLExport($this->target_dir);
        $sys_style_html_export->addImage("icon_lm.svg");
        $sys_style_html_export->export();
    }

    /**
     * Export content style
     */
    protected function exportContentStyle()
    {
        // init co page html exporter
        $this->co_page_html_export->setContentStyleId($this->lm->getStyleSheetId());
        $this->co_page_html_export->createDirectories();
        $this->co_page_html_export->exportStyles();
        $this->co_page_html_export->exportSupportScripts();
    }

    /**
     * Get language Iterator
     *
     * @param
     * @return
     */
    protected function getLanguageIterator(): \Iterator
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
            public function __construct(string $lang, \ilObjectTranslation $obj_transl) {
                $this->position = 0;
                if ($lang != "all")
                {
                    $this->langs = [$lang];
                }
                else
                {
                    foreach ($obj_transl->getLanguages() as $otl)
                    {
                        $this->langs[] = $otl["lang_code"];
                    }
                }
            }

            public function rewind() {
                $this->position = 0;
            }

            public function current() {
                return $this->langs[$this->position];
            }

            public function key() {
                return $this->position;
            }

            public function next() {
                ++$this->position;
            }

            public function valid() {
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
            \ilLMHtmlExportViewLayoutProvider::LM_HTML_EXPORT_RENDERING, true);
    }


    /**
     * export html package
     */
    function exportHTML($zip = true)
    {
        $this->initGlobalScreen();
        $this->initDirectories();
        $this->initMathJax();
        $this->exportSystemStyle();
        $this->exportContentStyle();

        $lang_iterator = $this->getLanguageIterator();

        // iterate all languages
        foreach ($lang_iterator as $lang)
        {
            $this->initLanguage($this->user, $this->lm_gui, $lang);
            $this->exportHTMLPages();
        }
        // export glossary terms
//        $this->exportHTMLGlossaryTerms($lm_gui, $a_target_dir);

        // export all media objects
        $linked_mobs = array();
        foreach ($this->offline_mobs as $mob)
        {
            if (\ilObject::_exists($mob) && \ilObject::_lookupType($mob) == "mob")
            {
                $this->exportHTMLMOB($mob, "_blank", $linked_mobs);
            }
        }
        $linked_mobs2 = array();				// mobs linked in link areas
        foreach ($linked_mobs as $mob)
        {
            if (\ilObject::_exists($mob))
            {
                $this->exportHTMLMOB($mob, "_blank", $linked_mobs2);
            }
        }

        /*$_GET["obj_type"] = "MediaObject";
        $_GET["obj_id"]  = $a_mob_id;
        $_GET["cmd"] = "";*/

        // export all file objects
/*
        foreach ($this->offline_files as $file)
        {
            $this->exportHTMLFile($a_target_dir, $file);
        }
*/

/*
        // export questions (images)
        if (count($this->q_ids) > 0)
        {
            foreach ($this->q_ids as $q_id)
            {
                ilUtil::makeDirParents($a_target_dir."/assessment/0/".$q_id."/images");
                ilUtil::rCopy(ilUtil::getWebspaceDir()."/assessment/0/".$q_id."/images",
                    $a_target_dir."/assessment/0/".$q_id."/images");
            }
        }
*/

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

        $this->export_util->exportResourceFiles($this->global_screen, $this->target_dir);

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
        if ($this->lang == "")
        {
            $zip_target_dir = $this->lm->getExportDirectory("html");
        }
        else
        {
            $zip_target_dir = $this->lm->getExportDirectory("html_".$this->lang);
            \ilUtil::makeDir($zip_target_dir);
        }

        // zip it all
        $date = time();
        $zip_file = $zip_target_dir."/".$date."__".IL_INST_ID."__".
            $this->lm->getType()."_".$this->lm->getId().".zip";
        \ilUtil::zip($this->target_dir, $zip_file);
        \ilUtil::delDir($this->target_dir);
    }
    

    /**
     * Get supplying export files
     *
     * @param
     * @return
     */
    /*
    static function getSupplyingExportFiles($a_target_dir = ".")
    {
        $scripts = array(
            array("source" => ilYuiUtil::getLocalPath('yahoo/yahoo-min.js'),
                "target" => $a_target_dir.'/js/yahoo/yahoo-min.js',
                "type" => "js"),
            array("source" => ilYuiUtil::getLocalPath('yahoo-dom-event/yahoo-dom-event.js'),
                "target" => $a_target_dir.'/js/yahoo/yahoo-dom-event.js',
                "type" => "js"),
            array("source" => ilYuiUtil::getLocalPath('animation/animation-min.js'),
                "target" => $a_target_dir.'/js/yahoo/animation-min.js',
                "type" => "js"),
            array("source" => './Services/JavaScript/js/Basic.js',
                "target" => $a_target_dir.'/js/Basic.js',
                "type" => "js"),
            array("source" => './Services/Accordion/js/accordion.js',
                "target" => $a_target_dir.'/js/accordion.js',
                "type" => "js"),
            array("source" => './Services/Accordion/css/accordion.css',
                "target" => $a_target_dir.'/css/accordion.css',
                "type" => "css"),
            array("source" => iljQueryUtil::getLocaljQueryPath(),
                "target" => $a_target_dir.'/js/jquery.js',
                "type" => "js"),
            array("source" => iljQueryUtil::getLocalMaphilightPath(),
                "target" => $a_target_dir.'/js/maphilight.js',
                "type" => "js"),
            array("source" => iljQueryUtil::getLocaljQueryUIPath(),
                "target" => $a_target_dir.'/js/jquery-ui-min.js',
                "type" => "js"),
            array("source" => './Services/COPage/js/ilCOPagePres.js',
                "target" => $a_target_dir.'/js/ilCOPagePres.js',
                "type" => "js"),
            array("source" => './Modules/Scorm2004/scripts/questions/pure.js',
                "target" => $a_target_dir.'/js/pure.js',
                "type" => "js"),
            array("source" => './Modules/Scorm2004/scripts/questions/question_handling.js',
                "target" => $a_target_dir.'/js/question_handling.js',
                "type" => "js"),
            array("source" => './Modules/TestQuestionPool/js/ilMatchingQuestion.js',
                "target" => $a_target_dir.'/js/ilMatchingQuestion.js',
                "type" => "js"),
            array("source" => './Modules/Scorm2004/templates/default/question_handling.css',
                "target" => $a_target_dir.'/css/question_handling.css',
                "type" => "css"),
            array("source" => './Modules/TestQuestionPool/templates/default/test_javascript.css',
                "target" => $a_target_dir.'/css/test_javascript.css',
                "type" => "css"),
            array("source" => ilPlayerUtil::getLocalMediaElementJsPath(),
                "target" => $a_target_dir."/".ilPlayerUtil::getLocalMediaElementJsPath(),
                "type" => "js"),
            array("source" => ilPlayerUtil::getLocalMediaElementCssPath(),
                "target" => $a_target_dir."/".ilPlayerUtil::getLocalMediaElementCssPath(),
                "type" => "css"),
            array("source" => ilExplorerBaseGUI::getLocalExplorerJsPath(),
                "target" => $a_target_dir."/".ilExplorerBaseGUI::getLocalExplorerJsPath(),
                "type" => "js"),
            array("source" => ilExplorerBaseGUI::getLocalJsTreeJsPath(),
                "target" => $a_target_dir."/".ilExplorerBaseGUI::getLocalJsTreeJsPath(),
                "type" => "js"),
            array("source" => ilExplorerBaseGUI::getLocalJsTreeCssPath(),
                "target" => $a_target_dir."/".ilExplorerBaseGUI::getLocalJsTreeCssPath(),
                "type" => "css"),
            array("source" => './Modules/LearningModule/js/LearningModule.js',
                "target" => $a_target_dir.'/js/LearningModule.js',
                "type" => "js")
        );

        $mathJaxSetting = new ilSetting("MathJax");
        $use_mathjax = $mathJaxSetting->get("enable");
        if ($use_mathjax)
        {
            $scripts[] = array("source" => "",
                "target" => $mathJaxSetting->get("path_to_mathjax"),
                "type" => "js");
        }

        // auto linking js
        foreach (ilLinkifyUtil::getLocalJsPaths() as $p)
        {
            if (is_int(strpos($p, "ExtLink")))
            {
                $scripts[] = array("source" => $p,
                    "target" => $a_target_dir.'/js/ilExtLink.js',
                    "type" => "js");
            }
            if (is_int(strpos($p, "linkify")))
            {
                $scripts[] = array("source" => $p,
                    "target" => $a_target_dir.'/js/linkify.js',
                    "type" => "js");
            }
        }

        return $scripts;

    }*/

    /**
     * export file object
     */
    function exportHTMLFile($a_target_dir, $a_file_id)
    {
        $file_dir = $a_target_dir."/files/file_".$a_file_id;
        ilUtil::makeDir($file_dir);
        $file_obj = new ilObjFile($a_file_id, false);
        $source_file = $file_obj->getDirectory($file_obj->getVersion())."/".$file_obj->getFileName();
        if (!is_file($source_file))
        {
            $source_file = $file_obj->getDirectory()."/".$file_obj->getFileName();
        }
        if (is_file($source_file))
        {
            copy($source_file, $file_dir."/".$file_obj->getFileName());
        }
    }

    /**
     * Init media screen
     *
     * @param int $mob_id
     * @param string $frame
     * @param bool $fullscreen
     */
    protected function initMediaScreen(int $mob_id, string $frame, bool $fullscreen = false)
    {
        $params = [
            "obj_type" => "MediaObject",
            "frame" => $frame,
            "cmd" => ""
        ];

        if ($fullscreen) {
            $params = [
                "obj_type" => "",
                "frame" => "",
                "cmd" => "fullscreen"
            ];
        }

        $params["ref_id"] = $this->lm->getRefId();
        $params["mob_id"] = $mob_id;

        $this->lm_gui->initByRequest($params);

        $this->global_screen->tool()->context()->current()->addAdditionalData(
            \ilLMGSToolProvider::LM_QUERY_PARAMS, $params);
        $this->global_screen->tool()->context()->current()->addAdditionalData(
            \ilLMGSToolProvider::LM_OFFLINE, true);
    }
    
    
    /**
     * @param int $a_mob_id
     * @param string $a_frame
     * @param array $a_linked_mobs
     * @throws \ILIAS\Filesystem\Exception\DirectoryNotFoundException
     * @throws \ILIAS\Filesystem\Exception\FileNotFoundException
     * @throws \ILIAS\Filesystem\Exception\IOException
     */
    function exportHTMLMOB(int $a_mob_id, string $a_frame, array &$a_linked_mobs)
    {
        $lm_gui = $this->lm_gui;
        $target_dir = $this->target_dir;

        $mob_dir = $target_dir."/mobs";

        $source_dir = \ilUtil::getWebspaceDir()."/mobs/mm_".$a_mob_id;
        if (@is_dir($source_dir))
        {
            \ilUtil::makeDir($mob_dir."/mm_".$a_mob_id);
            \ilUtil::rCopy($source_dir, $mob_dir."/mm_".$a_mob_id);
        }

        $this->initMediaScreen($a_mob_id, $a_frame);

        $content = $lm_gui->media();
        $file = $target_dir."/media_".$a_mob_id.".html";

        // open file
        if (!($fp = @fopen($file,"w+")))
        {
            die ("<b>Error</b>: Could not open \"".$file."\" for writing".
                " in <b>".__FILE__."</b> on line <b>".__LINE__."</b><br />");
        }
        //chmod($file, 0770);
        fwrite($fp, $content);
        fclose($fp);

        // fullscreen
        $mob_obj = new \ilObjMediaObject($a_mob_id);
        if ($mob_obj->hasFullscreenItem())
        {
            $this->initMediaScreen($a_mob_id, "", true);

            $content = $lm_gui->fullscreen();
            $file = $target_dir."/fullscreen_".$a_mob_id.".html";

            // open file
            if (!($fp = @fopen($file,"w+")))
            {
                die ("<b>Error</b>: Could not open \"".$file."\" for writing".
                    " in <b>".__FILE__."</b> on line <b>".__LINE__."</b><br />");
            }
            //chmod($file, 0770);
            fwrite($fp, $content);
            fclose($fp);
        }
        $linked_mobs = $mob_obj->getLinkedMediaObjects();
        foreach ($linked_mobs as $id)
        {
            $this->log->debug("HTML Export: Add media object $id (".\ilObject::_lookupTitle($id).") ".
                " due to media object ".$a_mob_id." (".\ilObject::_lookupTitle($a_mob_id).").");
        }
        $a_linked_mobs = array_merge($a_linked_mobs, $linked_mobs);
    }

    /**
     * export glossary terms
     */
    function exportHTMLGlossaryTerms(&$a_lm_gui, $a_target_dir)
    {
        $ilLocator = $this->locator;

        foreach($this->offline_int_links as $int_link)
        {
            $ilLocator->clearItems();
            if ($int_link["type"] == "git")
            {
                $tpl = new ilGlobalTemplate("tpl.main.html", true, true);
                $tpl->addBlockFile("CONTENT", "content", "tpl.adm_content.html");

                $_GET["obj_id"] = $int_link["id"];
                $_GET["frame"] = "_blank";
                $content = $a_lm_gui->glossary();
                $file = $a_target_dir."/term_".$int_link["id"].".html";

                // open file
                if (!($fp = @fopen($file,"w+")))
                {
                    die ("<b>Error</b>: Could not open \"".$file."\" for writing".
                        " in <b>".__FILE__."</b> on line <b>".__LINE__."</b><br />");
                }
                chmod($file, 0770);
                fwrite($fp, $content);
                fclose($fp);

                // store linked/embedded media objects of glosssary term
                $defs = ilGlossaryDefinition::getDefinitionList($int_link["id"]);
                foreach($defs as $def)
                {
                    $def_mobs = ilObjMediaObject::_getMobsOfObject("gdf:pg", $def["id"]);
                    foreach($def_mobs as $def_mob)
                    {
                        $this->offline_mobs[$def_mob] = $def_mob;
                        $this->log->debug("HTML Export: Add media object $def_mob (".\ilObject::_lookupTitle($def_mob).") ".
                            " due to glossary entry ".$int_link["id"]." (".ilGlossaryTerm::_lookGlossaryTerm($int_link["id"]).").");
                    }

                    // get all files of page
                    $def_files = ilObjFile::_getFilesOfObject("gdf:pg", $page["obj_id"]);
                    $this->offline_files = array_merge($this->offline_files, $def_files);

                }

            }
        }
    }

    /**
     * export all pages of learning module to html file
     */
    function exportHTMLPages()
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

        if ($lm_set->get("html_export_ids"))
        {
            foreach ($pages as $page)
            {
                $exp_id = \ilLMPageObject::getExportId($this->lm->getId(), $page["obj_id"]);
                if (trim($exp_id) != "")
                {
                    $exp_id_map[$page["obj_id"]] = trim($exp_id);
                }
            }
        }

        if ($lang == "")
        {
            $lang = "-";
        }

        reset($pages);
        foreach ($pages as $page)
        {
            if (\ilLMPage::_exists($this->lm->getType(), $page["obj_id"]))
            {
                $ilLocator->clearItems();
                $this->exportPageHTML($page["obj_id"], ($first_page_id == $page["obj_id"]), $lang, "", $exp_id_map);

                // get all snippets of page
                $pcs = \ilPageContentUsage::getUsagesOfPage($page["obj_id"], $this->lm->getType().":pg", 0, false, $lang);
                foreach ($pcs as $pc)
                {
                    if ($pc["type"] == "incl")
                    {
                        $incl_mobs = \ilObjMediaObject::_getMobsOfObject("mep:pg", $pc["id"]);
                        foreach($incl_mobs as $incl_mob)
                        {
                            $mobs[$incl_mob] = $incl_mob;
                            $this->log->debug("HTML Export: Add media object $incl_mob (".\ilObject::_lookupTitle($incl_mob).") ".
                                " due to snippet ".$pc["id"]." in page ".$page["obj_id"]." (".\ilLMObject::_lookupTitle($page["obj_id"]).").");
                        }
                    }
                }

                // get all media objects of page
                $pg_mobs = \ilObjMediaObject::_getMobsOfObject($this->lm->getType().":pg", $page["obj_id"], 0, $lang);
                foreach($pg_mobs as $pg_mob)
                {
                    $mobs[$pg_mob] = $pg_mob;
                    $this->log->debug("HTML Export: Add media object $pg_mob (".\ilObject::_lookupTitle($pg_mob).") ".
                        " due to page ".$page["obj_id"]." (".\ilLMObject::_lookupTitle($page["obj_id"]).").");
                }

                // get all internal links of page
                $pg_links = \ilInternalLink::_getTargetsOfSource($this->lm->getType().":pg", $page["obj_id"], $lang);
                $int_links = array_merge($int_links, $pg_links);

                // get all files of page
                $pg_files = \ilObjFile::_getFilesOfObject($this->lm->getType().":pg", $page["obj_id"], 0, $lang);
                $this->offline_files = array_merge($this->offline_files, $pg_files);

                // collect all questions
                $q_ids = \ilPCQuestion::_getQuestionIdsForPage($this->lm->getType(), $page["obj_id"], $lang);
                foreach($q_ids as $q_id)
                {
                    $this->q_ids[$q_id] = $q_id;
                }

            }
        }
        foreach ($mobs as $m)
        {
            $this->offline_mobs[$m] = $m;
        }
        foreach ($int_links as $k => $v)
        {
            $this->offline_int_links[$k] = $v;
        }
    }


    /**
     * Get initialised template
     * @return \ilGlobalPageTemplate
     */
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
     * Init page
     * @param int $lm_page_id
     * @param string $frame
     */
    protected function initScreen(int $lm_page_id, string $frame)
    {
        // template workaround: reset of template
        $tpl = $this->getInitialisedTemplate();
        \ilPCQuestion::resetInitialState();

        $params = [
            "obj_id" => $lm_page_id,
            "ref_id" => $this->lm->getRefId(),
            "frame" => $frame
        ];

        $this->lm_gui->initByRequest($params);

        $this->global_screen->tool()->context()->current()->addAdditionalData(
            \ilLMGSToolProvider::LM_QUERY_PARAMS, $params);
        $this->global_screen->tool()->context()->current()->addAdditionalData(
            \ilLMGSToolProvider::LM_OFFLINE, true);

        $this->lm_gui->injectTemplate($tpl);
    }


    /**
     * export page html
     */
    function exportPageHTML($lm_page_id, $is_first = false,
        $lang = "-",
        $frame = "",
        $exp_id_map = [])
    {
        global $DIC;

        $target_dir = $this->target_dir;

        $lang_suffix = "";
        if (!in_array($lang, ["-", ""]) && $this->lang == "all") {
            $lang_suffix = "_".$lang;
        }

        // Init template, lm_gui
        $this->initScreen($lm_page_id, $frame);


        if ($frame == "")
        {
            if (is_array($exp_id_map) && isset($a_exp_id_map[$lm_page_id]))
            {
                $file = $target_dir."/lm_pg_".$exp_id_map[$lm_page_id].$lang_suffix.".html";
            }
            else
            {
                $file = $target_dir."/lm_pg_".$lm_page_id.$lang_suffix.".html";
            }
        }
        else
        {
            if ($frame != "toc")
            {
                $file = $target_dir."/frame_".$lm_page_id."_".$frame.$lang_suffix.".html";
            }
            else
            {
                $file = $target_dir."/frame_".$frame.$lang_suffix.".html";
            }
        }

        // return if file is already existing
        if (@is_file($file))
        {
            return;
        }

        $content = $this->lm_gui->layout("main.xml", false);

        // open file
        if (!($fp = @fopen($file,"w+")))
        {
            die ("<b>Error</b>: Could not open \"".$file."\" for writing".
                " in <b>".__FILE__."</b> on line <b>".__LINE__."</b><br />");
        }

        // set file permissions
        //chmod($file, 0770);

        // write xml data into the file
        fwrite($fp, $content);

        // close file
        fclose($fp);

        if ($is_first && $frame == "")
        {
            copy($file, $target_dir."/index".$lang_suffix.".html");
        }

        // write frames of frameset
        /*
        $frameset = $a_lm_gui->getCurrentFrameSet();

        foreach ($frameset as $frame)
        {
            $this->exportPageHTML($a_lm_gui, $a_target_dir, $a_lm_page_id, $frame);
        }*/

    }

}