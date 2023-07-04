<?php
/* Copyright (c) 1998-2011 ILIAS open source, Extended GPL, see docs/LICENSE */

/**
 * HTML export class for pages
 *
 * @author Alex Killing <alex.killing@gmx.de>
 * @version $Id: $
 * @ingroup ServicesCOPage
 */
class ilCOPageHTMLExport
{
    /**
     * @var array
     */
    protected $mobs = [];

    /**
     * @var array
     */
    protected $glossary_terms = [];

    /**
     * @var array
     */
    protected $files = [];

    /**
     * @var array
     */
    protected $files_direct = [];

    /**
     * @var array
     */
    protected $int_links = [];

    /**
     * @var array
     */
    protected $q_ids = [];

    /**
     * @var string
     */
    protected $exp_dir = "";

    /**
     * @var int
     */
    protected $content_style_id = 0;

    /**
     * @var ilObjUser
     */
    protected $user;

    /**
     * @var ilLogger
     */
    protected $log;

    /**
     * @var \ILIAS\GlobalScreen\Services
     */
    protected $global_screen;

    /**
     * @var \ILIAS\COPage\PageLinker
     */
    protected $page_linker;

    /**
     * @var int
     */
    protected $ref_id;

    /**
     * ilCOPageHTMLExport constructor.
     * @param $a_exp_dir
     */
    public function __construct($a_exp_dir, \ILIAS\COPage\PageLinker $linker = null, $ref_id = 0)
    {
        global $DIC;

        $this->log = ilLoggerFactory::getLogger('copg');
        $this->user = $DIC->user();
        $this->global_screen = $DIC->globalScreen();
        $this->page_linker = is_null($linker)
            ? new ilPageLinker("", true)
            : $linker;
        $this->ref_id = $ref_id;

        $this->exp_dir = $a_exp_dir;
        $this->mobs_dir = $a_exp_dir . "/mobs";
        $this->files_dir = $a_exp_dir . "/files";
        $this->tex_dir = $a_exp_dir . "/teximg";
        $this->content_style_dir = $a_exp_dir . "/content_style";
        $this->content_style_img_dir = $a_exp_dir . "/content_style/images";

        include_once("./Services/MediaObjects/classes/class.ilPlayerUtil.php");
        $this->services_dir = $a_exp_dir . "/Services";
        $this->media_service_dir = $this->services_dir . "/MediaObjects";
        $this->flv_dir = $a_exp_dir . "/" . ilPlayerUtil::getFlashVideoPlayerDirectory();
        $this->mp3_dir = $this->media_service_dir . "/flash_mp3_player";

        $this->js_dir = $a_exp_dir . '/js';
        $this->js_yahoo_dir = $a_exp_dir . '/js/yahoo';
        $this->css_dir = $a_exp_dir . '/css';
    }

    /**
     * Set content style id
     *
     * @param int $a_val content style id
     */
    public function setContentStyleId($a_val)
    {
        $this->content_style_id = $a_val;
    }

    /**
     * Get content style id
     *
     * @return int content style id
     */
    public function getContentStyleId()
    {
        return $this->content_style_id;
    }

    /**
     * Create directories
     *
     * @param
     * @return
     */
    public function createDirectories()
    {
        ilUtil::makeDir($this->mobs_dir);
        ilUtil::makeDir($this->files_dir);
        ilUtil::makeDir($this->tex_dir);
        ilUtil::makeDir($this->content_style_dir);
        ilUtil::makeDir($this->content_style_img_dir);
        ilUtil::makeDir($this->services_dir);
        ilUtil::makeDir($this->media_service_dir);
        ilUtil::makeDir($this->flv_dir);
        ilUtil::makeDir($this->mp3_dir);

        ilUtil::makeDir($this->js_dir);
        ilUtil::makeDir($this->js_yahoo_dir);
        ilUtil::makeDir($this->css_dir);
        ilUtil::makeDir($this->css_dir . "/yahoo");
    }

