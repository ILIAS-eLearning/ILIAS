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

class ilStudyProgrammePlaceholderValues implements ilCertificatePlaceholderValues
{
    private ilDefaultPlaceholderValues $defaultPlaceholderValuesObject;
    private ilLanguage $language;
    private ilCertificateObjectHelper $objectHelper;
    private ilCertificateParticipantsHelper $participantsHelper;
    private ilCertificateUtilHelper $ilUtilHelper;

    public function __construct(
        ?ilDefaultPlaceholderValues $defaultPlaceholderValues = null,
        ?ilLanguage $language = null,
        ?ilCertificateObjectHelper $objectHelper = null,
        ?ilCertificateParticipantsHelper $participantsHelper = null,
        ?ilCertificateUtilHelper $ilUtilHelper = null
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

        $this->defaultPlaceholderValuesObject = $defaultPlaceholderValues;
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
     * @throws ilException
     * @throws ilObjectNotFoundException
     */
    public function getPlaceholderValues(int $userId, int $objId): array
    {
        $object = $this->objectHelper->getInstanceByObjId($objId);
        $placeholders = $this->defaultPlaceholderValuesObject->getPlaceholderValues($userId, $objId);
        $assignments = $object->getAssignmentsOfSingleProgramForUser($userId);
        $latest_progress = $this->getRelevantProgressFromAssignments($assignments);
        $type = $object->getSubType();

        $placeholders['PRG_TITLE'] = ilLegacyFormElementsUtil::prepareFormOutput($object->getTitle());
        $placeholders['PRG_DESCRIPTION'] = ilLegacyFormElementsUtil::prepareFormOutput($object->getDescription());
        $placeholders['PRG_TYPE'] = ilLegacyFormElementsUtil::prepareFormOutput($type ? $type->getTitle() : '');
        $placeholders['PRG_POINTS'] = ilLegacyFormElementsUtil::prepareFormOutput(
            $latest_progress ? (string) $latest_progress->getCurrentAmountOfPoints() : ''
        );
        $placeholders['PRG_COMPLETION_DATE'] = ilLegacyFormElementsUtil::prepareFormOutput(
            $latest_progress ? $latest_progress->getCompletionDate()->format('d.m.Y') : ''
        );
        $placeholders['PRG_EXPIRES_AT'] = ilLegacyFormElementsUtil::prepareFormOutput(
            $latest_progress && $latest_progress->getValidityOfQualification() ?
                $latest_progress->getValidityOfQualification()->format('d.m.Y') : ''
        );
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

    protected function getRelevantProgressFromAssignments(array $assignments): ?ilPRGProgress
    {
        if (count($assignments) === 0) {
            return null;
        }
        $latest_progress = null;
        $latest_successful = $this->getLatestSuccessfulAssignment($assignments);
        if ($latest_successful) {
            $latest_progress = $latest_successful->getProgressTree();
        }
        return $latest_progress;
    }

    protected function getLatestSuccessfulAssignment(array $assignments): ?ilPRGAssignment
    {
        $successful = array_filter(
            $assignments,
            fn ($ass) => $ass->getProgressTree()->isSuccessful()
        );
        if (count($successful) === 0) {
            return null;
        }

        //is there an unlimited validity? if so, take latest.
        $unlimited = array_filter(
            $successful,
            fn ($ass) => is_null($ass->getProgressTree()->getValidityOfQualification())
        );
        if (count($unlimited) > 0) {
            $successful = $unlimited;
            usort($successful, static function (ilPRGAssignment $a, ilPRGAssignment $b): int {
                $a_dat = $a->getProgressTree()->getCompletionDate();
                $b_dat = $b->getProgressTree()->getCompletionDate();
                if ($a_dat > $b_dat) {
                    return -1;
                } elseif ($a_dat < $b_dat) {
                    return 1;
                } else {
                    return 0;
                }
            });
        } else {
            //there are (only) limited validities: take the one that lasts the longest.
            $limited = array_filter(
                $successful,
                fn ($ass) => !is_null($ass->getProgressTree()->getValidityOfQualification())
            );
            $successful = $limited;
            usort($successful, static function (ilPRGAssignment $a, ilPRGAssignment $b): int {
                $a_dat = $a->getProgressTree()->getValidityOfQualification();
                $b_dat = $b->getProgressTree()->getValidityOfQualification();
                if ($a_dat > $b_dat) {
                    return -1;
                } elseif ($a_dat < $b_dat) {
                    return 1;
                } else {
                    return 0;
                }
            });
        }
        return array_shift($successful);
    }
}
