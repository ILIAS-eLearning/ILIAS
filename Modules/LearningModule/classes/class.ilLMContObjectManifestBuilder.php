<?php
/* Copyright (c) 1998-2010 ILIAS open source, Extended GPL, see docs/LICENSE */

require_once("./Modules/LearningModule/classes/class.ilObjContentObject.php");

/**
* Content Object (ILIAS native learning module / digilib book)
* Manifest export class
*
* @author Alex Killing <alex.killing@gmx.de>
*
* @version $Id$
*
* @ingroup ModulesIliasLearningModule
*/
class ilLMContObjectManifestBuilder
{
    public $db;			// database object
    public $cont_obj;		// content object (learning module | digilib book)
    public $inst_id;		// installation id

    /**
    * Constructor
    * @access	public
    */
    public function __construct($a_cont_obj)
    {
        global $DIC;

        $ilDB = $DIC->database();

        $this->cont_obj = $a_cont_obj;

        $this->db = $ilDB;

        $this->inst_id = IL_INST_ID;
    }

    /**
    * build manifest structure
    */
    public function buildManifest()
    {
        require_once("./Services/Xml/classes/class.ilXmlWriter.php");

        $this->writer = new ilXmlWriter;

        // set xml header
        $this->writer->xmlHeader();

        // manifest start tag
        $attrs = array();
        $attrs["identifier"] = "il_" . IL_INST_ID . "_" . "man" .
            "_" . $this->cont_obj->getId();
        $attrs["version"] = "";
        $attrs["xmlns:xsi"] = "http://www.w3.org/2001/XMLSchema-instance";
        $attrs["xsi:schemaLocation"] = "http://www.imsproject.org/xsd/imscp_rootv1p1p2" .
            " imscp_rootv1p1p2.xsd" .
            " http://www.imsglobal.org/xsd/imsmd_rootv1p2p1" .
            " imsmd_rootv1p2p1.xsd" .
            " http://www.adlnet.org/xsd/adlcp_rootv1p2" .
            " adlcp_rootv1p2.xsd";
        $attrs["xmlns:imsmd"] = "http://www.imsproject.org/xsd/imsmd_rootv1p2p1";
        $attrs["xmlns:adlcp"] = "http://www.adlnet.org/xsd/adlcp_rootv1p2";
        $attrs["xmlns"] = "http://www.imsproject.org/xsd/imscp_rootv1p1p2";
        $this->writer->xmlStartTag("manifest", $attrs);

        // organizations start tag
        $attrs = array();
        $this->writer->xmlStartTag("organizations", $attrs);

        // organization start tag
        $attrs = array();
        $attrs["identifier"] = "il_" . IL_INST_ID . "_" . $this->cont_obj->getType() .
            "_" . $this->cont_obj->getId();
        $attrs["structure"] = "hierarchical";
        $this->writer->xmlStartTag("organization", $attrs);
        
        // title element
        $attrs = array();
        $this->writer->xmlElement("title", $attrs, $this->cont_obj->getTitle());
        
        // write item hierarchy
        $this->writeItemHierarchy();

        // organization end tag
        $this->writer->xmlEndTag("organization");
        
        // organizations end tag
        $this->writer->xmlEndTag("organizations");
        
        // resources start tag
        $attrs = array();
        $this->writer->xmlStartTag("resources", $attrs);
        
        // write resources
        $this->writeResources();

        // resources end tag
        $this->writer->xmlEndTag("resources");
        
        // manifest end tag
        $this->writer->xmlEndTag("manifest");
        
        // write manifest file
        //$this->xml->xmlDumpFile($this->export_dir."/".$this->subdir."/".$this->filename
        //	, false);
            
        // destroy writer object
        $this->writer->_XmlWriter;
    }
    
    /**
    * dump manifest file into directory
    */
    public function dump($a_target_dir)
    {
        $this->writer->xmlDumpFile($a_target_dir . "/imsmanifest.xml", false);
    }
    
    /**
    * write item hierarchy
    *
    * this first version only writes one item for the whole learning module
    */
    public function writeItemHierarchy()
    {
        // start item
        $attrs = array();
        $attrs["identifier"] = "INDEX";
        $attrs["identifierref"] = "RINDEX";
        $this->writer->xmlStartTag("item", $attrs);
        
        // title element
        $attrs = array();
        $this->writer->xmlElement("title", $attrs, $this->cont_obj->getTitle());
        
        // end item
        $this->writer->xmlEndTag("item");
    }
    
    
    /**
    * write resources
    *
    * this first version only writes one resource for the whole learning module
    */
    public function writeResources()
    {
        $attrs = array();
        $attrs["identifier"] = "RINDEX";
        $attrs["type"] = "webcontent";
        $attrs["adlcp:scormtype"] = "asset";
        $attrs["href"] = "res/index.html";
        $this->writer->xmlElement("resource", $attrs, "");
    }
}
