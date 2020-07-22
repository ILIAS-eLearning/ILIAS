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
    protected $mobs = array();

    /**
     * @var array
     */
    protected $files = array();

    /**
     * @var array
     */
    protected $files_direct = array();

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
     * ilCOPageHTMLExport constructor.
     * @param $a_exp_dir
     */
    public function __construct($a_exp_dir)
    {
        global $DIC;

        $this->log = ilLoggerFactory::getLogger('copg');
        $this->user = $DIC->user();

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
     * @param
     * @return
     */
    public function exportStyles()
    {
        $this->log->debug("export styles");

        include_once "Services/Style/Content/classes/class.ilObjStyleSheet.php";
        
        // export content style sheet
        if ($this->getContentStyleId() < 1) {
            $cont_stylesheet = "./Services/COPage/css/content.css";

            $css = fread(fopen($cont_stylesheet, 'r'), filesize($cont_stylesheet));
            preg_match_all("/url\(([^\)]*)\)/", $css, $files);
            foreach (array_unique($files[1]) as $fileref) {
                if (is_file(str_replace("..", ".", $fileref))) {
                    copy(str_replace("..", ".", $fileref), $this->content_style_img_dir . "/" . basename($fileref));
                }
                $css = str_replace($fileref, "images/" . basename($fileref), $css);
            }
            fwrite(fopen($this->content_style_dir . "/content.css", 'w'), $css);
        } else {
            $style = new ilObjStyleSheet($this->getContentStyleId());
            $style->writeCSSFile($this->content_style_dir . "/content.css", "images");
            $style->copyImagesToDir($this->content_style_img_dir);
        }
        
        // export syntax highlighting style
        $syn_stylesheet = ilObjStyleSheet::getSyntaxStylePath();
        copy($syn_stylesheet, $this->exp_dir . "/syntaxhighlight.css");
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

        // basic js
        copy('./Services/JavaScript/js/Basic.js', $this->js_dir . '/Basic.js');
        
        copy('./Services/UIComponent/Overlay/js/ilOverlay.js', $this->js_dir . '/ilOverlay.js');
        
        // jquery
        include_once("./Services/jQuery/classes/class.iljQueryUtil.php");
        copy(iljQueryUtil::getLocaljQueryPath(), $this->js_dir . '/jquery.js');
        copy(iljQueryUtil::getLocaljQueryUIPath(), $this->js_dir . '/jquery-ui-min.js');
        copy(iljQueryUtil::getLocalMaphilightPath(), $this->js_dir . '/maphilight.js');

        // yui stuff we use
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
        );		// see #23083

        // accordion
        include_once("./Services/Accordion/classes/class.ilAccordionGUI.php");
        foreach (ilAccordionGUI::getLocalJavascriptFiles() as $f) {
            $tfile = $this->exp_dir . "/" . $f;
            ilUtil::makeDirParents(dirname($tfile));
            copy($f, $tfile);
        }
        foreach (ilAccordionGUI::getLocalCssFiles() as $f) {
            $tfile = $this->exp_dir . "/" . $f;
            ilUtil::makeDirParents(dirname($tfile));
            copy($f, $tfile);
        }

        copy(
            './Services/Accordion/js/accordion.js',
            $this->js_dir . '/accordion.js'
        );
        copy(
            './Services/Accordion/css/accordion.css',
            $this->css_dir . '/accordion.css'
        );
        
        // page presentation js
        copy(
            './Services/COPage/js/ilCOPagePres.js',
            $this->js_dir . '/ilCOPagePres.js'
        );
        
        // tooltip
        copy(
            './Services/UIComponent/Tooltip/js/ilTooltip.js',
            $this->js_dir . '/ilTooltip.js'
        );
        
        // mediaelement.js
        include_once("./Services/MediaObjects/classes/class.ilPlayerUtil.php");
        ilPlayerUtil::copyPlayerFilesToTargetDirectory($this->flv_dir);

        // matching / multiple choice - question
        copy(
            './Modules/TestQuestionPool/js/ilMatchingQuestion.js',
            $this->js_dir . '/ilMatchingQuestion.js'
        );
        copy(
            './Modules/TestQuestionPool/js/ilAssMultipleChoice.js',
            $this->js_dir . '/ilAssMultipleChoice.js'
        );
        copy(
            './Modules/TestQuestionPool/templates/default/test_javascript.css',
            $this->css_dir . '/test_javascript.css'
        );

        // auto linking js
        include_once("./Services/Link/classes/class.ilLinkifyUtil.php");
        foreach (ilLinkifyUtil::getLocalJsPaths() as $p) {
            if (is_int(strpos($p, "ExtLink"))) {
                copy($p, $this->js_dir . '/ilExtLink.js');
            }
            if (is_int(strpos($p, "linkify"))) {
                copy($p, $this->js_dir . '/linkify.js');
            }
        }


        //		copy(ilPlayerUtil::getLocalMediaElementCssPath(),
//			$this->css_dir.'/mediaelementplayer.css');
//		copy(ilPlayerUtil::getLocalMediaElementJsPath(),
//			$this->js_dir.'/mediaelement-and-player.js');
    }

    /**
     * Get prepared main template
     *
     * @param
     * @return
     */
    public function getPreparedMainTemplate($a_tpl = "")
    {
        $this->log->debug("get main template");
        
        include_once("./Services/MediaObjects/classes/class.ilPlayerUtil.php");
        
        if ($a_tpl != "") {
            $tpl = $a_tpl;
        } else {
            // template workaround: reset of template
            $tpl = new ilTemplate("tpl.main.html", true, true);
        }
        
        // scripts needed
        $scripts = array("./js/yahoo/yahoo-min.js", "./js/yahoo/yahoo-dom-event.js",
            "./js/yahoo/animation-min.js", "./js/yahoo/container-min.js", "./js/jquery.js",
            "./js/Basic.js", "./js/jquery-ui-min.js",
            "./js/ilOverlay.js", "./js/ilCOPagePres.js",
            "./js/ilTooltip.js", "./js/maphilight.js", "./js/ilMatchingQuestion.js", "./js/ilAssMultipleChoice.js",
            "./js/ilExtLink.js", "./js/linkify.js");
        $scripts = array_merge($scripts, ilPlayerUtil::getJsFilePaths());

        $mathJaxSetting = new ilSetting("MathJax");
        $use_mathjax = $mathJaxSetting->get("enable");
        if ($use_mathjax) {
            $scripts[] = $mathJaxSetting->get("path_to_mathjax");
        }

        // accordion
        include_once("./Services/Accordion/classes/class.ilAccordionGUI.php");
        foreach (ilAccordionGUI::getLocalJavascriptFiles() as $f) {
            $scripts[] = $f;
        }

        foreach ($scripts as $script) {
            $tpl->setCurrentBlock("js_file");
            $tpl->setVariable("JS_FILE", $script);
            $tpl->parseCurrentBlock();
        }

        // css files needed
        $style_name = $this->user->prefs["style"] . ".css";
        $css_files = array("./css/container.css",
            "./content_style/content.css", "./style/" . $style_name, "./css/test_javascript.css");
        $css_files = array_merge($css_files, ilPlayerUtil::getCssFilePaths());

        // accordion
        include_once("./Services/Accordion/classes/class.ilAccordionGUI.php");
        foreach (ilAccordionGUI::getLocalCssFiles() as $f) {
            $css_files[] = $f;
        }


        foreach ($css_files as $css) {
            $tpl->setCurrentBlock("css_file");
            $tpl->setVariable("CSS_FILE", $css);
            $tpl->parseCurrentBlock();
        }

        return $tpl;
    }
    
    /**
     * Collect page elements (that need to be exported separately)
     *
     * @param string $a_pg_type page type
     * @param int $a_pg_id page id
     */
    public function collectPageElements($a_type, $a_id)
    {
        $this->log->debug("collect page elements");

        // collect media objects
        $pg_mobs = ilObjMediaObject::_getMobsOfObject($a_type, $a_id);
        foreach ($pg_mobs as $pg_mob) {
            $this->mobs[$pg_mob] = $pg_mob;
        }
        
        // collect all files
        include_once("./Modules/File/classes/class.ilObjFile.php");
        $files = ilObjFile::_getFilesOfObject($a_type, $a_id);
        foreach ($files as $f) {
            $this->files[$f] = $f;
        }

        
        $skill_tree = $ws_tree = null;
        
        $pcs = ilPageContentUsage::getUsagesOfPage($a_id, $a_type);
        foreach ($pcs as $pc) {
            // skils
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
                        include_once "Services/Skill/classes/class.ilSkillTree.php";
                        $skill_tree = new ilSkillTree();

                        include_once "Services/Skill/classes/class.ilPersonalSkill.php";

                        include_once "Services/PersonalWorkspace/classes/class.ilWorkspaceTree.php";
                        $ws_tree = new ilWorkspaceTree($user_id);
                    }

                    // walk skill tree
                    include_once("./Services/Skill/classes/class.ilVirtualSkillTree.php");
                    $vtree = new ilVirtualSkillTree();
                    $tref_id = 0;
                    $skill_id = (int) $skill_id;
                    include_once("./Services/Skill/classes/class.ilSkillTreeNode.php");
                    if (ilSkillTreeNode::_lookupType($skill_id) == "sktr") {
                        include_once("./Services/Skill/classes/class.ilSkillTemplateReference.php");
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

        // #12930 - fullscreen
        include_once("./Services/MediaObjects/classes/class.ilObjMediaObject.php");
        $mob_obj = new ilObjMediaObject($a_mob_id);
        if ($mob_obj->hasFullscreenItem()) {
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
                        "</dummy>",
                    "/_xsl" => file_get_contents("./Services/COPage/xsl/page.xsl")
                ),
                array("mode" => "fullscreen")
            );
            xslt_free($xh);
            unset($xh);
                        
            // render fullscreen html
            $tpl = new ilTemplate("tpl.fullscreen.html", true, true, "Services/COPage");
            $tpl = $this->getPreparedMainTemplate($tpl); // adds js/css
            $tpl->setCurrentBlock("ilMedia");
            $tpl->setVariable("MEDIA_CONTENT", $output);
            $output = $tpl->get();
            unset($tpl);
            
            // write file
            $file = $this->exp_dir . "/fullscreen_" . $a_mob_id . ".html";
            if (!($fp = @fopen($file, "w+"))) {
                die("<b>Error</b>: Could not open \"" . $file . "\" for writing" .
                    " in <b>" . __FILE__ . "</b> on line <b>" . __LINE__ . "</b><br />");
            }
            chmod($file, 0770);
            fwrite($fp, $output);
            fclose($fp);
            unset($fp);
            unset($output);
        }
        
        $linked_mobs = $mob_obj->getLinkedMediaObjects();
        $a_linked_mobs = array_merge($a_linked_mobs, $linked_mobs);
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
        $source_file = $file_obj->getDirectory($file_obj->getVersion()) . "/" . $file_obj->getFileName();
        if (!is_file($source_file)) {
            $source_file = $file_obj->getDirectory() . "/" . $file_obj->getFileName();
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
}
