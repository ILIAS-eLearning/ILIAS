<?php

/* Copyright (c) 2015 Richard Klees <richard.klees@concepts-and-training.de> Extended GPL, see docs/LICENSE */

/**
 * Class ilVirtualClassroom
 *
 * Encapsulates information about a virtual classroom.
 *
 * @author: Richard Klees <richard.klees@concepts-and-training.de>
 */

class ilVirtualClassroom {
	private $id;				// integer
	private $url;				// string
	private $type;				// string
	private $tutor_password;	// string
	private $member_password;	// string
	
	// This should only be used by VCPool to protect the constraints
	// of the pool.
	public function __construct($a_id, $a_url, $a_type,$a_tutor_password = null, $a_member_password = null) {
		assert(is_int($a_id));
		assert(is_string($a_url));
		assert(is_string($a_type));
		$this->id = $a_id;
		$this->url = $a_url;
		$this->type = $a_type;
		$this->tutor_password = $a_tutor_password;
		$this->member_password = $a_member_password;
	}
	
	public function getId() {
		return $this->id;
	}

	public function getUrl() {
		return $this->url;
	}
	
	public function getType() {
		return $this->type;
	}

	public function getTutorPassword() {
		return $this->tutor_password;
	}

	public function getMemberPassword() {
		return $this->member_password;
	}
}

?>