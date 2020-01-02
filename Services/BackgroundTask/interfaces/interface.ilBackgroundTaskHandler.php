<?php
/* Copyright (c) 1998-2013 ILIAS open source, Extended GPL, see docs/LICENSE */

/**
 * Background task handler interface
 *
 * @author Jörg Lützenkirchen <luetzenkirchen@leifos.com>
 * @ingroup ServicesBackgroundTask
 */
interface ilBackgroundTaskHandler
{
    /**
     * Constructor/Factory
     * @param ilBackgroundTask $a_task
     * @return \self
     */
    public static function getInstanceFromTask(ilBackgroundTask $a_task);
    
    /**
     * Get current task instance
     *
     * @return \ilBackgroundTask
     */
    public function getTask();
        
    /**
     * Init background task
     *
     * @param mixed $a_params
     * @return \stdClass json
     */
    public function init($a_params);
    
    /**
     * Process the task
     */
    public function process();
    
    /**
     * Cancel the task
     */
    public function cancel();
    
    /**
     * Finish the task
     */
    public function finish();
    
    /**
     * Remove task and its files
     */
    public function deleteTaskAndFiles();
}
