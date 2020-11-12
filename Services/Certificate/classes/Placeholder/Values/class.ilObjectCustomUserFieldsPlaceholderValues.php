<?php
/* Copyright (c) 1998-2019 ILIAS open source, Extended GPL, see docs/LICENSE */

/**
 * @author  Niels Theen <ntheen@databay.de>
 */
class ilObjectCustomUserFieldsPlaceholderValues implements ilCertificatePlaceholderValues
{
    /**
     * @var array
     */
    private $placeholder;

    /**
     * @var ilCertificateObjectHelper|null
     */
    private $objectHelper;

    /**
     * @var ilCertificateUtilHelper
     */
    private $ilUtilHelper;

    /**
     * @param ilCertificateObjectHelper|null $objectHelper
     * @param ilCertificateUtilHelper|null $ilUtilHelper
     */
    public function __construct(
        ilCertificateObjectHelper $objectHelper = null,
        ilCertificateUtilHelper $ilUtilHelper = null
    ) {
        $this->placeholder = array();

        if (null === $objectHelper) {
            $objectHelper = new ilCertificateObjectHelper();
        }
        $this->objectHelper = $objectHelper;

        if (null === $ilUtilHelper) {
            $ilUtilHelper = new ilCertificateUtilHelper();
        }
        $this->ilUtilHelper = $ilUtilHelper;
    }

    /**
     * This method MUST return an array that contains the
     * actual data for the given user of the given object.
     *
     * ilInvalidCertificateException MUST be thrown if the
     * data could not be determined or the user did NOT
     * achieve the certificate.
     *
     * @param int $user_id
     * @param int $obj_id
     * @throws ilInvalidCertificateException
     * @return array - [PLACEHOLDER] => 'actual value'
     * @throws ilException
     */
    public function getPlaceholderValues(int $user_id, int $obj_id) : array
    {
        /** @var ilObjUser $user */
        $user = $this->objectHelper->getInstanceByObjId($user_id);
        if (!$user instanceof ilObjUser) {
            throw new ilException('The entered id: ' . $user_id . ' is not an user object');
        }

        $course_defined_fields = ilCourseDefinedFieldDefinition::_getFields($obj_id);
        $field_values = ilCourseUserData::_getValuesByObjId($obj_id);

        $placeholder = array();
        foreach ($course_defined_fields as $key => $field) {
            $field_id = $field->getId();

            $placeholderText = '+' . str_replace(' ', '_', ilStr::strToUpper($field->getName()));

            $placeholder[$placeholderText] = !empty($field_values[$user_id][$field_id]) ? $field_values[$user_id][$field_id] : "";
        }

        return $placeholder;
    }

    /**
     * This method is different then the 'getPlaceholderValues' method, this
     * method is used to create a placeholder value array containing dummy values
     * that is used to create a preview certificate.
     *
     * @param int $user_id
     * @param int $obj_id
     * @return array - [PLACEHOLDER] => 'dummy value'
     * @throws ilException
     * @throws ilInvalidCertificateException
     */
    public function getPlaceholderValuesForPreview(int $user_id, int $obj_id) : array
    {
        global $DIC;

        $lng = $DIC->language();

        $course_defined_fields = ilCourseDefinedFieldDefinition::_getFields($obj_id);

        $placeholder = array();
        foreach ($course_defined_fields as $key => $field) {
            $placeholderText = '+' . str_replace(' ', '_', ilStr::strToUpper($field->getName()));

            $placeholder[$placeholderText] = $placeholderText . '_' . ilStr::strToUpper($lng->txt('value'));
        }

        return $placeholder;
    }
}
