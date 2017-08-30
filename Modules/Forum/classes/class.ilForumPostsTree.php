<?php
/* Copyright (c) 1998-2012 ILIAS open source, Extended GPL, see docs/LICENSE */

/**
 * @author Nadia Ahmad <nahmad@databay.de>
 * @version $Id$
 *
 * @ingroup ModulesForum
 */

class ilForumPostsTree
{

	private $thr_fk = 0;
	private $pos_fk = 0;
	private $parent_pos = 0;
	private $lft = 0;
	private $rgt = 0;
	private $depth = 0;
	
	private $source_thread_id = 0;
	private $target_thread_id = 0;

	public function setDepth($depth)
	{
		$this->depth = $depth;
	}

	public function getDepth()
	{
		return $this->depth;
	}

	public function setLft($lft)
	{
		$this->lft = $lft;
	}

	public function getLft()
	{
		return $this->lft;
	}

	public function setParentPos($parent_pos)
	{
		$this->parent_pos = $parent_pos;
	}

	public function getParentPos()
	{
		return $this->parent_pos;
	}

	public function setPosFk($pos_fk)
	{
		$this->pos_fk = $pos_fk;
	}

	public function getPosFk()
	{
		return $this->pos_fk;
	}

	public function setRgt($rgt)
	{
		$this->rgt = $rgt;
	}

	public function getRgt()
	{
		return $this->rgt;
	}

	public function setThrFk($thr_fk)
	{
		$this->thr_fk = $thr_fk;
	}

	public function getThrFk()
	{
		return $this->thr_fk;
	}

	public function setSourceThreadId($source_thread_id)
	{
		$this->source_thread_id = $source_thread_id;
	}

	public function getSourceThreadId()
	{
		return $this->source_thread_id;
	}

	public function setTargetThreadId($target_thread_id)
	{
		$this->target_thread_id = $target_thread_id;
	}

	public function getTargetThreadId()
	{
		return $this->target_thread_id;
	}
	
	public function __construct()
	{
		
	}
	
	public function mergeParentPos()
	{
		global $ilDB;
		
		$ilDB->update('frm_posts_tree',
			array(
				'parent_pos' => array('integer', $this->getParentPos()),
				'lft'        => array('integer', $this->getLft()),
				'rgt'        => array('integer', $this->getRgt()),
				'depth'      => array('integer', $this->getDepth()),
				'thr_fk'     => array('integer', $this->getTargetThreadId())
			),
			array(
				'pos_fk'	 => array('integer', $this->getPosFk()),
				'parent_pos' => array('integer', 0),
				'thr_fk'     => array('integer', $this->getSourceThreadId())
			));
	}
	public function merge()
	{
		global $ilDB;
		$ilDB->update('frm_posts_tree',
			array(
				'lft'        => array('integer', $this->getLft()),
				'rgt'        => array('integer', $this->getRgt()),
				'depth'      => array('integer', $this->getDepth()),
				'thr_fk'     => array('integer', $this->getTargetThreadId())
			),
			array(
				'pos_fk'	 => array('integer', $this->getPosFk()),
				'parent_pos' => array('integer', $this->getParentPos()),
				'thr_fk'     => array('integer', $this->getSourceThreadId())
			));
	}

	/***
	 * @param integer $root_node_id
	 * @param integer $rgt
	 */
	public static function updateTargetRootRgt($root_node_id, $rgt)
	{
		global $ilDB;
		
		$ilDB->update('frm_posts_tree',
		array(
			'rgt' => array('integer', $rgt)),
		array(
			'parent_pos' => array('integer', 0),
			'pos_fk'	=> array('integer', $root_node_id)
		));
	}
}