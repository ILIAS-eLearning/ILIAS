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

use ILIAS\Refinery\Factory;
use ILIAS\Filesystem\Filesystem;
use ILIAS\FileUpload\FileUpload;
use ILIAS\HTTP\Wrapper\WrapperFactory;
use ILIAS\Filesystem\Exception\IOException;
use ILIAS\ResourceStorage\Services as IRSS;
use ILIAS\Certificate\CertificateResourceHandler;
use ILIAS\Filesystem\Exception\FileNotFoundException;
use ILIAS\Filesystem\Exception\FileAlreadyExistsException;
use ILIAS\Certificate\File\ilCertificateTemplateStakeholder;

/**
 * GUI class to create PDF certificates
 * @author        Helmut Schottmüller <helmut.schottmueller@mac.com>
 * @version       $Id$
 * @ingroup       components/ILIAS
 * @ilCtrl_Calls  ilCertificateGUI: ilPropertyFormGUI
 */
class ilCertificateGUI
{
    protected ilCtrlInterface $ctrl;
    protected ilTree $tree;
    protected ILIAS $ilias;
    protected ilGlobalPageTemplate $tpl;
    protected ilLanguage $lng;
    protected int $ref_id;
    protected ilAccessHandler $access;
    protected ilToolbarGUI $toolbar;
    private readonly WrapperFactory $httpWrapper;
    private readonly Factory $refinery;
    private IRSS $irss;
    private ilCertificateTemplateStakeholder $stakeholder;
    private ilCertificateTemplateDatabaseRepository $certificate_repo;
    private readonly ilCertificateTemplateRepository $templateRepository;
    private readonly ilCertificateFormRepository $settingsFormFactory;
    private readonly ilXlsFoParser $xlsFoParser;
    private readonly ilCertificateDeleteAction $deleteAction;
    private readonly ilCertificateTemplateExportAction $exportAction;
    private readonly ilCertificateTemplatePreviewAction $previewAction;
    private readonly FileUpload $fileUpload;
    private readonly string $certificatePath;
    private readonly ilPageFormats $pageFormats;
    private readonly ilLogger $logger;
    private readonly ilObjCertificateSettings $global_certificate_settings;
    private readonly ilDBInterface $database;

    public function __construct(
        private readonly ilCertificatePlaceholderDescription $placeholderDescriptionObject,
        ilCertificatePlaceholderValues $placeholderValuesObject,
        private readonly int $objectId,
        string $certificatePath,
        ilCertificateFormRepository $settingsFormFactory = null,
        ilCertificateDeleteAction $deleteAction = null,
        ilCertificateTemplateRepository $templateRepository = null,
        ilPageFormats $pageFormats = null,
        ilXlsFoParser $xlsFoParser = null,
        ilCertificateTemplateExportAction $exportAction = null,
        ilCertificateTemplatePreviewAction $previewAction = null,
        FileUpload $fileUpload = null,
        private readonly ilSetting $settings = new ilSetting('certificate'),
        Filesystem $fileSystem = null,
        Filesystem $tmp_file_system = null
    ) {
        global $DIC;

        $this->httpWrapper = $DIC->http()->wrapper();
        $this->refinery = $DIC->refinery();
        $this->lng = $DIC['lng'];
        $this->tpl = $DIC['tpl'];
        $this->ctrl = $DIC['ilCtrl'];
        $this->ilias = $DIC['ilias'];

        $this->irss = $DIC->resourceStorage();
        $this->stakeholder = new ilCertificateTemplateStakeholder($this->objectId);

        $this->tree = $DIC['tree'];
        $this->access = $DIC['ilAccess'];
        $this->toolbar = $DIC['ilToolbar'];

        $this->global_certificate_settings = new ilObjCertificateSettings();
        $this->lng->loadLanguageModule('certificate');
        $this->lng->loadLanguageModule('cert');
        $this->lng->loadLanguageModule('trac');

        $this->ref_id = (int) $DIC->http()->wrapper()->query()->retrieve('ref_id', $DIC->refinery()->kindlyTo()->int());

        $this->logger = $DIC->logger()->cert();

        $this->settingsFormFactory = $settingsFormFactory ?? new ilCertificateSettingsFormRepository(
            $this->objectId,
            $certificatePath,
            $this->lng,
            $this->tpl,
            $this->ctrl,
            $this->access,
            $this->toolbar,
            $placeholderDescriptionObject,
            $DIC->ui()->factory(),
            $DIC->ui()->renderer()
        );
        $this->templateRepository = $templateRepository ?? new ilCertificateTemplateDatabaseRepository(
            $DIC->database(),
            $this->logger
        );
        $this->deleteAction = $deleteAction ?? new ilCertificateTemplateDeleteAction($this->templateRepository);
        $this->pageFormats = $pageFormats ?? new ilPageFormats($DIC->language());
        $this->xlsFoParser = $xlsFoParser ?? new ilXlsFoParser($DIC->settings(), $this->pageFormats);
        $this->exportAction = $exportAction ?? new ilCertificateTemplateExportAction(
            $this->objectId,
            $certificatePath,
            $this->templateRepository,
            $this->irss
        );
        $this->previewAction = $previewAction ?? new ilCertificateTemplatePreviewAction(
            $this->templateRepository,
            $placeholderValuesObject,
            $this->irss
        );
        $this->fileUpload = $fileUpload ?? $DIC->upload();
        $this->database = $DIC->database();
    }

