<?php
/* Copyright (c) 1998-2015 ILIAS open source, Extended GPL, see docs/LICENSE */

require_once 'Services/User/interfaces/interface.ilGalleryUsers.php';
require_once 'Services/User/classes/class.ilUserUtil.php';

/**
 * Class ilAbstractGalleryUsers
 */
abstract class ilAbstractGalleryUsers implements ilGalleryUsers
{
	/**
	 * @param array $user_data
	 * @return array
	 */
	protected function collectUserDetails(array $user_data)
	{
		/**
		 * @var $ilUser ilObjUser
		 */
		global $ilUser;

		$sortable_names = ilUserUtil::getNamePresentation(array_keys($user_data));
		$names          = ilUserUtil::getNamePresentation(array_keys($user_data), false, false, '', false, false, false);

		foreach($user_data as $id => &$data)
		{
			/**
			 * @var $user ilObjUser
			 */
			$user = $data['user'];

			$profile_published = false;
			if((!$ilUser->isAnonymous() && $user->getPref('public_profile') == 'y') || $user->getPref('public_profile') == 'g')
			{
				$profile_published = true;
			}

			$data['sort']             = $sortable_names[$user->getId()];
			$data['public_profile']   = $profile_published;
			$data['public_name']      = $names[$user->getId()];
		}

		return $user_data;
	}
}