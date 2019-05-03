<?php

/* Copyright (c) 1998-2019 ILIAS open source, Extended GPL, see docs/LICENSE */

/**
 * This should hold all accesses to exc_members table in the future
 *
 * @author killing@leifos.de
 * @ingroup ModulesExercise
 */
class ilExcMemberRepository
{

	/**
	 * @var \ilDBInterface
	 */
	protected $db;

	/**
	 * Constructor
	 *
	 * @param ilDBInterface $db
	 */
	public function __construct(\ilDBInterface $db = null)
	{
		global $DIC;

		$this->db = (is_null($db))
			? $DIC->database()
			: $db;
	}

	/**
	 * Get all exercise IDs of a user
	 *
	 * @param int user id
	 * @return int[] exercise ids
	 */
	public function getExerciseIdsOfUser(int $user_id): array
	{
		$db = $this->db;

		$set = $db->queryF("SELECT DISTINCT obj_id FROM exc_members " .
			" WHERE usr_id = %s ",
			array("integer"),
			array($user_id)
		);
		$ids = [];
		while ($rec = $db->fetchAssoc($set))
		{
			$ids[] = $rec["obj_id"];
		}

		return $ids;
	}
}