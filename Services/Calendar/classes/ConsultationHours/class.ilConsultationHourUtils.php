<?php declare(strict_types=1);

/* Copyright (c) 1998-2009 ILIAS open source, Extended GPL, see docs/LICENSE */

/**
 * @author Stefan Meyer <smeyer.ilias@gmx.de>
 */
class ilConsultationHourUtils
{
    public static function getConsultationHourLinksForRepositoryObject(
        int $ref_id,
        int $current_user_id,
        array $ctrl_class_structure
    ) : array {
        global $DIC;

        $ctrl = $DIC->ctrl();
        $lng = $DIC->language();
        $logger = $DIC->logger()->cal();

        $obj_id = \ilObject::_lookupObjId($ref_id);
        $participants = \ilParticipants::getInstance($ref_id);
        $candidates = array_unique(array_merge(
            $participants->getAdmins(),
            $participants->getTutors()
        ));
        $users = \ilBookingEntry::lookupBookableUsersForObject([$obj_id], $candidates);
        $now = new \ilDateTime(time(), IL_CAL_UNIX);
        $links = [];
        foreach ($users as $user_id) {
            $next_entry = null;
            $appointments = \ilConsultationHourAppointments::getAppointments($user_id);
            foreach ($appointments as $entry) {
                // find next entry
                if (ilDateTime::_before($entry->getStart(), $now, IL_CAL_DAY)) {
                    continue;
                }
                $booking_entry = new ilBookingEntry($entry->getContextId());
                if (!in_array($obj_id, $booking_entry->getTargetObjIds())) {
                    continue;
                }
                if (!$booking_entry->isAppointmentBookableForUser($entry->getEntryId(), $current_user_id)) {
                    continue;
                }
                $next_entry = $entry;
                break;
            }

            $ctrl->setParameterByClass(end($ctrl_class_structure), 'ch_user_id', $user_id);
            if ($next_entry instanceof \ilCalendarEntry) {
                $ctrl->setParameterByClass(
                    end($ctrl_class_structure),
                    'seed',
                    $next_entry->getStart()->get(IL_CAL_DATE)
                );
            }
            $current_link = [
                'link' => $ctrl->getLinkTargetByClass($ctrl_class_structure, 'selectCHCalendarOfUser'),
                'txt' => str_replace(
                    "%1",
                    ilObjUser::_lookupFullname($user_id),
                    $lng->txt("cal_consultation_hours_for_user")
                )
            ];
            $links[] = $current_link;
        }
        // Reset control structure links
        $ctrl->setParameterByClass(end($ctrl_class_structure), 'seed', '');
        $ctrl->setParameterByClass(end($ctrl_class_structure), 'ch_user_id', '');
        return $links;
    }

    /**
     * @return int[]
     */
    public static function findCalendarAppointmentsForBooking(
        ilBookingEntry $booking,
        ilDateTime $start,
        ilDateTime $end
    ) : array {
        global $DIC;

        $db = $DIC->database();

        $query = 'select ce.cal_id from cal_entries ce ' .
            'join cal_cat_assignments cca on ce.cal_id = cca.cal_id ' .
            'join cal_categories cc on cca.cat_id = cc.cat_id ' .
            'where context_id = ' . $db->quote($booking->getId(), 'integer') . ' ' .
            'and starta = ' . $db->quote(
                $start->get(IL_CAL_DATETIME, '', \ilTimeZone::UTC),
                \ilDBConstants::T_TIMESTAMP
            ) . ' ' .
            'and enda = ' . $db->quote(
                $end->get(IL_CAL_DATETIME, '', \ilTimeZone::UTC),
                \ilDBConstants::T_TIMESTAMP
            ) . ' ' .
            'and type = ' . $db->quote(\ilCalendarCategory::TYPE_CH, 'integer');
        $res = $db->query($query);

        $calendar_apppointments = [];
        while ($row = $res->fetchRow(\ilDBConstants::FETCHMODE_OBJECT)) {
            $calendar_apppointments[] = (int) $row->cal_id;
        }
        return $calendar_apppointments;
    }

    /**
     * Book an appointment. All checks (assignment possible, max booking) must be done before
     * @param int $a_usr_id
     * @param int $a_app_id
     * @return bool
     */
    public static function bookAppointment(int $a_usr_id, int $a_app_id) : bool
    {
        global $DIC;

        $lng = $DIC->language();

        // Create new default consultation hour calendar
        $cal_lang = ilLanguageFactory::_getLanguage($lng->getDefaultLanguage());
        $cal_lang->loadLanguageModule('dateplaner');

        $ch = ilCalendarUtil::initDefaultCalendarByType(
            ilCalendarCategory::TYPE_CH,
            $a_usr_id,
            $cal_lang->txt('cal_ch_personal_ch'),
            true
        );

        // duplicate appointment
        $app = new ilCalendarEntry($a_app_id);
        $personal_app = clone $app;
        $personal_app->save();

        // assign appointment to category
        $assignment = new ilCalendarCategoryAssignments($personal_app->getEntryId());
        $assignment->addAssignment($ch->getCategoryID());

        // book appointment
        $booking = new ilBookingEntry($app->getContextId());
        $booking->book($app->getEntryId(), $a_usr_id);
        return true;
    }

    /**
     * Cancel a booking
     */
    public static function cancelBooking(int $a_usr_id, int $a_app_id, bool $a_send_notification = true) : bool
    {
        // Delete personal copy of appointment
        $app = new ilCalendarEntry($a_app_id);

        $user_apps = ilConsultationHourAppointments::getAppointmentIds(
            $a_usr_id,
            $app->getContextId(),
            $app->getStart(),
            ilCalendarCategory::TYPE_CH,
            false
        );
        foreach ($user_apps as $uapp_id) {
            $uapp = new ilCalendarEntry($uapp_id);
            $uapp->delete();

            ilCalendarCategoryAssignments::_deleteByAppointmentId($uapp_id);

            break;
        }

        // Delete booking entries
        // Send notification
        $booking = new ilBookingEntry($app->getContextId());
        if ($a_send_notification) {
            $booking->cancelBooking($a_app_id, $a_usr_id);
        } else {
            $booking->deleteBooking($a_app_id, $a_usr_id);
        }
        return true;
    }

    /**
     * Lookup managed users
     * @return int[]
     */
    public static function lookupManagedUsers($a_usr_id) : array
    {
        global $DIC;

        $ilDB = $DIC->database();
        $query = 'SELECT user_id FROM cal_ch_settings ' .
            'WHERE admin_id = ' . $ilDB->quote($a_usr_id, 'integer');
        $res = $ilDB->query($query);

        $users = array();
        while ($row = $res->fetchRow(ilDBConstants::FETCHMODE_OBJECT)) {
            $users[] = (int) $row->user_id;
        }
        return $users;
    }
}
