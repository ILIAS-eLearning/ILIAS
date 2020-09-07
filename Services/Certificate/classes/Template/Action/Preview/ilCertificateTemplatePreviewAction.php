<?php
/* Copyright (c) 1998-2018 ILIAS open source, Extended GPL, see docs/LICENSE */

/**
 * @author  Niels Theen <ntheen@databay.de>
 */
class ilCertificateTemplatePreviewAction
{
    /**
     * @var ilCertificateTemplateRepository
     */
    private $templateRepository;

    /**
     * @var ilCertificatePlaceholderValues
     */
    private $placeholderValuesObject;

    /**
     * @var ilLogger
     */
    private $logger;

    /**
     * @var ilObjUser|null
     */
    private $user;

    /**
     * @var ilCertificateUtilHelper|null
     */
    private $utilHelper;

    /**
     * @var ilCertificateMathJaxHelper|null
     */
    private $mathJaxHelper;

    /**
     * @var ilCertificateUserDefinedFieldsHelper|null
     */
    private $userDefinedFieldsHelper;

    /**
     * @var ilCertificateRpcClientFactoryHelper|null
     */
    private $rpcClientFactoryHelper;

    /**
     * @var string
     */
    private $rootDirectory;

    /**
     * @param ilCertificateTemplateRepository $templateRepository
     * @param ilCertificatePlaceholderValues $placeholderValuesObject
     * @param ilLogger|null $logger
     * @param ilObjUser|null $user
     * @param ilCertificateUtilHelper|null $utilHelper
     * @param ilCertificateMathJaxHelper|null $mathJaxHelper
     * @param ilCertificateUserDefinedFieldsHelper|null $userDefinedFieldsHelper
     * @param ilCertificateRpcClientFactoryHelper|null $rpcClientFactoryHelper
     * @param string $rootDirectory
     */
    public function __construct(
        ilCertificateTemplateRepository $templateRepository,
        ilCertificatePlaceholderValues $placeholderValuesObject,
        ilLogger $logger = null,
        ilObjUser $user = null,
        ilCertificateUtilHelper $utilHelper = null,
        ilCertificateMathJaxHelper $mathJaxHelper = null,
        ilCertificateUserDefinedFieldsHelper $userDefinedFieldsHelper = null,
        ilCertificateRpcClientFactoryHelper $rpcClientFactoryHelper = null,
        string $rootDirectory = CLIENT_WEB_DIR
    ) {
        $this->templateRepository = $templateRepository;
        $this->placeholderValuesObject = $placeholderValuesObject;

        if (null === $logger) {
            global $DIC;
            $logger = $DIC->logger()->cert();
        }
        $this->logger = $logger;

        if (null === $user) {
            global $DIC;
            $user = $DIC->user();
        }
        $this->user = $user;

        if (null === $utilHelper) {
            $utilHelper = new ilCertificateUtilHelper();
        }
        $this->utilHelper = $utilHelper;

        if (null === $mathJaxHelper) {
            $mathJaxHelper = new ilCertificateMathJaxHelper();
        }
        $this->mathJaxHelper = $mathJaxHelper;

        if (null === $userDefinedFieldsHelper) {
            $userDefinedFieldsHelper = new ilCertificateUserDefinedFieldsHelper();
        }
        $this->userDefinedFieldsHelper = $userDefinedFieldsHelper;

        if (null === $rpcClientFactoryHelper) {
            $rpcClientFactoryHelper = new ilCertificateRpcClientFactoryHelper();
        }
        $this->rpcClientFactoryHelper = $rpcClientFactoryHelper;

        $this->rootDirectory = $rootDirectory;
    }

    /**
     * @param int $objectId
     * @return bool
     * @throws ilException
     * @throws Exception
     */
    public function createPreviewPdf(int $objectId)
    {
        $template = $this->templateRepository->fetchCurrentlyUsedCertificate($objectId);

        $xslfo = $template->getCertificateContent();

        $xslfo = $this->exchangeCertificateVariables($xslfo, $template, $objectId);

        try {
            // render tex as fo graphics
            $xlsfo = $this->mathJaxHelper->fillXlsFoContent($xslfo);

            $pdf_base64 = $this->rpcClientFactoryHelper
                ->ilFO2PDF('RPCTransformationHandler', $xlsfo);

            $this->utilHelper->deliverData(
                $pdf_base64->scalar,
                'Certificate.pdf',
                'application/pdf'
            );
        } catch (Exception $e) {
            throw $e;
        }
    }

    /**
     * Exchanges the variables in the certificate text with given values
     *
     * @param string $certificate_text The XSL-FO certificate text
     * @param ilCertificateTemplate $template
     * @param int $objectId
     * @return string XSL-FO code
     */
    private function exchangeCertificateVariables(
        string $certificate_text,
        ilCertificateTemplate $template,
        int $objectId
    ) {
        $insert_tags = $this->placeholderValuesObject->getPlaceholderValuesForPreview($this->user->getId(), $objectId);

        foreach ($this->getCustomCertificateFields() as $key => $value) {
            $insert_tags[$value['ph']] = $this->utilHelper->prepareFormOutput($value['name']);
        }

        foreach ($insert_tags as $placeholderVariable => $value) {
            $certificate_text = str_replace('[' . $placeholderVariable . ']', $value, $certificate_text);
        }

        $certificate_text = str_replace(
            '[CLIENT_WEB_DIR]',
            $this->rootDirectory,
            $certificate_text
        );

        $backgroundImagePath = $template->getBackgroundImagePath();

        $certificate_text = str_replace(
            '[BACKGROUND_IMAGE]',
            $this->rootDirectory . $backgroundImagePath,
            $certificate_text
        );

        return $certificate_text;
    }

    /**
     * Get custom certificate fields
     *
     * @return array
     */
    private function getCustomCertificateFields()
    {
        $user_field_definitions = $this->userDefinedFieldsHelper->createInstance();
        $fds = $user_field_definitions->getDefinitions();

        $fields = array();
        foreach ($fds as $f) {
            if ($f['certificate']) {
                $fields[$f['field_id']] = array(
                    'name' => $f['field_name'],
                    'ph' => '[#' . str_replace(' ', '_', strtoupper($f['field_name'])) . ']');
            }
        }

        return $fields;
    }
}
