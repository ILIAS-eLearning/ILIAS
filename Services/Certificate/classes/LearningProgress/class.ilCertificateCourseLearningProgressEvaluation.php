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
class ilCertificateCourseLearningProgressEvaluation
{
    private ilCertificateTemplateRepository $templateRepository;
    private ilSetting $setting;
    private ilCertificateObjectHelper $objectHelper;
    private ilCertificateLPStatusHelper $statusHelper;
    private ilCertificateObjUserTrackingHelper $trackingHelper;

    public function __construct(
        ilCertificateTemplateRepository $templateRepository,
        ?ilSetting $setting = null,
        ?ilCertificateObjectHelper $objectHelper = null,
        ?ilCertificateLPStatusHelper $statusHelper = null,
        ?ilCertificateObjUserTrackingHelper $trackingHelper = null
    ) {
        $this->templateRepository = $templateRepository;

        if (null === $setting) {
            $setting = new ilSetting('crs');
        }
        $this->setting = $setting;

        if (null === $objectHelper) {
            $objectHelper = new ilCertificateObjectHelper();
        }
        $this->objectHelper = $objectHelper;

        if (null === $statusHelper) {
            $statusHelper = new ilCertificateLPStatusHelper();
        }
        $this->statusHelper = $statusHelper;
        if (null === $trackingHelper) {
            $trackingHelper = new ilCertificateObjUserTrackingHelper();
        }
        $this->trackingHelper = $trackingHelper;
    }

    /**
     * @param int $refId
     * @param int $userId
     * @return ilCertificateTemplate[]
     */
    public function evaluate(int $refId, int $userId) : array
    {
        $courseTemplates = $this->templateRepository
            ->fetchActiveCertificateTemplatesForCoursesWithDisabledLearningProgress(
                $this->trackingHelper->enabledLearningProgress()
            );

        $templatesOfCompletedCourses = [];
        foreach ($courseTemplates as $courseTemplate) {
            $courseObjectId = $courseTemplate->getObjId();

            $subItems = $this->setting->get('cert_subitems_' . $courseObjectId);
            if ($subItems === null) {
                continue;
            }
            $subItems = json_decode($subItems, true, 512, JSON_THROW_ON_ERROR);
            if (!is_array($subItems)) {
                continue;
            }

            $subitem_obj_ids = [];
            foreach ($subItems as $subItemRefId) {
                $subitem_obj_ids[$subItemRefId] = $this->objectHelper->lookupObjId((int) $subItemRefId);
            }

            if (in_array($refId, $subItems, true)) {
                $completed = true;

                // check if all subitems are completed now
                foreach ($subitem_obj_ids as $subitem_ref_id => $subitem_id) {
                    $status = $this->statusHelper->lookUpStatus($subitem_id, $userId);

                    if ($status !== ilLPStatus::LP_STATUS_COMPLETED_NUM) {
                        $completed = false;
                        break;
                    }
                }

                if (true === $completed) {
                    $templatesOfCompletedCourses[] = $courseTemplate;
                }
            }
        }

        return $templatesOfCompletedCourses;
    }
}
