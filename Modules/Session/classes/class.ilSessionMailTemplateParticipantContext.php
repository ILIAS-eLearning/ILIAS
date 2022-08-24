<?php

declare(strict_types=1);

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
 * Mail context template for mails send via session participants tab
 *
 * @author Stefan Meyer <smeyer.ilias@gmx.de>
 * @package ModulesSession
 */
class ilSessionMailTemplateParticipantContext extends ilMailTemplateContext
{
    protected ilLanguage $lng;
    protected ilObjectDataCache $obj_data_cache;

    public const ID = 'sess_context_participant_manual';

    public function __construct()
    {
        global $DIC;

        $this->lng = $DIC->language();
        $this->obj_data_cache = $DIC['ilObjDataCache'];
    }

    public function getId(): string
    {
        return self::ID;
    }

    public function getTitle(): string
    {
        $lng = $this->lng;

        $lng->loadLanguageModule('sess');
        return $lng->txt('sess_mail_context_participant_title');
    }

    public function getDescription(): string
    {
        $lng = $this->lng;

        $lng->loadLanguageModule('sess');
        return $lng->txt('sess_mail_context_participant_info');
    }

    public function getSpecificPlaceholders(): array
    {
        $lng = $this->lng;

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

    public function resolveSpecificPlaceholder(
        string $placeholder_id,
        array $context_parameters,
        ?ilObjUser $recipient = null,
        bool $html_markup = false
    ): string {
        $ilObjDataCache = $this->obj_data_cache;
        $obj_id = $ilObjDataCache->lookupObjId((int) $context_parameters['ref_id']);
        $sess_data = ilObjSession::lookupSession($obj_id);
        $sess_app = ilSessionAppointment::_lookupAppointment($obj_id);

        switch ($placeholder_id) {
            case 'sess_title':
                return $ilObjDataCache->lookupTitle($obj_id);
            case 'sess_appointment':
                return ilSessionAppointment::_appointmentToString($sess_app['start'], $sess_app['end'], (bool) $sess_app['fullday']);
            case 'sess_location':
                return $sess_data['location'];
            case 'sess_details':
                return $sess_data['details'];
        }

        return '';
    }
}
