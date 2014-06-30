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
 * Presentation of ecs content details (http://...campusconnect/courselinks/id/details)
 *
 * @author Stefan Meyer <smeyer.ilias@gmx.de>
 * @version $Id$
 *
 * @ingroup ServicesWebServicesECS
 */
class ilECSEContentDetails
{

	public $senders = array();
	public $sender_index = NULL;
	public $receivers = array();
	public $url = array();
	public $content_type = array();
	public $owner = 0;

	private $receiver_info = array();

	public function __construct()
	{
		
	}
	
	/**
	 * Get data from server
	 * 
	 * @param int $a_server_id
	 * @param int $a_econtent_id
	 * @param string $a_resource_type
	 * @return ilECSEContentDetails
	 */
	public static function getInstance($a_server_id, $a_econtent_id, $a_resource_type)
	{
		global $ilLog;
		
		try
		{
			include_once './Services/WebServices/ECS/classes/class.ilECSSetting.php';		
			include_once './Services/WebServices/ECS/classes/class.ilECSConnector.php';
			$connector = new ilECSConnector(ilECSSetting::getInstanceByServerId($a_server_id));		
			$res = $connector->getResource($a_resource_type, $a_econtent_id, true);
			if($res->getHTTPCode() == ilECSConnector::HTTP_CODE_NOT_FOUND)
			{			
				return;
			}
			if(!is_object($res->getResult()))
			{
				$ilLog->write(__METHOD__ . ': Error parsing result. Expected result of type array.');
				$ilLog->logStack();
				throw new ilECSConnectorException('error parsing json');
			}
		}
		catch(ilECSConnectorException $exc)
		{
			return;
		}

		include_once './Services/WebServices/ECS/classes/class.ilECSEContentDetails.php';
		$details = new self();
		$details->loadFromJSON($res->getResult());
		return $details;
	}

	/**
	 * Get senders
	 * @return array
	 */
	public function getSenders()
	{
		return (array) $this->senders;
	}

	/**
	 * get first sender
	 */
	public function getFirstSender()
	{
		return isset($this->senders[0]) ? $this->senders[0] : 0;
	}
	
	/**
	 * Get sender from whom we received the ressource
	 * According to the documentation the sender and receiver arrays have corresponding indexes.
	 */
	public function getMySender()
	{
		return $this->senders[$this->sender_index];
	}

	/**
	 * Get recievers
	 * @return <type>
	 */
	public function getReceivers()
	{
		return (array) $this->receivers;
	}
	
	/**
	 * Get first receiver
	 * @return int
	 */
	public function getFirstReceiver()
	{
		foreach ($this->getReceivers() as $mid)
		{
			return $mid;
		}
		return 0;
	}

	/**
	 * Get receiver info
	 * @return array
	 */
	public function getReceiverInfo()
	{
		return (array) $this->receiver_info;
	}

	/**
	 * Get url
	 * @return string
	 */
	public function getUrl()
	{
		return $this->url;
	}

	public function getOwner()
	{
		return (int) $this->owner;
	}

	/**
	 * Load from JSON object
	 *
	 * @access public
	 * @param object JSON object
	 * @throws ilException
	 */
	public function loadFromJson($json)
	{
		global $ilLog;

		if(!is_object($json))
		{
			$ilLog->write(__METHOD__.': Cannot load from JSON. No object given.');
			throw new ilException('Cannot parse ECS content details.');
		}

		foreach((array) $json->senders as $sender)
		{
			$this->senders[] = $sender->mid;
		}

		$index = 0;
		foreach((array) $json->receivers as $receiver)
		{
			$this->receivers[] = $receiver->mid;
			if($receiver->itsyou and $this->sender_index === NULL)
			{
				$this->sender_index = $index;
			}
			++$index;
		}

		// Collect in one array
		for($i = 0; $i < count($this->getReceivers()); ++$i)
		{
			$this->receiver_info[$this->sender[$i]] = $this->receivers[$i];
		}

		if(is_object($json->owner))
		{
			$this->owner = (int) $json->owner->pid;
		}

		$this->url = $json->url;
		$this->content_type = $json->content_type;
		return true;
	}
}
?>
