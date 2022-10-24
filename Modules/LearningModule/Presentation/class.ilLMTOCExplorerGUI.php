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

/**
 * LM presentation (left frame) explorer GUI class
 *
 * @author Alexander Killing <killing@leifos.de>
 */
class ilLMTOCExplorerGUI extends ilLMExplorerGUI
{
    protected string $lang;
    protected int $highlight_node = 0;
    protected bool $export_all_languages;
    protected ilPageActivationDBRepository $activation_repo;
    protected array $complete_tree = [];
    protected array $activation_data = [];
    protected ilSetting $lm_set;
    protected ilLMPresentationLinker $linker;
    protected int $focus_id = 0;
    protected ilLMPresentationService $service;
    protected ilLMTracker $tracker;

    /**
     * Constructor
     * @param object|string $a_parent_obj parent gui object
     */
    public function __construct(
        $a_parent_obj,
        string $a_parent_cmd,
        ilLMPresentationService $service,
        string $a_lang = "-",
        int $a_focus_id = 0,
        bool $export_all_languages = false
    ) {
        global $DIC;

        $this->service = $service;
        $this->user = $DIC->user();
        $this->lm = $service->getLearningModule();
        $this->linker = $service->getLinker();
        $this->tracker = $service->getTracker();

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

    protected function initTreeData(): void
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

    protected function initVisibilityData(
        int $node_id
    ): void {
        $current_node = $this->complete_tree["nodes"][$node_id];

        if (isset($this->complete_tree["childs"][$node_id])) {
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
            if (isset($this->complete_tree["childs"][$node_id])) {
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

    public function getRootNode(): array
    {
        $root_id = $this->getTree()->readRootId();
        if ($this->focus_id > 0 && $this->getTree()->isInTree($this->focus_id) &&
            ilLMObject::_lookupType($this->focus_id) == "st") {
            //			$root_id = $this->focus_id;
        }
        return $this->getTree()->getNodeData($root_id);
    }

    public function setTracker(ilLMTracker $a_val): void
    {
        $this->tracker = $a_val;
    }

    public function getTracker(): ilLMTracker
    {
        return $this->tracker;
    }

    public function setHighlightNode(int $a_val): void
    {
        $this->highlight_node = $a_val;
    }

    public function getHighlightNode(): int
    {
        return $this->highlight_node;
    }

    /**
     * @param object|array $a_node
     */
    public function isNodeHighlighted($a_node): bool
    {
        if ($a_node["child"] == $this->getHighlightNode()) {
            return true;
        }
        return false;
    }

    /**
     * @param array|object $a_node
     */
    public function getNodeContent($a_node): string
    {
        if ($a_node["child"] == $this->getNodeId($this->getRootNode())) {
            return $this->service->getPresentationStatus()->getLMPresentationTitle();
        }

        if ($a_node["type"] == "st") {
            return ilStructureObject::_getPresentationTitle(
                $a_node["child"],
                ilLMObject::CHAPTER_TITLE,
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
     * @param array|object $a_node
     */
    public function getNodeIcon($a_node): string
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

        if ($a_node["type"] == "du") {
            $a_node["type"] = "lm";
        }
        $a_name = "icon_" . $a_node["type"] . ".svg";
        if ($a_node["type"] == "pg") {
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
     * @param array|object $a_node
     */
    public function isNodeClickable($a_node): bool
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
            if ($ilUser->getId() == ANONYMOUS_USER_ID && !ilLMObject::_isPagePublic($a_node["child"], true)) {
                return false;
            }
        }

        return true;
    }


    /**
     * @param array|object $a_node
     */
    public function getNodeIconAlt($a_node): string
    {
        return "";
    }

    /**
     * @param array|object $a_node
     */
    public function getNodeHref($a_node): string
    {
        if (!$this->getOfflineMode()) {
            return $this->linker->getLink("", $a_node["child"]);
        //return parent::buildLinkTarget($a_node_id, $a_type);
        } else {
            if ($a_node["type"] != "pg") {
                // get next activated page
                $found = false;
                while (!$found) {
                    $a_node = $this->getTree()->fetchSuccessorNode($a_node["child"], "pg");
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

            if ($nid = ilLMPageObject::getExportId($this->lm->getId(), $a_node["child"])) {
                return "lm_pg_" . $nid . $lang_suffix . ".html";
            }
            return "lm_pg_" . $a_node["child"] . $lang_suffix . ".html";
        }
    }

    /**
     * @param array|object $a_node
     */
    public function isNodeVisible($a_node): bool
    {
        return (bool) $this->complete_tree["visibility"][$a_node["child"]];
    }

    //
    // Learning Sequence TOC
    //

    public function renderLSToc(\LSTOCBuilder $toc): void
    {
        $this->renderLSTocNode($toc, null);
    }

    protected function renderLSTocNode(\LSTOCBuilder $toc, ?int $current_node = null): void
    {
        $root = false;
        if ($current_node == 0) {
            $root = true;
            $current_node = $this->tree->getNodeData($this->tree->readRootId());
        }

        $children = $this->getChildren($current_node);
        if (count($children) > 0) {
            if ($root) {
                $node_toc = $toc;
            } else {
                // current workaround
                $lp = LSTOCBuilder::LP_IN_PROGRESS;
                $node_icon = $this->getNodeIcon($current_node);
                if (strpos($node_icon, "complete")) {
                    $lp = LSTOCBuilder::LP_COMPLETED;
                }

                $node_toc = $toc->node($current_node["title"], $current_node["child"], $lp);
            }
            foreach ($this->getChildren($current_node) as $child) {
                $this->renderLSTocNode($node_toc, $child);
            }
            $node_toc->end();
        } else {
            $highlight = $this->isNodeHighlighted($current_node);
            $toc->item($current_node["title"], $current_node["child"], null, $highlight);
        }
    }
}
