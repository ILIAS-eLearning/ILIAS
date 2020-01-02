<?php
/* Copyright (c) 1998-2010 ILIAS open source, Extended GPL, see docs/LICENSE */

require_once("./Modules/LearningModule/classes/class.ilLMPageObject.php");
require_once("./Services/MediaObjects/classes/class.ilObjMediaObjectGUI.php");

/**
 * Class ilInternalLinkGUI
 *
 * Some gui methods to handle internal links
 *
 * @author Alex Killing <alex.killing@gmx.de>
 * @version $Id$
 *
 * @ingroup ServicesLink
 */
class ilInternalLinkGUI
{
    /**
     * @var string
     */
    protected $default_link_type;

    /**
     * @var int
     */
    protected $default_parent_ref_id;

    /**
     * @var int
     */
    protected $default_parent_obj_id;

    /**
     * @var int
     */
    protected $parent_ref_id;

    /**
     * @var int
     */
    protected $parent_obj_id;

    protected $link_type;		// "PageObject_New"
    protected $link_target;		// "New"
    protected $base_link_type;	// "PageObject"


    public $set_link_script;

    /**
     * @var array link types
     */
    protected $ltypes = array();

    /**
     * @var array parent object types for link base types
     */
    protected $parent_type = array();

    /**
     * @var ilCtrl
     */
    public $ctrl;

    /**
     * @var bool
     */
    protected $filter_white_list = false;

    /**
     * @var array
     */
    protected $filter_link_types = array();

    /**
     * @var ilTree
     */
    protected $tree;

    /**
     * @var ilLanguage
     */
    protected $lng;

    /**
     * @var ilObjUser
     */
    protected $user;
    
    public function __construct($a_default_link_type, $a_default_parent_id, $a_is_ref = true)
    {
        global $DIC;
        $this->tree = $DIC->repositoryTree();
        $this->lng = $DIC->language();
        $this->ctrl = $DIC->ctrl();
        $this->user = $DIC->user();

        $this->lng->loadLanguageModule("link");
        $this->lng->loadLanguageModule("content");
        //$this->ctrl->saveParameter($this, array("linkmode", "target_type", "link_par_ref_id", "link_par_obj_id",
        //	"link_par_fold_id", "link_type"));
        $this->ctrl->saveParameter($this, array("linkmode", "link_par_ref_id", "link_par_obj_id",
            "link_par_fold_id", "link_type"));

        // default type and parent
        $this->default_link_type = $a_default_link_type;
        if ($a_is_ref) {
            $this->default_parent_ref_id = $a_default_parent_id;
            $this->default_parent_obj_id = ilObject::_lookupObjId($a_default_parent_id);
        } else {
            $this->default_parent_ref_id = 0;
            $this->default_parent_obj_id = $a_default_parent_id;
        }
        $this->default_parent_obj_type = ($this->default_parent_obj_id > 0)
            ? ilObject::_lookupType($this->default_parent_obj_id)
            : "";

        // current parent object
        $this->parent_ref_id = (int) $_GET["link_par_ref_id"];
        $this->parent_fold_id = (int) $_GET["link_par_fold_id"];		// e.g. media pool folder
        if ($this->parent_ref_id > 0) {
            $this->parent_obj_id = ilObject::_lookupObjId($this->parent_ref_id);
        } else {
            $this->parent_obj_id = (int) $_GET["link_par_obj_id"];
        }
    }

    /**
     * Init (first in execute command)
     */
    public function init()
    {
        $lng = $this->lng;
        $tree = $this->tree;
        $ctrl = $this->ctrl;

        if ($this->parent_ref_id > 0 && !$tree->isInTree($this->parent_ref_id)) {
            $this->resetLinkList();
        }

        $this->parent_type = array(
            "StructureObject" => "lm",
            "PageObject" => "lm",
            "GlossaryItem" => "glo",
            "Media" => "mep",
            "WikiPage" => "wiki",
            "PortfolioPage" => "prtf",
            "PortfolioTemplatePage" => "prtt",
            "File" => "",
            "RepositoryItem" => "",
            "User" => ""
        );

        // filter link types
        $this->ltypes = array(
            "StructureObject" => $lng->txt("cont_lk_chapter"),
            "StructureObject_New" => $lng->txt("cont_lk_chapter_new"),
            "PageObject" => $lng->txt("cont_lk_page"),
            "PageObject_FAQ" => $lng->txt("cont_lk_page_faq"),
            "PageObject_New" => $lng->txt("cont_lk_page_new"),
            "GlossaryItem" => $lng->txt("cont_lk_term"),
            "GlossaryItem_New" => $lng->txt("cont_lk_term_new"),
            "Media" => $lng->txt("cont_lk_media_inline"),
            "Media_Media" => $lng->txt("cont_lk_media_media"),
            "Media_FAQ" => $lng->txt("cont_lk_media_faq"),
            "Media_New" => $lng->txt("cont_lk_media_new"),
            "WikiPage" => $lng->txt("cont_wiki_page"),
            "PortfolioPage" => $lng->txt("cont_prtf_page"),
            "PortfolioTemplatePage" => $lng->txt("cont_prtt_page"),
            "File" => $lng->txt("cont_lk_file"),
            "RepositoryItem" => $lng->txt("cont_repository_item"),
            "User" => $lng->txt("cont_user")
            );
        if (!$this->filter_white_list) {
            foreach ($this->filter_link_types as $link_type) {
                unset($this->ltypes[$link_type]);
            }
        } else {
            $ltypes = array();
            foreach ($this->ltypes as $k => $l) {
                if (in_array($k, $this->filter_link_types)) {
                    $ltypes[$k] = $l;
                }
            }
            $this->ltypes = $ltypes;
        }
        // determine link type and target
        $this->link_type = ($_GET["link_type"] == "")
            ? $this->default_link_type
            : $_GET["link_type"];
        $ltype_arr = explode("_", $this->link_type);
        $this->base_link_type = $ltype_arr[0];
        $this->link_target = $ltype_arr[1];


        $def_type = ilObject::_lookupType($this->default_parent_obj_id);

        // determine content object id
        switch ($this->base_link_type) {
            case "PageObject":
            case "StructureObject":
            case "GlossaryItem":
            case "Media":
            case "WikiPage":
            case "PortfolioPage":
            case "PortfolioTemplatePage":
                if ($this->parent_ref_id == 0 && $this->parent_obj_id == 0
                    && $def_type == $this->parent_type[$this->base_link_type]) {
                    $this->parent_ref_id = $this->default_parent_ref_id;
                    $this->parent_obj_id = $this->default_parent_obj_id;
                    $ctrl->setParameter($this, "link_par_obj_id", $this->parent_obj_id);
                    $ctrl->setParameter($this, "link_par_ref_id", $this->parent_ref_id);
                }
                break;
        }
    }
    

