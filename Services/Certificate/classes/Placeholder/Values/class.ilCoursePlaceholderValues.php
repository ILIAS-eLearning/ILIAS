<?php
/* Copyright (c) 1998-2018 ILIAS open source, Extended GPL, see docs/LICENSE */

/**
 * @author  Niels Theen <ntheen@databay.de>
 */
class ilCoursePlaceholderValues implements ilCertificatePlaceholderValues
{
    /**
     * @var ilDefaultPlaceholderValues
     */
    private $defaultPlaceholderValuesObject;

    /**
     * @var ilObjectCustomUserFieldsPlaceholderValues
     */
    private $customUserFieldsPlaceholderValuesObject;

    /**
     * @var ilLanguage|null
     */
    private $language;

    /**
     * @var ilCertificateObjectHelper|null
     */
    private $objectHelper;

    /**
     * @var ilCertificateParticipantsHelper|null
     */
    private $participantsHelper;

    /**
     * @var ilCertificateUtilHelper
     */
    private $ilUtilHelper;

    /**
     * @var ilCertificateDateHelper|null
     */
    private $dateHelper;

    /**
     * @var ilCertificateLPStatusHelper|null
     */
    private $lpStatusHelper;

    /**
     * @param ilObjectCustomUserFieldsPlaceholderValues|null    $customUserFieldsPlaceholderValues
     * @param ilDefaultPlaceholderValues $defaultPlaceholderValues
     * @param ilLanguage|null $language
     * @param ilCertificateObjectHelper|null $objectHelper
     * @param ilCertificateParticipantsHelper|null $participantsHelper
     * @param ilCertificateUtilHelper $ilUtilHelper
     * @param ilCertificateDateHelper|null $dateHelper
     * @param ilCertificateLPStatusHelper|null $lpStatusHelper
     */
    public function __construct(
        ilObjectCustomUserFieldsPlaceholderValues $customUserFieldsPlaceholderValues = null,
        ilDefaultPlaceholderValues $defaultPlaceholderValues = null,
        ilLanguage $language = null,
        ilCertificateObjectHelper $objectHelper = null,
        ilCertificateParticipantsHelper $participantsHelper = null,
        ilCertificateUtilHelper $ilUtilHelper = null,
        ilCertificateDateHelper $dateHelper = null,
        ilCertificateLPStatusHelper $lpStatusHelper = null
    ) {
        if (null === $language) {
            global $DIC;
            $language = $DIC->language();
            $language->loadLanguageModule('certificate');
        }
        $this->language = $language;

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
     * @return bool
     */
    private function hasCompletionDate($possibleDate) : bool
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
     * @param $userId
     * @param $objId
     * @return array - [PLACEHOLDER] => 'actual value'
     * @throws ilException
     */
    public function getPlaceholderValues(int $userId, int $objId) : array
    {
        $courseObject = $this->objectHelper->getInstanceByObjId($objId);

        $placeholders = $this->defaultPlaceholderValuesObject->getPlaceholderValues($userId, $objId);

        $customUserFieldsPlaceholders = $this->customUserFieldsPlaceholderValuesObject->getPlaceholderValues($userId, $objId);

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
     * @param int $userId
     * @param int $objId
     * @return array
     */
    public function getPlaceholderValuesForPreview(int $userId, int $objId) : array
    {
        $placeholders = $this->defaultPlaceholderValuesObject->getPlaceholderValuesForPreview($userId, $objId);

        $customUserFieldsPlaceholders = $this->customUserFieldsPlaceholderValuesObject->getPlaceholderValuesForPreview($userId, $objId);

        $placeholders = array_merge($placeholders, $customUserFieldsPlaceholders);

        $object = $this->objectHelper->getInstanceByObjId($objId);

        $placeholders['COURSE_TITLE'] = ilUtil::prepareFormOutput($object->getTitle());

        return $placeholders;
    }
}
