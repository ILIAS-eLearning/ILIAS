<?php
/* Copyright (c) 1998-2009 ILIAS open source, Extended GPL, see docs/LICENSE */

include_once("./Services/Tracking/classes/class.ilLPTableBaseGUI.php");

/**
* TableGUI class for learning progress
*
* @author Jörg Lützenkirchen <luetzenkirchen@leifos.com>
* @version $Id$
*
* @ilCtrl_Calls ilLPObjectStatisticsTableGUI: ilFormPropertyDispatchGUI
* @ingroup ServicesTracking
*/
class ilLPObjectStatisticsTableGUI extends ilLPTableBaseGUI
{
	/**
	* Constructor
	*/
	function __construct($a_parent_obj, $a_parent_cmd, array $a_preselect = null)
	{
		global $ilCtrl, $lng;
		
		$this->preselected = $a_preselect;

		$this->setId("lpobjstattbl");
		
		parent::__construct($a_parent_obj, $a_parent_cmd);
		
		$this->setShowRowsSelector(true);
		// $this->setLimit(ilSearchSettings::getInstance()->getMaxHits());
		$this->initFilter();
		
		$this->addColumn("", "", "1", true);
		$this->addColumn($lng->txt("trac_title"), "title");
		if(strpos($this->filter["yearmonth"], "-") === false)
		{
			for($loop = 1; $loop<13; $loop++)
			{
				$this->addColumn($lng->txt("month_".str_pad($loop, 2, "0", STR_PAD_LEFT)."_short"), "month_".$loop);
			}
		}
		$this->addColumn($lng->txt("total"), "total");

		$this->setTitle($this->lng->txt("trac_object_stat_access"));

		// $this->setSelectAllCheckbox("item_id");
		$this->addMultiCommand("showAccessGraph", $lng->txt("trac_show_graph"));
		$this->setResetCommand("resetAccessFilter");
		$this->setFilterCommand("applyAccessFilter");
		
		$this->setFormAction($ilCtrl->getFormAction($a_parent_obj, $a_parent_cmd));
		$this->setRowTemplate("tpl.lp_object_statistics_row.html", "Services/Tracking");
		$this->setEnableHeader(true);
		$this->setEnableNumInfo(true);
		$this->setEnableTitle(true);
		$this->setDefaultOrderField("title");
		$this->setDefaultOrderDirection("asc");
		
		$this->setExportFormats(array(self::EXPORT_EXCEL, self::EXPORT_CSV));
		
		include_once("./Services/Tracking/classes/class.ilLPObjSettings.php");

		$this->getItems();
	}
	
	public function numericOrdering($a_field) 
	{
		$fields = array();
		$fields[] = "total";
		
		if(strpos($this->filter["yearmonth"], "-") === false)
		{
			for($loop = 1; $loop<13; $loop++)
			{
				$fields[] = "month_".$loop;
			}
		}
		
		if(in_array($a_field, $fields))
		{
			return true;
		}
		return false;
	}

	/**
	* Init filter
	*/
	public function initFilter()
	{
		global $lng;

		$this->setDisableFilterHiding(true);

		// object type selection
		include_once("./Services/Form/classes/class.ilSelectInputGUI.php");
		$si = new ilSelectInputGUI($lng->txt("obj_type"), "type");
		$options = $this->getPossibleTypes(true);
		$options["file"] = $lng->txt("objs_file");
		$options["webr"] = $lng->txt("objs_webr");
		$si->setOptions($options);
		$this->addFilterItem($si);
		$si->readFromSession();
		if(!$si->getValue())
		{
			$si->setValue("crs");
		}
		$this->filter["type"] = $si->getValue();

		// title/description
		include_once("./Services/Form/classes/class.ilTextInputGUI.php");
		$ti = new ilTextInputGUI($lng->txt("trac_title_description"), "query");
		$ti->setMaxLength(64);
		$ti->setSize(20);
		$this->addFilterItem($ti);
		$ti->readFromSession();
		$this->filter["query"] = $ti->getValue();

		// read_count/spent_seconds
		$si = new ilSelectInputGUI($lng->txt("trac_figure"), "figure");
		$si->setOptions(array("read_count"=>$lng->txt("trac_read_count"),
			"spent_seconds"=>$lng->txt("trac_spent_seconds")));
		$this->addFilterItem($si);
		$si->readFromSession();
		if(!$si->getValue())
		{
			$si->setValue("read_count");
		}
		$this->filter["measure"] = $si->getValue();

		// year/month
		$si = new ilSelectInputGUI($lng->txt("year")." / ".$lng->txt("month"), "yearmonth");
		$options = array();
		for($loop = 0; $loop < 10; $loop++)
		{
			$year = date("Y")-$loop;
			$options[$year] = $year;
			for($loop2 = 12; $loop2 > 0; $loop2--)
			{
				$month = str_pad($loop2, 2, "0", STR_PAD_LEFT);
				if($year.$month <= date("Ym"))
				{
					$options[$year."-".$month] = $year." / ".
						$lng->txt("month_".$month."_long");
				}
			}
		}
		$si->setOptions($options);
		$this->addFilterItem($si);
		$si->readFromSession();
		if(!$si->getValue())
		{
			$si->setValue(date("Y-m"));
		}
		$this->filter["yearmonth"] = $si->getValue();
	}

