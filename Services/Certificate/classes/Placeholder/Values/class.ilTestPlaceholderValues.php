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

/**
 * @author  Niels Theen <ntheen@databay.de>
 */
class ilTestPlaceholderValues implements ilCertificatePlaceholderValues
{
    private ilDefaultPlaceholderValues $defaultPlaceholderValuesObject;
    private ilCertificateObjectHelper $objectHelper;
    private ilCertificateTestObjectHelper $testObjectHelper;
    private ilCertificateUserObjectHelper $userObjectHelper;
    private ilCertificateUtilHelper $utilHelper;
    private ilCertificateLPStatusHelper $lpStatusHelper;
    private ilCertificateDateHelper $dateHelper;
    private ilLanguage $language;

    public function __construct(
        ?ilDefaultPlaceholderValues $defaultPlaceholderValues = null,
        ?ilLanguage $language = null,
        ?ilCertificateObjectHelper $objectHelper = null,
        ?ilCertificateTestObjectHelper $testObjectHelper = null,
        ?ilCertificateUserObjectHelper $userObjectHelper = null,
        ?ilCertificateLPStatusHelper $lpStatusHelper = null,
        ?ilCertificateUtilHelper $utilHelper = null,
        ?ilCertificateDateHelper $dateHelper = null
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
        $this->defaultPlaceholderValuesObject = $defaultPlaceholderValues;

        if (null === $objectHelper) {
            $objectHelper = new ilCertificateObjectHelper();
        }
        $this->objectHelper = $objectHelper;

        if (null === $testObjectHelper) {
            $testObjectHelper = new ilCertificateTestObjectHelper();
        }
        $this->testObjectHelper = $testObjectHelper;

        if (null === $userObjectHelper) {
            $userObjectHelper = new ilCertificateUserObjectHelper();
        }
        $this->userObjectHelper = $userObjectHelper;

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
    public function getPlaceholderValues(int $userId, int $objId) : array
    {
        /** @var ilObjTest $testObject */
        $testObject = $this->objectHelper->getInstanceByObjId($objId);

        $active_id = $testObject->getActiveIdOfUser($userId);
        $pass = (string) $this->testObjectHelper->getResultPass($active_id);

        $result_array = &$testObject->getTestResult($active_id);
        if ($pass !== '') {
            $result_array = &$testObject->getTestResult($active_id, $pass);
        }

        $passed = $this->language->txt('certificate_failed');
        if ($result_array['test']['passed']) {
            $passed = $this->language->txt('certificate_passed');
        }

        $percentage = 0;
        if ($result_array['test']['total_max_points']) {
            $percentage = ($result_array['test']['total_reached_points'] / $result_array['test']['total_max_points']) * 100;
        }

        $mark_obj = $testObject->getMarkSchema()->getMatchingMark($percentage);
        $user_data = $this->userObjectHelper->lookupFields($userId);

        $completionDate = false;
        if ($user_data['usr_id'] > 0) {
            $completionDate = $this->lpStatusHelper->lookupStatusChanged($objId, $userId);
        }

        $placeholders = $this->defaultPlaceholderValuesObject->getPlaceholderValues($userId, $objId);

        $placeholders['RESULT_PASSED'] = $this->utilHelper->prepareFormOutput($passed);
        $placeholders['RESULT_POINTS'] = $this->utilHelper->prepareFormOutput((string) $result_array['test']['total_reached_points']);
        $placeholders['RESULT_PERCENT'] = sprintf('%2.2f', $percentage) . '%';
        $placeholders['MAX_POINTS'] = $this->utilHelper->prepareFormOutput((string) $result_array['test']['total_max_points']);
        $placeholders['RESULT_MARK_SHORT'] = $this->utilHelper->prepareFormOutput($mark_obj->getShortName());
        $placeholders['RESULT_MARK_LONG'] = $this->utilHelper->prepareFormOutput($mark_obj->getOfficialName());
        $placeholders['TEST_TITLE'] = $this->utilHelper->prepareFormOutput($testObject->getTitle());
        $placeholders['DATE_COMPLETED'] = '';
        $placeholders['DATETIME_COMPLETED'] = '';

        if ($completionDate !== false && $completionDate !== '') {
            $placeholders['DATE_COMPLETED'] = $this->dateHelper->formatDate($completionDate);
            $placeholders['DATETIME_COMPLETED'] = $this->dateHelper->formatDateTime($completionDate);
        }

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

        $object = $this->objectHelper->getInstanceByObjId($objId);

        $placeholders['RESULT_PASSED'] = $this->utilHelper->prepareFormOutput($this->language->txt('certificate_var_result_passed'));
        $placeholders['RESULT_POINTS'] = $this->utilHelper->prepareFormOutput($this->language->txt('certificate_var_result_points'));
        $placeholders['RESULT_PERCENT'] = $this->utilHelper->prepareFormOutput($this->language->txt('certificate_var_result_percent'));
        $placeholders['MAX_POINTS'] = $this->utilHelper->prepareFormOutput($this->language->txt('certificate_var_max_points'));
        $placeholders['RESULT_MARK_SHORT'] = $this->utilHelper->prepareFormOutput($this->language->txt('certificate_var_result_mark_short'));
        $placeholders['RESULT_MARK_LONG'] = $this->utilHelper->prepareFormOutput($this->language->txt('certificate_var_result_mark_long'));
        $placeholders['TEST_TITLE'] = $this->utilHelper->prepareFormOutput($object->getTitle());

        return $placeholders;
    }
}
