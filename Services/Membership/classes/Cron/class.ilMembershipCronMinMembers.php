<?php declare(strict_types=1);

/* Copyright (c) 1998-2010 ILIAS open source, Extended GPL, see docs/LICENSE */

/**
 * Cron for course/group minimum members
 * @author  Jörg Lützenkirchen <luetzenkirchen@leifos.com>
 * @ingroup ServicesMembership
 */
class ilMembershipCronMinMembers extends ilCronJob
{
    protected ilLanguage $lng;

    public function __construct()
    {
        global $DIC;

        $this->lng = $DIC->language();
    }

    public function getId() : string
    {
        return "mem_min_members";
    }

    public function getTitle() : string
    {
        return $this->lng->txt("mem_cron_min_members");
    }

    public function getDescription() : string
    {
        return $this->lng->txt("mem_cron_min_members_info");
    }

    public function getDefaultScheduleType() : int
    {
        return self::SCHEDULE_TYPE_DAILY;
    }

    public function getDefaultScheduleValue() : ?int
    {
        return null;
    }

    public function hasAutoActivation() : bool
    {
        return true;
    }

    public function hasFlexibleSchedule() : bool
    {
        return false;
    }

    public function run() : ilCronJobResult
    {
        $status = ilCronJobResult::STATUS_NO_ACTION;
        $message = null;

        $recipients_map = array();

        $this->getCourses($recipients_map);
        $this->getGroups($recipients_map);

        if (sizeof($recipients_map)) {
            foreach ($recipients_map as $reci_id => $items) {
                $this->sendMessage($reci_id, $items);
            }

            $status = ilCronJobResult::STATUS_OK;
            $message = sizeof($recipients_map) . " notifications sent";
        }

        $result = new ilCronJobResult();
        $result->setStatus($status);
        $result->setMessage($message);

        return $result;
    }

    protected function getCourses(array &$a_recipients_map) : void
    {
        foreach (ilObjCourse::findCoursesWithNotEnoughMembers() as $obj_id => $item) {
            $too_few = (bool) $item[0];

            /*
            $ilDB->manipulate("UPDATE crs_settings".
                " SET cancel_end_noti = ".$ilDB->quote($now, "integer").
                " WHERE obj_id = ".$ilDB->quote($obj_id, "integer"));
            */

            if ($too_few) {
                // not enough members: notifiy course admins
                foreach ($item[1] as $reci_id) {
                    $a_recipients_map[$reci_id][] = array("crs", $obj_id, $item[0]);
                }
            } else {
                // enough members: notify members?
                // :TODO: ?
            }
        }
    }

    protected function getGroups(array &$a_recipients_map) : void
    {
        foreach (ilObjGroup::findGroupsWithNotEnoughMembers() as $obj_id => $item) {
            $too_few = (bool) $item[0];

            /*
            $ilDB->manipulate("UPDATE grp_settings".
                " SET cancel_end_noti = ".$ilDB->quote($now, "integer").
                " WHERE obj_id = ".$ilDB->quote($obj_id, "integer"));
            */

            if ($too_few) {
                // not enough members: notifiy group admins
                foreach ($item[1] as $reci_id) {
                    $a_recipients_map[$reci_id][] = array("grp", $obj_id, $item[0]);
                }
            } else {
                // enough members: notify members?

                // :TODO: ?
            }
        }
    }

    protected function sendMessage(int $a_reci_id, array $a_items)
    {
        $ntf = new ilSystemNotification();
        $ntf->setLangModules(array("crs"));

        // #17097
        $ntf->setReasonLangId("mem_cron_min_members_reason");

        // user specific language
        $lng = $ntf->getUserLanguage($a_reci_id);

        $list = array();
        foreach ($a_items as $item) {
            $obj_type = $item[0];
            $obj_id = $item[1];
            $refs = ilObject::_getAllReferences($obj_id);
            $ref_id = array_pop($ref_id);
            $title = ilObject::_lookupTitle($obj_id);
            $url = ilLink::_getLink($ref_id, $obj_type);

            $list[] = $title . "\n" . $url . "\n";
        }
        $list = implode($ntf->getBlockBorder(), $list);

        $ntf->addAdditionalInfo("mem_cron_min_members_intro", $list, true);
        $ntf->addAdditionalInfo("mem_cron_min_members_task", "");

        $mail = new ilMail(ANONYMOUS_USER_ID);
        $mail->enqueue(
            ilObjUser::_lookupLogin($a_reci_id),
            '',
            '',
            $lng->txt("mem_cron_min_members_subject"),
            $ntf->composeAndGetMessage($a_reci_id, null, "read", true),
            []
        );
    }
}
