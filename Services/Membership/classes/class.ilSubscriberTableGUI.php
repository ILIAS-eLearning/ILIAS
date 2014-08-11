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
* GUI class for course/group subscriptions
* 
* @author Stefan Meyer <smeyer.ilias@gmx.de>
* @version $Id$
*
* @ingroup ServicesMembership 
*/
class ilSubscriberTableGUI extends ilTable2GUI
{
	protected $subscribers = array();

	protected static $all_columns = null;
	
	
	/**
	 * Constructor
	 *
	 * @access public
	 * @param
	 * @return
	 */
	public function __construct($a_parent_obj,$show_content = true)
	{
	 	global $lng,$ilCtrl;
	 	
	 	$this->lng = $lng;
		$this->lng->loadLanguageModule('grp');
		$this->lng->loadLanguageModule('crs');
	 	$this->ctrl = $ilCtrl;
	 	
		$this->setId('crs_sub_'. $a_parent_obj->object->getId());
		parent::__construct($a_parent_obj,'members');

		$this->setFormName('subscribers');
		$this->setFormAction($this->ctrl->getFormAction($a_parent_obj,'members'));

	 	$this->addColumn('','f',"1");
	 	$this->addColumn($this->lng->txt('name'),'lastname','20%');

		$all_cols = $this->getSelectableColumns();
		foreach($this->getSelectedColumns() as $col)
		{
			$this->addColumn($all_cols[$col]['txt'], $col);
		}
		
	 	$this->addColumn($this->lng->txt('application_date'),'sub_time',"10%");
		$this->addColumn($this->lng->txt('subject'),'subject','15%');
		$this->addColumn('','mail','10%');
		
		$this->addMultiCommand('assignSubscribers',$this->lng->txt('assign'));
		$this->addMultiCommand('refuseSubscribers',$this->lng->txt('refuse'));
		$this->addMultiCommand('sendMailToSelectedUsers',$this->lng->txt('crs_mem_send_mail'));
		

		$this->setPrefix('subscribers');
		$this->setSelectAllCheckbox('subscribers');
		$this->setRowTemplate("tpl.show_subscribers_row.html","Services/Membership");
		
		if($show_content)
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
	}
	
	/**
	 * Get selectable columns
	 * @return 
	 */
	public function getSelectableColumns()
	{
		if(self::$all_columns)
		{
			return self::$all_columns;
		}

		include_once './Services/PrivacySecurity/classes/class.ilExportFieldsInfo.php';
		$ef = ilExportFieldsInfo::_getInstanceByType($this->getParentObject()->object->getType());
		self::$all_columns = $ef->getSelectableFieldsInfo($this->getParentObject()->object->getId());
		return self::$all_columns;
	}
	
	
	/**
	 * fill row 
	 *
	 * @access public
	 * @param
	 * @return
	 */
	public function fillRow($a_set)
	{
		global $ilUser;
		
				
		include_once './Modules/Course/classes/class.ilObjCourseGrouping.php';
		if(!ilObjCourseGrouping::_checkGroupingDependencies($this->getParentObject()->object,$a_set['id']) and
			($ids = ilObjCourseGrouping::getAssignedObjects()))
		{
			$prefix = $this->getParentObject()->object->getType();
			$this->tpl->setVariable('ALERT_MSG',
				sprintf($this->lng->txt($prefix.'_lim_assigned'),
				ilObject::_lookupTitle(current($ids))
				));
				
		}

		$this->tpl->setVariable('VAL_ID',$a_set['usr_id']);
		$this->tpl->setVariable('VAL_NAME',$a_set['lastname'].', '.$a_set['firstname']);
		
		foreach($this->getSelectedColumns() as $field)
		{
			switch($field)
			{
				case 'gender':
					$a_set['gender'] = $a_set['gender'] ? $this->lng->txt('gender_' . $a_set['gender']) : '';
					$this->tpl->setCurrentBlock('custom_fields');
					$this->tpl->setVariable('VAL_CUST', $a_set[$field]);
					$this->tpl->parseCurrentBlock();
					break;

				case 'birthday':
					$a_set['birthday'] = $a_set['birthday'] ? ilDatePresentation::formatDate(new ilDate($a_set['birthday'], IL_CAL_DATE)) : $this->lng->txt('no_date');
					$this->tpl->setCurrentBlock('custom_fields');
					$this->tpl->setVariable('VAL_CUST', $a_set[$field]);
					$this->tpl->parseCurrentBlock();
					break;

				default:
					$this->tpl->setCurrentBlock('custom_fields');
					$this->tpl->setVariable('VAL_CUST', isset($a_set[$field]) ? (string) $a_set[$field] : '');
					$this->tpl->parseCurrentBlock();
					break;
			}
		}
		
		
		$this->tpl->setVariable('VAL_SUBTIME',ilDatePresentation::formatDate(new ilDateTime($a_set['sub_time'],IL_CAL_UNIX)));
		
		$this->ctrl->setParameterByClass(get_class($this->getParentObject()),'member_id',$a_set['usr_id']);
		$link = $this->ctrl->getLinkTargetByClass(get_class($this->getParentObject()),'sendMailToSelectedUsers');
		$this->tpl->setVariable('MAIL_LINK',$link);
		$this->tpl->setVariable('MAIL_TITLE',$this->lng->txt('crs_mem_send_mail'));
		
		
		if(strlen($a_set['subject']))
		{
			$this->tpl->setVariable('VAL_SUBJECT','"'.$a_set['subject'].'"');
			
		}
	}
	
