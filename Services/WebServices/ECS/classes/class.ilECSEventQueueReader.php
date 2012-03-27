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

include_once './Services/WebServices/ECS/classes/class.ilECSEvent.php';


/** 
* Reads ECS events and stores them in the database.
*  
* @author Stefan Meyer <meyer@leifos.com>
* @version $Id$
* 
* 
* @ingroup ServicesWebServicesECS
*/
class ilECSEventQueueReader
{
	const TYPE_ECONTENT = 'econtents';
	const TYPE_EXPORTED = 'exported';
	
	const ADMIN_RESET = 'reset';
	const ADMIN_RESET_ALL = 'reset_all';
	
	protected $log;
	protected $db;
	
	protected $events = array();
	protected $econtent_ids = array();

	/**
	 * Constructor
	 *
	 * @access public
	 */
	public function __construct($a_server_id)
	{
	 	global $ilLog,$ilDB;
	 	
	 	include_once('Services/WebServices/ECS/classes/class.ilECSSetting.php');
		include_once('Services/WebServices/ECS/classes/class.ilECSReaderException.php');
	 	
	 	$this->settings = ilECSSetting::getInstanceByServerId($a_server_id);
	 	$this->log = $ilLog;
	 	$this->db = $ilDB;
	 	
	 	$this->read();
	}
	
	/**
	 * Reread all imported econtent.
	 *
	 * @return bool
	 * @static
	 * throws ilException, ilECSConnectorException
	 */
	 public static function handleImportReset(ilECSSetting $server)
	 {
		global $ilLog;
		
		include_once('Services/WebServices/ECS/classes/class.ilECSConnector.php');
		include_once('Services/WebServices/ECS/classes/class.ilECSConnectorException.php');

		try
		{
			include_once('./Services/WebServices/ECS/classes/class.ilECSEContentReader.php');
			include_once('./Services/WebServices/ECS/classes/class.ilECSEventQueueReader.php');
			include_once('./Services/WebServices/ECS/classes/class.ilECSImport.php');
			include_once('./Services/WebServices/ECS/classes/class.ilECSExport.php');

			$event_queue = new ilECSEventQueueReader($server->getServerId());
			$event_queue->deleteAllEContentEvents();
			
			$reader = new ilECSEContentReader($server->getServerId());
			$list = $reader->readResourceList();
			//$all_content = $reader->getEContent();

			$imported = ilECSImport::_getAllImportedLinks($server->getServerId());

			if(count($list))
			{
				foreach($list->getLinkIds() as $link_id)
				{
					if(!isset($imported[$link_id]))
					{
						// Add create event for not imported econtent
						$event_queue->add(
							ilECSEventQueueReader::TYPE_ECONTENT,
							$link_id,
							ilECSEvent::CREATED
						);
					}
					else
					{
						// Add update event for already existing events
						$event_queue->add(
							ilECSEventQueueReader::TYPE_ECONTENT,
							$link_id,
							ilECSEvent::UPDATED
						);
					}

					if(isset($imported[$link_id]))
					{
						unset($imported[$link_id]);
					}
				}
			}
			if(is_array($imported))
			{
				// Delete event for deprecated econtent
				foreach($imported as $econtent_id => $null)
				{
					$event_queue->add(ilECSEventQueueReader::TYPE_ECONTENT,
						$econtent_id,
						ilECSEvent::DESTROYED
					);
				}
			}
		}
		catch(ilECSConnectorException $e1)
		{
			$ilLog->write('Cannot connect to ECS server: '.$e1->getMessage());
			throw $e1;
		}
		catch(ilException $e2)
		{
			$ilLog->write('Update failed: '.$e2->getMessage());
			throw $e2;
		}
		return true;
	 }
	 
