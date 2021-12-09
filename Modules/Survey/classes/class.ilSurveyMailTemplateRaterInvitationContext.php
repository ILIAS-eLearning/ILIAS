<?php declare(strict_types=1);

/**
 * This file is part of ILIAS, a powerful learning management system
 * published by ILIAS open source e-Learning e.V.
 * ILIAS is licensed with the GPL-3.0,
 * see https://www.gnu.org/licenses/gpl-3.0.en.html
 * You should have received a copy of said license along with the
 * source code, too.
 * If this is not the case or you just want to try ILIAS, you'll find
 * us at:
 * https://www.ilias.de
 * https://github.com/ILIAS-eLearning
 */

use OrgUnit\PublicApi\OrgUnitUserService;

/**
 * Invitation for raters
 *
 * @author Alexander Killing <killing@leifos.de>
 */
class ilSurveyMailTemplateRaterInvitationContext extends ilMailTemplateContext
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

    const ID = 'svy_rater_inv';

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

        return $lng->txt('svy_mail_context_rater_invitation_title');
    }

    /**
     * @return string
     */
    public function getDescription() : string
    {
        $lng = $this->lng;

        $lng->loadLanguageModule('survey');

        return $lng->txt('svy_mail_context_rater_invitation_info');
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
            'label' => $lng->txt('svy_mail_context_rater_invitation_survey_title')
        );

        $placeholders['svy_link'] = array(
            'placeholder' => 'SURVEY_LINK',
            'label' => $lng->txt('perma_link')
        );

        $placeholders['svy_ext_rater_firstname'] = array(
            'placeholder' => 'EXTERNAL_RATER_FIRSTNAME',
            'label' => $lng->txt('svy_ext_rater_firstname')
        );

        $placeholders['svy_ext_rater_lastname'] = array(
            'placeholder' => 'EXTERNAL_RATER_LASTNAME',
            'label' => $lng->txt('svy_ext_rater_lastname')
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

        $svy = new ilObjSurvey($context_parameters['ref_id']);
        $raters = $svy->getRatersData($context_parameters['appr_id']);
        $current_rater = null;
        foreach ($raters as $rater) {
            if ($rater["user_id"] == $context_parameters['rater_id']) {
                $current_rater = $rater;
            }
        }

        switch ($placeholder_id) {
            case 'svy_title':
                return (string) $ilObjDataCache->lookupTitle($ilObjDataCache->lookupObjId($context_parameters['ref_id']));

            case 'svy_link':
                $svy = new ilObjSurvey($context_parameters['ref_id']);
                $raters = $svy->getRatersData($context_parameters['appr_id']);
                $href = ilLink::_getLink($context_parameters['ref_id'], 'svy');
                if (isset($current_rater["href"]) && $current_rater["href"] != "") {
                    $href = $current_rater["href"];
                }
                return $href;

            case 'svy_ext_rater_firstname':
                return $current_rater["firstname"] ?? "";

            case 'svy_ext_rater_lastname':
                return $current_rater["lastname"] ?? "";
        }

        return '';
    }
}
