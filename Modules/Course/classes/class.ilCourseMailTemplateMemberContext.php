<?php

declare(strict_types=0);
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

/**
 * Handles course mail placeholders
 * @author  Stefan Meyer <smeyer.ilias@gmx.de>
 * @package ModulesCourse
 */
class ilCourseMailTemplateMemberContext extends ilMailTemplateContext
{
    public const ID = 'crs_context_member_manual';

    /** @var array */
    protected static array $periodInfoByObjIdCache = [];

    protected ilLanguage $lng;

    public function __construct(
        \OrgUnit\PublicApi\OrgUnitUserService $orgUnitUserService = null,
        ilMailEnvironmentHelper $envHelper = null,
        ilMailUserHelper $usernameHelper = null,
        ilMailLanguageHelper $languageHelper = null
    ) {
        parent::__construct($orgUnitUserService, $envHelper, $usernameHelper, $languageHelper);
    }

    public function getId(): string
    {
        return self::ID;
    }

    public function getTitle(): string
    {
        global $DIC;

        $lng = $DIC['lng'];

        $lng->loadLanguageModule('crs');

        return $lng->txt('crs_mail_context_member_title');
    }

    public function getDescription(): string
    {
        global $DIC;

        $lng = $DIC['lng'];

        $lng->loadLanguageModule('crs');

        return $lng->txt('crs_mail_context_member_info');
    }

    public function getSpecificPlaceholders(): array
    {
        /**
         * @var $lng ilLanguage
         */
        global $DIC;

        $lng = $DIC['lng'];

        $lng->loadLanguageModule('crs');

        $placeholders = array();

        $placeholders['crs_title'] = array(
            'placeholder' => 'COURSE_TITLE',
            'label' => $lng->txt('crs_title')
        );

        $placeholders['crs_period_start'] = array(
            'placeholder' => 'COURSE_PERIOD_START',
            'label' => $lng->txt('crs_period_start_mail_placeholder')
        );

        $placeholders['crs_period_end'] = array(
            'placeholder' => 'COURSE_PERIOD_END',
            'label' => $lng->txt('crs_period_end_mail_placeholder')
        );

        $placeholders['crs_link'] = array(
            'placeholder' => 'COURSE_LINK',
            'label' => $lng->txt('crs_mail_permanent_link')
        );

        return $placeholders;
    }

    private function getCachedPeriodByObjId(int $objId): array
    {
        if (!array_key_exists($objId, self::$periodInfoByObjIdCache)) {
            self::$periodInfoByObjIdCache[$objId] = ilObjCourseAccess::lookupPeriodInfo($objId);
        }

        return self::$periodInfoByObjIdCache[$objId];
    }

    /**
     * {@inheritdoc}
     */
    public function resolveSpecificPlaceholder(
        string $placeholder_id,
        array $context_parameters,
        ilObjUser $recipient = null,
        bool $html_markup = false
    ): string {
        /**
         * @var $ilObjDataCache ilObjectDataCache
         */
        global $DIC;

        $ilObjDataCache = $DIC['ilObjDataCache'];

        if ('crs_title' == $placeholder_id) {
            return $ilObjDataCache->lookupTitle($ilObjDataCache->lookupObjId((int) $context_parameters['ref_id']));
        } else {
            if ('crs_link' == $placeholder_id) {
                return ilLink::_getLink($context_parameters['ref_id'], 'crs');
            } elseif ('crs_period_start' == $placeholder_id) {
                $periodInfo = $this->getCachedPeriodByObjId((int) $ilObjDataCache->lookupObjId((int) $context_parameters['ref_id']));
                if ($periodInfo) {
                    $useRelativeDates = ilDatePresentation::useRelativeDates();
                    ilDatePresentation::setUseRelativeDates(false);
                    $formattedDate = ilDatePresentation::formatDate($periodInfo['crs_end']);
                    ilDatePresentation::setUseRelativeDates($useRelativeDates);

                    return $formattedDate;
                }

                return '';
            } elseif ('crs_period_end' == $placeholder_id) {
                $periodInfo = $this->getCachedPeriodByObjId((int) $ilObjDataCache->lookupObjId((int) $context_parameters['ref_id']));
                if ($periodInfo) {
                    $useRelativeDates = ilDatePresentation::useRelativeDates();
                    ilDatePresentation::setUseRelativeDates(false);
                    $formattedDate = ilDatePresentation::formatDate($periodInfo['crs_end']);
                    ilDatePresentation::setUseRelativeDates($useRelativeDates);

                    return $formattedDate;
                }

                return '';
            }
        }

        return '';
    }
}
