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
class ilExercisePlaceholderValues implements ilCertificatePlaceholderValues
{
    private ilLanguage $language;
    private ilDefaultPlaceholderValues $defaultPlaceholderValuesObject;
    private ilCertificateLPMarksHelper $lpMarksHelper;
    private ilCertificateObjectHelper $objectHelper;
    private ilCertificateExerciseMembersHelper $exerciseMembersHelper;
    private ilCertificateLPStatusHelper $lpStatusHelper;
    private ilCertificateUtilHelper $utilHelper;
    private ilCertificateDateHelper $dateHelper;

    public function __construct(
        ?ilDefaultPlaceholderValues $defaultPlaceholderValues = null,
        ?ilLanguage $language = null,
        ?ilCertificateObjectHelper $objectHelper = null,
        ?ilCertificateLPMarksHelper $lpMarksHelper = null,
        ?ilCertificateExerciseMembersHelper $exerciseMembersHelper = null,
        ?ilCertificateLPStatusHelper $lpStatusHelper = null,
        ?ilCertificateUtilHelper $utilHelper = null,
        ?ilCertificateDateHelper $dateHelper = null
    ) {
        if (null === $language) {
            global $DIC;
            $language = $DIC->language();
            $language->loadLanguageModule('certificate');
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
     * ilInvalidCertificateException MUST be thrown if the
     * data could not be determined or the user did NOT
     * achieve the certificate.
     * @param int $userId
     * @param int $objId
     * @return array - [PLACEHOLDER] => 'actual value'
     * @throws ilDatabaseException
     * @throws ilDateTimeException
     * @throws ilException
     * @throws ilObjectNotFoundException
     */
    public function getPlaceholderValues(int $userId, int $objId): array
    {
        $exerciseObject = $this->objectHelper->getInstanceByObjId($objId);

        $mark = $this->lpMarksHelper->lookUpMark($userId, $objId);
        $status = $this->exerciseMembersHelper->lookUpStatus($objId, $userId);

        $completionDate = $this->lpStatusHelper->lookupStatusChanged($objId, $userId);

        $placeHolders = $this->defaultPlaceholderValuesObject->getPlaceholderValues($userId, $objId);

        if ($status !== null) {
            $placeHolders['RESULT_PASSED'] = $this->utilHelper->prepareFormOutput($this->language->txt('exc_' . $status));
        }

        $placeHolders['RESULT_MARK'] = $this->utilHelper->prepareFormOutput($mark);
        $placeHolders['EXERCISE_TITLE'] = $this->utilHelper->prepareFormOutput($exerciseObject->getTitle());
        $placeHolders['DATE_COMPLETED'] = '';
        $placeHolders['DATETIME_COMPLETED'] = '';

        if ($completionDate !== '') {
            $placeHolders['DATE_COMPLETED'] = $this->dateHelper->formatDate($completionDate);
            $placeHolders['DATETIME_COMPLETED'] = $this->dateHelper->formatDateTime($completionDate);
        }

        return $placeHolders;
    }

    /**
     * This method is different then the 'getPlaceholderValues' method, this
     * method is used to create a placeholder value array containing dummy values
     * that is used to create a preview certificate.
     * @param int $userId
     * @param int $objId
     * @return array
     */
    public function getPlaceholderValuesForPreview(int $userId, int $objId): array
    {
        $placeholders = $this->defaultPlaceholderValuesObject->getPlaceholderValuesForPreview($userId, $objId);

        $object = $this->objectHelper->getInstanceByObjId($objId);

        $placeholders['RESULT_PASSED'] = $this->utilHelper->prepareFormOutput($this->language->txt('certificate_var_result_passed'));
        $placeholders['RESULT_MARK'] = $this->utilHelper->prepareFormOutput($this->language->txt('certificate_var_result_mark_short'));
        $placeholders['EXERCISE_TITLE'] = $this->utilHelper->prepareFormOutput($object->getTitle());

        return $placeholders;
    }
}
