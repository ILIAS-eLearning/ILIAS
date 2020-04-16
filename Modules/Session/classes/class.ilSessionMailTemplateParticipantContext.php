<?php
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
    public function getId()
    {
        return self::ID;
    }

    /**
     * @return string
     */
    public function getTitle()
    {
        global $DIC;

        $lng = $DIC['lng'];
        
        $lng->loadLanguageModule('sess');
        return $lng->txt('sess_mail_context_participant_title');
    }

    /**
     * @return string
     */
    public function getDescription()
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
    public function getSpecificPlaceholders()
    {
        global $DIC;

        /**
         * @var $lng \ilLanguage
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
    public function resolveSpecificPlaceholder($placeholder_id, array $context_parameters, ilObjUser $recipient = null, $html_markup = false)
    {
        /**
         * @var $ilObjDataCache ilObjectDataCache
         */
        global $DIC;

        $ilObjDataCache = $DIC['ilObjDataCache'];

        if ('crs_title' == $placeholder_id) {
            return $ilObjDataCache->lookupTitle($ilObjDataCache->lookupObjId($context_parameters['ref_id']));
        } elseif ('crs_link' == $placeholder_id) {
            require_once './Services/Link/classes/class.ilLink.php';
            return ilLink::_getLink($context_parameters['ref_id'], 'crs');
        }

        return '';
    }
}
