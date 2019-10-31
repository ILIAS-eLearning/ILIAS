<?php

/* Copyright (c) 1998-2019 ILIAS open source, Extended GPL, see docs/LICENSE */

/**
 * This repo stores infos on repository objects that are using booking managers as a service
 * (resource management).
 *
 * @author killing@leifos.de
 * @ingroup ModulesBookingManager
 */
class ilObjUseBookDBRepository
{
	const TABLE_NAME = 'book_obj_use_book';

	/**
	 * ilObjUseBookDBRepository constructor.
	 * @param \ilDBInterfacee $db
	 */
	public function __construct(\ilDBInterface $db)
	{
		$this->db = $db;
	}

	/**
	 * @param int $obj_id
	 * @param int[] $book_obj_ids
	 */
	public function updateUsedBookingPools(int $obj_id, array $book_obj_ids)
	{
		$db = $this->db;

		$db->manipulateF("DELETE FROM ".self::TABLE_NAME." WHERE ".
			" obj_id = %s",
			array("integer"),
			array($obj_id));

		foreach ($book_obj_ids as $id)
		{
			$db->insert(self::TABLE_NAME, array(
				"obj_id" => array("integer", (int) $obj_id),
				"book_ref_id" => array("integer", (int) $id)
			));
		}
	}

	/**
	 * Get used booking pools
	 * @param int $obj_id
	 * @return int[] ref ids
	 */
	public function getUsedBookingPools(int $obj_id): array
	{
		$db = $this->db;

		$set = $db->queryF("SELECT * FROM ".self::TABLE_NAME." ".
			" WHERE obj_id = %s ",
			array("integer"),
			array($obj_id)
			);
		$book_ids = [];
		while ($rec = $db->fetchAssoc($set))
		{
			$book_ids[] = $rec["book_ref_id"];
		}
		return $book_ids;
	}


}
