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

use ILIAS\Filesystem\Filesystem;
use ILIAS\Filesystem\Exception\FileAlreadyExistsException;
use ILIAS\Filesystem\Exception\FileNotFoundException;
use ILIAS\Filesystem\Exception\IOException;
use ILIAS\HTTP\Wrapper\WrapperFactory;
use ILIAS\Refinery\Factory;

/**
 * @author  Niels Theen <ntheen@databay.de>
 */
class ilCertificateSettingsFormRepository implements ilCertificateFormRepository
{
    private int $objectId;
    private ilLanguage $language;
    private ilCtrlInterface $ctrl;
    private ilAccess $access;
    private ilToolbarGUI $toolbar;
    private ilCertificatePlaceholderDescription $placeholderDescriptionObject;
    private ilPageFormats $pageFormats;
    private ilFormFieldParser $formFieldParser;
    private ilCertificateTemplateImportAction $importAction;
    private ilCertificateTemplateRepository $templateRepository;
    private bool $hasAdditionalElements;
    private ilCertificateBackgroundImageFileService $backGroundImageFileService;
    private WrapperFactory $httpWrapper;
    private Factory $refinery;
    private ilObjCertificateSettings $global_certificate_settings;

    public function __construct(
        int $objectId,
        string $certificatePath,
        bool $hasAdditionalElements,
        ilLanguage $language,
        ilCtrlInterface $ctrl,
        ilAccess $access,
        ilToolbarGUI $toolbar,
        ilCertificatePlaceholderDescription $placeholderDescriptionObject,
        ?ilPageFormats $pageFormats = null,
        ?ilFormFieldParser $formFieldParser = null,
        ?ilCertificateTemplateImportAction $importAction = null,
        ?ilLogger $logger = null,
        ?ilCertificateTemplateRepository $templateRepository = null,
        ?Filesystem $filesystem = null,
        ?ilCertificateBackgroundImageFileService $backgroundImageFileService = null
    ) {
        global $DIC;
        $this->httpWrapper = $DIC->http()->wrapper();
        $this->refinery = $DIC->refinery();
        $this->objectId = $objectId;
        $this->language = $language;
        $this->ctrl = $ctrl;
        $this->access = $access;
        $this->toolbar = $toolbar;
        $this->placeholderDescriptionObject = $placeholderDescriptionObject;
        $this->hasAdditionalElements = $hasAdditionalElements;
        $this->global_certificate_settings = new ilObjCertificateSettings();

        $database = $DIC->database();

        if (null === $logger) {
            $logger = $logger = $DIC->logger()->cert();
        }

        if (null === $pageFormats) {
            $pageFormats = new ilPageFormats($language);
        }
        $this->pageFormats = $pageFormats;

        if (null === $formFieldParser) {
            $formFieldParser = new ilFormFieldParser();
        }
        $this->formFieldParser = $formFieldParser;

        if (null === $importAction) {
            $importAction = new ilCertificateTemplateImportAction(
                $objectId,
                $certificatePath,
                $placeholderDescriptionObject,
                $logger,
                $DIC->filesystem()->web()
            );
        }
        $this->importAction = $importAction;

        if (null === $templateRepository) {
            $templateRepository = new ilCertificateTemplateDatabaseRepository($database, $logger);
        }
        $this->templateRepository = $templateRepository;

        if (null === $filesystem) {
            $filesystem = $DIC->filesystem()->web();
        }

        if (null === $backgroundImageFileService) {
            $backgroundImageFileService = new ilCertificateBackgroundImageFileService(
                $certificatePath,
                $filesystem
            );
        }
        $this->backGroundImageFileService = $backgroundImageFileService;
    }

