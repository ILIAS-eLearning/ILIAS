<?php
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

include_once './Services/Mail/classes/class.ilMailNotification.php';

/**
* Distributes calendar mail notifications
*
* @author Stefan Meyer <smeyer.ilias@gmx.de>
* @version $Id$
*
* @ingroup ServicesCalendar
*/
class ilCalendarMailNotification extends ilMailNotification
{
	const TYPE_GRP_NOTIFICATION = 1;
	const TYPE_GRP_NEW_NOTIFICATION = 2;
	const TYPE_CRS_NOTIFICATION = 3;
	const TYPE_CRS_NEW_NOTIFICATION = 4;
	const TYPE_BOOKING_CONFIRMATION = 5;
	const TYPE_BOOKING_CANCELLATION = 6;
	const TYPE_USER = 7;
	const TYPE_USER_ANONYMOUS = 8;
	const TYPE_BOOKING_REMINDER = 9;
	
	private $appointment_id = null;
	
	/**
	 * Constructor
	 */
	public function __construct()
	{
		
		$this->setSender(ANONYMOUS_USER_ID);
	}
	
	/**
	 * Set calendar appointment id
	 * @param object $a_id
	 * @return 
	 */
	public function setAppointmentId($a_id)
	{
		$this->appointment_id = $a_id;

		include_once './Services/Calendar/classes/class.ilCalendarEntry.php';
		$this->appointment = new ilCalendarEntry($this->getAppointmentId());
	}

	/**
	 * Get appointment
	 * @return ilCalendarEntry
	 */
	public function getAppointment()
	{
		return $this->appointment;
	}
	
	/**
	 * get appointment id
	 * @return 
	 */
	public function getAppointmentId()
	{
		return $this->appointment_id;
	}
	
	public function appendAppointmentDetails()
	{
		include_once './Services/Calendar/classes/class.ilCalendarEntry.php';
		$app = new ilCalendarEntry($this->getAppointmentId());
		$this->appendBody($app->appointmentToMailString($this->getLanguage()));
	}
	
	
	/**
	 * 
	 * @return 
	 */
	public function send()
	{
		global $rbacreview,$lng;
		
		switch($this->getType())
		{
			case self::TYPE_USER:
				$rcp = array_pop($this->getRecipients());
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
					array('system'),
					true
				);
				break;

			case self::TYPE_USER_ANONYMOUS:

				$rcp = array_pop($this->getRecipients());

				$this->setLanguage(ilLanguageFactory::_getLanguage($lng->getDefaultLanguage()));
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
					array('email'),
					false
				);
				break;

			case self::TYPE_GRP_NEW_NOTIFICATION:
				
				$this->setLanguage(ilLanguageFactory::_getLanguage($lng->getDefaultLanguage()));
				$this->getLanguage()->loadLanguageModule('grp');
				$this->getLanguage()->loadLanguageModule('dateplaner');
				$this->initMail();
				$this->setSubject(
					sprintf($this->getLanguageText('cal_grp_new_notification_sub'),$this->getObjectTitle(true))
				);
				$this->setBody($this->getLanguageText('grp_notification_salutation'));
				$this->appendBody("\n\n");
				$this->appendBody(
					sprintf($this->getLanguageText('cal_grp_new_notification_body'),$this->getObjectTitle(true))
				);
				$this->appendBody("\n\n");
				$this->appendBody($this->getLanguageText('grp_mail_permanent_link'));
				$this->appendBody("\n\n");
				
				$this->appendAppointmentDetails();
				
				$this->appendBody("\n\n");
				$this->appendBody($this->createPermanentLink());
				$this->getMail()->appendInstallationSignature(true);

				$this->addAttachment();
										
				$this->sendMail(array('#il_grp_admin_'.$this->getRefId(),'#il_grp_member_'.$this->getRefId()),array('system'),false);
				break;

			case self::TYPE_GRP_NOTIFICATION:
				
				$this->setLanguage(ilLanguageFactory::_getLanguage($lng->getDefaultLanguage()));
				$this->getLanguage()->loadLanguageModule('grp');
				$this->getLanguage()->loadLanguageModule('dateplaner');
				$this->initMail();
				$this->setSubject(
					sprintf($this->getLanguageText('cal_grp_notification_sub'),$this->getObjectTitle(true))
				);
				$this->setBody($this->getLanguageText('grp_notification_salutation'));
				$this->appendBody("\n\n");
				$this->appendBody(
					sprintf($this->getLanguageText('cal_grp_notification_body'),$this->getObjectTitle(true))
				);
				$this->appendBody("\n\n");

