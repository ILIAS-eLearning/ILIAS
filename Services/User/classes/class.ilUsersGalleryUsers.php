<?php
/* Copyright (c) 1998-2015 ILIAS open source, Extended GPL, see docs/LICENSE */
require_once 'Services/User/classes/class.ilAbstractGalleryUsers.php';
require_once 'Services/Contact/BuddySystem/classes/class.ilBuddyList.php';
/**
 * Class ilUsersGalleryUsers
 */
class ilUsersGalleryUsers extends ilAbstractGalleryUsers
{
	/**
	 * @return array
	 */
	protected function getSortedRelations()
	{
		$requested_for_me = ilBuddyList::getInstanceByGlobalUser()->getRequestRelationsForOwner()->toArray();
		$linked           = ilBuddyList::getInstanceByGlobalUser()->getLinkedRelations()->toArray();
		$requested_by_me  = ilBuddyList::getInstanceByGlobalUser()->getRequestRelationsByOwner()->toArray();
		$me_ignored       = ilBuddyList::getInstanceByGlobalUser()->getIgnoredRelationsByOwner()->toArray();
		$ignored          = ilBuddyList::getInstanceByGlobalUser()->getIgnoredRelationsForOwner()->toArray();

		return array($requested_for_me, $linked, $requested_by_me + $me_ignored,  $ignored);
	}

    /**
     * @param bool $ignore_myself
     * @return array
     */
    public function getGalleryUsers($ignore_myself = false)
	{
        /**
         * @var $ilUser ilObjUser
         */
        global $ilUser;
		$relations    = $this->getSortedRelations();
		$ordered_data = array();
		foreach($relations as $sorted_relation)
		{
			$user_data = array();
			foreach($sorted_relation as $key => $users)
			{
				/**
				 * @var $user ilObjUser
				 */
				if(!($user = ilObjectFactory::getInstanceByObjId($key, false)))
				{
					continue;
				}
				if(!$user->getActive())
				{
					continue;
				}

				if($ignore_myself && $user->getId() == $ilUser->getId())
				{
					continue;
				}

				$user_data[$user->getId()] = array(
					'id'   => $user->getId(),
					'user' => $user
				);
			}
			$user_data    = $this->collectUserDetails($user_data);
			$ordered_data = array_merge($ordered_data, ilUtil::sortArray($user_data, 'sort', 'asc'));
		}
		return $ordered_data;
	}

	/**
	 * @return string
	 */
	public function getUserCssClass()
	{
		return 'ilBuddySystemRemoveWhenUnlinked';
	}
}