    /**
     * Set mode
     * @deprecated
     */
    public function setMode($a_mode = "text")
    {
    }

    public function setSetLinkTargetScript($a_script)
    {
        $this->set_link_script = $a_script;
    }
    
    public function setReturn($a_return)
    {
        $this->return = $a_return;
    }

    public function getSetLinkTargetScript()
    {
        return $this->set_link_script;
    }

    public function filterLinkType($a_link_type)
    {
        $this->filter_link_types[] = $a_link_type;
    }

    /**
     * Set filter list as white list (per detault it is a black list)
     *
     * @return boolean white list
     */
    public function setFilterWhiteList($a_white_list)
    {
        $this->filter_white_list = $a_white_list;
    }


    public function executeCommand()
    {
        $this->init();
        $next_class = $this->ctrl->getNextClass($this);

        $cmd = $this->ctrl->getCmd("showLinkHelp");
        switch ($next_class) {
            default:
                $ret = $this->$cmd();
                break;
        }

        return $ret;
    }

    public function resetLinkList()
    {
        $ctrl = $this->ctrl;

        $ctrl->setParameter($this, "link_par_ref_id", "");
        $ctrl->setParameter($this, "link_par_obj_id", "");
        $ctrl->setParameter($this, "link_par_fold_id", "");
        $ctrl->setParameter($this, "link_type", "");

        $ctrl->redirect($this, "showLinkHelp", "", true);
    }

    public function closeLinkHelp()
    {
        if ($this->return == "") {
            $this->ctrl->returnToParent($this);
        } else {
            ilUtil::redirect($this->return);
        }
    }

