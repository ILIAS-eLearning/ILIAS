<?php
/* Copyright (c) 1998-2010 ILIAS open source, Extended GPL, see docs/LICENSE */

/**
* Stores registration keys for key based registration on courses and groups
*
* @author Stefan Meyer <meyer@leifos.com>
*
* @version $Id$
*
* @ingroup ServicesMembership
*/
class ilMembershipRegistrationCodeUtils
{
	const CODE_LENGTH = 10;
	

	/**
	 * Handle target parameter
	 * @param object $a_target
	 * @return 
	 */
	public static function handleCode($a_ref_id,$a_type,$a_code)
	{
		global $lng, $tree, $ilUser;
		include_once './Services/Link/classes/class.ilLink.php';
		$lng->loadLanguageModule($a_type);
		try
		{
			self::useCode($a_code,$a_ref_id);
			$title =  ilObject::_lookupTitle(ilObject::_lookupObjectId($a_ref_id));
			ilUtil::sendSuccess(sprintf($lng->txt($a_type."_admission_link_success_registration"),$title),true);
			ilUtil::redirect(ilLink::_getLink($a_ref_id));
		}
		catch(ilMembershipRegistrationException $e)
		{
			switch($e->getCode())
			{
				case 124://added to waiting list
					ilUtil::sendSuccess($e->getMessage(),true);
					break;
				case 123://object is full
					ilUtil::sendFailure($lng->txt($a_type."_admission_link_failure_membership_limited"), true);
					break;
				case 789://out of registration period
					ilUtil::sendFailure($lng->txt($a_type."_admission_link_failure_registration_period"), true);
					break;
				default:
					ilUtil::sendFailure($e->getMessage(), true);
					break;
			}
			$GLOBALS['ilLog']->logStack();
			$GLOBALS['ilLog']->write($e->getCode().': '.$e->getMessage());

			$parent_id = $tree->getParentId($a_ref_id);
			ilUtil::redirect(ilLink::_getLink($parent_id));
		}
	}
	
	
	
	/**
	 * Use a registration code and assign the logged in user
	 * to the (parent) course/group that offer the code.
	 * 
	 * @todo: throw an error if registration fails (max members, availibility...)
	 * 
	 * @param string $a_code
	 * @param int $a_endnode Reference id of node in tree
	 * @return 
	 */
	protected static function useCode($a_code,$a_endnode)
	{
		global $tree,$ilUser;
		
		$obj_ids = self::lookupObjectsByCode($a_code);
		foreach($tree->getPathId($a_endnode) as $ref_id)
		{
			if(in_array(ilObject::_lookupObjId($ref_id), $obj_ids))
			{
				if($obj = ilObjectFactory::getInstanceByRefId($ref_id,false))
				{
					$obj->register($ilUser->getId());
				}
			}
		}
	}
	
	/**
	 * Generate new registration key
	 * @return 
	 */
	public static function generateCode()
	{
		// missing : 01iloO
		$map = "23456789abcdefghjkmnpqrstuvwxyzABCDEFGHJKLMNPQRSTUVWXYZ";
		
		$code = "";
		$max = strlen($map)-1;
		for($loop = 1; $loop <= self::CODE_LENGTH; $loop++)
		{
		  $code .= $map[mt_rand(0, $max)];
		}
		return $code;
	}
	
	/**
	 * Get all objects with enabled access codes
	 * @param string $a_code
	 * @return 
	 */
	protected static function lookupObjectsByCode($a_code)
	{
		include_once './Modules/Group/classes/class.ilObjGroup.php';
		include_once './Modules/Course/classes/class.ilObjCourse.php';
		
		return array_merge(ilObjGroup::lookupObjectsByCode($a_code), ilObjCourse::lookupObjectsByCode($a_code));
	}
}
?>