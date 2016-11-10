<?php
/* Copyright (c) 1998-2016 ILIAS open source, Extended GPL, see docs/LICENSE */

require_once './Services/Object/classes/class.ilObject.php';

/**
 * Class ilObjLTIAdministration
 * @author Jesús López <lopez@leifos.com>
 *
 * @package ServicesLTI
 */
class ilObjLTIAdministration extends ilObject
{
	public function __construct($a_id = 0, $a_call_by_reference = true)
	{
		$this->type = "ltis";
		parent::__construct($a_id,$a_call_by_reference);
	}

	public function getLTIObjectTypes()
	{
		//Values I've found in the table object_data
		$validLTIObjectTypes = array(
			'crs' => 'course',
			'sahs' => 'scorm',
			'svy' => "survey",
			'tst' => "test"
		);
		//check if this elements of the $validLTIObjectTypes are repository objects
		/*
		$obj_def = new ilObjectDefinition();
		$repository_objects = $obj_def->getCreatableSubObjects("root");
		$rep_obj_filtered = array();
		foreach ($repository_objects as $key => $value)
		{
			array_push($rep_obj_filtered,$key);
		}
		var_dump($repository_objects);
		print_r("<br><br>");
		var_dump($rep_obj_filtered);
		exit();
		*/
		//$obj_def->isAllowedInRepository()
		return $validLTIObjectTypes;

	}



}