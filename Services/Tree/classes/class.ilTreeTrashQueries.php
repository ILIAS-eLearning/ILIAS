<?php

/* Copyright (c) 1998-2009 ILIAS open source, Extended GPL, see docs/LICENSE */


/**
 *
 * @author Stefan Meyer <meyer@leifos.com>
 * @ingroup ServicesTree
 *
 */
class ilTreeTrashQueries
{
	/**
	 * @var int
	 */
	private $ref_id = 0;


	private $tree = null;

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

		$this->tree = $DIC->repositoryTree();
	}

	/**
	 * Get trashed nodes
	 * @param int $ref_id
	 * @return \ilTreeTrashItem[]
	 */
	public function getTrashNodeForContainer(int $ref_id) {

		$subtreequery = $this->tree->getTrashSubTreeQuery($ref_id, ['child']);

		$query = 'select ref_id, obd.obj_id, type, title, description, deleted, deleted_by from object_data obd ' .
			'join object_reference obr on obd.obj_id = obr.obj_id ' .
			'where ref_id in (' .
			$subtreequery . ' '.
			')';
		$res = $this->db->query($query);

		$items = [];
		while($row = $res->fetchRow(\ilDBConstants::FETCHMODE_OBJECT)) {

			$item = new \ilTreeTrashItem();
			$item->setObjId($row->obj_id);
			$item->setRefId($row->ref_id);
			$item->setTitle($row->title);
			$item->setDescription($row->description);
			$item->setType($row->type);
			$item->setDeleted($row->deleted);
			$item->setDeletedBy($row->deleted_by);

			$items[] = $item;
		}
		return $items;
	}



	/**
	 * Unfortunately not supported by mysql 5
	 * @param int $ref_id
	 *
	 */
	public function getTrashedNodesForContainerUsingRecursion(int $ref_id)
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