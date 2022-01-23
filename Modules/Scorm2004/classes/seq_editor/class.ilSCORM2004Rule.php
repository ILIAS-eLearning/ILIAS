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
* Class ilSCORM2004Rule
*
* Sequencing Template class for SCORM 2004 Editing
*
* @author Hendrik Holtmann <holtmann@me.com>
* @version $Id$
*
* @ingroup ModulesScorm2004
*/
class ilSCORM2004Rule extends ilSCORM2004SeqNode
{
        
    //db fields
    private $id = null;
    private $seqNodeId = null;
    private $type = null;
    private bool $action = true;
    /**
    * Constructor
    * @access	public
    */
    public function __construct()
    {
        parent::__construct();
        $this->setNodeName("rule");
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
    
    public function getType()
    {
        return $this->type;
    }
    
    public function getAction() : bool
    {
        return $this->action;
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
    
    public function setType($a_type) : void
    {
        $this->type = $a_type;
    }
    
    public function setAction(bool $a_action) : void
    {
        $this->action = $a_action;
    }
    
    
    // **********************
    // Standard DB Operations for Object
    // **********************
    
    public function insert($a_insert_node = false) : bool
    {
        if ($a_insert_node == true) {
            $this->setSeqNodeId(parent::insert());
        }
        $sql = "INSERT INTO sahs_sc13_seq_rule (seqnodeid,type,action)" .
                " values(" .
                $this->db->quote($this->seqNodeId, "integer") . "," .
                $this->db->quote($this->type, "text") . "," .
                $this->db->quote($this->action, "text") . ");";
        $result = $this->db->manipulate($sql);
        return true;
    }
}
