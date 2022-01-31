<?php declare(strict_types=1);

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
*
* @author Stefan Meyer <smeyer.ilias@gmx.de>
* @version $Id$
*
*
* @ingroup ServicesWebServicesECS
*/
class ilECSTaskScheduler
{
    const MAX_TASKS = 30;
    
    private static array $instances = array();
    
    // Injected
    private ilLogger $log;
    private ilDBInterface $db;
    private ilAppEventHandler $eventHandler;
    
    // Local
    private ilECSSetting $settings;
    private ?\ilECSEventQueueReader $event_reader = null;
    private array $mids = array();
    
    /**
     * Singleton constructor
     *
     * @access public
     *
     */
    private function __construct(ilECSSetting $setting)
    {
        global $DIC;

        $this->db = $DIC->database();
        $this->log = $DIC->logger()->wsrv();
        $this->eventHandler = $DIC->event();
        
        $this->settings = $setting;
    }

    /**
     * get singleton instance
     * Private access use
     * ilECSTaskScheduler::start() or
     * ilECSTaskScheduler::startTaskExecution
     *
     * @access private
     * @static
     *
     * @return ilECSTaskScheduler
     *
     */
    public static function _getInstanceByServerId($a_server_id)
    {
        if (isset(self::$instances[$a_server_id])) {
            return self::$instances[$a_server_id];
        }
        return self::$instances[$a_server_id] =
            new ilECSTaskScheduler(
                ilECSSetting::getInstanceByServerId($a_server_id)
            );
    }

    /**
     * Start task scheduler for each server instance
     */
    public static function start()
    {
        if (ilContext::getType() != ilContext::CONTEXT_WEB) {
            return;
        }
        
        $servers = ilECSServerSettings::getInstance();
        foreach ($servers->getServers(ilECSServerSettings::ACTIVE_SERVER) as $server) {
            $sched = new ilECSTaskScheduler($server);
            if ($sched->checkNextExecution()) {
                $sched->initNextExecution();
            }
        }
    }

    /**
     * Static version iterates over all active instances
     */
    public static function startExecution()
    {
        $server = ilECSServerSettings::getInstance();
        foreach ($server->getServers(ilECSServerSettings::ACTIVE_SERVER) as $server) {
            $sched = new ilECSTaskScheduler($server);
            $sched->startTaskExecution();
        }
    }

    /**
     * Get server setting
     * @return ilECSSetting
     */
    public function getServer()
    {
        return $this->settings;
    }


    /**
     * Start Tasks
     *
     * @access private
     *
     */
    public function startTaskExecution()
    {
        try {
            $this->readMIDs();
            $this->readEvents();
            $this->handleEvents();
            
            $this->handleDeprecatedAccounts();
        } catch (ilException $exc) {
            $this->log->warning('Cannot start ecs task execution: ' . $exc->getMessage());
            return false;
        }
        return true;
    }
    
    /**
     * Read EContent
     *
     * @access private
     *
     */
    private function readEvents()
    {
        try {
            $this->event_reader = new ilECSEventQueueReader($this->getServer());
            $this->event_reader->refresh();
        } catch (ilException $exc) {
            throw $exc;
        }
    }
    
    /**
     * Handle events
     *
     * @access private
     *
     */
    private function handleEvents()
    {
        for ($i = 0;$i < self::MAX_TASKS;$i++) {
            if (!$event = $this->event_reader->shift()) {
                $this->log->info(__METHOD__ . ': No more pending events found. DONE');
                break;
            }
            
            $this->log->info(print_r($event, true));
            
            // determine event handler
            
            $event_ignored = false;
            switch ($event['type']) {
                case ilECSEventQueueReader::TYPE_REMOTE_COURSE:
                case ilECSEventQueueReader::TYPE_REMOTE_CATEGORY:
                case ilECSEventQueueReader::TYPE_REMOTE_FILE:
                case ilECSEventQueueReader::TYPE_REMOTE_GLOSSARY:
                case ilECSEventQueueReader::TYPE_REMOTE_GROUP:
                case ilECSEventQueueReader::TYPE_REMOTE_LEARNING_MODULE:
                case ilECSEventQueueReader::TYPE_REMOTE_WIKI:
                case ilECSEventQueueReader::TYPE_REMOTE_TEST:
                    $handler = ilRemoteObjectBase::getInstanceByEventType($event['type']);
                    $this->log->write("got handler " . get_class($handler));
                    break;
                
                case ilECSEventQueueReader::TYPE_DIRECTORY_TREES:
                    $this->log->debug('Handling new cms tree event.');
                    $handler = new ilECSCmsTreeCommandQueueHandler($this->getServer());
                    break;
                
                case ilECSEventQueueReader::TYPE_CMS_COURSES:
                    $handler = new ilECSCmsCourseCommandQueueHandler($this->getServer());
                    break;
                
                case ilECSEventQueueReader::TYPE_CMS_COURSE_MEMBERS:
                    $handler = new ilECSCmsCourseMemberCommandQueueHandler($this->getServer());
                    break;
                
                case ilECSEventQueueReader::TYPE_COURSE_URLS:
                    $this->log->info(__METHOD__ . ': Ignoring event type in queue ' . $event['type']);
                    $event_ignored = true;
                    break;
                
                case ilECSEventQueueReader::TYPE_ENROLMENT_STATUS:
                    $handler = new ilECSEnrolmentStatusCommandQueueHandler($this->getServer());
                    break;

                default:
                    
                    $this->log->warning('Unknown type in queue, raising new event handling event: ' . $event['type']);
                    $event_ignored = true;
                    
                    $this->eventHandler->raise(
                        'Services/WebServices/ECS',
                        'newEcsEvent',
                        array('event' => $event)
                    );
                    break;
            }
            
            if ($event_ignored) {
                $this->event_reader->deleteEvent($event['event_id']);
                continue;
            }
            
            $res = false;
            switch ($event['op']) {
                case ilECSEvent::NEW_EXPORT:
                    // DEPRECATED?
                    // $this->handleNewlyCreate($event['id']);
                    // $this->log->write(__METHOD__.': Handling new creation. DONE');
                    break;
            
                case ilECSEvent::DESTROYED:
                    $res = $handler->handleDelete($this->getServer(), $event['id'], $this->mids);
                    $this->log->info(__METHOD__ . ': Handling delete. DONE');
                    break;
                        
                case ilECSEvent::CREATED:
                    $res = $handler->handleCreate($this->getServer(), $event['id'], $this->mids);
                    $this->log->info(__METHOD__ . ': Handling create. DONE');
                    break;
                
                case ilECSEvent::UPDATED:
                    $res = $handler->handleUpdate($this->getServer(), $event['id'], $this->mids);
                    $this->log->info(__METHOD__ . ': Handling update. DONE');
                    break;
                
                default:
                    $this->log->info(__METHOD__ . ': Unknown event operation in queue ' . $event['op']);
                    break;
            }
            if ($res) {
                $this->log->info(__METHOD__ . ': Processing of event done ' . $event['event_id']);
                $this->event_reader->deleteEvent($event['event_id']);
            } else {
                $this->log->info(__METHOD__ . ': Processing of event failed ' . $event['event_id']);
            }
        }
    }
    