    /**
     * Prepare output for JS enabled editing
     */
    public function prepareJavascriptOutput($str)
    {
        return htmlspecialchars($str, ENT_QUOTES);
    }
    
    
    /**
    * Show link help list
    */
    public function showLinkHelp()
    {
        $ilUser = $this->user;
        $ilCtrl = $this->ctrl;


        $parent_type = $this->parent_type[$this->base_link_type];
        if ((in_array($this->base_link_type, array("GlossaryItem", "WikiPage", "PageObject", "StructureObject")) &&
            ($this->parent_ref_id == 0))
            ||
            (($this->parent_ref_id > 0) &&
            !in_array(ilObject::_lookupType($this->parent_ref_id, true), array($parent_type)))) {
            if ($parent_type != "") {
                $this->changeTargetObject($parent_type);
            }
        }
        if ($ilCtrl->isAsynch()) {
            $tpl = new ilTemplate("tpl.link_help_asynch.html", true, true, "Services/Link");
            $tpl->setVariable("NEW_LINK_URL", $this->ctrl->getLinkTarget(
                $this,
                "",
                false,
                true,
                false
            ));
        } else {
            $tpl = new ilTemplate("tpl.link_help.html", true, true, "Services/Link");
            $tpl->setVariable("LOCATION_STYLESHEET", ilUtil::getStyleSheetLocation());
        }

        $tpl->setVariable("FORMACTION", $this->ctrl->getFormAction($this, "changeLinkType", "", true));
        $tpl->setVariable("FORMACTION2", $this->ctrl->getFormAction($this));
        $tpl->setVariable("TXT_HELP_HEADER", $this->lng->txt("cont_link_select"));
        $tpl->setVariable("TXT_TYPE", $this->lng->txt("cont_link_type"));


        $select_ltype = ilUtil::formSelect(
            $this->link_type,
            "ltype",
            $this->ltypes,
            false,
            true,
            "0",
            "",
            array("id" => "ilIntLinkTypeSelector")
        );
        $tpl->setVariable("SELECT_TYPE", $select_ltype);
        $tpl->setVariable("CMD_CHANGETYPE", "changeLinkType");
        $tpl->setVariable("BTN_CHANGETYPE", $this->lng->txt("cont_change_type"));
        
        $tpl->setVariable("CMD_CLOSE", "closeLinkHelp");
        $tpl->setVariable("BTN_CLOSE", $this->lng->txt("close"));

        $chapterRowBlock = "chapter_row_js";

        // switch link type
        switch ($this->base_link_type) {
            // page link
            case "PageObject":
                require_once("./Modules/LearningModule/classes/class.ilObjLearningModule.php");
                include_once("./Modules/LearningModule/classes/class.ilLMPage.php");
                
                $cont_obj = new ilObjLearningModule($this->parent_ref_id, true);

                // get all chapters
                $ctree = $cont_obj->getLMTree();
                $nodes = $ctree->getSubtree($ctree->getNodeData($ctree->getRootId()));
                $tpl->setCurrentBlock("chapter_list");
                $tpl->setVariable("TXT_CONTENT_OBJECT", $this->lng->txt("obj_lm"));
                $tpl->setVariable("TXT_CONT_TITLE", $cont_obj->getTitle());
                $tpl->setVariable("THEAD", $this->lng->txt("pages"));


                $tpl->setCurrentBlock("change_cont_obj");
                $tpl->setVariable("CMD_CHANGE_CONT_OBJ", "changeTargetObject");
                $tpl->setVariable("BTN_CHANGE_CONT_OBJ", $this->lng->txt("change"));
                $tpl->parseCurrentBlock();

                foreach ($nodes as $node) {
                    if ($node["type"] == "st") {
                        $tpl->setCurrentBlock("header_row");
                        $tpl->setVariable("TXT_HEADER", $node["title"]);
                        $tpl->parseCurrentBlock();
                        $tpl->setCurrentBlock("row");
                        $tpl->parseCurrentBlock();
                    }

                    if ($node["type"] == "pg") {
                        include_once("./Services/COPage/classes/class.ilPCParagraph.php");
                        $this->renderLink(
                            $tpl,
                            $node["title"],
                            $node["obj_id"],
                            "PageObject",
                            "pg",
                            "page",
                            ilPCParagraph::_readAnchors("lm", $node["obj_id"], "")
                        );
                    }
                }

                // get all free pages
                $pages = ilLMPageObject::getPageList($cont_obj->getId());
                $free_pages = array();
                foreach ($pages as $page) {
                    if (!$ctree->isInTree($page["obj_id"])) {
                        $free_pages[] = $page;
                    }
                }
                if (count($free_pages) > 0) {
                    $tpl->setCurrentBlock("header_row");
                    $tpl->setVariable("TXT_HEADER", $this->lng->txt("cont_free_pages"));
                    $tpl->parseCurrentBlock();

                    foreach ($free_pages as $node) {
                        include_once("./Services/COPage/classes/class.ilPCParagraph.php");
                        $this->renderLink(
                            $tpl,
                            $node["title"],
                            $node["obj_id"],
                            "PageObject",
                            "pg",
                            "page",
                            ilPCParagraph::_readAnchors("lm", $node["obj_id"], "")
                        );
                    }
                }

                $tpl->setCurrentBlock("chapter_list");
                $tpl->parseCurrentBlock();

                break;

            // chapter link
            case "StructureObject":
            
                // check whether current object matchs to type
                if (!in_array(
                    ilObject::_lookupType($this->parent_ref_id, true),
                    array("lm")
                )) {
                    $this->changeTargetObject("lm");
                }

                require_once("./Modules/LearningModule/classes/class.ilObjLearningModule.php");
                $cont_obj = new ilObjLearningModule($this->parent_ref_id, true);

                // get all chapters
                $ctree =&$cont_obj->getLMTree();
                $nodes = $ctree->getSubtree($ctree->getNodeData($ctree->getRootId()));
                $tpl->setCurrentBlock("chapter_list");
                $tpl->setVariable("TXT_CONTENT_OBJECT", $this->lng->txt("obj_lm"));
                $tpl->setVariable("TXT_CONT_TITLE", $cont_obj->getTitle());
                $tpl->setVariable("THEAD", $this->lng->txt("link_chapters"));
                $tpl->setCurrentBlock("change_cont_obj");
                $tpl->setVariable("CMD_CHANGE_CONT_OBJ", "changeTargetObject");
                $tpl->setVariable("BTN_CHANGE_CONT_OBJ", $this->lng->txt("change"));
                $tpl->parseCurrentBlock();

                foreach ($nodes as $node) {
                    if ($node["type"] == "st") {
                        $this->renderLink(
                            $tpl,
                            $node["title"],
                            $node["obj_id"],
                            "StructureObject",
                            "st",
                            "chap"
                        );
                    }
                }
                $tpl->setCurrentBlock("chapter_list");
                $tpl->parseCurrentBlock();
                break;

            // glossary item link
            case "GlossaryItem":
                require_once("./Modules/Glossary/classes/class.ilObjGlossary.php");
                $glossary = new ilObjGlossary($this->parent_ref_id, true);

                // get all glossary items
                $terms = $glossary->getTermList();
                $tpl->setCurrentBlock("chapter_list");
                $tpl->setVariable("TXT_CONTENT_OBJECT", $this->lng->txt("glossary"));
                $tpl->setVariable("TXT_CONT_TITLE", $glossary->getTitle());
                $tpl->setVariable("THEAD", $this->lng->txt("link_terms"));
                $tpl->setCurrentBlock("change_cont_obj");
                $tpl->setVariable("CMD_CHANGE_CONT_OBJ", "changeTargetObject");
                $tpl->setVariable("BTN_CHANGE_CONT_OBJ", $this->lng->txt("change"));
                $tpl->parseCurrentBlock();

                foreach ($terms as $term) {
                    $this->renderLink(
                        $tpl,
                        $term["term"],
                        $term["id"],
                        "GlossaryItem",
                        "git",
                        "term"
                    );
                }
                
                $tpl->setCurrentBlock("chapter_list");
                $tpl->parseCurrentBlock();
                break;

            // media object
            case "Media":
                include_once("./Modules/MediaPool/classes/class.ilMediaPoolItem.php");
                //$tpl->setVariable("TARGET2", " target=\"content\" ");
                // content object id = 0 --> get clipboard objects
                if ($this->parent_ref_id == 0) {
                    $tpl->setCurrentBlock("change_cont_obj");
                    $tpl->setVariable("CMD_CHANGE_CONT_OBJ", "changeTargetObject");
                    $tpl->setVariable("BTN_CHANGE_CONT_OBJ", $this->lng->txt("change"));
                    $tpl->parseCurrentBlock();
                    $mobjs = $ilUser->getClipboardObjects("mob");
                    // sort by name
                    $objs = array();
                    foreach ($mobjs as $obj) {
                        $objs[$obj["title"] . ":" . $obj["id"]] = $obj;
                    }
                    ksort($objs);
                    $tpl->setCurrentBlock("chapter_list");
                    $tpl->setVariable("TXT_CONTENT_OBJECT", $this->lng->txt("cont_media_source"));
                    $tpl->setVariable("TXT_CONT_TITLE", $this->lng->txt("cont_personal_clipboard"));
                    $tpl->setVariable("THEAD", $this->lng->txt("link_mobs"));
                    $tpl->setVariable("COLSPAN", "2");

                    foreach ($objs as $obj) {
                        $this->renderLink(
                            $tpl,
                            $obj["title"],
                            $obj["id"],
                            "MediaObject",
                            "mob",
                            "media"
                        );
                    }
                    $tpl->setCurrentBlock("chapter_list");
                    $tpl->parseCurrentBlock();
                } else {
                    require_once("./Modules/MediaPool/classes/class.ilObjMediaPool.php");
                    $med_pool = new ilObjMediaPool($this->parent_ref_id, true);
                    // get current folders
                    $fobjs = $med_pool->getChilds($this->parent_fold_id, "fold");
                    $f2objs = array();
                    foreach ($fobjs as $obj) {
                        $f2objs[$obj["title"] . ":" . $obj["child"]] = $obj;
                    }
                    ksort($f2objs);
                    // get current media objects
                    $mobjs = $med_pool->getChilds($this->parent_fold_id, "mob");
                    $m2objs = array();
                    foreach ($mobjs as $obj) {
                        $m2objs[$obj["title"] . ":" . $obj["child"]] = $obj;
                    }
                    ksort($m2objs);
                    
                    // merge everything together
                    $objs = array_merge($f2objs, $m2objs);
                
                    $tpl->setCurrentBlock("chapter_list");
                    $tpl->setVariable("TXT_CONTENT_OBJECT", $this->lng->txt("mep"));
                    $tpl->setVariable("TXT_CONT_TITLE", $med_pool->getTitle());
                    $tpl->setVariable("THEAD", $this->lng->txt("link_mobs"));
                    $tpl->setCurrentBlock("change_cont_obj");
                    $tpl->setVariable("CMD_CHANGE_CONT_OBJ", "changeTargetObject");
                    $tpl->setVariable("BTN_CHANGE_CONT_OBJ", $this->lng->txt("change"));
                    $tpl->setVariable("COLSPAN", "2");
                    $tpl->parseCurrentBlock();
                    if ($parent_id = $med_pool->getParentId($this->parent_fold_id)) {
                        $css_row = "tblrow1";
                        $tpl->setCurrentBlock("icon");
                        $tpl->setVariable("ICON_SRC", ilUtil::getImagePath("icon_fold.svg"));
                        $tpl->parseCurrentBlock();
                        $tpl->setCurrentBlock("link_row");
                        $tpl->setVariable("ROWCLASS", $css_row);
                        $tpl->setVariable("TXT_CHAPTER", "..");
                        $this->ctrl->setParameter($this, "mep_fold", $parent_id);
                        if ($ilCtrl->isAsynch()) {
                            $tpl->setVariable("LINK", "#");
                            $tpl->setVariable(
                                "LR_ONCLICK",
                                " onclick=\"return il.IntLink.setMepPoolFolder('" . $parent_id . "');\" "
                            );
                        } else {
                            $tpl->setVariable(
                                "LINK",
                                $this->ctrl->getLinkTarget($this, "setMedPoolFolder")
                            );
                        }
                        $tpl->parseCurrentBlock();
                        $tpl->setCurrentBlock("row");
                        $tpl->parseCurrentBlock();
                    }
                    foreach ($objs as $obj) {
                        if ($obj["type"] == "fold") {
                            $css_row = ($css_row == "tblrow2")
                                ? "tblrow1"
                                : "tblrow2";
                            $tpl->setCurrentBlock("icon");
                            $tpl->setVariable("ICON_SRC", ilUtil::getImagePath("icon_fold.svg"));
                            $tpl->parseCurrentBlock();
                            $tpl->setCurrentBlock("link_row");
                            $tpl->setVariable("ROWCLASS", $css_row);
                            $tpl->setVariable("TXT_CHAPTER", $obj["title"]);
                            $this->ctrl->setParameter($this, "mep_fold", $obj["child"]);
                            if ($ilCtrl->isAsynch()) {
                                $tpl->setVariable("LINK", "#");
                                $tpl->setVariable(
                                    "LR_ONCLICK",
                                    " onclick=\"return il.IntLink.setMepPoolFolder('" . $obj["child"] . "');\" "
                                );
                            } else {
                                $tpl->setVariable(
                                    "LINK",
                                    $this->ctrl->getLinkTarget($this, "setMedPoolFolder")
                                );
                            }
                            $tpl->parseCurrentBlock();
                        } else {
                            $fid = ilMediaPoolItem::lookupForeignId($obj["child"]);
                            if (ilObject::_lookupType($fid) == "mob") {
                                $this->renderLink(
                                    $tpl,
                                    $obj["title"],
                                    $fid,
                                    "MediaObject",
                                    "mob",
                                    "media"
                                );
                            }
                        }
                        $tpl->setCurrentBlock("row");
                        $tpl->parseCurrentBlock();
                    }
                    $tpl->setCurrentBlock("chapter_list");
                    $tpl->parseCurrentBlock();
                }
                break;

            // wiki page link
            case "WikiPage":
                $wiki_id = ilObject::_lookupObjId($this->parent_ref_id);
                require_once("./Modules/Wiki/classes/class.ilWikiPage.php");
                $wpages = ilWikiPage::getAllWikiPages($wiki_id);

                // get all glossary items
                $tpl->setCurrentBlock("chapter_list");
                $tpl->setVariable("TXT_CONTENT_OBJECT", $this->lng->txt("obj_wiki"));
                $tpl->setVariable("TXT_CONT_TITLE", ilObject::_lookupTitle($wiki_id));
                $tpl->setVariable("THEAD", $this->lng->txt("link_wpages"));
                $tpl->setCurrentBlock("change_cont_obj");
                $tpl->setVariable("CMD_CHANGE_CONT_OBJ", "changeTargetObject");
                $tpl->setVariable("BTN_CHANGE_CONT_OBJ", $this->lng->txt("change"));
                $tpl->parseCurrentBlock();

                foreach ($wpages as $wpage) {
                    $this->renderLink(
                        $tpl,
                        $wpage["title"],
                        $wpage["id"],
                        "WikiPage",
                        "wpage",
                        "wpage"
                    );
                }
                
                $tpl->setCurrentBlock("chapter_list");
                $tpl->parseCurrentBlock();
                break;

            // Portfolio page link
            case "PortfolioPage":
            case "PortfolioTemplatePage":
                $prtf_id = $this->parent_obj_id;
                require_once("./Modules/Portfolio/classes/class.ilPortfolioPage.php");
                $ppages = ilPortfolioPage::getAllPortfolioPages($prtf_id);

                // get all glossary items
                $tpl->setCurrentBlock("chapter_list");
                $tpl->setVariable("TXT_CONTENT_OBJECT", $this->lng->txt("obj_" . ilObject::_lookupType($prtf_id)));
                $tpl->setVariable("TXT_CONT_TITLE", ilObject::_lookupTitle($prtf_id));
                $tpl->setVariable("THEAD", $this->lng->txt("pages"));

                foreach ($ppages as $ppage) {
                    $this->renderLink(
                        $tpl,
                        $ppage["title"],
                        $ppage["id"],
                        "PortfolioPage",
                        "ppage",
                        "ppage",
                        array(),
                        $ppage["title"]
                    );
                }

                $tpl->setCurrentBlock("chapter_list");
                $tpl->parseCurrentBlock();
                break;

            // repository item
            case "RepositoryItem":
                $tpl->setVariable("LINK_HELP_CONTENT", $this->selectRepositoryItem());
                break;

            // file download link
            case "File":
                if (!is_object($this->uploaded_file)) {
                    $tpl->setVariable("LINK_HELP_CONTENT", $this->getFileLinkHTML());
                } else {
                    echo $this->getFileLinkHTML();
                    exit;
                }
                break;

            // file download link
            case "User":
                $tpl->setVariable("LINK_HELP_CONTENT", $this->addUser());
                break;

        }

        if ($ilCtrl->isAsynch()) {
            echo $tpl->get();
            exit;
        }
        
        exit;
    }
    