				$this->appendAppointmentDetails();

				$this->appendBody("\n\n");
				$this->appendBody($this->getLanguageText('grp_mail_permanent_link'));
				$this->appendBody("\n\n");
				$this->appendBody($this->createPermanentLink());
				$this->getMail()->appendInstallationSignature(true);

				$this->addAttachment();
										
				$this->sendMail(array('#il_grp_admin_'.$this->getRefId(),'#il_grp_member_'.$this->getRefId()),array('system'),false);
				break;

			case self::TYPE_CRS_NEW_NOTIFICATION:
				
				$this->setLanguage(ilLanguageFactory::_getLanguage($lng->getDefaultLanguage()));
				$this->getLanguage()->loadLanguageModule('crs');
				$this->getLanguage()->loadLanguageModule('dateplaner');
				$this->initMail();
				$this->setSubject(
					sprintf($this->getLanguageText('cal_crs_new_notification_sub'),$this->getObjectTitle(true))
				);
				$this->setBody($this->getLanguageText('crs_notification_salutation'));
				$this->appendBody("\n\n");
				$this->appendBody(
					sprintf($this->getLanguageText('cal_crs_new_notification_body'),$this->getObjectTitle(true))
				);
				$this->appendBody("\n\n");
				$this->appendBody($this->getLanguageText('crs_mail_permanent_link'));
				$this->appendBody("\n\n");
				
				$this->appendAppointmentDetails();
				
				$this->appendBody("\n\n");
				$this->appendBody($this->createPermanentLink());
				$this->getMail()->appendInstallationSignature(true);

				$this->addAttachment();
										
				$this->sendMail(array('#il_crs_admin_'.$this->getRefId(),'#il_crs_tutor_'.$this->getRefId(),'#il_crs_member_'.$this->getRefId()),array('system'),false);
				break;

			case self::TYPE_CRS_NOTIFICATION:
				
				$this->setLanguage(ilLanguageFactory::_getLanguage($lng->getDefaultLanguage()));
				$this->getLanguage()->loadLanguageModule('crs');
				$this->getLanguage()->loadLanguageModule('dateplaner');
				$this->initMail();
				$this->setSubject(
					sprintf($this->getLanguageText('cal_crs_notification_sub'),$this->getObjectTitle(true))
				);
				$this->setBody($this->getLanguageText('crs_notification_salutation'));
				$this->appendBody("\n\n");
				$this->appendBody(
					sprintf($this->getLanguageText('cal_crs_notification_body'),$this->getObjectTitle(true))
				);
				$this->appendBody("\n\n");

				$this->appendAppointmentDetails();

				$this->appendBody("\n\n");
				$this->appendBody($this->getLanguageText('crs_mail_permanent_link'));
				$this->appendBody("\n\n");
				$this->appendBody($this->createPermanentLink());
				$this->getMail()->appendInstallationSignature(true);

				$this->addAttachment();
										
				$this->sendMail(array('#il_crs_admin_'.$this->getRefId(),'#il_crs_tutor_'.$this->getRefId(),'#il_crs_member_'.$this->getRefId()),array('system'),false);
				break;

			case self::TYPE_BOOKING_CONFIRMATION:

				$user_id = array_pop($this->getRecipients());
				include_once 'Services/Calendar/classes/class.ilCalendarEntry.php';
				include_once 'Services/Booking/classes/class.ilBookingEntry.php';
				$entry = new ilCalendarEntry($this->getAppointmentId());
				$booking = new ilBookingEntry($entry->getContextId());

				$this->initLanguage($user_id);
				$this->getLanguage()->loadLanguageModule('dateplaner');
				$this->initMail();
				$this->setSubject(
					sprintf($this->getLanguageText('cal_booking_confirmation_subject'),$entry->getTitle())
				);
				$this->setBody(ilMail::getSalutation($user_id,$this->getLanguage()));
				$this->appendBody("\n\n");
				$this->appendBody(
					sprintf($this->getLanguageText('cal_booking_confirmation_body'),ilObjUser::_lookupFullname($booking->getObjId()))
				);
				$this->appendBody("\n\n");

