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

use ILIAS\Language\Language;
use ILIAS\User\Context;
use ILIAS\User\Profile\Profile;

/**
 * @author  Niels Theen <ntheen@databay.de>
 */
class ilUserDefinedFieldsPlaceholderValues implements ilCertificatePlaceholderValues
{
    public function __construct(
        private readonly Language $lng,
        private readonly ilCertificateObjectHelper $objectHelper,
        private readonly Profile $user_profile,
        private readonly ilCertificateUtilHelper $ilUtilHelper
    ) {
    }

    /**
     * This method MUST return an array that contains the
     * actual data for the given user of the given object.
     * ilInvalidCertificateException MUST be thrown if the
     * data could not be determined or the user did NOT
     * achieve the certificate.
     * @throws ilInvalidCertificateException
     * @throws ilException
     */
    public function getPlaceholderValues(int $userId, int $objId): array
    {
        /** @var ilObjUser|null $user */
        $user = $this->objectHelper->getInstanceByObjId($userId);
        if (!$user instanceof ilObjUser) {
            throw new ilException('The entered id: ' . $userId . ' is not an user object');
        }

        $fields = $this->user_profile->getVisibleUserDefinedFields(Context::Certificate);

        $placeholder = [];
        foreach ($fields as $field) {
            $placeholderText = '#' . str_replace(' ', '_', ilStr::strToUpper($field->getLabel($this->lng)));
            $placeholder[$placeholderText] = $this->ilUtilHelper->prepareFormOutput(
                $field->retrieveValueFromUser($user)
            );
        }

        return $placeholder;
    }

    /**
     * This method is different then the 'getPlaceholderValues' method, this
     * method is used to create a placeholder value array containing dummy values
     * that is used to create a preview certificate.
     */
    public function getPlaceholderValuesForPreview(int $userId, int $objId): array
    {
        $fields = $this->user_profile->getVisibleUserDefinedFields(Context::Certificate);

        $placeholder = [];
        foreach ($fields as $field) {
            if ($field->isAvailableInCertificates()) {
                $label = $field->getLabel($this->lng);
                $placeholder_text = '#' . str_replace(' ', '_', ilStr::strToUpper($label));
                $placeholder[$placeholder_text] = $label;
            }
        }

        return $placeholder;
    }
}
