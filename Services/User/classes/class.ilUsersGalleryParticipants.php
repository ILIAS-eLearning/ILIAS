<?php
/* Copyright (c) 1998-2015 ILIAS open source, Extended GPL, see docs/LICENSE */
require_once 'Services/User/classes/class.ilAbstractGalleryUsers.php';

/**
 * Class ilUsersGalleryParticipants
 */
class ilUsersGalleryParticipants extends ilAbstractGalleryUsers
{
	/**
	 * @var ilParticipants
	 */
	protected $participants;

	/**
	 * @param ilParticipants $participants
	 */
	public function __construct(ilParticipants $participants)
	{
		$this->participants = $participants;
	}

	/**
	 * @return array
	 */
	public function getGalleryUsers()
	{
		$participants_data = array();
		foreach($this->participants->getParticipants() as $users_id)
		{
			/**
			 * @var $user ilObjUser
			 */
			if(!($user = ilObjectFactory::getInstanceByObjId($users_id, false)))
			{
				continue;
			}

			if(!$user->getActive())
			{
				continue;
			}

			$participants_data[$user->getId()] = array(
				'id'   => $user->getId(),
				'user' => $user
			);
		}
		$participants_data = $this->collectUserDetails($participants_data);
		$ordered_user      = ilUtil::sortArray($participants_data, 'sort', 'asc');
		return $ordered_user;
	}
} 