<?php
require_once 'Services/Init/classes/class.ilErrorHandling.php';
/**
 * @inheritdoc
 */
class ilSetupErrorHandling extends ilErrorHandling
{
    /**
     * @inheritdoc
     */
    public function getHandler()
    {
        global $ilLog;
        if ($ilLog) {
            $ilLog->write("err");
        }
        return $this->devmodeHandler();
    }
}
