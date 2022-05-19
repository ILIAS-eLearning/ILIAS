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

use OrgUnit\PublicApi\OrgUnitUserService;

/**
 * Handles survey reminder mail placeholders
 *
 * @author Jörg Lützenkirchen <luetzenkirchen@leifos.com>
 */
class ilSurveyMailTemplateReminderContext extends ilMailTemplateContext
{
    protected ilLanguage $lng;
    protected ilObjectDataCache $obj_data_cache;

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

    public const ID = 'svy_context_rmd';

    public function getId() : string
    {
        return self::ID;
    }

    public function getTitle() : string
    {
        $lng = $this->lng;

        $lng->loadLanguageModule('survey');

        return $lng->txt('svy_mail_context_reminder_title');
    }

    public function getDescription() : string
    {
        $lng = $this->lng;

        $lng->loadLanguageModule('survey');

        return $lng->txt('svy_mail_context_reminder_info');
    }

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

        if ('svy_title' === $placeholder_id) {
            return $ilObjDataCache->lookupTitle($ilObjDataCache->lookupObjId((int) $context_parameters['ref_id']));
        }

        if ('svy_link' === $placeholder_id) {
            return ilLink::_getLink($context_parameters['ref_id'], 'svy');
        }

        return '';
    }
}
