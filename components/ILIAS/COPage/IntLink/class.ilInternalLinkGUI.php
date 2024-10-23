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

use ILIAS\COPage\IntLink\StandardGUIRequest;

/**
 * Internal link selector
 * @author Alexander Killing <killing@leifos.de>
 */
class ilInternalLinkGUI
{
    protected ?ilObjFile $uploaded_file = null;
    protected int $parent_fold_id;
    protected string $default_parent_obj_type;
    protected StandardGUIRequest $request;
    protected string $return;
    protected string $default_link_type = "";
    protected int $default_parent_ref_id = 0;
    protected int $default_parent_obj_id = 0;
    protected int $parent_ref_id = 0;
    protected int $parent_obj_id = 0;
    protected string $link_type = "";		// "PageObject_New"
    protected string $link_target = "";		// "New"
    protected string $base_link_type = "";	// "PageObject"
    public string $set_link_script = "";
    /** @var array<string, string> array link types */
    protected array $ltypes = [];
    /** @var array<string, string> parent object types for link base types */
    protected array $parent_type = [];
    public ilCtrl $ctrl;
    protected bool $filter_white_list = false;
    /** @var string[] */
    protected array $filter_link_types = [];
    protected ilTree $tree;
    protected ilLanguage $lng;
    protected ilObjUser $user;

    public function __construct(
        string $a_default_link_type,
        int $a_default_parent_id,
        bool $a_is_ref = true
    ) {
        global $DIC;
        $this->tree = $DIC->repositoryTree();
        $this->lng = $DIC->language();
        $this->ctrl = $DIC->ctrl();
        $this->user = $DIC->user();

        $this->request = new StandardGUIRequest(
            $DIC->http(),
            $DIC->refinery()
        );

        $this->lng->loadLanguageModule("link");
        $this->lng->loadLanguageModule("content");
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
        $this->parent_ref_id = $this->request->getLinkParentRefId();
        $this->parent_fold_id = $this->request->getLinkParentFolderId();		// e.g. media pool folder
        if ($this->parent_ref_id > 0) {
            $this->parent_obj_id = ilObject::_lookupObjId($this->parent_ref_id);
        } else {
            $this->parent_obj_id = $this->request->getLinkParentObjId();
        }
    }

