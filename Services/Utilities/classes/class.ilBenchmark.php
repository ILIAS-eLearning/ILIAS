<?php

/* Copyright (c) 1998-2009 ILIAS open source, Extended GPL, see docs/LICENSE */

/**
* performance measurement class
*
* Author: Alex Killing <Alex.Killing@gmx.de>
*
* @version	$Id$
*/
class ilBenchmark
{
	var $bench = array();
	
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
		$ilDB->manipulate($q);
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
return;
		if ($this->isEnabled())
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
return;
		if ($this->isEnabled())
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
		global $ilDB, $ilUser;

		if ($this->isDbBenchEnabled() && is_object($ilUser) &&
			$this->db_enabled_user == $ilUser->getLogin())
		{
			if (is_array($this->db_bench) && is_object($ilDB))
			{
				$this->db_bench_stop_rec = true;

				$ilDB->manipulate("DELETE FROM benchmark");
				foreach ($this->db_bench as $b)
				{
					$ilDB->insert("benchmark", array(
						"duration" => array("float", $this->microtimeDiff($b["start"], $b["stop"])),
						"sql_stmt" => array("clob", $b["sql"])
					));
				}
			}
			$this->enableDbBench(false);
		}

		// log slow requests
		//define("LOG_SLOW_REQUESTS", (float) "0.1");
		if (defined("SLOW_REQUEST_TIME") && SLOW_REQUEST_TIME > 0)
		{
			$t1 = explode(" ", $GLOBALS['ilGlobalStartTime']);
			$t2 = explode(" ", microtime());
			$diff = $t2[0] - $t1[0] + $t2[1] - $t1[1];
			if ($diff > SLOW_REQUEST_TIME)
			{
				global $ilIliasIniFile;
				
				$diff = round($diff, 4);
				
				include_once("./Services/Logging/classes/class.ilLog.php");
				$slow_request_log = new ilLog(
					$ilIliasIniFile->readVariable("log","slow_request_log_path"),
					$ilIliasIniFile->readVariable("log","slow_request_log_file"),
					CLIENT_ID);
				$slow_request_log->write("SLOW REQUEST (".$diff."), Client:".CLIENT_ID.", GET: ".
					str_replace("\n", " ", print_r($_GET, true)).", POST: ".
					ilUtil::shortenText(str_replace("\n", " ", print_r($_POST, true)), 800, true));
			}
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
			" WHERE module = ".$ilDB->quote($a_module, "text")." ".
			" GROUP BY benchmark".
			" ORDER BY benchmark";
		$bench_set = $ilDB->query($q);
		$eva = array();
		while($bench_rec = $ilDB->fetchAssoc($bench_set))
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
		$cnt_rec = $ilDB->fetchAssoc($cnt_set);

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
		global $ilSetting;

		if (!is_object($ilSetting))
		{
			return true;
		}

		return (boolean) $ilSetting->get("enable_bench");
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
		while ($mod_rec = $ilDB->fetchAssoc($mod_set))
		{
			$modules[$mod_rec["module"]] = $mod_rec["module"];
		}

		return $modules;
	}

	// BEGIN WebDAV: Get measured time.
	/**
	* Get measurement.
	*
	* @return	Measurement in milliseconds.
	*/
	function getMeasuredTime($a_module, $a_bench)
	{
		if (isset($this->bench[$a_module.":".$a_bench]))
		{
			return $this->bench[$a_module.":".$a_bench][count($this->bench[$a_module.":".$a_bench]) - 1];
		}
		return false;
	}
	// END WebDAV: Get measured time.

	//
	//
	// NEW DB BENCHMARK IMPLEMENTATION
	//
	//

	/**
	 * Check wether benchmarking is enabled or not
	 */
	function isDbBenchEnabled()
	{
		global $ilSetting;

		if (isset($this->db_enabled))
		{
			return $this->db_enabled;
		}

		if (!is_object($ilSetting))
		{
			return false;
		}

		$this->db_enabled = $ilSetting->get("enable_db_bench");
		$this->db_enabled_user = $ilSetting->get("db_bench_user");
		return $this->db_enabled;
	}

	/**
	 * Enable DB benchmarking
	 *
	 * @param	boolean		enable db benchmarking
	 * @param	string		user account name that should be benchmarked
	 */
	function enableDbBench($a_enable, $a_user = 0)
	{
		global $ilias;

		if ($a_enable)
		{
			$ilias->setSetting("enable_db_bench", 1);
			if ($a_user !== 0)
			{
				$ilias->setSetting("db_bench_user", $a_user);
			}
		}
		else
		{
			$ilias->setSetting("enable_db_bench", 0);
			if ($a_user !== 0)
			{
				$ilias->setSetting("db_bench_user", $a_user);
			}
		}
	}

	/**
	 * start measurement
	 *
	 * @param	string		$type		measurement type
	 *
	 * @return	int			measurement id
	 */
	function startDbBench($a_sql)
	{
		global $ilUser;

		if ($this->isDbBenchEnabled() && is_object($ilUser) &&
			$this->db_enabled_user == $ilUser->getLogin() &&
			!$this->db_bench_stop_rec)
		{
			$this->start = microtime();
			$this->sql = $a_sql;
		}
	}


	/**
	 * stop measurement
	 *
	 * @param	int			$mid		measurement id
	 */
	function stopDbBench()
	{
		global $ilUser;

		if ($this->isDbBenchEnabled() && is_object($ilUser) &&
			$this->db_enabled_user == $ilUser->getLogin() &&
			!$this->db_bench_stop_rec)
		{
			$this->db_bench[] =
				array("start" => $this->start, "stop" => microtime(),
					"sql" => $this->sql);
		}
	}

	/**
	 * Get db benchmark records
	 *
	 * @param
	 * @return
	 */
	function getDbBenchRecords()
	{
		global $ilDB;

		$set = $ilDB->query("SELECT * FROM benchmark");
		$b = array();
		while ($rec = $ilDB->fetchAssoc($set))
		{
			$b[] = array("sql" => $rec["sql_stmt"],
				"time" => $rec["duration"]);
		}
		return $b;
	}

}

?>
