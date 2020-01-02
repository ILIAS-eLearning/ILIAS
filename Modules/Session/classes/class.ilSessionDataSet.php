<?php
/* Copyright (c) 1998-2010 ILIAS open source, Extended GPL, see docs/LICENSE */

include_once("./Services/DataSet/classes/class.ilDataSet.php");

/**
 * Session data set class
 *
 * @author Alex Killing <alex.killing@gmx.de>
 * @version $Id$
 * @ingroup ingroup ModulesSession
 */
class ilSessionDataSet extends ilDataSet
{
    /**
     * @var \ilLogger
     */
    private $logger = null;

    /**
     * @var int
     */
    private $target_id = 0;

    public function __construct()
    {
        global $DIC;

        parent::__construct();
        $this->logger = $DIC->logger()->sess();
    }

    /**
     * @param int $target_id
     */
    public function setTargetId(int $target_id)
    {
        $this->target_id = $target_id;
    }

    /**
     * Get supported versions
     *
     * @param
     * @return
     */
    public function getSupportedVersions()
    {
        return array("4.1.0", "5.0.0", "5.1.0", '5.4.0');
    }
    
    /**
     * Get xml namespace
     *
     * @param
     * @return
     */
    public function getXmlNamespace($a_entity, $a_schema_version)
    {
        return "http://www.ilias.de/xml/Modules/Session/" . $a_entity;
    }
    
    /**
     * Get field types for entity
     *
     * @param
     * @return
     */
    protected function getTypes($a_entity, $a_version)
    {
        if ($a_entity == "sess") {
            switch ($a_version) {
                case "4.1.0":
                    return array(
                        "Id" => "integer",
                        "Title" => "text",
                        "Description" => "text",
                        "Location" => "text",
                        "TutorName" => "text",
                        "TutorEmail" => "text",
                        "TutorPhone" => "text",
                        "Details" => "text",
                        "Registration" => "integer",
                        "EventStart" => "text",
                        "EventEnd" => "text",
                        "StartingTime" => "integer",
                        "EndingTime" => "integer",
                        "Fulltime" => "integer"
                        );
                case "5.0.0":
                    return array(
                            "Id" => "integer",
                            "Title" => "text",
                            "Description" => "text",
                            "Location" => "text",
                            "TutorName" => "text",
                            "TutorEmail" => "text",
                            "TutorPhone" => "text",
                            "Details" => "text",
                            "Registration" => "integer",
                            "EventStart" => "text",
                            "EventEnd" => "text",
                            "StartingTime" => "integer",
                            "EndingTime" => "integer",
                            "Fulltime" => "integer",
                            "LimitedRegistration" => "integer",
                            "WaitingList" => "integer",
                            "LimitUsers" => "integer"
                    );
                case "5.1.0":
                    return array(
                        "Id" => "integer",
                        "Title" => "text",
                        "Description" => "text",
                        "Location" => "text",
                        "TutorName" => "text",
                        "TutorEmail" => "text",
                        "TutorPhone" => "text",
                        "Details" => "text",
                        "Registration" => "integer",
                        "EventStart" => "text",
                        "EventEnd" => "text",
                        "StartingTime" => "integer",
                        "EndingTime" => "integer",
                        "Fulltime" => "integer",
                        "LimitedRegistration" => "integer",
                        "WaitingList" => "integer",
                        "AutoWait" => "integer",
                        "LimitUsers" => "integer",
                        "MinUsers" => "integer"
                    );
                case "5.4.0":
                    return array(
                        "Id" => "integer",
                        "Title" => "text",
                        "Description" => "text",
                        "Location" => "text",
                        "TutorName" => "text",
                        "TutorEmail" => "text",
                        "TutorPhone" => "text",
                        "Details" => "text",
                        "Registration" => "integer",
                        "EventStart" => "text",
                        "EventEnd" => "text",
                        "StartingTime" => "integer",
                        "EndingTime" => "integer",
                        "Fulltime" => "integer",
                        "LimitedRegistration" => "integer",
                        "WaitingList" => "integer",
                        "AutoWait" => "integer",
                        "LimitUsers" => "integer",
                        "MinUsers" => "integer",
                        'MailMembers' => 'integer',
                        'ShowMembers' => 'integer',
                        'Type' => 'integer'
                    );
            }
        }

        if ($a_entity == "sess_item") {
            switch ($a_version) {
                case "4.1.0":
                case "5.0.0":
                case "5.1.0":
                case "5.4.0":
                    return array(
                        "SessionId" => "integer",
                        "ItemId" => "text",
                        );
            }
        }
    }