	function getItems()
	{
		$data = array();
		
		$objects = $this->searchObjects($this->getCurrentFilter(true), "read");
		if($objects)
		{
			include_once "Services/Tracking/classes/class.ilTrQuery.php";
			
			$yearmonth = explode("-", $this->filter["yearmonth"]);
			if(sizeof($yearmonth) == 1)
			{
				foreach(ilTrQuery::getObjectAccessStatistics($objects, $yearmonth[0]) as $obj_id => $months)
				{
					$data[$obj_id]["obj_id"] = $obj_id;
					$data[$obj_id]["title"] = ilObject::_lookupTitle($obj_id);
					
					foreach($months as $month => $values)
					{					
						$data[$obj_id]["month_".$month] = (int)$values[$this->filter["measure"]];
						$data[$obj_id]["total"] += (int)$values[$this->filter["measure"]];
					}
				}
			}
			else
			{
				foreach(ilTrQuery::getObjectAccessStatistics($objects, $yearmonth[0], (int)$yearmonth[1]) as $obj_id => $days)
				{
					$data[$obj_id]["obj_id"] = $obj_id;
					$data[$obj_id]["title"] = ilObject::_lookupTitle($obj_id);
					
					foreach($days as $day => $values)
					{					
						$data[$obj_id]["day_".$day] = (int)$values[$this->filter["measure"]];
						$data[$obj_id]["total"] += (int)$values[$this->filter["measure"]];
					}
				}
			}
			
			// add objects with no usage data
			foreach($objects as $obj_id => $ref_ids)
			{
				if(!isset($data[$obj_id]))
				{
					$data[$obj_id]["obj_id"] = $obj_id;
					$data[$obj_id]["title"] = ilObject::_lookupTitle($obj_id);
				}
			}		
		}
		
		$this->setData($data);
	}
	
	/**
	* Fill table row
	*/
	protected function fillRow($a_set)
	{
		global $ilCtrl;

		$type = ilObject::_lookupType($a_set["obj_id"]);

		$this->tpl->setVariable("OBJ_ID", $a_set["obj_id"]);
		$this->tpl->setVariable("ICON_SRC", ilUtil::getTypeIconPath($type, $a_set["obj_id"], "tiny"));
		$this->tpl->setVariable("ICON_ALT", $this->lng->txt($type));
		$this->tpl->setVariable("TITLE_TEXT", $a_set["title"]);
		
		if($this->preselected && in_array($a_set["obj_id"], $this->preselected))
		{
			$this->tpl->setVariable("CHECKBOX_STATE", " checked=\"checked\"");
		}

		$sum = 0;
		if(strpos($this->filter["yearmonth"], "-") === false)
		{
			$this->tpl->setCurrentBlock("month");
			for($loop = 1; $loop<13; $loop++)
			{
				$value = (int)$a_set["month_".$loop];
				if($this->filter["measure"] == "read_count")
				{
					$value = $this->anonymizeValue($value);
				}						
				else if($this->filter["measure"] == "spent_seconds")
				{
					$value = $this->formatSeconds($value);
				}
				$this->tpl->setVariable("MONTH_VALUE", $value);
				$this->tpl->parseCurrentBlock();
			}
		}

		if($this->filter["measure"] == "spent_seconds")
		{
			$sum = $this->formatSeconds((int)$a_set["total"]);
		}
		else 
		{
			$sum = $this->anonymizeValue((int)$a_set["total"]);
		}	
		$this->tpl->setVariable("TOTAL", $sum);
	}

