<?php
/* Copyright (c) 1998-2013 ILIAS open source, Extended GPL, see docs/LICENSE */

include_once("./Modules/LearningModule/classes/class.ilLMExplorerGUI.php");

/**
 * LM presentation (left frame) explorer GUI class
 *
 * @author	Alex Killing <alex.killing@gmx.de>
 * @version	$Id$
 *
 * @ingroup ModulesLearningModule
 */
class ilLMTOCExplorerGUI extends ilLMExplorerGUI
{
    protected $lang;
    protected $highlight_node;
    protected $tracker;
    protected $export_all_languages;

    /**
     * @var ilPageActivationDBRepository
     */
    protected $activation_repo;

    /**
     * @var array
     */
    protected $complete_tree;

    /**
     * @var array
     */
    protected $activation_data;

    /**
     * Constructor
     *
     * @param object $a_parent_obj parent gui object
     * @param string $a_parent_cmd parent cmd
     * @param ilLMPresentationGUI $a_lm_pres learning module presentation gui object
     * @param string $a_lang language
     */
    public function __construct(
        $a_parent_obj,
        $a_parent_cmd,
        ilLMPresentationGUI $a_lm_pres,
        $a_lang = "-",
        $a_focus_id = 0,
        $export_all_languages = false
    ) {
        global $DIC;

        $this->user = $DIC->user();
        $this->lm_pres = $a_lm_pres;
        $this->lm = $this->lm_pres->lm;
        $exp_id = (!$this->getOfflineMode() && $this->lm->getProgressIcons())
            ? "ilLMProgressTree"
            : "";
        parent::__construct($a_parent_obj, $a_parent_cmd, $this->lm, $exp_id);
        $this->lm_set = new ilSetting("lm");
        $this->lang = $a_lang;
        if ($a_focus_id > 0) {
            $this->setSecondaryHighlightedNodes(array($a_focus_id));
        }
        if ($this->lm->getTOCMode() != "pages") {
            $this->setTypeWhiteList(array("st", "du"));
        }
        $this->focus_id = $a_focus_id;
        $this->export_all_languages = $export_all_languages;

        $this->activation_repo = new ilPageActivationDBRepository();

        $this->initTreeData();
    }

    /**
     * Init tree data
     * @param
     * @return
     */
    protected function initTreeData()
    {
        $nodes = $this->tree->getCompleteTree();
        foreach ($nodes as $node) {
            $this->complete_tree["childs"][$node["parent"]][] = $node;
            $this->complete_tree["parent"][$node["child"]] = $node["parent"];
            $this->complete_tree["nodes"][$node["child"]] = $node;
        }

        $page_ids = array_column($this->complete_tree["nodes"], "child");
        $this->activation_data = $this->activation_repo->get(
            "lm",
            $page_ids,
            $this->lm_set->get("time_scheduled_page_activation"),
            $this->lang
        );
        $this->initVisibilityData($this->tree->readRootId());
    }

    /**
     * Init visibility data
     * @param int $node_id
     */
    protected function initVisibilityData($node_id)
    {
        $current_node = $this->complete_tree["nodes"][$node_id];

        if (is_array($this->complete_tree["childs"][$node_id])) {
            foreach ($this->complete_tree["childs"][$node_id] as $node) {
                $this->initVisibilityData($node["child"]);
            }
        }

        // pages are visible if they are active or activation info should be shown
        if ($current_node["type"] == "pg") {
            $this->complete_tree["visibility"][$node_id] = ($this->activation_data[$node_id]["active"] ||
                $this->activation_data[$node_id]["show_info"]);
        } elseif ($current_node["type"] == "st") {

            // make chapters visible as soon as there is one visible child
            $this->complete_tree["visibility"][$node_id] = false;
            if (is_array($this->complete_tree["childs"][$node_id])) {
                foreach ($this->complete_tree["childs"][$node_id] as $node) {
                    if (isset($this->complete_tree["visibility"][$node["child"]]) &&
                        $this->complete_tree["visibility"][$node["child"]]) {
                        $this->complete_tree["visibility"][$node_id] = true;
                    }
                }
            }
        } else {
            $this->complete_tree["visibility"][$node_id] = true;
        }
    }