    /**
     * Export content style
     *
     * @throws \ILIAS\Filesystem\Exception\DirectoryNotFoundException
     * @throws \ILIAS\Filesystem\Exception\FileNotFoundException
     * @throws \ILIAS\Filesystem\Exception\IOException
     */
    public function exportStyles()
    {
        $this->log->debug("export styles");

        // export content style sheet
        if ($this->getContentStyleId() < 1) {     // basic style
            ilUtil::rCopy(ilObjStyleSheet::getBasicImageDir(), $this->exp_dir . "/" . ilObjStyleSheet::getBasicImageDir());
            ilUtil::makeDirParents($this->exp_dir . "/Services/COPage/css");
            copy("Services/COPage/css/content.css", $this->exp_dir . "/Services/COPage/css/content.css");
        } else {
            $style = new ilObjStyleSheet($this->getContentStyleId());
            $style->copyImagesToDir($this->exp_dir . "/" . $style->getImagesDirectory());
            $this->exportResourceFile(
                $this->exp_dir,
                ilObjStyleSheet::getContentStylePath($this->getContentStyleId(), false, false)
            );
        }

        // export syntax highlighting style
        $syn_stylesheet = ilObjStyleSheet::getSyntaxStylePath();
        $this->exportResourceFile($this->exp_dir, $syn_stylesheet);
    }

    /**
     * Export support scripts
     *
     * @todo: use ilPageContent js/css functions here (problem: currently they need a page object for init)
     *
     * @param
     * @return
     */
    public function exportSupportScripts()
    {
        $this->log->debug("export scripts");

        $collector = new \ILIAS\COPage\ResourcesCollector(ilPageObjectGUI::OFFLINE);

        foreach ($collector->getJavascriptFiles() as $js) {
            $this->exportResourceFile($this->exp_dir, $js);
        }

        foreach ($collector->getCssFiles() as $css) {
            $this->exportResourceFile($this->exp_dir, $css);
        }

        //copy('./Services/UIComponent/Overlay/js/ilOverlay.js', $this->js_dir . '/ilOverlay.js');


        // yui stuff we use
        /*
        include_once("./Services/YUI/classes/class.ilYuiUtil.php");
        copy(
            ilYuiUtil::getLocalPath('yahoo/yahoo-min.js'),
            $this->js_yahoo_dir . '/yahoo-min.js'
        );
        copy(
            ilYuiUtil::getLocalPath('yahoo-dom-event/yahoo-dom-event.js'),
            $this->js_yahoo_dir . '/yahoo-dom-event.js'
        );
        copy(
            ilYuiUtil::getLocalPath('animation/animation-min.js'),
            $this->js_yahoo_dir . '/animation-min.js'
        );
        copy(
            ilYuiUtil::getLocalPath('container/container-min.js'),
            $this->js_yahoo_dir . '/container-min.js'
        );
        copy(
            ilYuiUtil::getLocalPath('container/assets/skins/sam/container.css'),
            $this->css_dir . '/container.css'
        );
        copy(
            ilYuiUtil::getLocalPath('container/assets/skins/sam/container.css'),
            $this->css_dir . '/yahoo/container.css'
        );*/        // see #23083


        // mediaelement.js
        include_once("./Services/MediaObjects/classes/class.ilPlayerUtil.php");
        ilPlayerUtil::copyPlayerFilesToTargetDirectory($this->flv_dir);
    }

    /**
     * Export resource file
     *
     * @param string $target_dir
     * @param string $file
     */
    protected function exportResourceFile(string $target_dir, string $file)
    {
        if (is_int(strpos($file, "?"))) {
            $file = substr($file, 0, strpos($file, "?"));
        }
        if (is_file($file)) {
            $dir = dirname($file);
            \ilUtil::makeDirParents($target_dir . "/" . $dir);
            if (!is_file($target_dir . "/" . $file)) {
                copy($file, $target_dir . "/" . $file);
            }
        }
    }