	/**
	 * Handle export reset.
	 * Delete exported econtent and create it again 
	 *
	 * @return bool
	 * @static
	 * throws ilException, ilECSConnectorException
	 */
	 public static function handleExportReset(ilECSSetting $server)
	 {
	 	include_once('./Services/WebServices/ECS/classes/class.ilECSExport.php');

		// Delete all export events
	 	$queue = new ilECSEventQueueReader($server->getServerId());
	 	$queue->deleteAllExportedEvents();

		// Read all local export info
		$local_econtent_ids = ilECSExport::_getAllEContentIds($server->getServerId());

		// Read remote list
		try
		{
			include_once './Services/WebServices/ECS/classes/class.ilECSEContentReader.php';
			$reader = new ilECSEContentReader($server->getServerId());
			$list = $reader->readResourceList(ilECSEContentReader::SENDER_ONLY);
		}
		catch(ilECSConnectorException $e)
		{
			$GLOBALS['ilLog']->write(__METHOD__.': Connect failed '.$e->getMessage());
			throw $e;
		}
		catch(ilECSReaderException $e)
		{
			$GLOBALS['ilLog']->write(__METHOD__.': Connect failed '.$e->getMessage());
			throw $e;
		}

		$remote_econtent_ids = array();
		if(count($list))
		{
			$remote_econtent_ids = $list->getLinkIds();
		}

		// Delete all deprecated local export info
		foreach($local_econtent_ids as $econtent_id => $obj_id)
		{
			if(!in_array($econtent_id, $remote_econtent_ids))
			{
				ilECSExport::_deleteEContentIds($server->getServerId(),array($econtent_id));
			}
		}

		// Delete all with deprecated remote info
		foreach($remote_econtent_ids as $econtent_id)
		{
			if(!isset($local_econtent_ids[$econtent_id]))
			{
				ilECSExport::_deleteEContentIds($server->getServerId(),array($econtent_id));
			}
		}
		return true;
	 }


	 /**
	  * get server setting
	  * @return ilECSSetting
	  */
	 public function getServer()
	 {
		 return $this->settings;
	 }
	
	
	/**
	 * get all events
	 *
	 * @access public
	 * 
	 */
	public function getEvents()
	{
	 	return $this->events ? $this->events : array();
	}
	
	/**
	 * Delete all events
	 *
	 * @access public
	 */
	public function deleteAll()
	{
	 	global $ilDB;
	 	
	 	$query = "DELETE FROM ecs_events ".
			'WHERE server_id = '.$ilDB->quote($this->getServer()->getServerId(),'integer');
		$res = $ilDB->manipulate($query);
	 	return true;
	}
	
	/**
	 * Delete all econtents
	 *
	 * @access public
	 */
	public function deleteAllEContentEvents()
	{
	 	global $ilDB;
	 	
	 	$query = "DELETE FROM ecs_events ".
	 		"WHERE type = ".$this->db->quote(self::TYPE_ECONTENT,'text').' '.
			'AND server_id = '.$ilDB->quote($this->getServer()->getServerId(),'integer');
	 	$res = $ilDB->manipulate($query);
	 	return true;
	}
	
	/**
	 * Delete all exported events
	 *
	 * @access public
	 */
	public function deleteAllExportedEvents()
	{
	 	global $ilDB;
	 	
	 	$query = "DELETE FROM ecs_events ".
	 		"WHERE type = ".$this->db->quote(self::TYPE_EXPORTED,'text').' '.
			'AND server_id = '.$ilDB->quote($this->getServer()->getServerId(),'integer');
		$res = $ilDB->manipulate($query);
	 	return true;
	}

	/**
	 * Fetch events from fifo
	 * Using fifo
	 * @access public
	 * @throws ilECSConnectorException, ilECSReaderException
	 */
	public function refresh()
	{
		try {
		 	include_once('Services/WebServices/ECS/classes/class.ilECSConnector.php');
			include_once('Services/WebServices/ECS/classes/class.ilECSConnectorException.php');

			$connector = new ilECSConnector($this->getServer());
			while(true)
			{
				$res = $connector->readEventFifo(false);

				if(!count($res->getResult()))
				{
					return true;
				}

				foreach($res->getResult() as $result)
				{
					include_once './Services/WebServices/ECS/classes/class.ilECSEvent.php';
					$event = new ilECSEvent($result);

					// Fill command queue
					$this->writeEventToDB($event);
				}
				// Delete from fifo
				$connector->readEventFifo(true);
			}
		}
		catch(ilECSConnectorException $e)
		{
			$GLOBALS['ilLog']->write(__METHOD__.': Cannot read event fifo. Aborting');
		}
	}

