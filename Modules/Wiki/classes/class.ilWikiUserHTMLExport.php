<?php

/* Copyright (c) 1998-2014 ILIAS open source, Extended GPL, see docs/LICENSE */

/**
 *  Class manages user html export
 *
 * @author Alex Killing <alex.killing@gmx.de>
 * @version $Id$
 * @ingroup 
 */
class ilWikiUserHTMLExport
{
	const PROCESS_OTHER_USER = 0;	// another user has started a running export
	const PROCESS_STARTED = 1;		// export has been started by current user
	const PROCESS_UPTODATE = 2;		// no export necessary, current export is up-to-date


	const NOT_RUNNING = 0;
	const RUNNING = 1;

	protected $data;
	protected $db;
	protected $wiki;
	protected $user;

	/**
	 * Construct
	 *
	 * @param
	 * @return
	 */
	function __construct(ilObjWiki $a_wiki, ilDB $a_db, ilObjUser $a_user)
	{
		$this->db = $a_db;
		$this->wiki = $a_wiki;
		$this->user = $a_user;
		$this->read();
	}

	/**
	 * Read
	 *
	 * @param
	 * @return
	 */
	protected function read()
	{
		$set = $this->db->query("SELECT * FROM wiki_user_html_export ".
			" WHERE wiki_id  = ".$this->db->quote($this->wiki->getId(), "integer")
			);
		if (!$this->data = $this->db->fetchAssoc($set))
		{
			$this->data = array();
		}
	}

	/**
	 * Get process
	 *
	 * @param
	 * @return
	 */
	protected function getProcess()
	{
		$last_change = ilPageObject::getLastChangeByParent("wpg", $this->wiki->getId());

		$this->db->lockTables(
			array(
				0 => array('name' => 'wiki_user_html_export', 'type' => ilDB::LOCK_WRITE)));
		$this->read();
		$ts = ilUtil::now();

		if ($this->data["start_ts"] != "" &&
			$this->data["start_ts"] > $last_change)
		{

			$this->db->unlockTables();
			return self::PROCESS_UPTODATE;
		}

		if (!isset($this->data["wiki_id"]))
		{
			$this->db->manipulate("INSERT INTO wiki_user_html_export  ".
				"(wiki_id, usr_id, progress, start_ts, status) VALUES (".
				$this->db->quote($this->wiki->getId(), "integer").",".
				$this->db->quote($this->user->getId(), "integer").",".
				$this->db->quote(0, "integer").",".
				$this->db->quote($ts, "timestamp").",".
				$this->db->quote(self::RUNNING, "integer").
				")");
		}
		else
		{
			$this->db->manipulate("UPDATE wiki_user_html_export SET ".
				" start_ts = ".$this->db->quote($ts, "timestamp").",".
				" usr_id = ".$this->db->quote($this->user->getId(), "integer").",".
				" progress = ".$this->db->quote(0, "integer").",".
				" status = ".$this->db->quote(self::RUNNING, "integer").
				" WHERE status = ".$this->db->quote(self::NOT_RUNNING, "integer").
				" AND wiki_id = ".$this->db->quote($this->wiki->getId(), "integer")
				);
		}
		$this->read();
		$this->db->unlockTables();

		if ($this->data["start_ts"] == $ts && $this->data["usr_id"] == $this->user->getId())
		{
			//  we started the process
			return self::PROCESS_STARTED;
		}

		// process was already running
		return self::PROCESS_OTHER_USER;
	}

	/**
	 * Update status
	 *
	 * @param
	 * @return
	 */
	public function updateStatus($a_progress, $a_status)
	{
		$this->db->manipulate("UPDATE wiki_user_html_export SET ".
			" progress = ".$this->db->quote((int) $a_progress, "integer").",".
			" status = ".$this->db->quote((int) $a_status, "integer").
			" WHERE wiki_id = ".$this->db->quote($this->wiki->getId(), "integer").
			" AND usr_id = ".$this->db->quote($this->user->getId(), "integer")
			);

		$this->read();
	}

	/**
	 * Get Progress
	 *
	 * @param
	 * @return
	 */
	function getProgress()
	{
		$set = $this->db->query("SELECT progress, status FROM wiki_user_html_export ".
			" WHERE wiki_id = ".$this->db->quote($this->wiki->getId(), "integer")
			);
		$rec = $this->db->fetchAssoc($set);

		return array("progress" => (int) $rec["progress"], "status" => (int) $rec["status"]);
	}


	/**
	 * Init user html export
	 *
	 * @param
	 * @return
	 */
	public function initUserHTMLExport()
	{
		// get process, if not already running or export is up-to-date, return corresponding status
		echo $this->getProcess();
		exit;
	}

	/**
	 * Start user html export
	 */
	function startUserHTMLExport()
	{
		ignore_user_abort(true);
		// do the export
		include_once("./Modules/Wiki/classes/class.ilWikiHTMLExport.php");
		$exp = new ilWikiHTMLExport($this->wiki);
		$exp->setMode(ilWikiHTMLExport::MODE_USER);
		$exp->buildExportFile();
		// reset user export status
		$this->updateStatus(100, self::NOT_RUNNING);
		exit;
	}

	/**
	 * Deliver file
	 */
	function deliverFile()
	{
		include_once("./Modules/Wiki/classes/class.ilWikiHTMLExport.php");
		$exp = new ilWikiHTMLExport($this->wiki);
		$exp->setMode(ilWikiHTMLExport::MODE_USER);
		$file = $exp->getUserExportFile();
		ilUtil::deliverFile($file, pathinfo($file, PATHINFO_BASENAME));
	}


}

?>