<?php
/* Copyright (c) 1998-2013 ILIAS open source, Extended GPL, see docs/LICENSE */

require_once 'Modules/TestQuestionPool/classes/tables/class.ilUnitCategoryTableGUI.php';

/**
 * Class ilLocalUnitCategoryTableGUI
 */
class ilLocalUnitCategoryTableGUI extends ilUnitCategoryTableGUI
{
	/**
	 *
	 */
	protected function populateTitle()
	{
		if($this->getParentObject()->isCRUDContext())
		{
			$this->setTitle($this->lng->txt('un_local_units') . ': ' . $this->lng->txt('categories'));
		}
		else
		{
			$this->setTitle($this->lng->txt('un_global_units') . ': ' . $this->lng->txt('categories'));
		}
	}
}