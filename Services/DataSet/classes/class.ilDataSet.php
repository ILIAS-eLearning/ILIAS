<?php

/**
 * This file is part of ILIAS, a powerful learning management system
 * published by ILIAS open source e-Learning e.V.
 *
 * ILIAS is licensed with the GPL-3.0,
 * see https://www.gnu.org/licenses/gpl-3.0.en.html
 * You should have received a copy of said license along with the
 * source code, too.
 *
 * If this is not the case or you just want to try ILIAS, you'll find
 * us at:
 * https://www.ilias.de
 * https://github.com/ILIAS-eLearning
 *
 *********************************************************************/

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
 * @author Alexander Killing <killing@leifos.de>
 */
abstract class ilDataSet
{
    public const EXPORT_NO_INST_ID = 1;
    public const EXPORT_ID_ILIAS_LOCAL = 2;
    public const EXPORT_ID_ILIAS_LOCAL_INVALID = 3;
    public const EXPORT_ID_ILIAS_REMOTE = 4;
    public const EXPORT_ID_ILIAS_REMOTE_INVALID = 5;
    public const EXPORT_ID = 6;
    public const EXPORT_ID_INVALID = 7;

    public int $dircnt = 0;
    protected string $current_installation_id = "";
    protected array $data = [];
    protected ilDBInterface $db;
    protected ilLogger $ds_log;
    protected string $import_directory = "";
    protected string $entity = "";
    protected string $schema_version = "";
    protected string $relative_export_dir = "";
    protected string $absolute_export_dir = "";
    protected string $ds_prefix = "";
    protected string $version = "";
    protected ilSurveyImporter $import;

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
    final public function init(string $a_entity, string $a_schema_version) : void
    {
        $this->entity = $a_entity;
        $this->schema_version = $a_schema_version;
        $this->data = array();
    }
    
    abstract public function getSupportedVersions() : array;

    /**
     * Get (abstract) types for (abstract) field names.
     * Please note that the abstract fields/types only depend on
     * the version! Not on a choosen representation!
     * @return	array        types array, e.g.
     * array("field_1" => "text", "field_2" => "integer", ...)
     */
    abstract protected function getTypes(string $a_entity, string $a_version) : array;

    abstract protected function getXmlNamespace(string $a_entity, string $a_schema_version) : string;
    
    /**
     * Read data from DB. This should result in the
     * abstract field structure of the version set in the constructor.
     */
    abstract public function readData(
        string $a_entity,
        string $a_version,
        array $a_ids
    ) : void;
    
    public function setExportDirectories(string $a_relative, string $a_absolute) : void
    {
        $this->relative_export_dir = $a_relative;
        $this->absolute_export_dir = $a_absolute;
    }

    public function setImportDirectory(string $a_val) : void
    {
        $this->import_directory = $a_val;
    }

    public function getImportDirectory() : string
    {
        return $this->import_directory;
    }

    public function setDSPrefix(string $a_val) : void
    {
        $this->ds_prefix = $a_val;
    }

    public function getDSPrefix() : string
    {
        return $this->ds_prefix;
    }

    public function getDSPrefixString() : string
    {
        if ($this->getDSPrefix() !== "") {
            return $this->getDSPrefix() . ":";
        }
        return "";
    }