    /**
     * @return mixed|null
     * @throws FileAlreadyExistsException
     * @throws FileNotFoundException
     * @throws IOException
     * @throws ilCtrlException
     * @throws ilDatabaseException
     * @throws ilException
     * @throws ilWACException
     */
    public function executeCommand()
    {
        $cmd = $this->ctrl->getCmd();
        $next_class = $this->ctrl->getNextClass($this);

        $ret = null;

        $cmd = $this->getCommand($cmd);
        switch ($next_class) {
            case 'ilpropertyformgui':
                $form = $this->getEditorForm();
                $this->ctrl->forwardCommand($form);

                break;
            default:
                $ret = $this->$cmd();

                break;
        }

        return $ret;
    }

    public function getCommand($cmd)
    {
        return $cmd;
    }

    public function certificateImport(): void
    {
        $this->certificateEditor();
    }

    public function certificatePreview(): void
    {
        try {
            $this->previewAction->createPreviewPdf($this->objectId);
        } catch (Exception) {
            $this->tpl->setOnScreenMessage('failure', $this->lng->txt('error_creating_certificate_pdf'));
            $this->certificateEditor();
        }
    }

    /**
     * Exports the certificate
     * @throws FileAlreadyExistsException
     * @throws FileNotFoundException
     * @throws IOException
     */
    public function certificateExportFO(): void
    {
        $this->exportAction->export();
    }

    public function certificateDelete(): void
    {
        // display confirmation message
        $cgui = new ilConfirmationGUI();
        $cgui->setFormAction($this->ctrl->getFormAction($this, 'certificateEditor'));
        $cgui->setHeaderText($this->lng->txt('certificate_confirm_deletion_text'));
        $cgui->setCancel($this->lng->txt('no'), 'certificateEditor');
        $cgui->setConfirm($this->lng->txt('yes'), 'certificateDeleteConfirm');

        $this->tpl->setContent($cgui->getHTML());
    }

    /**
     * Deletes the certificate and all its data
     */
    public function certificateDeleteConfirm(): void
    {
        $template = $this->templateRepository->fetchCurrentlyUsedCertificate($this->objectId);
        $templateId = $template->getId();

        $this->deleteAction->delete($templateId, $this->objectId);
        $this->ctrl->redirect($this, 'certificateEditor');
    }

    /**
     * Saves the certificate
     * @throws FileAlreadyExistsException
     * @throws FileNotFoundException
     * @throws IOException
     * @throws ilDatabaseException
     * @throws ilException
     * @throws ilWACException
     */
    public function certificateSave(): void
    {
        global $DIC;

        $form = $this->settingsFormFactory->createForm(
            $this
        );

        $form->setValuesByPost();

        $request = $DIC->http()->request();

        $formFields = $request->getParsedBody();

        $this->tpl->setVariable('ADM_CONTENT', $form->getHTML());

        $this->saveCertificate($form, $formFields, $this->objectId);
    }

