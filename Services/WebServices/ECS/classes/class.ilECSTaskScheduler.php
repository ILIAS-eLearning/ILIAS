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
* @author Stefan Meyer <smeyer.ilias@gmx.de>
* @version $Id$
*
*
* @ingroup ServicesWebServicesECS
*/
class ilECSTaskScheduler
{
    const MAX_TASKS = 30;
    
    private static $instances = array();
    
    /**
     * @var ilLogger
     */
    protected $log;
    
    private $event_reader = null;

    protected $settings = null;
    protected $db;
    
    private $mids = array();
    
    /**
     * Singleton constructor
     *
     * @access public
     *
     */
    private function __construct(ilECSSetting $setting)
    {
        global $DIC;

        $ilDB = $DIC['ilDB'];
        $ilLog = $DIC['ilLog'];
        
        $this->db = $ilDB;
        
        $this->log = $GLOBALS['DIC']->logger()->wsrv();
        
        include_once('./Services/WebServices/ECS/classes/class.ilECSSetting.php');
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
        if (self::$instances[$a_server_id]) {
            return self::$instances[$a_server_id];
        }
        include_once './Services/WebServices/ECS/classes/class.ilECSSetting.php';
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
        include_once './Services/Context/classes/class.ilContext.php';
        if (ilContext::getType() != ilContext::CONTEXT_WEB) {
            return;
        }
        
        include_once './Services/WebServices/ECS/classes/class.ilECSServerSettings.php';
        $servers = ilECSServerSettings::getInstance();
        foreach ($servers->getServers() as $server) {
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
        include_once './Services/WebServices/ECS/classes/class.ilECSServerSettings.php';
        $server = ilECSServerSettings::getInstance();
        foreach ($server->getServers() as $server) {
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
        global $DIC;

        $ilLog = $DIC['ilLog'];

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
            include_once('./Services/WebServices/ECS/classes/class.ilECSEventQueueReader.php');
            $this->event_reader = new ilECSEventQueueReader($this->getServer()->getServerId());
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
        include_once './Services/WebServices/ECS/classes/class.ilECSEvent.php';

        for ($i = 0;$i < self::MAX_TASKS;$i++) {
            if (!$event = $this->event_reader->shift()) {
                $this->log->write(__METHOD__ . ': No more pending events found. DONE');
                break;
            }
            
            $this->log->write(print_r($event, true));
            
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
                    include_once 'Services/WebServices/ECS/classes/class.ilRemoteObjectBase.php';
                    $handler = ilRemoteObjectBase::getInstanceByEventType($event['type']);
                    $this->log->write("got handler " . get_class($handler));
                    break;
                
                case ilECSEventQueueReader::TYPE_DIRECTORY_TREES:
                    $this->log->debug('Handling new cms tree event.');
                    include_once './Services/WebServices/ECS/classes/Tree/class.ilECSCmsTreeCommandQueueHandler.php';
                    $handler = new ilECSCmsTreeCommandQueueHandler($this->getServer());
                    break;
                
                case ilECSEventQueueReader::TYPE_CMS_COURSES:
                    include_once './Services/WebServices/ECS/classes/Course/class.ilECSCmsCourseCommandQueueHandler.php';
                    $handler = new ilECSCmsCourseCommandQueueHandler($this->getServer());
                    break;
                
                case ilECSEventQueueReader::TYPE_CMS_COURSE_MEMBERS:
                    include_once './Services/WebServices/ECS/classes/Course/class.ilECSCmsCourseMemberCommandQueueHandler.php';
                    $handler = new ilECSCmsCourseMemberCommandQueueHandler($this->getServer());
                    break;
                
                case ilECSEventQueueReader::TYPE_COURSE_URLS:
                    $this->log->write(__METHOD__ . ': Ignoring event type in queue ' . $event['type']);
                    $event_ignored = true;
                    break;
                
                case ilECSEventQueueReader::TYPE_ENROLMENT_STATUS:
                    include_once './Services/WebServices/ECS/classes/Connectors/class.ilECSEnrolmentStatusCommandQueueHandler.php';
                    $handler = new ilECSEnrolmentStatusCommandQueueHandler($this->getServer());
                    break;

                default:
                    
                    $this->log->warning('Unknown type in queue, raising new event handling event: ' . $event['type']);
                    $event_ignored = true;
                    
                    $GLOBALS['DIC']['ilAppEventHandler']->raise(
                        'Services/WebServices/ECS',
                        'newEcsEvent',
                        array('event' => $event)
                    );
                    break;
            }
            
            if ($event_ignored) {
                $this->event_reader->delete($event['event_id']);
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
                    $this->log->write(__METHOD__ . ': Handling delete. DONE');
                    break;
                        
                case ilECSEvent::CREATED:
                    $res = $handler->handleCreate($this->getServer(), $event['id'], $this->mids);
                    $this->log->write(__METHOD__ . ': Handling create. DONE');
                    break;
                
                case ilECSEvent::UPDATED:
                    $res = $handler->handleUpdate($this->getServer(), $event['id'], $this->mids);
                    $this->log->write(__METHOD__ . ': Handling update. DONE');
                    break;
                
                default:
                    $this->log->write(__METHOD__ . ': Unknown event operation in queue ' . $event['op']);
                    break;
            }
            if ($res) {
                $this->log->write(__METHOD__ . ': Processing of event done ' . $event['event_id']);
                $this->event_reader->delete($event['event_id']);
            } else {
                $this->log->write(__METHOD__ . ': Processing of event failed ' . $event['event_id']);
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
        global $DIC;

        $ilDB = $DIC['ilDB'];
        
        $query = "SELECT usr_id FROM usr_data WHERE auth_mode = 'ecs' " .
            "AND time_limit_until < " . time() . " " .
            "AND time_limit_unlimited = 0 " .
            "AND (time_limit_until - time_limit_from) < 7200";
        $res = $ilDB->query($query);
        while ($row = $res->fetchRow(ilDBConstants::FETCHMODE_OBJECT)) {
            if ($user_obj = ilObjectFactory::getInstanceByObjId($row->usr_id, false)) {
                $this->log->write(__METHOD__ . ': Deleting deprecated ECS user account ' . $user_obj->getLogin());
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
            
            include_once('./Services/WebServices/ECS/classes/class.ilECSCommunityReader.php');
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
        global $DIC;

        $ilDB = $DIC['ilDB'];

        
        if (!$this->settings->isEnabled()) {
            return false;
        }
        
        if (!$this->settings->checkImportId()) {
            $this->log->warning('Import ID is deleted or not of type "category". Aborting');
            return false;
        }
        if (!$this->settings->getPollingTime()) {
            return false;
        }

        // check next task excecution time:
        // If it's greater than time() directly increase this value with the polling time
        /* synchronized { */
        $query = 'UPDATE settings SET ' .
            'value = ' . $ilDB->quote(time() + $this->settings->getPollingTime(), 'text') . ' ' .
            'WHERE module = ' . $ilDB->quote('ecs', 'text') . ' ' .
            'AND keyword = ' . $ilDB->quote('next_execution_' . $this->settings->getServerId(), 'text') . ' ' .
            'AND value < ' . $ilDB->quote(time(), 'text');
        $affected_rows = $ilDB->manipulate($query);
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
        global $DIC;

        $ilLog = $DIC['ilLog'];

        // Start task execution as backend process
        include_once 'Services/WebServices/SOAP/classes/class.ilSoapClient.php';

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
            include_once('./webservice/soap/include/inc.soap_functions.php');
            $res = ilSoapFunctions::handleECSTasks($new_session_id . '::' . $client_id, $this->settings->getServerId());
        }
    }
}
