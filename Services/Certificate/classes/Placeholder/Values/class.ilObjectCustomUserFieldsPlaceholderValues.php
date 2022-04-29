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
class ilObjectCustomUserFieldsPlaceholderValues implements ilCertificatePlaceholderValues
{
    private ilCertificateObjectHelper $objectHelper;

    public function __construct(?ilCertificateObjectHelper $objectHelper = null)
    {
        if (null === $objectHelper) {
            $objectHelper = new ilCertificateObjectHelper();
        }
        $this->objectHelper = $objectHelper;
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

        $course_defined_fields = ilCourseDefinedFieldDefinition::_getFields($objId);
        $field_values = ilCourseUserData::_getValuesByObjId($objId);

        $placeholder = [];
        foreach ($course_defined_fields as $key => $field) {
            $field_id = $field->getId();

            $placeholderText = '+' . str_replace(' ', '_', ilStr::strToUpper($field->getName()));

            $placeholder[$placeholderText] = !empty($field_values[$userId][$field_id]) ? $field_values[$userId][$field_id] : "";
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
        global $DIC;

        $lng = $DIC->language();

        $course_defined_fields = ilCourseDefinedFieldDefinition::_getFields($objId);

        $placeholder = [];
        foreach ($course_defined_fields as $key => $field) {
            $placeholderText = '+' . str_replace(' ', '_', ilStr::strToUpper($field->getName()));

            $placeholder[$placeholderText] = $placeholderText . '_' . ilStr::strToUpper($lng->txt('value'));
        }

        return $placeholder;
    }
}
