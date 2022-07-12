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

use ILIAS\Skill\Service\SkillTreeService;

/**
 * Explorer for skill management
 * @author Alexander Killing <killing@leifos.de>
 */
class ilSurveySkillExplorer extends ilExplorer
{
    protected array $parent;
    protected array $node;
    protected bool $templates;
    protected \ILIAS\Survey\Editing\EditingGUIRequest $edit_request;
    protected array $force_open_path;
    protected ilObjUser $user;
    protected ilCtrl $ctrl;
    protected array $all_nodes = [];
    protected array $child_nodes = [];
    protected SkillTreeService $skill_tree_service;

    public function __construct(
        string $a_target,
        bool $a_templates = false
    ) {
        global $DIC;
        $this->user = $DIC->user();
        $this->ctrl = $DIC->ctrl();
        $this->lng = $DIC->language();
        $this->templates = $a_templates;
        
        parent::__construct($a_target);
        
        $this->setFilterMode(IL_FM_POSITIVE);
        $this->addFilter("skrt");
        $this->addFilter("skll");
        $this->addFilter("scat");
        //		$this->addFilter("sktr");
        $this->setTitleLength(999);

        $this->skill_tree_service = $DIC->skills()->tree();
        $this->tree = $this->skill_tree_service->getGlobalSkillTree();
        $this->root_id = $this->tree->readRootId();
        
        $this->setSessionExpandVariable("skpexpand");
        $this->checkPermissions(false);
        $this->setPostSort(false);
        
        $this->setOrderColumn("order_nr");
        //		$this->textwidth = 200;

        $this->force_open_path = array();
        
        $this->all_nodes = $this->tree->getSubTree($this->tree->getNodeData($this->root_id));
        foreach ($this->all_nodes as $n) {
            $this->node[$n["child"]] = $n;
            $this->child_nodes[$n["parent"]][] = $n;
            $this->parent[$n["child"]] = $n["parent"];
        }

        $this->edit_request = $DIC->survey()
            ->internal()
            ->gui()
            ->editing()
            ->request();
    }
    
    /**
     * set force open path
     */
    public function setForceOpenPath(array $a_path) : void
    {
        $this->force_open_path = $a_path;
    }

    public function isClickable(
        string $type,
        int $ref_id = 0
    ) : bool {
        return $type === "skll";
    }
    
    public function buildLinkTarget($a_node_id, string $a_type) : string
    {
        $ilCtrl = $this->ctrl;
        $ilCtrl->setParameterByClass("ilsurveyskillgui", "obj_id", $a_node_id);
        $ret = $ilCtrl->getLinkTargetByClass("ilsurveyskillgui", "selectSkillForQuestion");
        $ilCtrl->setParameterByClass("ilsurveyskillgui", "obj_id", $this->edit_request->getObjId());
        
        return $ret;
    }

    public function forceExpanded($a_obj_id) : bool
    {
        if (in_array($a_obj_id, $this->force_open_path)) {
            return true;
        }
        return false;
    }

    public function getImage(
        string $a_name,
        string $a_type = "",
        $a_obj_id = ""
    ) : string {
        if ($a_type === "sktr") {
            return ilUtil::getImagePath("icon_skll_s.gif");
        }
        return ilUtil::getImagePath($a_name);
    }
}