    /**
     * Read data
     *
     * @param
     * @return
     */
    public function readData($a_entity, $a_version, $a_ids, $a_field = "")
    {
        global $DIC;

        $ilDB = $DIC['ilDB'];

        if (!is_array($a_ids)) {
            $a_ids = array($a_ids);
        }
                
        if ($a_entity == "sess") {
            switch ($a_version) {
                case "4.1.0":
                    $this->getDirectDataFromQuery($q = "SELECT ev.obj_id id, od.title title, od.description description, " .
                        " location, tutor_name, tutor_email, tutor_phone, details, registration, " .
                        " e_start event_start, e_end event_end, starting_time, ending_time, fulltime " .
                        " FROM event ev JOIN object_data od ON (ev.obj_id = od.obj_id) " .
                        " JOIN event_appointment ea ON (ev.obj_id = ea.event_id)  " .
                        "WHERE " .
                        $ilDB->in("ev.obj_id", $a_ids, false, "integer"));
                    break;
                case "5.0.0":
                    $this->getDirectDataFromQuery($q = "SELECT ev.obj_id id, od.title title, odes.description description, " .
                            " location, tutor_name, tutor_email, tutor_phone, details, reg_type registration, " .
                            " reg_limited limited_registration, reg_waiting_list waiting_list, " .
                            " reg_limit_users limit_users, " .
                            " e_start event_start, e_end event_end, starting_time, ending_time, fulltime " .
                            " FROM event ev JOIN object_data od ON (ev.obj_id = od.obj_id) " .
                            " JOIN event_appointment ea ON (ev.obj_id = ea.event_id)  " .
                            " JOIN object_description odes ON (ev.obj_id = odes.obj_id) " .
                            "WHERE " .
                            $ilDB->in("ev.obj_id", $a_ids, false, "integer"));
                    break;
                case "5.1.0":
                    $this->getDirectDataFromQuery($q = "SELECT ev.obj_id id, od.title title, odes.description description, " .
                        " location, tutor_name, tutor_email, tutor_phone, details, reg_type registration, " .
                        " reg_limited limited_registration, reg_waiting_list waiting_list, reg_auto_wait auto_wait, " .
                        " reg_limit_users limit_users, reg_min_users min_users, " .
                        " e_start event_start, e_end event_end, starting_time, ending_time, fulltime " .
                        " FROM event ev JOIN object_data od ON (ev.obj_id = od.obj_id) " .
                        " JOIN event_appointment ea ON (ev.obj_id = ea.event_id)  " .
                        " JOIN object_description odes ON (ev.obj_id = odes.obj_id) " .
                        "WHERE " .
                        $ilDB->in("ev.obj_id", $a_ids, false, "integer"));
                    break;
                case "5.4.0":
                    $this->getDirectDataFromQuery($q = "SELECT ev.obj_id id, od.title title, odes.description description, " .
                        " location, tutor_name, tutor_email, tutor_phone, details, reg_type registration, " .
                        " reg_limited limited_registration, reg_waiting_list waiting_list, reg_auto_wait auto_wait, " .
                        " reg_limit_users limit_users, reg_min_users min_users, " .
                        " e_start event_start, e_end event_end, starting_time, ending_time, fulltime, mail_members, show_members " .
                        " FROM event ev JOIN object_data od ON (ev.obj_id = od.obj_id) " .
                        " JOIN event_appointment ea ON (ev.obj_id = ea.event_id)  " .
                        " JOIN object_description odes ON (ev.obj_id = odes.obj_id) " .
                        "WHERE " .
                        $ilDB->in("ev.obj_id", $a_ids, false, "integer"));

                    $this->readDidacticTemplateType($a_ids);
                    break;
            }
        }

        if ($a_entity == "sess_item") {
            switch ($a_version) {
                case "4.1.0":
                case "5.0.0":
                case "5.1.0":
                    $this->getDirectDataFromQuery($q = "SELECT event_id session_id, item_id " .
                        " FROM event_items " .
                        "WHERE " .
                        $ilDB->in("event_id", $a_ids, false, "integer"));
                    break;
            }
        }
    }

