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


/** 
*
* 
* @author Stefan Meyer <meyer@leifos.com>
* @version $Id$
* 
* 
* @ilCtrl_Calls 
* @ingroup ServicesWebServicesECS 
*/

class ilECSCommunityReader
{
	private static $instances = null;

	protected $position = 0;

	protected $log;
	protected $settings = null;
	protected $connector = null;
	
	protected $communities = array();
	protected $participants = array();
	protected $own_ids = array();

	/**
	 * Singleton constructor
	 *
	 * @access private
	 * @throws ilECSConnectorException 
	 */
	private function __construct(ilECSSetting $setting = null)
	{
	 	global $ilLog;
	 	
	 	include_once('Services/WebServices/ECS/classes/class.ilECSSetting.php');
	 	include_once('Services/WebServices/ECS/classes/class.ilECSConnector.php');
		include_once('Services/WebServices/ECS/classes/class.ilECSConnectorException.php');
		include_once('Services/WebServices/ECS/classes/class.ilECSCommunity.php');

		if($setting)
		{
			$this->settings = $setting;
		}
		else
		{
			$GLOBALS['ilLog']->write(__METHOD__.': Using deprecated call');
			$GLOBALS['ilLog']->logStack();
		}
	 	$this->connector = new ilECSConnector($this->settings);
	 	$this->log = $ilLog;
	 	
	 	$this->read();
	}
	
	/**
	 * get singleton instance
	 *
	 * @access public
	 * @static
	 * @return ilECSCommunityReader
	 */
	public static function _getInstance()
	{
		$GLOBALS['ilLog']->write(__METHOD__.': Using deprecated call');
		return self::getInstanceByServerId(15);
	}

	/**
	 * Get instance by server id
	 * @param int $a_server_id
	 * @return ilECSCommunityReader
	 */
	public static function getInstanceByServerId($a_server_id)
	{
		if(isset(self::$instances[$a_server_id]))
		{
			return self::$instances[$a_server_id];
		}
		return self::$instances[$a_server_id] = new ilECSCommunityReader(ilECSSetting::getInstanceByServerId($a_server_id));
	}

	/**
	 * Get server setting
	 * @return ilECSSetting
	 */
	public function getServer()
	{
		return $this->settings;
	}
	
	/**
	 * Get participants
	 * @return ilECSParticipant[]
	 */
	public function getParticipants()
	{
		return $this->participants;
	}


	/**
	 * get publishable ids
	 *
	 * @access public
	 * 
	 */
	public function getOwnMIDs()
	{
	 	return $this->own_ids ? $this->own_ids : array();
	}
	
	/**
	 * get communities
	 *
	 * @access public
	 * @param
	 * 
	 */
	public function getCommunities()
	{
	 	return $this->communities ? $this->communities : array();
	}
	
	/**
	 * get community by id
	 *
	 * @access public
	 * @param int comm_id
	 * 
	 */
	public function getCommunityById($a_id)
	{
	 	foreach($this->communities as $community)
	 	{
	 		if($community->getId() == $a_id)
	 		{
	 			return $community;
	 		}
	 	}
	 	return null;
	}
	
	/**
	 * get participant by id
	 *
	 * @access public
	 * @param int mid 
	 */
	public function getParticipantByMID($a_mid)
	{
	 	return isset($this->participants[$a_mid]) ? $this->participants[$a_mid] : false;
	}

	/**
	 * Get community by mid
	 * @param int $a_mid
	 * @return ilECSCommunity
	 */
	public function getCommunityByMID($a_mid)
	{
		foreach($this->communities as $community)
		{
			foreach($community->getParticipants() as $part)
			{
				if($part->getMID() == $a_mid)
				{
					return $community;
				}
			}
		}
		return null;
	}
	
	/**
	 * get publishable communities
	 *
	 * @access public
	 * 
	 */
	public function getPublishableParticipants()
	{
	 	foreach($this->getCommunities() as $community)
	 	{
	 		foreach($community->getParticipants() as $participant)
	 		{
	 			if($participant->isPublishable())
	 			{
	 				$p_part[] = $participant;
	 			}
	 		}
	 	}
	 	return $p_part ? $p_part : array();
	}
	
	/**
	 * get enabled participants
	 *
	 * @access public
	 * 
	 */
	public function getEnabledParticipants()
	{
		include_once './Services/WebServices/ECS/classes/class.ilECSParticipantSettings.php';
		$ps = ilECSParticipantSettings::getInstanceByServerId($this->getServer()->getServerId());
		$en = $ps->getEnabledParticipants();
		foreach($this->getCommunities() as $community)
	 	{
	 		foreach($community->getParticipants() as $participant)
	 		{
	 			if(in_array($participant->getMid(), $en))
				{
					$e_part[] = $participant;
				}
	 		}
	 	}
	 	return $e_part ? $e_part : array();
	}

	/**
	 * Read
	 * @access private
	 * @throws ilECSConnectorException
	 * 
	 */
	private function read()
	{
	 	global $ilLog;
	 	
	 	try
	 	{
	 		$res = $this->connector->getMemberships();
			if(!is_array($res->getResult()))
			{
				return false;
			}
			foreach($res->getResult() as $community)
			{
				$tmp_comm = new ilECSCommunity($community);
				foreach($tmp_comm->getParticipants() as $participant)
				{
					$this->participants[$participant->getMID()] = $participant;
					if($participant->isSelf())
					{
						$this->own_ids[] = $participant->getMID();
					}
				}
				$this->communities[] = $tmp_comm;
			}	 		
	 	}
	 	catch(ilECSConnectorException $e)
	 	{
	 		$ilLog->write(__METHOD__.': Error connecting to ECS server. '.$e->getMessage());
	 		throw $e;
	 	}
	}
}
?>