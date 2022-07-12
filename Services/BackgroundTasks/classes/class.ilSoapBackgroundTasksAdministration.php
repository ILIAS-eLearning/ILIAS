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
 
use ILIAS\BackgroundTasks\Implementation\TaskManager\AsyncTaskManager;
use ILIAS\BackgroundTasks\Persistence;

class ilSoapBackgroundTasksAdministration extends ilSoapAdministration
{
    public Persistence $persistence;
    
    public function __construct(bool $use_nusoap = true)
    {
        global $DIC;
        parent::__construct($use_nusoap);
        $this->persistence = $DIC->backgroundTasks()->persistence();
    }

    /**
     * @return bool|soap_fault|SoapFault|null
     */
    public function runAsync(string $sid)
    {
        $this->initAuth($sid);
        $this->initIlias();
        
        if (!$this->checkSession($sid)) {
            return $this->raiseError($this->getMessage(), $this->getMessageCode());
        }
        $tm = new AsyncTaskManager($this->persistence);
        $tm->runAsync();
        return true;
    }
}