    /**
     * Get xml record (export)
     *
     * @param	array	abstract data record
     * @return	array	xml record
     */
    public function getXmlRecord($a_entity, $a_version, $a_set)
    {
        if ($a_entity == "sess") {
            // convert server dates to utc
            if (!$a_set["Fulltime"]) {
                // nothing has to be done here, since the dates are already stored in UTC
                #$start = new ilDateTime($a_set["EventStart"], IL_CAL_DATETIME);
                #$a_set["EventStart"] = $start->get(IL_CAL_DATETIME,'','UTC');
                #$end = new ilDateTime($a_set["EventEnd"], IL_CAL_DATETIME);
                #$a_set["EventEnd"] = $end->get(IL_CAL_DATETIME,'','UTC');
            }
        }
        if ($a_entity == "sess_item") {
            // make ref id an object id
            $a_set["ItemId"] = ilObject::_lookupObjId($a_set["ItemId"]);
        }
        return $a_set;
    }



    /**
     * Determine the dependent sets of data
     */
    protected function getDependencies($a_entity, $a_version, $a_rec, $a_ids)
    {
        switch ($a_entity) {
            case "sess":
                return array(
                    "sess_item" => array("ids" => $a_rec["Id"])
                );
        }

        return false;
    }
    
    
    /**
     * Import record
     *
     * @param
     * @return
     */
    public function importRecord($a_entity, $a_types, $a_rec, $a_mapping, $a_schema_version)
    {
        switch ($a_entity) {
            case "sess":
                include_once("./Modules/Session/classes/class.ilObjSession.php");
                include_once("./Modules/Session/classes/class.ilSessionAppointment.php");

                if ($new_id = $a_mapping->getMapping('Services/Container', 'objs', $a_rec['Id'])) {
                    $refs = ilObject::_getAllReferences($new_id);
                    $newObj = ilObjectFactory::getInstanceByRefId(end($refs), false);
                } else {
                    $this->logger->debug('Session creation without existing instance');
                    $newObj = new ilObjSession();
                    $newObj->setType("sess");
                    $newObj->create(true);
                    $newObj->createReference();
                    $newObj->putInTree($this->target_id);
                    $newObj->setPermissions($this->target_id);
                }
                $newObj->setTitle($a_rec["Title"]);
                $newObj->setDescription($a_rec["Description"]);
                $newObj->setLocation($a_rec["Location"]);
                $newObj->setName($a_rec["TutorName"]);
                $newObj->setPhone($a_rec["TutorPhone"]);
                $newObj->setEmail($a_rec["TutorEmail"]);
                $newObj->setDetails($a_rec["Details"]);

                switch ($a_schema_version) {
                    case "5.0.0":
                    case "5.1.0":
                        $newObj->setRegistrationType($a_rec["Registration"]);

                        $newObj->enableRegistrationUserLimit($a_rec["LimitedRegistration"]);
                        $newObj->setRegistrationMaxUsers($a_rec["LimitUsers"]);
                        $newObj->enableRegistrationWaitingList($a_rec["WaitingList"]);

                        if (isset($a_rec["MinUsers"])) {
                            $newObj->setRegistrationMinUsers($a_rec["MinUsers"]);
                        }

                        if (isset($a_rec["AutoWait"])) {
                            $newObj->setWaitingListAutoFill($a_rec["AutoWait"]);
                        }
                        break;
                    case '5.4.0':
                        if (isset($a_rec['MailMembers'])) {
                            $newObj->setMailToMembersType($a_rec['MailMembers']);
                        }
                        if (isset($a_rec['ShowMembers'])) {
                            $newObj->setShowMembers($a_rec['ShowMembers']);
                        }
                        $this->applyDidacticTemplate($newObj, $a_rec['Type']);
                        break;
                }

                $newObj->update(true);

                $start = new ilDateTime($a_rec["EventStart"], IL_CAL_DATETIME, "UTC");
                $end = new ilDateTime($a_rec["EventEnd"], IL_CAL_DATETIME, "UTC");
//echo "<br>".$start->get(IL_CAL_UNIX);
//echo "<br>".$start->get(IL_CAL_DATETIME);
                $app = new ilSessionAppointment();
                $app->setStart($a_rec["EventStart"]);
                $app->setEnd($a_rec["EventEnd"]);
                $app->setStartingTime($start->get(IL_CAL_UNIX));
                $app->setEndingTime($end->get(IL_CAL_UNIX));
                $app->toggleFullTime($a_rec["Fulltime"]);
                $app->setSessionId($newObj->getId());
                $app->create();
                
                //$newObj->setAppointments(array($app));
                //$newObj->update();

                $this->current_obj = $newObj;
                $a_mapping->addMapping("Modules/Session", "sess", $a_rec["Id"], $newObj->getId());
                $a_mapping->addMapping('Services/Object', 'objs', $a_rec['Id'], $newObj->getId());
                $a_mapping->addMapping('Services/AdvancedMetaData', 'parent', $a_rec['Id'], $newObj->getId());
                $a_mapping->addMapping(
                    "Services/MetaData",
                    "md",
                    $a_rec["Id"] . ":0:sess",
                    $newObj->getId() . ":0:sess"
                );
                
                
//var_dump($a_mapping->mappings["Services/News"]["news_context"]);
                break;

            case "sess_item":

                if ($obj_id = $a_mapping->getMapping('Services/Container', 'objs', $a_rec['ItemId'])) {
                    $ref_id = current(ilObject::_getAllReferences($obj_id));
                    include_once './Modules/Session/classes/class.ilEventItems.php';
                    $evi = new ilEventItems($this->current_obj->getId());
                    $evi->addItem($ref_id);
                    $evi->update();
                }
                break;
        }
    }

    /**
     * @param int[] $a_obj_ids
     */
    protected function readDidacticTemplateType($a_obj_ids)
    {
        $ref_ids = [];
        $counter = 0;
        foreach ($a_obj_ids as $obj_id) {
            $ref_ids = ilObject::_getAllReferences($obj_id);
            foreach ($ref_ids as $ref_id) {
                $tpl_id = ilDidacticTemplateObjSettings::lookupTemplateId($ref_id);
                $this->data[$counter++]['Type'] = (int) $tpl_id;
                break;
            }
        }
    }

    /**
     * @param ilObject $rep_object
     * @param $tpl_id
     */
    protected function applyDidacticTemplate(ilObject $rep_object, $tpl_id)
    {
        $this->logger->debug('Apply didactic template');

        if ((int) $tpl_id == 0) {
            $this->logger->debug('Default permissions');
            // Default template
            return;
        }

        $templates = ilDidacticTemplateSettings::getInstanceByObjectType('sess')->getTemplates();
        foreach ($templates as $template) {
            if ($template->isAutoGenerated()) {
                $this->logger->debug('Apply first auto generated');
                $rep_object->applyDidacticTemplate($template->getId());
            }
        }
    }
}
