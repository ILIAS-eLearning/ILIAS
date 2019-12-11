<?php

/* Copyright (c) 1998-2011 ILIAS open source, Extended GPL, see docs/LICENSE */

require_once("./Modules/Scorm2004/classes/seq_editor/class.ilSCORM2004SeqNode.php");

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
class ilSCORM2004Item
{
    /**
     * @var ilDB
     */
    protected $db;

    //db fields
    private $id = null;
    private $seqNodeId = null;
    private $treeNodeId = null;
    private $sequencingId = null;
    private $nocopy = false;
    private $nodelete = false;
    private $nomove = false;
    private $importId = null;
    private $seqXml = null;
    private $importSeqXml = null;
    private $rootLevel = false;
        
    protected $dom = null;

    /**
     * @var ilLogger
     */
    protected $log;

    /**
     * Constructor
     * @access	public
     */
    public function __construct($a_treeid = null, $a_rootlevel = false)
    {
        global $DIC;

        $this->db = $DIC->database();
        $this->log = ilLoggerFactory::getLogger("sc13");

        //different handling for organization level
        $this->rootLevel = $a_rootlevel;
        
        if ($a_treeid != null) {
            $this->treeNodeId = $a_treeid;
            $this->loadItem();
            $this->dom = new DOMDocument();
            $this->initDom();
        }
    }
    
    // **********************
    // GETTER METHODS
    // **********************
    
    public function getSeqNodeId()
    {
        return $this->seqNodeId;
    }
    
    public function getTreeNodeId()
    {
        return $this->treeNodeId;
    }
    
    
    public function getSequencingId()
    {
        return $this->sequencingId;
    }
    
    public function getImportId()
    {
        return $this->importId;
    }
    public function getNocopy()
    {
        return $this->nocopy;
    }
    
    public function getNodelete()
    {
        return $this->nodelete;
    }
    
    public function getNomove()
    {
        return $this->nomove;
    }
    
    public function getSeqXml()
    {
        return $this->seqXml;
    }
    
    public function getRoolLevel()
    {
        return $this->rootLevel;
    }
        
    /**
     * Get import seq xml
     *
     * @return string xml
     */
    public function getImportSeqXml()
    {
        return $this->importSeqXml;
    }
    
    // **********************
    // Setter METHODS
    // **********************

    /**
     * Set import seq xml
     *
     * @param string $a_val xml
     */
    public function setImportSeqXml($a_val)
    {
        $this->importSeqXml = $a_val;
    }

    public function setSeqNodeId($a_seqnodeid)
    {
        $this->seqNodeId = $a_seqnodeid;
    }
    
    public function setTreeNodeId($a_tree_node)
    {
        $this->treeNodeId = $a_tree_node;
    }
    
    public function setSequencingId($a_seq_id)
    {
        $this->sequencingId = $a_seq_id;
    }
    
    public function setNocopy($a_nocopy)
    {
        $this->nocopy = $a_nocopy;
    }
    
    public function setNodelete($a_nodelete)
    {
        $this->nodelete = $a_nodelete ;
    }
    
    public function setNomove($a_nomove)
    {
        $this->nomove = $a_nomove;
    }
    
    public function setImportId($a_importid)
    {
        $this->importid = $a_importid;
    }
    
    public function setSeqXml($a_seqxml)
    {
        $this->log->debug("seq xml: " . $a_seqxml);
        $this->seqXml = $a_seqxml;
    }
    
    public function setDom($a_dom)
    {
        $this->dom = $a_dom;
    }
    
    public function setRootLevel($a_rootlevel)
    {
        $this->rootLevel = $a_rootlevel;
    }
    
    public static function getAllowedActions($a_node_id)
    {
        global $DIC;

        $ilDB = $DIC->database();
        $ilLog = $DIC["ilLog"];
        $query = "SELECT * FROM sahs_sc13_seq_item WHERE sahs_sc13_tree_node_id = " .
            $ilDB->quote($a_node_id, "integer") .
            " AND rootlevel = " . $ilDB->quote(false, "integer");
        $obj_set = $ilDB->query($query);
        $obj_rec = $obj_set->fetchRow(ilDBConstants::FETCHMODE_ASSOC);
        return array("copy"=>!$obj_rec['nocopy'],"move"=>!$obj_rec['nomove'],"delete"=>!$obj_rec['nodelete']);
    }
    