    /**
     * Get prepared main template
     *
     * @param
     * @return
     */
    public function getPreparedMainTemplate($a_tpl = "")
    {
        global $DIC;
        $this->log->debug("get main template");


        $resource_collector = new \ILIAS\COPage\ResourcesCollector(ilPageObjectGUI::OFFLINE);
        $resource_injector = new \ILIAS\COPage\ResourcesInjector($resource_collector);


        include_once("./Services/MediaObjects/classes/class.ilPlayerUtil.php");

        if ($a_tpl != "") {
            $tpl = $a_tpl;
        } else {
            // template workaround: reset of template
            $tpl = new ilGlobalPageTemplate($DIC->globalScreen(), $DIC->ui(), $DIC->http());
        }
        // scripts needed
        /*
        $scripts = array("./js/yahoo/yahoo-min.js", "./js/yahoo/yahoo-dom-event.js",
            "./js/yahoo/animation-min.js", "./js/yahoo/container-min.js",
            "./js/ilOverlay.js",
            "./js/ilTooltip.js",);*/
        $scripts = [];
        $scripts = array_merge($scripts, ilPlayerUtil::getJsFilePaths());

        $mathJaxSetting = new ilSetting("MathJax");
        $use_mathjax = $mathJaxSetting->get("enable");
        if ($use_mathjax) {
            $scripts[] = $mathJaxSetting->get("path_to_mathjax");
        }

        $tpl->addCss(\ilUtil::getStyleSheetLocation());
        $tpl->addCss(ilObjStyleSheet::getContentStylePath($this->getContentStyleId()));
        $tpl->addCss(ilObjStyleSheet::getSyntaxStylePath());

        $resource_injector->inject($tpl);

        return $tpl;
    }

