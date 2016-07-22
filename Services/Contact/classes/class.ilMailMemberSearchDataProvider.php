<?php
/* Copyright (c) 1998-2015 ILIAS open source, Extended GPL, see docs/LICENSE */

/**
 * Class ilMailMemberSearchDataProvider
 *
 * @author Nadia Matuschek <nmatuschek@databay.de>
 *
 **/
class ilMailMemberSearchDataProvider
{
	/**
	 * @var string
	 */
	protected $type = 'crs';
	/**
	 * @var array
	 */
	protected $data = array();
	/**
	 * @var null
	 */
	protected $objParticipants = null;

	/**
	 * @var ilLanguage
	 */
	protected $lng;

	/**
	 * @param $objParticipants
	 */
	public function __construct($objParticipants)
	{
		global $DIC;

		$this->objParticipants = $objParticipants;
		$this->type            = $this->objParticipants->getType();
		$this->lng             = $DIC['lng'];

		$this->collectTableData();
	}

	/**
	 * 
	 */
	private function collectTableData()
	{
		$members = $this->objParticipants->getMembers();
		$admins = $this->objParticipants->getAdmins();
		
		$participants['il_'.$this->type.'_member'] = $members;
		$participants['il_'.$this->type.'_admin'] = $admins;
		if($this->type == 'crs')
		{
			$tutors = $this->objParticipants->getTutors();
			$participants['il_crs_tutor'] = $tutors;
		}
		
		foreach($participants as $role => $users )
		{
			foreach($users as $user_id)
			{
				$name  = ilObjUser::_lookupName($user_id);
				$login = ilObjUser::_lookupLogin($user_id);
				$fullname = $name['lastname'] . ', ' . $name['firstname'];

				$this->data[$fullname]['user_id'] = $user_id;
				$this->data[$fullname]['login']   = $login;
				$this->data[$fullname]['name']    = $fullname;
				$this->data[$fullname]['role']    = $this->lng->txt($role);
			}
		}
	}

	/**
	 * @return array
	 */
	public function getData()
	{
		return $this->data;
	}
}