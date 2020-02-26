<?php

/* Copyright (c) 1998-2011 ILIAS open source, Extended GPL, see docs/LICENSE */


/**
* Class ilSCORM2004Utilities
*
* Sequencing Utilities class for SCORM 2004 Editing
*
* @author Hendrik Holtmann <holtmann@me.com>
* @version $Id$
*
* @ingroup ModulesScorm2004
*/

class ilSCORM2004Utilities
{
    /**
     * @var ilDB
     */
    protected $db;

    /**
     * @var Logger
     */
    protected $log;

    
    
    private $id = null;
    
    /*
    * Constructor
    * @access	public
    */
    public function __construct($a_id)
    {
        global $DIC;

        $this->db = $DIC->database();
        $this->log = $DIC["ilLog"];
        $this->id = $a_id;
    }

    //following 2 functions not used
    //	public function parentHasSeqTemplate($a_slm_id)
    //	{
    //		require_once("./Modules/Scorm2004/classes/seq_editor/class.ilSCORM2004SeqTemplate.php");
//
    //		$ilDB = $this->db;
    //		$ilLog = $this->log;
    //		$has_template = false;
//
    //		$mtree = new ilTree($a_slm_id);
    //		$mtree->setTableNames('sahs_sc13_tree','sahs_sc13_tree_node');
    //		$mtree->setTreeTablePK("slm_id");
    //		//get all parents for current node
    //		$parents = $this -> getParentsForNode($a_parents=array(),$this->id);
    //		for ($i=0;$i<count($parents);$i++)
    //		{
    //			$template = ilSCORM2004SeqTemplate::templateForChapter($parents[$i]);
    //			if ($template) {
    //				$has_template = true;
    //				break;
    //			}
    //		}
//
    //		return $has_template;
    //	}
//
    //	private function getParentsForNode($a_parents,$a_id){
    //		$parent_id = $tree->getParentId($id);
    //		if ($parent_id != 0) {
    //			array_push($a_parents,$parent_id);
    //			$this->getParentsForNode($a_parents,$parent_id);
    //		} else {
    //			return $a_parents;
    //		}
    //	}
    
    
    public function getLeftRightInfo()
    {
        $ilDB = $this->db;
        $ilLog = $this->log;
        $ilLog->write("SCORM: getLeftRightInfo");
        $query = "SELECT * FROM sahs_sc13_seq_tree WHERE (child = " .
            $ilDB->quote($this->getSeqNodeId(), "integer") .
            " AND importid=" . $ilDB->quote($this->getImportIdent(), "text") . ")";
        $obj_set = $ilDB->query($query);
        $ilLog->write("SCORM: getLeftRightInfo executed" . $query);
        $obj_rec = $ilDB->fetchAssoc($obj_set);
        return array("left"=> $obj_rec["lft"], "right" => $obj_rec["rgt"]);
    }
    
    
    protected function getSeqNodeId()
    {
        $ilDB = $this->db;
        $ilLog = $this->log;
        $all_props = $this->getAllSequencingProperties();
        $ilLog->write("SCORM: getSeqNodeId: " . $all_props["seqnodeid"]);
        return $all_props["seqnodeid"];
    }
    
    private function getSequencingId()
    {
        $ilDB = $this->db;
        $ilLog = $this->log;
        $ilLog->write("SCORM: getSequencingId for" . $this->getId());
        $query = "SELECT * FROM sahs_sc13_seq_item WHERE sahs_sc13_tree_node_id = " .
            $ilDB->quote($this->getId(), "integer");
        $obj_set = $ilDB->query($query);
        $obj_rec = $obj_set->fetchRow(ilDBConstants::FETCHMODE_ASSOC);
        return $obj_rec['sequencingid'];
    }
    
    private function getItemId()
    {
        $ilDB = $this->db;
        $ilLog = $this->log;
        $ilLog->write("SCORM: getSequencingId for" . $this->getId);
        $query = "SELECT * FROM sahs_sc13_seq_item WHERE sahs_sc13_tree_node_id = " .
            $ilDB->quote($this->getId(), "integer");
        $obj_set = $ilDB->query($query);
        $obj_rec = $obj_set->fetchRow(ilDBConstants::FETCHMODE_ASSOC);
        return $obj_rec['seqnodeid'];
    }
    
    public function getImportIdent()
    {
        $ilDB = $this->db;
        $ilLog = $this->log;
        $ilLog->write("SCORM: getImportIdent for" . $this->getId);
        $query = "SELECT * FROM sahs_sc13_seq_item WHERE sahs_sc13_tree_node_id = " .
            $ilDB->quote($this->getId(), "integer");
        $obj_set = $ilDB->query($query);
        $obj_rec = $obj_set->fetchRow(ilDBConstants::FETCHMODE_ASSOC);
        return $obj_rec['importid'];
    }
    
    
    
