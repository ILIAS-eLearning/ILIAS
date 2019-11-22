<?php

/* Copyright (c) 1998-2009 ILIAS open source, Extended GPL, see docs/LICENSE */


/**
 *
 * @author Stefan Meyer <meyer@leifos.com>
 * @ingroup ServicesTree
 *
 */
class ilTreeTrash
{
	/**
	 * @var int
	 */
	private $ref_id = 0;

	/**
	 * @var \ilLogger
	 */
	private $logger;

	/**
	 * @var \ilDBInterface
	 */
	private $db;

	/**
	 * ilTreeTrash constructor.
	 * @param int $ref_id
	 */
	public function __construct()
	{
		global $DIC;


		$this->db = $DIC->database();
		$this->logger = $DIC->logger()->tree();
	}

	/**
	 * @param int $ref_id
	 */
	public function getTrashedNodesForContainer(int $ref_id)
	{
		$query  = 'with recursive trash (child,tree) as ' .
			'( select child, tree from tree where child = ' . $this->db->quote($ref_id, \ilDBConstants::T_INTEGER) . ' ' .
			'union select tc.child,tc.tree from tree tc join tree tp on tp.child = tc.parent ) ' .
			'select * from trash where tree < 1 ';

		$trash_ids = [];

		try {
			$res = $this->db->query($query);
			while($row = $res->fetchRow(\ilDBConstants::FETCHMODE_OBJECT)) {

				$trash_ids[] = $row->child;
			}
		}
		catch(\ilDatabaseException $e) {
			$this->logger->warning($query . ' is not supported');
		}


	}
}