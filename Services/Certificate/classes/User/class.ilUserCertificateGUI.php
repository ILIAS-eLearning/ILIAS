<?php declare(strict_types=1);

/* Copyright (c) 1998-2018 ILIAS open source, Extended GPL, see docs/LICENSE */

use ILIAS\UI\Factory;
use ILIAS\UI\Renderer;
use ILIAS\Filesystem\Filesystem;
use Psr\Http\Message\RequestInterface;
use Psr\Http\Message\ServerRequestInterface;
use GuzzleHttp\Psr7\Request;

/**
 * @ingroup           ServicesCertificate
 * @author            Niels Theen <ntheen@databay.de>
 * @ilCtrl_IsCalledBy ilUserCertificateGUI: ilAchievementsGUI
 */
class ilUserCertificateGUI
{
    private ilGlobalTemplateInterface $template;
    private ilCtrl $ctrl;
    private ilLanguage $language;
    private ilUserCertificateRepository $userCertificateRepository;
    private ilObjUser $user;
    private ServerRequestInterface $request;
    private ilLogger $certificateLogger;
    protected ilSetting $certificateSettings;
    protected Factory $uiFactory;
    protected Renderer $uiRenderer;
    protected ilAccessHandler $access;
    public const SORTATION_SESSION_KEY = 'my_certificates_sorting';
    protected array $sortationOptions = [
        'title_ASC' => 'cert_sortable_by_title_asc',
        'title_DESC' => 'cert_sortable_by_title_desc',
        'date_ASC' => 'cert_sortable_by_issue_date_asc',
        'date_DESC' => 'cert_sortable_by_issue_date_desc',
    ];
    protected string $defaultSorting = 'date_DESC';
    private Filesystem $filesystem;

    public function __construct(
        ?ilTemplate $template = null,
        ?ilCtrl $ctrl = null,
        ?ilLanguage $language = null,
        ?ilObjUser $user = null,
        ?ilUserCertificateRepository $userCertificateRepository = null,
        ?GuzzleHttp\Psr7\Request $request = null,
        ?ilLogger $certificateLogger = null,
        ?ilSetting $certificateSettings = null,
        ?Factory $uiFactory = null,
        ?Renderer $uiRenderer = null,
        ?ilAccessHandler $access = null,
        ?Filesystem $filesystem = null
    ) {
        global $DIC;

        $logger = $DIC->logger()->cert();

        if ($template === null) {
            $template = $DIC->ui()->mainTemplate();
        }
        $this->template = $template;

        if ($ctrl === null) {
            $ctrl = $DIC->ctrl();
        }
        $this->ctrl = $ctrl;

        if ($language === null) {
            $language = $DIC->language();
        }
        $this->language = $language;

        if ($user === null) {
            $user = $DIC->user();
        }
        $this->user = $user;

        if ($request === null) {
            $request = $DIC->http()->request();
        }
        $this->request = $request;

        if ($certificateLogger === null) {
            $certificateLogger = $DIC->logger()->cert();
        }
        $this->certificateLogger = $certificateLogger;

        if ($certificateSettings === null) {
            $certificateSettings = new ilSetting("certificate");
        }
        $this->certificateSettings = $certificateSettings;

        if (null === $uiFactory) {
            $uiFactory = $DIC->ui()->factory();
        }
        $this->uiFactory = $uiFactory;

        if (null === $uiRenderer) {
            $uiRenderer = $DIC->ui()->renderer();
        }
        $this->uiRenderer = $uiRenderer;

        if (null === $access) {
            $access = $DIC->access();
        }
        $this->access = $access;

        if (null === $filesystem) {
            $filesystem = $DIC->filesystem()->web();
        }
        $this->filesystem = $filesystem;

        if ($userCertificateRepository === null) {
            $userCertificateRepository = new ilUserCertificateRepository(null, $this->certificateLogger);
        }
        $this->userCertificateRepository = $userCertificateRepository;

        $this->language->loadLanguageModule('cert');
        $this->language->loadLanguageModule('cert');
    }

    private function getDefaultCommand() : string
    {
        return 'listCertificates';
    }