    /**
     * Get HTML for file link
     * @return	string		file link html
     */
    public function getFileLinkHTML()
    {
        $lng = $this->lng;
        $ilCtrl = $this->ctrl;

        if (!is_object($this->uploaded_file)) {
            $tpl = new ilTemplate("tpl.link_file.html", true, true, "Services/Link");
            $tpl->setCurrentBlock("form");
            $tpl->setVariable(
                "FORM_ACTION",
                $ilCtrl->getFormAction($this, "saveFileLink", "", true)
            );
            $tpl->setVariable("TXT_SELECT_FILE", $lng->txt("cont_select_file"));
            $tpl->setVariable("TXT_SAVE_LINK", $lng->txt("cont_create_link"));
            $tpl->setVariable("CMD_SAVE_LINK", "saveFileLink");
            include_once("./Services/Form/classes/class.ilFileInputGUI.php");
            $fi = new ilFileInputGUI("", "link_file");
            $fi->setSize(15);
            $tpl->setVariable("INPUT", $fi->getToolbarHTML());
            $tpl->parseCurrentBlock();
            return $tpl->get();
        } else {
            $tpl = new ilTemplate("tpl.link_file.html", true, true, "Services/Link");
            $tpl->setCurrentBlock("link_js");
            //			$tpl->setVariable("LINK_FILE",
            //				$this->prepareJavascriptOutput("[iln dfile=\"".$this->uploaded_file->getId()."\"] [/iln]")
            //				);
            $tpl->setVariable(
                "TAG_B",
                '[iln dfile=\x22' . $this->uploaded_file->getId() . '\x22]'
            );
            $tpl->setVariable(
                "TAG_E",
                "[/iln]"
            );
            $tpl->setVariable(
                "TXT_FILE",
                $this->uploaded_file->getTitle()
            );
            //			$tpl->parseCurrentBlock();
            return $tpl->get();
        }
    }
    
