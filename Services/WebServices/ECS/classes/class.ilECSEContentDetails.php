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
	public $receivers = array();
	public $url = array();
	public $content_type = array();

	private $receiver_info = array();


	/**
	 * Constructor
	 */
	public function __construct()
	{

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
	 * Get recievers
	 * @return <type>
	 */
	public function getReceivers()
	{
		return (array) $this->receivers;
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


	/**
	 * Load from JSON object
	 *
	 * @access public
	 * @param object JSON object
	 * @throws ilECSReaderException
	 */
	public function loadFromJson($json)
	{
		global $ilLog;

		if(!is_object($json))
		{
		 	include_once('./Services/WebServices/ECS/classes/class.ilECSReaderException.php');
			$ilLog->write(__METHOD__.': Cannot load from JSON. No object given.');
			throw new ilECSReaderException('Cannot parse ECS content details.');
		}

		foreach((array) $json->senders as $sender)
		{
			$this->senders[] = $sender->mid;
		}

		foreach((array) $json->receivers as $receiver)
		{
			$this->receivers[] = $receiver->mid;
		}

		// Collect in one array
		for($i = 0; $i < count($this->getReceivers()); ++$i)
		{
			$this->receiver_info[$this->sender[$i]] = $this->receivers[$i];
		}

		$this->url = $json->url;
		$this->content_type = $json->content_type;
		return true;
	}
}
?>
