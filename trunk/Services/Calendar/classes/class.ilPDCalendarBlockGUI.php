<?php
/*
	+-----------------------------------------------------------------------------+
	| ILIAS open source                                                           |
	+-----------------------------------------------------------------------------+
	| Copyright (c) 1998-2008 ILIAS open source, University of Cologne            |
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

include_once("Services/Calendar/classes/class.ilCalendarBlockGUI.php");

/**
* Calendar blocks, displayed on personal desktop
*
* @author Alex Killing <alex.killing@gmx.de>
* @version $Id$
*
* @ilCtrl_IsCalledBy ilPDCalendarBlockGUI: ilColumnGUI
* @ilCtrl_Calls ilPDCalendarBlockGUI: ilCalendarDayGUI, ilCalendarAppointmentGUI
* @ilCtrl_Calls ilPDCalendarBlockGUI: ilCalendarMonthGUI, ilCalendarWeekGUI, ilCalendarInboxGUI
* @ilCtrl_Calls ilPDCalendarBlockGUI: ilConsultationHoursGUI
*
* @ingroup ServicesCalendar
*/
class ilPDCalendarBlockGUI extends ilCalendarBlockGUI
{
	static $block_type = "pdcal";
	
	/**
	* Constructor
	*/
	function ilPDCalendarBlockGUI()
	{
		global $ilCtrl, $lng, $ilUser, $tpl;
		
		parent::ilCalendarBlockGUI(true);
		$this->allow_moving = true;
		$this->initCategories();
		$this->setBlockId(0);
	}
	
	/**
	* Get block type
	*
	* @return	string	Block type.
	*/
	static function getBlockType()
	{
		return self::$block_type;
	}

	/**
	 * init categories
	 *
	 * @access protected
	 * @param
	 * @return
	 */
	protected function initCategories()
	{
		include_once './Services/Calendar/classes/class.ilCalendarUserSettings.php';
		if(ilCalendarUserSettings::_getInstance()->getCalendarSelectionType() == ilCalendarUserSettings::CAL_SELECTION_MEMBERSHIP)
		{
			$this->mode = ilCalendarCategories::MODE_PERSONAL_DESKTOP_MEMBERSHIP;
		}
		else
		{
			$this->mode = ilCalendarCategories::MODE_PERSONAL_DESKTOP_ITEMS;
		}
		
		include_once('./Services/Calendar/classes/class.ilCalendarCategories.php');
		ilCalendarCategories::_getInstance()->initialize($this->mode,(int)$_GET['ref_id'],true);
	}

	/**
	* Return to upper context
	*/
	function returnToUpperContext()
	{
		global $ilCtrl;
		
		$ilCtrl->redirectByClass("ilpersonaldesktopgui", "show");
	}

}

?>