	function getGraph(array $a_graph_items)
	{
		global $lng;
		
		include_once "Services/Chart/classes/class.ilChart.php";
		$chart = new ilChart("objstacc", 700, 500);

		$legend = new ilChartLegend();
		$chart->setLegend($legend);

		$max_value = 0;
		foreach($this->getData() as $object)
		{
			if(in_array($object["obj_id"], $a_graph_items))
			{
				$series = new ilChartData("lines");
				$series->setLabel(ilObject::_lookupTitle($object["obj_id"]));

				if(strpos($this->filter["yearmonth"], "-") === false)
				{
					for($loop = 1; $loop<13; $loop++)
					{
						$value = (int)$object["month_".$loop];
						$max_value = max($max_value, $value);
						if($this->filter["measure"] == "read_count")
						{
							$value = $this->anonymizeValue($value, true);
						}	
						$series->addPoint($loop, $value);
					}
				}
				else
				{
					for($loop = 1; $loop<32; $loop++)
					{
						$value = (int)$object["day_".$loop];
						$max_value = max($max_value, $value);
						if($this->filter["measure"] == "read_count")
						{
							$value = $this->anonymizeValue($value, true);
						}	
						$series->addPoint($loop, $value);
					}
				}

				$chart->addData($series);
			}
		}
		
		$value_ticks = $this->buildValueScale($max_value, ($this->filter["measure"] == "read_count"));
		
		$labels = array();
		if(strpos($this->filter["yearmonth"], "-") === false)
		{
			for($loop = 1; $loop<13; $loop++)
			{
				$labels[$loop] = $lng->txt("month_".str_pad($loop, 2, "0", STR_PAD_LEFT)."_short");
			}
		}
		else
		{
			for($loop = 1; $loop<32; $loop++)
			{
				$labels[$loop] = $loop.".";
			}
		}
		$chart->setTicks($labels, $value_ticks, true);

		return $chart->getHTML();
	}
	
	protected function fillMetaExcel()
	{
		
	}
	
	protected function fillRowExcel($a_worksheet, &$a_row, $a_set)
	{
		$a_worksheet->write($a_row, 0, ilObject::_lookupTitle($a_set["obj_id"]));
			
		$col = 0;
		if(strpos($this->filter["yearmonth"], "-") === false)
		{
			for($loop = 1; $loop<13; $loop++)
			{
				$value = (int)$a_set["month_".$loop];
				if($this->filter["measure"] == "read_count")
				{
					$value = $this->anonymizeValue($value);
				}	
				else if($this->filter["measure"] == "spent_seconds")
				{
					$value = $this->formatSeconds($value);
				}
				
				$col++;
				$a_worksheet->write($a_row, $col, $value);
			}
		}
		
		if($this->filter["measure"] == "spent_seconds")
		{
			$sum = $this->formatSeconds((int)$a_set["total"]);
		}
		else 
		{
			$sum = $this->anonymizeValue((int)$a_set["total"]);
		}	
		$col++;
		$a_worksheet->write($a_row, $col, $sum);
	}
	
	protected function fillMetaCSV()
	{
		
	}
	
	protected function fillRowCSV($a_csv, $a_set)
	{
		$a_csv->addColumn(ilObject::_lookupTitle($a_set["obj_id"]));
			
		if(strpos($this->filter["yearmonth"], "-") === false)
		{
			for($loop = 1; $loop<13; $loop++)
			{
				$value = (int)$a_set["month_".$loop];
				if($this->filter["measure"] == "read_count")
				{
					$value = $this->anonymizeValue($value);
				}	
				else if($this->filter["measure"] == "spent_seconds")
				{
					$value = $this->formatSeconds($value);
				}
				
				$a_csv->addColumn($value);
			}
		}
		
		if($this->filter["measure"] == "spent_seconds")
		{
			$sum = $this->formatSeconds((int)$a_set["total"]);
		}	
		else 
		{
			$sum = $this->anonymizeValue((int)$a_set["total"]);
		}	
		$a_csv->addColumn($sum);
		
		$a_csv->addRow();
	}
}

?>