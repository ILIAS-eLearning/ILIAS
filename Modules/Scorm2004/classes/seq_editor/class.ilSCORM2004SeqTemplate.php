<?php

/* Copyright (c) 1998-2011 ILIAS open source, Extended GPL, see docs/LICENSE */

require_once("./Modules/Scorm2004/classes/seq_editor/class.ilSCORM2004Item.php");
require_once("./Modules/Scorm2004/classes/class.ilSCORM2004Chapter.php");
require_once("./Modules/Scorm2004/classes/class.ilSCORM2004SeqChapter.php");
require_once("./Modules/Scorm2004/classes/class.ilSCORM2004Sco.php");

/**
* Class ilSCORM2004Chapter
*
* Sequencing Template class for SCORM 2004 Editing
*
* @author Hendrik Holtmann <holtmann@me.com>
* @version $Id$
*
* @ingroup ModulesScorm2004
*/
class ilSCORM2004SeqTemplate extends ilSCORM2004SeqNode
{
    const SEQ_TEMPLATE_DIR = './Modules/Scorm2004/templates/editor/seq_templates';
    const SEQ_TEMPLATE_XSL = './Modules/Scorm2004/templates/editor/seq_templates/xsl/normalize_seqtemplate.xsl';
    const SEQ_TEMPLATE_XSD = './Modules/Scorm2004/templates/editor/seq_templates/xsd/seq_template.xsd';
    
    
    private $template;
    private $template_file;
    private $normalized_template;
    private $tree_node;
    private $diagnostic = array();
    private $parentchapter = true;
    private $importId;
        
    //db fields
    private $id = null;
    private $seqNodeId = null;
    private $sahs_sc13_treeId = null;
    private $importid;
    
