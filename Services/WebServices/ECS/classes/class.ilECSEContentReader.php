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
* @author Stefan Meyer <smeyer@databay.de>
* @version $Id$
* 
* 
* @ingroup ServicesWebServicesECS 
*/
class ilECSEContentReader
{
	protected $log;
	protected $settings = null;
	protected $connector = null;
	
	protected $econtent = array();
	protected $econtent_id = 0;

	/**
	 * Constructor
	 *
	 * @access public
	 * @throws ilECSConnectorException 
	 */
	public function __construct($a_econtent_id = 0)
	{
	 	global $ilLog;
	 	
	 	include_once('Services/WebServices/ECS/classes/class.ilECSSettings.php');
	 	include_once('Services/WebServices/ECS/classes/class.ilECSConnector.php');
		include_once('Services/WebServices/ECS/classes/class.ilECSConnectorException.php');
		include_once('Services/WebServices/ECS/classes/class.ilECSEContent.php');
		include_once('Services/WebServices/ECS/classes/class.ilECSReaderException.php');
	 	
	 	$this->settings = ilECSSettings::_getInstance();
	 	$this->connector = new ilECSConnector();
	 	$this->log = $ilLog;
	 	
	 	$this->econtent_id = $a_econtent_id;
	}
	
	/**
	 * get resources
	 *
	 * @access public
	 * 
	 */
	public function getEContent()
	{
	 	return $this->econtent ? $this->econtent : array();
	}
	
	/**
	 * Read
	 *
	 * @access public
	 * @return bool false in case og HTTP 404
	 * @throws ilECSConnectorException, ilECSReaderException
	 */
	public function read()
	{
	 	global $ilLog;
	 	
	 	try
	 	{
	 		$res = $this->connector->getResources($this->econtent_id);
	 		
	 		if($res->getHTTPCode() == ilECSConnector::HTTP_CODE_NOT_FOUND)
	 		{
	 			return false;
	 		}
	
			if(!is_array($res->getResult()))
			{
				$ilLog->write(__METHOD__.': Error parsing result. Expected result of type array.');
				throw new ilECSReaderException('Error parsing query');
			}
			foreach($res->getResult() as $econtent)
			{
				$tmp_content = new ilECSEContent();
				$tmp_content->loadFromJSON($econtent);
				
				$this->econtent[] = $tmp_content;
			}	 		
			return true;
	 	}
	 	catch(ilECSConnectorException $e)
	 	{
	 		$ilLog->write(__METHOD__.': Error connecting to ECS server. '.$e->getMessage());
	 		throw $e;
	 	}
	 	catch(ilECSReaderException $e)
	 	{
	 		$ilLog->write(__METHOD__.': Error reading EContent. '.$e->getMessage());
	 		throw $e;
	 	}
	}
	
	
}


?>