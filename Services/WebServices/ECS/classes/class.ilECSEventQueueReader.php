<?php

declare(strict_types=1);

/******************************************************************************
 *
 * This file is part of ILIAS, a powerful learning management system.
 *
 * ILIAS is licensed with the GPL-3.0, you should have received a copy
 * of said license along with the source code.
 *
 * If this is not the case or you just want to try ILIAS, you'll find
 * us at:
 *      https://www.ilias.de
 *      https://github.com/ILIAS-eLearning
 *
 *****************************************************************************/

/**
* Reads ECS events and stores them in the database.
*
* @author Stefan Meyer <meyer@leifos.com>
*/
class ilECSEventQueueReader
{
    public const TYPE_EXPORTED = 'exported';
    public const TYPE_DIRECTORY_TREES = 'directory_trees';
    public const TYPE_CMS_COURSES = 'courses';
    public const TYPE_CMS_COURSE_MEMBERS = 'course_members';
    public const TYPE_REMOTE_COURSE = 'rcrs';
    public const TYPE_REMOTE_CATEGORY = 'rcat';
    public const TYPE_REMOTE_FILE = 'rfil';
    public const TYPE_REMOTE_GLOSSARY = 'rglo';
    public const TYPE_REMOTE_GROUP = 'rgrp';
    public const TYPE_REMOTE_LEARNING_MODULE = 'rlm';
    public const TYPE_REMOTE_WIKI = 'rwik';
    public const TYPE_REMOTE_TEST = 'rtst';
    public const TYPE_COURSE_URLS = 'course_urls';
    public const TYPE_ENROLMENT_STATUS = 'member_status';

    private ilLogger $logger;
    private ilDBInterface $db;

    protected array $events = array();
    protected array $econtent_ids = array();
    private ilECSSetting $settings;

    /**
     * Constructor
     *
     * @access public
     */
    public function __construct(ilECSSetting $settings)
    {
        global $DIC;

        $this->logger = $DIC->logger()->wsrv();
        $this->db = $DIC->database();

        $this->settings = $settings;
        $this->read();
    }

    /**
     * Convert object type to event type
     */
    protected static function getEventTypeFromObjectType(string $a_obj_type): string
    {
        // currently they are the same for all resource types
        return $a_obj_type;
    }

    /**
     * All available content types
     */
    public static function getAllEContentTypes(): array
    {
        return array(self::TYPE_REMOTE_COURSE, self::TYPE_REMOTE_CATEGORY,
            self::TYPE_REMOTE_FILE, self::TYPE_REMOTE_GLOSSARY, self::TYPE_REMOTE_GROUP,
            self::TYPE_REMOTE_LEARNING_MODULE, self::TYPE_REMOTE_WIKI, self::TYPE_REMOTE_TEST);
    }

