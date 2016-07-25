<?php
/* Copyright (c) 1998-2013 ILIAS open source, Extended GPL, see docs/LICENSE */

require_once('./Services/Export/classes/class.ilExportGUI.php');
require_once('./Modules/DataCollection/classes/class.ilDclExportTableGUI.php');

/**
 * Export User Interface Class
 * 
 * @author       Michael Herren <mh@studer-raimann.ch>
 */
class ilDclExportGUI extends ilExportGUI
{
	/**
	 * @return ilTestExportTableGUI
	 */
	protected function buildExportTableGUI()
	{

		$table = new ilDclExportTableGUI($this, 'listExportFiles', $this->obj);
		return $table;
	}
}