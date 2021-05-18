<?php declare(strict_types=1);
/* Copyright (c) 1998-2009 ILIAS open source, Extended GPL, see docs/LICENSE */

include_once './Services/Mail/classes/class.ilMailTemplateContext.php';

/**
 * Mail context template for mails send via session participants tab
 *
 * @author Stefan Meyer <smeyer.ilias@gmx.de>
 * @package ModulesSession
 */
class ilSessionMailTemplateParticipantContext extends ilMailTemplateContext
{
    const ID = 'sess_context_participant_manual';

    /**
     * @return string
     */
    public function getId() : string
    {
        return self::ID;
    }

    /**
     * @return string
     */
    public function getTitle() : string
    {
        global $DIC;

        $lng = $DIC['lng'];

        $lng->loadLanguageModule('sess');
        return $lng->txt('sess_mail_context_participant_title');
    }

    /**
     * @return string
     */
    public function getDescription() : string
    {
        global $DIC;

        $lng = $DIC['lng'];
        $lng->loadLanguageModule('sess');

        return $lng->txt('sess_mail_context_participant_info');
    }

    /**
     * Return an array of placeholders
     * @return array
     */
    public function getSpecificPlaceholders() : array
    {
        global $DIC;

        /**
         * @var $lng ilLanguage
         */
        $lng = $DIC['lng'];

        $lng->loadLanguageModule('sess');
        $lng->loadLanguageModule('crs');

        $placeholders = [];
        $placeholders['sess_title'] = [
            'placeholder' => 'SESS_TITLE',
            'label' => $lng->txt('sess_title')
        ];

        $placeholders['sess_appointment'] = [
            'placeholder' => 'SESS_APPOINTMENT',
            'label' => $lng->txt('event_date_time')
        ];


        $placeholders['sess_location'] = [
            'placeholder' => 'SESS_LOCATION',
            'label' => $lng->txt('event_location')
        ];

        $placeholders['sess_details'] = [
            'placeholder' => 'SESS_DETAILS',
            'label' => $lng->txt('event_details_workflow')
        ];


        return $placeholders;
    }

    /**
     * {@inheritdoc}
     */
    public function resolveSpecificPlaceholder(
        string $placeholder_id,
        array $context_parameters,
        ilObjUser $recipient = null,
        bool $html_markup = false
    ) : string {
        global $DIC;

        $ilObjDataCache = $DIC['ilObjDataCache'];
        $obj_id = $ilObjDataCache->lookupObjId($context_parameters['ref_id']);
        $sess_data = ilObjSession::lookupSession($obj_id);
        $sess_app = ilSessionAppointment::_lookupAppointment($obj_id);


        switch ($placeholder_id) {
            case 'sess_title':
                return $ilObjDataCache->lookupTitle($obj_id);
            case 'sess_appointment':
                return ilSessionAppointment::_appointmentToString($sess_app['start'], $sess_app['end'], $sess_app['fullday']);
            case 'sess_location':
                return $sess_data['location'];
            case 'sess_details':
                return $sess_data['details'];
        }

        return '';
    }
}
