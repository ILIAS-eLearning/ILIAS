<?php
/* Copyright (c) 1998-2013 ILIAS open source, Extended GPL, see docs/LICENSE */

include_once "Services/BackgroundTask/classes/class.ilBackgroundTask.php";

/**
 * background task hub (aka ajax handler, GUI)
 *
 * @author Jörg Lützenkirchen <luetzenkirchen@leifos.com>
 *
 * @ilCtrl_Calls ilBackgroundTaskHub:
 */
class ilBackgroundTaskHub
{
    protected $task; // [ilBackgroundTask]
    protected $handler; // [ilBackgroundTaskHandler]
    
    /**
     * Constructor
     *
     * @return \self
     */
    public function __construct()
    {
        global $DIC;
        $lng = $DIC['lng'];
        
        $lng->loadLanguageModule("bgtask");
        
        if ((int) $_REQUEST["tid"]) {
            $this->task = new ilBackgroundTask((int) $_REQUEST["tid"]);
            $this->handler = $this->task->getHandlerInstance();
        }
    }


    //
    // ajax
    //
    
    /**
     * Execute current command
     */
    public function executeCommand()
    {
        global $DIC;
        $ilCtrl = $DIC['ilCtrl'];
        
        $next_class = $ilCtrl->getNextClass($this);
        $cmd = $ilCtrl->getCmd("validate");
            
        switch ($next_class) {
            default:
                if ($cmd == "deliver" ||
                    $ilCtrl->isAsynch()) {
                    $this->$cmd();
                    break;
                }
        }
        
        // deliver file and ajax require exit
        exit();
    }
    
    /**
     * Send Json to client
     *
     * @param stdClass $a_json
     */
    protected function sendJson(stdClass $a_json)
    {
        echo json_encode($a_json);
    }
    
    /**
     * Validate given task
     */
    protected function validate()
    {
        $class = trim($_GET["hid"]);
        $file = "Services/BackgroundTask/classes/class." . $class . ".php";
        if (file_exists($file)) {
            include_once $file;
            $handler = new $class();
            $json = $handler->init($_GET["par"]);
        
            $this->sendJson($json);
        }
    }
    
    /**
     * Cancel all other tasks, start current one
     *
     */
    protected function unblock()
    {
        global $DIC;
        $ilUser = $DIC['ilUser'];
        
        foreach (ilBackgroundTask::getActiveByUserId($ilUser->getId()) as $task_id) {
            // leave current task alone
            if ($task_id != $this->task->getId()) {
                // emit cancelling status, running processes will cancel
                $task = new ilBackgroundTask($task_id);
                $task->setStatus(ilBackgroundTask::STATUS_CANCELLING);
                $task->save();
            }
        }
        
        // init/start current task
        $json = $this->handler->init();
        $this->sendJson($json);
    }
    
    /**
     * Process current task
     */
    protected function process()
    {
        $this->task->setStatus(ilBackgroundTask::STATUS_PROCESSING);
        $this->task->save();
        
        if (!$this->isSOAPEnabled()) {
            $this->handler->process();
        } else {
            require_once 'Services/WebServices/SOAP/classes/class.ilSoapClient.php';
            $soap_client = new ilSoapClient();
            $soap_client->setResponseTimeout(1);
            $soap_client->enableWSDL(true);
            $soap_client->init();
            $soap_client->call('processBackgroundTask', array(
                session_id() . '::' . $_COOKIE['ilClientId'],
                $this->task->getId()
            ));
        }
    }
    
    /**
     * Is soap enabled?
     *
     * @return bool
     */
    public function isSOAPEnabled()
    {
        global $DIC;
        $ilSetting = $DIC['ilSetting'];

        // see ilMail
        return (extension_loaded('curl') &&
            $ilSetting->get('soap_user_administration') &&
            ilContext::getType() != ilContext::CONTEXT_CRON);
    }
    
    /**
     * Check progress of current task
     */
    protected function progress()
    {
        include_once "Services/BackgroundTask/classes/class.ilBackgroundTaskJson.php";
        
        // if task has been finished, get result action
        if ($this->task->getStatus() == ilBackgroundTask::STATUS_FINISHED) {
            $result = $this->handler->finish();
            $json = ilBackgroundTaskJson::getProgressJson($this->task, $result[0], $result[1]);
        } else {
            $json = ilBackgroundTaskJson::getProgressJson($this->task);
        }
        
        $this->sendJson($json);
    }
    
    /**
     * Cancel current task
     */
    protected function cancel()
    {
        // just emit cancelling status, (background) process will stop ASAP
        $this->task->setStatus(ilBackgroundTask::STATUS_CANCELLING);
        $this->task->save();
    }
    
    /**
     * Deliver result
     */
    protected function deliver()
    {
        // :TODO: delete task?
        
        $this->handler->deliver();
    }
}
