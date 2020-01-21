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
    const TYPE_EXPORTED = 'exported';
    const TYPE_DIRECTORY_TREES = 'directory_trees';
    const TYPE_CMS_COURSES = 'courses';
    const TYPE_CMS_COURSE_MEMBERS = 'course_members';
    const TYPE_REMOTE_COURSE = 'rcrs';
    const TYPE_REMOTE_CATEGORY = 'rcat';
    const TYPE_REMOTE_FILE = 'rfil';
    const TYPE_REMOTE_GLOSSARY = 'rglo';
    const TYPE_REMOTE_GROUP = 'rgrp';
    const TYPE_REMOTE_LEARNING_MODULE = 'rlm';
    const TYPE_REMOTE_WIKI = 'rwik';
    const TYPE_REMOTE_TEST = 'rtst';
    const TYPE_COURSE_URLS = 'course_urls';
    const TYPE_ENROLMENT_STATUS = 'member_status';
    
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
        global $DIC;

        $ilLog = $DIC['ilLog'];
        $ilDB = $DIC['ilDB'];
        
        include_once('Services/WebServices/ECS/classes/class.ilECSSetting.php');
        
        $this->settings = ilECSSetting::getInstanceByServerId($a_server_id);
        $this->log = $ilLog;
        $this->db = $ilDB;
        
        $this->read();
    }
    
    /**
     * Convert object type to event type
     *
     * @param string $a_obj_type
     * @return string
     */
    protected static function getEventTypeFromObjectType($a_obj_type)
    {
        // currently they are the same for all resource types
        return $a_obj_type;
    }
    
    /**
     * All available content types
     *
     * @return array
     */
    public static function getAllEContentTypes()
    {
        return array(self::TYPE_REMOTE_COURSE, self::TYPE_REMOTE_CATEGORY,
            self::TYPE_REMOTE_FILE, self::TYPE_REMOTE_GLOSSARY, self::TYPE_REMOTE_GROUP,
            self::TYPE_REMOTE_LEARNING_MODULE, self::TYPE_REMOTE_WIKI, self::TYPE_REMOTE_TEST);
    }
    
    /**
     * Get all resource ids by resource type
     *
     * @param ilECSSetting $server
     * @param array $a_types
     * @param bool $a_sender_only
     * @return array type => ids
     */
    protected static function getAllResourceIds(ilECSSetting $server, array $a_types, $a_sender_only = false)
    {
        include_once 'Services/WebServices/ECS/classes/class.ilRemoteObjectBase.php';
        $list = array();
        foreach ($a_types as $type) {
            $robj = ilRemoteObjectBase::getInstanceByEventType($type);
            if ($robj) {
                $list[$type] = $robj->getAllResourceIds($server, $a_sender_only);
            }
        }
            
        return $list;
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
        global $DIC;

        $ilLog = $DIC['ilLog'];
        
        include_once('Services/WebServices/ECS/classes/class.ilECSConnector.php');
        include_once('Services/WebServices/ECS/classes/class.ilECSConnectorException.php');
        
        try {
            include_once('./Services/WebServices/ECS/classes/class.ilECSEventQueueReader.php');
            include_once('./Services/WebServices/ECS/classes/class.ilECSImport.php');
            include_once('./Services/WebServices/ECS/classes/class.ilECSExport.php');

            $types = self::getAllEContentTypes();

            $event_queue = new ilECSEventQueueReader($server->getServerId());
            $event_queue->deleteAllEContentEvents($types);
            
            $list = self::getAllResourceIds($server, $types);
            $imported = ilECSImport::getAllImportedRemoteObjects($server->getServerId());
            
            $GLOBALS['DIC']['ilLog']->write(__METHOD__ . ': Imported = ' . print_r($imported, true));
            $GLOBALS['DIC']['ilLog']->write(__METHOD__ . ': List = ' . print_r($list, true));
            
            foreach ($list as $resource_type => $link_ids) {
                if (!in_array($resource_type, ilECSUtils::getPossibleRemoteTypes())) {
                    $GLOBALS['DIC']['ilLog']->write(__METHOD__ . ': Ignoring resource type ' . $resource_type);
                    continue;
                }
                
                
                foreach ((array) $link_ids as $link_id) {
                    if (!isset($imported[$link_id])) {
                        // Add create event for not imported econtent
                        $event_queue->add(
                            $resource_type,
                            $link_id,
                            ilECSEvent::CREATED
                        );
                    } else {
                        // Add update event for already existing events
                        $event_queue->add(
                            $resource_type,
                            $link_id,
                            ilECSEvent::UPDATED
                        );
                    }

                    if (isset($imported[$link_id])) {
                        unset($imported[$link_id]);
                    }
                }
            }
            
            if (is_array($imported)) {
                // Delete event for deprecated econtent
                include_once 'Services/WebServices/ECS/classes/class.ilECSObjectSettings.php';
                foreach ($imported as $econtent_id => $obj_id) {
                    $type = self::getEventTypeFromObjectType(ilObject::_lookupType($obj_id));
                    if ($type) {
                        $event_queue->add(
                            $type,
                            $econtent_id,
                            ilECSEvent::DESTROYED
                        );
                    }
                }
            }
        } catch (ilECSConnectorException $e1) {
            $ilLog->write('Cannot connect to ECS server: ' . $e1->getMessage());
            throw $e1;
        } catch (ilException $e2) {
            $ilLog->write('Update failed: ' . $e2->getMessage());
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

        $types = self::getAllEContentTypes();
        $list = self::getAllResourceIds($server, $types, true);
        
        
        // merge in one array
        $all_remote_ids = array();
        foreach ($list as $resource_type => $remote_ids) {
            $all_remote_ids = array_merge($all_remote_ids, (array) $remote_ids);
        }
        $all_remote_ids = array_unique($all_remote_ids);
        
        $GLOBALS['DIC']['ilLog']->write(__METHOD__ . ': Resources = ' . print_r($all_remote_ids, true));
        $GLOBALS['DIC']['ilLog']->write(__METHOD__ . ': Local = ' . print_r($local_econtent_ids, true));
        foreach ($local_econtent_ids as $local_econtent_id => $local_obj_id) {
            if (!in_array($local_econtent_id, $all_remote_ids)) {
                // Delete this deprecated info
                $GLOBALS['DIC']['ilLog']->write(__METHOD__ . ': Deleting deprecated econtent id ' . $local_econtent_id);
                ilECSExport::_deleteEContentIds($server->getServerId(), array($local_econtent_id));
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
        global $DIC;

        $ilDB = $DIC['ilDB'];
        
        $query = "DELETE FROM ecs_events " .
            'WHERE server_id = ' . $ilDB->quote($this->getServer()->getServerId(), 'integer');
        $res = $ilDB->manipulate($query);
        return true;
    }
    
    /**
     * Delete all econtents
     *
     * @access public
     * @param array $a_types
     */
    public function deleteAllEContentEvents(array $a_types)
    {
        global $DIC;

        $ilDB = $DIC['ilDB'];
        
        $query = "DELETE FROM ecs_events " .
            "WHERE " . $this->db->in("type", $a_types, "", "text") . ' ' .
            'AND server_id = ' . $ilDB->quote($this->getServer()->getServerId(), 'integer');
        $res = $ilDB->manipulate($query);
        return true;
    }
    
    /**
     * Delete all exported events
     *
     * @access public
     */
    protected function deleteAllExportedEvents()
    {
        global $DIC;

        $ilDB = $DIC['ilDB'];
        
        $query = "DELETE FROM ecs_events " .
            "WHERE type = " . $this->db->quote(self::TYPE_EXPORTED, 'text') . ' ' .
            'AND server_id = ' . $ilDB->quote($this->getServer()->getServerId(), 'integer');
        $res = $ilDB->manipulate($query);
        return true;
    }

    /**
     * Fetch events from fifo
     * Using fifo
     * @access public
     * @throws ilECSConnectorException
     */
    public function refresh()
    {
        try {
            include_once('Services/WebServices/ECS/classes/class.ilECSConnector.php');
            include_once('Services/WebServices/ECS/classes/class.ilECSConnectorException.php');

            $connector = new ilECSConnector($this->getServer());
            while (true) {
                $res = $connector->readEventFifo(false);

                if (!count($res->getResult())) {
                    return true;
                }

                foreach ($res->getResult() as $result) {
                    include_once './Services/WebServices/ECS/classes/class.ilECSEvent.php';
                    $event = new ilECSEvent($result);

                    $GLOBALS['DIC']['ilLog']->write(__METHOD__ . ' ---------------------------- Handling new event ');
                    $GLOBALS['DIC']['ilLog']->write(__METHOD__ . print_r($event, true));
                    $GLOBALS['DIC']['ilLog']->write(__METHOD__ . ' ---------------------------- Done! ');

                    // Fill command queue
                    $this->writeEventToDB($event);
                }
                // Delete from fifo
                $connector->readEventFifo(true);
            }
        } catch (ilECSConnectorException $e) {
            $GLOBALS['DIC']['ilLog']->write(__METHOD__ . ': Cannot read event fifo. Aborting');
        }
    }

    /**
     * Delete by server id
     * @global ilDB $ilDB
     * @param int $a_server_id
     */
    public static function deleteServer($a_server_id)
    {
        global $DIC;

        $ilDB = $DIC['ilDB'];

        $query = 'DELETE FROM ecs_events ' .
            'WHERE server_id = ' . $ilDB->quote($a_server_id, 'integer');
        $ilDB->manipulate($query);
    }

    /**
     * Write event to db
     */
    private function writeEventToDB(ilECSEvent $ev)
    {
        global $DIC;

        $ilDB = $DIC['ilDB'];

        // this should probably be moved elsewhere
        switch ($ev->getRessourceType()) {
            case 'directory_trees':
                $type = self::TYPE_DIRECTORY_TREES;
                break;
            
            case 'course_members':
                $type = self::TYPE_CMS_COURSE_MEMBERS;
                break;
            
            case 'courses':
                $type = self::TYPE_CMS_COURSES;
                break;
            
            case 'courselinks':
                $type = self::TYPE_REMOTE_COURSE;
                break;
            
            case 'categories':
                $type = self::TYPE_REMOTE_CATEGORY;
                break;
            
            case 'files':
                $type = self::TYPE_REMOTE_FILE;
                break;
            
            case 'glossaries':
                $type = self::TYPE_REMOTE_GLOSSARY;
                break;
            
            case 'groups':
                $type = self::TYPE_REMOTE_GROUP;
                break;
            
            case 'learningmodules':
                $type = self::TYPE_REMOTE_LEARNING_MODULE;
                break;
            
            case 'wikis':
                $type = self::TYPE_REMOTE_WIKI;
                break;
            
            case 'tests':
                $type = self::TYPE_REMOTE_TEST;
                break;
            
            case 'course_urls':
                $type = self::TYPE_COURSE_URLS;
                break;
            
            case 'member_status':
                $type = self::TYPE_ENROLMENT_STATUS;
                break;
            
            default:
                // write custom event type
                $type = $ev->getRessourceType();
                break;
        }

        $query = "SELECT * FROM ecs_events " .
            "WHERE type = " . $ilDB->quote($type, 'integer') . " " .
            "AND id = " . $ilDB->quote($ev->getRessourceId(), 'integer') . " " .
            'AND server_id = ' . $ilDB->quote($this->getServer()->getServerId(), 'integer');
        $res = $ilDB->query($query);

        $event_id = 0;
        while ($row = $res->fetchRow(ilDBConstants::FETCHMODE_OBJECT)) {
            $event_id = $row->event_id;
        }

        if (!$event_id) {
            // No previous entry exists => perform insert
            $query = "INSERT ecs_events (event_id,type,id,op,server_id) " .
                "VALUES( " .
                $ilDB->quote($ilDB->nextId('ecs_events'), 'integer') . ',' .
                $ilDB->quote($type, 'text') . ', ' .
                $ilDB->quote($ev->getRessourceId(), 'integer') . ', ' .
                $ilDB->quote($ev->getStatus(), 'text') . ', ' .
                $ilDB->quote($this->getServer()->getServerId(), 'integer') . ' ' .
                ')';
            $ilDB->manipulate($query);
            return true;
        }
        // Do update
        $do_update = false;
        switch ($ev->getStatus()) {
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

        if (!$do_update) {
            return true;
        }
        $query = "UPDATE ecs_events " .
            "SET op = " . $ilDB->quote($ev->getStatus(), 'text') . " " .
            "WHERE event_id = " . $ilDB->quote($event_id, 'integer') . ' ' .
            'AND type = ' . $ilDB->quote($type) . ' ' .
            'AND server_id = ' . $ilDB->quote($this->getServer()->getServerId(), 'integer');
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
        if ($event == null) {
            return array();
        } else {
            
            #$this->delete($event['event_id']);
            return $event;
        }
    }
    
    
    /**
     * add
     *
     * @access public
     */
    public function add($a_type, $a_id, $a_op)
    {
        global $DIC;

        $ilDB = $DIC['ilDB'];

        $next_id = $ilDB->nextId('ecs_events');
        $query = "INSERT INTO ecs_events (event_id,type,id,op,server_id) " .
            "VALUES (" .
            $ilDB->quote($next_id, 'integer') . ", " .
            $this->db->quote($a_type, 'text') . ", " .
            $this->db->quote($a_id, 'integer') . ", " .
            $this->db->quote($a_op, 'text') . ", " .
            $ilDB->quote($this->getServer()->getServerId(), 'integer') . ' ' .
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
    private function update($a_type, $a_id, $a_operation)
    {
        global $DIC;

        $ilDB = $DIC['ilDB'];
        
        $query = "UPDATE ecs_events " .
            "SET op = " . $this->db->quote($a_operation, 'text') . " " .
            "WHERE type = " . $this->db->quote($a_type, 'text') . " " .
            "AND id = " . $this->db->quote($a_id, 'integer') . " " .
            'AND server_id = ' . $ilDB->quote($this->getServer()->getServerId(), 'integer');
        $res = $ilDB->manipulate($query);
    }
    
    /**
     * delete
     * @access private
     * @param int event id
     *
     */
    public function delete($a_event_id)
    {
        global $DIC;

        $ilDB = $DIC['ilDB'];
        
        $query = "DELETE FROM ecs_events " .
            "WHERE event_id = " . $this->db->quote($a_event_id, 'integer') . " " .
            'AND server_id = ' . $ilDB->quote($this->getServer()->getServerId(), 'integer');
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
        global $DIC;

        $ilDB = $DIC['ilDB'];
        
        $query = "SELECT * FROM ecs_events  " .
            'WHERE server_id = ' . $ilDB->quote($this->getServer()->getServerId(), 'integer') . ' ' .
            'ORDER BY event_id';
        
        $res = $this->db->query($query);
        $counter = 0;
        while ($row = $res->fetchRow(ilDBConstants::FETCHMODE_OBJECT)) {
            $this->events[$counter]['event_id'] = $row->event_id;
            $this->events[$counter]['type'] = $row->type;
            $this->events[$counter]['id'] = $row->id;
            $this->events[$counter]['op'] = $row->op;
            
            $this->econtent_ids[$row->event_id] = $row->event_id;
            ++$counter;
        }
        return true;
    }
    
    public static function deleteByServerId($a_server_id)
    {
        global $DIC;

        $ilDB = $DIC['ilDB'];

        $query = 'DELETE FROM ecs_events' .
            ' WHERE server_id = ' . $ilDB->quote($a_server_id, 'integer');
        $ilDB->manipulate($query);
        return true;
    }
}