    /**
     * Get root node
     */
    public function getRootNode()
    {
        $root_id = $this->getTree()->readRootId();
        if ($this->focus_id > 0 && $this->getTree()->isInTree($this->focus_id) &&
            ilLMObject::_lookupType($this->focus_id) == "st") {
            //			$root_id = $this->focus_id;
        }
        return $this->getTree()->getNodeData($root_id);
    }

    /**
     * Set tracker
     *
     * @param ilLMTracker $a_val tracker object
     */
    public function setTracker($a_val)
    {
        $this->tracker = $a_val;
    }

    /**
     * Get tracker
     *
     * @return ilLMTracker tracker object
     */
    public function getTracker()
    {
        return $this->tracker;
    }

    /**
     * Set highlighted node
     *
     * @param int $a_val node id
     */
    public function setHighlightNode($a_val)
    {
        $this->highlight_node = $a_val;
    }

    /**
     * Get highlighted node
     *
     * @return int node id
     */
    public function getHighlightNode()
    {
        return $this->highlight_node;
    }

    /**
     * Is node highlighted?
     *
     * @param mixed $a_node node object/array
     * @return boolean node visible true/false
     */
    public function isNodeHighlighted($a_node)
    {
        if ($a_node["child"] == $this->getHighlightNode()) {
            return true;
        }
        return false;
    }

    /**
     * Get node content
     *
     * @param array $a_node node array
     * @return string node content
     */
    public function getNodeContent($a_node)
    {
        if ($a_node["child"] == $this->getNodeId($this->getRootNode())) {
            return $this->lm_pres->getLMPresentationTitle();
        }

        if ($a_node["type"] == "st") {
            return ilStructureObject::_getPresentationTitle(
                $a_node["child"],
                IL_CHAPTER_TITLE,
                $this->lm->isActiveNumbering(),
                false,
                false,
                $this->lm->getId(),
                $this->lang,
                true
            );
        } elseif ($a_node["type"] == "pg") {
            return ilLMPageObject::_getPresentationTitle(
                $a_node["child"],
                $this->lm->getPageHeader(),
                $this->lm->isActiveNumbering(),
                $this->lm_set->get("time_scheduled_page_activation"),
                true,
                $this->lm->getId(),
                $this->lang,
                true
            );
        } elseif ($a_node["child"] == $this->getNodeId($this->getRootNode())) {
            return $this->lm->getTitle();
        }

        return $a_node["title"];
    }


    /**
     * Get node icon
     *
     * @param array $a_node node array
     * @return string icon path
     */
    public function getNodeIcon($a_node)
    {
        // overwrite chapter icons with lp info?
        if (!$this->getOfflineMode() && $a_node["type"] == "st") {
            $icon = $this->checkLPIcon($a_node["child"]);
            if ($icon != "") {
                return $icon;
            }
        }

        // use progress icons (does not depend on lp mode)
        if (!$this->getOfflineMode() && $this->lm->getProgressIcons()) {
            return $this->tracker->getIconForLMObject($a_node, $this->highlight_node);
        }

        include_once("./Modules/LearningModule/classes/class.ilLMObject.php");

        if ($a_node["type"] == "du") {
            $a_node["type"] = "lm";
        }
        $a_name = "icon_" . $a_node["type"] . ".svg";
        if ($a_node["type"] == "pg") {
            include_once("./Modules/LearningModule/classes/class.ilLMPage.php");
            $lm_set = new ilSetting("lm");
            $active = ilLMPage::_lookupActive(
                $a_node["child"],
                $this->lm->getType(),
                $lm_set->get("time_scheduled_page_activation")
            );

            // is page scheduled?
            $img_sc = ($lm_set->get("time_scheduled_page_activation") &&
                ilLMPage::_isScheduledActivation($a_node["child"], $this->lm->getType()) && !$active
                && !$this->getOfflineMode())
                ? "_sc"
                : "";

            $a_name = "icon_pg" . $img_sc . ".svg";

            if (!$active && !$this->getOfflineMode()) {
                $a_name = "icon_pg_d" . $img_sc . ".svg";
            }
        }

        return ilUtil::getImagePath($a_name, false, "output", $this->getOfflineMode());
    }