    /**
     * @throws FileAlreadyExistsException
     * @throws FileNotFoundException
     * @throws IOException
     * @throws ilDatabaseException
     * @throws ilException
     * @throws ilObjectNotFoundException
     * @throws ilWACException
     */
    public function certificateUpload(): void
    {
        $this->certificateEditor();
    }

    /**
     * Shows the certificate editor for ILIAS tests
     * @throws FileAlreadyExistsException
     * @throws FileNotFoundException
     * @throws IOException
     * @throws ilDatabaseException
     * @throws ilException
     * @throws ilObjectNotFoundException
     * @throws ilWACException
     */
    public function certificateEditor(): void
    {
        $form = $this->getEditorForm();
        $enabledGlobalLearningProgress = ilObjUserTracking::_enabledLearningProgress();

        $messageBoxHtml = '';
        if ($enabledGlobalLearningProgress) {
            $objectLearningProgressSettings = new ilLPObjSettings($this->objectId);
            $mode = $objectLearningProgressSettings->getMode();

            /** @var ilObject $object */
            $object = ilObjectFactory::getInstanceByObjId($this->objectId);
            if (ilLPObjSettings::LP_MODE_DEACTIVATED === $mode && $object->getType() !== 'crs') {
                global $DIC;

                $renderer = $DIC->ui()->renderer();
                $messageBox = $DIC->ui()
                    ->factory()
                    ->messageBox()
                    ->info($this->lng->txt('learning_progress_deactivated'))
                ;

                $messageBoxHtml = $renderer->render($messageBox);
                $form->clearCommandButtons();
            }
        }

        $formHtml = $form->getHTML();

        $this->tpl->setVariable('ADM_CONTENT', $messageBoxHtml . $formHtml);
    }

    /**
     * @throws FileAlreadyExistsException
     * @throws FileNotFoundException
     * @throws IOException
     * @throws ilDatabaseException
     * @throws ilException
     * @throws ilWACException
     */
    private function getEditorForm(): ilPropertyFormGUI
    {
        $certificateTemplate = $this->templateRepository->fetchCurrentlyUsedCertificate($this->objectId);

        $form = $this->settingsFormFactory->createForm(
            $this
        );

        $formFields = $this->createFormatArray($certificateTemplate);

        $formFields['active'] = $certificateTemplate->isCurrentlyActive();

        $form->setValuesByArray($formFields);

        return $form;
    }