    /**
     * Get data from query.This is a standard procedure,
     * all db field names are directly mapped to abstract fields.
     */
    public function getDirectDataFromQuery(
        string $a_query,
        bool $a_convert_to_leading_upper = true,
        bool $a_set = true
    ) : array {
        $ilDB = $this->db;
        
        $set = $ilDB->query($a_query);
        $this->data = array();
        $ret = [];
        while ($rec = $ilDB->fetchAssoc($set)) {
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
     */
    public function convertToLeadingUpper(string $a_str) : string
    {
        $a_str = strtoupper(substr($a_str, 0, 1)) . substr($a_str, 1);
        while (($pos = strpos($a_str, "_")) !== false) {
            $a_str = substr($a_str, 0, $pos) .
                strtoupper(substr($a_str, $pos + 1, 1)) .
                substr($a_str, $pos + 2);
        }
        return $a_str;
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
        string $a_entity,
        string $a_schema_version,
        ?array $a_ids,
        string $a_field = "",
        bool $a_omit_header = false,
        bool $a_omit_types = false
    ) : string {
        $this->dircnt = 1;
        
        // init writer
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

        return $writer->xmlDumpMem(false);
    }
    
    
    public function addRecordsXml(
        ilXmlWriter $a_writer,
        array $a_prefixes,
        string $a_entity,
        string $a_schema_version,
        array $a_ids,
        ?string $a_field = ""
    ) : void {
        $types = $this->getXmlTypes($a_entity, $a_schema_version);

        $this->ds_log->debug("...read data");
        $this->readData($a_entity, $a_schema_version, $a_ids);
        $this->ds_log->debug("...data: " . print_r($this->data, true));
        foreach ($this->data as $d) {
            $a_writer->xmlStartTag(
                $this->getDSPrefixString() . "Rec",
                array("Entity" => $this->getXMLEntityName($a_entity, $a_schema_version))
            );

            // entity tag
            $a_writer->xmlStartTag($this->getXMLEntityTag($a_entity, $a_schema_version));

            $rec = $this->getXmlRecord($a_entity, $a_schema_version, $d);
            foreach ($rec as $f => $c) {
                if ((($types[$f] ?? "") == "directory") && $this->absolute_export_dir !== "" && $this->relative_export_dir !== "") {
                    ilFileUtils::makeDirParents($this->absolute_export_dir . "/dsDir_" . $this->dircnt);
                    ilFileUtils::rCopy($c, $this->absolute_export_dir . "/dsDir_" . $this->dircnt);
                    //echo "<br>copy-".$c."-".$this->absolute_export_dir."/dsDir_".$this->dircnt."-";
                    $c = $this->relative_export_dir . "/dsDir_" . $this->dircnt;
                    $this->dircnt++;
                }
                // this changes schema/dtd
                //$a_writer->xmlElement($a_prefixes[$a_entity].":".$f,
                //	array(), $c);
                $a_writer->xmlElement($f, array(), $c);
            }

            $a_writer->xmlEndTag($this->getXMLEntityTag($a_entity, $a_schema_version));

            $a_writer->xmlEndTag($this->getDSPrefixString() . "Rec");

            $this->afterXmlRecordWriting($a_entity, $a_schema_version, $d);

            // foreach record records of dependent entities
            $this->ds_log->debug("...get dependencies");
            $deps = $this->getDependencies($a_entity, $a_schema_version, $rec, $a_ids);
            $this->ds_log->debug("...dependencies: " . print_r($deps, true));
            foreach ($deps as $dp => $par) {
                $ids = !is_array($par["ids"])
                    ? [$par["ids"]]
                    : $par["ids"];
                $this->addRecordsXml($a_writer, $a_prefixes, $dp, $a_schema_version, $ids, $par["field"] ?? null);
            }
        }
    }

    protected function getDependencies(
        string $a_entity,
        string $a_version,
        ?array $a_rec = null,
        ?array $a_ids = null
    ) : array {
        return [];
    }

    // After xml record writing hook record
    public function afterXmlRecordWriting(string $a_entity, string $a_version, array $a_set) : void
    {
    }

    // Add types to xml writer
    private function addTypesXml(ilXmlWriter $a_writer, string $a_entity, string $a_schema_version) : void
    {
        $types = $this->getXmlTypes($a_entity, $a_schema_version);
        
        // add types of current entity
        if (count($types) > 0) {
            $a_writer->xmlStartTag(
                $this->getDSPrefixString() . "Types",
                array("Entity" => $this->getXMLEntityName($a_entity, $a_schema_version),
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
        foreach ($deps as $dp => $w) {
            $this->addTypesXml($a_writer, $dp, $a_schema_version);
        }
    }
    
    // Get xml namespaces
    public function getNamespaces(array &$namespaces, string $a_entity, string $a_schema_version) : void
    {
        $ns = $this->getXmlNamespace($a_entity, $a_schema_version);
        if ($ns !== "") {
            $namespaces[$a_entity] = $ns;
        }
        // add types of dependent entities
        $deps = $this->getDependencies($a_entity, $a_schema_version, null, null);
        foreach ($deps as $dp => $w) {
            $this->getNamespaces($namespaces, $dp, $a_schema_version);
        }
    }
    
    /**
     * Get xml record for version
     * @param array  $a_set abstract data record
     */
    public function getXmlRecord(string $a_entity, string $a_version, array $a_set) : array
    {
        return $a_set;
    }

    /**
     * Get xml types
     * @return	array	types array for xml/version set in constructor
     */
    public function getXmlTypes(string $a_entity, string $a_version) : array
    {
        return $this->getTypes($a_entity, $a_version);
    }
    
    /**
     * Get entity name for xml
     * (may be overwritten)
     */
    public function getXMLEntityName(string $a_entity, string $a_version) : string
    {
        return $a_entity;
    }

    /**
     * Get entity tag
     */
    public function getXMLEntityTag(string $a_entity, string $a_schema_version) : string
    {
        return $this->convertToLeadingUpper($a_entity);
    }
    
    public function setImport(ilSurveyImporter $a_val) : void
    {
        $this->import = $a_val;
    }
    
    public function getImport() : ilSurveyImporter
    {
        return $this->import;
    }

    public function setCurrentInstallationId(string $a_val) : void
    {
        $this->current_installation_id = $a_val;
    }

    public function getCurrentInstallationId() : string
    {
        return $this->current_installation_id;
    }
    
    /**
     * Build ilias export id
     */
    protected function createObjectExportId(string $a_type, string $a_id) : string
    {
        return "il_" . IL_INST_ID . "_" . $a_type . "_" . $a_id;
    }
        
    /**
     * Parse export id
     * @return array type, id
     */
    protected function parseObjectExportId(string $a_id, ?string $a_fallback_id = null) : array
    {
        // ilias export id?
        if (strpos($a_id, "il_") === 0) {
            $parts = explode("_", $a_id);
            if (count($parts) !== 4) {
                throw new ilException("Invalid import ID '" . $a_id . "'.");
            }
            $inst_id = $parts[1];
            $type = $parts[2];
            $id = $parts[3];
            
            // missing installation ids?
            if (($inst_id == 0 || IL_INST_ID === "0") && !DEVMODE) {
                return array("type" => self::EXPORT_NO_INST_ID, "id" => $a_fallback_id);
            }
                            
            // same installation?
            if ($inst_id == IL_INST_ID) {
                // still existing?
                if (ilObject::_lookupType($id) === $type) {
                    return array("type" => self::EXPORT_ID_ILIAS_LOCAL, "id" => $id);
                }
                // not found
                else {
                    return array("type" => self::EXPORT_ID_ILIAS_LOCAL_INVALID, "id" => $a_fallback_id);
                }
            }
            // different installation
            else {
                $id = ilObject::_getIdForImportId($a_id);
                // matching type?
                if ($id && ilObject::_lookupType($id) === $type) {
                    return array("type" => self::EXPORT_ID_ILIAS_REMOTE, "id" => $id);
                }
                // not found
                else {
                    return array("type" => self::EXPORT_ID_ILIAS_REMOTE_INVALID, "id" => $a_fallback_id);
                }
            }
        } else {
            // external id
            $id = ilObject::_getIdForImportId($a_id);
            if ($id) {
                return array("type" => self::EXPORT_ID, "id" => $id);
            } else {
                return array("type" => self::EXPORT_ID_INVALID, "id" => $a_fallback_id);
            }
        }
    }

    /**
     * Needs to be overwritten for import use case.
     */
    public function importRecord(
        string $a_entity,
        array $a_types,
        array $a_rec,
        ilImportMapping $a_mapping,
        string $a_schema_version
    ) : void {
    }
}
