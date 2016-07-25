<?php
require_once("./Services/Export/classes/class.ilExport.php");
require_once('./Services/Export/classes/class.ilXmlExporter.php');
require_once('class.ilDataCollectionDataSet.php');
require_once('./Modules/DataCollection/classes/Helpers/class.ilDclCache.php');
require_once('./Modules/MediaPool/classes/class.ilObjMediaPool.php');

/**
 * Class ilDataCollectionExporter
 *
 * @author Stefan Wanzenried <sw@studer-raimann.ch>
 * @author Fabian Schmid <fs@studer-raimann.ch>
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


	public function init() {
		global $DIC;
		$ilDB = $DIC['ilDB'];
		$this->ds = new ilDataCollectionDataSet();
		$this->ds->setDSPrefix('ds');
		$this->db = $ilDB;
	}


	/**
	 * @param string $a_entity
	 *
	 * @return array
	 */
	public function getValidSchemaVersions($a_entity) {
		return array(
			'4.5.0' => array(
				'namespace' => 'http://www.ilias.de/Modules/DataCollection/dcl/4_5',
				'xsd_file" => "ilias_dcl_4_5.xsd',
				'uses_dataset' => true,
				'min' => '4.5.0',
				'max' => ''
			)
		);
	}


	public function getXmlRepresentation($a_entity, $a_schema_version, $a_id) {
		ilUtil::makeDirParents($this->getAbsoluteExportDirectory());
		$this->ds->setExportDirectories($this->dir_relative, $this->dir_absolute);

		return $this->ds->getXmlRepresentation($a_entity, $a_schema_version, $a_id, '', true, true);
	}


	/**
	 * MOB/File fieldtypes objects are head dependencies
	 * They must be exported and imported first, so the new DC has the new IDs of those objects available
	 *
	 * @param $a_entity
	 * @param $a_target_release
	 * @param $a_ids
	 *
	 * @return array
	 */
	public function getXmlExportHeadDependencies($a_entity, $a_target_release, $a_ids) {

		foreach ($a_ids as $id) {
			$m_ids = ilObjMediaPool::getAllMobIds($id);
			foreach ($m_ids as $m) {
				$mob_ids[] = $m;
			}
		}

		$dependencies = array(
			ilDclDatatype::INPUTFORMAT_FILE => array(
				'component' => 'Modules/File',
				'entity' => 'file',
				'ids' => array(),
			),
			ilDclDatatype::INPUTFORMAT_MOB => array(
				'component' => 'Services/MediaObjects',
				'entity' => 'mob',
				'ids' => array(),
			),
		);

		// Direct SQL query is faster than looping over objects
		$page_object_ids = array();
		foreach ($a_ids as $dcl_obj_id) {
			$sql = "SELECT stloc2.value AS ext_id, f.`datatype_id` FROM il_dcl_stloc2_value AS stloc2 "
				. "INNER JOIN il_dcl_record_field AS rf ON (rf.`id` = stloc2.`record_field_id`) "
				. "INNER JOIN il_dcl_field AS f ON (rf.`field_id` = f.`id`) " . "INNER JOIN il_dcl_table AS t ON (t.`id` = f.`table_id`) "
				. "WHERE t.`obj_id` = " . $this->db->quote($dcl_obj_id, 'integer') . " " . "AND f.datatype_id IN ("
				. implode(',', array_keys($dependencies)) . ") AND stloc2.`value` IS NOT NULL";
			$set = $this->db->query($sql);
			while ($rec = $this->db->fetchObject($set)) {
				$dependencies[$rec->datatype_id]['ids'][] = (int)$rec->ext_id;
			}
		}

		// Return external dependencies/IDs if there are any
		$return = array();
		if (count($dependencies[ilDclDatatype::INPUTFORMAT_FILE]['ids'])) {
			$return[] = $dependencies[ilDclDatatype::INPUTFORMAT_FILE];
		}
		if (count($dependencies[ilDclDatatype::INPUTFORMAT_MOB]['ids'])) {
			$return[] = $dependencies[ilDclDatatype::INPUTFORMAT_MOB];
		}

		return $return;
	}
	
}

?>
