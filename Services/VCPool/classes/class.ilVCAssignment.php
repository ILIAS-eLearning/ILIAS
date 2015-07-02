<?php

/* Copyright (c) 2015 Richard Klees <richard.klees@concepts-and-training.de> Extended GPL, see docs/LICENSE */

/**
 * Class ilVCAssignment
 *
 * An assignment of a VC for a certain timespan.
 *
 * @author: Richard Klees <richard.klees@concepts-and-training.de>
 */

class ilVCAssignment {
	private $id;		// int
	private $vc;		// ilVirtualClassroom
	private $start;		// ilDateTime
	private $end;		// ilDateTime
	
	// This should only be used by VCPool to protect the constraints
	// of the pool.
	public function __construct($a_id, ilVirtualClassroom $a_vc, $a_obj_id, ilDateTime $a_start, ilDateTime $a_end) {
		assert(is_int($a_id));
		assert(ilDateTime::_before($a_start, $a_end));

		$this->id = $a_id;
		$this->vc = $a_vc;
		$this->obj_id = $a_obj_id;
		$this->start = $a_start;
		$this->end = $a_end;
	}
	
	public function getId() {
		return $this->id;
	}

	public function getVC() {
		return $this->vc;
	}

	public function getObjId() {
		return $this->obj_id;
	}
	
	public function getStart() {
		return $this->start;
	}
	
	public function getEnd() {
		return $this->end;
	}
	
	public function release() {
		require_once("Services/VCPool/classes/class.ilVCPool.php");
		ilVCPool::getInstance()->releaseVCAssignment($this);
	}
}

?>