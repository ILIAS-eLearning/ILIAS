<?php declare(strict_types=1);
/* Copyright (c) 1998-2019 ILIAS open source, Extended GPL, see docs/LICENSE */

/**
 * @author  Niels Theen <ntheen@databay.de>
 */
class ilUserDefinedFieldsPlaceholderValues implements ilCertificatePlaceholderValues
{
    private ilCertificateObjectHelper $objectHelper;
    private ilUserDefinedFields $userDefinedFieldsObject;
    private ilCertificateUtilHelper $ilUtilHelper;

    public function __construct(
        ?ilCertificateObjectHelper $objectHelper = null,
        ?ilUserDefinedFields $userDefinedFieldsObject = null,
        ?ilCertificateUtilHelper $ilUtilHelper = null
    ) {
        if (null === $objectHelper) {
            $objectHelper = new ilCertificateObjectHelper();
        }
        $this->objectHelper = $objectHelper;

        if (null === $userDefinedFieldsObject) {
            $userDefinedFieldsObject = ilUserDefinedFields::_getInstance();
        }
        $this->userDefinedFieldsObject = $userDefinedFieldsObject;

        if (null === $ilUtilHelper) {
            $ilUtilHelper = new ilCertificateUtilHelper();
        }
        $this->ilUtilHelper = $ilUtilHelper;
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
     * @throws ilInvalidCertificateException
     * @throws ilException
     */
    public function getPlaceholderValues(int $userId, int $objId) : array
    {
        /** @var ilObjUser $user */
        $user = $this->objectHelper->getInstanceByObjId($userId);
        if (!$user instanceof ilObjUser) {
            throw new ilException('The entered id: ' . $userId . ' is not an user object');
        }

        $userDefinedFields = $this->userDefinedFieldsObject->getDefinitions();

        $placeholder = [];
        foreach ($userDefinedFields as $field) {
            if ($field['certificate']) {
                $placeholderText = '#' . str_replace(' ', '_', ilStr::strToUpper($field['field_name']));

                $userDefinedData = $user->getUserDefinedData();

                $userDefinedFieldValue = '';
                if (isset($userDefinedData['f_' . $field['field_id']])) {
                    $userDefinedFieldValue = $this->ilUtilHelper->prepareFormOutput($userDefinedData['f_' . $field['field_id']]);
                }

                $placeholder[$placeholderText] = $userDefinedFieldValue;
            }
        }

        return $placeholder;
    }

    /**
     * This method is different then the 'getPlaceholderValues' method, this
     * method is used to create a placeholder value array containing dummy values
     * that is used to create a preview certificate.
     * @param int $userId
     * @param int $objId
     * @return array - [PLACEHOLDER] => 'dummy value'
     */
    public function getPlaceholderValuesForPreview(int $userId, int $objId) : array
    {
        $userDefinedFields = $this->userDefinedFieldsObject->getDefinitions();

        $placeholder = [];
        foreach ($userDefinedFields as $field) {
            if ($field['certificate']) {
                $placeholderText = '#' . str_replace(' ', '_', ilStr::strToUpper($field['field_name']));

                $placeholder[$placeholderText] = $field['field_name'];
            }
        }

        return $placeholder;
    }
}
