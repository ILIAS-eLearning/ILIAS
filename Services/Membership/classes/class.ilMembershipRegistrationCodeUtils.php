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
		include_once './Services/Link/classes/class.ilLink.php';
		try
		{
			self::useCode($a_code,$a_ref_id);
			ilUtil::redirect(ilLink::_getLink($a_ref_id,ilObject::_lookupType(ilObject::_lookupObjId($a_ref_id))));
		}
		catch(Exception $e) 
		{
			$GLOBALS['ilLog']->logStack();
			$GLOBALS['ilLog']->write($e->getMessage());
			ilUtil::redirect(ilLink::_getLink($e->getCode(),ilObject::_lookupType(ilObject::_lookupObjId($e->getCode()))));
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
					$GLOBALS['ilLog']->logStack();
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