    /**
     * Collect page elements (that need to be exported separately)
     *
     * @param string $a_pg_type page type
     * @param int $a_pg_id page id
     */
    public function collectPageElements($a_type, $a_id, $lang = "")
    {
        $this->log->debug("collect page elements");

        // collect all dependent pages (only one level deep)
        $pages[] = [
            "type" => $a_type,
            "id" => $a_id
        ];

        // ... content includes
        $pcs = ilPageContentUsage::getUsagesOfPage($a_id, $a_type, 0, false, $lang);
        foreach ($pcs as $pc) {
            // content includes
            if ($pc["type"] == "incl") {
                $pages[] = [
                    "type" => "mep:pg",
                    "id" => $pc["id"]
                ];
            }
        }

        // ... internal links
        $pg_links = \ilInternalLink::_getTargetsOfSource($a_type, $a_id, $lang);
        $this->int_links = array_merge($this->int_links, $pg_links);
        $this->glossary_terms = [];

        // ... glossary pages of internal links
        foreach ($this->int_links as $int_link) {
            if ($int_link["type"] == "git") {
                $this->glossary_terms[] = $int_link["id"];
                // store linked/embedded media objects of glosssary term
                $defs = \ilGlossaryDefinition::getDefinitionList($int_link["id"]);
                foreach ($defs as $def) {
                    $pages[] = [
                        "type" => "gdf:pg",
                        "id" => $def["obj_id"]
                    ];
                }
            }
        }

        // resources of pages
        foreach ($pages as $page) {
            $page_id = $page["id"];
            $page_type = $page["type"];

            // collect media objects
            $pg_mobs = ilObjMediaObject::_getMobsOfObject($page_type, $page_id, 0, $lang);
            foreach ($pg_mobs as $pg_mob) {
                $this->mobs[$pg_mob] = $pg_mob;
                $this->log->debug("HTML Export: Add media object $pg_mob (" . \ilObject::_lookupTitle($pg_mob) . ") " .
                    " due to page $page_id, $page_type ).");
            }

            // collect all files
            $files = ilObjFile::_getFilesOfObject($page_type, $page_id, 0, $lang);
            foreach ($files as $f) {
                $this->files[$f] = $f;
            }

            // collect all questions
            $q_ids = \ilPCQuestion::_getQuestionIdsForPage($a_type, $a_id, $lang);
            foreach ($q_ids as $q_id) {
                $this->q_ids[$q_id] = $q_id;
            }
        }

        // collect page content items
        $skill_tree = $ws_tree = null;

        // skills
        foreach ($pcs as $pc) {
            if ($pc["type"] == "skmg") {
                $skill_id = $pc["id"];

                // trying to find user id
                $user_id = null;
                switch ($a_type) {
                    case "prtf:pg":
                        include_once "Modules/Portfolio/classes/class.ilPortfolioPage.php";
                        $page = new ilPortfolioPage($a_id);
                        $user_id = $page->create_user;
                        break;

                    default:
                        // :TODO:
                        break;
                }

                if ($user_id) {
                    // we only need 1 instance each
                    if (!$skill_tree) {
                        $skill_tree = new ilSkillTree();

                        $ws_tree = new ilWorkspaceTree($user_id);
                    }

                    // walk skill tree
                    $vtree = new ilVirtualSkillTree();
                    $tref_id = 0;
                    $skill_id = (int) $skill_id;
                    if (ilSkillTreeNode::_lookupType($skill_id) == "sktr") {
                        $tref_id = $skill_id;
                        $skill_id = ilSkillTemplateReference::_lookupTemplateId($skill_id);
                    }
                    $b_skills = $vtree->getSubTreeForCSkillId($skill_id . ":" . $tref_id, true);

                    foreach ($b_skills as $bs) {
                        $skill = ilSkillTreeNodeFactory::getInstance($bs["skill_id"]);
                        $level_data = $skill->getLevelData();
                        foreach ($level_data as $k => $v) {
                            // get assigned materials from personal skill
                            $mat = ilPersonalSkill::getAssignedMaterial($user_id, $bs["tref_id"], $v["id"]);
                            if (sizeof($mat)) {
                                foreach ($mat as $item) {
                                    $wsp_id = $item["wsp_id"];
                                    $obj_id = $ws_tree->lookupObjectId($wsp_id);

                                    // all possible material types for now
                                    switch (ilObject::_lookupType($obj_id)) {
                                        case "file":
                                            $this->files[$obj_id] = $obj_id;
                                            break;

                                        case "tstv":
                                            include_once "Modules/Test/classes/class.ilObjTestVerification.php";
                                            $obj = new ilObjTestVerification($obj_id, false);
                                            $this->files_direct[$obj_id] = array($obj->getFilePath(),
                                                $obj->getOfflineFilename());
                                            break;

                                        case "excv":
                                            include_once "Modules/Exercise/classes/class.ilObjExerciseVerification.php";
                                            $obj = new ilObjExerciseVerification($obj_id, false);
                                            $this->files_direct[$obj_id] = array($obj->getFilePath(),
                                                $obj->getOfflineFilename());
                                            break;

                                        case "crsv":
                                            include_once "Modules/Course/classes/Verification/class.ilObjCourseVerification.php";
                                            $obj = new ilObjCourseVerification($obj_id, false);
                                            $this->files_direct[$obj_id] = array($obj->getFilePath(),
                                                $obj->getOfflineFilename());
                                            break;

                                        case "cmxv":
                                            $obj = new ilObjCmiXapiVerification($obj_id, false);
                                            $this->files_direct[$obj_id] = array($obj->getFilePath(),
                                                                                 $obj->getOfflineFilename());
                                            break;

                                        case "ltiv":
                                            $obj = new ilObjLTIConsumerVerification($obj_id, false);
                                            $this->files_direct[$obj_id] = array($obj->getFilePath(),
                                                                                 $obj->getOfflineFilename());
                                            break;

                                        case "scov":
                                            include_once "Modules/ScormAicc/classes/Verification/class.ilObjSCORMVerification.php";
                                            $obj = new ilObjSCORMVerification($obj_id, false);
                                            $this->files_direct[$obj_id] = array($obj->getFilePath(),
                                                $obj->getOfflineFilename());
                                            break;
                                    }
                                }
                            }
                        }
                    }
                }
            }
        }
    }