    /**
     * Save file link
     */
    public function saveFileLink()
    {
        if ($_FILES["link_file"]["name"] != "") {
            include_once("./Modules/File/classes/class.ilObjFile.php");
            $fileObj = new ilObjFile();
            $fileObj->setType("file");
            $fileObj->setTitle($_FILES["link_file"]["name"]);
            $fileObj->setDescription("");
            $fileObj->setFileName($_FILES["link_file"]["name"]);
            $fileObj->setFileType($_FILES["link_file"]["type"]);
            $fileObj->setFileSize($_FILES["link_file"]["size"]);
            $fileObj->setMode("filelist");
            $fileObj->create();
            // upload file to filesystem
            $fileObj->createDirectory();
            $fileObj->raiseUploadError(false);
            $fileObj->getUploadFile(
                $_FILES["link_file"]["tmp_name"],
                $_FILES["link_file"]["name"]
            );
            $this->uploaded_file = $fileObj;
        }
        $this->showLinkHelp();
    }
    
    /**
     * output thumbnail
     */
    public function outputThumbnail(&$tpl, $a_id, $a_mode = "")
    {
        // output thumbnail
        $mob = new ilObjMediaObject($a_id);
        $med =&$mob->getMediaItem("Standard");
        $target = $med->getThumbnailTarget("small");
        $suff = "";
        if ($this->getSetLinkTargetScript() != "") {
            $tpl->setCurrentBlock("thumbnail_link");
            $suff = "_link";
        } else {
            $tpl->setCurrentBlock("thumbnail_js");
            $suff = "_js";
        }

        if ($target != "") {
            $tpl->setCurrentBlock("thumb" . $suff);
            $tpl->setVariable("SRC_THUMB", $target);
            $tpl->parseCurrentBlock();
        } else {
            $tpl->setVariable("NO_THUMB", "&nbsp;");
        }
        
        if ($this->getSetLinkTargetScript() != "") {
            $tpl->setCurrentBlock("thumbnail_link");
        } else {
            $tpl->setCurrentBlock("thumbnail_js");
        }
        $tpl->parseCurrentBlock();
    }


