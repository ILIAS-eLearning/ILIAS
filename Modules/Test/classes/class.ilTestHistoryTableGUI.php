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
* @ingroup ModulesGroup
*/

class ilTestHistoryTableGUI extends ilTable2GUI
{
	protected $tstObject;
	
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
	
		$this->setFormName('questionbrowser');
		$this->setStyle('table', 'fullwidth');

		$this->addColumn($this->lng->txt("assessment_log_datetime"),'datetime', '');
		$this->addColumn($this->lng->txt("user"),'user', '');
		$this->addColumn($this->lng->txt("assessment_log_text"),'log', '');
		$this->addColumn($this->lng->txt("location"),'location', '');
	
		$this->setRowTemplate("tpl.il_as_tst_history_row.html", "Modules/Test");

		$this->setDefaultOrderField("datetime");
		$this->setDefaultOrderDirection("asc");
		
		$this->enable('header');
	}

	public function setTestObject($obj)
	{
		$this->tstObject = $obj;
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

		$username = $this->tstObject->userLookupFullName($data["user_fi"], TRUE);
		$this->tpl->setVariable("DATETIME", ilDatePresentation::formatDate(new ilDateTime($data["tstamp"],IL_CAL_UNIX)));
		$this->tpl->setVariable("USER", $username);
		$this->tpl->setVariable("LOG", trim(ilUtil::prepareFormOutput($data["logtext"])));
		$location = '';
		if (strlen($data["ref_id"]) && strlen($data["href"]))
		{
			$location = '<a href="' . $data['href'] . '">' . $this->lng->txt("perma_link") . '</a>';
		}
		$this->tpl->setVariable("LOCATION", $location);
	}
}
?>