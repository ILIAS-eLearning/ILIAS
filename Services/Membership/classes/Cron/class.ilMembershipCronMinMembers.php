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
 *********************************************************************/
 
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

        if (count($recipients_map)) {
            foreach ($recipients_map as $reci_id => $items) {
                $this->sendMessage($reci_id, $items);
            }

            $status = ilCronJobResult::STATUS_OK;
            $message = count($recipients_map) . " notifications sent";
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
            

            if ($too_few) {
                // not enough members: notifiy course admins
                foreach ($item[1] as $reci_id) {
                    $a_recipients_map[$reci_id][] = array("crs", $obj_id, $item[0]);
                }
            }
        }
    }

    protected function getGroups(array &$a_recipients_map) : void
    {
        foreach (ilObjGroup::findGroupsWithNotEnoughMembers() as $obj_id => $item) {
            $too_few = (bool) $item[0];
            

            if ($too_few) {
                // not enough members: notifiy group admins
                foreach ($item[1] as $reci_id) {
                    $a_recipients_map[$reci_id][] = array("grp", $obj_id, $item[0]);
                }
            }
        }
    }

    protected function sendMessage(int $a_reci_id, array $a_items) : void
    {
        $ntf = new ilSystemNotification();
        $ntf->setLangModules(array("crs"));

        // #17097
        $ntf->setReasonLangId("mem_cron_min_members_reason");

        // user specific language
        $lng = $ntf->getUserLanguage($a_reci_id);

        $list = array();
        foreach ($a_items as $item) {
            $obj_type = (string) $item[0];
            $obj_id = (int) $item[1];
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
