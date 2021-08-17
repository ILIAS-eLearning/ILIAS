<?php declare(strict_types=1);

/* Copyright (c) 1998-2018 ILIAS open source, Extended GPL, see docs/LICENSE */

use ILIAS\UI\Factory;
use ILIAS\UI\Renderer;
use ILIAS\DI\Container;

/**
 * @author  Niels Theen <ntheen@databay.de>
 */
class ilCertificateLearningHistoryProvider extends ilAbstractLearningHistoryProvider implements ilLearningHistoryProviderInterface
{
    private ilUserCertificateRepository $userCertificateRepository;
    private ilCtrl $controller;
    private ilSetting $certificateSettings;
    protected Factory $uiFactory;
    protected Renderer $uiRenderer;
    private ilCertificateUtilHelper $utilHelper;

    public function __construct(
        int $user_id,
        ilLearningHistoryFactory $factory,
        ilLanguage $lng,
        ?ilTemplate $template = null,
        ?Container $dic = null,
        ?ilUserCertificateRepository $userCertificateRepository = null,
        ?ilCtrl $controller = null,
        ?ilSetting $certificateSettings = null,
        ?Factory $uiFactory = null,
        ?Renderer $uiRenderer = null,
        ?ilCertificateUtilHelper $utilHelper = null
    ) {
        $lng->loadLanguageModule("cert");

        parent::__construct($user_id, $factory, $lng, $template);

        if (null === $dic) {
            global $DIC;
            $dic = $DIC;
        }

        if (null === $userCertificateRepository) {
            $database = $dic->database();
            $looger = $dic->logger()->cert();
            $userCertificateRepository = new ilUserCertificateRepository($database, $looger);
        }
        $this->userCertificateRepository = $userCertificateRepository;

        if (null === $controller) {
            $controller = $dic->ctrl();
        }
        $this->controller = $controller;

        if (null === $certificateSettings) {
            $certificateSettings = new ilSetting("certificate");
        }
        $this->certificateSettings = $certificateSettings;

        if (null === $uiFactory) {
            $uiFactory = $dic->ui()->factory();
        }
        $this->uiFactory = $uiFactory;

        if (null === $uiRenderer) {
            $uiRenderer = $dic->ui()->renderer();
        }
        $this->uiRenderer = $uiRenderer;

        if (null === $utilHelper) {
            $utilHelper = new ilCertificateUtilHelper();
        }
        $this->utilHelper = $utilHelper;
    }

    /**
     * Is active?
     * @return bool
     */
    public function isActive() : bool
    {
        return (bool) $this->certificateSettings->get('active');
    }

    /**
     * Get entries
     * @param int $ts_start
     * @param int $ts_end
     * @return ilLearningHistoryEntry[]
     */
    public function getEntries($ts_start, $ts_end) : array
    {
        $entries = [];

        $certificates = $this->userCertificateRepository->fetchActiveCertificatesInIntervalForPresentation(
            $this->user_id,
            $ts_start,
            $ts_end
        );

        foreach ($certificates as $certificate) {
            $objectId = $certificate->getUserCertificate()->getObjId();

            $this->controller->setParameterByClass(
                'ilUserCertificateGUI',
                'certificate_id',
                $certificate->getUserCertificate()->getId()
            );
            $href = $this->controller->getLinkTargetByClass(['ilDashboardGUI',
                                                             'ilAchievementsGUI',
                                                             'ilUserCertificateGUI'
            ], 'download');
            $this->controller->clearParametersByClass('ilUserCertificateGUI');

            $prefixTextWithLink = sprintf(
                $this->lng->txt('certificate_achievement_sub_obj'),
                $this->uiRenderer->render($this->uiFactory->link()->standard(
                    $this->getEmphasizedTitle($certificate->getObjectTitle()),
                    $href
                ))
            );

            $text = sprintf(
                $this->lng->txt('certificate_achievement'),
                $prefixTextWithLink
            );

            $entries[] = new ilLearningHistoryEntry(
                $text,
                $text,
                $this->utilHelper->getImagePath("icon_cert.svg"),
                $certificate->getUserCertificate()->getAcquiredTimestamp(),
                $objectId
            );
        }

        return $entries;
    }

    /**
     * Get name of provider (in user language)
     * @return string
     */
    public function getName() : string
    {
        return $this->lng->txt('certificates');
    }
}
