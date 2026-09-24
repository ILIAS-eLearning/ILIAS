<?php

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

declare(strict_types=1);

/**
 * The values of the placeholders of ilLTIToolPlaceholderDescription for a user of an LTI object.
 * The certificate GUI and the certificate cron job (ilCertificateTypeClassMap) create it by this name.
 *
 * @author Saúl Díaz <sdiaz@surlabs.com>
 */
class ilLTIToolPlaceholderValues implements ilCertificatePlaceholderValues
{
    private readonly ilLanguage $language;
    private readonly ilDefaultPlaceholderValues $default_values;
    private readonly ilCertificateObjectHelper $object_helper;
    private readonly ilCertificateUtilHelper $util_helper;
    private readonly ilCertificateLPStatusHelper $lp_status_helper;
    private readonly ilCertificateDateHelper $date_helper;

    public function __construct()
    {
        global $DIC;

        $this->language = $DIC->language();
        $this->language->loadLanguageModule('certificate');
        $this->default_values = new ilDefaultPlaceholderValues();
        $this->object_helper = new ilCertificateObjectHelper();
        $this->util_helper = new ilCertificateUtilHelper();
        $this->lp_status_helper = new ilCertificateLPStatusHelper();
        $this->date_helper = new ilCertificateDateHelper();
    }

    /**
     * @return array
     */
    public function getPlaceholderValues(int $userId, int $objId): array
    {
        $object = $this->object_helper->getInstanceByObjId($objId);
        $placeholders = $this->default_values->getPlaceholderValues($userId, $objId);

        $placeholders['OBJECT_TITLE'] = $this->util_helper->prepareFormOutput($object->getTitle());
        $placeholders['OBJECT_DESCRIPTION'] = $this->util_helper->prepareFormOutput($object->getDescription());
        $placeholders['MASTERY_SCORE'] = $this->util_helper->prepareFormOutput(
            $object instanceof ilObjLTITool ? $this->formatPercentage($object->getMasteryScore()) : ''
        );

        $result = ilLTIToolResult::getByKeys($objId, $userId)?->getResult();
        $placeholders['REACHED_SCORE'] = $this->util_helper->prepareFormOutput(
            $result === null ? '' : $this->formatPercentage($result)
        );

        $placeholders['DATE_COMPLETED'] = '';
        $placeholders['DATETIME_COMPLETED'] = '';
        $completion_date = $this->lp_status_helper->lookupStatusChanged($objId, $userId);
        if ($completion_date !== '') {
            $user = $this->object_helper->getInstanceByObjId($userId);
            $placeholders['DATE_COMPLETED'] = $this->date_helper->formatDate($completion_date, $user);
            $placeholders['DATETIME_COMPLETED'] = $this->date_helper->formatDateTime($completion_date, $user);
        }

        return $placeholders;
    }

    /**
     * @return array
     */
    public function getPlaceholderValuesForPreview(int $userId, int $objId): array
    {
        $placeholders = $this->default_values->getPlaceholderValuesForPreview($userId, $objId);
        foreach ([
            'OBJECT_TITLE' => 'lti_cert_ph_object_title',
            'OBJECT_DESCRIPTION' => 'lti_cert_ph_object_description',
            'MASTERY_SCORE' => 'lti_cert_ph_mastery_score',
            'REACHED_SCORE' => 'lti_cert_ph_reached_score',
        ] as $placeholder => $txt) {
            $placeholders[$placeholder] = $this->util_helper->prepareFormOutput($this->language->txt($txt));
        }

        return $placeholders;
    }

    private function formatPercentage(float $share): string
    {
        return sprintf('%0.2f %%', 100 * $share);
    }
}