				$this->appendAppointmentDetails($booking);

				/*
				$this->appendBody("\n\n");
				$this->appendBody($this->getLanguageText('cal_booking_confirmation_link'));
				$this->appendBody("\n\n");
				$this->appendBody($this->createPermanentLink());
				 */
				$this->getMail()->appendInstallationSignature(true);

				$this->sendMail(array($user_id),array('system'),true);

				$this->appendBody("\n\n");
				$this->appendBody($this->getLanguageText('cal_booking_confirmation_user')."\n");
				$this->appendBody(ilObjUser::_lookupFullname($user_id));

				$this->sendMail(array($booking->getObjId()),array('system'),true);
				break;

			case self::TYPE_BOOKING_CANCELLATION:

				$user_id = array_pop($this->getRecipients());
				include_once 'Services/Calendar/classes/class.ilCalendarEntry.php';
				include_once 'Services/Booking/classes/class.ilBookingEntry.php';
				$entry = new ilCalendarEntry($this->getAppointmentId());
				$booking = new ilBookingEntry($entry->getContextId());

				$user_id = array_pop($this->getRecipients());
				$this->initLanguage($user_id);
				$this->getLanguage()->loadLanguageModule('dateplaner');
				$this->initMail();
				$this->setSubject(
					sprintf($this->getLanguageText('cal_booking_cancellation_subject'),$entry->getTitle())
				);
				$this->setBody(ilMail::getSalutation($user_id,$this->getLanguage()));
				$this->appendBody("\n\n");
				$this->appendBody(
					sprintf($this->getLanguageText('cal_booking_cancellation_body'),ilObjUser::_lookupFullname($booking->getObjId()))
				);
				$this->appendBody("\n\n");

				$this->appendAppointmentDetails($booking);

				$this->getMail()->appendInstallationSignature(true);

				$this->sendMail(array($user_id),array('system'),true);

				$this->appendBody("\n\n");
				$this->appendBody($this->getLanguageText('cal_booking_cancellation_user')."\n");
				$this->appendBody(ilObjUser::_lookupFullname($user_id));

				$this->sendMail(array($booking->getObjId()),array('system'),true);
				break;
			
			case ilCalendarMailNotification::TYPE_BOOKING_REMINDER:
				
				$user_id = array_pop($this->getRecipients());
				
				include_once 'Services/Calendar/classes/class.ilCalendarEntry.php';
				include_once 'Services/Booking/classes/class.ilBookingEntry.php';
				$entry = new ilCalendarEntry($this->getAppointmentId());
				$booking = new ilBookingEntry($entry->getContextId());

				$this->initLanguage($user_id);
				$this->getLanguage()->loadLanguageModule('dateplaner');
				$this->initMail();
				$this->setSubject(
					sprintf($this->getLanguageText('cal_ch_booking_reminder_subject'),$entry->getTitle())
				);
				$this->setBody(ilMail::getSalutation($user_id,$this->getLanguage()));
				$this->appendBody("\n\n");
				$this->appendBody(
					sprintf($this->getLanguageText('cal_ch_booking_reminder_body'),ilObjUser::_lookupFullname($booking->getObjId()))
				);
				$this->appendBody("\n\n");

				$this->appendAppointmentDetails($booking);

				$this->getMail()->appendInstallationSignature(true);
				$this->sendMail(array($user_id),array('system'),true);
				break;
		}
		
		$this->deleteAttachments();
		
	}

	protected function addAttachment()
	{
		global $ilUser;

		include_once './Services/Calendar/classes/Export/class.ilCalendarExport.php';
		$export = new ilCalendarExport();
		$export->setExportType(ilCalendarExport::EXPORT_APPOINTMENTS);
		$export->setAppointments(array($this->getAppointmentId()));
		$export->export();

		include_once './Services/Mail/classes/class.ilFileDataMail.php';
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

	/**
	 * Delete attachments
	 */
	protected function deleteAttachments()
	{
		include_once './Services/Mail/classes/class.ilFileDataMail.php';
		$attachment = new ilFileDataMail($this->getSender());
		$attachment->unlinkFiles($this->getAttachments());
		
	}
}
?>
