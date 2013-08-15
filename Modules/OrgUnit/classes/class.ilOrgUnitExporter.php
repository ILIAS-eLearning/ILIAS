<?php
/**
 * Created by JetBrains PhpStorm.
 * @author: Oskar Truffer <ot@studer-raimann.ch>
 * Date: 8/08/13
 * Time: 10:55 AM
 * To change this template use File | Settings | File Templates.
 */

require_once("./Modules/Category/classes/class.ilCategoryExporter.php");
require_once("./Services/Xml/classes/class.ilXmlWriter.php");
include_once("./Services/Export/classes/class.ilExport.php");

class ilOrgUnitExporter extends ilCategoryExporter {



	public function simpleExport($orgu_ref_id){
		$nodes = $this->getStructure($orgu_ref_id);
		$writer = new ilXmlWriter();
		$writer->xmlStartTag("OrgUnits");
		foreach($nodes as $orgu_ref_id){
			$orgu = new ilObjOrgUnit($orgu_ref_id);
			if($orgu->getRefId() == ilObjOrgUnit::getRootOrgRefId())
				continue;
			$attrs = $this->getAttrForOrgu($orgu);
			$writer->xmlStartTag("OrgUnit", $attrs);
			$writer->xmlElement("reference_id", null, $orgu->getRefId());
			$writer->xmlElement("external_id", null, $orgu->getImportId());
			$writer->xmlElement("title", null, $orgu->getTitle());
			$writer->xmlElement("description", null, $orgu->getDescription());
			$writer->xmlEndTag("OrgUnit");
		}
		$writer->xmlEndTag("OrgUnits");
		return $writer;
	}

	public function sendAndCreateSimpleExportFile(){
		$orgu_id = ilObjOrgUnit::getRootOrgId();
		$orgu_ref_id = ilObjOrgUnit::getRootOrgRefId();

		ilExport::_createExportDirectory($orgu_id, "xml", "orgu");
		$export_dir = ilExport::_getExportDirectory($orgu_id, "xml", "orgu");
		$ts = time();

		// Workaround for test assessment
		$sub_dir = $ts.'__'.IL_INST_ID.'__'."orgu".'_'.$orgu_id."";
		$new_file = $sub_dir.'.zip';

		$export_run_dir = $export_dir."/".$sub_dir;
		ilUtil::makeDirParents($export_run_dir);

		$writer = $this->simpleExport($orgu_ref_id);
		$writer->xmlDumpFile($export_run_dir."/manifest.xml", false);

		// zip the file
		ilUtil::zip($export_run_dir , $export_dir."/".$new_file);
		ilUtil::delDir($export_run_dir );

		// Store info about export
		include_once './Services/Export/classes/class.ilExportFileInfo.php';
		$exp = new ilExportFileInfo($orgu_id);
		$exp->setVersion(ILIAS_VERSION_NUMERIC);
		$exp->setCreationDate(new ilDateTime($ts,IL_CAL_UNIX));
		$exp->setExportType('xml');
		$exp->setFilename($new_file);
		$exp->create();

		ilUtil::deliverFile($export_dir."/".$new_file,
			$new_file);

		return array(
			"success" => true,
			"file" => $new_file,
			"directory" => $export_dir
		);
	}

//	public function lookupExportDirectory()

	private function getStructure($root_node_ref){
		global $tree;
		$open = array($root_node_ref);
		$closed = array();
		while(count($open)){
			$current = array_shift($open);
			$closed[] = $current;
			foreach($tree->getChildsByType($current, "orgu") as $new){
				if(!in_array($new["child"], $closed) && !in_array($new["child"], $open))
					$open[] = $new["child"];
			}
		}
		return $closed;
	}

	/**
	 * @param $orgu ilObjOrgUnit
	 * @return array
	 */
	private function getAttrForOrgu($orgu){
		global $tree;
		$parent_ref = $tree->getParentId($orgu->getRefId());
		if($parent_ref != ilObjOrgUnit::getRootOrgRefId()){
			$parent = new ilObjOrgUnit($parent_ref);
			$ou_parent_id = $parent->getRefId();
		} else {
			$ou_parent_id = "__ILIAS";
		}
//		$ou_id = ($orgu->getImportId()?$orgu->getImportId():$orgu->getRefId());
//		$ou_parent_id = ($parent->getImportId()?$parent->getImportId(): $parent_ref);
		// Only the ref id is guaranteed to be unique.
		$ou_id = $orgu->getRefId();
		$attr = array("ou_id" => $ou_id, "ou_id_type" => "reference_id", "ou_parent_id" => $ou_parent_id, "ou_parent_id_type" => "reference_id", "action" => "create");
		return $attr;
	}
}