<?php

/* Copyright (c) 1998-2011 ILIAS open source, Extended GPL, see docs/LICENSE */

/**
* Class ilSCORM2004Condition
*
* Sequencing Template class for SCORM 2004 Editing
*
* @author Hendrik Holtmann <holtmann@me.com>
* @version $Id$
*
* @ingroup ModulesScorm2004
*/
class ilSCORM2004Condition extends ilSCORM2004SeqNode
{
    
        
    //db fields
    private $id = null;
    private $seqNodeId = null;
    private $referencedObjective = null;
    private $condition = null;
    private $measureThreshold = 0.0;
    private $operator = null;
    
    /**
    * Constructor
    * @access	public
    */
    public function __construct()
    {
        parent::__construct();
        $this->setNodeName("condition");
    }
    
    
    // **********************
    // GETTER METHODS
    // **********************
    
    public function getSeqNodeId()
    {
        return $this->seqNodeId;
    }
    
    public function getId()
    {
        return $this->id;
    }
    
    
    public function getReferencedObjective()
    {
        return $this->referencedObjective;
    }
    
    public function getCondition()
    {
        return $this->condition;
    }
    
    public function getMeasureThreshold()
    {
        return $this->measureThreshold;
    }
    
    public function getOperator()
    {
        return $this->operator;
    }
    
    // **********************
    // Setter METHODS
    // **********************

    public function setSeqNodeId($a_seqnodeid)
    {
        $this->seqNodeId = $a_seqnodeid;
    }
    
    public function setId($a_id)
    {
        $this->id = $a_id;
    }
    
    public function setReferencedObjective($a_objective)
    {
        $this->referencedObjective = $a_objective;
    }
    
    public function setCondition($a_condition)
    {
        $this->condition = $a_condition;
    }
    
    public function setMeasureThreshold($a_measure)
    {
        $this->measureThreshold = $a_measure;
    }
    
    public function setOperator($a_operator)
    {
        $this->operator = $a_operator;
    }
    
    // **********************
    // Standard DB Operations for Object
    // **********************
    
    public function insert($a_insert_node = false)
    {
        if ($a_insert_node==true) {
            $this->setSeqNodeId(parent::insert());
        }
        $sql = "INSERT INTO sahs_sc13_seq_cond (seqnodeid,referencedobjective,cond,measurethreshold,operator)" .
                " values(" .
                $this->db->quote($this->seqNodeId, "integer") . "," .
                $this->db->quote($this->referencedObjective, "text") . "," .
                $this->db->quote($this->condition, "text") . "," .
                $this->db->quote($this->measureThreshold, "text") . "," .
                $this->db->quote($this->operator, "text") . ");";
        $result = $this->db->manipulate($sql);
        return true;
    }
}
