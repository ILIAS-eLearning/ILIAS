<?php

declare(strict_types=1);

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

use ILIAS\Filesystem\Filesystem;
use ILIAS\Filesystem\Exception\FileAlreadyExistsException;
use ILIAS\Filesystem\Exception\FileNotFoundException;
use ILIAS\Filesystem\Exception\IOException;
use ILIAS\HTTP\Wrapper\WrapperFactory;
use ILIAS\Refinery\Factory as Refinery;
use ILIAS\UI\Factory as UiFactory;
use ILIAS\UI\Renderer as UiRenderer;

/**
 * @author  Niels Theen <ntheen@databay.de>
 */
class ilCertificateSettingsFormRepository implements ilCertificateFormRepository
{
    private readonly UiFactory $ui_factory;
    private readonly UiRenderer $ui_renderer;
    private readonly ilPageFormats $pageFormats;
    private readonly ilCertificateTemplateImportAction $importAction;
    private readonly ilCertificateTemplateRepository $templateRepository;
    private readonly ilCertificateBackgroundImageFileService $backGroundImageFileService;
    private readonly WrapperFactory $httpWrapper;
    private readonly Refinery $refinery;

    public function __construct(
        private readonly int $objectId,
        string $certificatePath,
        private readonly bool $hasAdditionalElements,
        private readonly ilLanguage $language,
        private readonly ilCtrlInterface $ctrl,
        private readonly ilAccessHandler $access,
        private readonly ilToolbarGUI $toolbar,
        private readonly ilCertificatePlaceholderDescription $placeholderDescriptionObject,
        ?UiFactory $ui_factory = null,
        ?UiRenderer $ui_renderer = null,
        ?ilPageFormats $pageFormats = null,
        private readonly ilFormFieldParser $formFieldParser = new ilFormFieldParser(),
        ?ilCertificateTemplateImportAction $importAction = null,
        ?ilLogger $logger = null,
        ?ilCertificateTemplateRepository $templateRepository = null,
        ?Filesystem $filesystem = null,
        ?ilCertificateBackgroundImageFileService $backgroundImageFileService = null
    ) {
        global $DIC;

        $this->httpWrapper = $DIC->http()->wrapper();
        $this->refinery = $DIC->refinery();

        $this->ui_factory = $ui_factory ?? $DIC->ui()->factory();
        $this->ui_renderer = $ui_renderer ?? $DIC->ui()->renderer();
        $this->pageFormats = $pageFormats ?? new ilPageFormats($language);
        $this->importAction = $importAction ?? new ilCertificateTemplateImportAction(
            $objectId,
            $certificatePath,
            $placeholderDescriptionObject,
            ($logger ?? $DIC->logger()->cert()),
            $DIC->filesystem()->web()
        );
        $this->templateRepository = $templateRepository ?? new ilCertificateTemplateDatabaseRepository(
            $DIC->database(),
            ($logger ?? $DIC->logger()->cert())
        );
        $this->backGroundImageFileService = $backgroundImageFileService ?? new ilCertificateBackgroundImageFileService(
            $certificatePath,
            ($filesystem ?? $DIC->filesystem()->web())
        );
    }

