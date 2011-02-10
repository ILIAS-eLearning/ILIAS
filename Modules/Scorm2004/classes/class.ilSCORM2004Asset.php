<?php
/* Copyright (c) 1998-2010 ILIAS open source, Extended GPL, see docs/LICENSE */

require_once("./Modules/Scorm2004/classes/class.ilSCORM2004Sco.php");

/**
 * Class ilSCORM2004Asset
 *
 * Asset class for SCORM 2004 Editing
 *
 * @author Alex Killing <alex.killing@gmx.de>
 * @version $Id$
 *
 * @ingroup ModulesScorm2004
 */
class ilSCORM2004Asset extends ilSCORM2004Sco
{
	/**
	 * Constructor
	 *
	 * @param object SCORM LM object
	 */
	function __construct($a_slm_object, $a_id = 0)
	{
		parent::ilSCORM2004Node($a_slm_object, $a_id);
		$this->setType("ass");
	}

	/**
	 * Create asset
	 */
	function create($a_upload = false, $a_template = false)
	{
		include_once("./Modules/Scorm2004/classes/seq_editor/class.ilSCORM2004Item.php");
		include_once("./Modules/Scorm2004/classes/seq_editor/class.ilSCORM2004Objective.php");
		parent::create($a_upload, false, true);
		if (!$a_template) {
			$seq_item = new ilSCORM2004Item($this->getId());
			$seq_item->insert();
		}
	}

}
?>