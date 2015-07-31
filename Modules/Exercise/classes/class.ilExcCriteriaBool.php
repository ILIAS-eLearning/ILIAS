<?php

/* Copyright (c) 1998-2010 ILIAS open source, Extended GPL, see docs/LICENSE */

include_once "Modules/Exercise/classes/class.ilExcCriteria.php";

/**
 * Class ilExcCriteria
 *
 * @author Jörg Lützenkirchen <luetzenkirchen@leifos.com>
 * @ingroup ModulesExercise
 */
class ilExcCriteriaBool extends ilExcCriteria
{
	protected function getType()
	{
		return "bool";
	}
	
	
}
