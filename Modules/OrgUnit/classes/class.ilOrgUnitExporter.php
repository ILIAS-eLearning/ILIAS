<?php
/* Copyright (c) 1998-2013 ILIAS open source, Extended GPL, see docs/LICENSE */
require_once("./Modules/Category/classes/class.ilCategoryExporter.php");
require_once("./Services/Xml/classes/class.ilXmlWriter.php");
require_once("./Services/Export/classes/class.ilExport.php");
/**
 * Class ilOrgUnitExporter
 *
 * @author: Oskar Truffer <ot@studer-raimann.ch>
 * @author: Martin Studer <ms@studer-raimann.ch>
 *
 */
class ilOrgUnitExporter extends ilCategoryExporter {



	public function simpleExport($orgu_ref_id){
		$nodes = $this->getStructure($orgu_ref_id);
		$writer = new ilXmlWriter();
		$writer->xmlStartTag("OrgUnits");
		foreach($nodes as $orgu_ref_id){
			$orgu = new ilObjOrgUnit($orgu_ref_id);
			if($orgu->getRefId() == ilObjOrgUnit::getRootOrgRefId())
				continue;
			$attributes = $this->getAttributesForOrgu($orgu);
			$writer->xmlStartTag("OrgUnit", $attributes);
			$writer->xmlElement("external_id", null, $this->buildExternalId($orgu_ref_id));
			$writer->xmlElement("title", null, $orgu->getTitle());
			$writer->xmlElement("description", null, $orgu->getDescription());
			$writer->xmlEndTag("OrgUnit");
		}
		$writer->xmlEndTag("OrgUnits");
		return $writer;
	}

	/**
	 * @param $orgu_ref_id int
	 * @return string
	 */
	protected function buildExternalId($orgu_ref_id) {
		return "orgu_".CLIENT_ID."_".$orgu_ref_id;
	}

	public function simpleExportExcel($orgu_ref_id){
		$nodes = $this->getStructure($orgu_ref_id);
		include_once "./Services/Excel/classes/class.ilExcelUtils.php";
		include_once "./Services/Excel/classes/class.ilExcelWriterAdapter.php";
		$adapter = new ilExcelWriterAdapter("org_unit_export_".$orgu_ref_id.".xls", true);
		$workbook = $adapter->getWorkbook();
		/** @var Spreadsheet_Excel_Writer_Worksheet $worksheet */
		$worksheet = $workbook->addWorksheet();

		ob_start();
		$worksheet->write(0, 0, "ou_id");
		$worksheet->write(0, 1, "ou_id_type");
		$worksheet->write(0, 2, "ou_parent_id");
		$worksheet->write(0, 3, "ou_parent_id_type");
		$worksheet->write(0, 4, "reference_id");
		$worksheet->write(0, 5, "external_id");
		$worksheet->write(0, 6, "title");
		$worksheet->write(0, 7, "description");
		$worksheet->write(0, 8, "action");

		$row = 0;
		foreach($nodes as $node)
		{
			$orgu = new ilObjOrgUnit($node);
			if($orgu->getRefId() == ilObjOrgUnit::getRootOrgRefId())
				continue;
			$row++;
			$attrs = $this->getAttributesForOrgu($orgu);
			$worksheet->write($row, 0, $attrs["ou_id"]);
			$worksheet->write($row, 1, $attrs["ou_id_type"]);
			$worksheet->write($row, 2, $attrs["ou_parent_id"]);
			$worksheet->write($row, 3, $attrs["ou_parent_id_type"]);
			$worksheet->write($row, 4, $orgu->getRefId());
			$worksheet->write($row, 5, $orgu->getImportId());
			$worksheet->write($row, 6, $orgu->getTitle());
			$worksheet->write($row, 7, $orgu->getDescription());
			$worksheet->write($row, 8, "create");
		}
		ob_end_clean();
		$workbook->close();
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
	private function getAttributesForOrgu($orgu){
		global $tree;
		$parent_ref = $tree->getParentId($orgu->getRefId());
		if($parent_ref != ilObjOrgUnit::getRootOrgRefId()){
			$ou_parent_id = $this->buildExternalId($parent_ref);
		} else {
			$ou_parent_id = "__ILIAS";
		}
		// Only the ref id is guaranteed to be unique.
		$ref_id = $orgu->getRefId();
		$attr = array("ou_id" => $this->buildExternalId($ref_id), "ou_id_type" => "external_id", "ou_parent_id" => $ou_parent_id, "ou_parent_id_type" => "external_id", "action" => "create");
		return $attr;
	}
}
?>