    /**
     * Export page elements
     *
     * @param
     * @return
     */
    public function exportPageElements($a_update_callback = null)
    {
        $this->log->debug("export page elements");

        $total = count($this->mobs) + count($this->files) + count($this->files_direct);
        $cnt = 0;

        // export all media objects
        $linked_mobs = array();
        foreach ($this->mobs as $mob) {
            if (ilObject::_exists($mob) && ilObject::_lookupType($mob) == "mob") {
                $this->exportHTMLMOB($mob, $linked_mobs);
            }
            if (is_callable($a_update_callback)) {
                $cnt++;
                $a_update_callback($total, $cnt);
            }
        }
        $linked_mobs2 = array();				// mobs linked in link areas
        foreach ($linked_mobs as $mob) {
            if (ilObject::_exists($mob)) {
                $this->exportHTMLMOB($mob, $linked_mobs2);
            }
        }

        // export all file objects
        foreach ($this->files as $file) {
            $this->exportHTMLFile($file);
            if (is_callable($a_update_callback)) {
                $cnt++;
                $a_update_callback($total, $cnt);
            }
        }

        // export all files (which are not objects
        foreach ($this->files_direct as $file_id => $attr) {
            $this->exportHTMLFileDirect($file_id, $attr[0], $attr[1]);
            if (is_callable($a_update_callback)) {
                $cnt++;
                $a_update_callback($total, $cnt);
            }
        }

        $this->exportQuestionFiles();

        // export all glossary terms
        $this->exportHTMLGlossaryTerms();
    }

    /**
     * Get resource template
     *
     * @param
     * @return
     */
    protected function initResourceTemplate($tempalate_file)
    {
        $this->global_screen->layout()->meta()->reset();
        $tpl = new ilGlobalTemplate($tempalate_file, true, true, "Services/COPage");
        $this->getPreparedMainTemplate($tpl);
        $tpl->addCss(\ilUtil::getStyleSheetLocation());
        $tpl->addCss(ilObjStyleSheet::getContentStylePath($this->getContentStyleId()));
        $tpl->addCss(ilObjStyleSheet::getSyntaxStylePath());
        return $tpl;
    }

    /**
     * Export media object to html
     */
    public function exportHTMLMOB($a_mob_id, &$a_linked_mobs)
    {
        $this->log->debug("export html mobs");

        $source_dir = ilUtil::getWebspaceDir() . "/mobs/mm_" . $a_mob_id;
        if (is_dir($source_dir)) {
            ilUtil::makeDir($this->mobs_dir . "/mm_" . $a_mob_id);
            ilUtil::rCopy($source_dir, $this->mobs_dir . "/mm_" . $a_mob_id);
        }

        $mob_obj = new ilObjMediaObject($a_mob_id);

        $tpl = $this->initResourceTemplate("tpl.fullscreen.html");
        $med_links = ilMediaItem::_getMapAreasIntLinks($a_mob_id);
        $link_xml = $this->page_linker->getLinkXML($med_links);

        $params = [
            "mode" => "media",
            'enlarge_path' => ilUtil::getImagePath("enlarge.svg", false, "output", true),
            'fullscreen_link' => $this->page_linker->getFullScreenLink("fullscreen")
        ];
        if ($this->ref_id > 0) {
            $params["ref_id"] = $this->ref_id;
            $params["link_params"] = "ref_id=" . $this->ref_id;
        }

        $tpl->setVariable("MEDIA_CONTENT", $this->renderMob($mob_obj, $link_xml, $params));
        $html = $tpl->printToString();
        $file = $this->exp_dir . "/media_" . $a_mob_id . ".html";
        $fp = fopen($file, "w+");
        fwrite($fp, $html);
        fclose($fp);
        unset($fp);

        if ($mob_obj->hasFullscreenItem()) {
            $tpl = $this->initResourceTemplate("tpl.fullscreen.html");
            $params["mode"] = "fullscreen";
            $tpl->setVariable("MEDIA_CONTENT", $this->renderMob($mob_obj, $link_xml, $params));
            $html = $tpl->printToString();
            $file = $this->exp_dir . "/fullscreen_" . $a_mob_id . ".html";
            $fp = fopen($file, "w+");
            fwrite($fp, $html);
            fclose($fp);
            unset($fp);
        }

        $linked_mobs = $mob_obj->getLinkedMediaObjects();
        $a_linked_mobs = array_merge($a_linked_mobs, $linked_mobs);
    }

