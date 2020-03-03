<?php
/* Copyright (c) 1998-2018 ILIAS open source, Extended GPL, see docs/LICENSE */

/**
 * @author  Niels Theen <ntheen@databay.de>
 */
class ilExercisePlaceholderValues implements ilCertificatePlaceholderValues
{
    /**
     * @var ilLanguage|null
     */
    private $language;

    /**
     * @var ilDefaultPlaceholderValues|null
     */
    private $defaultPlaceholderValuesObject;

    /**
     * @var ilCertificateLPMarksHelper|null
     */
    private $lpMarksHelper;

    /**
     * @var ilCertificateObjectHelper|null
     */
    private $objectHelper;

    /**
     * @var ilCertificateExerciseMembersHelper|null
     */
    private $exerciseMembersHelper;

    /**
     * @var ilCertificateLPStatusHelper|null
     */
    private $lpStatusHelper;

    /**
     * @var ilCertificateUtilHelper|null
     */
    private $utilHelper;

    /**
     * @var ilCertificateDateHelper|null
     */
    private $dateHelper;

    /**
     * @param ilDefaultPlaceholderValues|null $defaultPlaceholderValues
     * @param ilLanguage|null $language
     * @param ilCertificateObjectHelper|null $objectHelper
     * @param ilCertificateLPMarksHelper|null $lpMarksHelper
     * @param ilCertificateExerciseMembersHelper|null $exerciseMembersHelper
     * @param ilCertificateLPStatusHelper|null $lpStatusHelper
     * @param ilCertificateUtilHelper|null $utilHelper
     * @param ilCertificateDateHelper|null $dateHelper
     */
    public function __construct(
        ilDefaultPlaceholderValues $defaultPlaceholderValues = null,
        ilLanguage $language = null,
        ilCertificateObjectHelper $objectHelper = null,
        ilCertificateLPMarksHelper $lpMarksHelper = null,
        ilCertificateExerciseMembersHelper $exerciseMembersHelper = null,
        ilCertificateLPStatusHelper $lpStatusHelper = null,
        ilCertificateUtilHelper $utilHelper = null,
        ilCertificateDateHelper $dateHelper = null
    ) {
        if (null === $language) {
            global $DIC;
            $language = $DIC->language();
        }
        $language->loadLanguageModule('exercise');
        $language->loadLanguageModule('exc');

        $this->language = $language;

        if (null === $defaultPlaceholderValues) {
            $defaultPlaceholderValues = new ilDefaultPlaceholderValues();
        }
        $this->defaultPlaceholderValuesObject = $defaultPlaceholderValues;

        if (null === $objectHelper) {
            $objectHelper = new ilCertificateObjectHelper();
        }
        $this->objectHelper = $objectHelper;

        if (null === $lpMarksHelper) {
            $lpMarksHelper = new ilCertificateLPMarksHelper();
        }
        $this->lpMarksHelper = $lpMarksHelper;

        if (null === $exerciseMembersHelper) {
            $exerciseMembersHelper = new ilCertificateExerciseMembersHelper();
        }
        $this->exerciseMembersHelper = $exerciseMembersHelper;

        if (null === $lpStatusHelper) {
            $lpStatusHelper = new ilCertificateLPStatusHelper();
        }
        $this->lpStatusHelper = $lpStatusHelper;

        if (null === $utilHelper) {
            $utilHelper = new ilCertificateUtilHelper();
        }
        $this->utilHelper = $utilHelper;

        if (null === $dateHelper) {
            $dateHelper = new ilCertificateDateHelper();
        }
        $this->dateHelper = $dateHelper;
    }

    /**
     * This method MUST return an array that contains the
     * actual data for the given user of the given object.
     *
     * ilInvalidCertificateException MUST be thrown if the
     * data could not be determined or the user did NOT
     * achieve the certificate.
     *
     * @param $userId
     * @param $objId
     * @return mixed - [PLACEHOLDER] => 'actual value'
     * @throws ilDateTimeException
     * @throws ilException
     */
    public function getPlaceholderValues(int $userId, int $objId) : array
    {
        $exerciseObject = $this->objectHelper->getInstanceByObjId($objId);

        $mark = (string) $this->lpMarksHelper->lookUpMark($userId, $objId);
        $status = $this->exerciseMembersHelper->lookUpStatus($objId, $userId);

        $completionDate = $this->lpStatusHelper->lookupStatusChanged($objId, $userId);

        $placeHolders = $this->defaultPlaceholderValuesObject->getPlaceholderValues($userId, $objId);

        $placeHolders['RESULT_PASSED']      = $this->utilHelper->prepareFormOutput($this->language->txt('exc_' . $status));
        $placeHolders['RESULT_MARK']        = $this->utilHelper->prepareFormOutput($mark);
        $placeHolders['EXERCISE_TITLE']     = $this->utilHelper->prepareFormOutput($exerciseObject->getTitle());
        $placeHolders['DATE_COMPLETED']     = '';
        $placeHolders['DATETIME_COMPLETED'] = '';

        if ($completionDate !== false &&
            $completionDate !== null &&
            $completionDate !== ''
        ) {
            $placeHolders['DATE_COMPLETED']     = $this->dateHelper->formatDate($completionDate);
            $placeHolders['DATETIME_COMPLETED'] = $this->dateHelper->formatDateTime($completionDate);
        }

        return $placeHolders;
    }

    /**
     * This method is different then the 'getPlaceholderValues' method, this
     * method is used to create a placeholder value array containing dummy values
     * that is used to create a preview certificate.
     *
     * @param int $userId
     * @param int $objId
     * @return array
     */
    public function getPlaceholderValuesForPreview(int $userId, int $objId) : array
    {
        $placeholders = $this->defaultPlaceholderValuesObject->getPlaceholderValuesForPreview($userId, $objId);

        $object = $this->objectHelper->getInstanceByObjId($objId);

        $placeholders['RESULT_PASSED'] = $this->utilHelper->prepareFormOutput($this->language->txt('certificate_var_result_passed'));
        $placeholders['RESULT_MARK'] = $this->utilHelper->prepareFormOutput($this->language->txt('certificate_var_result_mark_short'));
        $placeholders['EXERCISE_TITLE'] = $this->utilHelper->prepareFormOutput($object->getTitle());

        return $placeholders;
    }
}
