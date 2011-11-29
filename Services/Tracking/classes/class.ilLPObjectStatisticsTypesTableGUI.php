<?php
/* Copyright (c) 1998-2009 ILIAS open source, Extended GPL, see docs/LICENSE */

include_once("./Services/Tracking/classes/class.ilLPTableBaseGUI.php");

/**
* TableGUI class for learning progress
*
* @author Jörg Lützenkirchen <luetzenkirchen@leifos.com>
* @version $Id$
*
* @ilCtrl_Calls ilLPObjectStatisticsTypesTableGUI: ilFormPropertyDispatchGUI
* @ingroup ServicesTracking
*/
class ilLPObjectStatisticsTypesTableGUI extends ilLPTableBaseGUI
{
	/**
	* Constructor
	*/
	function __construct($a_parent_obj, $a_parent_cmd, array $a_preselect = null, $a_load_items = true)
	{
		global $ilCtrl, $lng;
		
		$this->preselected = $a_preselect;

		$this->setId("lpobjstattypetbl");
		
		parent::__construct($a_parent_obj, $a_parent_cmd);

		$this->addColumn("", "", "1", true);
		$this->addColumn($lng->txt("type"), "title");
		$this->addColumn($lng->txt("count"), "objects", "", false, "ilRight");
		$this->addColumn($lng->txt("trac_reference"), "references", "", false, "ilRight");
		$this->addColumn($lng->txt("trac_trash"), "deleted", "", false, "ilRight");
	
		$this->setTitle($this->lng->txt("trac_object_stat_types"));

		// $this->setSelectAllCheckbox("item_id");
		$this->addMultiCommand("showTypesGraph", $lng->txt("trac_show_graph"));
		$this->setResetCommand("resetTypesFilter");
		$this->setFilterCommand("applyTypesFilter");
		
		$this->setFormAction($ilCtrl->getFormAction($a_parent_obj, $a_parent_cmd));
		$this->setRowTemplate("tpl.lp_object_statistics_types_row.html", "Services/Tracking");
		$this->setEnableHeader(true);
		$this->setEnableNumInfo(true);
		$this->setEnableTitle(true);
		$this->setDefaultOrderField("title");
		$this->setDefaultOrderDirection("asc");
		
		$this->setLimit(9999);
	
		// $this->initFilter();
		
		$this->setExportFormats(array(self::EXPORT_EXCEL, self::EXPORT_CSV));

		include_once("./Services/Tracking/classes/class.ilLPObjSettings.php");

		if($a_load_items)
		{
			$this->getItems();
		}
	}
	
	public function numericOrdering($a_field) 
	{
		if($a_field != "title")
		{
			return true;
		}
		return false;
	}

	function getItems()
	{
		include_once "Services/Tracking/classes/class.ilTrQuery.php";
		$data = ilTrQuery::getObjectTypeStatistics();
				
		// get plugin titles
		include_once("./Services/Repository/classes/class.ilRepositoryObjectPluginSlot.php");
		$plugins = array();
		$plugins = ilRepositoryObjectPluginSlot::addCreatableSubObjects($plugins);
		
		// to enable sorting by title
		foreach($data as $idx => $row)
		{
			if(array_key_exists($row["type"], $plugins))
			{
				include_once("./Services/Component/classes/class.ilPlugin.php");
				$data[$idx]["title"] = ilPlugin::lookupTxt("rep_robj", $row["type"], "obj_".$row["type"]);
				$data[$idx]["icon"] = ilObject::_getIcon("", "tiny", $row["type"]);
			}			
			else
			{
				$data[$idx]["title"] = $this->lng->txt("objs_".$row["type"]);
				$data[$idx]["icon"] = ilUtil::getTypeIconPath($row["type"], null, "tiny");
			}
		}
		
		$this->setData($data);
	}
	
	/**
	* Fill table row
	*/
	protected function fillRow($a_set)
	{
		$this->tpl->setVariable("ICON_SRC", $a_set["icon"]);
		$this->tpl->setVariable("ICON_ALT", $this->lng->txt("objs_".$a_set["type"]));
		$this->tpl->setVariable("TITLE_TEXT", $a_set["title"]);
		$this->tpl->setVariable("COUNT", (int)$a_set["objects"]);
		$this->tpl->setVariable("REF", (int)$a_set["references"]);
		$this->tpl->setVariable("TRASH", (int)$a_set["deleted"]);
		$this->tpl->setVariable("OBJ_TYPE", $a_set["type"]);
		
		if($this->preselected && in_array($a_set["type"], $this->preselected))
		{
			$this->tpl->setVariable("CHECKBOX_STATE", " checked=\"checked\"");
		}
	}

	function getGraph(array $a_graph_items)
	{
		global $lng;
		
		include_once "Services/Chart/classes/class.ilChart.php";
		$chart = new ilChart("objsttp", 700, 500);

		$legend = new ilChartLegend();
		$chart->setLegend($legend);
		
		$types = array();
		foreach($this->getData() as $id => $item)
		{
			$types[$id] = $item["title"];
 		}

		$labels = array();
		$cnt = 0;
		foreach($types as $type => $caption)
		{			
			if(in_array($type, $a_graph_items))
			{
				$map[$type] = $cnt;

				$labels[$cnt+1] = "";
				$labels[$cnt+2] = $caption;
				$labels[$cnt+3] = "";

				$cnt+=4;
			}
		}
		$chart->setTicks($labels , false, true);

		$series_obj = new ilChartData("bars");
		$series_obj->setLabel($this->lng->txt("objects"));
		$series_obj->setBarOptions(0.75, "center");
		
		$series_ref = new ilChartData("bars");
		$series_ref->setLabel($this->lng->txt("trac_reference"));
		$series_ref->setBarOptions(0.72, "center");
		
		$series_trash = new ilChartData("bars");
		$series_trash->setLabel($this->lng->txt("trac_trash"));
		$series_trash->setBarOptions(0.75, "center");
	
		foreach($this->getData() as $object)
		{
			if(in_array($object["type"], $a_graph_items))
			{
				$x = $map[$object["type"]];

				$series_obj->addPoint($x+1, $object["objects"]);
				$series_ref->addPoint($x+2, $object["references"]);
				$series_trash->addPoint($x+3, $object["deleted"]);
			}
		}

		$chart->addData($series_obj);
		$chart->addData($series_ref);
		$chart->addData($series_trash);

		return $chart->getHTML();
	}
	
	protected function fillMetaExcel()
	{
		
	}
	
	protected function fillRowExcel($a_worksheet, &$a_row, $a_set)
	{
		$a_worksheet->write($a_row, 0, $a_set["title"]);
		$a_worksheet->write($a_row, 1, (int)$a_set["objects"]);
		$a_worksheet->write($a_row, 2, (int)$a_set["references"]);
		$a_worksheet->write($a_row, 3, (int)$a_set["deleted"]);
	}
	
	protected function fillMetaCSV()
	{
		
	}
	
	protected function fillRowCSV($a_csv, $a_set)
	{
		$a_csv->addColumn($a_set["title"]);
		$a_csv->addColumn((int)$a_set["objects"]);
		$a_csv->addColumn((int)$a_set["references"]);
		$a_csv->addColumn((int)$a_set["deleted"]);
		
		$a_csv->addRow();
	}
}

?>