<?php

/* Copyright (c) 1998-2019 ILIAS open source, Extended GPL, see docs/LICENSE */

/**
 * Stores user settings per workspace folder
 * Table: wfld_user_setting (rw)
 *
 * @author killing@leifos.de
 */
class ilWorkspaceFolderUserSettingsRepository
{
	/**
	 * @var int
	 */
	protected $user_id;


	/**
	 * @var ilDBInterface
	 */
	protected $db;

	/**
	 * Constructor
	 */
	public function __construct($user_id, ilDBInterface $db = null)
	{
		global $DIC;

		$this->user_id = $user_id;
		$this->db = ($db != null)
			? $db
			: $DIC->database();
	}

	/**
	 * Get Sortation of workspace folder
	 * @param int $wfld_id folder object id
	 * @return int
	 */
	public function getSortation(int $wfld_id): int
	{
		$db = $this->db;

		$set = $db->queryF("SELECT * FROM wfld_user_setting ".
			" WHERE user_id = %s ".
			" AND wfld_id = %s ",
			array("integer", "integer"),
			array($this->user_id, $wfld_id)
			);
		$rec = $db->fetchAssoc($set);
		return (int) $rec["sortation"];
	}

	/**
	 * Get Sortation of workspace folder
	 * @param int[] $wfld_id folder object ids
	 * @return int[]
	 */
	public function getSortationMultiple(array $wfld_ids): array
	{
		$db = $this->db;

		$set = $db->queryF("SELECT * FROM wfld_user_setting ".
			" WHERE user_id = %s ".
			" AND ".$db->in("wfld_id", $wfld_ids, false, "integer"),
			array("integer"),
			array($this->user_id)
		);
		$ret = [];

		while ($rec = $db->fetchAssoc($set))
		{
			$ret[$rec["wfld_id"]] = (int) $rec["sortation"];
		}
		foreach ($wfld_ids as $id)
		{
			if (!isset($ret[$id])) {
				$ret[$id] = 0;
			}
		}
		return $ret;
	}

	/**
	 * Update sortation for workspace folder
	 * @param int $wfld_id folder object id
	 * @param int $sortation
	 */
	public function updateSortation(int $wfld_id, int $sortation)
	{
		$db = $this->db;

		$db->replace("wfld_user_setting", array(		// pk
				"user_id" => array("integer", $this->user_id),
				"wfld_id" => array("integer", $wfld_id)
			), array(
				"sortation" => array("integer", $sortation)
			));
	}
}