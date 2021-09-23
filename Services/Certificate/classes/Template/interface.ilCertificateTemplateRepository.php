<?php declare(strict_types=1);

/**
 * @author  Niels Theen <ntheen@databay.de>
 * Repository that allows interaction with the database
 * in the context of certificate templates.
 */
interface ilCertificateTemplateRepository
{
    public function save(ilCertificateTemplate $certificateTemplate) : void;

    /**
     * @param ilCertificateTemplate $certificateTemplate
     * @param bool $currentlyActive
     * @return int
     */
    public function updateActivity(ilCertificateTemplate $certificateTemplate, bool $currentlyActive) : int;

    /**
     * @param int $templateId
     * @return ilCertificateTemplate
     * @throws ilException
     */
    public function fetchTemplate(int $templateId) : ilCertificateTemplate;

    /**
     * @param int $objId
     * @return ilCertificateTemplate[]
     */
    public function fetchCertificateTemplatesByObjId(int $objId) : array;

    public function fetchCurrentlyUsedCertificate(int $objId) : ilCertificateTemplate;

    /**
     * @param int $objId
     * @return ilCertificateTemplate
     * @throws ilException
     */
    public function fetchCurrentlyActiveCertificate(int $objId) : ilCertificateTemplate;

    public function fetchPreviousCertificate(int $objId) : ilCertificateTemplate;

    public function deleteTemplate(int $templateId, int $objectId) : void;

    public function activatePreviousCertificate(int $objId) : ilCertificateTemplate;

    /**
     * @param bool $isGlobalLpEnabled
     * @return ilCertificateTemplate[]
     */
    public function fetchActiveCertificateTemplatesForCoursesWithDisabledLearningProgress(
        bool $isGlobalLpEnabled
    ) : array;

    /**
     * @param int $objId
     * @return ilCertificateTemplate
     * @throws ilException
     */
    public function fetchFirstCreatedTemplate(int $objId) : ilCertificateTemplate;
}
