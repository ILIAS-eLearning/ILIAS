<?php
/*
        +-----------------------------------------------------------------------------+
        | ILIAS open source                                                           |
        +-----------------------------------------------------------------------------+
        | Copyright (c) 1998-2006 ILIAS open source, University of Cologne            |
        |                                                                             |
        | This program is free software; you can redistribute it and/or               |
        | modify it under the terms of the GNU General Public License                 |
        | as published by the Free Software Foundation; either version 2              |
        | of the License, or (at your option) any later version.                      |
        |                                                                             |
        | This program is distributed in the hope that it will be useful,             |
        | but WITHOUT ANY WARRANTY; without even the implied warranty of              |
        | MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the               |
        | GNU General Public License for more details.                                |
        |                                                                             |
        | You should have received a copy of the GNU General Public License           |
        | along with this program; if not, write to the Free Software                 |
        | Foundation, Inc., 59 Temple Place - Suite 330, Boston, MA  02111-1307, USA. |
        +-----------------------------------------------------------------------------+
*/

include_once('./Services/Table/classes/class.ilTable2GUI.php');

/**
*
* @author Helmut Schottmüller <ilias@aurealis.de>
* @version $Id$
*
* @ingroup ModulesTest
*/

class ilTestAverageReachedPointsTableGUI extends ilTable2GUI
{
	/**
	 * Constructor
	 *
	 * @access public
	 * @param
	 * @return
	 */
	public function __construct($a_parent_obj, $a_parent_cmd)
	{
		parent::__construct($a_parent_obj, $a_parent_cmd);

		global $lng, $ilCtrl;

		$this->lng = $lng;
		$this->ctrl = $ilCtrl;
	
		$this->setFormName('average_reached_points');
		$this->setTitle($this->lng->txt('average_reached_points'));
		$this->setStyle('table', 'fullwidth');
		$this->addColumn($this->lng->txt("question_title"),'title', '');
		$this->addColumn($this->lng->txt("points"),'points', '');
		$this->addColumn($this->lng->txt("percentage"),'percentage', '');
		$this->addColumn($this->lng->txt("number_of_answers"),'answers', '');
	
		$this->setRowTemplate("tpl.il_as_tst_average_reached_points_row.html", "Modules/Test");

		$this->setFormAction($this->ctrl->getFormAction($a_parent_obj, $a_parent_cmd));

		$this->setDefaultOrderField("title");
		$this->setDefaultOrderDirection("asc");
		$this->enable('sort');
		$this->enable('header');
		$this->disable('select_all');
	}

	/**
	* Should this field be sorted numeric?
	*
	* @return	boolean		numeric ordering; default is false
	*/
	function numericOrdering($a_field)
	{
		switch ($a_field)
		{
			case 'percentage':
				return true;
				break;
			default:
				return false;
				break;
		}
	}

	/**
	 * fill row 
	 *
	 * @access public
	 * @param
	 * @return
	 */
	public function fillRow($data)
	{
		global $ilUser,$ilAccess;

		$this->tpl->setVariable("TITLE", $data["title"]);
		$this->tpl->setVariable("POINTS", $data["points"]);
		$this->tpl->setVariable("PERCENTAGE", sprintf("%.2f", $data["percentage"]) . "%");
		$this->tpl->setVariable("ANSWERS", $data["answers"]);
	}
}
?>