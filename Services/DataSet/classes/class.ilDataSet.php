<?php

/* Copyright (c) 1998-2009 ILIAS open source, Extended GPL, see docs/LICENSE */

/**
* A dataset contains in data in a common structure that can be
* shared and transformed for different purposes easily, examples
* - transform associative arrays into (set-)xml and back (e.g. for import/export)
* - transform assiciative arrays into json and back (e.g. for ajax requests)
*
* The general structure is:
* - entity name (many times this corresponds to a table name)
* - structure (this is a set of field names and types pairs)
*   currently supported types: text, integer, timestamp
*   planned: date, time, clob
*   types correspond to db types, see
*   http://www.ilias.de/docu/goto.php?target=pg_25354_42&client_id=docu
* - records (similar to records of a database query; associative arrays)
*
* @author Alex Killing <alex.killing@gmx.de>
* @version $Id$
* @ingroup
*/
abstract class ilDataSet
{
    public $dircnt;
    protected $current_installation_id = "";
    
    const EXPORT_NO_INST_ID = 1;
    const EXPORT_ID_ILIAS_LOCAL = 2;
    const EXPORT_ID_ILIAS_LOCAL_INVALID = 3;
    const EXPORT_ID_ILIAS_REMOTE = 4;
    const EXPORT_ID_ILIAS_REMOTE_INVALID = 5;
    const EXPORT_ID = 6;
    const EXPORT_ID_INVALID = 7;

    /**
     * @var ilDB
     */
    protected $db;

    /**
     * @var ilLogger
     */
    protected $ds_log;
    
    /**
     * Constructor
     */
    public function __construct()
    {
        global $DIC;

        $this->db = $DIC->database();
        $this->ds_log = ilLoggerFactory::getLogger('ds');
    }
    
    /**
     * Init
     *
     * @param	string		(abstract) entity name
     * @param	string		version string, always the ILIAS release
     * 						versions that defined the a structure
     * 						or made changes to it, never use another
     * 						version. Example: structure is defined
     * 						in 4.1.0 and changed in 4.3.0 -> use these
     * 						values only, not 4.2.0 (ask for the 4.1.0
     * 						version in ILIAS 4.2.0)
     */
    final public function init($a_entity, $a_schema_version)
    {
        $this->entity = $a_entity;
        $this->schema_version = $a_schema_version;
        $this->data = array();
    }
    
    /**
     * Get supported version
     *
     * @return	array		array of supported version
     */
    abstract public function getSupportedVersions();
        
    /**
     * Get (abstract) types for (abstract) field names.
     * Please note that the abstract fields/types only depend on
     * the version! Not on a choosen representation!
     *
     * @return	array		types array, e.g.
     * array("field_1" => "text", "field_2" => "integer", ...)
     */
    abstract protected function getTypes($a_entity, $a_version);
    
    /**
     * Get xml namespace
     *
     */
    abstract protected function getXmlNamespace($a_entity, $a_schema_version);
    
    /**
     * Read data from DB. This should result in the
     * abstract field structure of the version set in the constructor.
     *
     * @param	array	one or multiple ids
     */
    abstract public function readData($a_entity, $a_version, $a_ids);
    
    /**
     * Set export directories
     *
     * @param
     * @return
     */
    public function setExportDirectories($a_relative, $a_absolute)
    {
        $this->relative_export_dir = $a_relative;
        $this->absolute_export_dir = $a_absolute;
    }

    /**
     * Set import directory
     *
     * @param	string	import directory
     */
    public function setImportDirectory($a_val)
    {
        $this->import_directory = $a_val;
    }

    /**
     * Get import directory
     *
     * @return	string	import directory
     */
    public function getImportDirectory()
    {
        return $this->import_directory;
    }

    /**
     * Set XML dataset namespace prefix
     *
     * @param	string	XML dataset namespace prefix
     */
    public function setDSPrefix($a_val)
    {
        $this->var = $a_val;
    }

    /**
     * Get XML dataset namespace prefix
     *
     * @return	string	XML dataset namespace prefix
     */
    public function getDSPrefix()
    {
        return $this->var;
    }

    public function getDSPrefixString()
    {
        if ($this->getDSPrefix() != "") {
            return $this->getDSPrefix() . ":";
        }
    }