    /**
    * change link type
    */
    public function changeLinkType()
    {
        $ctrl = $this->ctrl;

        $ctrl->setParameter($this, "link_type", $_GET["link_type"]);
        $base_type = explode("_", $_GET["link_type"])[0];
        if ($this->parent_type[$base_type] != ilObject::_lookupType($this->parent_ref_id, true)) {
            $ctrl->setParameter($this, "link_par_ref_id", 0);
            $ctrl->setParameter($this, "link_par_obj_id", 0);
        }

        $ctrl->redirect($this, "showLinkHelp", "", true);
    }

    /**
    * select media pool folder
    */
    public function setMedPoolFolder()
    {
        $ctrl = $this->ctrl;
        $ctrl->setParameter($this, "link_par_fold_id", $_GET["mep_fold"]);
        $ctrl->redirect($this, "showLinkHelp", "", true);
    }

    /**
     * Cange target object
     */
    public function getTargetExplorer()
    {
        //$ilCtrl->setParameter($this, "target_type", $a_type);
        include_once("./Services/Link/classes/class.ilLinkTargetObjectExplorerGUI.php");
        $exp = new ilLinkTargetObjectExplorerGUI($this, "getTargetExplorer", $this->link_type);

        $a_type = $this->parent_type[$this->base_link_type];

        $white = array("root", "cat", "crs", "fold", "grp");

        $white[] = $a_type;
        $exp->setClickableType($a_type);
        if ($a_type == "prtf") {
            $white[] = "prtt";
            $exp->setClickableType("prtt");
        }

        $exp->setTypeWhiteList($white);


        if (!$exp->handleCommand()) {
            return $exp->getHTML();
        }
    }

