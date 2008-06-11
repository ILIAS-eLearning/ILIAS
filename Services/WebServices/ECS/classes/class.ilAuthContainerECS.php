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

include_once('Auth/Container.php');

/** 
* Custom PEAR Auth Container for ECS auth checks
* 
* @author Stefan Meyer <smeyer@databay.de>
* @version $Id$
* 
* @ingroup ServicesWebServicesECS 
*/
class ilAuthContainerECS extends Auth_Container
{
	protected $mid = null;
	protected $abreviation = null;

	/**
	 * Constructor
	 *
	 * @access public
	 * @param
	 * 
	 */
	public function __construct($a_params)
	{
	 	parent::__construct(array());
		$this->initECSServices();
	}
	
	/**
	 * get abbreviation
	 *
	 * @access public
	 * @param
	 * 
	 */
	public function getAbreviation()
	{
	 	return $this->abreviation;
	}
	
	/**
	 * get mid
	 *
	 * @access public
	 */
	public function getMID()
	{
		return $this->mid;	 	
	}
	
	
	/**
	 * fetch data
	 *
	 * @access public
	 * @param string username
	 * @param string pass
	 * 
	 */
	public function fetchData($a_username,$a_pass)
	{
		global $ilLog;
		
		$ilLog->write(__METHOD__.': Starting ECS authentication.');
		
		if(!$this->settings->isEnabled())
		{
			$ilLog->write(__METHOD__.': ECS settings .');
			return false;
		}

	 	// Check if hash is valid ...
	 	include_once('./Services/WebServices/ECS/classes/class.ilECSConnector.php');
	 	
	 	try
	 	{
	 		$connector = new ilECSConnector();
	 		$res = $connector->getAuth($_GET['ecs_hash']);
			$auths = $res->getResult();
			$this->mid = $auths[0]->mid;
			$ilLog->write(__METHOD__.': Got mid: '.$this->mid);
			$this->abreviation = $auths[0]->abr;
			$ilLog->write(__METHOD__.': Got abr: '.$this->abreviation);
			/*			
			// Read abbreviation from mid
			$res = $connector->getMemberships($this->mid);
			$member = $res->getResult();
			$this->abbreviation = $member[0]->participants[0]->abr;
			*/
		 	return true;
	 	}
	 	catch(ilECSConnectorException $e)
	 	{
	 		$ilLog->write(__METHOD__.': Authentication failed with message: '.$e->getMessage());
	 		return false;
	 	}
	}

	/**
	 * Init ECS Services
	 * @access private
	 * @param
	 * 
	 */
	private function initECSServices()
	{
	 	include_once('./Services/WebServices/ECS/classes/class.ilECSSettings.php');
	 	$this->settings = ilECSSettings::_getInstance();
	}
	
}


?>