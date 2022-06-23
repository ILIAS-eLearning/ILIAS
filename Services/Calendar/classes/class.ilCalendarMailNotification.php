<?php declare(strict_types=1);
/*
    +-----------------------------------------------------------------------------+
    | ILIAS open source                                                           |
    +-----------------------------------------------------------------------------+
    | Copyright (c) 1998-2008 ILIAS open source, University of Cologne            |
    |                                                                             |
    | This program is free software; you can redistribute it and/or               |
    | modify it under the terms of the GNU General Public License                 |
    | as published by the Free Software Foundation; either version 2              |
    | of the License, or (at your option) any later version.                      |
    |                                                                             |
    | This program is distributed in the hope that it will be useful,             |
    | but WITHOUT ANY WARRANTY; without even the implied warranty of              |
    | MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the               |
    | GNU General Public License for more details.                                |
    |                                                                             |
    | You should have received a copy of the GNU General Public License           |
    | along with this program; if not, write to the Free Software                 |
    | Foundation, Inc., 59 Temple Place - Suite 330, Boston, MA  02111-1307, USA. |
    +-----------------------------------------------------------------------------+
*/

/**
 * Distributes calendar mail notifications
 * @author  Stefan Meyer <smeyer.ilias@gmx.de>
 * @ingroup ServicesCalendar
 */
class ilCalendarMailNotification extends ilMailNotification
{
    public const TYPE_GRP_NOTIFICATION = 1;
    public const TYPE_GRP_NEW_NOTIFICATION = 2;
    public const TYPE_CRS_NOTIFICATION = 3;
    public const TYPE_CRS_NEW_NOTIFICATION = 4;
    public const TYPE_BOOKING_CONFIRMATION = 5;
    public const TYPE_BOOKING_CANCELLATION = 6;
    public const TYPE_USER = 7;
    public const TYPE_USER_ANONYMOUS = 8;
    public const TYPE_BOOKING_REMINDER = 9;

    private ?int $appointment_id = null;
    private ?ilCalendarEntry $appointment = null;

    protected ilLanguage $lng;
    protected ilRbacReview $rbacreview;

    public function __construct(bool $a_is_personal_workspace = false)
    {
        global $DIC;

        parent::__construct($a_is_personal_workspace);
        $this->lng = $DIC->language();
        $this->rbacreview = $DIC->rbac()->review();
    }

    public function setAppointmentId(int $a_id) : void
    {
        $this->appointment_id = $a_id;
        $this->appointment = new ilCalendarEntry($this->getAppointmentId());
    }

    public function getAppointment() : ?ilCalendarEntry
    {
        return $this->appointment;
    }

    public function getAppointmentId() : ?int
    {
        return $this->appointment_id;
    }

    public function appendAppointmentDetails() : void
    {
        $app = new ilCalendarEntry($this->getAppointmentId());
        $this->appendBody($app->appointmentToMailString($this->getLanguage()));
    }

