<?php
/** Copyright (c) 1998-2015 ILIAS open source, Extended GPL, see docs/LICENSE */

/**
 * Interface ilUsersGalleryParticipants
 */
interface ilUsersGalleryParticpants
{
	/**
	 * @return int[]
	 */
	public function getContacts();
	
	/**
	 * @return int[]
	 */
	public function getAdmins();
	
	/**
	 * @return int[]
	 */
	public function getTutors();
	
	/**
	 * @return int[]
	 */
	public function getMembers();
}
?>