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

include_once('./classes/class.ilObjectListGUI.php');


/**
*
* @author Stefan Meyer <smeyer.ilias@gmx.de>
* @version $Id$
*
* @ingroup ModulesSession
*/

class ilObjSessionListGUI extends ilObjectListGUI
{
	/**
	 * Constructor
	 *
	 * @access public
	 * @return
	 */
	public function __construct()
	{
		parent::__construct();
	}
	
	/**
	 * Initialisation
	 *
	 * @access public
	 * @return void
	 */
	public function init()
	{
		$this->delete_enabled = true;
		$this->cut_enabled = false;
		$this->subscribe_enabled = true;
		$this->link_enabled = false;
		$this->payment_enabled = false;
		$this->info_screen_enabled = true;
		$this->subitems_enabled = true;
		$this->type = "sess";
		$this->gui_class_name = "ilobjsessiongui";
		
		// general commands array
		include_once('./Modules/Session/classes/class.ilObjSessionAccess.php');
		$this->commands = ilObjSessionAccess::_getCommands();
	}
	
	/**
	* Get command link url.
	*
	* @param	int			$a_ref_id		reference id
	* @param	string		$a_cmd			command
	*
	*/
	public function getCommandLink($a_cmd)
	{
		// separate method for this line
		return "repository.php?ref_id=".$this->ref_id."&cmd=$a_cmd";
	}
	
	/**
	 * get properties
	 *
	 * @access public
	 * @param
	 * @return
	 */
	public function getProperties()
	{
		include_once('./Modules/Session/classes/class.ilSessionAppointment.php');
		$app_info = ilSessionAppointment::_lookupAppointment($this->obj_id);
		
		$props = parent::getProperties();
		$props[] = array(
			'alert'		=> false,
			'property'	=> $this->lng->txt('event_date'),
			'value'		=> ilSessionAppointment::_appointmentToString($app_info['start'],$app_info['end'],$app_info['fullday']));
		
		return $props;
	}

	
	
}
?>