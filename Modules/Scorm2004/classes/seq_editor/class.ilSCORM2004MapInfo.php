<?php declare(strict_types=1);

/******************************************************************************
 *
 * This file is part of ILIAS, a powerful learning management system.
 *
 * ILIAS is licensed with the GPL-3.0, you should have received a copy
 * of said license along with the source code.
 *
 * If this is not the case or you just want to try ILIAS, you'll find
 * us at:
 *      https://www.ilias.de
 *      https://github.com/ILIAS-eLearning
 *
 *****************************************************************************/
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
    private bool $readSatisfiedStatus = true;
    private bool $readNormalizedMeasure = true;
    private bool $writeSatisfiedStatus = false;
    private bool $writeNormalizedMeasure = false;
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
    
    public function getReadSatisfiedStatus() : bool
    {
        return $this->readSatisfiedStatus;
    }
    
    public function getReadNormalizedMeasure() : bool
    {
        return $this->readNormalizedMeasure;
    }
    
    public function getWriteSatisfiedStatus() : bool
    {
        return $this->writeSatisfiedStatus;
    }
    
    public function getWriteNormalizedMeasure() : bool
    {
        return $this->writeNormalizedMeasure;
    }
    
    // **********************
    // Setter METHODS
    // **********************

    public function setSeqNodeId($a_seqnodeid) : void
    {
        $this->seqNodeId = $a_seqnodeid;
    }
    
    public function setId($a_id) : void
    {
        $this->id = $a_id;
    }
    
    public function setTargetObjectiveID($a_id) : void
    {
        $this->targetObjectiveID = $a_id;
    }
    
    public function setReadSatisfiedStatus(bool $a_status) : void
    {
        $this->readSatisfiedStatus = $a_status;
    }
    
    public function setReadNormalizedMeasure(bool $a_measure) : void
    {
        $this->readNormalizedMeasure = $a_measure;
    }
    
    public function setWriteSatisfiedStatus(bool $a_status) : void
    {
        $this->writeSatisfiedStatus = $a_status ;
    }
    
    public function setWriteNormalizedMeasure(bool $a_measure) : void
    {
        $this->writeNormalizedMeasure = $a_measure;
    }
    
    
    // **********************
    // Standard DB Operations for Object
    // **********************
    
    public function insert($a_insert_node = false) : bool
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
    
    public static function fetchmapInfo($a_seq_node_id) : \ilSCORM2004MapInfo
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
