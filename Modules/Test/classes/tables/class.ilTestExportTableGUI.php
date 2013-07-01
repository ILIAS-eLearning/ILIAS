<?php
/* Copyright (c) 1998-2013 ILIAS open source, Extended GPL, see docs/LICENSE */

require_once 'Services/Export/classes/class.ilExportTableGUI.php';

/**
*
* @author Helmut Schottmüller <ilias@aurealis.de>
* @version $Id$
*
* @ingroup ModulesTest
*/

class ilTestExportTableGUI extends ilExportTableGUI
{
	protected $counter;
	protected $confirmdelete;
	
	/**
	 * Constructor
	 *
	 * @access public
	 * @param
	 * @return
	 */
	public function __construct($a_parent_obj, $a_parent_cmd, $a_exp_obj)
	{
		parent::__construct($a_parent_obj, $a_parent_cmd, $a_exp_obj);
	}

	/**
	 * Overwrite method because data is passed from outside
	 */
	public function getExportFiles()
	{
		return array();
	}

	/**
	 *
	 */
	protected function initColumns()
	{
		$this->addColumn($this->lng->txt(''), '', '1', true);
		$this->addColumn($this->lng->txt('file'), 'file');
		$this->addColumn($this->lng->txt('size'), 'size');
		$this->addColumn($this->lng->txt('date'), 'timestamp');
	}

	/**
	 * @param string $column
	 * @return bool
	 */
	public function numericOrdering($column)
	{
		if(in_array($column, array('size', 'date')))
		{
			return true;
		}

		return false;
	}

	/**
	 * @param array $row
	 * @return string
	 */
	protected function getRowId(array $row)
	{
		return $row['file'];
	}
}