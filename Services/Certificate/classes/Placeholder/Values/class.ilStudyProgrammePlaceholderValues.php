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

class ilStudyProgrammePlaceholderValues implements ilCertificatePlaceholderValues
{
    private readonly ilDefaultPlaceholderValues $defaultPlaceholderValuesObject;
    private readonly ilCertificateObjectHelper $objectHelper;

    public function __construct(
        ?ilDefaultPlaceholderValues $defaultPlaceholderValues = null,
        ?ilLanguage $language = null,
        ?ilCertificateObjectHelper $objectHelper = null
    ) {
        if (null === $language) {
            global $DIC;
            $language = $DIC->language();
            $language->loadLanguageModule('certificate');
        }

        if (null === $defaultPlaceholderValues) {
            $defaultPlaceholderValues = new ilDefaultPlaceholderValues();
        }

        if (null === $objectHelper) {
            $objectHelper = new ilCertificateObjectHelper();
        }
        $this->objectHelper = $objectHelper;

        $this->defaultPlaceholderValuesObject = $defaultPlaceholderValues;
    }

    /**
     * This method MUST return an array that contains the
     * actual data for the given user of the given object.
     * ilInvalidCertificateException MUST be thrown if the
     * data could not be determined or the user did NOT
     * achieve the certificate.
     * @throws ilDatabaseException
     * @throws ilException
     * @throws ilObjectNotFoundException
     */
    public function getPlaceholderValues(int $userId, int $objId): array
    {
        $object = $this->objectHelper->getInstanceByObjId($objId);
        $placeholders = $this->defaultPlaceholderValuesObject->getPlaceholderValues($userId, $objId);

        $latest_progress = false;
        $ass_id = $object->getCertificateRelevantAssignmentIds($userId);
        if ($ass_id !== []) {
            $latest_progress = $object->getSpecificAssignment(current($ass_id))->getProgressTree();
        }

        $type = $object->getSubType();
        $placeholders['PRG_TITLE'] = \ilLegacyFormElementsUtil::prepareFormOutput($object->getTitle());
        $placeholders['PRG_DESCRIPTION'] = \ilLegacyFormElementsUtil::prepareFormOutput($object->getDescription());
        $placeholders['PRG_TYPE'] = \ilLegacyFormElementsUtil::prepareFormOutput($type ? $type->getTitle() : '');
        $placeholders['PRG_POINTS'] = \ilLegacyFormElementsUtil::prepareFormOutput(
            $latest_progress ? (string) $latest_progress->getCurrentAmountOfPoints() : ''
        );
        $placeholders['PRG_COMPLETION_DATE'] = ilLegacyFormElementsUtil::prepareFormOutput(
            $latest_progress && $latest_progress->getCompletionDate() instanceof DateTimeImmutable ? $latest_progress->getCompletionDate(
            )->format('d.m.Y') : ''
        );
        $placeholders['PRG_EXPIRES_AT'] = ilLegacyFormElementsUtil::prepareFormOutput(
            $latest_progress && $latest_progress->getValidityOfQualification(
            ) instanceof DateTimeImmutable ? $latest_progress->getValidityOfQualification()->format('d.m.Y') : ''
        );
        return $placeholders;
    }

    /**
     * This method is different then the 'getPlaceholderValues' method, this
     * method is used to create a placeholder value array containing dummy values
     * that is used to create a preview certificate.
     */
    public function getPlaceholderValuesForPreview(int $userId, int $objId): array
    {
        $placeholders = $this->defaultPlaceholderValuesObject->getPlaceholderValuesForPreview($userId, $objId);

        $object = $this->objectHelper->getInstanceByObjId($objId);
        $type = $object->getSubType();
        $today = ilLegacyFormElementsUtil::prepareFormOutput((new DateTime())->format('d.m.Y'));
        $placeholders['PRG_TITLE'] = ilLegacyFormElementsUtil::prepareFormOutput($object->getTitle());
        $placeholders['PRG_DESCRIPTION'] = ilLegacyFormElementsUtil::prepareFormOutput($object->getDescription());
        $placeholders['PRG_TYPE'] = ilLegacyFormElementsUtil::prepareFormOutput($type ? $type->getTitle() : '');
        $placeholders['PRG_POINTS'] = ilLegacyFormElementsUtil::prepareFormOutput((string) $object->getPoints());
        $placeholders['PRG_COMPLETION_DATE'] = $today;
        $placeholders['PRG_EXPIRES_AT'] = $today;
        return $placeholders;
    }
}
