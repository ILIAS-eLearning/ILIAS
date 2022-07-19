<?php declare(strict_types=1);

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
 ********************************************************************
 */

/**
 * Session data set class
 *
 * @author Alex Killing <alex.killing@gmx.de>
 * @version $Id$
 * @ingroup ingroup ModulesSession
 */
class ilSessionDataSet extends ilDataSet
{
    protected ilLogger $logger;
    protected string $target_id = "";
    protected ilObjSession $current_obj;

    public function __construct()
    {
        global $DIC;

        parent::__construct();
        $this->logger = $DIC->logger()->root();
    }

    public function setTargetId(string $target_id) : void
    {
        $this->target_id = $target_id;
    }

    public function getSupportedVersions() : array
    {
        return ['7.0'];
        //return array("4.1.0", "5.0.0", "5.1.0", '5.4.0', '7.0');
    }

    protected function getXmlNamespace(string $a_entity, string $a_schema_version) : string
    {
        return "http://www.ilias.de/xml/Modules/Session/" . $a_entity;
    }

    protected function getTypes(string $a_entity, string $a_version) : array
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
                case "7.0":
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
                        'Type' => 'integer',
                        'ShowCannotPart' => 'integer'
                    );
            }
        }

        if ($a_entity == "sess_item") {
            switch ($a_version) {
                case "4.1.0":
                case "5.0.0":
                case "5.1.0":
                case "5.4.0":
                case '7.0':
                    return array(
                        "SessionId" => "integer",
                        "ItemId" => "text",
                        );
            }
        }

        return [];
    }

    public function readData(string $a_entity, string $a_version, array $a_ids) : void
    {
        $ilDB = $this->db;
                
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
                case "7.0":
                    $this->getDirectDataFromQuery($q = "SELECT ev.obj_id id, od.title title, odes.description description, " .
                        " location, tutor_name, tutor_email, tutor_phone, details, reg_type registration, " .
                        " reg_limited limited_registration, reg_waiting_list waiting_list, reg_auto_wait auto_wait, " .
                        " reg_limit_users limit_users, reg_min_users min_users, " .
                        " e_start event_start, e_end event_end, starting_time, ending_time, fulltime, mail_members, show_members " .
                        " show_cannot_part " .
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

    public function getXmlRecord(string $a_entity, string $a_version, array $a_set) : array
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
            $a_set["ItemId"] = ilObject::_lookupObjId((int) ($a_set["ItemId"] ?? 0));
        }
        return $a_set;
    }

    protected function getDependencies(
        string $a_entity,
        string $a_version,
        ?array $a_rec = null,
        ?array $a_ids = null
    ) : array {
        switch ($a_entity) {
            case "sess":
                return array(
                    "sess_item" => array("ids" => ($a_rec["Id"] ?? ''))
                );
        }

        return [];
    }

    public function importRecord(string $a_entity, array $a_types, array $a_rec, ilImportMapping $a_mapping, string $a_schema_version) : void
    {
        switch ($a_entity) {
            case "sess":
                if ($new_id = $a_mapping->getMapping('Services/Container', 'objs', $a_rec['Id'])) {
                    $refs = ilObject::_getAllReferences((int) $new_id);
                    $newObj = ilObjectFactory::getInstanceByRefId(end($refs), false);
                } else {
                    $this->logger->debug('Session creation without existing instance');
                    $newObj = new ilObjSession();
                    $newObj->setType("sess");
                    $newObj->create(true);
                }
                $newObj->setTitle((string) ($a_rec["Title"] ?? ''));
                $newObj->setDescription((string) ($a_rec["Description"] ?? ''));
                $newObj->setLocation((string) ($a_rec["Location"] ?? ''));
                $newObj->setName((string) ($a_rec["TutorName"] ?? ''));
                $newObj->setPhone((string) ($a_rec["TutorPhone"] ?? ''));
                $newObj->setEmail((string) ($a_rec["TutorEmail"] ?? ''));
                $newObj->setDetails((string) ($a_rec["Details"] ?? ''));

                switch ($a_schema_version) {
                    case "5.0.0":
                    case "5.1.0":
                        $newObj->setRegistrationType((int) ($a_rec["Registration"] ?? 0));

                        $newObj->enableRegistrationUserLimit((int) ($a_rec["LimitedRegistration"] ?? 0));
                        $newObj->setRegistrationMaxUsers((int) ($a_rec["LimitUsers"] ?? 0));
                        $newObj->enableRegistrationWaitingList((bool) ($a_rec["WaitingList"] ?? false));

                        if (isset($a_rec["MinUsers"])) {
                            $newObj->setRegistrationMinUsers((int) ($a_rec["MinUsers"] ?? 0));
                        }

                        if (isset($a_rec["AutoWait"])) {
                            $newObj->setWaitingListAutoFill((bool) ($a_rec["AutoWait"] ?? false));
                        }
                        break;
                    case '5.4.0':
                    case '7.0':
                        if (isset($a_rec['MailMembers'])) {
                            $newObj->setMailToMembersType((int) ($a_rec['MailMembers'] ?? 0));
                        }
                        if (isset($a_rec['ShowMembers'])) {
                            $newObj->setShowMembers((bool) ($a_rec['ShowMembers'] ?? false));
                        }
                        if (isset($a_rec['ShowCannotPart'])) {
                            $newObj->enableCannotParticipateOption((bool) ($a_rec['show_cannot_part'] ?? false));
                            break;
                        }
                        $this->applyDidacticTemplate($newObj, (int) ($a_rec['Type'] ?? 0));
                        break;
                }

                $newObj->update(true);

                $start = new ilDateTime($a_rec["EventStart"], IL_CAL_DATETIME, "UTC");
                $end = new ilDateTime($a_rec["EventEnd"], IL_CAL_DATETIME, "UTC");
                $app = new ilSessionAppointment();
                $app->setStart($start);
                $app->setEnd($end);
                $app->setStartingTime($start->get(IL_CAL_UNIX));
                $app->setEndingTime($end->get(IL_CAL_UNIX));
                $app->toggleFullTime((bool) ($a_rec["Fulltime"] ?? false));
                $app->setSessionId($newObj->getId());
                $app->create();

                $this->current_obj = $newObj;
                $a_mapping->addMapping("Modules/Session", "sess", $a_rec["Id"], (string) $newObj->getId());
                $a_mapping->addMapping('Services/Object', 'objs', $a_rec['Id'], (string) $newObj->getId());
                $a_mapping->addMapping('Services/AdvancedMetaData', 'parent', $a_rec['Id'], (string) $newObj->getId());
                $a_mapping->addMapping(
                    "Services/MetaData",
                    "md",
                    $a_rec["Id"] . ":0:sess",
                    $newObj->getId() . ":0:sess"
                );
                break;

            case "sess_item":
                if ($obj_id = $a_mapping->getMapping('Services/Container', 'objs', $a_rec['ItemId'])) {
                    $ref_id = current(ilObject::_getAllReferences((int) $obj_id));
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
    protected function readDidacticTemplateType(array $a_obj_ids) : void
    {
        $ref_ids = [];
        $counter = 0;
        foreach ($a_obj_ids as $obj_id) {
            $ref_ids = ilObject::_getAllReferences((int) $obj_id);
            foreach ($ref_ids as $ref_id) {
                $tpl_id = ilDidacticTemplateObjSettings::lookupTemplateId((int) $ref_id);
                $this->data[$counter++]['Type'] = $tpl_id;
                break;
            }
        }
    }

    protected function applyDidacticTemplate(ilObject $rep_object, int $tpl_id) : void
    {
        $this->logger->debug('Apply didactic template');

        if ($tpl_id == 0) {
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
