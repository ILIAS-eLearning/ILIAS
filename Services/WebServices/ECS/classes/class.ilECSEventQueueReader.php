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
* Reads ECS events and stores them in the database.
*  
* @author Stefan Meyer <smeyer@databay.de>
* @version $Id$
* 
* 
* @ingroup ServicesWebServicesECS
*/

class ilECSEventQueueReader
{
	const TYPE_ECONTENT = 'econtents';
	
	const OPERATION_DELETE = 'delete';
	const OPERATION_UPDATE = 'update';
	const OPERATION_CREATE = 'create';
	
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
	public function __construct()
	{
	 	global $ilLog,$ilDB;
	 	
	 	include_once('Services/WebServices/ECS/classes/class.ilECSSettings.php');
		include_once('Services/WebServices/ECS/classes/class.ilECSReaderException.php');
	 	
	 	$this->settings = ilECSSettings::_getInstance();
	 	$this->log = $ilLog;
	 	$this->db = $ilDB;
	 	
	 	$this->read();
	}
	
	/**
	 * handle admin reset  
	 *
	 * @return bool
	 * @static
	 */
	 public static function handleReset()
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
			
			$event_queue = new ilECSEventQueueReader();
			$event_queue->deleteAll();
			
			$reader = new ilECSEContentReader();
			$reader->read();
			$all_content = $reader->getEContent();
			
			$imported = ilECSImport::_getAllImportedLinks();
			$exported = ilECSExport::_getAllEContentIds();
			
			// read update events
			foreach($all_content as $content)
			{
				$event_queue->add(ilECSEventQueueReader::TYPE_ECONTENT,
					$content->getEContentId(),
					ilECSEventQueueReader::OPERATION_UPDATE);
				
				if(isset($imported[$content->getEContentId()]))
				{
					unset($imported[$content->getEContentId()]);
				}
				if(isset($exported[$content->getEContentId()]))
				{
					unset($exported[$content->getEContentId()]);
				}
				
			}
			// read delete events
			if(is_array($imported))
			{
				foreach($imported as $econtent_id => $null)
				{
					$event_queue->add(ilECSEventQueueReader::TYPE_ECONTENT,
						$econtent_id,
						ilECSEventQueueReader::OPERATION_DELETE);
					
				}
			}
			// delete all deprecated export information
			if(is_array($exported))
			{
				ilECSExport::_deleteEContentIds($exported);
			}
		}
		catch(ilECSConnectorException $e1)
		{
			$ilLog->write('Cannot connect to ECS server: '.$e1->getMessage());
			return false;
		}
		catch(ilException $e2)
		{
			$ilLog->write('Update failed: '.$e1->getMessage());
			return false;
		}
		return true;
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
	 	$query = "DELETE FROM ecs_events";
	 	$this->db->query($query);
	 	return true;
	}
	

	/**
	 * Fetch events from ECS server
	 *
	 * @access public
	 * @param
	 * @throws ilECSConnectorException, ilECSReaderException
	 */
	public function refresh()
	{
	 	global $ilLog;
	 	
	 	try
	 	{
		 	include_once('Services/WebServices/ECS/classes/class.ilECSConnector.php');
			include_once('Services/WebServices/ECS/classes/class.ilECSConnectorException.php');
		 	
		 	$connector = new ilECSConnector();
			$res = $connector->getEventQueues();

			if(!is_array($res->getResult()))
			{
				$ilLog->write(__METHOD__.': No new events found.');
				return true;
			}
			$this->log->write(__METHOD__.': Found '.count($res->getResult()).' new events.');
			foreach($res->getResult() as $event)
			{
				// Handle command queue
				if(isset($event->cmd) and is_object($event->cmd))
				{
					if(!isset($event->cmd->admin) and !is_object($event->cmd->admin))
					{
						throw new ilECSReaderException('Received invalid command queue structure. Property "admin" is missing');
					}
					$admin_cmd = $event->cmd->admin;
					$this->log->write(__METHOD__.': Received new Commandqueue command: '.$admin_cmd);
					switch($admin_cmd)
					{
						case self::ADMIN_RESET:
							self::handleReset();
							break;
						case self::ADMIN_RESET_ALL:
							break;						
					}
				}
				// Handle econtents events
				if(isset($event->econtents) and is_object($event->econtents))
				{
					$operation = $event->econtents->op;

					if(!in_array($event->econtents->eid,$this->econtent_ids))
					{
						// It is not necessary to store multiple entries with the same econtent_id.
						// since we always have to receive and parse the econtent from the ecs server. 
						$this->add('econtents',$event->econtents->eid,$event->econtents->op);
						$this->log->write(__METHOD__.': Added new entry for EContentId: '.$event->econtents->eid);
					}
					elseif($operation == self::OPERATION_DELETE)
					{
						$this->log->write(__METHOD__.': Updating delete operation for EContentId: '.$event->econtents->eid);
						$this->update('econtents',$event->econtents->eid,$event->econtents->op);
					}
					else
					{
						// update with last operation
						$this->log->write(__METHOD__.': Ignoring multiple operations for EContentId: '.$event->econtents->eid);
					}
					
				}
			}
			$this->read();		
	 	}
	 	catch(ilECSConnectorException $e)
	 	{
	 		$ilLog->write(__METHOD__.': Error connecting to ECS server. '.$e->getMessage());
	 		throw $e;
	 	}
	 	catch(ilECSReaderException $e)
	 	{
	 		$ilLog->write(__METHOD__.': Error reading EventQueue. '.$e->getMessage());
	 		throw $e;
	 	}
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
	 	$query = "INSERT INTO ecs_events ".
	 		"SET type = ".$this->db->quote($a_type).", ".
	 		"id = ".$this->db->quote($a_id).", ".
	 		"op = ".$this->db->quote($a_op)." ";
	 	$res = $this->db->query($query);
	 	
	 	$new_event['event_id'] = $this->db->getLastInsertId();
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
	 	$query = "UPDATE ecs_events ".
	 		"SET op = ".$this->db->quote($a_operation)." ".
	 		"WHERE type = ".$this->db->quote($a_type)." ".
	 		"AND id = ".$this->db->quote($a_id)." ";
	 	$this->db->query($query);
	}
	
	/**
	 * delete
	 * @access private
	 * @param int event id
	 * 
	 */
	private function delete($a_event_id)
	{
	 	$query = "DELETE FROM ecs_events ".
	 		"WHERE event_id = ".$this->db->quote($a_event_id)." ";
	 	$this->db->query($query);
	 	unset($this->econtent_ids[$a_event_id]);
	 	return true;
	}
	
	/**
	 * Read
	 * @access public
	 */
	public function read()
	{
	 	$query = "SELECT * FROM ecs_events ORDER BY event_id ";
	 	$res = $this->db->query($query);
	 	$counter = 0;
	 	while($row = $res->fetchRow(DB_FETCHMODE_OBJECT))
	 	{
	 		$this->events[$counter]['event_id'] = $row->event_id;
	 		$this->events[$counter]['type'] = $row->event_type;
	 		$this->events[$counter]['id'] = $row->id;
	 		$this->events[$counter]['op'] = $row->op;
	 		
	 		$this->econtent_ids[$row->event_id] = $row->event_id;
	 		++$counter;
	 	}
	 	return true;
	}
	
	
}
?>