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

use ILIAS\UI\Factory;
use ILIAS\UI\Renderer;
use ILIAS\Filesystem\Filesystem;
use Psr\Http\Message\ServerRequestInterface;

/**
 * @ilCtrl_IsCalledBy ilUserCertificateGUI: ilAchievementsGUI
 */
class ilUserCertificateGUI
{
    final public const SORTATION_SESSION_KEY = 'my_certificates_sorting';

    private readonly ilGlobalTemplateInterface $template;
    private readonly ilCtrlInterface $ctrl;
    private readonly ilLanguage $language;
    private readonly ilUserCertificateRepository $userCertificateRepository;
    private readonly ilObjUser $user;
    private readonly ServerRequestInterface $request;
    private readonly ilLogger $certificateLogger;
    private readonly Factory $uiFactory;
    private readonly Renderer $uiRenderer;
    private readonly ilAccessHandler $access;
    private readonly ilHelpGUI $help;
    private readonly ilDBInterface $db;

    /**
     * @var array<string, string>
     */
    protected array $sortationOptions = [
        'title_ASC' => 'cert_sortable_by_title_asc',
        'title_DESC' => 'cert_sortable_by_title_desc',
        'date_ASC' => 'cert_sortable_by_issue_date_asc',
        'date_DESC' => 'cert_sortable_by_issue_date_desc',
    ];
    protected string $defaultSorting = 'date_DESC';
    private readonly Filesystem $filesystem;

    public function __construct(
        ?ilGlobalTemplateInterface $template = null,
        ?ilCtrlInterface $ctrl = null,
        ?ilLanguage $language = null,
        ?ilObjUser $user = null,
        ?ilUserCertificateRepository $userCertificateRepository = null,
        ?ServerRequestInterface $request = null,
        ?ilLogger $certificateLogger = null,
        private readonly ilSetting $certificateSettings = new ilSetting('certificate'),
        ?Factory $uiFactory = null,
        ?Renderer $uiRenderer = null,
        ?ilAccessHandler $access = null,
        ?Filesystem $filesystem = null,
        ?ilHelpGUI $help = null,
        ?ilDBInterface $db = null
    ) {
        global $DIC;

        $this->template = $template ?? $DIC->ui()->mainTemplate();
        $this->ctrl = $ctrl ?? $DIC->ctrl();
        $this->user = $user ?? $DIC->user();
        $this->language = $language ?? $DIC->language();
        $this->request = $request ?? $DIC->http()->request();
        $this->certificateLogger = $certificateLogger ?? $DIC->logger()->cert();
        $this->uiFactory = $uiFactory ?? $DIC->ui()->factory();
        $this->uiRenderer = $uiRenderer ?? $DIC->ui()->renderer();
        $this->access = $access ?? $DIC->access();
        $this->filesystem = $filesystem ?? $DIC->filesystem()->web();
        $this->userCertificateRepository = $userCertificateRepository ?? new ilUserCertificateRepository(null, $this->certificateLogger);
        $this->help = $help ?? $DIC->help();
        $this->db = $db ?? $DIC->database();

        $this->language->loadLanguageModule('cert');
    }

    private function getDefaultCommand(): string
    {
        return 'listCertificates';
    }

    public function executeCommand(): bool
    {
        $cmd = $this->ctrl->getCmd();

        if (!$this->certificateSettings->get('active', '0')) {
            $this->ctrl->returnToParent($this);
        }

        $this->template->setTitle($this->language->txt('obj_cert'));
        if (!method_exists($this, $cmd)) {
            $cmd = $this->getDefaultCommand();
        }
        $this->{$cmd}();

        return true;
    }

