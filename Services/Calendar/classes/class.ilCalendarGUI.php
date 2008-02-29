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

/** 
* 
* @author Stefan Meyer <smeyer@databay.de>
* @version $Id$
* 
* 
* @ilCtrl_Calls ilCalendarGUI:  
* @ingroup ServicesCalendar
*/

class ilCalendarGUI
{
	protected $ctrl;
	protected $lng;
	protected $tpl;
	
	/**
	 * Constructor
	 *
	 * @access public
	 * @param
	 * 
	 */
	public function __construct()
	{
		global $ilCtrl,$lng,$tpl;
	
		$this->ctrl = $ilCtrl;
		$this->lng = $lng;
		$this->tpl = $tpl; 	
	}
	
	
	/**
	 * Execute command
	 *
	 * @access public
	 * @param
	 * 
	 */
	public function executeCommand()
	{
		global $ilUser, $ilSetting;

		$next_class = $this->ctrl->getNextClass();

		switch($next_class)
		{
				
			default:
				$cmd = $this->ctrl->getCmd("show");
				$this->$cmd();
				break;
		}
		return true;
	}
	
	/**
	 * Show
	 *
	 * @access public
	 * @param
	 * 
	 */
	public function show()
	{
		$this->tpl->addCss(ilUtil::getStyleSheetLocation('filesystem','delos.css','Services/Calendar'));
		$this->tpl->setContent($this->getCenterColumnHTML());
	}
	
	/**
	* Display center column
	*/
	function getCenterColumnHTML()
	{
		global $ilCtrl;
		
		include_once("Services/Block/classes/class.ilColumnGUI.php");
		$column_gui = new ilColumnGUI("cal", IL_COL_CENTER);
		$column_gui->setEnableMovement(false);
		if ($ilCtrl->getNextClass() == "ilcolumngui" &&
			$column_gui->getCmdSide() == IL_COL_CENTER)
		{
			$html = $ilCtrl->forwardCommand($column_gui);
		}
		else
		{
			if (!$ilCtrl->isAsynch())
			{
				if ($column_gui->getScreenMode() != IL_SCREEN_SIDE)
				{
					// right column wants center
					if ($column_gui->getCmdSide() == IL_COL_RIGHT)
					{
						$column_gui = new ilColumnGUI("cal", IL_COL_RIGHT);
						$column_gui->setEnableMovement(false);
						$html = $ilCtrl->forwardCommand($column_gui);
					}
					// left column wants center
					if ($column_gui->getCmdSide() == IL_COL_LEFT)
					{
						$column_gui = new ilColumnGUI("pd", IL_COL_LEFT);
						$column_gui->setEnableMovement(false);
						$html = $ilCtrl->forwardCommand($column_gui);
					}
				}
				else
				{
					$html = $ilCtrl->getHTML($column_gui);
				}
			}
		}
		return $html;
	}
	
}
?>