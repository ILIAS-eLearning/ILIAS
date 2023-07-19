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
 * Class ilLTIConsumerPlaceholderValues
 *
 * @author      Uwe Kohnle <kohnle@internetlehrer-gmbh.de>
 * @author      Björn Heyser <info@bjoernheyser.de>
 *
 * @package     Modules/LTIConsumer
 */
class ilLTIConsumerPlaceholderValues implements ilCertificatePlaceholderValues
{
    private \ilDefaultPlaceholderValues $defaultPlaceholderValuesObject;

    private ?\ilCertificateObjectHelper $objectHelper;

//    private \ilCertificateUserObjectHelper $userObjectHelper;

    private ?\ilCertificateUtilHelper $utilHelper;

    private ?\ilCertificateLPStatusHelper $lpStatusHelper;

    /**
     * @var ilCertificateDateHelper|ilDatePresentation|null
     */
    private $dateHelper;

    private ?\ilLanguage $language;

    /**
     * @param ilDefaultPlaceholderValues|null    $defaultPlaceholderValues
     * @param ilLanguage|null                    $language
     * @param ilCertificateObjectHelper|null     $objectHelper
     * @param ilCertificateUserObjectHelper|null $userObjectHelper
     * @param ilCertificateLPStatusHelper|null   $lpStatusHelper
     * @param ilCertificateUtilHelper|null       $utilHelper
     * @param ilCertificateDateHelper|null       $dateHelper
     */
    public function __construct(
        ?ilDefaultPlaceholderValues $defaultPlaceholderValues = null,
        ?ilLanguage $language = null,
        ?ilCertificateObjectHelper $objectHelper = null,
        ?ilCertificateUserObjectHelper $userObjectHelper = null,
        ?ilCertificateLPStatusHelper $lpStatusHelper = null,
        ?ilCertificateUtilHelper $utilHelper = null,
        ?ilCertificateDateHelper $dateHelper = null
    ) {
        if (null === $language) {
            global $DIC; /* @var \ILIAS\DI\Container $DIC */
            $language = $DIC->language();
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

        if (null === $userObjectHelper) {
            $userObjectHelper = new ilCertificateUserObjectHelper();
        }
//        $this->userObjectHelper = $userObjectHelper;

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
     * @throws ilDateTimeException
     * @throws ilException
     * @return mixed[]
     */
    public function getPlaceholderValuesForPreview(int $userId, int $objId): array
    {
        $placeholders = $this->defaultPlaceholderValuesObject->getPlaceholderValuesForPreview($userId, $objId);

        $placeholders['OBJECT_TITLE'] = $this->utilHelper->prepareFormOutput($this->language->txt('lti_cert_ph_object_title'));
        $placeholders['OBJECT_DESCRIPTION'] = $this->utilHelper->prepareFormOutput($this->language->txt('lti_cert_ph_object_description'));

        $placeholders['MASTERY_SCORE'] = $this->utilHelper->prepareFormOutput($this->language->txt('lti_cert_ph_mastery_score'));
        $placeholders['REACHED_SCORE'] = $this->utilHelper->prepareFormOutput($this->language->txt('lti_cert_ph_reached_score'));

        return $placeholders;
    }

    /**
     * @throws ilDatabaseException
     * @throws ilDateTimeException
     * @throws ilException
     * @throws ilInvalidCertificateException
     * @throws ilObjectNotFoundException
     * @return mixed[]
     */
    public function getPlaceholderValues(int $userId, int $objId): array
    {
        $placeholders = $this->defaultPlaceholderValuesObject->getPlaceholderValues($userId, $objId);

        /* @var ilObjLTIConsumer $object */
        $object = $this->objectHelper->getInstanceByObjId($objId);

        $placeholders['OBJECT_TITLE'] = $this->utilHelper->prepareFormOutput($object->getTitle());
        $placeholders['OBJECT_DESCRIPTION'] = $this->utilHelper->prepareFormOutput($object->getDescription());

        $placeholders['MASTERY_SCORE'] = $this->utilHelper->prepareFormOutput($this->getMasteryScore($object));
        $placeholders['REACHED_SCORE'] = $this->utilHelper->prepareFormOutput($this->getReachedScore($object, $userId));

        $completionDate = $this->lpStatusHelper->lookupStatusChanged($objId, $userId);
        if ($completionDate != false &&
            $completionDate !== null &&
            $completionDate !== ''
        ) {
            $placeHolders['DATE_COMPLETED'] = $this->dateHelper->formatDate($completionDate);
            $placeHolders['DATETIME_COMPLETED'] = $this->dateHelper->formatDateTime($completionDate);
        }

        return $placeholders;
    }

    protected function getMasteryScore(ilObjLTIConsumer $object): string
    {
        return sprintf('%0.2f %%', $object->getMasteryScorePercent());
    }

    protected function getReachedScore(ilObjLTIConsumer $object, int $userId): string
    {
        $userResult = ilLTIConsumerResult::getByKeys($object->getId(), $userId);

        $reachedScore = sprintf('%0.2f %%', 0);
        if ($userResult !== null) {
            $reachedScore = sprintf('%0.2f %%', $userResult->getResult() * 100);
        }

        return $reachedScore;
    }
}
