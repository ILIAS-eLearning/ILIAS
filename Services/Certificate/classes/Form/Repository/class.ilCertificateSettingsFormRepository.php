<?php
/* Copyright (c) 1998-2018 ILIAS open source, Extended GPL, see docs/LICENSE */

/**
 * @author  Niels Theen <ntheen@databay.de>
 */
class ilCertificateSettingsFormRepository implements ilCertificateFormRepository
{
    /**
     * @var int
     */
    private $objectId;

    /**
     * @var ilLanguage
     */
    private $language;

    /**
     * @var ilTemplate
     */
    private $template;

    /**
     * @var ilCtrl
     */
    private $controller;

    /**
     * @var ilAccess
     */
    private $access;

    /**
     * @var ilToolbarGUI
     */
    private $toolbar;

    /**
     * @var ilCertificatePlaceholderDescription
     */
    private $placeholderDescriptionObject;

    /**
     * @var ilPageFormats
     */
    private $pageFormats;

    /**
     * @var ilFormFieldParser
     */
    private $formFieldParser;

    /**
     * @var ilCertificateTemplateImportAction|null
     */
    private $importAction;

    /**
     * @var ilCertificateTemplateRepository
     */
    private $templateRepository;

    /**
     * @param integer $objectId
     * @param string $certificatePath
     * @param ilLanguage $language
     * @param ilTemplate $template
     * @param ilCtrl $controller
     * @param ilAccess $access
     * @param ilToolbarGUI $toolbar
     * @param ilCertificatePlaceholderDescription $placeholderDescriptionObject
     * @param ilPageFormats|null $pageFormats
     * @param ilFormFieldParser|null $formFieldParser
     * @param ilCertificateTemplateImportAction|null $importAction
     * @param ilLogger|null $logger
     * @param ilCertificateTemplateRepository|null $templateRepository
     */
    public function __construct(
        int $objectId,
        string $certificatePath,
        ilLanguage $language,
        ilTemplate $template,
        ilCtrl $controller,
        ilAccess $access,
        ilToolbarGUI $toolbar,
        ilCertificatePlaceholderDescription $placeholderDescriptionObject,
        ilPageFormats $pageFormats = null,
        ilFormFieldParser $formFieldParser = null,
        ilCertificateTemplateImportAction $importAction = null,
        ilLogger $logger = null,
        ilCertificateTemplateRepository $templateRepository = null
    ) {
        global $DIC;

        $this->objectId = $objectId;
        $this->language = $language;
        $this->template = $template;
        $this->controller = $controller;
        $this->access = $access;
        $this->toolbar = $toolbar;
        $this->placeholderDescriptionObject = $placeholderDescriptionObject;

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
                (int) $objectId,
                $certificatePath,
                $placeholderDescriptionObject,
                $logger,
                $DIC->filesystem()->web()
            );
        }
        $this->importAction = $importAction;

        if (null === $templateRepository) {
            $templateRepository = new ilCertificateTemplateRepository($database, $logger);
        }
        $this->templateRepository = $templateRepository;
    }

    /**
     * @param ilCertificateGUI $certificateGUI
     * @param ilCertificate $certificateObject
     * @return ilPropertyFormGUI
     * @throws \ILIAS\Filesystem\Exception\FileAlreadyExistsException
     * @throws \ILIAS\Filesystem\Exception\FileNotFoundException
     * @throws \ILIAS\Filesystem\Exception\IOException
     * @throws ilDatabaseException
     * @throws ilException
     * @throws ilWACException
     */
    public function createForm(ilCertificateGUI $certificateGUI, ilCertificate $certificateObject)
    {
        $certificateTemplate = $this->templateRepository->fetchCurrentlyUsedCertificate($this->objectId);

        $command = $this->controller->getCmd();

        $form = new ilPropertyFormGUI();
        $form->setPreventDoubleSubmission(false);
        $form->setFormAction($this->controller->getFormAction($certificateGUI));
        $form->setTitle($this->language->txt("cert_form_sec_availability"));
        $form->setMultipart(true);
        $form->setTableWidth("100%");
        $form->setId("certificate");

        $active = new ilCheckboxInputGUI($this->language->txt("active"), "active");
        $form->addItem($active);

        $import = new ilFileInputGUI($this->language->txt("import"), "certificate_import");
        $import->setRequired(false);
        $import->setSuffixes(array("zip"));

        // handle the certificate import
        if (strlen($_FILES["certificate_import"]["name"])) {
            if ($import->checkInput()) {
                $result = $this->importAction->import($_FILES["certificate_import"]["tmp_name"], $_FILES["certificate_import"]["name"]);
                if ($result == false) {
                    $import->setAlert($this->language->txt("certificate_error_import"));
                } else {
                    $this->controller->redirect($certificateGUI, "certificateEditor");
                }
            }
        }
        $form->addItem($import);

        $formSection = new \ilFormSectionHeaderGUI();
        $formSection->setTitle($this->language->txt("cert_form_sec_layout"));
        $form->addItem($formSection);

        $pageformat = new ilRadioGroupInputGUI($this->language->txt("certificate_page_format"), "pageformat");
        $pageformats = $this->pageFormats->fetchPageFormats();

        foreach ($pageformats as $format) {
            $option = new ilRadioOption($format["name"], $format["value"]);

            if (strcmp($format["value"], "custom") == 0) {
                $pageheight = new ilTextInputGUI($this->language->txt("certificate_pageheight"), "pageheight");
                $pageheight->setSize(6);
                $pageheight->setValidationRegexp('/^(([1-9]+|([1-9]+[0]*[\.,]{0,1}[\d]+))|(0[\.,](0*[1-9]+[\d]*)))(cm|mm|in|pt|pc|px|em)$/is');
                $pageheight->setInfo($this->language->txt("certificate_unit_description"));
                $pageheight->setRequired(true);
                $option->addSubitem($pageheight);

                $pagewidth = new ilTextInputGUI($this->language->txt("certificate_pagewidth"), "pagewidth");
                $pagewidth->setSize(6);
                $pagewidth->setValidationRegexp('/^(([1-9]+|([1-9]+[0]*[\.,]{0,1}[\d]+))|(0[\.,](0*[1-9]+[\d]*)))(cm|mm|in|pt|pc|px|em)$/is');
                $pagewidth->setInfo($this->language->txt("certificate_unit_description"));
                $pagewidth->setRequired(true);
                $option->addSubitem($pagewidth);
            }

            $pageformat->addOption($option);
        }

        $pageformat->setRequired(true);

        if (strcmp($command, "certificateSave") == 0) {
            $pageformat->checkInput();
        }

        $form->addItem($pageformat);

        $bgimage = new ilImageFileInputGUI($this->language->txt("certificate_background_image"), "background");
        $bgimage->setRequired(false);
        $bgimage->setUseCache(false);

        $bgimage->setALlowDeletion(true);
        if (!$certificateObject->hasBackgroundImage()) {
            if (ilObjCertificateSettingsAccess::hasBackgroundImage()) {
                ilWACSignedPath::setTokenMaxLifetimeInSeconds(15);
                $imagePath = ilWACSignedPath::signFile(ilObjCertificateSettingsAccess::getBackgroundImageThumbPathWeb());
                $bgimage->setImage($imagePath);
                $bgimage->setALlowDeletion(false);
            }
        } else {
            ilWACSignedPath::setTokenMaxLifetimeInSeconds(15);

            $thumbnailPath = $certificateObject->getBackgroundImageThumbPath();

            if (!file_exists($thumbnailPath)) {
                $thumbnailPath = ilObjCertificateSettingsAccess::getBackgroundImageThumbPath();
                $bgimage->setALlowDeletion(false);
            }
            $imagePath = ilWACSignedPath::signFile($thumbnailPath);
            $bgimage->setImage($imagePath);
        }

        $form->addItem($bgimage);

        $thumbnailImage = new ilImageFileInputGUI($this->language->txt('certificate_card_thumbnail_image'), 'certificate_card_thumbnail_image');
        $thumbnailImage->setRequired(false);
        $thumbnailImage->setUseCache(false);
        $thumbnailImage->setSuffixes(array('svg'));

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

        if (strcmp($command, "certificateSave") == 0) {
            $rect->checkInput();
        }

        $form->addItem($rect);

        $certificate = new ilTextAreaInputGUI($this->language->txt("certificate_text"), "certificate_text");
        $certificate->removePlugin('ilimgupload');
        $certificate->setRequired(true);
        $certificate->setRows(20);
        $certificate->setCols(80);


        $placeholderHtmlDescription = $this->placeholderDescriptionObject->createPlaceholderHtmlDescription();

        $placeholderDescriptionInHtml = $placeholderHtmlDescription;

        $certificate->setInfo($placeholderDescriptionInHtml);

        $certificate->setUseRte(true, '3.4.7');

        $tags = array(
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
        );

        $certificate->setRteTags($tags);

        if (strcmp($command, "certificateSave") == 0) {
            $certificate->checkInput();
        }

        $form->addItem($certificate);

        if ($certificateObject->getAdapter()->hasAdditionalFormElements()) {
            $formSection = new \ilFormSectionHeaderGUI();
            $formSection->setTitle($this->language->txt("cert_form_sec_add_features"));
            $form->addItem($formSection);
        }

        if ($this->access->checkAccess("write", "", $_GET["ref_id"])) {
            if ($certificateTemplate->isCurrentlyActive()) {
                $this->toolbar->setFormAction($this->controller->getFormAction($certificateGUI));

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

    /**
     * @param array $formFields
     * @return mixed|void
     */
    public function save(array $formFields)
    {
    }

    /**
     * @param string $content
     * @return array|mixed
     */
    public function fetchFormFieldData(string $content)
    {
        return $this->formFieldParser->fetchDefaultFormFields($content);
    }
}
