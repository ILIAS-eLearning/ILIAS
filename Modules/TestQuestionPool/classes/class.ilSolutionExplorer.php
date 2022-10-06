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

include_once "./Modules/Test/classes/inc.AssessmentConstants.php";

/*
* Solution Explorer for question pools
*
* @author Helmut Schottmüller <helmut.schottmueller@mac.com>
* @version $Id$
*
* @ingroup ModulesTestQuestionPool
 */

class ilSolutionExplorer extends ilExplorer
{
    /**
     * id of root folder
     * @var int root folder id
     * @access private
     */
    public $root_id;
    public $ctrl;

    public $selectable_type;
    public $ref_id;
    public $target_class;

    /**
    * Constructor
    * @access	public
    * @param	string	target
    */
    public function __construct($a_target, $a_target_class)
    {
        global $DIC;
        $tree = $DIC['tree'];
        $ilCtrl = $DIC['ilCtrl'];

        $this->ctrl = $ilCtrl;
        $this->target_class = $a_target_class;
        parent::__construct($a_target);
        $this->tree = $tree;
        $this->root_id = $this->tree->readRootId();
        $this->order_column = "title";

        $this->setSessionExpandVariable("expand_sol");

        // add here all container objects
        $this->addFilter("root");
        $this->addFilter("cat");
        $this->addFilter("grp");
        $this->addFilter("fold");
        $this->addFilter("crs");

        $this->setFilterMode(IL_FM_POSITIVE);
        $this->setFiltered(true);
    }

    /**
     * @param int $ref_id
     */
    public function expandPathByRefId($ref_id): void
    {
        /**
         * @var $tree ilTree
         */
        global $DIC;
        $tree = $DIC['tree'];

        if (ilSession::get($this->expand_variable) == null) {
            ilSession::set($this->expand_variable, array());
        }

        $path = $tree->getPathId($ref_id);
        foreach ((array) $path as $node_id) {
            if (!in_array($node_id, ilSession::get($this->expand_variable))) {
                $expand = ilSession::get($this->expand_variable);
                $expand[] = $node_id;
                ilSession::set($this->expand_variable, $expand);
            }
        }

        $this->expanded = ilSession::get($this->expand_variable);
    }

    public function setSelectableType($a_type): void
    {
        $this->selectable_type = $a_type;
    }
    public function setRefId($a_ref_id): void
    {
        $this->ref_id = $a_ref_id;
    }


    public function buildLinkTarget($a_node_id, string $a_type): string
    {
        if ($a_type == $this->selectable_type) {
            $this->ctrl->setParameterByClass($this->target_class, 'source_id', $a_node_id);
            return $this->ctrl->getLinkTargetByClass($this->target_class, 'linkChilds');
        } else {
            $this->ctrl->setParameterByClass($this->target_class, "ref_id", $this->ref_id);
            return $this->ctrl->getLinkTargetByClass($this->target_class, 'addSolutionHint');
        }
    }

    public function buildFrameTarget(string $a_type, $a_child = 0, $a_obj_id = 0): string
    {
        return '';
    }

    public function isClickable(string $type, int $ref_id = 0): bool
    {
        return $type == $this->selectable_type && $ref_id !== $this->ref_id;
    }

    public function showChilds($a_parent_id): bool
    {
        global $DIC;
        $rbacsystem = $DIC['rbacsystem'];

        if ($a_parent_id == 0) {
            return true;
        }

        if ($rbacsystem->checkAccess("read", $a_parent_id)) {
            return true;
        } else {
            return false;
        }
    }
} // END class ilSolutionExplorer