    public function send() : void
    {
        switch ($this->getType()) {
            case self::TYPE_USER:
                $rcps = $this->getRecipients();
                $rcp = array_pop($rcps);
                $this->initLanguage($rcp);
                $this->getLanguage()->loadLanguageModule('dateplaner');
                $this->initMail();
                $this->setSubject(
                    sprintf(
                        $this->getLanguageText('cal_mail_notification_subject'),
                        $this->getAppointment()->getTitle()
                    )
                );
                $this->setBody(ilMail::getSalutation($rcp, $this->getLanguage()));
                $this->appendBody("\n\n");
                $this->appendBody(
                    $this->getLanguageText('cal_mail_notification_body')
                );
                $this->appendBody("\n\n");
                $this->appendAppointmentDetails();
                $this->appendBody("\n\n");
                $this->getMail()->appendInstallationSignature(true);
                $this->addAttachment();

                $this->sendMail(
                    $this->getRecipients(),
                    true
                );
                break;

            case self::TYPE_USER_ANONYMOUS:

                $rcps = $this->getRecipients();
                $rcp = array_pop($rcps);

                $this->setLanguage(ilLanguageFactory::_getLanguage($this->lng->getDefaultLanguage()));
                $this->getLanguage()->loadLanguageModule('dateplaner');
                $this->getLanguage()->loadLanguageModule('mail');
                $this->initMail();
                $this->setSubject(
                    sprintf(
                        $this->getLanguageText('cal_mail_notification_subject'),
                        $this->getAppointment()->getTitle()
                    )
                );
                $this->setBody(ilMail::getSalutation(0, $this->getLanguage()));
                $this->appendBody("\n\n");
                $this->appendBody(
                    $this->getLanguageText('cal_mail_notification_body')
                );
                $this->appendBody("\n\n");
                $this->appendAppointmentDetails();
                $this->appendBody("\n\n");
                $this->getMail()->appendInstallationSignature(true);
                $this->addAttachment();

                $this->sendMail(
                    $this->getRecipients(),
                    false
                );
                break;

            case self::TYPE_GRP_NEW_NOTIFICATION:

                $this->setLanguage(ilLanguageFactory::_getLanguage($this->lng->getDefaultLanguage()));
                $this->getLanguage()->loadLanguageModule('grp');
                $this->getLanguage()->loadLanguageModule('dateplaner');
                $this->initMail();
                $this->setSubject(
                    sprintf($this->getLanguageText('cal_grp_new_notification_sub'), $this->getObjectTitle(true))
                );
                $this->setBody($this->getLanguageText('grp_notification_salutation'));
                $this->appendBody("\n\n");
                $this->appendBody(
                    sprintf($this->getLanguageText('cal_grp_new_notification_body'), $this->getObjectTitle(true))
                );
                $this->appendBody("\n\n");
                $this->appendBody($this->getLanguageText('grp_mail_permanent_link'));
                $this->appendBody("\n\n");

                $this->appendAppointmentDetails();

                $this->appendBody("\n\n");
                $this->appendBody($this->createPermanentLink());
                $this->getMail()->appendInstallationSignature(true);

                $this->addAttachment();

                $this->sendMail(
                    array('#il_grp_admin_' . $this->getRefId(), '#il_grp_member_' . $this->getRefId()),
                    false
                );
                break;

            case self::TYPE_GRP_NOTIFICATION:

                $this->setLanguage(ilLanguageFactory::_getLanguage($this->lng->getDefaultLanguage()));
                $this->getLanguage()->loadLanguageModule('grp');
                $this->getLanguage()->loadLanguageModule('dateplaner');
                $this->initMail();
                $this->setSubject(
                    sprintf($this->getLanguageText('cal_grp_notification_sub'), $this->getObjectTitle(true))
                );
                $this->setBody($this->getLanguageText('grp_notification_salutation'));
                $this->appendBody("\n\n");
                $this->appendBody(
                    sprintf($this->getLanguageText('cal_grp_notification_body'), $this->getObjectTitle(true))
                );
                $this->appendBody("\n\n");

                $this->appendAppointmentDetails();

                $this->appendBody("\n\n");
                $this->appendBody($this->getLanguageText('grp_mail_permanent_link'));
                $this->appendBody("\n\n");
                $this->appendBody($this->createPermanentLink());
                $this->getMail()->appendInstallationSignature(true);

                $this->addAttachment();

                $this->sendMail(
                    array('#il_grp_admin_' . $this->getRefId(), '#il_grp_member_' . $this->getRefId()),
                    false
                );
                break;

            case self::TYPE_CRS_NEW_NOTIFICATION:

                $this->setLanguage(ilLanguageFactory::_getLanguage($this->lng->getDefaultLanguage()));
                $this->getLanguage()->loadLanguageModule('crs');
                $this->getLanguage()->loadLanguageModule('dateplaner');
                $this->initMail();
                $this->setSubject(
                    sprintf($this->getLanguageText('cal_crs_new_notification_sub'), $this->getObjectTitle(true))
                );
                $this->setBody($this->getLanguageText('crs_notification_salutation'));
                $this->appendBody("\n\n");
                $this->appendBody(
                    sprintf($this->getLanguageText('cal_crs_new_notification_body'), $this->getObjectTitle(true))
                );
                $this->appendBody("\n\n");
                $this->appendBody($this->getLanguageText('crs_mail_permanent_link'));
                $this->appendBody("\n\n");
                $this->appendBody($this->createPermanentLink());
                $this->appendBody("\n\n");
                $this->appendAppointmentDetails();

                $this->getMail()->appendInstallationSignature(true);

                $this->addAttachment();

                $this->sendMail(array('#il_crs_admin_' . $this->getRefId(),
                                      '#il_crs_tutor_' . $this->getRefId(),
                                      '#il_crs_member_' . $this->getRefId()
                ), false);
                break;

            case self::TYPE_CRS_NOTIFICATION:

                $this->setLanguage(ilLanguageFactory::_getLanguage($this->lng->getDefaultLanguage()));
                $this->getLanguage()->loadLanguageModule('crs');
                $this->getLanguage()->loadLanguageModule('dateplaner');
                $this->initMail();
                $this->setSubject(
                    sprintf($this->getLanguageText('cal_crs_notification_sub'), $this->getObjectTitle(true))
                );
                $this->setBody($this->getLanguageText('crs_notification_salutation'));
                $this->appendBody("\n\n");
                $this->appendBody(
                    sprintf($this->getLanguageText('cal_crs_notification_body'), $this->getObjectTitle(true))
                );
                $this->appendBody("\n\n");

                $this->appendAppointmentDetails();

                $this->appendBody("\n\n");
                $this->appendBody($this->getLanguageText('crs_mail_permanent_link'));
                $this->appendBody("\n\n");
                $this->appendBody($this->createPermanentLink());
                $this->getMail()->appendInstallationSignature(true);

                $this->addAttachment();

                $this->sendMail(array('#il_crs_admin_' . $this->getRefId(),
                                      '#il_crs_tutor_' . $this->getRefId(),
                                      '#il_crs_member_' . $this->getRefId()
                ), false);
                break;

            case self::TYPE_BOOKING_CONFIRMATION:

                $rcps = $this->getRecipients();
                $user_id = array_pop($rcps);
                $entry = new ilCalendarEntry($this->getAppointmentId());
                $booking = new ilBookingEntry($entry->getContextId());

                $this->initLanguage($user_id);
                $this->getLanguage()->loadLanguageModule('dateplaner');
                $this->initMail();
                $this->setSubject(
                    sprintf($this->getLanguageText('cal_booking_confirmation_subject'), $entry->getTitle())
                );
                $this->setBody(ilMail::getSalutation($user_id, $this->getLanguage()));
                $this->appendBody("\n\n");
                $this->appendBody(
                    sprintf(
                        $this->getLanguageText('cal_booking_confirmation_body'),
                        ilObjUser::_lookupFullname($booking->getObjId())
                    )
                );
                $this->appendBody("\n\n");
                $this->appendAppointmentDetails();

                /*
                $this->appendBody("\n\n");
                $this->appendBody($this->getLanguageText('cal_booking_confirmation_link'));
                $this->appendBody("\n\n");
                $this->appendBody($this->createPermanentLink());
                 */
                $this->getMail()->appendInstallationSignature(true);

                $this->sendMail(array($user_id), true);

                $this->appendBody("\n\n");
                $this->appendBody($this->getLanguageText('cal_booking_confirmation_user') . "\n");
                $this->appendBody(ilObjUser::_lookupFullname($user_id));

                $this->sendMail(array($booking->getObjId()), true);
                break;

            case self::TYPE_BOOKING_CANCELLATION:

                $rcps = $this->getRecipients();
                $user_id = array_pop($rcps);
                $entry = new ilCalendarEntry($this->getAppointmentId());
                $booking = new ilBookingEntry($entry->getContextId());

                $rcps = $this->getRecipients();
                $user_id = array_pop($rcps);
                $this->initLanguage($user_id);
                $this->getLanguage()->loadLanguageModule('dateplaner');
                $this->initMail();
                $this->setSubject(
                    sprintf($this->getLanguageText('cal_booking_cancellation_subject'), $entry->getTitle())
                );
                $this->setBody(ilMail::getSalutation($user_id, $this->getLanguage()));
                $this->appendBody("\n\n");
                $this->appendBody(
                    sprintf(
                        $this->getLanguageText('cal_booking_cancellation_body'),
                        ilObjUser::_lookupFullname($booking->getObjId())
                    )
                );
                $this->appendBody("\n\n");

                $this->appendAppointmentDetails();

                $this->getMail()->appendInstallationSignature(true);

                $this->sendMail(array($user_id), true);

                $this->appendBody("\n\n");
                $this->appendBody($this->getLanguageText('cal_booking_cancellation_user') . "\n");
                $this->appendBody(ilObjUser::_lookupFullname($user_id));

                $this->sendMail(array($booking->getObjId()), true);
                break;

            case ilCalendarMailNotification::TYPE_BOOKING_REMINDER:

                $rcps = $this->getRecipients();
                $user_id = array_pop($rcps);

                $entry = new ilCalendarEntry($this->getAppointmentId());
                $booking = new ilBookingEntry($entry->getContextId());

                $this->initLanguage($user_id);
                $this->getLanguage()->loadLanguageModule('dateplaner');
                $this->initMail();
                $this->setSubject(
                    sprintf($this->getLanguageText('cal_ch_booking_reminder_subject'), $entry->getTitle())
                );
                $this->setBody(ilMail::getSalutation($user_id, $this->getLanguage()));
                $this->appendBody("\n\n");
                $this->appendBody(
                    sprintf(
                        $this->getLanguageText('cal_ch_booking_reminder_body'),
                        ilObjUser::_lookupFullname($booking->getObjId())
                    )
                );
                $this->appendBody("\n\n");
                $this->appendAppointmentDetails();
                $this->getMail()->appendInstallationSignature(true);
                $this->sendMail(array($user_id), true);
                break;
        }

        $this->deleteAttachments();
    }

    protected function addAttachment() : void
    {
        $export = new ilCalendarExport();
        $export->setExportType(ilCalendarExport::EXPORT_APPOINTMENTS);
        $export->setAppointments(array($this->getAppointmentId()));
        $export->export();

        $attachment = new ilFileDataMail($this->getSender());
        $attachment->storeAsAttachment(
            'appointment.ics',
            $export->getExportString()
        );

        $this->setAttachments(
            array(
                'appointment.ics'
            )
        );
    }

    protected function deleteAttachments() : void
    {
        $attachment = new ilFileDataMail($this->getSender());
        $attachment->unlinkFiles($this->getAttachments());
    }
}
