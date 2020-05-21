<?php

/* Copyright (c) 1998-2019 ILIAS open source, Extended GPL, see docs/LICENSE */

/**
 * Class ilObjSurveyListGUI
 *
 * @author Alex Killing <alex.killing@gmx.de>
 * @author Helmut Schottmüller <helmut.schottmueller@mac.com>
 */
class ilObjSurveyListGUI extends ilObjectListGUI
{
    /**
     * @var ilRbacSystem
     */
    protected $rbacsystem;

    /**
    * constructor
    *
    */
    public function __construct()
    {
        global $DIC;

        $this->lng = $DIC->language();
        $this->lng->loadLanguageModule("survey");
        $this->user = $DIC->user();
        $this->rbacsystem = $DIC->rbac()->system();
        parent::__construct();
        $this->info_screen_enabled = true;
    }

    /**
    * initialisation
    */
    public function init()
    {
        $this->static_link_enabled = true;
        $this->delete_enabled = true;
        $this->cut_enabled = true;
        $this->copy_enabled = true;
        $this->subscribe_enabled = true;
        $this->link_enabled = true;
        $this->type = "svy";
        $this->gui_class_name = "ilobjsurveygui";

        // general commands array
        $this->commands = ilObjSurveyAccess::_getCommands();
    }



    /**
    * Get command target frame
    *
    * @param	string		$a_cmd			command
    *
    * @return	string		command target frame
    */
    public function getCommandFrame($a_cmd)
    {
        switch ($a_cmd) {
            case "":
            case "infoScreen":
            case "evaluation":
                $frame = ilFrameTargetInfo::_getFrame("MainContent");
                break;

            default:
        }

        return $frame;
    }



    /**
    * Get item properties
    *
    * @return	array		array of property arrays:
    *						"alert" (boolean) => display as an alert property (usually in red)
    *						"property" (string) => property name
    *						"value" (string) => property value
    */
    public function getProperties()
    {
        $lng = $this->lng;
        $ilUser = $this->user;
        $rbacsystem = $this->rbacsystem;

        $props = [];

        if (!$rbacsystem->checkAccess("visible,read", $this->ref_id)) {
            return $props;
        }

        $props = parent::getProperties();
        
        if (!ilObject::lookupOfflineStatus($this->obj_id)) {
            // BEGIN Usability Distinguish between status and participation
            if (!ilObjSurveyAccess::_lookupCreationComplete($this->obj_id)) {
                // no completion
                $props[] = array("alert" => true,
                    "property" => $lng->txt("svy_participation"),
                    "value" => $lng->txt("svy_warning_survey_not_complete"),
                    'propertyNameVisible' => false);
            } else {
                if ($ilUser->getId() != ANONYMOUS_USER_ID) {
                    $mode = ilObjSurveyAccess::_lookupMode($this->obj_id);
                    if ($mode == ilObjSurvey::MODE_360) {
                        $props[] = array("alert" => false, "property" => $lng->txt("type"),
                                         "value" => $lng->txt("survey_360_mode"), 'propertyNameVisible' => true);
                    } elseif ($mode == ilObjSurvey::MODE_SELF_EVAL) {
                        $props[] = array("alert" => false, "property" => $lng->txt("type"),
                                         "value" => $lng->txt("survey_360_self_evaluation"), 'propertyNameVisible' => true);
                    } else {
                        $finished = ilObjSurveyAccess::_lookupFinished($this->obj_id, $ilUser->id);

                        // finished
                        if ($finished === 1) {
                            $stat = $this->lng->txt("svy_finished");
                        }
                        // not finished
                        elseif ($finished === 0) {
                            $stat = $this->lng->txt("svy_not_finished");
                        }
                        // not started
                        else {
                            $stat = $this->lng->txt("svy_not_started");
                        }
                        $props[] = array("alert" => false, "property" => $lng->txt("svy_participation"),
                            "value" => $stat, 'propertyNameVisible' => true);
                    }
                }
            }
            // END Usability Distinguish between status and participation
        }

        return $props;
    }


    /**
    * Get command link url.
    *
    * @param	int			$a_ref_id		reference id
    * @param	string		$a_cmd			command
    *
    */
    public function getCommandLink($a_cmd)
    {
        $cmd_link = "";
        switch ($a_cmd) {
            default:
                $cmd_link = "ilias.php?baseClass=ilObjSurveyGUI&amp;ref_id=" . $this->ref_id .
                    "&amp;cmd=$a_cmd";
                break;
        }
        // separate method for this line
        return $cmd_link;
    }
} // END class.ilObjTestListGUI
