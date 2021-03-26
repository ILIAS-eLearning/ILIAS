<?php declare(strict_types=1);

/* Copyright (c) 1998-2021 ILIAS open source, GPLv3, see LICENSE */

use OrgUnit\PublicApi\OrgUnitUserService;

/**
 * Handles survey reminder mail placeholders
 *
 * @author Jörg Lützenkirchen <luetzenkirchen@leifos.com>
 */
class ilSurveyMailTemplateReminderContext extends ilMailTemplateContext
{
    /**
     * @var ilLanguage
     */
    protected $lng;

    /**
     * @var ilObjectDataCache
     */
    protected $obj_data_cache;

    public function __construct(
        OrgUnitUserService $orgUnitUserService = null,
        ilMailEnvironmentHelper $envHelper = null,
        ilMailUserHelper $usernameHelper = null,
        ilMailLanguageHelper $languageHelper = null
    ) {
        global $DIC;

        parent::__construct(
            $orgUnitUserService,
            $envHelper,
            $usernameHelper,
            $languageHelper
        );

        $this->lng = $DIC->language();
        if (isset($DIC["ilObjDataCache"])) {
            $this->obj_data_cache = $DIC["ilObjDataCache"];
        }
    }

    const ID = 'svy_context_rmd';

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
        $lng = $this->lng;

        $lng->loadLanguageModule('survey');

        return $lng->txt('svy_mail_context_reminder_title');
    }

    /**
     * @return string
     */
    public function getDescription() : string
    {
        $lng = $this->lng;

        $lng->loadLanguageModule('survey');

        return $lng->txt('svy_mail_context_reminder_info');
    }

    /**
     * Return an array of placeholders
     * @return array
     */
    public function getSpecificPlaceholders() : array
    {
        /**
         * @var $lng ilLanguage
         */
        $lng = $this->lng;

        $lng->loadLanguageModule('survey');

        $placeholders = array();

        $placeholders['svy_title'] = array(
            'placeholder' => 'SURVEY_TITLE',
            'label' => $lng->txt('svy_mail_context_reminder_survey_title')
        );

        $placeholders['svy_link'] = array(
            'placeholder' => 'SURVEY_LINK',
            'label' => $lng->txt('perma_link')
        );

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
        /**
         * @var $ilObjDataCache ilObjectDataCache
         */
        $ilObjDataCache = $this->obj_data_cache;

        if ('svy_title' == $placeholder_id) {
            return $ilObjDataCache->lookupTitle($ilObjDataCache->lookupObjId($context_parameters['ref_id']));
        } else {
            if ('svy_link' == $placeholder_id) {
                return ilLink::_getLink($context_parameters['ref_id'], 'svy');
            }
        }

        return '';
    }
}