    /**
     * Get data from query.This is a standard procedure,
     * all db field names are directly mapped to abstract fields.
     * @param string $a_query
     * @param bool $a_convert_to_leading_upper
     * @param bool $a_set should internal data array already be set?
     * @return array
     */
    public function getDirectDataFromQuery($a_query, $a_convert_to_leading_upper = true, $a_set = true)
    {
        $ilDB = $this->db;
        
        $set = $ilDB->query($a_query);
        $this->data = array();
        $ret = [];
        while ($rec  = $ilDB->fetchAssoc($set)) {
            if ($a_convert_to_leading_upper) {
                $tmp = array();
                foreach ($rec as $k => $v) {
                    $tmp[$this->convertToLeadingUpper($k)]
                        = $v;
                }
                $rec = $tmp;
            }

            if ($a_set) {
                $this->data[] = $rec;
            }
            $ret[] = $rec;
        }
        return $ret;
    }

    /**
     * Make xyz_abc a XyzAbc string
     *
     * @param
     * @return
     */
    public function convertToLeadingUpper($a_str)
    {
        $a_str = strtoupper(substr($a_str, 0, 1)) . substr($a_str, 1);
        while (is_int($pos = strpos($a_str, "_"))) {
            $a_str = substr($a_str, 0, $pos) .
                strtoupper(substr($a_str, $pos+1, 1)) .
                substr($a_str, $pos+2);
        }
        return $a_str;
    }

            
    /**
     * Get json representation
     */
    final public function getJsonRepresentation()
    {
        if ($this->version === false) {
            return false;
        }
        
        $arr["entity"] = $this->getJsonEntityName();
        $arr["version"] = $this->version;
        $arr["install_id"] = IL_INST_ID;
        $arr["install_url"] = ILIAS_HTTP_PATH;
        $arr["types"] = $this->getJsonTypes();
        $arr["set"] = array();
        foreach ($this->data as $d) {
            $arr["set"][] = $this->getJsonRecord($d);
        }
        
        include_once("./Services/JSON/classes/class.ilJsonUtil.php");

        return ilJsonUtil::encode($arr);
    }

