<?php

/* Copyright (c) 1998-2011 ILIAS open source, Extended GPL, see docs/LICENSE */

/**
* Class ilSCORM2004MapInfo
*
* Sequencing Template class for SCORM 2004 Editing
*
* @author Hendrik Holtmann <holtmann@me.com>
* @version $Id$
*
* @ingroup ModulesScorm2004
*/
class ilSCORM2004MapInfo extends ilSCORM2004SeqNode
{
    
    
    
    //db fields
    private $id = null;
    private $seqNodeId = null;
    private $targetObjectiveID = null;
    private $readSatisfiedStatus = true;
    private $readNormalizedMeasure = true;
    private $writeSatisfiedStatus = false;
    private $writeNormalizedMeasure = false;
    /**
    * Constructor
    * @access	public
    */
    public function __construct()
    {
        parent::__construct();
        $this->setNodeName("mapinfo");
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
    
    public function getTargetObjectiveID()
    {
        return $this->targetObjectiveID;
    }
    
    public function getReadSatisfiedStatus()
    {
        return $this->readSatisfiedStatus;
    }
    
    public function getReadNormalizedMeasure()
    {
        return $this->readNormalizedMeasure;
    }
    
    public function getWriteSatisfiedStatus()
    {
        return $this->writeSatisfiedStatus;
    }
    
    public function getWriteNormalizedMeasure()
    {
        return $this->writeNormalizedMeasure;
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
    
    public function setTargetObjectiveID($a_id)
    {
        $this->targetObjectiveID = $a_id;
    }
    
    public function setReadSatisfiedStatus($a_status)
    {
        $this->readSatisfiedStatus = $a_status;
    }
    
    public function setReadNormalizedMeasure($a_measure)
    {
        $this->readNormalizedMeasure = $a_measure;
    }
    
    public function setWriteSatisfiedStatus($a_status)
    {
        $this->writeSatisfiedStatus = $a_status ;
    }
    
    public function setWriteNormalizedMeasure($a_measure)
    {
        $this->writeNormalizedMeasure = $a_measure;
    }
    
    
    // **********************
    // Standard DB Operations for Object
    // **********************
    
    public function insert($a_insert_node = false)
    {
        if ($a_insert_node == true) {
            $this->setSeqNodeId(parent::insert());
        }
        $sql = "INSERT INTO sahs_sc13_seq_mapinfo (seqnodeid,targetobjectiveid,readsatisfiedstatus,readnormalizedmeasure,writesatisfiedstatus,writemormalizedmeasure)" .
                " values(" .
                $this->db->quote($this->seqNodeId, "integer") . "," .
                $this->db->quote($this->targetObjectiveID, "text") . "," .
                $this->db->quote($this->readSatisfiedStatus, "integer") . "," .
                $this->db->quote($this->readNormalizedMeasure, "integer") . "," .
                $this->db->quote($this->writeSatisfiedStatus, "integer") . "," .
                $this->db->quote($this->writeNormalizedMeasure, "integer") . ");";
        $result = $this->db->manipulate($sql);
        return true;
    }
    
    public static function fetchmapInfo($a_seq_node_id)
    {
        global $DIC;

        $ilDB = $DIC->database();
        
        $sql = "SELECT *  FROM sahs_sc13_seq_mapinfo WHERE seqnodeid=" .
            $ilDB->quote($a_seq_node_id, "integer") . ";";
        $result = $ilDB->query($sql);
        $row = $ilDB->fetchAssoc($result);
        $obj = new ilSCORM2004MapInfo();
        $obj->setSeqNodeId($row["seqnodeid"]);
        $obj->setTargetObjectiveID($row["targetobjectiveid"]);
        $obj->setReadSatisfiedStatus($row["readsatisfiedstatus"]);
        $obj->setReadNormalizedMeasure($row["readnormalizedmeasure"]);
        $obj->setWriteSatisfiedStatus($row["writesatisfiedstatus"]);
        $obj->setWriteNormalizedMeasure($row["writemormalizedmeasure"]);
        /*foreach ($row as $key=>$value) {
            $method = "set".ucwords($key);
            if (method_exists($obj,$method)) {$obj->$method($value);}
        }*/
        return $obj;
    }
}