    /**
     * Is node clickable
     *
     * @param array $a_node node array
     * @return bool clickable?
     */
    public function isNodeClickable($a_node)
    {
        $ilUser = $this->user;

        $orig_node_id = $a_node["child"];

        // if navigation is restricted based on correct answered questions
        // check if we have preceeding pages including unsanswered/incorrect answered questions
        if (!$this->getOfflineMode()) {
            if ($this->lm->getRestrictForwardNavigation()) {
                if ($this->getTracker()->hasPredIncorrectAnswers($orig_node_id)) {
                    return false;
                }
            }
        }

        if ($a_node["type"] == "st") {
            if (!$this->getOfflineMode()) {
                if ($this->lm->getTOCMode() != "pages") {
                    $a_node = $this->getTree()->fetchSuccessorNode($a_node["child"], "pg");
                } else {
                    // faster, but needs pages to be in explorer
                    $a_node = $this->getSuccessorNode($a_node["child"], "pg");
                }
                if ($a_node["child"] == 0) {
                    return false;
                }
            } else {
                // get next activated page
                $found = false;
                while (!$found) {
                    if ($this->lm->getTOCMode() != "pages") {
                        $a_node = $this->getTree()->fetchSuccessorNode($a_node["child"], "pg");
                    } else {
                        $a_node = $this->getSuccessorNode($a_node["child"], "pg");
                    }
                    include_once("./Modules/LearningModule/classes/class.ilLMPage.php");
                    $active = ilLMPage::_lookupActive(
                        $a_node["child"],
                        $this->lm->getType(),
                        $this->lm_set->get("time_scheduled_page_activation")
                    );

                    if ($a_node["child"] > 0 && !$active) {
                        $found = false;
                    } else {
                        $found = true;
                    }
                }
                if ($a_node["child"] <= 0) {
                    return false;
                } else {
                    $path = $this->getTree()->getPathId($a_node["child"]);
                    if (!in_array($orig_node_id, $path)) {
                        return false;
                    }
                }
            }
        }

        if ($a_node["type"] == "pg") {
            // check public area mode
            include_once("./Modules/LearningModule/classes/class.ilLMObject.php");
            if ($ilUser->getId() == ANONYMOUS_USER_ID && !ilLMObject::_isPagePublic($a_node["child"], true)) {
                return false;
            }
        }

        return true;
    }


    /**
     * Get node icon alt text
     *
     * @param array $a_node node array
     * @return string alt text
     */
    public function getNodeIconAlt($a_node)
    {
    }
    
    /**
     * Get href for node
     *
     * @param mixed $a_node node object/array
     * @return string href attribute
     */
    public function getNodeHref($a_node)
    {
        if (!$this->getOfflineMode()) {
            return $this->lm_pres->getLink($this->lm->getRefId(), "", $a_node["child"]);
        //return parent::buildLinkTarget($a_node_id, $a_type);
        } else {
            if ($a_node["type"] != "pg") {
                // get next activated page
                $found = false;
                while (!$found) {
                    $a_node = $this->getTree()->fetchSuccessorNode($a_node["child"], "pg");
                    include_once("./Modules/LearningModule/classes/class.ilLMPage.php");
                    $active = ilLMPage::_lookupActive(
                        $a_node["child"],
                        $this->lm->getType(),
                        $this->lm_set->get("time_scheduled_page_activation")
                    );

                    if ($a_node["child"] > 0 && !$active) {
                        $found = false;
                    } else {
                        $found = true;
                    }
                }
            }

            $lang_suffix = "";
            if ($this->export_all_languages) {
                if ($this->lang != "" && $this->lang != "-") {
                    $lang_suffix = "_" . $this->lang;
                }
            }

            include_once("./Modules/LearningModule/classes/class.ilLMPageObject.php");
            if ($nid = ilLMPageObject::getExportId($this->lm->getId(), $a_node["child"])) {
                return "lm_pg_" . $nid . $lang_suffix . ".html";
            }
            return "lm_pg_" . $a_node["child"] . $lang_suffix . ".html";
        }
    }

    /**
     * Is node visible?
     *
     * @param mixed $a_node node object/array
     * @return boolean node visible true/false
     */
    public function isNodeVisible($a_node)
    {
        return (bool) $this->complete_tree["visibility"][$a_node["child"]];
        //include_once("./Modules/LearningModule/classes/class.ilLMTracker.php");
        //return ilLMTracker::_isNodeVisible($a_node);
    }
}