    /**
     * Delete deprecate ECS accounts
     *
     * @access private
     *
     */
    private function handleDeprecatedAccounts()
    {
        $query = "SELECT usr_id FROM usr_data WHERE auth_mode = 'ecs' " .
            "AND time_limit_until < " . time() . " " .
            "AND time_limit_unlimited = 0 " .
            "AND (time_limit_until - time_limit_from) < 7200";
        $res = $this->db->query($query);
        while ($row = $res->fetchRow(ilDBConstants::FETCHMODE_OBJECT)) {
            if ($user_obj = ilObjectFactory::getInstanceByObjId($row->usr_id, false)) {
                $this->log->info(__METHOD__ . ': Deleting deprecated ECS user account ' . $user_obj->getLogin());
                $user_obj->delete();
            }
            // only one user
            break;
        }
        return true;
    }
    
    /**
     * Read MID's of this installation
     *
     * @access private
     *
     */
    private function readMIDs()
    {
        try {
            $this->mids = array();
            
            $reader = ilECSCommunityReader::getInstanceByServerId($this->getServer()->getServerId());
            foreach ($reader->getCommunities() as $com) {
                foreach ($com->getParticipants() as $part) {
                    if ($part->isSelf()) {
                        $this->mids[] = $part->getMID();
                    }
                }
            }
        } catch (ilException $exc) {
            throw $exc;
        }
    }
    
    
    /**
     * Start
     *
     * @access public
     *
     */
    public function checkNextExecution()
    {
        if (!$this->settings->isEnabled()) {
            return false;
        }
        
        if (!$this->settings->checkImportId()) {
            $this->log->warning('Import ID is deleted or not of type "category". Aborting');
            return false;
        }

        // check next task excecution time:
        // If it's greater than time() directly increase this value with the polling time
        /* synchronized { */
        $query = 'UPDATE settings SET ' .
            'value = ' . $this->db->quote(time() + $this->settings->getPollingTime(), 'text') . ' ' .
            'WHERE module = ' . $this->db->quote('ecs', 'text') . ' ' .
            'AND keyword = ' . $this->db->quote('next_execution_' . $this->settings->getServerId(), 'text') . ' ' .
            'AND value < ' . $this->db->quote(time(), 'text');
        $affected_rows = $this->db->manipulate($query);
        /* } */


        if (!$affected_rows) {
            // Nothing to do
            return false;
        }
        return true;
    }


    /**
     * Call next task scheduler run
     */
    protected function initNextExecution()
    {
        // Start task execution as backend process
        $soap_client = new ilSoapClient();
        $soap_client->setResponseTimeout(1);
        $soap_client->enableWSDL(true);

        $new_session_id = ilSession::_duplicate($_COOKIE[session_name()]);
        $client_id = $_COOKIE['ilClientId'];

        if ($soap_client->init() and 0) {
            $this->log->info('Calling soap handleECSTasks method...');
            $res = $soap_client->call('handleECSTasks', array($new_session_id . '::' . $client_id,$this->settings->getServerId()));
        } else {
            $this->log->info('SOAP call failed. Calling clone method manually. ');
            $res = ilSoapFunctions::handleECSTasks($new_session_id . '::' . $client_id, $this->settings->getServerId());
        }
    }
}