    /**
     * @param ilCertificateGUI $certificateGUI
     * @return ilPropertyFormGUI
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
        $form->setTitle($this->language->txt("cert_form_sec_availability"));
        $form->setMultipart(true);
        $form->setTableWidth("100%");
        $form->setId("certificate");

        $active = new ilCheckboxInputGUI($this->language->txt("active"), "active");
        $form->addItem($active);

        $import = new ilFileInputGUI($this->language->txt("import"), "certificate_import");
        $import->setRequired(false);
        $import->setSuffixes(["zip"]);

        // handle the certificate import
        if (!empty($_FILES["certificate_import"]["name"]) && $import->checkInput()) {
            $result = $this->importAction->import(
                $_FILES["certificate_import"]["tmp_name"],
                $_FILES["certificate_import"]["name"]
            );
            if ($result === false) {
                $import->setAlert($this->language->txt("certificate_error_import"));
            } else {
                $this->ctrl->redirect($certificateGUI, "certificateEditor");
            }
        }
        $form->addItem($import);

        $formSection = new ilFormSectionHeaderGUI();
        $formSection->setTitle($this->language->txt("cert_form_sec_layout"));
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

        if (strcmp($command, "certificateSave") === 0) {
            $pageformat->checkInput();
        }

        $form->addItem($pageformat);

        $bgimage = new ilImageFileInputGUI($this->language->txt("certificate_background_image"), "background");
        $bgimage->setRequired(false);
        $bgimage->setUseCache(false);

        $bgimage->setAllowDeletion(true);
        if (!$this->backGroundImageFileService->hasBackgroundImage($certificateTemplate)) {
            if ($this->global_certificate_settings->hasBackgroundImage()) {
                ilWACSignedPath::setTokenMaxLifetimeInSeconds(15);
                $imagePath = ilWACSignedPath::signFile($this->global_certificate_settings->getBackgroundImageThumbPathWeb());
                $bgimage->setImage($imagePath);
                $bgimage->setAllowDeletion(false);
            }
        } else {
            ilWACSignedPath::setTokenMaxLifetimeInSeconds(15);

            $thumbnail_path = $this->backGroundImageFileService->getBackgroundImageThumbPath();

            if (!is_file($thumbnail_path)) {
                //Trying if it uses default image path
                $thumbnail_path = CLIENT_WEB_DIR . $certificateTemplate->getBackgroundImagePath() . '.thumb.jpg';
                if (!is_file($thumbnail_path)) {
                    //Trying to use global default image
                    $thumbnail_path = $this->global_certificate_settings->getDefaultBackgroundImageThumbPath();
                    if (!is_file($thumbnail_path)) {
                        //No image global default configured
                        $thumbnail_path = '';
                    }
                }
                $bgimage->setALlowDeletion(false);
            }
            $imagePath = ilWACSignedPath::signFile($thumbnail_path);
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

        $rect = new ilCSSRectInputGUI($this->language->txt("certificate_margin_body"), "margin_body");
        $rect->setRequired(true);
        $rect->setUseUnits(true);
        $rect->setInfo($this->language->txt("certificate_unit_description"));

        if (strcmp($command, "certificateSave") === 0) {
            $rect->checkInput();
        }

        $form->addItem($rect);

        $certificate = new ilTextAreaInputGUI($this->language->txt("certificate_text"), "certificate_text");
        $certificate->setInfo($this->language->txt('certificate_text_info'));
        $certificate->removePlugin('ilimgupload');
        $certificate->setRequired(true);
        $certificate->setRows(20);
        $certificate->setCols(80);

        $placeholderHtmlDescription = $this->placeholderDescriptionObject->createPlaceholderHtmlDescription();

        $placeholderDescriptionInHtml = $placeholderHtmlDescription;

        $certificate->setInfo($placeholderDescriptionInHtml);

        $certificate->setUseRte(true, '3.4.7');

        $tags = [
            "br",
            "em",
            "font",
            "li",
            "ol",
            "p",
            "span",
            "strong",
            "u",
            "ul"
        ];

        $certificate->setRteTags($tags);

        if (strcmp($command, "certificateSave") === 0) {
            $certificate->checkInput();
        }

        $form->addItem($certificate);

        if (true === $this->hasAdditionalElements) {
            $formSection = new ilFormSectionHeaderGUI();
            $formSection->setTitle($this->language->txt("cert_form_sec_add_features"));
            $form->addItem($formSection);
        }

        if ($this->access->checkAccess(
            "write",
            "",
            $this->httpWrapper->query()->retrieve("ref_id", $this->refinery->kindlyTo()->int())
        )) {
            if ($certificateTemplate->isCurrentlyActive()) {
                $this->toolbar->setFormAction($this->ctrl->getFormAction($certificateGUI));

                $preview = ilSubmitButton::getInstance();
                $preview->setCaption('certificate_preview');
                $preview->setCommand('certificatePreview');
                $this->toolbar->addStickyItem($preview);

                $export = ilSubmitButton::getInstance();
                $export->setCaption('certificate_export');
                $export->setCommand('certificateExportFO');
                $this->toolbar->addButtonInstance($export);

                $delete = ilSubmitButton::getInstance();
                $delete->setCaption('delete');
                $delete->setCommand('certificateDelete');
                $this->toolbar->addButtonInstance($delete);
            }
            $form->addCommandButton("certificateSave", $this->language->txt("save"));
        }

        return $form;
    }

    public function save(array $formFields): void
    {
    }

    /**
     * @param string $content
     * @return array
     */
    public function fetchFormFieldData(string $content): array
    {
        return $this->formFieldParser->fetchDefaultFormFields($content);
    }
}
