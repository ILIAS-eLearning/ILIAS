<?php
/* Copyright (c) 1998-2013 ILIAS open source, Extended GPL, see docs/LICENSE */

/*
* Solution Explorer for question pools
*
* @author Helmut Schottmüller <helmut.schottmueller@mac.com>
* @version $Id$
*
* @ingroup ModulesTestQuestionPool
*/

include_once "./Services/UIComponent/Explorer/classes/class.ilExplorer.php";
include_once "./Modules/Test/classes/inc.AssessmentConstants.php";

class ilSolutionExplorer extends ilExplorer
{

/**
 * id of root folder
 * @var int root folder id
 * @access private
 */
    public $root_id;
    public $output;
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
    public function expandPathByRefId($ref_id)
    {
        /**
         * @var $tree ilTree
         */
        global $DIC;
        $tree = $DIC['tree'];

        if (!$_SESSION[$this->expand_variable]) {
            $_SESSION[$this->expand_variable] = array();
        }

        $path = $tree->getPathId($ref_id);
        foreach ((array) $path as $node_id) {
            if (!in_array($node_id, $_SESSION[$this->expand_variable])) {
                $_SESSION[$this->expand_variable][] = $node_id;
            }
        }

        $this->expanded = $_SESSION[$this->expand_variable];
    }

    public function setSelectableType($a_type)
    {
        $this->selectable_type = $a_type;
    }
    public function setRefId($a_ref_id)
    {
        $this->ref_id = $a_ref_id;
    }
    

    public function buildLinkTarget($a_node_id, $a_type)
    {
        if ($a_type == $this->selectable_type) {
            $this->ctrl->setParameterByClass($this->target_class, 'source_id', $a_node_id);
            return $this->ctrl->getLinkTargetByClass($this->target_class, 'linkChilds');
        } else {
            $this->ctrl->setParameterByClass($this->target_class, "ref_id", $this->ref_id);
            return $this->ctrl->getLinkTargetByClass($this->target_class, 'addSolutionHint');
        }
    }

    public function buildFrameTarget($a_type, $a_child = 0, $a_obj_id = 0)
    {
        return '';
    }

    public function isClickable($a_type, $a_ref_id = 0)
    {
        return $a_type == $this->selectable_type and $a_ref_id != $this->ref_id;
    }

    public function showChilds($a_ref_id)
    {
        global $DIC;
        $rbacsystem = $DIC['rbacsystem'];

        if ($a_ref_id == 0) {
            return true;
        }

        if ($rbacsystem->checkAccess("read", $a_ref_id)) {
            return true;
        } else {
            return false;
        }
    }
} // END class ilSolutionExplorer
