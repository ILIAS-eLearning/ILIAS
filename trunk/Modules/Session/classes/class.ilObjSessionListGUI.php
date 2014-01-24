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

include_once('Services/Object/classes/class.ilObjectListGUI.php');


/**
*
* @author Stefan Meyer <smeyer.ilias@gmx.de>
* @version $Id$
*
* @ingroup ModulesSession
*/
class ilObjSessionListGUI extends ilObjectListGUI
{
	protected $app_info = array();
	
	
	/**
	 * Constructor
	 *
	 * @access public
	 * @return
	 */
	public function __construct()
	{
		global $lng;
		
		$lng->loadLanguageModule('crs');
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
		$this->copy_enabled = true;
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
	 * get title
	 * Overwritten since sessions prepend the date of the session
	 * to the title
	 *
	 * @access public
	 * @param
	 * @return
	 */
	public function getTitle()
	{
		$app_info = $this->getAppointmentInfo();
		$title = strlen($this->title) ? (': '.$this->title) : '';
		return ilSessionAppointment::_appointmentToString($app_info['start'], $app_info['end'],$app_info['fullday']) . $title;
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
		global $ilCtrl;
		
		// separate method for this line
		$ilCtrl->setParameterByClass("ilrepositorygui", "ref_id", $this->ref_id);
		$cmd_link = $ilCtrl->getLinkTargetByClass("ilrepositorygui", $a_cmd);
		$ilCtrl->setParameterByClass("ilrepositorygui", "ref_id", $_GET["ref_id"]);
		return $cmd_link;
	}
	
	/**
	 * Only check cmd access for cmd 'register' and 'unregister'
	 * @param string $a_permission
	 * @param object $a_cmd
	 * @param object $a_ref_id
	 * @param object $a_type
	 * @param object $a_obj_id [optional]
	 * @return 
	 */
	public function checkCommandAccess($a_permission,$a_cmd,$a_ref_id,$a_type,$a_obj_id="")
	{
		if($a_cmd != 'register' and $a_cmd != 'unregister')
		{
			$a_cmd = '';
		}
		return parent::checkCommandAccess($a_permission,$a_cmd,$a_ref_id,$a_type,$a_obj_id);
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
		$app_info = $this->getAppointmentInfo(); 
		
		/*
		$props[] = array(
			'alert'		=> false,
			'property'	=> $this->lng->txt('event_date'),
			'value'		=> ilSessionAppointment::_appointmentToString($app_info['start'],$app_info['end'],$app_info['fullday']));
		*/
		
		if($this->getDetailsLevel() == ilObjectListGUI::DETAILS_MINIMAL)
		{
			if($items = self::lookupAssignedMaterials($this->obj_id))
			{
				$props[] = array(
					'alert'		=> false,
					'property'	=> $this->lng->txt('event_ass_materials_prop'),
					'value'		=> count($items)
				);
					
			}
		}
		if($this->getDetailsLevel() == ilObjectListGUI::DETAILS_ALL)
		{
			include_once './Modules/Session/classes/class.ilObjSession.php';
			$session_data = ilObjSession::lookupSession($this->obj_id);
			
			if(strlen($session_data['location']))
			{
				$props[] = array(
					'alert'		=> false,
					'property'	=> $this->lng->txt('event_location'),
					'value'		=> $session_data['location']
				);
			}
			if(strlen($session_data['details']))
			{
				$props[] = array(
					'alert'		=> false,
					'property'	=> $this->lng->txt('event_details_workflow'),
					'value'		=> nl2br($session_data['details']),
					'newline'	=> true
				);
			}
			$has_new_line = false;
			if(strlen($session_data['name']))
			{
				$props[] = array(
					'alert'		=> false,
					'property'	=> $this->lng->txt('event_lecturer'),
					'value'		=> $session_data['name'],
					'newline'	=> true
				);
				$has_new_line = true;				
			}
			if(strlen($session_data['email']))
			{
				$props[] = array(
					'alert'		=> false,
					'property'	=> $this->lng->txt('tutor_email'),
					'value'		=> $session_data['email'],
					'newline'	=> $has_new_line ? false : true
				);
				$has_new_line = true;				
			}
			if(strlen($session_data['phone']))
			{
				$props[] = array(
					'alert'		=> false,
					'property'	=> $this->lng->txt('tutor_phone'),
					'value'		=> $session_data['phone'],
					'newline'	=> $has_new_line ? false : true
				);
				$has_new_line = true;	
			}
		}

		return $props;
	}

	
	
	/**
	 * get appointment info
	 *
	 * @access protected
	 * @return array
	 */
	protected function getAppointmentInfo()
	{
		if(isset($this->app_info[$this->obj_id]))
		{
			return $this->app_info[$this->obj_id];
		}
		include_once('./Modules/Session/classes/class.ilSessionAppointment.php');
		return $this->app_info[$this->obj_id] = ilSessionAppointment::_lookupAppointment($this->obj_id); 
	}
	
	/**
	 * Get assigned items of event.
	 * @return 
	 * @param object $a_sess_id
	 */
	protected static function lookupAssignedMaterials($a_sess_id)
	{
		global $ilDB;
		
		$query = 'SELECT * FROM event_items ei '.
				'JOIN tree ON item_id = child '.
				'WHERE event_id = '.$ilDB->quote($a_sess_id,'integer').' '.
				'AND tree > 0';
		$res = $ilDB->query($query);
		while($row = $res->fetchRow(FETCHMODE_OBJECT))
		{
			$items[] = $row->item_id;
		}
		return $items ? $items : array();	
	}
	
}
?>