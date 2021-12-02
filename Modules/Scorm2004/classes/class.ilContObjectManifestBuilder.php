<?php

/* Copyright (c) 1998-2021 ILIAS open source, GPLv3, see LICENSE */

/**
 * Scorm 2004 Content Object Manifest export class
 *
 * @author Alexander Killing <killing@leifos.de>
 */
class ilContObjectManifestBuilder
{
    public $db;			// database object
    public $cont_obj;		// content object (learning module | digilib book)
    public $inst_id;		// installation id
    public $writer;
    public $version;
    /**
     * Constructor
     * @access	public
     */
    public function __construct(&$a_cont_obj)
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
    public function buildManifest($ver, $revision = null)
    {
        $this->version = $ver;
        $this->writer = new ilXmlWriter;

        // set xml header
        $this->writer->xmlHeader();

        // manifest start tag
        $attrs = array();
        $attrs["identifier"] = "il_" . IL_INST_ID . "_" . $this->cont_obj->getType() . "_m_" . $this->cont_obj->getId();
        switch ($this->version) {
            case "2004":
                $attrs["xmlns:imsss"] = "http://www.imsglobal.org/xsd/imsss";
                $attrs["xmlns:adlseq"] = "http://www.adlnet.org/xsd/adlseq_v1p3";
                $attrs["xmlns:adlnav"] = "http://www.adlnet.org/xsd/adlnav_v1p3";
                $attrs["xmlns:xsi"] = "http://www.w3.org/2001/XMLSchema-instance";
                $attrs["xmlns:adlcp"] = "http://www.adlnet.org/xsd/adlcp_v1p3";
                $attrs["xmlns"] = "http://www.imsglobal.org/xsd/imscp_v1p1";
                $attrs["xsi:schemaLocation"] = "http://www.imsglobal.org/xsd/imscp_v1p1 imscp_v1p1.xsd http://www.adlnet.org/xsd/adlcp_v1p3 adlcp_v1p3.xsd http://www.imsglobal.org/xsd/imsss imsss_v1p0.xsd http://www.adlnet.org/xsd/adlseq_v1p3 adlseq_v1p3.xsd http://www.adlnet.org/xsd/adlnav_v1p3 adlnav_v1p3.xsd";
                $attrs["version"] = "2004 " . $revision . " Edition";
                break;
            case "12":
                $attrs["xmlns"] = "http://www.imsproject.org/xsd/imscp_rootv1p1p2";
                $attrs["xmlns:adlcp"] = "http://www.adlnet.org/xsd/adlcp_rootv1p2";
                $attrs["xmlns:xsi"] = "http://www.w3.org/2001/XMLSchema-instance";
                $attrs["xsi:schemaLocation"] = "http://www.imsproject.org/xsd/imscp_rootv1p1p2 imscp_rootv1p1p2.xsd http://www.imsglobal.org/xsd/imsmd_rootv1p2p1 imsmd_rootv1p2p1.xsd http://www.adlnet.org/xsd/adlcp_rootv1p2 adlcp_rootv1p2.xsd";
                $attrs["version"] = "1.1";
                break;
        }
        $this->writer->xmlStartTag("manifest", $attrs);
        
        if ($this->version == "2004") {
            $this->writer->xmlStartTag("metadata");
            $this->writer->xmlElement("schema", null, "ADL SCORM");
            $this->writer->xmlElement("schemaversion", null, "2004 " . $revision . " Edition");
            $this->writer->xmlElement("adlcp:location", null, "indexMD.xml");
            $this->writer->xmlEndTag("metadata");
        }
        // organizations start tag
        $attrs = array();
        if ($this->version == "2004") {
            $attrs["xmlns:imscp"] = "http://www.imsglobal.org/xsd/imscp_v1p1";
        }
        $attrs["default"] = "il_" . IL_INST_ID . "_" . $this->cont_obj->getType() . "_" . $this->cont_obj->getId();
        $this->writer->xmlStartTag("organizations", $attrs);

        // organization start tag
        $attrs = array();
        $attrs["identifier"] = "il_" . IL_INST_ID . "_" . $this->cont_obj->getType() . "_" . $this->cont_obj->getId();
        $attrs["structure"] = "hierarchical";
        $this->writer->xmlStartTag("organization", $attrs);

        // title element
        $attrs = array();
        $this->writer->xmlElement("title", $attrs, $this->cont_obj->getTitle());

        // write item hierarchy
        $tree = new ilSCORM2004Tree($this->cont_obj->getId());

        $this->writeItemHierarchyRec($tree, $tree->getRootId());
        
        
        // sequencing information
        if ($this->version == "2004") {
            $seq_item = new ilSCORM2004Item($this->cont_obj->getId(), true);
            $this->writer->xmlData($this->writer->xmlFormatData($seq_item->exportAsXML()), false, false);
        }

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
        $tree = new ilSCORM2004Tree($this->cont_obj->getId());
        $last_type = "";
        foreach ($tree->getFilteredSubTree($tree->getRootId(), array('page')) as $obj) {
            if ($obj['type'] == '') {
                continue;
            }
            $attrs = array();
            if ($obj['type'] != 'sco' && $last_type == "sco") {
                $this->writer->xmlEndTag("item");
            }
            $attrs["identifier"] = "il_" . IL_INST_ID . "_" . $obj['type'] . "_" . $obj['obj_id'];
            if ($obj['type'] == 'sco') {
                $attrs["identifierref"] = $attrs["identifier"] . "_ref";
            }
            $this->writer->xmlStartTag("item", $attrs);
            $attrs = array();
        
            $this->writer->xmlElement("title", $attrs, $obj['title']);

            if ($this->version == "2004") {
                // sequencing information
                $seq_item = new ilSCORM2004Item($obj['obj_id']);
                $this->writer->xmlData($this->writer->xmlFormatData($seq_item->exportAsXML()), false, false);
            }
            
            if ($obj['type'] == 'sco') {
                $this->writer->xmlEndTag("item");
            }
            $last_type = $obj['type'];
        }
        $this->writer->xmlEndTag("item");
    }

