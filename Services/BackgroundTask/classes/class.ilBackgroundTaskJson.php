<?php
/* Copyright (c) 1998-2013 ILIAS open source, Extended GPL, see docs/LICENSE */

/**
 * Background task JSON helper
 *
 * @author Jörg Lützenkirchen <luetzenkirchen@leifos.com>
 * @ingroup ServicesBackgroundTask
 */
class ilBackgroundTaskJson
{
    /**
     * Get json for failed task
     *
     * @param string $a_message
     * @return \stdClass
     */
    public static function getFailedJson($a_message)
    {
        global $DIC;
        $lng = $DIC['lng'];
        
        $json = new stdClass();
        
        $json->status = "fail";
        $json->title = self::jsonSafeString($lng->txt("bgtask_failure"));
        $json->message = self::jsonSafeString($a_message);
        $json->button = self::jsonSafeString($lng->txt("ok"));
        
        return $json;
    }
    
    /**
     * Get json for blocked task
     *
     * @param int $a_task_id
     * @return \stdClass
     */
    public static function getBlockedJson($a_task_id)
    {
        global $DIC;
        $lng = $DIC['lng'];
        
        $json = new stdClass();
        
        $json->status = "block";
        $json->task_id = $a_task_id;
        $json->title = self::jsonSafeString($lng->txt("bgtask_blocked"));
        $json->message = self::jsonSafeString($lng->txt("bgtask_blocked_info"));
        $json->button_old = self::jsonSafeString($lng->txt("bgtask_blocked_cancel_old"));
        $json->button_new = self::jsonSafeString($lng->txt("bgtask_blocked_cancel_new"));
        
        return $json;
    }
    
    /**
     * Get json for processing task
     *
     * @param int $a_task_id
     * @param string $a_message
     * @param int $a_steps
     * @return \stdClass
     */
    public static function getProcessingJson($a_task_id, $a_message, $a_steps)
    {
        global $DIC;
        $lng = $DIC['lng'];
        
        $json = new stdClass();
        
        $json->status = "bg";
        $json->task_id = $a_task_id;
        $json->title = self::jsonSafeString($lng->txt("bgtask_processing"));
        $json->message = self::jsonSafeString($a_message);
        $json->button = self::jsonSafeString($lng->txt("cancel"));
        $json->steps = (int) $a_steps;
        
        return $json;
    }
    
    /**
     * Get json for task progress
     *
     * @param ilBackgroundTask $a_message
     * @param string $a_finished_cmd
     * @param string $a_finished_result
     * @return \stdClass
     */
    public static function getProgressJson(ilBackgroundTask $a_task, $a_finished_cmd = null, $a_finished_result = null)
    {
        $json = new stdClass();
        
        $json->status = $a_task->getStatus();
        $json->steps = $a_task->getSteps();
        $json->current = $a_task->getCurrentStep();
        
        // if task has been finished, get result action
        if ($a_finished_cmd) {
            $json->result_cmd = $a_finished_cmd;
            $json->result = $a_finished_result;
        }
                
        return $json;
    }
    
    /**
     * Get json for finished task
     *
     * @param int $a_task_id
     * @param string $a_cmd
     * @param string $a_result
     * @return \stdClass
     */
    public static function getFinishedJson($a_task_id, $a_cmd, $a_result)
    {
        $json = new stdClass();
        
        $json->status = "finished";
        $json->task_id = $a_task_id;
        $json->result_cmd = $a_cmd;
        $json->result = $a_result;
        
        return $json;
    }
    
    /**
     * Makes the specified string safe for JSON
     *
     * @param string $a_text
     * @return string
     */
    protected static function jsonSafeString($a_text)
    {
        if (!is_string($a_text)) {
            return $a_text;
        }

        $a_text = htmlentities($a_text, ENT_COMPAT | ENT_HTML401, "UTF-8");
        $a_text = str_replace("'", "&#039;", $a_text);

        return $a_text;
    }
}
