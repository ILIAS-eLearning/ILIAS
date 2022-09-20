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
 *********************************************************************/

/**
 * @author  Niels Theen <ntheen@databay.de>
 */
class ilCoursePlaceholderValues implements ilCertificatePlaceholderValues
{
    private ilDefaultPlaceholderValues $defaultPlaceholderValuesObject;
    private ilObjectCustomUserFieldsPlaceholderValues $customUserFieldsPlaceholderValuesObject;
    private ilCertificateObjectHelper $objectHelper;
    private ilCertificateParticipantsHelper $participantsHelper;
    private ilCertificateUtilHelper $ilUtilHelper;
    private ilCertificateDateHelper $dateHelper;
    private ilCertificateLPStatusHelper $lpStatusHelper;

    public function __construct(
        ?ilObjectCustomUserFieldsPlaceholderValues $customUserFieldsPlaceholderValues = null,
        ?ilDefaultPlaceholderValues $defaultPlaceholderValues = null,
        ?ilLanguage $language = null,
        ?ilCertificateObjectHelper $objectHelper = null,
        ?ilCertificateParticipantsHelper $participantsHelper = null,
        ?ilCertificateUtilHelper $ilUtilHelper = null,
        ?ilCertificateDateHelper $dateHelper = null,
        ?ilCertificateLPStatusHelper $lpStatusHelper = null
    ) {
        if (null === $language) {
            global $DIC;
            $language = $DIC->language();
            $language->loadLanguageModule('certificate');
        }

        if (null === $defaultPlaceholderValues) {
            $defaultPlaceholderValues = new ilDefaultPlaceholderValues();
        }

        if (null === $customUserFieldsPlaceholderValues) {
            $customUserFieldsPlaceholderValues = new ilObjectCustomUserFieldsPlaceholderValues();
        }

        if (null === $objectHelper) {
            $objectHelper = new ilCertificateObjectHelper();
        }
        $this->objectHelper = $objectHelper;

        if (null === $participantsHelper) {
            $participantsHelper = new ilCertificateParticipantsHelper();
        }
        $this->participantsHelper = $participantsHelper;

        if (null === $ilUtilHelper) {
            $ilUtilHelper = new ilCertificateUtilHelper();
        }
        $this->ilUtilHelper = $ilUtilHelper;

        if (null === $dateHelper) {
            $dateHelper = new ilCertificateDateHelper();
        }
        $this->dateHelper = $dateHelper;

        if (null === $lpStatusHelper) {
            $lpStatusHelper = new ilCertificateLPStatusHelper();
        }
        $this->lpStatusHelper = $lpStatusHelper;

        $this->customUserFieldsPlaceholderValuesObject = $customUserFieldsPlaceholderValues;
        $this->defaultPlaceholderValuesObject = $defaultPlaceholderValues;
    }

    /**
     * @param mixed $possibleDate
     */
    private function hasCompletionDate($possibleDate): bool
    {
        return (
            $possibleDate !== false &&
            $possibleDate !== null &&
            $possibleDate !== ''
        );
    }

    /**
     * This method MUST return an array that contains the
     * actual data for the given user of the given object.
     * ilInvalidCertificateException MUST be thrown if the
     * data could not be determined or the user did NOT
     * achieve the certificate.
     * @throws ilDatabaseException
     * @throws ilDateTimeException
     * @throws ilException
     * @throws ilInvalidCertificateException
     * @throws ilObjectNotFoundException
     */
    public function getPlaceholderValues(int $userId, int $objId): array
    {
        $courseObject = $this->objectHelper->getInstanceByObjId($objId);

        $placeholders = $this->defaultPlaceholderValuesObject->getPlaceholderValues($userId, $objId);

        $customUserFieldsPlaceholders = $this->customUserFieldsPlaceholderValuesObject->getPlaceholderValues(
            $userId,
            $objId
        );

        $placeholders = array_merge($placeholders, $customUserFieldsPlaceholders);

        $completionDate = $this->participantsHelper->getDateTimeOfPassed($objId, $userId);
        if (!$this->hasCompletionDate($completionDate)) {
            $completionDate = $this->lpStatusHelper->lookupStatusChanged($objId, $userId);
        }

        if ($this->hasCompletionDate($completionDate)) {
            $placeholders['DATE_COMPLETED'] = $this->dateHelper->formatDate($completionDate);
            $placeholders['DATETIME_COMPLETED'] = $this->dateHelper->formatDateTime($completionDate);
        }

        $placeholders['COURSE_TITLE'] = $this->ilUtilHelper->prepareFormOutput($courseObject->getTitle());

        return $placeholders;
    }

    /**
     * This method is different then the 'getPlaceholderValues' method, this
     * method is used to create a placeholder value array containing dummy values
     * that is used to create a preview certificate.
     * @throws ilDatabaseException
     * @throws ilDateTimeException
     * @throws ilException
     * @throws ilObjectNotFoundException
     */
    public function getPlaceholderValuesForPreview(int $userId, int $objId): array
    {
        $placeholders = $this->defaultPlaceholderValuesObject->getPlaceholderValuesForPreview($userId, $objId);

        $customUserFieldsPlaceholders = $this->customUserFieldsPlaceholderValuesObject->getPlaceholderValuesForPreview(
            $userId,
            $objId
        );

        $placeholders = array_merge($placeholders, $customUserFieldsPlaceholders);

        $object = $this->objectHelper->getInstanceByObjId($objId);

        $placeholders['COURSE_TITLE'] = ilLegacyFormElementsUtil::prepareFormOutput($object->getTitle());

        return $placeholders;
    }
}