    /**
     * @throws ilDateTimeException
     * @throws ilWACException
     */
    public function listCertificates(): void
    {
        $this->help->setScreenIdComponent('cert');

        if (!$this->certificateSettings->get('active', '0')) {
            $this->ctrl->redirect($this);
        }

        $provider = new ilUserCertificateTableProvider(
            $this->db,
            $this->certificateLogger,
            $this->language->txt('certificate_no_object_title')
        );

        $sorting = $this->getCurrentSortation();
        $data = $provider->fetchDataSet(
            $this->user->getId(),
            [
                'order_field' => explode('_', $sorting)[0],
                'order_direction' => explode('_', $sorting)[1],
                'language' => $this->user->getLanguage()
            ],
            []
        );

        $uiComponents = [];

        if ($data['items'] !== []) {
            $sortationOptions = [];
            $cards = [];

            foreach ($this->sortationOptions as $fieldAndDirection => $lngVariable) {
                $sortationOptions[$fieldAndDirection] = $this->language->txt($lngVariable);
            }

            $sortViewControl = $this->uiFactory
                ->viewControl()
                ->sortation($sortationOptions)
                ->withLabel($this->language->txt($this->sortationOptions[$sorting]))
                ->withTargetURL($this->ctrl->getLinkTarget($this, 'applySortation'), 'sort_by');
            $uiComponents[] = $sortViewControl;

            foreach ($data['items'] as $certificateData) {
                $thumbnailImagePath = $certificateData['thumbnail_image_path'];
                $imagePath = ilFileUtils::getWebspaceDir() . $thumbnailImagePath;
                if ($thumbnailImagePath === null
                    || $thumbnailImagePath === ''
                    || !$this->filesystem->has($thumbnailImagePath)
                ) {
                    $imagePath = ilUtil::getImagePath('standard/icon_cert.svg');
                }

                $cardImage = $this->uiFactory->image()->standard(
                    ilWACSignedPath::signFile($imagePath),
                    $certificateData['title']
                );

                $sections = [];

                if ($certificateData['description'] !== '') {
                    $sections[] = $this->uiFactory->listing()->descriptive([
                        $this->language->txt('cert_description_label') => $certificateData['description']
                    ]);
                }

                $oldDatePresentationStatus = ilDatePresentation::useRelativeDates();
                ilDatePresentation::setUseRelativeDates(true);
                $sections[] = $this->uiFactory->listing()->descriptive([
                    $this->language->txt('cert_issued_on_label') => ilDatePresentation::formatDate(
                        new ilDateTime($certificateData['date'], IL_CAL_UNIX)
                    )
                ]);
                ilDatePresentation::setUseRelativeDates($oldDatePresentationStatus);

                $objectTypeIcon = $this->uiFactory
                    ->symbol()
                    ->icon()
                    ->standard($certificateData['obj_type'], $certificateData['obj_type']);

                $objectTitle = $certificateData['title'];
                $refIds = ilObject::_getAllReferences((int) $certificateData['obj_id']);
                foreach ($refIds as $refId) {
                    if ($this->access->checkAccess('read', '', $refId)) {
                        $objectTitle = $this->uiRenderer->render(
                            $this->uiFactory->link()->standard($objectTitle, ilLink::_getLink($refId))
                        );
                        break;
                    }
                }

                $sections[] = $this->uiFactory->listing()->descriptive([$this->language->txt('cert_object_label') => implode(
                    '',
                    [
                        $this->uiRenderer->render($objectTypeIcon),
                        $objectTitle
                    ]
                )
                ]);

                $this->ctrl->setParameter($this, 'certificate_id', $certificateData['id']);
                $downloadHref = $this->ctrl->getLinkTarget($this, 'download');
                $this->ctrl->clearParameters($this);
                $sections[] = $this->uiFactory->button()->standard('Download', $downloadHref);

                $card = $this->uiFactory
                    ->card()
                    ->standard($certificateData['title'], $cardImage)
                    ->withSections($sections);

                $cards[] = $card;
            }

            $deck = $this->uiFactory->deck($cards)->withSmallCardsSize();

            $uiComponents[] = $this->uiFactory->divider()->horizontal();

            $uiComponents[] = $deck;
        } else {
            $this->template->setOnScreenMessage('info', $this->language->txt('cert_currently_no_certs'));
        }

        $this->template->setContent($this->uiRenderer->render($uiComponents));
    }

    protected function getCurrentSortation(): string
    {
        $sorting = ilSession::get(self::SORTATION_SESSION_KEY);
        if (!array_key_exists($sorting, $this->sortationOptions)) {
            $sorting = $this->defaultSorting;
        }

        return $sorting;
    }

    /**
     * @throws ilWACException
     * @throws ilDateTimeException
     */
    protected function applySortation(): void
    {
        $sorting = $this->request->getQueryParams()['sort_by'] ?? $this->defaultSorting;
        if (!array_key_exists($sorting, $this->sortationOptions)) {
            $sorting = $this->defaultSorting;
        }
        ilSession::set(self::SORTATION_SESSION_KEY, $sorting);

        $this->listCertificates();
    }

    /**
     * @throws ilException
     */
    public function download(): void
    {
        $pdfGenerator = new ilPdfGenerator($this->userCertificateRepository);

        $userCertificateId = (int) $this->request->getQueryParams()['certificate_id'];

        try {
            $userCertificate = $this->userCertificateRepository->fetchCertificate($userCertificateId);
            if ($userCertificate->getUserId() !== $this->user->getId()) {
                throw new ilException(sprintf(
                    'User "%s" tried to access certificate: "%s"',
                    $this->user->getLogin(),
                    $userCertificateId
                ));
            }
        } catch (ilException $exception) {
            $this->certificateLogger->warning($exception->getMessage());
            $this->template->setOnScreenMessage('failure', $this->language->txt('cert_error_no_access'));
            $this->listCertificates();
            return;
        }

        $pdfAction = new ilCertificatePdfAction(
            $pdfGenerator,
            new ilCertificateUtilHelper(),
            $this->language->txt('error_creating_certificate_pdf')
        );

        $pdfAction->downloadPdf($userCertificate->getUserId(), $userCertificate->getObjId());

        $this->listCertificates();
    }
}
