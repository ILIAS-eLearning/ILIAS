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
 * Class ilSCORM2004Objective
 *
 * Sequencing Template class for SCORM 2004 Editing
 *
 * @author Hendrik Holtmann <holtmann@me.com>
 */
class ilSCORM2004Objective extends ilSCORM2004Item
{
    
    //db fields
    
    private $node = null;
    /**
     * @var null
     */
    private $id = null;  //userd as ID
    private $objectiveID = null; //used as title
    
    //not supported in GUI yet
    private float $minNormalizedMeasure = 1.0;
    private bool $primary = true;
    private bool $satisfiedByMeasure = false;
    
    //mappings
    private array $mappings = array();
    
    /**
    * Constructor
    * @access	public
    */
    public function __construct($a_treeid = null, $a_obj_id = null)
    {
        parent::__construct($a_treeid);
        
        if ($a_obj_id != null && $a_treeid != null) {
            $xpath_obj = new DOMXPath($this->dom);
            $obj_node_list = $xpath_obj->query('//objective[@objectiveID = "' . $a_obj_id . '"] | ' .
                                               '//primaryObjective[@objectiveID = "' . $a_obj_id . '"]');
            $this->setNode($obj_node_list->item(0));
        } else {
            if ($a_obj_id == null && $a_treeid != null) {
                $obj_con = $this->dom->createElement("objectives");
                $obj = $this->dom->createElement("primaryObjective");
                $root = $this->dom->getElementsByTagName("sequencing")->item(0);
                $obj_con->appendChild($obj);
                $root->appendChild($obj_con);
                $this->node = $this->dom->getElementsByTagName("primaryObjective")->item(0);
            }
        }
    }
    
    
    
    // **********************
    // GETTER METHODS
    // **********************
    
    public function getId()
    {
        return $this->node->getAttribute("objectiveID");
    }
    
    public function getMinNormalizedMeasure()
    {
        return $this->node->getAttribute("minNormalizedMeasure");
    }
    
    public function getObjectiveID()
    {
        return $this->node->getAttribute("title");
    }
    
    public function getPrimary() : bool
    {
        return $this->primary;
    }
    
    public function getSatisfiedByMeasure()
    {
        return $this->node->getAttribute("satisfiedByMeasure");
    }
    
    /**
     * @return mixed[]
     */
    public function getMappings() : array
    {
        return $this->mappings;
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
        $this->node->setAttribute("objectiveID", $a_id);
    }
    
    
    public function setMinNormalizedMeasure($a_minmeasure) : void
    {
        $this->node->setAttribute("minNormalizedMeasure", $a_minmeasure);
    }
    
    public function setObjectiveID($a_objectiveid) : void
    {
        $this->node->setAttribute("title", $a_objectiveid);
    }
    
    public function setPrimary(bool $a_primary) : void
    {
        $this->primary = $a_primary;
    }
    
    public function setSatisfiedByMeasure($a_satisfied) : void
    {
        $this->node->setAttribute("satisfiedByMeasure", $a_satisfied);
    }
    
    /**
     * @param mixed[] $a_mappings
     */
    public function setMappings(array $a_mappings) : void
    {
        $this->mappings = $a_mappings;
    }
    
    public function setNode($a_node) : void
    {
        $this->node = $a_node;
    }
    
    public function setDom($a_dom) : void
    {
        $this->dom = $a_dom;
    }
    
    // **********************
    // Standard  Operations for Object
    // **********************

    
    public function updateObjective() : void
    {
        parent::update();
    }
    
    /**
     * @return \ilSCORM2004Objective[]
     */
    public static function fetchAllObjectives($a_slm_object, $a_tree_node_id) : array
    {
        global $DIC;

        $ilDB = $DIC->database();
        
        $objectives = array();
        $seq_item = new ilSCORM2004Item($a_tree_node_id);
        $xpath_obj = new DOMXPath($seq_item->dom);
        $obj_node_list = $xpath_obj->query('//objective | //primaryObjective');
        for ($i = 0;$i < $obj_node_list->length;$i++) {
            $obj = new ilSCORM2004Objective();
            $obj->setNode($obj_node_list->item($i));
            $mapping_node_list = $xpath_obj->query('//objective | //primaryObjective');
            //check for mapping
            array_push($objectives, $obj);
        }
        return $objectives;
    }
}