    private function saveCertificate(ilPropertyFormGUI $form, array $form_fields, int $objId): void
    {
        $certificate_handler = new CertificateResourceHandler(
            new ilUserCertificateRepository($this->database),
            new ilCertificateTemplateDatabaseRepository($this->database),
            $this->irss,
            $this->global_certificate_settings,
            $this->stakeholder,
        );
        $current_template = $this->templateRepository->fetchPreviousCertificate($objId);
        $currentVersion = $current_template->getVersion();
        $nextVersion = $currentVersion + 1;
        $current_background_rid = $this->irss->manageContainer()->find(
            $current_template->getBackgroundImageIdentification()
        );
        $current_thumbnail_rid = $this->irss->manageContainer()->find(
            $current_template->getThumbnailImageIdentification()
        );

        $should_delete_background =
            $this->httpWrapper->post()->retrieve(
                'background_delete',
                $this->refinery->byTrying([
                    $this->refinery->kindlyTo()->bool(),
                    $this->refinery->always(false)
                ])
            );
        $should_delete_thumbnail =
            $this->httpWrapper->post()->retrieve(
                'certificate_card_thumbnail_image_delete',
                $this->refinery->byTrying([
                    $this->refinery->kindlyTo()->bool(),
                    $this->refinery->always(false)
                ])
            );

        $new_background_rid = $current_background_rid && !$should_delete_background ? $current_background_rid :
            $this->global_certificate_settings->getBackgroundImageIdentification();
        $new_thumbnail_rid = !$should_delete_thumbnail ? $current_thumbnail_rid : null;
        if ($form->checkInput()) {
            try {
                $this->settingsFormFactory->save($form_fields);

                $templateValues = $this->placeholderDescriptionObject->getPlaceholderDescriptions();

                if ($this->fileUpload->hasUploads()) {
                    if (!$this->fileUpload->hasBeenProcessed()) {
                        $this->fileUpload->process();
                    }
                    $new_background = $form->getInput('background')['tmp_name'] ?? '';
                    $new_thumbnail_image = $form->getInput('certificate_card_thumbnail_image')['tmp_name'] ?? '';
                    $results = $this->fileUpload->getResults();

                    if ($new_background !== '') {
                        $new_background_rid = $this->irss->manage()->upload(
                            $results[$new_background],
                            $this->stakeholder
                        );
                    }

                    if ($new_thumbnail_image !== '') {
                        $new_thumbnail_rid = $this->irss->manage()->upload(
                            $results[$new_thumbnail_image],
                            $this->stakeholder
                        );
                    }
                }

                $jsonEncodedTemplateValues = json_encode($templateValues, JSON_THROW_ON_ERROR);

                $xslfo = $this->xlsFoParser->parse($form_fields);
                $newHashValue = hash(
                    'sha256',
                    implode('', [
                        $xslfo,
                        isset($new_background_rid) ? $this->irss->manage()->getResource(
                            $new_background_rid
                        )->getStorageID() : '',
                        $jsonEncodedTemplateValues,
                        isset($new_thumbnail_rid) ? $this->irss->manage()->getResource(
                            $new_thumbnail_rid
                        )->getStorageID() : '',
                    ])
                );

                $active = (bool) ($form_fields['active'] ?? false);

                if ($newHashValue !== $current_template->getCertificateHash()) {
                    $certificateTemplate = new ilCertificateTemplate(
                        $objId,
                        ilObject::_lookupType($objId),
                        $xslfo,
                        $newHashValue,
                        $jsonEncodedTemplateValues,
                        $nextVersion,
                        ILIAS_VERSION_NUMERIC,
                        time(),
                        $active,
                        isset($new_background_rid) ? $new_background_rid->serialize() : '',
                        isset($new_thumbnail_rid) ? $new_thumbnail_rid->serialize() : '',
                    );
                    $this->templateRepository->save($certificateTemplate);

                    if ($current_background_rid) {
                        $certificate_handler->handleResourceChange($current_background_rid);
                    }
                    if ($current_thumbnail_rid) {
                        $certificate_handler->handleResourceChange($current_thumbnail_rid);
                    }

                    $this->tpl->setOnScreenMessage('success', $this->lng->txt('saved_successfully'), true);
                    $this->ctrl->redirect($this, 'certificateEditor');
                }

                if (
                    $current_template->getId() !== null &&
                    $current_template->isCurrentlyActive() !== $active
                ) {
                    $this->templateRepository->updateActivity($current_template, $active);
                    $this->tpl->setOnScreenMessage('info', $this->lng->txt('certificate_change_active_status'), true);
                    $this->ctrl->redirect($this, 'certificateEditor');
                }

                $this->tpl->setOnScreenMessage('info', $this->lng->txt('certificate_same_not_saved'), true);
                $this->ctrl->redirect($this, 'certificateEditor');
            } catch (Exception $e) {
                $this->tpl->setOnScreenMessage(
                    'failure',
                    $e->getMessage()
                );
                $this->logger->error($e->getTraceAsString());
            }
        }

        $form->setValuesByPost();

        $this->tpl->setVariable('ADM_CONTENT', $form->getHTML());
    }

    private function createFormatArray(ilCertificateTemplate $certificateTemplate): array
    {
        if ('' === $certificateTemplate->getCertificateHash()) {
            $format = $this->settings->get('pageformat', '');
            $formats = $this->pageFormats->fetchPageFormats();

            return [
                'pageformat' => $format,
                'pagewidth' => $formats['width'] ?? '',
                'pageheight' => $formats['height'] ?? '',
                'margin_body_top' => ilPageFormats::DEFAULT_MARGIN_BODY_TOP,
                'margin_body_right' => ilPageFormats::DEFAULT_MARGIN_BODY_RIGHT,
                'margin_body_bottom' => ilPageFormats::DEFAULT_MARGIN_BODY_BOTTOM,
                'margin_body_left' => ilPageFormats::DEFAULT_MARGIN_BODY_LEFT,
                'certificate_text' => $certificateTemplate->getCertificateContent()
            ];
        }

        return $this->settingsFormFactory->fetchFormFieldData($certificateTemplate->getCertificateContent());
    }
}
