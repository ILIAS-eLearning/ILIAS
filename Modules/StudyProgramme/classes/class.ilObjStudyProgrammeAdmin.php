<?php
/* Copyright (c) 1998-2009 ILIAS open source, Extended GPL, see docs/LICENSE */
require_once("./Services/Object/classes/class.ilObject.php");
/**
 * Class ilObjStudyProgrammeAdmin
 *
 * @author  Michael Herren <mh@studer-raimann.ch>
 *
 */
class ilObjStudyProgrammeAdmin extends ilObject {

	/**
	 * Constructor
	 *
	 * @param    integer    reference_id or object_id
	 * @param    boolean    treat the id as reference_id (true) or object_id (false)
	 */
	public function __construct($a_id = 0, $a_call_by_reference = true) {
		$this->type = 'prgs';
		$this->ilObject($a_id, $a_call_by_reference);
	}
}