    public function init(): void
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
                if (in_array($k, $this->filter_link_types, true)) {
                    $ltypes[$k] = $l;
                }
            }
            $this->ltypes = $ltypes;
        }
        // determine link type and target
        $this->link_type = ($this->request->getLinkType() === "")
            ? $this->default_link_type
            : $this->request->getLinkType();
        $ltype_arr = explode("_", $this->link_type);
        $this->base_link_type = $ltype_arr[0];
        $this->link_target = $ltype_arr[1] ?? "";


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
                if ($this->parent_ref_id === 0 && $this->parent_obj_id === 0
                    && $def_type === ($this->parent_type[$this->base_link_type] ?? "")) {
                    $this->parent_ref_id = $this->default_parent_ref_id;
                    $this->parent_obj_id = $this->default_parent_obj_id;
                    $ctrl->setParameter($this, "link_par_obj_id", $this->parent_obj_id);
                    $ctrl->setParameter($this, "link_par_ref_id", $this->parent_ref_id);
                }
                break;
        }
    }

    public function setSetLinkTargetScript(string $a_script): void
    {
        $this->set_link_script = $a_script;
    }

    public function setReturn(string $a_return): void
    {
        $this->return = $a_return;
    }

    public function getSetLinkTargetScript(): string
    {
        return $this->set_link_script;
    }

    public function filterLinkType(string $a_link_type): void
    {
        $this->filter_link_types[] = $a_link_type;
    }

    /**
     * Set filter list as white list (per detault it is a black list)
     */
    public function setFilterWhiteList(bool $a_white_list): void
    {
        $this->filter_white_list = $a_white_list;
    }


    public function executeCommand(): string
    {
        $this->init();
        $next_class = $this->ctrl->getNextClass($this);

        $cmd = $this->ctrl->getCmd("showLinkHelp");
        switch ($next_class) {
            default:
                $ret = $this->$cmd();
                break;
        }

        return (string) $ret;
    }

    public function resetLinkList(): void
    {
        $ctrl = $this->ctrl;

        $ctrl->setParameter($this, "link_par_ref_id", 0);
        $ctrl->setParameter($this, "link_par_obj_id", 0);
        $ctrl->setParameter($this, "link_par_fold_id", 0);
        $ctrl->setParameter($this, "link_type", "");

        $ctrl->redirect($this, "showLinkHelp", "", true);
    }

    public function closeLinkHelp(): void
    {
        if ($this->return === "") {
            $this->ctrl->returnToParent($this);
        } else {
            ilUtil::redirect($this->return);
        }
    }

    /**
     * Prepare output for JS enabled editing
     */
    public function prepareJavascriptOutput(string $str): string
    {
        return htmlspecialchars($str, ENT_QUOTES);
    }


    /**
    * Show link help list
    */
    public function showLinkHelp(): void
    {
        $ilUser = $this->user;
        $ilCtrl = $this->ctrl;


        $parent_type = $this->parent_type[$this->base_link_type] ?? "";
        if ((in_array($this->base_link_type, array("GlossaryItem", "WikiPage", "PageObject", "StructureObject"), true) &&
            ($this->parent_ref_id === 0))
            ||
            (($this->parent_ref_id > 0) &&
                ilObject::_lookupType($this->parent_ref_id, true) !== $parent_type)) {
            if ($parent_type !== "") {
                $this->changeTargetObject($parent_type);
            }
        }
        if ($ilCtrl->isAsynch()) {
            $tpl = new ilGlobalTemplate("tpl.link_help_asynch.html", true, true, "components/ILIAS/COPage/IntLink");
            $tpl->setVariable("NEW_LINK_URL", $this->ctrl->getLinkTarget(
                $this,
                "",
                false,
                true,
                false
            ));
        } else {
            $tpl = new ilGlobalTemplate("tpl.link_help.html", true, true, "components/ILIAS/COPage/IntLink");
            $tpl->setVariable("LOCATION_STYLESHEET", ilUtil::getStyleSheetLocation());
        }

        $tpl->setVariable("FORMACTION", $this->ctrl->getFormAction($this, "changeLinkType", "", true));
        $tpl->setVariable("FORMACTION2", $this->ctrl->getFormAction($this));
        $tpl->setVariable("TXT_HELP_HEADER", $this->lng->txt("cont_link_select"));
        $tpl->setVariable("TXT_TYPE", $this->lng->txt("cont_link_type"));


        $select_ltype = ilLegacyFormElementsUtil::formSelect(
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
                $cont_obj = new ilObjLearningModule($this->parent_ref_id, true);

                // get all chapters
                $ctree = $cont_obj->getLMTree();
                $nodes = $ctree->getSubTree($ctree->getNodeData($ctree->getRootId()));
                $tpl->setCurrentBlock("chapter_list");
                $tpl->setVariable("TXT_CONTENT_OBJECT", $this->lng->txt("obj_lm"));
                $tpl->setVariable("TXT_CONT_TITLE", $cont_obj->getTitle());
                $tpl->setVariable("THEAD", $this->lng->txt("pages"));


                $tpl->setCurrentBlock("change_cont_obj");
                $tpl->setVariable("CMD_CHANGE_CONT_OBJ", "changeTargetObject");
                $tpl->setVariable("BTN_CHANGE_CONT_OBJ", $this->lng->txt("change"));
                $tpl->parseCurrentBlock();

                foreach ($nodes as $node) {
                    if ($node["type"] === "st") {
                        $tpl->setCurrentBlock("header_row");
                        $tpl->setVariable("TXT_HEADER", $node["title"]);
                        $tpl->parseCurrentBlock();
                        $tpl->setCurrentBlock("row");
                        $tpl->parseCurrentBlock();
                    }

                    if ($node["type"] === "pg") {
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
                if (ilObject::_lookupType($this->parent_ref_id, true) !== "lm") {
                    $this->changeTargetObject("lm");
                }

                $cont_obj = new ilObjLearningModule($this->parent_ref_id, true);

                // get all chapters
                $ctree = $cont_obj->getLMTree();
                $nodes = $ctree->getSubTree($ctree->getNodeData($ctree->getRootId()));
                $tpl->setCurrentBlock("chapter_list");
                $tpl->setVariable("TXT_CONTENT_OBJECT", $this->lng->txt("obj_lm"));
                $tpl->setVariable("TXT_CONT_TITLE", $cont_obj->getTitle());
                $tpl->setVariable("THEAD", $this->lng->txt("link_chapters"));
                $tpl->setCurrentBlock("change_cont_obj");
                $tpl->setVariable("CMD_CHANGE_CONT_OBJ", "changeTargetObject");
                $tpl->setVariable("BTN_CHANGE_CONT_OBJ", $this->lng->txt("change"));
                $tpl->parseCurrentBlock();

                foreach ($nodes as $node) {
                    if ($node["type"] === "st") {
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
                //$tpl->setVariable("TARGET2", " target=\"content\" ");
                // content object id = 0 --> get clipboard objects
                if ($this->parent_ref_id === 0) {
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
                } else {
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
                        $tpl->setCurrentBlock("icon");
                        $tpl->setVariable("ICON_SRC", ilUtil::getImagePath("standard/icon_fold.svg"));
                        $tpl->parseCurrentBlock();
                        $tpl->setCurrentBlock("link_row");
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
                        if ($obj["type"] === "fold") {
                            $tpl->setCurrentBlock("icon");
                            $tpl->setVariable("ICON_SRC", ilUtil::getImagePath("standard/icon_fold.svg"));
                            $tpl->parseCurrentBlock();
                            $tpl->setCurrentBlock("link_row");
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
                            if (ilObject::_lookupType($fid) === "mob") {
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
                }
                $tpl->setCurrentBlock("chapter_list");
                $tpl->parseCurrentBlock();
                break;

                // wiki page link
            case "WikiPage":
                $wiki_id = ilObject::_lookupObjId($this->parent_ref_id);
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
                        "wpage",
                        ilPCParagraph::_readAnchors("wpg", $wpage["id"], "")
                    );
                }
                $tpl->setCurrentBlock("chapter_list");
                $tpl->parseCurrentBlock();
                break;

                // Portfolio page link
            case "PortfolioPage":
            case "PortfolioTemplatePage":
                $prtf_id = $this->parent_obj_id;
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
                if (!isset($this->uploaded_file)) {
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
     */
    public function getFileLinkHTML(): string
    {
        $lng = $this->lng;
        $ilCtrl = $this->ctrl;

        $tpl = new ilTemplate("tpl.link_file.html", true, true, "components/ILIAS/COPage/IntLink");
        if (!is_object($this->uploaded_file)) {
            $tpl->setCurrentBlock("form");
            $tpl->setVariable(
                "FORM_ACTION",
                $ilCtrl->getFormAction($this, "saveFileLink", "", true)
            );
            $tpl->setVariable("TXT_SELECT_FILE", $lng->txt("cont_select_file"));
            $tpl->setVariable("TXT_SAVE_LINK", $lng->txt("cont_create_link"));
            $tpl->setVariable("CMD_SAVE_LINK", "saveFileLink");
            $fi = new ilFileInputGUI("", "link_file");
            $fi->setSize(15);
            $tpl->setVariable("INPUT", $fi->getToolbarHTML());
            $tpl->parseCurrentBlock();
        } else {
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
        }
        return $tpl->get();
    }

    /**
     * Save file link
     */
    public function saveFileLink(): void
    {
        if ($_FILES["link_file"]["name"] != "") {
            $fileObj = new ilObjFile();
            $fileObj->setType("file");
            $fileObj->setTitle($_FILES["link_file"]["name"]);
            $fileObj->setDescription("");
            $fileObj->setFileName($_FILES["link_file"]["name"]);
            $fileObj->setMode("filelist");
            $fileObj->create();
            // upload file to filesystem
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
    public function outputThumbnail(
        ilGlobalTemplate $tpl,
        int $a_id,
        string $a_mode = ""
    ): void {
        // output thumbnail
        $mob = new ilObjMediaObject($a_id);
        $med = $mob->getMediaItem("Standard");
        $target = $med->getThumbnailTarget("small");
        $suff = "";
        if ($this->getSetLinkTargetScript() !== "") {
            $tpl->setCurrentBlock("thumbnail_link");
            $suff = "_link";
        } else {
            $tpl->setCurrentBlock("thumbnail_js");
            $suff = "_js";
        }

        if ($target !== "") {
            $tpl->setCurrentBlock("thumb" . $suff);
            $tpl->setVariable("SRC_THUMB", $target);
            $tpl->parseCurrentBlock();
        } else {
            $tpl->setVariable("NO_THUMB", "&nbsp;");
        }

        if ($this->getSetLinkTargetScript() !== "") {
            $tpl->setCurrentBlock("thumbnail_link");
        } else {
            $tpl->setCurrentBlock("thumbnail_js");
        }
        $tpl->parseCurrentBlock();
    }

    public function changeLinkType(): void
    {
        $ctrl = $this->ctrl;

        $ctrl->setParameter($this, "link_type", $this->request->getLinkType());
        $base_type = explode("_", $this->request->getLinkType())[0];
        if ($this->parent_type[$base_type] !== ilObject::_lookupType($this->parent_ref_id, true)) {
            $ctrl->setParameter($this, "link_par_ref_id", 0);
            $ctrl->setParameter($this, "link_par_obj_id", 0);
        }

        $ctrl->redirect($this, "showLinkHelp", "", true);
    }

    /**
     * select media pool folder
     */
    public function setMedPoolFolder(): void
    {
        $ctrl = $this->ctrl;
        $ctrl->setParameter($this, "link_par_fold_id", $this->request->getMediaPoolFolder());
        $ctrl->redirect($this, "showLinkHelp", "", true);
    }

    /**
     * Cange target object
     */
    public function getTargetExplorer(): string
    {
        //$ilCtrl->setParameter($this, "target_type", $a_type);
        $exp = new ilLinkTargetObjectExplorerGUI($this, "getTargetExplorer", $this->link_type);

        $a_type = $this->parent_type[$this->base_link_type] ?? "";

        $white = array("root", "cat", "crs", "fold", "grp");

        $white[] = $a_type;
        $exp->setClickableType($a_type);
        if ($a_type === "prtf") {
            $white[] = "prtt";
            $exp->setClickableType("prtt");
        }

        $exp->setTypeWhiteList($white);


        if (!$exp->handleCommand()) {
            return $exp->getHTML();
        }
        return "";
    }

    /**
     * Cange target object
     */
    public function changeTargetObject(
        string $a_type = ""
    ): void {
        $ilCtrl = $this->ctrl;

        $ilCtrl->setParameter($this, "link_par_fold_id", "");
        if ($this->request->getDo() === "set") {
            $ilCtrl->setParameter($this, "link_par_ref_id", $this->request->getSelectedId());
            $ilCtrl->redirect($this, "showLinkHelp", "", true);
            return;
        }

        $ilCtrl->setParameter($this, "link_type", $this->link_type);

        $tpl = new ilTemplate("tpl.link_help_explorer.html", true, true, "components/ILIAS/COPage/IntLink");

        $output = $this->getTargetExplorer();

        $tpl->setVariable("TXT_EXPLORER_HEADER", $this->lng->txt("cont_choose_" . ($this->parent_type[$this->base_link_type] ?? "")));

        $tpl->setVariable("EXPLORER", $output);
        $tpl->setVariable("ACTION", $this->ctrl->getFormAction($this, "resetLinkList", "", true));
        $tpl->setVariable("BTN_RESET", "resetLinkList");
        $tpl->setVariable("TXT_RESET", $this->lng->txt("back"));

        if (($this->parent_type[$this->base_link_type] ?? "") === "mep") {
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
    public function selectRepositoryItem(): string
    {
        $ilCtrl = $this->ctrl;

        $ilCtrl->setParameter($this, "link_par_fold_id", "");

        $exp = new ilIntLinkRepItemExplorerGUI($this, "selectRepositoryItem");
        $exp->setSetLinkTargetScript($this->getSetLinkTargetScript());

        if (!$exp->handleCommand()) {
            return $exp->getHTML();
        }
        return "";
    }

    /**
     * Refresh Repository Selector
     */
    public function refreshRepositorySelector(): void
    {
        $output = $this->selectRepositoryItem();
        echo $output;
        exit;
    }

    public static function getOnloadCode(string $a_url): string
    {
        return "il.Util.addOnLoad(function() {il.IntLink.init({url: '$a_url'});});";
    }

    /**
     * Get initialisation HTML to use internal link editing
     */
    public static function getInitHTML(string $a_url): string
    {
        global $DIC;

        $lng = $DIC->language();
        $tpl = $DIC["tpl"];

        $tpl->addOnLoadCode(
            self::getOnloadCode($a_url)
        );

        $lng->loadLanguageModule("link");

        $tpl->addJavaScript("assets/js/ilExplorer.js");
        ilExplorerBaseGUI::init();

        //$tpl->addJavascript("../components/ILIAS/COPage/IntLink/resources/ilIntLink.js");
        $tpl->addJavascript("assets/js/ilIntLink.js");
        // #18721
        $tpl->addJavaScript("assets/js/Form.js");

        $mt = self::getModalTemplate();

        $html = "<div id='ilIntLinkModal' data-show-signal='".$mt["show"]."' data-close-signal='".$mt["close"]."'>".
            $mt["template"] .
            "</div>";

        return $html;
    }

    public static function getModalTemplate(): array
    {
        global $DIC;

        $lng = $DIC->language();

        $ui = $DIC->ui();
        $modal = $ui->factory()->modal()->roundtrip($lng->txt("link_link"), $ui->factory()->legacy("<div id='ilIntLinkModalContent'></div>"));
        $modalt["show"] = $modal->getShowSignal()->getId();
        $modalt["close"] = $modal->getCloseSignal()->getId();
        $modalt["template"] = $ui->renderer()->renderAsync($modal);

        return $modalt;
    }


    /**
     * Render internal link item
     */
    public function renderLink(
        ilGlobalTemplate $tpl,
        string $a_title,
        int $a_obj_id,
        string $a_type,
        string $a_type_short,
        string $a_bb_type,
        array $a_anchors = array(),
        string $a_link_content = ""
    ): void {
        $chapterRowBlock = "chapter_row_js";
        $anchor_row_block = "anchor_link_js";

        $target_str = ($this->link_target === "")
            ? ""
            : " target=\"" . $this->link_target . "\"";

        if (count($a_anchors) > 0) {
            foreach ($a_anchors as $anchor) {
                if ($this->getSetLinkTargetScript() !== "") {
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

        if ($this->getSetLinkTargetScript() !== "") {
            ilImageMapEditorGUI::_recoverParameters();
            if ($a_type === "MediaObject") {
                $this->outputThumbnail($tpl, $a_obj_id);
            }
            $tpl->setCurrentBlock("link_row");
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
        } else {
            $tpl->setCurrentBlock($chapterRowBlock);
            if ($a_type === "MediaObject") {
                $this->outputThumbnail($tpl, $a_obj_id);
                $tpl->setCurrentBlock($chapterRowBlock);
            }
            $tpl->setVariable("TXT_CHAPTER", $a_title);
            if ($a_type === "MediaObject" && empty($target_str)) {
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
        }
        $tpl->parseCurrentBlock();

        $tpl->setCurrentBlock("row");
        $tpl->parseCurrentBlock();
    }

    /**
     * Add user
     */
    public function addUser(): string
    {
        $form = $this->initUserSearchForm();
        return $form->getHTML() . $this->getUserSearchResult();
    }

    /**
     * Init user search form.
     */
    public function initUserSearchForm(): ilPropertyFormGUI
    {
        $form = new ilPropertyFormGUI();
        $form->setId("link_user_search_form");

        // user search
        $ti = new ilTextInputGUI($this->lng->txt("obj_user"), "usr_search_str");
        $ti->setValue($this->request->getUserSearchStr());
        $form->addItem($ti);

        $form->addCommandButton("searchUser", $this->lng->txt("search"));

        return $form;
    }

    /**
     * Search user
     */
    public function getUserSearchResult(): string
    {
        global $DIC;

        $lng = $DIC->language();

        if (strlen($this->request->getUserSearchStr()) < 3) {
            if (strlen($this->request->getUserSearchStr()) > 0) {
                $lng->loadLanguageModule("search");
                return ilUtil::getSystemMessageHTML($lng->txt("search_minimum_three"), "info");
            }

            return "";
        }

        $form = $this->initUserSearchForm();
        $form->checkInput();

        $users = ilInternalLink::searchUsers($form->getInput("usr_search_str"));
        if (count($users) === 0) {
            return ilUtil::getSystemMessageHTML($lng->txt("cont_user_search_did_not_match"), "info");
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