	/**
	 * read data
	 *
	 * @access protected
	 * @param
	 * @return
	 */
	public function readSubscriberData()
	{
		include_once './Services/Membership/classes/class.ilParticipants.php';
		
		$sub_data = ilParticipants::lookupSubscribersData($this->getParentObject()->object->getId());
		
		$sub_ids = array();
		foreach($sub_data as $usr_id => $usr_data)
		{
			$sub_ids[] = $usr_id;
		}
		
		$this->determineOffsetAndOrder();

		include_once './Services/User/classes/class.ilUserQuery.php';

		$additional_fields = $this->getSelectedColumns();
		unset($additional_fields["firstname"]);
		unset($additional_fields["lastname"]);
		unset($additional_fields["last_login"]);
		unset($additional_fields["access_until"]);

		$udf_ids = $usr_data_fields = $odf_ids = array();
		foreach($additional_fields as $field)
		{
			if(substr($field, 0, 3) == 'udf')
			{
				$udf_ids[] = substr($field, 4);
				continue;
			}
			if(substr($field, 0, 3) == 'odf')
			{
				$odf_ids[] = substr($field, 4);
				continue;
			}

			$usr_data_fields[] = $field;
		}

		$usr_data = ilUserQuery::getUserListData(
			$this->getOrderField(),
			$this->getOrderDirection(),
			$this->getOffset(),
			$this->getLimit(),
			'',
			'',
			null,
			false,
			false,
			0,
			0,
			null,
			$usr_data_fields,
			$sub_ids
		);
		
		foreach((array) $usr_data['set'] as $user)
		{
			$usr_ids[] = $user['usr_id'];
		}

		// merge course data
		$course_user_data = $this->getParentObject()->readMemberData($usr_ids,$this->type == 'admin');
		$a_user_data = array();
		foreach((array) $usr_data['set'] as $ud)
		{			
			$a_user_data[$ud['usr_id']] = array_merge($ud,(array) $course_user_data[$ud['usr_id']]);
		}

		// Custom user data fields
		if($udf_ids)
		{
			include_once './Services/User/classes/class.ilUserDefinedData.php';
			$data = ilUserDefinedData::lookupData($usr_ids, $udf_ids);
			foreach($data as $usr_id => $fields)
			{
				if(!$this->checkAcceptance($usr_id))
				{
					continue;
				}

				foreach($fields as $field_id => $value)
				{
					$a_user_data[$usr_id]['udf_' . $field_id] = $value;
				}
			}
		}
		// Object specific user data fields
		if($odf_ids)
		{
			include_once './Modules/Course/classes/Export/class.ilCourseUserData.php';
			$data = ilCourseUserData::_getValuesByObjId($this->getParentObject()->object->getId());
			foreach($data as $usr_id => $fields)
			{
				// #7264: as we get data for all course members filter against user data
				if(!$this->checkAcceptance($usr_id) || !in_array($usr_id, $usr_ids))
				{
					continue;
				}

				foreach($fields as $field_id => $value)
				{
					$a_user_data[$usr_id]['odf_' . $field_id] = $value;
				}
			}
		}

		foreach($usr_data['set'] as $user)
		{
			// Check acceptance
			if(!$this->checkAcceptance($user['usr_id']))
			{
				continue;
			}
			// DONE: accepted
			foreach($usr_data_fields as $field)
			{
				$a_user_data[$user['usr_id']][$field] = $user[$field] ? $user[$field] : '';
			}
		}
		
		// Waiting list subscription
		foreach($sub_data as $usr_id => $usr_data)
		{
			$a_user_data[$usr_id]['sub_time'] = $usr_data['time'];
			$a_user_data[$usr_id]['subject'] = $usr_data['subject'];
		}
		
		$this->setMaxCount($usr_data['cnt'] ? $usr_data['cnt'] : 0);
		return $this->setData($a_user_data);
	}
	
	protected function checkAcceptance()
	{
		return true;
	}
	
}
?>