    public function executeCommand() : bool
    {
        $nextClass = $this->ctrl->getNextClass($this);
        $cmd = $this->ctrl->getCmd();

        if (!$this->certificateSettings->get('active', '0')) {
            $this->ctrl->returnToParent($this);
        }

        $this->template->setTitle($this->language->txt('obj_cert'));

        switch ($nextClass) {
            default:
                if (!method_exists($this, $cmd)) {
                    $cmd = $this->getDefaultCommand();
                }
                $this->{$cmd}();
        }

        return true;
    }

    /**
     * @throws ilDateTimeException
     * @throws ilWACException|JsonException
     */
    public function listCertificates() : void
    {
        global $DIC;

        if (!$this->certificateSettings->get('active', '0')) {
            $this->ctrl->redirect($this);
            return;
        }

        $provider = new ilUserCertificateTableProvider(
            $DIC->database(),
            $this->certificateLogger,
            $this->ctrl,
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

        if (count($data['items']) > 0) {
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
                $imagePath = ilUtil::getWebspaceDir() . $thumbnailImagePath;
                if ($thumbnailImagePath === null
                    || $thumbnailImagePath === ''
                    || !$this->filesystem->has($thumbnailImagePath)
                ) {
                    $imagePath = ilUtil::getImagePath('icon_cert.svg');
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
                    ->standard($certificateData['obj_type'], $certificateData['obj_type'], 'small');

                $objectTitle = $certificateData['title'];
                $refIds = ilObject::_getAllReferences((int) $certificateData['obj_id']);
                if (count($refIds) > 0) {
                    foreach ($refIds as $refId) {
                        if ($this->access->checkAccess('read', '', $refId)) {
                            $objectTitle = $this->uiRenderer->render(
                                $this->uiFactory->link()->standard($objectTitle, ilLink::_getLink($refId))
                            );
                            break;
                        }
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

            $deck = $this->uiFactory->deck($cards)->withNormalCardsSize();

            $uiComponents[] = $this->uiFactory->divider()->horizontal();

            $uiComponents[] = $deck;
        } else {
            ilUtil::sendInfo($this->language->txt('cert_currently_no_certs'));
        }

        $this->template->setContent($this->uiRenderer->render($uiComponents));
    }

    protected function getCurrentSortation() : string
    {
        $sorting = ilSession::get(self::SORTATION_SESSION_KEY);
        if (!array_key_exists($sorting, $this->sortationOptions)) {
            $sorting = $this->defaultSorting;
        }

        return $sorting;
    }

    /**
     * @throws ilWACException
     * @throws ilDateTimeException|JsonException
     */
    protected function applySortation() : void
    {
        $sorting = $this->request->getQueryParams()['sort_by'] ?? $this->defaultSorting;
        if (!array_key_exists($sorting, $this->sortationOptions)) {
            $sorting = $this->defaultSorting;
        }
        ilSession::set(self::SORTATION_SESSION_KEY, $sorting);

        $this->listCertificates();
    }

    /**
     * @throws ilException|JsonException
     */
    public function download() : void
    {
        global $DIC;

        $user = $DIC->user();
        $language = $DIC->language();

        $pdfGenerator = new ilPdfGenerator($this->userCertificateRepository, $this->certificateLogger);

        $userCertificateId = (int) $this->request->getQueryParams()['certificate_id'];

        try {
            $userCertificate = $this->userCertificateRepository->fetchCertificate($userCertificateId);
            if ($userCertificate->getUserId() !== $user->getId()) {
                throw new ilException(sprintf(
                    'User "%s" tried to access certificate: "%s"',
                    $user->getLogin(),
                    $userCertificateId
                ));
            }
        } catch (ilException $exception) {
            $this->certificateLogger->warning($exception->getMessage());
            ilUtil::sendFailure($language->txt('cert_error_no_access'));
            $this->listCertificates();
            return;
        }

        $pdfAction = new ilCertificatePdfAction(
            $this->certificateLogger,
            $pdfGenerator,
            new ilCertificateUtilHelper(),
            $this->language->txt('error_creating_certificate_pdf')
        );

        $pdfAction->downloadPdf($userCertificate->getUserId(), $userCertificate->getObjId());

        $this->listCertificates();
    }
}
