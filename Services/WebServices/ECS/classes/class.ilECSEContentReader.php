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
 * @author Stefan Meyer <meyer@leifos.com>
 * @version $Id$
 *
 *
 * @ingroup ServicesWebServicesECS
 */
class ilECSEContentReader
{
	const ECS_QUERY_STRINGS = 'X-EcsQueryStrings';
	const ECS_HEADER_SENDER = 'X-EcsSender';
	const RECEIVER_ONLY = 'receiver=true';
	const SENDER_ONLY = 'sender=true';
	const ALL_ECONTENT = 'all=true';

	protected $log;
	protected $settings = null;
	protected $connector = null;
	protected $econtent = array();
	protected $econtent_details = array();
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
	 * Get econtent details
	 * @return array
	 */
	public function getEContentDetails()
	{
		return $this->econtent_details ? $this->econtent_details : array();
	}

	/**
	 * Read resource list
	 * @global ilLog $ilLog
	 * @return ilECSUriList
	 * @throws ilECSConnectorException, ilECSReaderException
	 */
	public function readResourceList($a_filter = null)
	{
		global $ilLog;

		try {
			$this->connector->addHeader(self::ECS_QUERY_STRINGS, $a_filter ? $a_filter : self::ALL_ECONTENT);
			$res = $this->connector->getResourceList();
			return $res->getResult();
		}
		catch(ilECSConnectorException $e)
		{
			$ilLog->write(__METHOD__ . ': Error connecting to ECS server. ' . $e->getMessage());
			throw $e;
		}
		catch(ilECSReaderException $e)
		{
			$ilLog->write(__METHOD__ . ': Error reading course links. ' . $e->getMessage());
			throw $e;
		}
	}

	/**
	 * Read
	 *
	 * @access public
	 * @return bool false in case og HTTP 404
	 * @throws ilECSConnectorException, ilECSReaderException
	 */
	public function read($details_only = false)
	{
		global $ilLog;

		if($details_only and $this->getEContentDetails() instanceof ilECSEContentDetails)
		{
			return true;
		}

		try
		{
			$res = $this->connector->getResource($this->econtent_id,$details_only);

			if($res->getHTTPCode() == ilECSConnector::HTTP_CODE_NOT_FOUND)
			{
				return false;
			}
			if(!is_object($res->getResult()))
			{
				$ilLog->write(__METHOD__ . ': Error parsing result. Expected result of type array.');
				$ilLog->logStack();
				throw new ilECSReaderException('Error parsing query');
			}

			if($details_only)
			{
				include_once './Services/WebServices/ECS/classes/class.ilECSEContentDetails.php';
				$tmp_content = new ilECSEContentDetails();
				$tmp_content->loadFromJSON($res->getResult());
				$this->econtent_details = $tmp_content;
			}
			else
			{
				$tmp_content = new ilECSEContent($this->econtent_id);
				$tmp_content->loadFromJSON($res->getResult());

				$sender = $this->parseSenderHeader($res);
				if($sender)
				{
					$tmp_content->setOwner($sender);
					$GLOBALS['ilLog']->write(__METHOD__.': Reading sender from header: '.$sender);
				}
				else
				{
					$details = $this->read(true);
					$details = $this->getEContentDetails();
					$GLOBALS['ilLog']->write(__METHOD__.': Reading sender from details: '.$details->getFirstSender());
					$tmp_content->setOwner($details->getFirstSender());
				}
				$this->econtent = $tmp_content;
			}
			return true;
		}
		catch(ilECSConnectorException $e)
		{
			$ilLog->write(__METHOD__ . ': Error connecting to ECS server. ' . $e->getMessage());
			throw $e;
		}
		catch(ilECSReaderException $e)
		{
			$ilLog->write(__METHOD__ . ': Error reading EContent. ' . $e->getMessage());
			throw $e;
		}
		return false;
	}

	/**
	 * Parse the sender header
	 * @param ilECSResult $res
	 * @return int
	 */
	private function parseSenderHeader(ilECSResult $res)
	{
		#$GLOBALS['ilLog']->write(__METHOD__.' Reading sender from header');
		foreach($res->getHeaders() as $key => $value)
		{
			#$GLOBALS['ilLog']->write(__METHOD__.' Key is: '.$key);
			if($key == self::ECS_HEADER_SENDER)
			{
				#$GLOBALS['ilLog']->write(__METHOD__.' Value is: '.$value);
				$sender = trim($value);
				$value_arr = explode(',', $value);
				foreach((array) $value_arr as $s)
				{
					return (int) trim($s);
				}
			}
		}
		return 0;
	}

}
?>