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
	 * @param ilCertificateTemplateRepository $templateRepository
	 * @param ilCertificatePlaceholderValues $placeholderValuesObject
	 */
	public function __construct(
		ilCertificateTemplateRepository $templateRepository,
		ilCertificatePlaceholderValues $placeholderValuesObject
	) {
		$this->templateRepository = $templateRepository;
		$this->placeholderValuesObject = $placeholderValuesObject;
	}

	/**
	 * @param $objectId
	 * @return bool
	 * @throws ilException
	 */
	public function createPreviewPdf($objectId)
	{
		ilDatePresentation::setUseRelativeDates(false);

		$template = $this->templateRepository->fetchCurrentlyActiveCertificate($objectId);

		$xslfo = $template->getCertificateContent();

		// render tex as fo graphics
		$xslfo = ilMathJax::getInstance()
			->init(ilMathJax::PURPOSE_PDF)
			->setRendering(ilMathJax::RENDER_PNG_AS_FO_FILE)
			->insertLatexImages($xslfo);

		try {
			$xlsfo = $this->exchangeCertificateVariables($xslfo, $template);

			$pdf_base64 = ilRpcClientFactory::factory('RPCTransformationHandler')
				->ilFO2PDF($xlsfo);

			ilUtil::deliverData(
				$pdf_base64->scalar,
				'Certificate.pdf',
				'application/pdf'
			);

		}
		catch(Exception $e) {
			$this->log->write(__METHOD__.': '.$e->getMessage());
			return false;
		}

		ilDatePresentation::setUseRelativeDates(true);
	}

	/**
	 * Exchanges the variables in the certificate text with given values
	 *
	 * @param string $certificate_text The XSL-FO certificate text
	 * @return string XSL-FO code
	 */
	private function exchangeCertificateVariables($certificate_text, $template)
	{
		$insert_tags = $this->placeholderValuesObject->getPlaceholderValuesForPreview();
		foreach ($this->getCustomCertificateFields() as $key => $value) {
			$insert_tags[$value["ph"]] = ilUtil::prepareFormOutput($value["name"]);
		}

		foreach ($insert_tags as $var => $value) {
			$certificate_text = str_replace($var, $value, $certificate_text);
		}

		$certificate_text = str_replace(
			'[CLIENT_WEB_DIR]',
			CLIENT_WEB_DIR,
			$certificate_text
		);

		$certificate_text = str_replace(
			'[BACKGROUND_IMAGE]',
			CLIENT_WEB_DIR . $template->getBackgroundImagePath(),
			$certificate_text
		);

		return $certificate_text;
	}

	/**
	 * Get custom certificate fields
	 */
	private function getCustomCertificateFields()
	{
		$user_field_definitions = ilUserDefinedFields::_getInstance();
		$fds = $user_field_definitions->getDefinitions();

		$fields = array();
		foreach ($fds as $f) {
			if ($f["certificate"]) {
				$fields[$f["field_id"]] = array("name" => $f["field_name"],
					"ph" => "[#" . str_replace(" ", "_", strtoupper($f["field_name"])) . "]");
			}
		}

		return $fields;
	}

}
