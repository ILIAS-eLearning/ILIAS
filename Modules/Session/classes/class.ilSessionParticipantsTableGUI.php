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
* @author Stefan Meyer <smeyer.ilias@gmx.de>
* @version $Id$
*
* @ingroup ModulesSession
*/
class ilSessionParticipantsTableGUI extends ilTable2GUI
{
	const TYPE_ADMIN = 'admins';
	const TYPE_TUTOR = 'tutors';
	const TYPE_MEMBER = 'members';
	
	private $role_type = '';
	
	private $session_participants = null;
	private $participants = array();
	private $reg_enabled = true;
	
	/**
	 * Constructor
	 *
	 * @access public
	 * @param object parent object
	 * @return
	 */
	public function __construct($a_parent_obj,$a_type = self::TYPE_ADMIN, $a_show_content = true)
	{
	 	global $lng,$ilCtrl;
	 	
	 	$this->lng = $lng;
		$this->lng->loadLanguageModule('sess');
		$this->lng->loadLanguageModule('crs');
		$this->lng->loadLanguageModule('trac');
	 	$this->ctrl = $ilCtrl;
		
		$this->role_type = $a_type;
		
		
        $this->setId('sess_'.$a_type.'_'.$a_parent_obj->object->getId());
		
		parent::__construct($a_parent_obj,'members');
		
		$this->setFormName('participants');
		
		switch($a_type)
		{
			case self::TYPE_ADMIN:
				$this->setPrefix('admins');
				break;
			case self::TYPE_TUTOR:
				$this->setPrefix('tutors');
				break;
			case self::TYPE_MEMBER:
				$this->setPrefix('member');
				break;
		}
		$this->setSelectAllCheckbox($this->getRoleType());
		$this->setShowRowsSelector(TRUE);
		
		if($a_show_content)
		{
			$this->enable('sort');
			$this->enable('header');
			$this->enable('numinfo');
			$this->enable('select_all');			
		}
		else
		{
			$this->disable('content');
			$this->disable('header');
			$this->disable('footer');
			$this->disable('numinfo');
			$this->disable('select_all');
		}		

		$this->session_participants = new ilEventParticipants($this->getParentObject()->object->getId());
	}
	
	public function getRoleType()
	{
		return $this->role_type;
	}
	
	/**
	 * enable registration
	 *
	 * @access public
	 * @param bool status
	 * @return
	 */
	public function enableRegistration($a_status)
	{
		$this->reg_enabled = $a_status;
	}
	
	/**
	 * is registration enabled
	 *
	 * @access public
	 * @return
	 */
	public function isRegistrationEnabled()
	{
		return $this->reg_enabled;
	}
	
	/**
	 * set participants
	 *
	 * @access public
	 * @param array participants
	 * @return
	 */
	public function setParticipants($a_part)
	{
		$this->participants = $a_part;
	}
	
	/**
	 * get participants
	 *
	 * @access public
	 * @return
	 */
	public function getParticipants()
	{
		return $this->participants;
	}
	
	/**
	 * parse table
	 *
	 * @access public
	 * @return
	 */
	public function parse()
	{
		$this->init();
		
		foreach($this->getParticipants() as $participant_id)
		{
			$usr_data = $this->session_participants->getUser($participant_id);
			
			$tmp_data['id'] = $participant_id;
			$name = ilObjUser::_lookupName($participant_id);
			
			$tmp_data['name'] = $name['lastname'];
			$tmp_data['lastname'] = $name['lastname'];
			$tmp_data['firstname'] = $name['firstname'];
			$tmp_data['login'] = ilObjUser::_lookupLogin($participant_id);
			$tmp_data['mark'] = $usr_data['mark'];
			$tmp_data['comment'] = $usr_data['comment'];
			$tmp_data['participated'] = $this->session_participants->hasParticipated($participant_id);
			$tmp_data['registered'] = $this->session_participants->isRegistered($participant_id);
			
			$part[] = $tmp_data;
		}
		$this->setData($part ? $part : array());
	}
	
	/**
	 * fill row
	 *
	 * @access public
	 * @param array data set
	 */
	public function fillRow($a_set)
	{		
		$this->tpl->setVariable('VAL_POSTNAME',$this->getRoleType());

		if($this->isRegistrationEnabled())
		{
			$this->tpl->setCurrentBlock('registered_col');
			$this->tpl->setVariable('VAL_ID',$a_set['id']);
			$this->tpl->setVariable('REG_CHECKED',$a_set['registered'] ? 'checked="checked"' : '');			
			$this->tpl->parseCurrentBlock();
		}
				
		$this->tpl->setVariable('VAL_ID',$a_set['id']);
		$this->tpl->setVariable('LASTNAME',$a_set['lastname']);
		$this->tpl->setVariable('FIRSTNAME',$a_set['firstname']);
		$this->tpl->setVariable('LOGIN',$a_set['login']);
		$this->tpl->setVariable('MARK',$a_set['mark']);
		$this->tpl->setVariable('COMMENT',$a_set['comment']);
		$this->tpl->setVariable('PART_CHECKED',$a_set['participated'] ? 'checked="checked"' : '');		
	}
	
	
	/**
	 * init table
	 *
	 * @access protected
	 * @param
	 * @return
	 */
	protected function init()
	{
		$this->setFormName('participants');
		#$this->setFormAction($this->ctrl->getFormAction($this->getParentObject(),'members'));

        $this->addColumn('','f',"1");
	 	$this->addColumn($this->lng->txt('name'),'name','20%');
		$this->addColumn($this->lng->txt('login'),'login','10%');
	 	$this->addColumn($this->lng->txt('trac_mark'),'mark');
	 	$this->addColumn($this->lng->txt('trac_comment'),'comment');
		if($this->isRegistrationEnabled())
		{
			$this->addColumn($this->lng->txt('event_tbl_registered'),'registered');
		}
		$this->addColumn($this->lng->txt('event_tbl_participated'),'participated');
		$this->setRowTemplate("tpl.sess_members_row.html","Modules/Session");
		if($this->isRegistrationEnabled())
		{
			$this->setDefaultOrderField('registered');
			$this->setDefaultOrderDirection('desc');
		}
		else
		{
			$this->setDefaultOrderField('name');
		}
	}
}
?>