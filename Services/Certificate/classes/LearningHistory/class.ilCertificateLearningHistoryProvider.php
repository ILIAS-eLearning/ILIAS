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

use ILIAS\UI\Factory;
use ILIAS\UI\Renderer;
use ILIAS\DI\Container;

/**
 * @author  Niels Theen <ntheen@databay.de>
 */
class ilCertificateLearningHistoryProvider extends ilAbstractLearningHistoryProvider implements ilLearningHistoryProviderInterface
{
    private ilUserCertificateRepository $userCertificateRepository;
    private ilCtrlInterface $ctrl;
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
        ?ilCtrlInterface $ctrl = null,
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

        if (null === $ctrl) {
            $ctrl = $dic->ctrl();
        }
        $this->ctrl = $ctrl;

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

    public function isActive() : bool
    {
        return (bool) $this->certificateSettings->get('active', '0');
    }

    /**
     * Get entries
     * @param int $ts_start
     * @param int $ts_end
     * @return ilLearningHistoryEntry[]
     */
    public function getEntries(int $ts_start, int $ts_end) : array
    {
        $entries = [];

        $certificates = $this->userCertificateRepository->fetchActiveCertificatesInIntervalForPresentation(
            $this->user_id,
            $ts_start,
            $ts_end
        );

        foreach ($certificates as $certificate) {
            $objectId = $certificate->getUserCertificate()->getObjId();

            $this->ctrl->setParameterByClass(
                ilUserCertificateGUI::class,
                'certificate_id',
                $certificate->getUserCertificate()->getId()
            );
            $href = $this->ctrl->getLinkTargetByClass(
                [
                    ilDashboardGUI::class,
                    ilAchievementsGUI::class,
                    ilUserCertificateGUI::class
                ],
                'download'
            );
            $this->ctrl->clearParametersByClass(ilUserCertificateGUI::class);

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

    public function getName() : string
    {
        return $this->lng->txt('certificates');
    }
}
