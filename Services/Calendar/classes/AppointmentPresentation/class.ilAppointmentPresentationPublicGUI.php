<?php
include_once './Services/Calendar/interfaces/interface.ilCalendarAppointmentPresentation.php';
include_once './Services/Calendar/classes/AppointmentPresentation/class.ilAppointmentPresentationGUI.php';

/**
 * ilAppointmentPresentationPublicGUI class presents modal information for public appointments.
 *
 * @author Jesús López Reyes <lopez@leifos.com>
 * @version $Id$
 *
 * @ilCtrl_IsCalledBy ilAppointmentPresentationPublicGUI: ilCalendarAppointmentPresentationGUI
 *
 * @ingroup ServicesCalendar
 */
class ilAppointmentPresentationPublicGUI extends ilAppointmentPresentationGUI implements ilCalendarAppointmentPresentation
{
    protected $seed;

    /**
     * Get seed date
     */
    public function getSeed()
    {
        return $this->seed;
    }

    public function collectPropertiesAndActions()
    {
        global $DIC;

        $a_app = $this->appointment;

        $cat_info = $this->getCatInfo();

        // event title
        $this->addInfoSection($a_app["event"]->getPresentationTitle());

        // event description
        $this->addEventDescription($a_app);

        // calendar info
        if ($cat_info != null) {
            $this->addCalendarInfo($cat_info);
        }


        $this->addInfoSection($this->lng->txt("cal_app_info"));

        // event location
        $this->addEventLocation($a_app);

        //user notifications
        include_once './Services/Calendar/classes/class.ilCalendarUserNotification.php';

        $notification = new ilCalendarUserNotification($a_app['event']->getEntryId());

        $recipients = $notification->getRecipients();
        if (count($recipients) > 0) {
            $str_notification = "";
            foreach ($recipients as $rcp) {
                switch ($rcp['type']) {
                    case ilCalendarUserNotification::TYPE_USER:
                        $str_notification .= $this->getUserName($rcp['usr_id']) . "<br>";
                        break;
                    case ilCalendarUserNotification::TYPE_EMAIL:
                        $str_notification .= $rcp['email'] . "<br>";
                        break;
                }
            }
            $this->addInfoProperty($this->lng->txt("cal_user_notification"), $str_notification);
        }
    }
}