    /**
     * write item hierarchy (Recursive Style)
     *
     */
    public function writeItemHierarchyRec($tree, $a_parent_node)
    {
        foreach ($tree->getFilteredChilds(array('page'), $a_parent_node) as $obj) {
            if ($obj['type'] == '') {
                continue;
            }
            $attrs = array();
            $attrs["identifier"] = "il_" . IL_INST_ID . "_" . $obj['type'] . "_" . $obj['obj_id'];
            if ($obj['type'] == 'sco' || $obj['type'] == 'ass') {
                $attrs["identifierref"] = $attrs["identifier"] . "_ref";
            }
            $this->writer->xmlStartTag("item", $attrs);
            $attrs = array();
            $this->writer->xmlElement("title", $attrs, $obj['title']);
            
            if ($tree->getFilteredChilds(array('page'), $obj['obj_id'])) {
                $this->writeItemHierarchyRec($tree, $obj['obj_id']);
            }
            
            if ($this->version == "2004") {
                if ($obj['type'] == 'sco' || $obj['type'] == 'ass') {
                    $this->writer->xmlStartTag("metadata");
                    $this->writer->xmlElement("adlcp:location", null, $obj['obj_id'] . "/indexMD.xml");
                    $this->writer->xmlEndTag("metadata");
                }
                $seq_item = new ilSCORM2004Item($obj['obj_id']);
                $this->writer->xmlData($this->writer->xmlFormatData($seq_item->exportAsXML()), false, false);
            }
            $this->writer->xmlEndTag("item");
        }
    }

