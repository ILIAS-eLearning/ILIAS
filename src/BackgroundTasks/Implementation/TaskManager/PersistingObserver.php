<?php

/**
 * This file is part of ILIAS, a powerful learning management system
 * published by ILIAS open source e-Learning e.V.
 *
 * ILIAS is licensed with the GPL-3.0,
 * see https://www.gnu.org/licenses/gpl-3.0.en.html
 * You should have received a copy of said license along with the
 * source code, too.
 *
 * If this is not the case or you just want to try ILIAS, you'll find
 * us at:
 * https://www.ilias.de
 * https://github.com/ILIAS-eLearning
 *
 *********************************************************************/
 
namespace ILIAS\BackgroundTasks\Implementation\TaskManager;

use ILIAS\BackgroundTasks\Bucket;
use ILIAS\BackgroundTasks\Observer;
use ILIAS\BackgroundTasks\Persistence;
use ILIAS\BackgroundTasks\Task;

/**
 * Class PersistingObserver
 * @author Oskar Truffer <ot@studer-raimann.ch>
 * When notifying something to the bucket this observer also persists the changes into the database.
 */
class PersistingObserver implements Observer
{
    protected \ILIAS\BackgroundTasks\Bucket $bucket;
    protected \ILIAS\BackgroundTasks\Persistence $persistence;
    
    public function __construct(Bucket $bucket, Persistence $persistence)
    {
        $this->bucket = $bucket;
        $this->persistence = $persistence;
    }
    
    /**
     * @param $state int
     */
    public function notifyState(int $state) : void
    {
        $this->bucket->setState($state);
        $this->bucket->heartbeat();
        $this->persistence->updateBucket($this->bucket);
    }
    
    public function notifyPercentage(Task $task, int $percentage) : void
    {
        $this->bucket->setPercentage($task, $percentage);
        $this->bucket->heartbeat();
        $this->persistence->updateBucket($this->bucket);
    }
    
    public function notifyCurrentTask(Task $task) : void
    {
        $this->bucket->setCurrentTask($task);
        $this->bucket->heartbeat();
        $this->persistence->updateBucket($this->bucket);
    }
    
    /**
     * I'm still alive! If your calculation takes a really long time don't forget to use the heartbeat. Otherwise
     * the bucket might be killed while still running. All notify tasks of the observer also trigger a heartbeat.
     */
    public function heartbeat() : void
    {
        $this->bucket->heartbeat();
        $this->persistence->updateBucket($this->bucket);
    }
}