    /**
     * Render Mob
     * @param ilObjMediaObject $mob_obj
     * @param string $link_xml
     * @param array $params
     * @return false|string
     */
    protected function renderMob(\ilObjMediaObject $mob_obj, string $link_xml, array $params)
    {
        // render media object html
        $xh = xslt_create();
        $output = xslt_process(
            $xh,
            "arg:/_xml",
            "arg:/_xsl",
            null,
            array(
                "/_xml" =>
                    "<dummy>" .
                    $mob_obj->getXML(IL_MODE_ALIAS) .
                    $mob_obj->getXML(IL_MODE_OUTPUT) .
                    $link_xml .
                    "</dummy>",
                "/_xsl" => file_get_contents("./Services/COPage/xsl/page.xsl")
            ),
            $params
        );
        xslt_free($xh);
        unset($xh);
        return $output;
    }


    /**
     * Export file object
     */
    public function exportHTMLFile($a_file_id)
    {
        $file_dir = $this->files_dir . "/file_" . $a_file_id;
        ilUtil::makeDir($file_dir);

        include_once("./Modules/File/classes/class.ilObjFile.php");
        $file_obj = new ilObjFile($a_file_id, false);
        $source_file = $file_obj->getFile($file_obj->getVersion());
        if (!is_file($source_file)) {
            $source_file = $file_obj->getFile();
        }
        if (is_file($source_file)) {
            copy($source_file, $file_dir . "/" . $file_obj->getFileName());
        }
    }

    /**
     * Export file from path
     */
    public function exportHTMLFileDirect($a_file_id, $a_source_file, $a_file_name)
    {
        $file_dir = $this->files_dir . "/file_" . $a_file_id;
        ilUtil::makeDir($file_dir);

        if (is_file($a_source_file)) {
            copy(
                $a_source_file,
                $file_dir . "/" . ilUtil::getASCIIFilename($a_file_name)
            );
        }
    }

    /**
     * Export question images
     */
    protected function exportQuestionFiles()
    {
        // export questions (images)
        if (count($this->q_ids) > 0) {
            foreach ($this->q_ids as $q_id) {
                \ilUtil::makeDirParents($this->exp_dir . "/assessment/0/" . $q_id . "/images");
                \ilUtil::rCopy(
                    \ilUtil::getWebspaceDir() . "/assessment/0/" . $q_id . "/images",
                    $this->exp_dir . "/assessment/0/" . $q_id . "/images"
                );
            }
        }
    }

    /**
     * Export glossary terms
     * @throws \ilTemplateException
     */
    public function exportHTMLGlossaryTerms()
    {
        foreach ($this->glossary_terms as $term_id) {
            $tpl = $this->initResourceTemplate("tpl.glossary_term_output.html");

            $term_gui = new ilGlossaryTermGUI($term_id);
            $term_gui->setPageLinker($this->page_linker);
            $term_gui->setOfflineDirectory($this->exp_dir);
            $term_gui->output(true, $tpl);

            // write file
            $html = $tpl->printToString();
            $file = $this->exp_dir . "/term_" . $term_id . ".html";
            $fp = fopen($file, "w+");
            fwrite($fp, $html);
            fclose($fp);
        }
    }
}