    /**
     * Get all resource ids by resource type
     *
     * @param ilECSSetting $server
     * @param int[] $a_types
     * @param bool $a_sender_only
     * @return array<int,int[]> type => ids
     */
    protected static function getAllResourceIds(ilECSSetting $server, array $a_types, bool $a_sender_only = false): array
    {
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
     * @throws ilException, ilECSConnectorException
     */
    public function handleImportReset(): bool
    {
        try {
            $types = self::getAllEContentTypes();

            $this->deleteAllEContentEvents($types);

            $list = self::getAllResourceIds($this->settings, $types);
            $imported = ilECSImportManager::getInstance()->getAllImportedRemoteObjects($this->settings->getServerId());

            $this->logger->info(__METHOD__ . ': Imported = ' . print_r($imported, true));
            $this->logger->info(__METHOD__ . ': List = ' . print_r($list, true));

            foreach ($list as $resource_type => $link_ids) {
                if (!in_array($resource_type, ilECSUtils::getPossibleRemoteTypes(), true)) {
                    $this->logger->info(__METHOD__ . ': Ignoring resource type ' . $resource_type);
                    continue;
                }


                foreach ((array) $link_ids as $link_id) {
                    if (!isset($imported[$link_id])) {
                        // Add create event for not imported econtent
                        $this->add(
                            $resource_type,
                            $link_id,
                            ilECSEvent::CREATED
                        );
                    } else {
                        // Add update event for already existing events
                        $this->add(
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
                foreach ($imported as $econtent_id => $obj_id) {
                    $type = self::getEventTypeFromObjectType(ilObject::_lookupType($obj_id));
                    if ($type) {
                        $this->add(
                            $type,
                            $econtent_id,
                            ilECSEvent::DESTROYED
                        );
                    }
                }
            }
        } catch (ilECSConnectorException $e1) {
            $this->logger->info('Cannot connect to ECS server: ' . $e1->getMessage());
            throw $e1;
        } catch (ilException $e2) {
            $this->logger->info('Update failed: ' . $e2->getMessage());
            throw $e2;
        }
        return true;
    }

    /**
     * Handle export reset.
     * Delete exported econtent and create it again
     *
     * @throws ilException, ilECSConnectorException
     */
    public function handleExportReset(): bool
    {
        // Delete all export events

        $this->deleteAllExportedEvents();

        // Read all local export info
        $exportManager = ilECSExportManager::getInstance();
        $local_econtent_ids = $exportManager->_getAllEContentIds($this->settings->getServerId());

        $types = self::getAllEContentTypes();
        $list = self::getAllResourceIds($this->settings, $types, true);


        // merge in one array
        $all_remote_ids = [];
        foreach ($list as $resource_type => $remote_ids) {
            $all_remote_ids += (array) $remote_ids;
        }
        $all_remote_ids = array_unique($all_remote_ids);

        $this->logger->info(__METHOD__ . ': Resources = ' . print_r($all_remote_ids, true));
        $this->logger->info(__METHOD__ . ': Local = ' . print_r($local_econtent_ids, true));
        foreach ($local_econtent_ids as $local_econtent_id => $local_obj_id) {
            if (!in_array($local_econtent_id, $all_remote_ids, true)) {
                // Delete this deprecated info
                $this->logger->info(__METHOD__ . ': Deleting deprecated econtent id ' . $local_econtent_id);
                $exportManager->_deleteEContentIds($this->settings->getServerId(), array($local_econtent_id));
            }
        }
        return true;
    }


    /**
     * get server settings
     */
    public function getServer(): \ilECSSetting
    {
        return $this->settings;
    }


    /**
     * get all events
     */
    public function getEvents(): array
    {
        return $this->events ?: array();
    }

    /**
     * Delete all events
     */
    public function deleteAll(): bool
    {
        $query = "DELETE FROM ecs_events " .
            'WHERE server_id = ' . $this->db->quote($this->settings->getServerId(), 'integer');
        $this->db->manipulate($query);
        return true;
    }

    /**
     * Delete all econtents
     */
    public function deleteAllEContentEvents(array $a_types): bool
    {
        $query = "DELETE FROM ecs_events " .
            "WHERE " . $this->db->in("type", $a_types, false, "text") . ' ' .
            'AND server_id = ' . $this->db->quote($this->getServer()->getServerId(), 'integer');
        $this->db->manipulate($query);
        return true;
    }

    /**
     * Delete all exported events
     */
    protected function deleteAllExportedEvents(): bool
    {
        $query = "DELETE FROM ecs_events " .
            "WHERE type = " . $this->db->quote(self::TYPE_EXPORTED, 'text') . ' ' .
            'AND server_id = ' . $this->db->quote($this->getServer()->getServerId(), 'integer');
        $this->db->manipulate($query);
        return true;
    }

    /**
     * Fetch events from fifo
     * Using fifo
     * @throws ilECSConnectorException
     */
    public function refresh(): void
    {
        try {
            $connector = new ilECSConnector($this->getServer());
            while (true) {
                $res = $connector->readEventFifo(false);

                if (!count($res->getResult())) {
                    return;
                }

                foreach ($res->getResult() as $result) {
                    $event = new ilECSEvent($result);

                    $this->logger->info(__METHOD__ . ' ---------------------------- Handling new event ');
                    $this->logger->info(__METHOD__ . print_r($event, true));
                    $this->logger->info(__METHOD__ . ' ---------------------------- Done! ');

                    // Fill command queue
                    $this->writeEventToDB($event);
                }
                // Delete from fifo
                $connector->readEventFifo(true);
            }
        } catch (ilECSConnectorException $e) {
            $this->logger->error(__METHOD__ . ': Cannot read event fifo. Aborting');
        }
    }

    /**
     * Delete by server id
     */
    public function delete(): void
    {
        $query = 'DELETE FROM ecs_events ' .
            'WHERE server_id = ' . $this->db->quote($this->getServer()->getServerId(), 'integer');
        $this->db->manipulate($query);
    }

    /**
     * Write event to db
     */
    private function writeEventToDB(ilECSEvent $ev): void
    {
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
            "WHERE type = " . $this->db->quote($type, 'integer') . " " .
            "AND id = " . $this->db->quote($ev->getRessourceId(), 'integer') . " " .
            'AND server_id = ' . $this->db->quote($this->getServer()->getServerId(), 'integer');
        $res = $this->db->query($query);

        $event_id = 0;
        while ($row = $res->fetchRow(ilDBConstants::FETCHMODE_OBJECT)) {
            $event_id = $row->event_id;
        }

        if (!$event_id) {
            // No previous entry exists => perform insert
            $query = "INSERT ecs_events (event_id,type,id,op,server_id) " .
                "VALUES( " .
                $this->db->quote($this->db->nextId('ecs_events'), 'integer') . ',' .
                $this->db->quote($type, 'text') . ', ' .
                $this->db->quote($ev->getRessourceId(), 'integer') . ', ' .
                $this->db->quote($ev->getStatus(), 'text') . ', ' .
                $this->db->quote($this->getServer()->getServerId(), 'integer') . ' ' .
                ')';
            $this->db->manipulate($query);
            return;
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
            return;
        }
        $query = "UPDATE ecs_events " .
            "SET op = " . $this->db->quote($ev->getStatus(), 'text') . " " .
            "WHERE event_id = " . $this->db->quote($event_id, 'integer') . ' ' .
            'AND type = ' . $this->db->quote($type) . ' ' .
            'AND server_id = ' . $this->db->quote($this->getServer()->getServerId(), 'integer');
        $this->db->manipulate($query);
    }

    /**
     * get and delete the first event entry
     *
     * @return array event data or an empty array if the queue is empty
     */
    public function shift(): array
    {
        $event = array_shift($this->events);
        return $event ?? [];
    }


    /**
     * add
     */
    public function add($a_type, $a_id, $a_op): bool
    {
        $next_id = $this->db->nextId('ecs_events');
        $query = "INSERT INTO ecs_events (event_id,type,id,op,server_id) " .
            "VALUES (" .
            $this->db->quote($next_id, 'integer') . ", " .
            $this->db->quote($a_type, 'text') . ", " .
            $this->db->quote($a_id, 'integer') . ", " .
            $this->db->quote($a_op, 'text') . ", " .
            $this->db->quote($this->getServer()->getServerId(), 'integer') . ' ' .
            ")";
        $this->db->manipulate($query);

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
     */
    private function update($a_type, $a_id, $a_operation): void
    {
        $query = "UPDATE ecs_events " .
            "SET op = " . $this->db->quote($a_operation, 'text') . " " .
            "WHERE type = " . $this->db->quote($a_type, 'text') . " " .
            "AND id = " . $this->db->quote($a_id, 'integer') . " " .
            'AND server_id = ' . $this->db->quote($this->settings->getServerId(), 'integer');
        $this->db->manipulate($query);
    }

    /**
     * delete
     */
    public function deleteEvent(int $a_event_id): bool
    {
        $query = "DELETE FROM ecs_events " .
            "WHERE event_id = " . $this->db->quote($a_event_id, 'integer') . " " .
            'AND server_id = ' . $this->db->quote($this->getServer()->getServerId(), 'integer');
        $this->db->manipulate($query);
        unset($this->econtent_ids[$a_event_id]);
        return true;
    }

    /**
     * Read
     */
    public function read(): bool
    {
        $query = "SELECT * FROM ecs_events  " .
            'WHERE server_id = ' . $this->db->quote($this->getServer()->getServerId(), 'integer') . ' ' .
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

    public static function deleteByServerId($a_server_id): bool
    {
        global $DIC;

        $ilDB = $DIC['ilDB'];

        $query = 'DELETE FROM ecs_events' .
            ' WHERE server_id = ' . $ilDB->quote($a_server_id, 'integer');
        $ilDB->manipulate($query);
        return true;
    }
}