	/**
	 * Write event to db
	 */
	private function writeEventToDB(ilECSEvent $ev)
	{
		global $ilDB;

		$query = "SELECT * FROM ecs_events ".
			"WHERE type = ".$ilDB->quote(self::TYPE_ECONTENT,'integer')." ".
			"AND id = ".$ilDB->quote($ev->getRessourceId(),'integer')." ".
			'AND server_id = '.$ilDB->quote($this->getServer()->getServerId(),'integer');
		$res = $ilDB->query($query);

		$event_id = 0;
		while($row = $res->fetchRow(DB_FETCHMODE_OBJECT))
		{
			$event_id = $row->event_id;
		}

		$GLOBALS['ilLog']->write(__METHOD__.': Handling new event '.$ev->getStatus().' for econtent '.$ev->getRessourceId());

		if(!$event_id)
		{
			// No previous entry exists => perform insert
			$query = "INSERT ecs_events (event_id,type,id,op,server_id) ".
				"VALUES( ".
				$ilDB->quote($ilDB->nextId('ecs_events'),'integer').','.
				$ilDB->quote(self::TYPE_ECONTENT,'text').', '.
				$ilDB->quote($ev->getRessourceId(),'integer').', '.
				$ilDB->quote($ev->getStatus(),'text').', '.
				$ilDB->quote($this->getServer()->getServerId(),'integer').' '.
				')';
			$ilDB->manipulate($query);
			return true;
		}
		// Do update
		$do_update = false;
		switch($ev->getStatus())
		{
			case ilECSEvent::CREATED:
				// Do update, although impossible
				$do_update = true;
				break;

			case ilECSEvent::DESTROYED:
				$do_update = true;
				break;

			case ilECSEvent::UPDATED:
				// Do nothing. Old status is ok.
				break;
		}

		if(!$do_update)
		{
			return true;
		}
		$query = "UPDATE ecs_events ".
			"SET op = ".$ilDB->quote($ev->getStatus(),'text')." ".
			"WHERE event_id = ".$ilDB->quote($event_id,'integer').' '.
			'AND server_id = '.$ilDB->quote($this->getServer()->getServerId(),'integer');
		$ilDB->manipulate($query);
		return true;
	}
	
	/**
	 * get and delete the first event entry
	 *
	 * @access public
	 * @return array event data or an empty array if the queue is empty
	 */
	public function shift()
	{
		$event = array_shift($this->events);
		if($event == null)
		{
			return array();
		}
		else
		{
			$this->delete($event['event_id']);
			return $event;
		}
	}
	
	
	/**
	 * add 
	 *
	 * @access public
	 */
	public function add($a_type,$a_id,$a_op)
	{
	 	global $ilDB;

	 	$next_id = $ilDB->nextId('ecs_events');
	 	$query = "INSERT INTO ecs_events (event_id,type,id,op,server_id) ".
	 		"VALUES (".
	 		$ilDB->quote($next_id,'integer').", ".
			$this->db->quote($a_type,'text').", ".
	 		$this->db->quote($a_id,'integer').", ".
	 		$this->db->quote($a_op,'text').", ".
			$ilDB->quote($this->getServer()->getServerId(),'integer').' '.
	 		")";
		$res = $ilDB->manipulate($query);
	 	
	 	$new_event['event_id'] = $next_id;
	 	$new_event['type'] = $a_type;
	 	$new_event['id'] = $a_id;
	 	$new_event['op'] = $a_op;
	 	
	 	$this->events[] = $new_event;
	 	$this->econtent_ids[$a_id] = $a_id;
		return true;
	}
	
	/**
	 * update one entry
	 *
	 * @access private
	 * 
	 */
	private function update($a_type,$a_id,$a_operation)
	{
	 	global $ilDB;
	 	
	 	$query = "UPDATE ecs_events ".
	 		"SET op = ".$this->db->quote($a_operation,'text')." ".
	 		"WHERE type = ".$this->db->quote($a_type,'text')." ".
	 		"AND id = ".$this->db->quote($a_id,'integer')." ".
			'AND server_id = '.$ilDB->quote($this->getServer()->getServerId(),'integer');
		$res = $ilDB->manipulate($query);
	}
	
	/**
	 * delete
	 * @access private
	 * @param int event id
	 * 
	 */
	private function delete($a_event_id)
	{
	 	global $ilDB;
	 	
	 	$query = "DELETE FROM ecs_events ".
	 		"WHERE event_id = ".$this->db->quote($a_event_id,'integer')." ".
			'AND server_id = '.$ilDB->quote($this->getServer()->getServerId(),'integer');
		$res = $ilDB->manipulate($query);
	 	unset($this->econtent_ids[$a_event_id]);
	 	return true;
	}
	
	/**
	 * Read
	 * @access public
	 */
	public function read()
	{
	 	global $ilDB;
	 	
	 	$query = "SELECT * FROM ecs_events ORDER BY event_id ".
			'AND server_id = '.$ilDB->quote($this->getServer()->getServerId(),'integer');
	 	$res = $this->db->query($query);
	 	$counter = 0;
	 	while($row = $res->fetchRow(DB_FETCHMODE_OBJECT))
	 	{
	 		$this->events[$counter]['event_id'] = $row->event_id;
	 		$this->events[$counter]['type'] = $row->type;
	 		$this->events[$counter]['id'] = $row->id;
	 		$this->events[$counter]['op'] = $row->op;
	 		
	 		$this->econtent_ids[$row->event_id] = $row->event_id;
	 		++$counter;
	 	}
	 	return true;
	}
	
	
}
?>