    /**
     * Cange target object
     */
    public function changeTargetObject($a_type = "")
    {
        $ilCtrl = $this->ctrl;

        $ilCtrl->setParameter($this, "link_par_fold_id", "");
        if ($_GET["do"] == "set") {
            $ilCtrl->setParameter($this, "link_par_ref_id", $_GET["sel_id"]);
            $ilCtrl->redirect($this, "showLinkHelp", "", true);
            return;
        }

        $ilCtrl->setParameter($this, "link_type", $this->link_type);

        $tpl = new ilTemplate("tpl.link_help_explorer.html", true, true, "Services/Link");

        $output = $this->getTargetExplorer();

        $tpl->setVariable("TXT_EXPLORER_HEADER", $this->lng->txt("cont_choose_" . $this->parent_type[$this->base_link_type]));

        $tpl->setVariable("EXPLORER", $output);
        $tpl->setVariable("ACTION", $this->ctrl->getFormAction($this, "resetLinkList", "", true));
        $tpl->setVariable("BTN_RESET", "resetLinkList");
        $tpl->setVariable("TXT_RESET", $this->lng->txt("back"));

        if ($this->parent_type[$this->base_link_type] == "mep") {
            $tpl->setCurrentBlock("sel_clipboard");
            $this->ctrl->setParameter($this, "do", "set");
            if ($ilCtrl->isAsynch()) {
                $tpl->setVariable("LINK_CLIPBOARD", "#");
                $tpl->setVariable(
                    "CLIPBOARD_ONCLICK",
                    " onclick=\"return il.IntLink.selectLinkTargetObject('mep', 0, '" . $this->link_type . "');\" "
                );
            } else {
                $tpl->setVariable("LINK_CLIPBOARD", $this->ctrl->getLinkTarget($this, "changeTargetObject"));
            }
            $tpl->setVariable("TXT_PERS_CLIPBOARD", $this->lng->txt("clipboard"));
            $tpl->parseCurrentBlock();
        }

        $tpl->parseCurrentBlock();

        echo $tpl->get();
        exit;
    }

    
    /**
    * select repository item explorer
    */
    public function selectRepositoryItem()
    {
        $ilCtrl = $this->ctrl;

        $ilCtrl->setParameter($this, "link_par_fold_id", "");

        //$ilCtrl->setParameter($this, "target_type", $a_type);
        include_once("./Services/Link/classes/class.ilIntLinkRepItemExplorerGUI.php");
        $exp = new ilIntLinkRepItemExplorerGUI($this, "selectRepositoryItem");
        $exp->setSetLinkTargetScript($this->getSetLinkTargetScript());

        if (!$exp->handleCommand()) {
            return $exp->getHTML();
        }
    }

    /**
     * Refresh Repository Selector
     */
    public function refreshRepositorySelector()
    {
        $output = $this->selectRepositoryItem();
        echo $output;
        exit;
    }


    /**
     * Get initialisation HTML to use interna link editing
     */
    public static function getInitHTML($a_url)
    {
        global $DIC;

        $lng = $DIC->language();
        $tpl = $DIC["tpl"];

        $lng->loadLanguageModule("link");

        $tpl->addJavaScript("./Services/UIComponent/Explorer/js/ilExplorer.js");
        include_once("./Services/UIComponent/Explorer2/classes/class.ilExplorerBaseGUI.php");
        ilExplorerBaseGUI::init();

        include_once("./Services/YUI/classes/class.ilYuiUtil.php");
        ilYuiUtil::initConnection();

        $tpl->addJavascript("./Services/Link/js/ilIntLink.js");
        
        // #18721
        $tpl->addJavaScript("Services/Form/js/Form.js");

        include_once("./Services/UIComponent/Modal/classes/class.ilModalGUI.php");
        $modal = ilModalGUI::getInstance();
        $modal->setHeading($lng->txt("link_link"));
        $modal->setId("ilIntLinkModal");
        $modal->setBody("<div id='ilIntLinkModalContent'></div>");

        $ltpl = new ilTemplate("tpl.int_link_panel.html", true, true, "Services/Link");
        $ltpl->setVariable("MODAL", $modal->getHTML());

        $ltpl->setVariable("IL_INT_LINK_URL", $a_url);

        return $ltpl->get();
    }