    /**
     * Get xml representation
     * 	<dataset install_id="123" install_url="...">
     * 	<types entity="table_name" version="4.0.1">
     *		<ftype name="field_1" type="text" />
     *		<ftype name="field_2" type="date" />
     *		<ftype name="field_3" type="integer" />
     *	</types>
     *  <types ...>
     *    ...
     *  </types>
     *	<set entity="table_name">
     *		<rec>
     *			<field_1>content</field_1>
     *			<field_2>my_date</field_2>
     *			<field_3>my_number</field_3>
     *		</rec>
     *		...
     *	</set>
     *  </dataset>
     */
    final public function getXmlRepresentation(
        $a_entity,
        $a_schema_version,
        $a_ids,
        $a_field = "",
        $a_omit_header = false,
        $a_omit_types = false
    ) {
        $this->dircnt = 1;
        
        // step 1: check target release and supported versions
        
        
        
        // step 2: init writer
        include_once "./Services/Xml/classes/class.ilXmlWriter.php";
        $writer = new ilXmlWriter();
        if (!$a_omit_header) {
            $writer->xmlHeader();
        }
        
        // collect namespaces
        $namespaces = $prefixes = array();
        $this->getNamespaces($namespaces, $a_entity, $a_schema_version);
        $atts = array("InstallationId" => IL_INST_ID,
            "InstallationUrl" => ILIAS_HTTP_PATH, "TopEntity" => $a_entity);
        $cnt = 1;
        foreach ($namespaces as $entity => $ns) {
            $prefix = "ns" . $cnt;
            $prefixes[$entity] = $prefix;
            //			$atts["xmlns:".$prefix] = $ns;
            $cnt++;
        }

        $this->ds_log->debug("Start writing Dataset, entity: " . $a_entity . ", schema version: " . $a_schema_version .
            ", ids: " . print_r($a_ids, true));
        $writer->xmlStartTag($this->getDSPrefixString() . 'DataSet', $atts);
        
        // add types
        if (!$a_omit_types) {
            $this->ds_log->debug("...write types");
            $this->addTypesXml($writer, $a_entity, $a_schema_version);
        }
        
        // add records
        $this->ds_log->debug("...write records");
        $this->addRecordsXml($writer, $prefixes, $a_entity, $a_schema_version, $a_ids, $a_field);
        
        
        $writer->xmlEndTag($this->getDSPrefixString() . "DataSet");
        //if ($a_entity == "mep")
        //{
        //	echo "<pre>".htmlentities($writer->xmlDumpMem(true))."</pre>"; exit;
        //}
        return $writer->xmlDumpMem(false);
    }
    
    
    /**
     * Add records xml
     *
     * @param
     * @return
     */
    public function addRecordsXml($a_writer, $a_prefixes, $a_entity, $a_schema_version, $a_ids, $a_field = "")
    {
        $types = $this->getXmlTypes($a_entity, $a_schema_version);

        $this->ds_log->debug("...read data");
        $this->readData($a_entity, $a_schema_version, $a_ids, $a_field);
        $this->ds_log->debug("...data: " . print_r($this->data, true));
        if (is_array($this->data)) {
            foreach ($this->data as $d) {
                $a_writer->xmlStartTag(
                    $this->getDSPrefixString() . "Rec",
                    array("Entity" => $this->getXmlEntityName($a_entity, $a_schema_version))
                );

                // entity tag
                $a_writer->xmlStartTag($this->getXmlEntityTag($a_entity, $a_schema_version));

                $rec = $this->getXmlRecord($a_entity, $a_schema_version, $d);
                foreach ($rec as $f => $c) {
                    switch ($types[$f]) {
                        case "directory":
                            if ($this->absolute_export_dir != "" && $this->relative_export_dir != "") {
                                ilUtil::makeDirParents($this->absolute_export_dir . "/dsDir_" . $this->dircnt);
                                ilUtil::rCopy($c, $this->absolute_export_dir . "/dsDir_" . $this->dircnt);
                                //echo "<br>copy-".$c."-".$this->absolute_export_dir."/dsDir_".$this->dircnt."-";
                                $c = $this->relative_export_dir . "/dsDir_" . $this->dircnt;
                                $this->dircnt++;
                            }
                            break;
                    }
                    // this changes schema/dtd
                    //$a_writer->xmlElement($a_prefixes[$a_entity].":".$f,
                    //	array(), $c);
                    $a_writer->xmlElement($f, array(), $c);
                }
                
                $a_writer->xmlEndTag($this->getXmlEntityTag($a_entity, $a_schema_version));

                $a_writer->xmlEndTag($this->getDSPrefixString() . "Rec");
                
                $this->afterXmlRecordWriting($a_entity, $a_schema_version, $d);

                // foreach record records of dependent entities
                $this->ds_log->debug("...get dependencies");
                $deps = $this->getDependencies($a_entity, $a_schema_version, $rec, $a_ids);
                $this->ds_log->debug("...dependencies: " . print_r($deps, true));
                if (is_array($deps)) {
                    foreach ($deps as $dp => $par) {
                        $this->addRecordsXml($a_writer, $a_prefixes, $dp, $a_schema_version, $par["ids"], $par["field"]);
                    }
                }
            }
        } elseif ($this->data === false) {
            // false -> add records of dependent entities (no record)
            $this->ds_log->debug("...get dependencies (no record)");
            $deps = $this->getDependencies($a_entity, $a_schema_version, null, $a_ids);
            if (is_array($deps)) {
                foreach ($deps as $dp => $par) {
                    $this->addRecordsXml($a_writer, $a_prefixes, $dp, $a_schema_version, $par["ids"], $par["field"]);
                }
            }
        }
    }
    
    /**
     * After xml record writing hook record
     *
     * @param
     * @return
     */
    public function afterXmlRecordWriting($a_entity, $a_version, $a_set)
    {
    }

    /**
     * Add types to xml writer
     *
     * @param
     */
    private function addTypesXml($a_writer, $a_entity, $a_schema_version)
    {
        $types = $this->getXmlTypes($a_entity, $a_schema_version);
        
        // add types of current entity
        if (is_array($types)) {
            $a_writer->xmlStartTag(
                $this->getDSPrefixString() . "Types",
                array("Entity" => $this->getXmlEntityName($a_entity, $a_schema_version),
                    "SchemaVersion" => $a_schema_version)
            );
            foreach ($this->getXmlTypes($a_entity, $a_schema_version) as $f => $t) {
                $a_writer->xmlElement(
                    $this->getDSPrefixString() . 'FieldType',
                    array("Name" => $f, "Type" => $t)
                );
            }
            $a_writer->xmlEndTag($this->getDSPrefixString() . "Types");
        }
        
        // add types of dependent entities
        $deps = $this->getDependencies($a_entity, $a_schema_version, null, null);
        if (is_array($deps)) {
            foreach ($deps as $dp => $w) {
                $this->addTypesXml($a_writer, $dp, $a_schema_version);
            }
        }
    }
    
