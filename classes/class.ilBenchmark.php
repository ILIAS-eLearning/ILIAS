<?php

/*
    +-----------------------------------------------------------------------------+
    | ILIAS open source                                                           |
    +-----------------------------------------------------------------------------+
    | Copyright (c) 1998-2001 ILIAS open source, University of Cologne            |
    |                                                                             |
    | This program is free software; you can redistribute it and/or               |
    | modify it under the terms of the GNU General Public License                 |
    | as published by the Free Software Foundation; either version 2              |
    | of the License, or (at your option) any later version.                      |
    |                                                                             |
    | This program is distributed in the hope that it will be useful,             |
    | but WITHOUT ANY WARRANTY; without even the implied warranty of              |
    | MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the               |
    | GNU General Public License for more details.                                |
    |                                                                             |
    | You should have received a copy of the GNU General Public License           |
    | along with this program; if not, write to the Free Software                 |
    | Foundation, Inc., 59 Temple Place - Suite 330, Boston, MA  02111-1307, USA. |
    +-----------------------------------------------------------------------------+
*/


/**
* performance measurement class
*
* Author: Alex Killing <Alex.Killing@gmx.de>
*
* @version	$Id$
*/
class ilBenchmark
{

	/**
	* constructor
	*/
	function ilBenchmark()
	{
	}


	/**
	*
	*/
	function microtimeDiff($t1, $t2)
	{
		$t1 = explode(" ",$t1);
		$t2 = explode(" ",$t2);
		$diff = $t2[0] - $t1[0] + $t2[1] - $t1[1];

		return $diff;
	}



	/**
	* delete all measurement data
	*/
	function clearData()
	{
		global $ilDB;

		$q = "DELETE FROM benchmark";
		$ilDB->query($q);
	}


	/**
	* start measurement
	*
	* @param	string		$type		measurement type
	*
	* @return	int			measurement id
	*/
	function start($a_module, $a_bench)
	{
		$this->bench[$a_module.":".$a_bench][] = microtime();
	}


	/**
	* stop measurement
	*
	* @param	int			$mid		measurement id
	*/
	function stop($a_module, $a_bench)
	{
		$this->bench[$a_module.":".$a_bench][count($this->bench[$a_module.":".$a_bench]) - 1]
			= $this->microtimeDiff($this->bench[$a_module.":".$a_bench][count($this->bench[$a_module.":".$a_bench]) - 1], microtime());
	}


	/**
	* save all measurements
	*/
	function save()
	{
		global $ilDB;

		if ($this->isEnabled() &&
			($this->getMaximumRecords() > $this->getCurrentRecordNumber()))
		{
			foreach($this->bench as $key => $bench)
			{
				$bench_arr = explode(":", $key);
				$bench_module = $bench_arr[0];
				$benchmark = $bench_arr[1];
				foreach($bench as $time)
				{
					$q = "INSERT INTO benchmark (cdate, duration, module, benchmark) VALUES ".
						"(now(), ".$ilDB->quote($time).", ".$ilDB->quote($bench_module).", ".$ilDB->quote($benchmark).")";
					$ilDB->query($q);
				}
			}
			$this->bench = array();
		}
	}


	/*
	SELECT module, benchmark, COUNT(*) AS cnt, AVG(duration) AS avg_dur FROM benchmark
	GROUP BY module, benchmark ORDER BY module, benchmark
	*/

	/**
	* get performance evaluation data
	*/
	function getEvaluation($a_module)
	{
		global $ilDB;

		$q = "SELECT COUNT(*) AS cnt, AVG(duration) AS avg_dur, benchmark,".
			" MIN(duration) AS min_dur, MAX(duration) AS max_dur".
			" FROM benchmark".
			" WHERE module = ".$ilDB->quote($a_module)." ".
			" GROUP BY benchmark".
			" ORDER BY benchmark";
		$bench_set = $ilDB->query($q);
		$eva = array();
		while($bench_rec = $bench_set->fetchRow(DB_FETCHMODE_ASSOC))
		{
			$eva[] = array("benchmark" => $bench_rec["benchmark"],
				"cnt" => $bench_rec["cnt"], "duration" => $bench_rec["avg_dur"],
				"min" => $bench_rec["min_dur"], "max" => $bench_rec["max_dur"]);
		}
		return $eva;
	}


	/**
	* get current number of benchmark records
	*/
	function getCurrentRecordNumber()
	{
		global $ilDB;

		$q = "SELECT COUNT(*) AS cnt FROM benchmark";
		$cnt_set = $ilDB->query($q);
		$cnt_rec = $cnt_set->fetchRow(DB_FETCHMODE_ASSOC);

		return $cnt_rec["cnt"];
	}


	/**
	* get maximum number of benchmark records
	*/
	function getMaximumRecords()
	{
		global $ilias;

		return $ilias->getSetting("bench_max_records");
	}


	/**
	* set maximum number of benchmark records
	*/
	function setMaximumRecords($a_max)
	{
		global $ilias;

		$ilias->setSetting("bench_max_records", (int) $a_max);
	}


	/**
	* check wether benchmarking is enabled or not
	*/
	function isEnabled()
	{
		global $ilias;

		return (boolean) $ilias->getSetting("enable_bench");
	}


	/**
	* enable benchmarking
	*/
	function enable($a_enable)
	{
		global $ilias;

		if ($a_enable)
		{
			$ilias->setSetting("enable_bench", 1);
		}
		else
		{
			$ilias->setSetting("enable_bench", 0);
		}
	}


	/**
	* get all current measured modules
	*/
	function getMeasuredModules()
	{
		global $ilDB;

		$q = "SELECT DISTINCT module FROM benchmark";
		$mod_set = $ilDB->query($q);

		$modules = array();
		while ($mod_rec = $mod_set->fetchRow(DB_FETCHMODE_ASSOC))
		{
			$modules[$mod_rec["module"]] = $mod_rec["module"];
		}

		return $modules;
	}

}

?>
