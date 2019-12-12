<?php
/* Copyright (c) 1998-2015 ILIAS open source, Extended GPL, see docs/LICENSE */

include_once './Services/Logging/classes/public/class.ilLogLevel.php';
include_once './Services/Administration/classes/class.ilSetting.php';

/**
*
* @author Stefan Meyer <smeyer.ilias@gmx.de>
* @version $Id$
*
*
* @ingroup ServicesLogging
*/
interface ilLoggingSettings
{
    public function isEnabled();
    
    public function getLogDir();

    public function getLogFile();
    
    public function getLevel();
    
    public function getLevelByComponent($a_component_id);
    
    public function getCacheLevel();
    
    public function isCacheEnabled();
    
    public function isMemoryUsageEnabled();
    
    public function isBrowserLogEnabled();
    
    public function isBrowserLogEnabledForUser($a_login);
    
    public function getBrowserLogUsers();
}