    /**
     * Get xml namespaces
     *
     * @param		array		namespaces per entity
     * @param		string		entity
     * @param		string		target release
     */
    public function getNamespaces(&$namespaces, $a_entity, $a_schema_version)
    {
        $ns = $this->getXmlNamespace($a_entity, $a_schema_version);
        if ($ns != "") {
            $namespaces[$a_entity] = $ns;
        }
        // add types of dependent entities
        $deps = $this->getDependencies($a_entity, $a_schema_version, null, null);
        if (is_array($deps)) {
            foreach ($deps as $dp => $w) {
                $this->getNamespaces($namespaces, $dp, $a_schema_version);
            }
        }
    }
    
    /**
     * Get xml record for version
     *
     * @param	array	abstract data record
     * @return	array	xml record
     */
    public function getXmlRecord($a_entity, $a_version, $a_set)
    {
        return $a_set;
    }
    
    /**
     * Get json record for version
     *
     * @param	array	abstract data record
     * @return	array	json record
     */
    public function getJsonRecord($a_set)
    {
        return $a_set;
    }
    
    /**
     * Get xml types
     *
     * @return	array	types array for xml/version set in constructor
     */
    public function getXmlTypes($a_entity, $a_version)
    {
        return $this->getTypes($a_entity, $a_version);
    }
    
    /**
     * Get json types
     *
     * @return	array	types array for json/version set in constructor
     */
    public function getJsonTypes($a_entity, $a_version)
    {
        return $this->getTypes($a_entity, $a_version);
    }
    
    /**
     * Get entity name for xml
     * (may be overwritten)
     *
     * @return	string
     */
    public function getXMLEntityName($a_entity, $a_version)
    {
        return $a_entity;
    }

    /**
     * Get entity tag
     *
     * @param
     * @return
     */
    public function getXMLEntityTag($a_entity, $a_schema_version)
    {
        return $this->convertToLeadingUpper($a_entity);
    }
    
    /**
     * Get entity name for json
     * (may be overwritten)
     */
    public function getJsonEntityName($a_entity, $a_version)
    {
        return $a_entity;
    }
    
    /**
     * Set import object
     *
     * @param	object	import object
     */
    public function setImport($a_val)
    {
        $this->import = $a_val;
    }
    
    /**
     * Get import object
     *
     * @return	object	import object
     */
    public function getImport()
    {
        return $this->import;
    }

    /**
     * Set current installation id
     *
     * @param string $a_val current installation id
     */
    public function setCurrentInstallationId($a_val)
    {
        $this->current_installation_id = $a_val;
    }

    /**
     * Get current installation id
     *
     * @return string current installation id
     */
    public function getCurrentInstallationId()
    {
        return $this->current_installation_id;
    }
    
    /**
     * Build ilias export id
     *
     * @param string $a_type
     * @param int $a_id
     * @return string
     */
    protected function createObjectExportId($a_type, $a_id)
    {
        return "il_" . IL_INST_ID . "_" . $a_type . "_" . $a_id;
    }
        
    /**
     * Parse export id
     *
     * @param string $a_id
     * @param int $a_fallback_id
     * @return array type, id
     */
    protected function parseObjectExportId($a_id, $a_fallback_id = null)
    {
        // ilias export id?
        if (substr($a_id, 0, 3) == "il_") {
            $parts = explode("_", $a_id);
            $inst_id = $parts[1];
            $type = $parts[2];
            $id = $parts[3];
            
            // missing installation ids?
            if (($inst_id == 0 || IL_INST_ID == 0) && !DEVMODE) {
                return array("type"=>self::EXPORT_NO_INST_ID, "id"=>$a_fallback_id);
            }
                            
            // same installation?
            if ($inst_id == IL_INST_ID) {
                // still existing?
                if (ilObject::_lookupType($id) == $type) {
                    return array("type"=>self::EXPORT_ID_ILIAS_LOCAL, "id"=>$id);
                }
                // not found
                else {
                    return array("type"=>self::EXPORT_ID_ILIAS_LOCAL_INVALID, "id"=>$a_fallback_id);
                }
            }
            // different installation
            else {
                $id = ilObject::_getIdForImportId($a_id);
                // matching type?
                if ($id && ilObject::_lookupType($id) == $type) {
                    return array("type"=>self::EXPORT_ID_ILIAS_REMOTE, "id"=>$id);
                }
                // not found
                else {
                    return array("type"=>self::EXPORT_ID_ILIAS_REMOTE_INVALID, "id"=>$a_fallback_id);
                }
            }
        }
        
        // external id
        $id = ilObject::_getIdForImportId($a_id);
        if ($id) {
            return array("type"=>self::EXPORT_ID, "id"=>$id);
        } else {
            return array("type"=>self::EXPORT_ID_INVALID, "id"=>$a_fallback_id);
        }
    }
}
