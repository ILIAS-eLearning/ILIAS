<?php
require_once('./Services/WebAccessChecker/interfaces/interface.ilWACCheckingClass.php');
require_once('./Services/MediaObjects/classes/class.ilObjMediaObject.php');

/**
 * Class ilContentStyleWAC
 *
 * @author  Alex Killing <killing@leifos.de>
 * @version 1.0.0
 */
class ilContentStyleWAC implements ilWACCheckingClass
{

    /**
     * @param ilWACPath $ilWACPath
     *
     * @return bool
     */
    public function canBeDelivered(ilWACPath $ilWACPath)
    {
        //preg_match("/.\\/data\\/.*\\/mm_([0-9]*)\\/.*/ui", $ilWACPath->getPath(), $matches);
        return true;
    }
}
