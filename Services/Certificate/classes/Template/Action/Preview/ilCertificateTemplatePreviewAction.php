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

/**
 * @author  Niels Theen <ntheen@databay.de>
 */
class ilCertificateTemplatePreviewAction
{
    private ilObjUser $user;
    private ilCertificateUtilHelper $utilHelper;
    private ilCertificateMathJaxHelper $mathJaxHelper;
    private ilCertificateUserDefinedFieldsHelper $userDefinedFieldsHelper;
    private ilCertificateRpcClientFactoryHelper $rpcClientFactoryHelper;
    private ilCertificatePdfFileNameFactory $pdfFileNameFactory;

    public function __construct(
        private ilCertificateTemplateRepository $templateRepository,
        private ilCertificatePlaceholderValues $placeholderValuesObject,
        private string $rootDirectory = CLIENT_WEB_DIR,
        ?ilObjUser $user = null,
        ?ilCertificateUtilHelper $utilHelper = null,
        ?ilCertificateMathJaxHelper $mathJaxHelper = null,
        ?ilCertificateUserDefinedFieldsHelper $userDefinedFieldsHelper = null,
        ?ilCertificateRpcClientFactoryHelper $rpcClientFactoryHelper = null,
        ?ilCertificatePdfFileNameFactory $pdfFileNameFactory = null
    ) {
        global $DIC;

        if (null === $user) {
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

        if (null === $pdfFileNameFactory) {
            $pdfFileNameFactory = new ilCertificatePdfFileNameFactory($DIC->language());
        }
        $this->pdfFileNameFactory = $pdfFileNameFactory;
    }

    /**
     * @throws Exception
     */
    public function createPreviewPdf(int $objectId): void
    {
        $template = $this->templateRepository->fetchCurrentlyUsedCertificate($objectId);

        $xslfo = $template->getCertificateContent();

        $xslfo = $this->exchangeCertificateVariables($xslfo, $template, $objectId);

        // render tex as fo graphics
        $xlsfo = $this->mathJaxHelper->fillXlsFoContent($xslfo);

        $pdf_base64 = $this->rpcClientFactoryHelper
            ->ilFO2PDF('RPCTransformationHandler', $xlsfo);

        $pdfPresentation = new ilUserCertificatePresentation(
            $template->getObjId(),
            $template->getObjType(),
            null,
            '',
            ''
        );

        $this->utilHelper->deliverData(
            $pdf_base64->scalar,
            $this->pdfFileNameFactory->create($pdfPresentation),
            'application/pdf'
        );
    }

    /**
     * Exchanges the variables in the certificate text with given values
     * @param string                $certificate_text The XSL-FO certificate text
     * @return string XSL-FO code
     */
    private function exchangeCertificateVariables(
        string $certificate_text,
        ilCertificateTemplate $template,
        int $objectId
    ): string {
        $insert_tags = $this->placeholderValuesObject->getPlaceholderValuesForPreview($this->user->getId(), $objectId);

        foreach ($this->getCustomCertificateFields() as $value) {
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

        return str_replace(
            '[BACKGROUND_IMAGE]',
            $this->rootDirectory . $backgroundImagePath,
            $certificate_text
        );
    }

    /**
     * @return array<int, array{name: string, ph: string}>
     */
    private function getCustomCertificateFields(): array
    {
        $user_field_definitions = $this->userDefinedFieldsHelper->createInstance();
        $fds = $user_field_definitions->getDefinitions();

        $fields = [];
        foreach ($fds as $f) {
            if ($f['certificate']) {
                $fields[$f['field_id']] = [
                    'name' => $f['field_name'],
                    'ph' => '[#' . str_replace(' ', '_', strtoupper($f['field_name'])) . ']'
                ];
            }
        }

        return $fields;
    }
}
