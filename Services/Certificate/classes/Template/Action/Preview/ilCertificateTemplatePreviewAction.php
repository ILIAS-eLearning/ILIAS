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
	 * @param ilCertificateTemplateRepository $templateRepository
	 * @param ilCertificatePlaceholderValues $placeholderValuesObject
	 * @param ilLogger|null $logger
	 * @param ilObjUser|null $user
	 */
	public function __construct(
		ilCertificateTemplateRepository $templateRepository,
		ilCertificatePlaceholderValues $placeholderValuesObject,
		ilLogger $logger = null,
		ilObjUser $user = null
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
	}

	/**
	 * @param int $objectId
	 * @return bool
	 * @throws ilException
	 * @throws Exception
	 */
	public function createPreviewPdf(int $objectId)
	{
		$oldDatePresentationValue = ilDatePresentation::useRelativeDates();
		ilDatePresentation::setUseRelativeDates(false);

		$template = $this->templateRepository->fetchCurrentlyActiveCertificate($objectId);

		$xslfo = $template->getCertificateContent();

		$xslfo = $this->exchangeCertificateVariables($xslfo, $template, $objectId);

		try {
			// render tex as fo graphics
			$xlsfo = ilMathJax::getInstance()
				->init(ilMathJax::PURPOSE_PDF)
				->setRendering(ilMathJax::RENDER_PNG_AS_FO_FILE)
				->insertLatexImages($xslfo);


			$pdf_base64 = ilRpcClientFactory::factory('RPCTransformationHandler')
				->ilFO2PDF($xlsfo);

			ilUtil::deliverData(
				$pdf_base64->scalar,
				'Certificate.pdf',
				'application/pdf'
			);
		}
		catch(Exception $e) {
			ilDatePresentation::setUseRelativeDates($oldDatePresentationValue);
			throw $e;
		}

		ilDatePresentation::setUseRelativeDates($oldDatePresentationValue);
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

		$backgroundImagePath = $template->getBackgroundImagePath();

		$certificate_text = str_replace(
			'[BACKGROUND_IMAGE]',
			CLIENT_WEB_DIR . $backgroundImagePath,
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
		$user_field_definitions = ilUserDefinedFields::_getInstance();
		$fds = $user_field_definitions->getDefinitions();

		$fields = array();
		foreach ($fds as $f) {
			if ($f["certificate"]) {
				$fields[$f["field_id"]] = array(
					"name" => $f["field_name"],
					"ph" => "[#" . str_replace(" ", "_", strtoupper($f["field_name"])) . "]");
			}
		}

		return $fields;
	}

}
