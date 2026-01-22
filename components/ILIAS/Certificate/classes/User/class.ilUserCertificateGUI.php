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
use Psr\Http\Message\ServerRequestInterface;
use ILIAS\ResourceStorage\Services as IRSS;
use ILIAS\ResourceStorage\Identification\ResourceIdentification;

/**
 * @ilCtrl_IsCalledBy ilUserCertificateGUI: ilAchievementsGUI
 */
class ilUserCertificateGUI
{
    final public const string SORTATION_SESSION_KEY = 'my_certificates_sorting';

    private readonly ilGlobalTemplateInterface $template;
    private readonly ilCtrlInterface $ctrl;
    private readonly ilLanguage $language;
    private readonly ilUserCertificateRepository $user_certificate_repo;
    private readonly ilObjUser $user;
    private readonly ServerRequestInterface $request;
    private readonly ilLogger $logger;
    private readonly Factory $ui_factory;
    private readonly Renderer $ui_renderer;
    private readonly ilAccessHandler $access;
    private readonly ilHelpGUI $help;
    private readonly ilDBInterface $db;

    /**
     * @var array{"title_ASC":string,"title_DESC":string,"date_ASC":string,"date_DESC":string}
     */
    protected array $sortation_options = [
        'title_ASC' => 'cert_sortable_by_title_asc',
        'title_DESC' => 'cert_sortable_by_title_desc',
        'date_ASC' => 'cert_sortable_by_issue_date_asc',
        'date_DESC' => 'cert_sortable_by_issue_date_desc',
    ];
    protected string $default_sorting = 'date_DESC';

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
        ?ilHelpGUI $help = null,
        ?ilDBInterface $db = null,
        private ?IRSS $irss = null
    ) {
        global $DIC;

        $this->template = $template ?? $DIC->ui()->mainTemplate();
        $this->ctrl = $ctrl ?? $DIC->ctrl();
        $this->user = $user ?? $DIC->user();
        $this->language = $language ?? $DIC->language();
        $this->request = $request ?? $DIC->http()->request();
        $this->logger = $certificateLogger ?? $DIC->logger()->cert();
        $this->ui_factory = $uiFactory ?? $DIC->ui()->factory();
        $this->ui_renderer = $uiRenderer ?? $DIC->ui()->renderer();
        $this->access = $access ?? $DIC->access();
        $this->user_certificate_repo = $userCertificateRepository ?? new ilUserCertificateRepository(null, $this->logger);
        $this->help = $help ?? $DIC->help();
        $this->db = $db ?? $DIC->database();
        $this->irss = $irss ?? $DIC->resourceStorage();

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

        $this->template->setPermanentLink('cert', null, 'list');

        $provider = new ilUserCertificateTableProvider(
            $this->db,
            $this->logger,
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

            foreach ($this->sortation_options as $fieldAndDirection => $lngVariable) {
                $sortationOptions[$fieldAndDirection] = $this->language->txt($lngVariable);
            }

            $sortViewControl = $this->ui_factory
                ->viewControl()
                ->sortation($sortationOptions, $sorting)
                ->withTargetURL($this->ctrl->getLinkTarget($this, 'applySortation'), 'sort_by');

            $uiComponents[] = $sortViewControl;

            foreach ($data['items'] as $certificateData) {
                $tile_image_identification = $certificateData['tile_image_ident'] ?? '';
                $imagePath = '';
                $tile_image_rid = $this->irss->manage()->find($tile_image_identification);
                if ($tile_image_rid instanceof ResourceIdentification) {
                    $imagePath = $this->irss->consume()->src($tile_image_rid)->getSrc(true);
                }

                if ($imagePath === '') {
                    $imagePath = ilUtil::getImagePath('standard/icon_cert.svg');
                }

                $cardImage = $this->ui_factory->image()->standard(
                    $imagePath,
                    $certificateData['title']
                );

                $sections = [];

                if ($certificateData['description'] !== '') {
                    $sections[] = $this->ui_factory->listing()->descriptive([
                        $this->language->txt('cert_description_label') => $certificateData['description']
                    ]);
                }

                $oldDatePresentationStatus = ilDatePresentation::useRelativeDates();
                ilDatePresentation::setUseRelativeDates(true);
                $sections[] = $this->ui_factory->listing()->descriptive([
                    $this->language->txt('cert_issued_on_label') => ilDatePresentation::formatDate(
                        new ilDateTime($certificateData['date'], IL_CAL_UNIX)
                    )
                ]);
                ilDatePresentation::setUseRelativeDates($oldDatePresentationStatus);

                $objectTypeIcon = $this->ui_factory
                    ->symbol()
                    ->icon()
                    ->standard($certificateData['obj_type'], $certificateData['obj_type'])
                ;

                $objectTitle = $certificateData['title'];
                $refIds = ilObject::_getAllReferences((int) $certificateData['obj_id']);
                foreach ($refIds as $refId) {
                    if ($this->access->checkAccess('read', '', $refId)) {
                        $objectTitle = $this->ui_renderer->render(
                            $this->ui_factory->link()->standard($objectTitle, ilLink::_getLink($refId))
                        );

                        break;
                    }
                }

                $sections[] = $this->ui_factory->listing()->descriptive([$this->language->txt('cert_object_label') => implode(
                    '',
                    [
                        $this->ui_renderer->render($objectTypeIcon),
                        $objectTitle
                    ]
                )
                ]);

                $this->ctrl->setParameter($this, 'certificate_id', $certificateData['id']);
                $downloadHref = $this->ctrl->getLinkTarget($this, 'download');
                $this->ctrl->clearParameters($this);
                $sections[] = $this->ui_factory->button()->standard('Download', $downloadHref);

                $card = $this->ui_factory
                    ->card()
                    ->standard($certificateData['title'], $cardImage)
                    ->withSections($sections)
                ;

                $cards[] = $card;
            }

            $deck = $this->ui_factory->deck($cards)->withSmallCardsSize();

            $uiComponents[] = $this->ui_factory->divider()->horizontal();

            $uiComponents[] = $deck;
        } else {
            $this->template->setOnScreenMessage('info', $this->language->txt('cert_currently_no_certs'));
        }

        $this->template->setContent($this->ui_renderer->render($uiComponents));
    }

    protected function getCurrentSortation(): string
    {
        $sorting = ilSession::get(self::SORTATION_SESSION_KEY);
        if (!array_key_exists($sorting, $this->sortation_options)) {
            $sorting = $this->default_sorting;
        }

        return $sorting;
    }

    /**
     * @throws ilWACException
     * @throws ilDateTimeException
     */
    protected function applySortation(): void
    {
        $sorting = $this->request->getQueryParams()['sort_by'] ?? $this->default_sorting;
        if (!array_key_exists($sorting, $this->sortation_options)) {
            $sorting = $this->default_sorting;
        }
        ilSession::set(self::SORTATION_SESSION_KEY, $sorting);

        $this->listCertificates();
    }

    /**
     * @throws ilException
     */
    public function download(): void
    {
        $certificate_id = (int) $this->request->getQueryParams()['certificate_id'];

        try {
            $certificate = $this->user_certificate_repo->fetchCertificate($certificate_id);
            if ($certificate->getUserId() !== $this->user->getId()) {
                throw new ilException(
                    sprintf('User "%s" tried to access certificate: "%s"', $this->user->getLogin(), $certificate_id)
                );
            }
        } catch (ilException $exception) {
            $this->logger->warning($exception->getMessage());
            $this->template->setOnScreenMessage('failure', $this->language->txt('cert_error_no_access'));
            $this->listCertificates();

            return;
        }

        $action = (new ilCertificatePdfAction(
            (new ilPdfGenerator($this->user_certificate_repo))->withLogger($this->logger),
            new ilCertificateUtilHelper(),
            $this->language->txt('error_creating_certificate_pdf')
        ))->withLogger($this->logger);
        $action->downloadPdf($certificate->getUserId(), $certificate->getObjId());

        $this->listCertificates();
    }
}