    /**
     * @throws FileAlreadyExistsException
     * @throws FileNotFoundException
     * @throws IOException
     * @throws ilDatabaseException
     * @throws ilException
     * @throws ilWACException
     */
    public function createForm(ilCertificateGUI $certificateGUI): ilPropertyFormGUI
    {
        $certificateTemplate = $this->templateRepository->fetchCurrentlyUsedCertificate($this->objectId);

        $command = $this->ctrl->getCmd();

        $form = new ilPropertyFormGUI();
        $form->setPreventDoubleSubmission(false);
        $form->setFormAction($this->ctrl->getFormAction($certificateGUI));
        $form->setTitle($this->language->txt('cert_form_sec_availability'));
        $form->setMultipart(true);
        $form->setTableWidth('100%');
        $form->setId('certificate');

        $active = new ilCheckboxInputGUI($this->language->txt('active'), 'active');
        $form->addItem($active);

        $import = new ilFileInputGUI($this->language->txt('import'), 'certificate_import');
        $import->setRequired(false);
        $import->setSuffixes(['zip']);

        // handle the certificate import
        if (!empty($_FILES['certificate_import']['name']) && $import->checkInput()) {
            $result = $this->importAction->import(
                $_FILES['certificate_import']['tmp_name'],
                $_FILES['certificate_import']['name']
            );
            if ($result) {
                $this->ctrl->redirect($certificateGUI, 'certificateEditor');
            } else {
                $import->setAlert($this->language->txt('certificate_error_import'));
            }
        }
        $form->addItem($import);

        $formSection = new ilFormSectionHeaderGUI();
        $formSection->setTitle($this->language->txt('cert_form_sec_layout'));
        $form->addItem($formSection);

        $pageformat = new ilRadioGroupInputGUI($this->language->txt("certificate_page_format"), "pageformat");
        $pageformats = $this->pageFormats->fetchPageFormats();

        foreach ($pageformats as $format) {
            $option = new ilRadioOption($format["name"], $format["value"]);

            if (strcmp($format["value"], "custom") === 0) {
                $pageheight = new ilTextInputGUI($this->language->txt("certificate_pageheight"), "pageheight");
                $pageheight->setSize(6);
                $pageheight->setValidationRegexp('/^(([1-9]+|([1-9]+[0]*[\.,]{0,1}[\d]+))|(0[\.,](0*[1-9]+[\d]*)))(cm|mm|in|pt|pc|px|em)$/is');
                $pageheight->setInfo($this->language->txt("certificate_unit_description"));
                $pageheight->setRequired(true);
                $option->addSubItem($pageheight);

                $pagewidth = new ilTextInputGUI($this->language->txt("certificate_pagewidth"), "pagewidth");
                $pagewidth->setSize(6);
                $pagewidth->setValidationRegexp('/^(([1-9]+|([1-9]+[0]*[\.,]{0,1}[\d]+))|(0[\.,](0*[1-9]+[\d]*)))(cm|mm|in|pt|pc|px|em)$/is');
                $pagewidth->setInfo($this->language->txt("certificate_unit_description"));
                $pagewidth->setRequired(true);
                $option->addSubItem($pagewidth);
            }

            $pageformat->addOption($option);
        }

        $pageformat->setRequired(true);

        if (strcmp($command, 'certificateSave') === 0) {
            $pageformat->checkInput();
        }

        $form->addItem($pageformat);

        $bgimage = new ilImageFileInputGUI($this->language->txt('certificate_background_image'), 'background');
        $bgimage->setRequired(false);
        $bgimage->setUseCache(false);

        $bgimage->setAllowDeletion(true);
        if (!$this->backGroundImageFileService->hasBackgroundImage($certificateTemplate)) {
            if (ilObjCertificateSettingsAccess::hasBackgroundImage()) {
                ilWACSignedPath::setTokenMaxLifetimeInSeconds(15);
                $imagePath = ilWACSignedPath::signFile(ilObjCertificateSettingsAccess::getBackgroundImageThumbPathWeb());
                $bgimage->setImage($imagePath);
                $bgimage->setAllowDeletion(false);
            }
        } else {
            ilWACSignedPath::setTokenMaxLifetimeInSeconds(15);

            $thumbnailPath = $this->backGroundImageFileService->getBackgroundImageThumbPath();

            if (!is_file($thumbnailPath)) {
                $thumbnailPath = ilObjCertificateSettingsAccess::getBackgroundImageThumbPath();
                $bgimage->setAllowDeletion(false);
            }
            $imagePath = ilWACSignedPath::signFile($thumbnailPath);
            $bgimage->setImage($imagePath);
        }

        $form->addItem($bgimage);

        $thumbnailImage = new ilImageFileInputGUI(
            $this->language->txt('certificate_card_thumbnail_image'),
            'certificate_card_thumbnail_image'
        );
        $thumbnailImage->setRequired(false);
        $thumbnailImage->setUseCache(false);
        $thumbnailImage->setSuffixes(['svg']);

        $allowThumbnailDeletion = false;

        $cardThumbnailImagePath = $certificateTemplate->getThumbnailImagePath();
        if ('' !== $cardThumbnailImagePath) {
            $presentationThumbnailImagePath = CLIENT_WEB_DIR . $cardThumbnailImagePath;
            $thumbnailImage->setImage(ilWACSignedPath::signFile($presentationThumbnailImagePath));
            $allowThumbnailDeletion = true;
        }

        $thumbnailImage->setAllowDeletion($allowThumbnailDeletion);

        $form->addItem($thumbnailImage);

        $rect = new ilCSSRectInputGUI($this->language->txt('certificate_margin_body'), 'margin_body');
        $rect->setRequired(true);
        $rect->setUseUnits(true);
        $rect->setInfo($this->language->txt('certificate_unit_description'));

        if (strcmp($command, 'certificateSave') === 0) {
            $rect->checkInput();
        }

        $form->addItem($rect);

        $certificate = new ilTextAreaInputGUI($this->language->txt('certificate_text'), 'certificate_text');
        $certificate->removePlugin('ilimgupload');
        $certificate->setRequired(true);
        $certificate->setRows(20);
        $certificate->setCols(80);

        $placeholderHtmlDescription = $this->placeholderDescriptionObject->createPlaceholderHtmlDescription();

        $placeholderDescriptionInHtml = $placeholderHtmlDescription;

        $certificate->setInfo($placeholderDescriptionInHtml);

        $certificate->setUseRte(true, '3.4.7');

        $tags = [
            'br',
            'em',
            'font',
            'li',
            'ol',
            'p',
            'span',
            'strong',
            'u',
            'ul'
        ];

        $certificate->setRteTags($tags);

        if (strcmp($command, 'certificateSave') === 0) {
            $certificate->checkInput();
        }

        $form->addItem($certificate);

        if ($this->hasAdditionalElements) {
            $formSection = new ilFormSectionHeaderGUI();
            $formSection->setTitle($this->language->txt('cert_form_sec_add_features'));
            $form->addItem($formSection);
        }

        if ($this->access->checkAccess(
            'write',
            '',
            $this->httpWrapper->query()->retrieve('ref_id', $this->refinery->kindlyTo()->int())
        )) {
            if ($certificateTemplate->isCurrentlyActive()) {
                $preview_button = $this->ui_factory->button()->standard(
                    $this->language->txt('certificate_preview'),
                    $this->ctrl->getLinkTarget($certificateGUI, 'certificatePreview')
                );
                $export_button = $this->ui_factory->button()->standard(
                    $this->language->txt('certificate_export'),
                    $this->ctrl->getLinkTarget($certificateGUI, 'certificateExportFO')
                );
                $delete_button = $this->ui_factory->button()->standard(
                    $this->language->txt('delete'),
                    $this->ctrl->getLinkTarget($certificateGUI, 'certificateDelete')
                );

                $this->toolbar->addStickyItem($preview_button);
                $this->toolbar->addComponent($export_button);
                $this->toolbar->addComponent($delete_button);
            }
            $form->addCommandButton("certificateSave", $this->language->txt("save"));
        }

        return $form;
    }

    public function save(array $formFields): void
    {
    }

    /**
     * @return array{pageformat: string, pagewidth: mixed, pageheight: mixed, margin_body_top: mixed, margin_body_right: mixed, margin_body_bottom: mixed, margin_body_left: mixed, certificate_text: string}
     */
    public function fetchFormFieldData(string $content): array
    {
        return $this->formFieldParser->fetchDefaultFormFields($content);
    }
}
