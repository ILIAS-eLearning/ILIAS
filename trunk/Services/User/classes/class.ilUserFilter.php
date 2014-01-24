<?php
/* Copyright (c) 1998-2009 ILIAS open source, Extended GPL, see docs/LICENSE */


include_once './Services/User/classes/class.ilUserAccountSettings.php';

/**
 * @classDescription user filter
 *
 * @author Stefan Meyer <smeyer.ilias@gmx.de>
 * @version $Id$
 *
 * @ingroup ServicesUser
 */
class ilUserFilter
{
	
	private static $instance = null;

	private $settings = null;
	
	private $folder_ids = array();


	/**
	 * Singleton constructor
	 * @return 
	 */
	protected function __construct()
	{
		$this->init();
	}

	/**
	 * Singelton get instance
	 * @return object ilUserFilter
	 */
	public static function getInstance()
	{
		if(self::$instance)
		{
			return self::$instance;
		}
		return self::$instance = new ilUserFilter();
	}

	/**
	 * Filter user accounts
	 * @return 
	 */
	public function filter($a_user_ids)
	{
		global $ilDB;
		
		if(!ilUserAccountSettings::getInstance()->isUserAccessRestricted())
		{
			return $a_user_ids;
		}
		
		$query = "SELECT usr_id FROM usr_data ".
			"WHERE ".$ilDB->in('time_limit_owner',$this->folder_ids,false,'integer')." ".
			"AND ".$ilDB->in('usr_id',$a_user_ids,false,'integer');
		$res = $ilDB->query($query);
		
		$filtered = array();
		while($row = $res->fetchRow(DB_FETCHMODE_ASSOC))
		{
			$filtered[] = $row['usr_id'];
		}
		return $filtered;
	}
	
	/**
	 * Get accessible user folder (cat and usrf) ids.
	 * @return 
	 */
	public function getFolderIds()
	{
		return (array) $this->folder_ids;
	}

	/**
	 * Init 
	 * @return 
	 */
	private function init()
	{
		if(ilUserAccountSettings::getInstance()->isUserAccessRestricted())
		{
			include_once './Services/User/classes/class.ilLocalUser.php';
			$this->folder_ids = ilLocalUser::_getFolderIds();
		}
	}
}
?>