    public function __construct($a_identifier)
    {
        global $DIC;

        $this->db = $DIC->database();
        
        parent::__construct();
        $this->setNodeName("seqtemplate");
        
        if ($a_identifier==null) {
            return;
        }
        $t_file = self::getFileNameForIdentifier($a_identifier);
                
        $this->template = new DOMDocument;
        $this->template->async = false;
        

        //look for template in lang_dir, fallback to en
        $test = self::SEQ_TEMPLATE_DIR . "/" . $_SESSION["lang"] . "/" . $t_file;
        if (file_exists($test)) {
            $this->template_file = $test;
        } else {
            $this->template_file = self::SEQ_TEMPLATE_DIR . "/en/" . $t_file;
        }
        if (!@$this->template->load($this->template_file)) {
            $this->diagnostic[] = 'Template not wellformed';
            $test = $this->template->saveXML();
            return false;
        } else {
            return true;
        }
    }
    
    
    /**
        * function getMetadataProperties
        *
        * @return hash of metadata for the template
        * @author Hendrik Holtmann
        **/
    public function getMetadataProperties()
    {
        $array_metad = array();
        $metadata = $this->template->getElementsByTagName("metadata");
        $nodes = $metadata->item(0)->childNodes;
        for ($i = 0; $i < $nodes->length; $i++) {
            $curNode = $nodes->item($i);
            $array_metad[$curNode->localName] = $curNode->nodeValue;
        }
        return $array_metad;
    }
    
    
    public static function availableTemplates()
    {
        global $DIC;

        $ilDB = $DIC->database();
        $arr_templates = array();
        $query = "SELECT * FROM sahs_sc13_seq_templts ORDER BY identifier";
        $result = $ilDB->query($query);
        while ($row = $ilDB->fetchAssoc($result)) {
            if ($row['identifier']!="pretestpost") { //temporarily deactivated
                array_push($arr_templates, new ilScorm2004SeqTemplate($row['identifier']));
            }
        }
        return $arr_templates;
    }
    
    
    /**
        * function getIdentifier
        *
        * @return get identifier for template
        * @author Hendrik Holtmann
        **/
    public function getIdentifier()
    {
        $seqtemplate = $this->template->getElementsByTagName("seqTemplate");
        return $seqtemplate->item(0)->getAttribute("identifier");
        ;
    }
    
    
    public function insertTemplateForObjectAtParent($a_object, $a_parent, $a_target)
    {
        $this->importId = uniqid();
        return $this->importTemplate($a_target, $a_object, $a_parent, $this->template->getElementsByTagName("item")->item(0));
    }
    

    
    /**
     * function import Template
     *
     * @return success of import
     * @author Hendrik Holtmann
     */
    public function importTemplate($a_target, $a_object, $a_parent, $node)
    {
        $ilDB = $this->db;
                
        switch ($node->getAttribute('type')) {
            case "lesson":
                if ($this->parentchapter==true) {
                    $chap = new ilSCORM2004SeqChapter($a_object);
                } else {
                    $chap = new ilSCORM2004Chapter($a_object);
                }
                $chap->setTitle($node->getElementsByTagName("title")->item(0)->nodeValue);
                $chap->setSLMId($a_object->getId());
                $chap->create();
                $this->sahs_sc13_treeId = $chap->getId();
                ilSCORM2004Node::putInTree($chap, $a_parent, $a_target);
                $a_parent = $this->sahs_sc13_treeId;
                if ($this->parentchapter==true) {
                    $ilDB->manipulate("INSERT INTO sahs_sc13_seq_assign (identifier, sahs_sc13_tree_node_id) VALUES " .
                        "(" . $ilDB->quote($this->getIdentifier(), "text") . "," .
                        $ilDB->quote($this->sahs_sc13_treeId, "integer") . ")");
                    $this->parentchapter = false;
                }
                $new_id = $chap->getId();
                break;
            case "sco":
                $sco = new ilSCORM2004Sco($a_object);
                $sco->setTitle($node->getElementsByTagName("title")->item(0)->nodeValue);
                $sco->setSLMId($a_object->getId());
                $sco->create(false, true);
                $this->sahs_sc13_treeId = $sco->getId();
                ilSCORM2004Node::putInTree($sco, $a_parent, $a_target);
                $new_id = $sco->getId();
                break;
        }
        
        $seq_node = $node->getElementsByTagName("sequencing")->item(0);
        
        $obj_node = $seq_node->getElementsByTagName("objectives")->item(0);
        //addtitle
        if ($obj_node) {
            foreach ($obj_node->childNodes as $objchild) {
                if ($objchild->nodeName === "objective" || $objchild->nodeName === "primaryObjective") {
                    $title = $objchild->getAttribute('objectiveID');
                    $objchild->setAttribute("title", $title);
                    //					$i++;
                }
            }
        }
        
        $seq_item = new ilSCORM2004Item();
        $seq_item->setTreeNodeId($this->sahs_sc13_treeId);
        $seq_item->setImportid($this->importId);
        $seq_item->setNocopy($seq_node->getAttribute('nocopy'));
        $seq_item->setNodelete($seq_node->getAttribute('nodelete'));
        $seq_item->setNomove($seq_node->getAttribute('nomove'));
        
        $seq_doc = new DOMDocument();
        $toadd = $seq_doc->importNode($seq_node, true);
        $seq_doc->appendChild($toadd);
        
        //generate Unique ObjectiveIDs for context

        //@targetObjectiveID
        $xpath_obj = new DOMXPath($seq_doc);
        
        $found_nodes = $xpath_obj->query('//@objectiveID | //@referencedObjective | //@targetObjectiveID');
        for ($i=0; $i<$found_nodes->length; $i++) {
            $element = null;
            $val = $found_nodes->item($i)->value;
            $uid = $this->sahs_sc13_treeId;
            if ($found_nodes->item($i)->name == "targetObjectiveID") {
                $uid = $this->importId;
            }
            $val = strtolower(preg_replace('/ +/', '_', $val) . "_" . $uid);
            $element = $found_nodes->item($i)->ownerElement;
            $element->setAttribute($found_nodes->item($i)->name, $val);
        }
        
        $seq_item->setDom($seq_doc);
        
        $seq_item->insert();
        
        foreach ($node->childNodes as $child) {
            if ($child->nodeName === "item") {
                $this->importTemplate($a_target, $a_object, $a_parent, $child);
            }
        }
        
        return $new_id;
    }
    

    
    private function generateObjIds($a_dom)
    {
    }
    
    // **********************
    // Standard DB Operations for Object
    // **********************
    
    public function insert($a_insert_node = false)
    {
        if ($a_insert_node==true) {
            $this->setSeqNodeId(parent::insert());
        }
        $sql = "INSERT INTO sahs_sc13_seq_templ (seqnodeid,id)" .
                " values(" .
                $this->db->quote($this->seqNodeId, "integer") . "," .
                $this->db->quote($this->id, "text") . ");";
        $result = $this->db->manipulate($sql);
        return true;
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
    
    //static functions
    
    public static function getFileNameForIdentifier($a_identifier)
    {
        global $DIC;

        $ilDB = $DIC->database();
        $query = "SELECT * FROM sahs_sc13_seq_templts WHERE identifier = " .
            $ilDB->quote($a_identifier, "text");
        $obj_set = $ilDB->query($query);
        $obj_rec = $ilDB->fetchAssoc($obj_set);
        return $obj_rec["fileName"];	// fixed, switch to all lowercase fields and tables in the future for mdb2 compliance
        return $obj_rec["filename"];
    }
    
    public static function templateForChapter($a_chapter_id)
    {
        global $DIC;

        $ilDB = $DIC->database();
        $template = null;
        $query = "SELECT * FROM sahs_sc13_seq_assign WHERE sahs_sc13_tree_node_id = " .
            $ilDB->quote($a_chapter_id, "integer");
        $obj_set = $ilDB->query($query);
        $obj_rec = $ilDB->fetchAssoc($obj_set);
        if ($obj_rec['identifier']) {
            $template = new ilScorm2004SeqTemplate($obj_rec['identifier']);
        }
        return $template;
    }
}