    /**
     * Create resource entries for the learning module "PKG" and all SCOS and Assets
     */
    public function writeResources()
    {
        $tree = new ilSCORM2004Tree($this->cont_obj->getId());

        foreach ($tree->getSubTree($tree->getNodeData($tree->getRootId()), true, array('sco', 'ass')) as $obj) {
            $attrs = array();
            $attrs["identifier"] = "il_" . IL_INST_ID . "_" . $obj['type'] . "_" . $obj['obj_id'] . "_ref";
            $attrs["type"] = "webcontent";
            if ($obj['type'] == "sco") {
                $attrs[($this->version == "2004"?"adlcp:scormType":"adlcp:scormtype")] = "sco";
            } else {
                $attrs[($this->version == "2004"?"adlcp:scormType":"adlcp:scormtype")] = "asset";
            }
            $attrs["href"] = "./" . $obj['obj_id'] . "/index.html";
            $this->writer->xmlStartTag("resource", $attrs, "");
            $this->writer->xmlElement("dependency", array("identifierref" => "il_" . IL_INST_ID . "_" . $obj['type'] . "_" . $obj['obj_id'] . 'ITSELF'), "");
            $this->writer->xmlElement("dependency", array("identifierref" => "il_" . IL_INST_ID . "_" . $obj['type'] . "_" . $obj['obj_id'] . 'OBJECTS'), "");
            $this->writer->xmlElement("dependency", array("identifierref" => "il_" . IL_INST_ID . "_" . $obj['type'] . "_" . $obj['obj_id'] . 'RESOURCES'), "");
            $this->writer->xmlElement("dependency", array("identifierref" => "il_" . IL_INST_ID . "_" . $obj['type'] . "_" . $obj['obj_id'] . 'FLAVOUR'), "");
            $this->writer->xmlEndTag("resource");
            
            $attrs = array();
            $attrs["identifier"] = "il_" . IL_INST_ID . "_" . $obj['type'] . "_" . $obj['obj_id'] . 'ITSELF';
            $attrs["type"] = "webcontent";
            $attrs[($this->version == "2004"?"adlcp:scormType":"adlcp:scormtype")] = "asset";
            $this->writer->xmlStartTag("resource", $attrs, "");
            $this->writer->xmlElement("file", array("href" => "./" . $obj['obj_id'] . "/index.xml"), "");
            $this->writer->xmlElement("file", array("href" => "./" . $obj['obj_id'] . "/ilias_co_3_7.dtd"), "");
            $this->writer->xmlElement("file", array("href" => "./" . $obj['obj_id'] . "/index.html"), "");
            $this->writer->xmlEndTag("resource");
            
            $attrs = array();
            $attrs["identifier"] = "il_" . IL_INST_ID . "_" . $obj['type'] . "_" . $obj['obj_id'] . 'RESOURCES';
            $attrs["type"] = "webcontent";
            $attrs[($this->version == "2004"?"adlcp:scormType":"adlcp:scormtype")] = "asset";
            $this->writer->xmlStartTag("resource", $attrs, "");
            $this->writer->xmlEndTag("resource");
            
            $attrs = array();
            $attrs["identifier"] = "il_" . IL_INST_ID . "_" . $obj['type'] . "_" . $obj['obj_id'] . 'FLAVOUR';
            $attrs["type"] = "webcontent";
            $attrs[($this->version == "2004"?"adlcp:scormType":"adlcp:scormtype")] = "asset";
            $this->writer->xmlStartTag("resource", $attrs, "");
            $this->writer->xmlElement("file", array("href" => "./" . $obj['obj_id'] . "/index.xml"), "");
            $this->writer->xmlElement("file", array("href" => "./" . $obj['obj_id'] . "/sco.xsl"), "");
            $this->writer->xmlElement("file", array("href" => "./" . $obj['obj_id'] . "/css/system.css"), "");
            $this->writer->xmlElement("file", array("href" => "./" . $obj['obj_id'] . "/css/style.css"), "");
            $this->writer->xmlElement("file", array("href" => "./" . $obj['obj_id'] . "/js/scorm.js"), "");
            $this->writer->xmlEndTag("resource");
            
            $attrs = array();
            $attrs["identifier"] = "il_" . IL_INST_ID . "_" . $obj['type'] . "_" . $obj['obj_id'] . 'OBJECTS';
            $attrs["type"] = "webcontent";
            $attrs[($this->version == "2004"?"adlcp:scormType":"adlcp:scormtype")] = "asset";
            $this->writer->xmlStartTag("resource", $attrs, "");
            
            $active_css = ilObjStyleSheet::getContentStylePath($this->cont_obj->getStyleSheetId());
            $active_css = explode('?', $active_css);
            $css = fread(fopen($active_css[0], 'r'), filesize($active_css[0]));
            preg_match_all("/url\(([^\)]*)\)/", $css, $css_files);
            $css_files = array_unique($css_files[1]);
            $currdir = getcwd();
            chdir(dirname($active_css[0]));
            foreach ($css_files as $fileref) {
                if (file_exists($fileref)) {
                    $this->writer->xmlElement("file", array("href" => "./" . $obj['obj_id'] . "/images/" . basename($fileref)), "");
                }
            }
            chdir($currdir);

            $pagetree = new ilSCORM2004Tree($this->cont_obj->getId());

            foreach ($pagetree->getSubTree($pagetree->getNodeData($obj['obj_id']), false, ['page']) as $page) {
                $page_obj = new ilSCORM2004Page($page);
                $page_obj->buildDom();
                $mob_ids = $page_obj->collectMediaObjects(false);
                foreach ($mob_ids as $mob_id) {
                    if ($mob_id > 0 && ilObject::_exists($mob_id)) {
                        $media_obj = new ilObjMediaObject($mob_id);
                        $media_obj = $media_obj->getMediaItem("Standard");
                        if ($media_obj != null && $media_obj->getLocationType() == "LocalFile") {
                            $this->writer->xmlElement("file", array("href" => "./" . $obj['obj_id'] . "/objects/il_" . IL_INST_ID . "_mob_" . $mob_id . "/" . rawurlencode($media_obj->getLocation())), "");
                        }
                    }
                }

                $file_ids = ilPCFileList::collectFileItems($page_obj, $page_obj->getDomDoc());
                foreach ($file_ids as $file_id) {
                    if (ilObject::_lookupType($file_id) == "file") {
                        $file_obj = new ilObjFile($file_id, false);
                        $this->writer->xmlElement("file", array("href" => "./" . $obj['obj_id'] . "/objects/il_" . IL_INST_ID . "_file_" . $file_id . "/" . rawurlencode($file_obj->getFileName())), "");
                    }
                }
                unset($page_obj);
            }
                        
            $this->writer->xmlEndTag("resource");
        }
        if ($this->version == "2004") {
            $attrs = array();
            $attrs["identifier"] = "PKG";
            $attrs["type"] = "webcontent";
            $attrs[($this->version == "2004"?"adlcp:scormType":"adlcp:scormtype")] = "asset";
            $this->writer->xmlStartTag("resource", $attrs, "");
            
            $xsd_files = array('adlcp_v1p3.xsd','adlseq_v1p3.xsd','imsss_v1p0.xsd','adlnav_v1p3.xsd','adlnav_v1p3.xsd',
            'imscp_v1p1.xsd','imsmanifest.xml','imsss_v1p0auxresource.xsd','imsss_v1p0control.xsd','imsss_v1p0delivery.xsd',
            'imsss_v1p0limit.xsd','imsss_v1p0objective.xsd','imsss_v1p0random.xsd','imsss_v1p0rollup.xsd','imsss_v1p0seqrule.xsd',
            'imsss_v1p0util.xsd','xml.xsd','index.html');
            foreach ($xsd_files as $xsd_file) {
                $attrs = array();
                $attrs["href"] = $xsd_file;
                $this->writer->xmlElement("file", $attrs, "");
            }
            $this->writer->xmlEndTag("resource");
        }
    }
}
