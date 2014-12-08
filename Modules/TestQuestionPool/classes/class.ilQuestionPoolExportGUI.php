<?php
/* Copyright (c) 1998-2014 ILIAS open source, Extended GPL, see docs/LICENSE */

require_once 'Services/Export/classes/class.ilExportGUI.php';

/**
 * Export User Interface Class
 * @author       Michael Jansen <mjansen@databay.de>
 * @version      $Id$
 * @ingroup      ModulesTest
 */
class ilQuestionPoolExportGUI extends ilExportGUI
{
	/**
	 * {@inheritdoc}
	 */
	protected function buildExportTableGUI()
	{
		require_once 'Modules/TestQuestionPool/classes/tables/class.ilQuestionPoolExportTableGUI.php';
		$table = new ilQuestionPoolExportTableGUI($this, 'listExportFiles', $this->obj);
		return $table;
	}

	/**
	 * Download file
	 */
	public function download()
	{
		if(isset($_GET['file']) && $_GET['file'])
		{
			$_POST['file'] = array($_GET['file']);
		}
		parent::download();
	}
}