    /**
     * Render internal link item
     */
    public function renderLink(
        $tpl,
        $a_title,
        $a_obj_id,
        $a_type,
        $a_type_short,
        $a_bb_type,
        $a_anchors = array(),
        $a_link_content = ""
    ) {
        $chapterRowBlock = "chapter_row_js";
        $anchor_row_block = "anchor_link_js";

        $target_str = ($this->link_target == "")
            ? ""
            : " target=\"" . $this->link_target . "\"";

        if (count($a_anchors) > 0) {
            foreach ($a_anchors as $anchor) {
                if ($this->getSetLinkTargetScript() != "") {
                    // not implemented yet (anchors that work with map areas)

                    /*$tpl->setCurrentBlock("anchor_link");
                    $tpl->setVariable("ALINK",
                        ilUtil::appendUrlParameterString($this->getSetLinkTargetScript(),
                            "linktype=".$a_type.
                            "&linktarget=il__".$a_type_short."_".$a_obj_id.
                            "&linktargetframe=".$this->link_target).
                            "&linkanchor=".$anchor);
                    $tpl->setVariable("TXT_ALINK", "#" . $anchor);
                    $tpl->parseCurrentBlock();*/
                } else {
                    $tpl->setCurrentBlock($anchor_row_block);
                    $tpl->setVariable(
                        "ALINK_BEGIN",
                        $this->prepareJavascriptOutput("[iln " . $a_bb_type . "=\"" . $a_obj_id . "\"" . $target_str . " anchor=\"$anchor\"]")
                    );
                    $tpl->setVariable("ALINK_END", "[/iln]");
                    $tpl->setVariable("TXT_LINK", "#" . $anchor);
                    $tpl->parseCurrentBlock();
                }
            }
        }

        $this->css_row = ($this->css_row == "tblrow1")
            ? "tblrow2"
            : "tblrow1";

        if ($this->getSetLinkTargetScript() != "") {
            require_once("./Services/MediaObjects/classes/class.ilObjMediaObjectGUI.php");
            require_once("./Services/MediaObjects/classes/class.ilImageMapEditorGUI.php");
            ilImageMapEditorGUI::_recoverParameters();
            if ($a_type == "MediaObject") {
                $this->outputThumbnail($tpl, $a_obj_id);
            }
            $tpl->setCurrentBlock("link_row");
            $tpl->setVariable("ROWCLASS", $this->css_row);
            $tpl->setVariable("TXT_CHAPTER", $a_title);
            $tpl->setVariable(
                "LINK",
                ilUtil::appendUrlParameterString(
                    $this->getSetLinkTargetScript(),
                    "linktype=" . $a_type .
                "&linktarget=il__" . $a_type_short . "_" . $a_obj_id .
                "&linktargetframe=" . $this->link_target
                )
            );
            $tpl->parseCurrentBlock();
        } else {
            $tpl->setCurrentBlock($chapterRowBlock);
            if ($a_type == "MediaObject") {
                $this->outputThumbnail($tpl, $a_obj_id);
                $tpl->setCurrentBlock($chapterRowBlock);
            }
            $tpl->setVariable("ROWCLASS", $this->css_row);
            $tpl->setVariable("TXT_CHAPTER", $a_title);
            if ($a_type == "MediaObject" && empty($target_str)) {
                $tpl->setVariable(
                    "LINK_BEGIN",
                    $this->prepareJavascriptOutput("[iln " . $a_bb_type . "=\"" . $a_obj_id . "\"/]")
                );
                $tpl->setVariable("LINK_END", "");
            } else {
                $tpl->setVariable(
                    "LINK_BEGIN",
                    $this->prepareJavascriptOutput("[iln " . $a_bb_type . "=\"" . $a_obj_id . "\"" . $target_str . "]")
                );
                $tpl->setVariable("LINK_CONTENT", $a_link_content);
                $tpl->setVariable("LINK_END", "[/iln]");
            }
            $tpl->parseCurrentBlock();
        }

        $tpl->setCurrentBlock("row");
        $tpl->parseCurrentBlock();
    }

    /**
     * Add user
     *
     * @param
     * @return
     */
    public function addUser()
    {
        $form = $this->initUserSearchForm();
        return $form->getHTML() . $this->getUserSearchResult();
    }

    /**
     * Init user search form.
     */
    public function initUserSearchForm()
    {
        include_once("Services/Form/classes/class.ilPropertyFormGUI.php");
        $form = new ilPropertyFormGUI();
        $form->setId("link_user_search_form");

        // user search
        $ti = new ilTextInputGUI($this->lng->txt("obj_user"), "usr_search_str");
        $ti->setValue($_POST["usr_search_str"]);
        $form->addItem($ti);

        $form->addCommandButton("searchUser", $this->lng->txt("search"));

        return $form;
    }

    /**
     * Search user
     *
     * @param
     * @return
     */
    public function getUserSearchResult()
    {
        global $DIC;

        $tpl = $DIC["tpl"];
        $lng = $DIC->language();

        if (strlen($_POST["usr_search_str"]) < 3) {
            if (strlen($_POST["usr_search_str"]) > 0) {
                $lng->loadLanguageModule("search");
                return $tpl->getMessageHTML($lng->txt("search_minimum_three"), "info");
            }

            return "";
        }

        $form = $this->initUserSearchForm();
        $form->checkInput();

        $users = ilInternalLink::searchUsers($form->getInput("usr_search_str"));
        if (count($users) == 0) {
            return $tpl->getMessageHTML($lng->txt("cont_user_search_did_not_match"), "info");
        }

        $f = $DIC->ui()->factory();
        $r = $DIC->ui()->renderer();
        $lng = $DIC->language();
        $cards = array();
        foreach ($users as $user) {
            $b = $f->button()->standard($lng->txt("insert"), "#")
                ->withOnLoadCode(function ($id) use ($user) {
                    return
                    '$("#' . $id . "\").click(function(ev) { il.IntLink.addInternalLink('[iln user=\"" .
                    ilObjUser::_lookupLogin($user) . "\"/]', '', ev); return false;});";
                });
            $name = ilUserUtil::getNamePresentation($user);
            $cards[] = $f->card()->standard($name, $f->image()->responsive(ilObjUser::_getPersonalPicturePath($user, "small"), $name))
                ->withSections(array($b));
        }
        $deck = $f->deck($cards)->withLargeCardsSize();

        return $r->renderAsync($deck);
    }
}
