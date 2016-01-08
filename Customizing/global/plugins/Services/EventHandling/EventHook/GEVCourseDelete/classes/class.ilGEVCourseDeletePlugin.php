<?php

require_once("./Services/EventHandling/classes/class.ilEventHookPlugin.php");
require_once("Services/GEV/Utils/classes/class.gevCourseUtils.php");

class ilGEVCourseDeletePlugin extends ilEventHookPlugin
{
    final function getPluginName() {
        return "GEVCourseDelete";
    }
    
    final function handleEvent($a_component, $a_event, $a_parameter) {
        if ($a_component !== "Modules/Course" || $a_event !== "delete") {
            return;
        }
        
        global $ilLog;
        
        $this->log = $ilLog;
        
        $this->crs_utils = gevCourseUtils::getInstanceByObj($a_parameter["object"]);
        $this->crs = $a_parameter["object"];
        $this->crs_id = $a_parameter["obj_id"];
        
        if(!$this->crs_utils->isTemplate()) {
            $this->deleteCourse();
        }
    }

    public function deleteCourse() {
        try {
            $this->crs_utils->deleteVCAssignment();
        }
        catch (Exception $e) {
            $this->log->write("Error in GEVCourseDelete::deleteCourse: ".print_r($e, true));
        }
    }
}

?>