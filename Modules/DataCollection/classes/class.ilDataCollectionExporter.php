<?php
require_once("./Services/Export/classes/class.ilExport.php");
require_once('./Services/Export/classes/class.ilXmlExporter.php');
require_once('class.ilDataCollectionDataSet.php');
require_once('class.ilDataCollectionCache.php');

/**
 * Class ilDataCollectionExporter
 *
 * @author Stefan Wanzenried <sw@studer-raimann.ch>
 */
class ilDataCollectionExporter extends ilXmlExporter {

    /**
     * @var ilDataCollectionDataSet
     */
    protected $ds;

    /**
     * @var ilDB
     */
    protected $db;

    public function init()
    {
        global $ilDB;
        $this->ds = new ilDataCollectionDataSet();
        $this->ds->setDSPrefix('ds');
        $this->db = $ilDB;
    }

    /**
     * @param string $a_entity
     * @return array
     */
    public function getValidSchemaVersions($a_entity)
    {
        return array (
            '4.5.0' => array(
                'namespace' => 'http://www.ilias.de/Modules/DataCollection/dcl/4_5',
                'xsd_file" => "ilias_dcl_4_5.xsd',
                'uses_dataset' => true,
                'min' => '4.5.0',
                'max' => ''
            )
        );

    }

    public function getXmlRepresentation($a_entity, $a_schema_version, $a_id)
    {
        ilUtil::makeDirParents($this->getAbsoluteExportDirectory());
        $this->ds->setExportDirectories($this->dir_relative, $this->dir_absolute);
        return $this->ds->getXmlRepresentation($a_entity, $a_schema_version, $a_id, '', true, true);
    }

    /**
     * MOB and File fieldtypes are head dependencies
     * They must be exported and imported first, so the new DC has the new IDs of those objects available
     *
     * @param $a_entity
     * @param $a_target_release
     * @param $a_ids
     * @return array
     */
    public function getXmlExportHeadDependencies($a_entity, $a_target_release, $a_ids)
    {
        return $this->ds->getHeadDependencies($a_ids);
    }

    public function getXmlExportTailDependencies($a_entity, $a_target_release, $a_ids)
    {
        return array();
    }


}

?>
