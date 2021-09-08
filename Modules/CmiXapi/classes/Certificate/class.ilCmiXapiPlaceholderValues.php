<?php

/* Copyright (c) 1998-2019 ILIAS open source, Extended GPL, see docs/LICENSE */

/**
 * Class ilCmiXapiPlaceholderValues
 *
 * @author      Uwe Kohnle <kohnle@internetlehrer-gmbh.de>
 * @author      Björn Heyser <info@bjoernheyser.de>
 * @author      Stefan Schneider <info@eqsoft.de>
 *
 * @package     Module/CmiXapi
 */
class ilCmiXapiPlaceholderValues implements ilCertificatePlaceholderValues
{
    /**
     * @var ilDefaultPlaceholderValues
     */
    private $defaultPlaceholderValuesObject;

    /**
     * @var ilCertificateObjectHelper|null
     */
    private $objectHelper;

    /**
     * @var ilCertificateUserObjectHelper
     */
    private $userObjectHelper;

    /**
     * @var ilCertificateUtilHelper|null
     */
    private $utilHelper;

    /**
     * @var ilCertificateLPStatusHelper|null
     */
    private $lpStatusHelper;

    /**
     * @var ilCertificateDateHelper|ilDatePresentation|null
     */
    private $dateHelper;

    /**
     * @var ilLanguage|null
     */
    private $language;

    /**
     * @param ilDefaultPlaceholderValues $defaultPlaceholderValues
     * @param ilLanguage|null $language
     * @param ilCertificateObjectHelper|null $objectHelper
     * @param ilCertificateTestObjectHelper|null $testObjectHelper
     * @param ilCertificateUserObjectHelper|null $userObjectHelper
     * @param ilCertificateLPStatusHelper|null $lpStatusHelper
     * @param ilCertificateUtilHelper|null $utilHelper
     * @param ilDatePresentation|null $dateHelper
     */
    public function __construct(
        ilDefaultPlaceholderValues $defaultPlaceholderValues = null,
        ilLanguage $language = null,
        ilCertificateObjectHelper $objectHelper = null,
        ilCertificateUserObjectHelper $userObjectHelper = null,
        ilCertificateLPStatusHelper $lpStatusHelper = null,
        ilCertificateUtilHelper $utilHelper = null,
        ilCertificateDateHelper $dateHelper = null
    ) {
        if (null === $language) {
            global $DIC;
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

    public function getPlaceholderValuesForPreview(int $userId, int $objId)
    {
        $placeholders = $this->defaultPlaceholderValuesObject->getPlaceholderValuesForPreview($userId, $objId);

        $placeholders['OBJECT_TITLE'] = $this->utilHelper->prepareFormOutput($this->language->txt('lti_cert_ph_object_title'));
        $placeholders['OBJECT_DESCRIPTION'] = $this->utilHelper->prepareFormOutput($this->language->txt('lti_cert_ph_object_description'));

        $placeholders['MASTERY_SCORE'] = $this->utilHelper->prepareFormOutput($this->language->txt('lti_cert_ph_mastery_score'));
        $placeholders['REACHED_SCORE'] = $this->utilHelper->prepareFormOutput($this->language->txt('lti_cert_ph_reached_score'));

        return $placeholders;
    }

    public function getPlaceholderValues(int $userId, int $objId) : array
    {
        $placeholders = $this->defaultPlaceholderValuesObject->getPlaceholderValues($userId, $objId);

        /* @var ilObjLTIConsumer $object */
        $object = $this->objectHelper->getInstanceByObjId($objId);

        $placeholders['OBJECT_TITLE'] = $this->utilHelper->prepareFormOutput($object->getTitle());
        $placeholders['OBJECT_DESCRIPTION'] = $this->utilHelper->prepareFormOutput($object->getDescription());

        $placeholders['REACHED_SCORE'] = $this->utilHelper->prepareFormOutput($this->getReachedScore((int) $objId, (int) $userId));

        return $placeholders;
    }

    protected function getReachedScore(int $objectId, int $userId) : string
    {
        try {
            $cmixResult = ilCmiXapiResult::getInstanceByObjIdAndUsrId(
                $objectId,
                $userId
            );
        } catch (ilCmiXapiException $e) {
            $cmixResult = ilCmiXapiResult::getEmptyInstance();
        }

        $reachedScore = sprintf('%0.2f %%', $cmixResult->getScore() * 100);

        return $reachedScore;
    }
}
