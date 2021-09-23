<?php declare(strict_types=1);

class ilCachedCertificateTemplateRepository implements ilCertificateTemplateRepository
{
    /** @var ilCertificateTemplate[]|null */
    protected static ?array $crs_certificates_without_lp;

    private ilCertificateTemplateRepository $wrapped;

    public function __construct(ilCertificateTemplateRepository $wrapped)
    {
        $this->wrapped = $wrapped;
    }

    public function save(ilCertificateTemplate $certificateTemplate) : void
    {
        $this->wrapped->save($certificateTemplate);
    }

    public function updateActivity(ilCertificateTemplate $certificateTemplate, bool $currentlyActive) : int
    {
        return $this->wrapped->updateActivity($certificateTemplate, $currentlyActive);
    }

    public function fetchTemplate(int $templateId) : ilCertificateTemplate
    {
        return $this->wrapped->fetchTemplate($templateId);
    }

    public function fetchCertificateTemplatesByObjId(int $objId) : array
    {
        return $this->wrapped->fetchCertificateTemplatesByObjId($objId);
    }

    public function fetchCurrentlyUsedCertificate(int $objId) : ilCertificateTemplate
    {
        return $this->wrapped->fetchCurrentlyUsedCertificate($objId);
    }

    public function fetchCurrentlyActiveCertificate(int $objId) : ilCertificateTemplate
    {
        return $this->wrapped->fetchCurrentlyActiveCertificate($objId);
    }

    public function fetchPreviousCertificate(int $objId) : ilCertificateTemplate
    {
        return $this->wrapped->fetchPreviousCertificate($objId);
    }

    public function deleteTemplate(int $templateId, int $objectId) : void
    {
        $this->wrapped->deleteTemplate($templateId, $objectId);
    }

    public function activatePreviousCertificate(int $objId) : ilCertificateTemplate
    {
        return $this->wrapped->activatePreviousCertificate($objId);
    }

    public function fetchActiveCertificateTemplatesForCoursesWithDisabledLearningProgress(
        bool $isGlobalLpEnabled
    ) : array {
        if (null === self::$crs_certificates_without_lp) {
            self::$crs_certificates_without_lp = $this->wrapped
                ->fetchActiveCertificateTemplatesForCoursesWithDisabledLearningProgress($isGlobalLpEnabled);
        }
        return self::$crs_certificates_without_lp;
    }

    public function fetchFirstCreatedTemplate(int $objId) : ilCertificateTemplate
    {
        return $this->wrapped->fetchFirstCreatedTemplate($objId);
    }
}
