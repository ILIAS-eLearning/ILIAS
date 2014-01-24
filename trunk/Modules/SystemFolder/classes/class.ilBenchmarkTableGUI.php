<?php
/* Copyright (c) 1998-2009 ILIAS open source, Extended GPL, see docs/LICENSE */

include_once("./Services/Table/classes/class.ilTable2GUI.php");

/**
 * Benchmark table
 *
 * @author Alex Killing <alex.killing@gmx.de>
 * @version $Id$
 *
 * @ingroup ModulesSystemFolder
 */
class ilBenchmarkTableGUI extends ilTable2GUI
{

	/**
	 * Constructor
	 */
	function __construct($a_parent_obj, $a_parent_cmd, $a_records, $a_mode = "chronological")
	{
		global $ilCtrl, $lng, $ilAccess, $lng;

		parent::__construct($a_parent_obj, $a_parent_cmd);
		$this->setLimit(9999);
		$this->mode = $a_mode;

		switch ($this->mode)
		{
			case "slowest_first":
				$this->setData(ilUtil::sortArray($a_records, "time", "desc", true));
				$this->setTitle($lng->txt("adm_db_bench_slowest_first"));
				$this->addColumn($this->lng->txt("adm_time"));
				$this->addColumn($this->lng->txt("adm_sql"));
				break;

			case "sorted_by_sql":
				$this->setData(ilUtil::sortArray($a_records, "sql", "asc"));
				$this->setTitle($lng->txt("adm_db_bench_sorted_by_sql"));
				$this->addColumn($this->lng->txt("adm_time"));
				$this->addColumn($this->lng->txt("adm_sql"));
				break;

			case "by_first_table":
				$this->setData($this->getDataByFirstTable($a_records));
				$this->setTitle($lng->txt("adm_db_bench_by_first_table"));
				$this->addColumn($this->lng->txt("adm_time"));
				$this->addColumn($this->lng->txt("adm_nr_statements"));
				$this->addColumn($this->lng->txt("adm_table"));
				break;

			default:
				$this->setData($a_records);
				$this->setTitle($lng->txt("adm_db_bench_chronological"));
				$this->addColumn($this->lng->txt("adm_time"));
				$this->addColumn($this->lng->txt("adm_sql"));
				break;

		}

		$this->setEnableHeader(true);
		$this->setFormAction($ilCtrl->getFormAction($a_parent_obj));
		$this->setRowTemplate("tpl.db_bench.html", "Modules/SystemFolder");
		$this->disable("footer");
		$this->setEnableTitle(true);

//		$this->addMultiCommand("", $lng->txt(""));
//		$this->addCommandButton("", $lng->txt(""));
	}

	/**
	 * Get first occurence of string
	 *
	 * @param
	 * @return
	 */
	function getFirst($a_str, $a_needles)
	{
		$pos = 0;
		foreach ($a_needles as $needle)
		{
			$pos2 = strpos($a_str, $needle);

			if ($pos2 > 0 && ($pos2 < $pos || $pos == 0))
			{
				$pos = $pos2;
			}
		}

		return $pos;
	}

	/**
	 * Extract first table from sql
	 *
	 * @param
	 * @return
	 */
	function extractFirstTableFromSQL($a_sql)
	{
		$pos1 = $this->getFirst(strtolower($a_sql), array("from ", "from\n", "from\t", "from\r"));

		$table = "";
		if ($pos1 > 0)
		{
			$tablef = substr(strtolower($a_sql), $pos1+5);
			$pos2 = $this->getFirst($tablef, array(" ", "\n", "\t", "\r"));
			if ($pos2 > 0)
			{
				$table =substr($tablef, 0, $pos2);
			}
			else
			{
				$table = $tablef;
			}
		}
		if (trim($table) != "")
		{
			return $table;
		}

		return "";
	}


	/**
	 * Get data by first table
	 *
	 * @param
	 * @return
	 */
	function getDataByFirstTable($a_records)
	{
		$data = array();
		foreach ($a_records as $r)
		{
			$table = $this->extractFirstTableFromSQL($r["sql"]);
			$data[$table]["table"] = $table;
			$data[$table]["cnt"]++;
			$data[$table]["time"] += $r["time"];
		}
		if (count($data) > 0)
		{
			$data = ilUtil::sortArray($data, "time", "desc", true);
		}

		return $data;
	}

	/**
	 * Fill table row
	 */
	protected function fillRow($a_set)
	{
		global $lng;

		switch ($this->mode)
		{
			case "by_first_table":
				$this->tpl->setCurrentBlock("td");
				$this->tpl->setVariable("VAL", $a_set["table"]);
				$this->tpl->parseCurrentBlock();
				$this->tpl->setVariable("VAL1", $a_set["time"]);
				$this->tpl->setVariable("VAL2", $a_set["cnt"]);
				break;

			case "slowest_first":
			case "sorted_by_sql":
			default:
				$this->tpl->setVariable("VAL1", $a_set["time"]);
				$this->tpl->setVariable("VAL2", $a_set["sql"]);
				break;
		}
	}

}
?>
