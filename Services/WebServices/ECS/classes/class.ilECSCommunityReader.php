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
* @author Stefan Meyer <smeyer@databay.de>
* @version $Id$
* 
* 
* @ilCtrl_Calls 
* @ingroup ServicesWebServicesECS 
*/

class ilECSCommunityReader
{
	private static $instance = null;

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
	private function __construct()
	{
	 	global $ilLog;
	 	
	 	include_once('Services/WebServices/ECS/classes/class.ilECSSettings.php');
	 	include_once('Services/WebServices/ECS/classes/class.ilECSConnector.php');
		include_once('Services/WebServices/ECS/classes/class.ilECSConnectorException.php');
		include_once('Services/WebServices/ECS/classes/class.ilECSCommunity.php');
	 	
	 	$this->settings = ilECSSettings::_getInstance();
	 	$this->connector = new ilECSConnector();
	 	$this->log = $ilLog;
	 	
	 	$this->read();
	}
	
	/**
	 * get singleton instance
	 *
	 * @access public
	 * @static
	 *
	 */
	public static function _getInstance()
	{
		if(self::$instance)
		{
			return self::$instance;
		}
		return self::$instance = new ilECSCommunityReader();
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
	 	foreach($this->getCommunities() as $community)
	 	{
	 		foreach($community->getParticipants() as $participant)
	 		{
	 			if($participant->isEnabled())
	 			{
	 				$e_part[] = $participant;
	 			}
	 		}
	 	}
	 	return $e_part ? $e_part : array();
	}
	
	/**
	 * parse
	 *
	 * @access private
	 * @param
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
	/*
	// Iterator methods
	public function rewind() 
	{
    	$this->position = 0;
	}
 
	public function valid() 
	{
    	return $this->position < sizeof($this->communities);
	}
 
	public function key() 
	{
    	return $this->position;
	}
 
	public function current() 
	{
    	return $this->communities[$this->position];
	}
 
	public function next() 
	{
    	$this->position++;
  	}
  	*/
}
?>