    private function getId()
    {
        return $this->id;
    }
    
    public function getAllowedActions()
    {
        $ilDB = $this->db;
        $ilLog = $this->log;
        $ilLog->write("SCORM: getAllowedActions for" . $this->tree_node_id);
        $query = "SELECT * FROM sahs_sc13_seq_item WHERE sahs_sc13_tree_node_id = " .
            $ilDB->quote($this->getId(), "integer");
        $obj_set = $ilDB->query($query);
        $obj_rec = $obj_set->fetchRow(ilDBConstants::FETCHMODE_ASSOC);
        return array("copy"=>!$obj_rec['nocopy'],"move"=>!$obj_rec['nomove'],"delete"=>!$obj_rec['nodelete']);
    }
    
    public function getControlModeProperties()
    {
        $ilDB = $this->db;
        $query = "SELECT * FROM sahs_sc13_seq_seq WHERE id = " .
            $ilDB->quote($this->getSequencingId(), "text");
        $obj_set = $ilDB->query($query);
        $obj_rec = $obj_set->fetchRow(ilDBConstants::FETCHMODE_ASSOC);
        $c_properties = array(
            'flow' => $obj_rec['flow'],
            'forwardOnly' => $obj_rec['forwardonly'],
            'choice' => $obj_rec['choice'],
            'choiceExit' => $obj_rec['choiceexit'] );
        return $c_properties;
    }
    
    public function getAllSequencingProperties()
    {
        $ilDB = $this->db;
        $ilLog = $this->log;
        $query = "SELECT * FROM sahs_sc13_seq_seq WHERE (id = " .
            $ilDB->quote($this->getSequencingId(), "text") .
            " AND importid=" . $ilDB->quote($this->getImportIdent(), "text") . ")";
        $obj_set = $ilDB->query($query);
        $ilLog->write("SCORM: getAllSequencingProperties for" . $this->getSequencingId());
        $obj_rec = $obj_set->fetchRow(ilDBConstants::FETCHMODE_ASSOC);
        $sprop = array(
            'importId' => $obj_rec['importid'],
            'activityAbsoluteDurationLimit' => $obj_rec['activityabsolutedurationlimit'],
            'activityExperiencedDurationLimit' => $obj_rec['activityexperienceddurlimit'],
            'attemptAbsoluteDurationLimit' => $obj_rec['attemptabsolutedurationlimit'],
            'attemptExperiencedDurationLimit' => $obj_rec['attemptexperienceddurlimit'],
            'attemptLimit' => $obj_rec['attemptlimit'],
            'beginTimeLimit' => $obj_rec['begintimelimit'],
            'completionSetByContent' => $obj_rec['completionsetbycontent'],
            'constrainChoice' => $obj_rec['constrainchoice'],
            'seqNodeId' => $obj_rec['seqnodeid'],
            'endTimeLimit' => $obj_rec['endtimelimit'],
            'id' => $obj_rec['id'],
            'measureSatisfactionIfActive' => $obj_rec['measuresatisfactionifactive'],
            'objectiveMeasureWeight' => $obj_rec['objectivemeasureweight'],
            'objectiveSetByContent' => $obj_rec['objectivesetbycontent'],
            'preventActivation' => $obj_rec['preventactivation'],
            'randomizationTiming' => $obj_rec['randomizationtiming'],
            'reorderChildren' => $obj_rec['reorderchildren'],
            'requiredForCompleted' => $obj_rec['requiredforcompleted'],
            'requiredForIncomplete' => $obj_rec['requiredforincomplete'],
            'requiredForNotSatisfied' => $obj_rec['requiredfornotsatisfied'],
            'requiredForSatisfied' => $obj_rec['requiredforsatisfied'],
            'rollupObjectiveSatisfied' => $obj_rec['rollupobjectivesatisfied'],
            'rollupProgressCompletion' => $obj_rec['rollupprogresscompletion'],
            'selectCount' => $obj_rec['selectcount'],
            'selectionTiming' => $obj_rec['selectiontiming'],
            'sequencingId' => $obj_rec['sequencingid'],
            'tracked' => $obj_rec['tracked'],
            'useCurrentAttemptObjectiveInfo' => $obj_rec['usecurrentattemptobjectiveinfo'],
            'useCurrentAttemptProgressInfo' => $obj_rec['usecurrentattemptprogressinfo'],
            'flow' => $obj_rec['flow'],
            'forwardOnly' => $obj_rec['forwardonly'],
            'choice' => $obj_rec['choice'],
            'choiceExit' => $obj_rec['choiceexit'] );
        return $sprop;
    }
}
