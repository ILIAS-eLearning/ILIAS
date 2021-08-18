<?php

/* Copyright (c) 1998-2021 ILIAS open source, GPLv3, see LICENSE */

/**
 * Class ilObjSurveyAdministration
 *
 * @author Helmut Schottmüller <helmut.schottmueller@mac.com>
 */
class ilObjSurveyAdministration extends ilObject
{
    public $setting;
    
    /**
    * Constructor
    * @access	public
    * @param	integer	reference_id or object_id
    * @param	boolean	treat the id as reference_id (true) or object_id (false)
    */
    public function __construct($a_id = 0, $a_call_by_reference = true)
    {
        $this->setting = new ilSetting("survey");
        $this->type = "svyf";
        parent::__construct($a_id, $a_call_by_reference);
    }

    /**
    * update object data
    *
    * @access	public
    * @return	boolean
    */
    public function update()
    {
        if (!parent::update()) {
            return false;
        }

        // put here object specific stuff

        return true;
    }


    /**
    * delete object and all related data
    *
    * @access	public
    * @return	boolean	true if all object data were removed; false if only a references were removed
    */
    public function delete()
    {
        // always call parent delete function first!!
        if (!parent::delete()) {
            return false;
        }

        //put here your module specific stuff

        return true;
    }
    
    /* #7927: special users are deprecated
    function addSpecialUsers($arr_user_id)
    {
        $surveySetting = new ilSetting("survey");
        $allowedUsers = strlen($surveySetting->get("multiple_survey_users")) ? explode(",",$surveySetting->get("multiple_survey_users")) : array();
        $arr = array_unique(array_merge($allowedUsers, $arr_user_id));
        $surveySetting->set("multiple_survey_users", implode(",", $arr));
    }

    function removeSpecialUsers($arr_user_id)
    {
        $surveySetting = new ilSetting("survey");
        $allowedUsers = strlen($surveySetting->get("multiple_survey_users")) ? explode(",",$surveySetting->get("multiple_survey_users")) : array();
        $arr = array_diff($allowedUsers, $arr_user_id);
        $surveySetting->set("multiple_survey_users", implode(",", array_values($arr)));
    }

    function getSpecialUsers()
    {
        $surveySetting = new ilSetting("survey");
        return strlen($surveySetting->get("multiple_survey_users")) ? explode(",",$surveySetting->get("multiple_survey_users")) : array();
    }
    */
} // END class.ilObjSurveyAdministration.php
