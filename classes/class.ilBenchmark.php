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
		if (IL_BENCH_ACTIVE)
		{
			$this->bench[$a_module.":".$a_bench][] = microtime();
		}
	}


	/**
	* stop measurement
	*
	* @param	int			$mid		measurement id
	*/
	function stop($a_module, $a_bench)
	{
		if (IL_BENCH_ACTIVE)
		{
			$this->bench[$a_module.":".$a_bench][count($this->bench[$a_module.":".$a_bench]) - 1]
				= $this->microtimeDiff($this->bench[$a_module.":".$a_bench][count($this->bench[$a_module.":".$a_bench]) - 1], microtime());
		}
	}


	/**
	* save all measurements
	*/
	function save()
	{
		global $ilDB;

		if (IL_BENCH_ACTIVE)
		{
			foreach($this->bench as $key => $bench)
			{
				$bench_arr = explode(":", $key);
				$bench_module = $bench_arr[0];
				$benchmark = $bench_arr[1];
				foreach($bench as $time)
				{
					$q = "INSERT INTO benchmark (cdate, duration, module, benchmark) VALUES ".
						"(now(), '".$time."', '".$bench_module."', '".$benchmark."')";
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

		$q = "SELECT COUNT(*) AS cnt, AVG(duration) AS avg_dur, benchmark FROM benchmark".
			" HAVING module = '".$a_module."' ".
			" GROUP BY benchmark ORDER BY benchmark";
		$bench_set = $ilDB($q);
		$eva = array();
		while($bench_rec = $bench_set->fetchRow(DB_FETCHMODE_ASSOC))
		{
			$eva[] = array("benchmark" => $bench_rec["benchmark"],
				"cnt" => $bench_rec["cnt"], "duration" => $bench_rec["avg_dur"]);
		}
		return $eva;
	}


}

?>