    /**
     * Init dom
     */
    public function initDom()
    {
        if ($this->getSeqXml() != "") {
            $this->dom->loadXML($this->getSeqXml());
        } else {
            $this->setDefaultXml();
        }
    }

    /**
     * Set default xml
     *
     * @param bool $a_def_control_mode
     */
    public function setDefaultXml($a_def_control_mode= false)
    {
        while ($this->dom->hasChildNodes()) {
            $this->dom->removeChild($this->dom->childNodes->item(0));
        }

        $element = $this->dom->createElement('sequencing');
        $this->dom->appendChild($element);

        if ($a_def_control_mode) {
            $cm = $this->dom->createElement('controlMode');
            $cm->setAttribute("flow", "true");
            $cm->setAttribute("choice", "true");
            $cm->setAttribute("forwardOnly", "false");
            $element->appendChild($cm);
        }
        $this->setSeqXml($this->dom->saveXML());
    }
    
    
    /**
     * Get sequencing information for export (use imsss namespace prefix)
     *
     * @return string sequencing xml
     */
    public function exportAsXML($add_prefix = true)
    {
        // remove titles
        // @todo: the objectives (titles) text should be stored outside of
        // the sequencing information in the future
        $xpath_obj = new DOMXPath($this->dom);
        $obj_node_list = $xpath_obj->query('//objective | //primaryObjective');
        for ($i=0;$i<$obj_node_list->length;$i++) {
            $obj_node_list->item($i)->removeAttribute("title");
        }
        $output = $this->dom->saveXML();
        
        $output = preg_replace('/\<\?xml version="1.0"\?\>/', '', $output);
        if ($add_prefix) {
            $output = preg_replace('/(<)([a-z]+|[A-Z]+)/', '<imsss:$2', $output);
            $output = preg_replace('/(<\/)([a-z]+|[A-Z]+)/', '</imsss:$2', $output);
        }
        $output = preg_replace('/\n/', '', $output);

        return $output;
    }
    
    /**
     * Read data from DB into object
     */
    public function loadItem()
    {
        $ilDB = $this->db;
        $query = "SELECT * FROM sahs_sc13_seq_item WHERE (sahs_sc13_tree_node_id = " . $ilDB->quote($this->treeNodeId, "integer") .
            " AND rootlevel =" . $ilDB->quote($this->rootLevel, "integer") . ")";
        $obj_set = $ilDB->query($query);
        $obj_rec = $ilDB->fetchAssoc($obj_set);
        $this->seqXml = $obj_rec['seqxml'];
        $this->importSeqXml = $obj_rec['importseqxml'];
        $this->importId = $obj_rec['importid'];
        $this->nocopy =  $obj_rec['nocopy'];
        $this->nomove = $obj_rec['nomove'];
        $this->nodelete = $obj_rec['nodelete'];
    }
    
    /**
     * Update item
     */
    public function update()
    {
        $this->insert();
    }
    
    /**
     * Delete item
     */
    public function delete($a_insert_node = false)
    {
        $ilDB = $this->db;
        
        $query = "DELETE FROM sahs_sc13_seq_item" . " WHERE (sahs_sc13_tree_node_id = " . $ilDB->quote($this->treeNodeId, "integer") .
                  " AND rootlevel=" . $ilDB->quote($this->rootLevel, "integer") . ")";
        $obj_set = $ilDB->manipulate($query);
    }
    
    /**
     * Insert/replace sequencing item in db
     */
    public function insert($import = false)
    {
        $ilDB = $this->db;

        $ilDB->replace(
            "sahs_sc13_seq_item",
            array("sahs_sc13_tree_node_id" => array("integer", $this->treeNodeId),
                "rootlevel" => array("integer", $this->rootLevel)),
            array(
                "importid" => array("text", $this->importId),
                "seqnodeid" => array("integer", (int) $this->seqNodeId),
                "sequencingid" => array("text", $this->sequencingId),
                "nocopy" => array("integer", $this->nocopy),
                "nodelete" => array("integer", $this->nodelete),
                "nomove" => array("integer", $this->nomove),
                "seqxml" => array("clob", $this->dom->saveXML()),
                "importseqxml" => array("clob", $this->getImportSeqXml())
                )
        );
        return true;
    }
}
