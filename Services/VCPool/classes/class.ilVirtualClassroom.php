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
	private $id;		// integer
	private $url;		// string
	private $type;		// string
	
	// This should only be used by VCPool to protect the constraints
	// of the pool.
	public function __construct($a_id, $a_url, $a_type) {
		assert(is_int($a_id));
		assert(is_string($a_url));
		assert(is_string($a_type));
		$this->id = $a_id;
		$this->url = $a_url;
		$this->type = $a_type;
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
}

?>