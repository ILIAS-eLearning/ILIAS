<?php

/* Copyright (c) 1998-2011 ILIAS open source, Extended GPL, see docs/LICENSE */


/**
* Create PDF certificates
*
* Base class to create PDF certificates using XML-FO XML transformations
*
* @author		Helmut Schottmüller <helmut.schottmueller@mac.com>
* @version	$Id$
* @ingroup Services
*/
class ilCertificate
{
	/**
	* The reference to the ILIAS control class
	*
	* @var ilCtrl
	*/
	protected $ctrl;

	/**
	* The reference to the ILIAS tree class
	*
	* @var ilTree
	*/
	protected $tree;

	/**
	* The reference to the ILIAS class
	*
	* @var ILIAS
	*/
	protected $ilias;

	/**
	* The reference to the Language class
	*
	* @var ilLanguage
	*/
	protected $lng;

	/**
	* The certificate adapter
	*
	* @var ilCertificateAdapter
	*/
	protected $adapter;

	/**
	 * @var ilSetting
	 */
	protected $settings;

	/**
	 * @var ilLog
	 */
	protected $log;

	/**
	 * @var ilDBInterface
	 */
	protected $db;

	/**
	 * @var bool
	 */
	protected static $is_active;

	/**
	 * @var ilCertificateTemplateRepository|null
	 */
	private $templateRepository;

	/**
	 * @var ilCertificatePlaceholderDescription
	 */
	private $placeholderDescriptionObject;

	/**
	 * @var integer
	 */
	private $objectId;

	/**
	 * @var ilUserCertificateRepository|null
	 */
	private $certificateRepository;

	/**
	 * @var string
	 */
	private $certificatePath;

	/**
	 * @var ilCertificatePlaceholderValues
	 */
	private $placeholderValuesObject;

	/**
	 * ilCertificate constructor
	 * @param ilCertificateAdapter $adapter The certificate adapter needed to construct the certificate
	 * @param ilCertificatePlaceholderDescription $placeholderDescriptionObject
	 * @param ilCertificatePlaceholderValues $placeholderValuesObject
	 * @param $objectId - Object ID of the current component (e.g. course, test, exercise)
	 * @param $certificatePath - Path to certificate data like background images etc.
	 * @param ilCertificateTemplateRepository|null $templateRepository
	 * @param ilUserCertificateRepository|null $certificateRepository
	 */
	public function __construct(
		ilCertificateAdapter $adapter,
		ilCertificatePlaceholderDescription $placeholderDescriptionObject,
		ilCertificatePlaceholderValues $placeholderValuesObject,
		$objectId,
		$certificatePath,
		ilCertificateTemplateRepository $templateRepository = null,
		ilUserCertificateRepository $certificateRepository = null
	) {
		global $DIC;

		$this->lng      = $DIC['lng'];
		$this->ctrl     = $DIC['ilCtrl'];
		$this->ilias    = $DIC['ilias'];
		$this->tree     = $DIC['tree'];
		$this->settings = $DIC['ilSetting'];
		$this->log      = $DIC['ilLog'];
		$this->db       = $DIC['ilDB'];

		$this->adapter = $adapter;

		$this->placeholderDescriptionObject = $placeholderDescriptionObject;

		$this->placeholderValuesObject = $placeholderValuesObject;

		$this->objectId = $objectId;

		$this->certificatePath = $certificatePath;

		if ($templateRepository === null) {
			$templateRepository = new ilCertificateTemplateRepository($DIC->database());
		}
		$this->templateRepository = $templateRepository;

		if ($certificateRepository === null) {
			$certificateRepository = new ilUserCertificateRepository($DIC->database(), $DIC->logger()->root());
		}
		$this->certificateRepository = $certificateRepository;
	}

	/**
	 * @param string $a_number
	 * @return float
	 */
	public function formatNumberString($a_number)
	{
		return str_replace(',', '.', $a_number);
	}
	
	/**
	* Returns the filesystem path of the background image
	* @param  bool $asRelative
	* @return string The filesystem path of the background image
	*/
	public function getBackgroundImagePath($asRelative = false)
	{
		if($asRelative)
		{
			return str_replace(
				array(CLIENT_WEB_DIR, '//'),
				array('[CLIENT_WEB_DIR]', '/'),
				$this->certificatePath
			);
		}

		return $this->certificatePath;
	}

	/**
	* Returns the filename of the background image
	*
	* @return string The filename of the background image
	*/
	public function getBackgroundImageName()
	{
		return "background.jpg";
	}

	/**
	* Returns the filesystem path of the background image thumbnail
	*
	* @return string The filesystem path of the background image thumbnail
	*/
	public function getBackgroundImageThumbPath()
	{
		return $this->certificatePath . $this->getBackgroundImageName() . ".thumb.jpg";
	}

	/**
	* Returns the filesystem path of the background image temp file during upload
	*
	* @return string The filesystem path of the background image temp file
	*/
	public function getBackgroundImageTempfilePath()
	{
		return $this->certificatePath . "background_upload.tmp";
	}

	/**
	* Returns the filesystem path of the XSL-FO file
	*
	* @return string The filesystem path of the XSL-FO file
	*/
	public function getXSLPath()
	{
		return $this->certificatePath . $this->getXSLName();
	}
	
	/**
	* Returns the filename of the XSL-FO file
	*
	* @return string The filename of the XSL-FO file
	*/
	function getXSLName()
	{
		return "certificate.xml";
	}
	
	/**
	* Returns the filename of the XSL-FO file
	*
	* @return string The filename of the XSL-FO file
	*/
	public static function _getXSLName()
	{
		return "certificate.xml";
	}
	
	/**
	* Returns the web path of the background image
	*
	* @return string The web path of the background image
	*/
	public function getBackgroundImagePathWeb()
	{
		// TODO: this is generic now -> provide better solution
		$webdir = $this->certificatePath . $this->getBackgroundImageName();
		return str_replace(ilUtil::removeTrailingPathSeparators(ILIAS_ABSOLUTE_PATH), ilUtil::removeTrailingPathSeparators(ILIAS_HTTP_PATH), $webdir);
	}
	
	/**
	* Returns the web path of the background image thumbnail
	*
	* @return string The web path of the background image thumbnail
	*/
	public function getBackgroundImageThumbPathWeb()
	{
		// TODO: this is generic now -> provide better solution
		return str_replace(ilUtil::removeTrailingPathSeparators(ILIAS_ABSOLUTE_PATH), ilUtil::removeTrailingPathSeparators(ILIAS_HTTP_PATH), $this->getBackgroundImageThumbPath());
	}
	
	/**
	* Deletes the background image of a certificate
	*
	* @return boolean TRUE if the process succeeds
	*/
	public function deleteBackgroundImage($version)
	{
		$result = TRUE;
		if (file_exists($this->getBackgroundImageThumbPath())) {
			$result = $result & unlink($this->getBackgroundImageThumbPath());
		}

		$filename = $this->getBackgroundImagePath() . 'background_' . $version . '.jpg';
		if (file_exists($filename)) {
			$result = $result & unlink($filename);
		}

		if (file_exists($this->getBackgroundImageTempfilePath())) {
			$result = $result & unlink($this->getBackgroundImageTempfilePath());
		}

		return $result;
	}

	/**
	 * Clone the certificate for another test object
	 *
	 * @param $newObject \ilCertificate The new certificate object
	 * @param $newObjId
	 * @throws ilDatabaseException
	 */
	public function cloneCertificate(\ilCertificate $newObject, $newObjId)
	{
		$templates = $this->templateRepository->fetchCertificateTemplatesByObjId($this->objectId);
		$currentlyActive = null;

		/** @var ilCertificateTemplate $template */
		foreach ($templates as $template) {
			$backgroundImagePath = $template->getBackgroundImagePath();
			$backgroundImageFile = basename($backgroundImagePath);
			$backgroundImageThumbnail = $this->getBackgroundImageThumbPath();

			$newBackgroundImage = $newObject->getBackgroundImagePath() . $backgroundImageFile;
			$newBackgroundImageThumbnail = $newObject->getBackgroundImageThumbPath();

			if (@file_exists($backgroundImagePath)) {
				@copy($backgroundImagePath, $newBackgroundImage);
			}

			if (@file_exists($backgroundImageThumbnail)) {
				@copy($backgroundImageThumbnail, $newBackgroundImageThumbnail);
			}

			$newTemplate = new ilCertificateTemplate(
				$newObjId,
				$template->getCertificateContent(),
				$template->getCertificateHash(),
				$template->getTemplateValues(),
				$template->getVersion(),
				ILIAS_VERSION_NUMERIC,
				time(),
				$template->isCurrentlyActive(),
				$newBackgroundImage
			);

			$this->templateRepository->save($newTemplate);
		}

		// #10271
		if($this->readActive()) {
			$newObject->writeActive(true);
		}
	}

	/**
	* Deletes the certificate and all it's data
	*
	* @access public
	*/
	public function deleteCertificate()
	{
		if (@file_exists($this->certificatePath)) {
			ilUtil::delDir($this->certificatePath);
			$this->certificatePath->deleteCertificate();
		}

		$this->writeActive(false);
	}

	/**
	* Convert the XSL-FO to the certificate text and the form settings using XSL transformation
	*/
	public function getFormFieldsFromFO()
	{
		if (@file_exists($this->getXSLPath()))
		{
			$xslfo = file_get_contents($this->getXSLPath());
		}
		// retrieve form information (using a dirty way with regular expressions)
		$pagewidth = "21cm";
		if (preg_match("/page-width\=\"([^\"]+)\"/", $xslfo, $matches))
		{
			$pagewidth = $matches[1];
		}
		$pageheight = "29.7cm";
		if (preg_match("/page-height\=\"([^\"]+)\"/", $xslfo, $matches))
		{
			$pageheight = $matches[1];
		}
		$certificatesettings = new ilSetting("certificate");
		$pagesize = $certificatesettings->get("pageformat");;
		if (((strcmp($pageheight, "29.7cm") == 0) || (strcmp($pageheight, "297mm") == 0)) && ((strcmp($pagewidth, "21cm") == 0) || (strcmp($pagewidth, "210mm") == 0)))
		{
			$pagesize = "a4";
		}
		else if (((strcmp($pagewidth, "29.7cm") == 0) || (strcmp($pagewidth, "297mm") == 0)) && ((strcmp($pageheight, "21cm") == 0) || (strcmp($pageheight, "210mm") == 0)))
		{
			$pagesize = "a4landscape";
		}
		else if (((strcmp($pageheight, "21cm") == 0) || (strcmp($pageheight, "210mm") == 0)) && ((strcmp($pagewidth, "14.8cm") == 0) || (strcmp($pagewidth, "148mm") == 0)))
		{
			$pagesize = "a5";
		}
		else if (((strcmp($pagewidth, "21cm") == 0) || (strcmp($pagewidth, "210mm") == 0)) && ((strcmp($pageheight, "14.8cm") == 0) || (strcmp($pageheight, "148mm") == 0)))
		{
			$pagesize = "a5landscape";
		}
		else if (((strcmp($pageheight, "11in") == 0)) && ((strcmp($pagewidth, "8.5in") == 0)))
		{
			$pagesize = "letter";
		}
		else if (((strcmp($pagewidth, "11in") == 0)) && ((strcmp($pageheight, "8.5in") == 0)))
		{
			$pagesize = "letterlandscape";
		}
		else
		{
			$pagesize = "custom";
		}
		if (!strlen($xslfo)) $pagesize = $certificatesettings->get("pageformat");;

		$marginbody_top = "0cm";
		$marginbody_right = "2cm";
		$marginbody_bottom = "0cm";
		$marginbody_left = "2cm";
		if(preg_match("/fo:flow[^>]*margin\=\"([^\"]+)\"/", $xslfo, $matches))
		{
			// Backwards compatibility
			$marginbody = $matches[1];
			if (preg_match_all("/([^\s]+)/", $marginbody, $matches))
			{
				$marginbody_top = $matches[1][0];
				$marginbody_right = $matches[1][1];
				$marginbody_bottom = $matches[1][2];
				$marginbody_left = $matches[1][3];
			}
		}
		else if(preg_match("/fo:region-body[^>]*margin\=\"([^\"]+)\"/", $xslfo, $matches))
		{
			$marginbody = $matches[1];
			if (preg_match_all("/([^\s]+)/", $marginbody, $matches))
			{
				$marginbody_top = $matches[1][0];
				$marginbody_right = $matches[1][1];
				$marginbody_bottom = $matches[1][2];
				$marginbody_left = $matches[1][3];
			}
		}

		$xsl = file_get_contents("./Services/Certificate/xml/fo2xhtml.xsl");
		if ((strlen($xslfo)) && (strlen($xsl)))
		{
			$args = array( '/_xml' => $xslfo, '/_xsl' => $xsl );
			$xh = xslt_create();
			$output = xslt_process($xh, "arg:/_xml", "arg:/_xsl", NULL, $args, NULL);
			xslt_error($xh);
			xslt_free($xh);
		}

		$output = preg_replace("/<\?xml[^>]+?>/", "", $output);
		// dirty hack: the php xslt processing seems not to recognize the following
		// replacements, so we do it in the code as well
		$output = str_replace("&#xA0;", "<br />", $output);
		$output = str_replace("&#160;", "<br />", $output);
		$form_fields = array(
			"pageformat" => $pagesize,
			"pagewidth" => $pagewidth,
			"pageheight" => $pageheight,
			"margin_body_top" => $marginbody_top,
			"margin_body_right" => $marginbody_right,
			"margin_body_bottom" => $marginbody_bottom,
			"margin_body_left" => $marginbody_left,
			"certificate_text" => $output
		);
		$this->getAdapter()->addFormFieldsFromObject($form_fields);
		return $form_fields;
	}

	/**
	 * Convert the certificate text to XSL-FO using XSL transformation
	 *
	 * @param array $form_data The form data
	 * @return string XSL-FO code
	 * @throws Exception
	 */
	public function processXHTML2FO($form_data)
	{
		$content = "<html><body>" . $form_data["certificate_text"] . "</body></html>";
		$content = preg_replace("/<p>(&nbsp;){1,}<\\/p>/", "<p></p>", $content);
		$content = preg_replace("/<p>(\\s)*?<\\/p>/", "<p></p>", $content);
		$content = str_replace("<p></p>", "<p class=\"emptyrow\"></p>", $content);
		$content = str_replace("&nbsp;", "&#160;", $content);
		$content = preg_replace("//", "", $content);

		$check = new ilXMLChecker();
		$check->setXMLContent($content);
		$check->startParsing();

		if ($check->hasError()) {
			throw new Exception($this->lng->txt("certificate_not_well_formed"));
		}

		$xsl = file_get_contents("./Services/Certificate/xml/xhtml2fo.xsl");

		// additional font support
		$xsl = str_replace(
				'font-family="Helvetica, unifont"',
				'font-family="'.$this->settings->get('rpc_pdf_font','Helvetica, unifont').'"',
				$xsl
		);

		$args = array( '/_xml' => $content, '/_xsl' => $xsl );
		$xh = xslt_create();
		if (strcmp($form_data["pageformat"], "custom") == 0)
		{
			$pageheight = $form_data["pageheight"];
			$pagewidth = $form_data["pagewidth"];
		}
		else
		{
			$pageformats = $this->getPageFormats();
			$pageheight = $pageformats[$form_data["pageformat"]]["height"];
			$pagewidth = $pageformats[$form_data["pageformat"]]["width"];
		}

		$backgroundimage = '';
		if ($this->hasBackgroundImage()) {
			$backgroundimage = $this->getBackgroundImagePath(true) . 'background.jpg';
		} elseif (ilObjCertificateSettingsAccess::hasBackgroundImage()) {
			$backgroundimage = ilObjCertificateSettingsAccess::getBackgroundImagePath(true);
		}

		$params = array(
			"pageheight"      => $pageheight,
			"pagewidth"       => $pagewidth,
			"backgroundimage" => $backgroundimage,
			"marginbody"      => implode(' ', array(
				$form_data["margin_body_top"],
				$form_data["margin_body_right"],
				$form_data["margin_body_bottom"],
				$form_data["margin_body_left"]
			))
		);

		$output = xslt_process($xh, "arg:/_xml", "arg:/_xsl", NULL, $args, $params);

		xslt_error($xh);
		xslt_free($xh);

		return $output;
	}

	/**
	* Exchanges the variables in the certificate text with given values
	*
	* @param string $certificate_text The XSL-FO certificate text
	* @param array $insert_tags An associative array containing the variables and the values to replace
	* @return string XSL-FO code
	*/
	private function exchangeCertificateVariables($certificate_text, $insert_tags = array())
	{
		if (count($insert_tags) == 0)
		{
			$insert_tags = $this->placeholderValuesObject->getPlaceholderValuesForPreview();
			foreach (self::getCustomCertificateFields() as $k => $f)
			{
				$insert_tags[$f["ph"]] = ilUtil::prepareFormOutput($f["name"]);
			}
		}
		foreach ($insert_tags as $var => $value)
		{
			$certificate_text = str_replace($var, $value, $certificate_text);
		}

		$certificate_text = str_replace('[CLIENT_WEB_DIR]', CLIENT_WEB_DIR, $certificate_text);

		return $certificate_text;
	}

	/**
	 * Creates a PDF certificate
	 * @param array $params An array of parameters which is needed to create the certificate
	 * @param bool $deliver
	 * @return void|string
	 * @throws ilException
	 */
	public function outCertificate($params, $deliver = TRUE)
	{
		/** @var ilObjUser $user */
		$user = ilObjectFactory::getInstanceByObjId($params['user_id']);

		ilDatePresentation::setUseRelativeDates(false);
		$insert_tags = $this->placeholderValuesObject->getPlaceholderValues($user->getId(), $this->objectId);

		$cust_data = new ilUserDefinedData($this->getAdapter()->getUserIdForParams($params));
		$cust_data = $cust_data->getAll();

		foreach (self::getCustomCertificateFields() as $key => $field)  {
			$insert_tags[$field["ph"]] = ilUtil::prepareFormOutput($cust_data["f_".$key]);
		}

		/** @var ilObject $object */
		$object = ilObjectFactory::getInstanceByObjId($this->objectId);


		$template = $this->templateRepository->fetchCurrentlyActiveCertificate($this->objectId);

		// render tex as fo graphics
		$xslfo = ilMathJax::getInstance()
			->init(ilMathJax::PURPOSE_PDF)
			->setRendering(ilMathJax::RENDER_PNG_AS_FO_FILE)
			->insertLatexImages($template->getCertificateContent());

		try {
			$fo_string = $this->exchangeCertificateVariables($xslfo, $insert_tags);

			$backgroundImagePath = $this->certificatePath . $this->getBackgroundImageName();

			$userCertificate = new ilUserCertificate(
				$template->getId(),
				$this->objectId,
				$object->getType(),
				$user->getId(),
				$user->getFullname(),
				time(),
				$fo_string,
				$template->getTemplateValues(),
				null,
				1,
				ILIAS_VERSION_NUMERIC,
				true,
				$backgroundImagePath
			);

			$this->certificateRepository->save($userCertificate);

			$pdf_base64 = ilRpcClientFactory::factory('RPCTransformationHandler')->ilFO2PDF($fo_string);

			if ($deliver) {
				ilUtil::deliverData(
					$pdf_base64->scalar,
					$this->getAdapter()->getCertificateFilename($params),
					"application/pdf"
				);
			}

			return $pdf_base64->scalar;
		}
		catch(Exception $e)
		{
			$this->log->write(__METHOD__.': '.$e->getMessage());
			return false;
		}

		ilDatePresentation::setUseRelativeDates(true);

	}

	/**
	* Creates a PDF preview of the XSL-FO certificate
	*/
	public function createPreview()
	{
		ilDatePresentation::setUseRelativeDates(false);

		$template = $this->templateRepository->fetchCurrentlyActiveCertificate($this->objectId);

		$xslfo = $template->getCertificateContent();

        // render tex as fo graphics
		$xslfo = ilMathJax::getInstance()
			->init(ilMathJax::PURPOSE_PDF)
			->setRendering(ilMathJax::RENDER_PNG_AS_FO_FILE)
			->insertLatexImages($xslfo);

		try
		{
			$pdf_base64 = ilRpcClientFactory::factory('RPCTransformationHandler')
				->ilFO2PDF($this->exchangeCertificateVariables($xslfo));

			ilUtil::deliverData(
				$pdf_base64->scalar,
				$this->getAdapter()->getCertificateFilename(),
				"application/pdf"
			);

		}
		catch(Exception $e)
		{
			$this->log->write(__METHOD__.': '.$e->getMessage());
			return false;
		}

		ilDatePresentation::setUseRelativeDates(true);

	}

	/**
	* Saves the XSL-FO code to a file
	*
	* @param string $xslfo XSL-FO code
	*/
	public function saveCertificate($xslfo, $filename = "")
	{
		if (!file_exists($this->certificatePath))
		{
			ilUtil::makeDirParents($this->certificatePath);
		}
		if (strlen($filename) == 0)
		{
			$filename = $this->getXSLPath();
		}
		$fh = fopen($filename, "w");
		fwrite($fh, $xslfo);
		fclose($fh);
	}

	/**
	 * Uploads a background image for the certificate. Creates a new directory for the
	 * certificate if needed. Removes an existing certificate image if necessary
	 *
	 * @param string $image_tempfilename Name of the temporary uploaded image file
	 * @param $version - Version of the current certifcate template
	 * @return integer An errorcode if the image upload fails, 0 otherwise
	 * @throws ilException
	 */
	public function uploadBackgroundImage($image_tempfilename, $version)
	{
		if (!empty($image_tempfilename))
		{
			$convert_filename = $this->getBackgroundImageName();

			$imagepath = $this->certificatePath;

			if (!file_exists($imagepath))
			{
				ilUtil::makeDirParents($imagepath);
			}
			// upload the file
			$backgroundImageTempfilePath = $this->getBackgroundImageTempfilePath();

			if (!ilUtil::moveUploadedFile(
					$image_tempfilename,
					basename($backgroundImageTempfilePath),
				$backgroundImageTempfilePath
			)) {
				throw new ilException('Unable to move file');
			}
			// convert the uploaded file to JPEG
			$backgroundImagePath = $this->getBackgroundImagePath() . 'background_' . $version . '.jpg';

			ilUtil::convertImage($backgroundImageTempfilePath, $backgroundImagePath, "JPEG");

			$backgroundImageThumbnailPath = $this->getBackgroundImageThumbPath();

			ilUtil::convertImage($backgroundImageTempfilePath, $backgroundImageThumbnailPath, "JPEG", 100);

			if (!file_exists($backgroundImagePath)) {
				// something went wrong converting the file. use the original file and hope, that PDF can work with it
				if (!ilUtil::moveUploadedFile($backgroundImageTempfilePath, $convert_filename, $backgroundImagePath)) {
					throw new ilException('Unable to convert the file and the original file');
				}
			}

			unlink($backgroundImageTempfilePath);

			if (file_exists($backgroundImagePath) && (filesize($backgroundImagePath) > 0)) {
				return $backgroundImagePath;
			}
		}

		throw new ilException('The given temporary filename is empty');
	}

	/**
	* Checks for the background image of the certificate
	*
	* @return boolean Returns TRUE if the certificate has a background image, FALSE otherwise
	*/
	public function hasBackgroundImage()
	{
		if (file_exists($this->getBackgroundImagePath() . 'background.jpg') && (filesize($this->getBackgroundImagePath() . 'background.jpg') > 0))
		{
			return true;
		}
		return false;
	}

	/**
	* Checks the status of the certificate
	*
	* @return boolean Returns TRUE if the certificate is complete, FALSE otherwise
	*/
	public function isComplete()
	{
		if(self::isActive()) {
			if($this->objectId && !self::isObjectActive($this->objectId)) {
				return false;
			}
			if (file_exists($this->certificatePath)) {
				if (file_exists($this->getXSLPath()) && (filesize($this->getXSLPath()) > 0)) {
					return true;
				}
			}
		}
		return false;
	}

	/**
	* Retrieves predefined page formats
	*
	* @return array Associative array containing available page formats
	*/
	public function getPageFormats()
	{
		return array(
			"a4" => array(
				"name" => $this->lng->txt("certificate_a4"), // (297 mm x 210 mm)
				"value" => "a4",
				"width" => "210mm",
				"height" => "297mm"
			),
			"a4landscape" => array(
				"name" => $this->lng->txt("certificate_a4_landscape"), // (210 mm x 297 mm)",
				"value" => "a4landscape",
				"width" => "297mm",
				"height" => "210mm"
			),
			"a5" => array(
				"name" => $this->lng->txt("certificate_a5"), // (210 mm x 148.5 mm)
				"value" => "a5",
				"width" => "148mm",
				"height" => "210mm"
			),
			"a5landscape" => array(
				"name" => $this->lng->txt("certificate_a5_landscape"), // (148.5 mm x 210 mm)
				"value" => "a5landscape",
				"width" => "210mm",
				"height" => "148mm"
			),
			"letter" => array(
				"name" => $this->lng->txt("certificate_letter"), // (11 inch x 8.5 inch)
				"value" => "letter",
				"width" => "8.5in",
				"height" => "11in"
			),
			"letterlandscape" => array(
				"name" => $this->lng->txt("certificate_letter_landscape"), // (8.5 inch x 11 inch)
				"value" => "letterlandscape",
				"width" => "11in",
				"height" => "8.5in"
			),
			"custom" => array(
				"name" => $this->lng->txt("certificate_custom"),
				"value" => "custom",
				"width" => "",
				"height" => ""
			)
		);
	}

	/**
	* Builds an export file in ZIP format and delivers it
	*/
	public function deliverExportFileXML()
	{
		include_once "./Services/Utilities/classes/class.ilUtil.php";
		$exportpath = $this->createArchiveDirectory();
		ilUtil::makeDir($exportpath);

		$adapter = $this->getAdapter();
		$objId = $adapter->getCertificateID();

		$certificate = $this->templateRepository->fetchCurrentlyActiveCertificate($objId);
		$xslExport = $certificate->getCertificateContent();

		// save export xsl file
		$this->saveCertificate($xslExport, $exportpath . $this->getXSLName());
		// save background image
		if ($this->hasBackgroundImage()) {
			$backgroundImage = $this->getBackgroundImagePath() . 'background.jpg';
			copy($backgroundImage, $exportpath . $this->getBackgroundImageName());
		}
		else if (ilObjCertificateSettingsAccess::hasBackgroundImage()) {
				copy(ilObjCertificateSettingsAccess::getBackgroundImagePath() . 'background.jpg', $exportpath . ilObjCertificateSettingsAccess::getBackgroundImageName());
		}

		$zipfile = time() . "__" . IL_INST_ID . "__" . $this->getAdapter()->getAdapterType() . "__" . $this->getAdapter()->getCertificateId() . "__certificate.zip";

		ilUtil::zip($exportpath, $this->certificatePath . $zipfile);
		ilUtil::delDir($exportpath);
		ilUtil::deliverFile($this->certificatePath . $zipfile, $zipfile, "application/zip");
	}

	/**
	 * Reads an import ZIP file and creates a certificate of it
	 *
	 * @return boolean TRUE if the import succeeds, FALSE otherwise
	 * @throws ilException
	 */
	public function importCertificate($zipfile, $filename)
	{
		$importpath = $this->createArchiveDirectory();
		if (!ilUtil::moveUploadedFile($zipfile, $filename, $importpath . $filename)) {
			ilUtil::delDir($importpath);
			return FALSE;
		}

		ilUtil::unzip($importpath . $filename, TRUE);
		$subdir = str_replace(".zip", "", strtolower($filename)) . "/";
		$copydir = $importpath;

		if (is_dir($importpath . $subdir)) {
			$importpath = $importpath .$subdir;
			$copydir = $importpath . $subdir;
		}

		$dirinfo = ilUtil::getDir($importpath);

		$xmlfiles = 0;
		$otherfiles = 0;
		foreach ($dirinfo as $file)
		{
			if (strcmp($file["type"], "file") == 0)
			{
				if (strpos($file["entry"], ".xml") !== FALSE)
				{
					$xmlfiles++;
				}
				else if (strpos($file["entry"], ".zip") !== FALSE)
				{
				}
				else
				{
					$otherfiles++;
				}
			}
		}
		// if one XML file is in the archive, we try to import it
		if ($xmlfiles == 1)
		{
			foreach ($dirinfo as $file)
			{
				if (strcmp($file["type"], "file") == 0)
				{
					if (strpos($file["entry"], ".xml") !== FALSE)
					{
						$xsl = file_get_contents($copydir . $file["entry"]);
						// as long as we cannot make RPC calls in a given directory, we have
						// to add the complete path to every url
						$xsl = preg_replace_callback("/url\([']{0,1}(.*?)[']{0,1}\)/", function(array $matches) {

							$basePath = rtrim(dirname($this->getBackgroundImagePath(true)), '/');
							$fileName = basename($matches[1]);

							return 'url(' . $basePath . '/' . $fileName . ')';
						}, $xsl);

						$this->templateRepository->fetchCurrentlyActiveCertificate($this->objectId);

						$template = new ilCertificateTemplate(
							$this->objectId,
							$xsl,
							md5($xsl),
							json_encode($this->placeholderDescriptionObject->getPlaceholderDescriptions()),
							'1',
							ILIAS_VERSION_NUMERIC,
							time(),
							true
						);

						$this->templateRepository->save($template);
					}
					else if (strpos($file["entry"], ".zip") !== FALSE)
					{
					}
					else
					{
						@copy($copydir . $file["entry"], $this->certificatePath . $file["entry"]);
						if (strcmp($this->getBackgroundImagePath() . 'background.jpg', $this->certificatePath . $file["entry"]) == 0)
						{
							// upload of the background image, create a preview
							ilUtil::convertImage($this->getBackgroundImagePath() . 'background.jpg', $this->getBackgroundImageThumbPath(), "JPEG", 100);
						}
					}
				}
			}
		}
		else
		{
			ilUtil::delDir($importpath);
			return FALSE;
		}
		ilUtil::delDir($importpath);
		return TRUE;
	}
	
	/**
	* Gets the adapter
	*
	* @return object Adapter
	*/
	public function getAdapter()
	{
		return $this->adapter;
	}
	
	/**
	* Sets the adapter
	*
	* @param object $adapter Adapter
	*/
	public function setAdapter($adapter)
	{
		$this->adapter =& $adapter;
	}
	
	/***************************************
	/* BULK CERTIFICATE PROCESSING METHODS *
	/***************************************

	/**
	* Creates a directory for a zip archive containing multiple certificates
	*
	* @return string The created archive directory
	*/
	public function createArchiveDirectory()
	{
		$dir = $this->certificatePath . time() . "__" . IL_INST_ID . "__" . $this->getAdapter()->getAdapterType() . "__" . $this->getAdapter()->getCertificateId() . "__certificate/";
		include_once "./Services/Utilities/classes/class.ilUtil.php";
		ilUtil::makeDirParents($dir);
		return $dir;
	}
	
	/**
	* Adds PDF data as a file to a given directory
	*
	* @param binary $pdfdata Binary PDF data
	* @param string $dir Directory to contain the PDF data
	* @param string $filename The filename to save the PDF data
	*/
	public function addPDFtoArchiveDirectory($pdfdata, $dir, $filename)
	{
		$fh = fopen($dir . $filename, "wb");
		fwrite($fh, $pdfdata);
		fclose($fh);
	}
	
	/**
	* Create a ZIP file from a directory with certificates
	*
	* @param string $dir Directory containing the certificates
	* @param boolean $deliver TRUE to deliver the ZIP file, FALSE to return the filename only
	* @return string The created ZIP archive path
	*/
	public function zipCertificatesInArchiveDirectory($dir, $deliver = TRUE)
	{
		$zipfile = time() . "__" . IL_INST_ID . "__" . $this->getAdapter()->getAdapterType() . "__" . $this->getAdapter()->getCertificateId() . "__certificates.zip";
		ilUtil::zip($dir, $this->certificatePath . $zipfile);
		ilUtil::delDir($dir);
		if ($deliver)
		{
			ilUtil::deliverFile($this->certificatePath . $zipfile, $zipfile, "application/zip");
		}
		return $this->certificatePath . $zipfile;
	}
	
	public static function isActive()
	{				
		if(self::$is_active === null)
		{
			// basic admin setting active?
			$certificate_active = new ilSetting("certificate");
			$certificate_active = (bool)$certificate_active->get("active");

			// java/rtpc-server active?
			if($certificate_active)
			{
				include_once './Services/WebServices/RPC/classes/class.ilRPCServerSettings.php';
				$certificate_active = ilRPCServerSettings::getInstance()->isEnabled();
			}
			
			self::$is_active = (bool)$certificate_active;
		}
		return self::$is_active;
	}

	/**
	 * @param int $a_obj_id
	 * @return bool
	 */
	public static function isObjectActive($a_obj_id)
	{
		$chk = self::areObjectsActive(array($a_obj_id));
		return $chk[$a_obj_id];
	}

	/**
	 * @param array $a_obj_ids
	 * @return array
	 */
	public static function areObjectsActive(array $a_obj_ids)
	{
		/**
		 * @var $ilDB ilDBInterface
		 */
		global $DIC;

		$ilDB = $DIC['ilDB'];

		$all = array();
		foreach($a_obj_ids as $id)
		{
			$all[$id] = false;
		}

		$set = $ilDB->query("SELECT obj_id FROM il_certificate WHERE ".$ilDB->in("obj_id", $a_obj_ids, "", "integer"));
		while($row = $ilDB->fetchAssoc($set))
		{
			$all[$row["obj_id"]] = true;
		}

		return $all;
	}

	/**
	 * @return int
	 */
	public function readActive()
	{
		$set    = $this->db->query("SELECT obj_id FROM il_certificate WHERE obj_id = " . $this->db->quote($this->objectId, "integer"));
		return $this->db->numRows($set);
	}

	/**
	 * @param $a_value bool
	 */
	public function writeActive($a_value)
	{
		if((bool)$a_value)
		{
			$this->db->replace("il_certificate", array("obj_id" => array("integer", $this->objectId)), array());
		}
		else
		{
			$this->db->manipulate("DELETE FROM il_certificate WHERE obj_id = " . $this->db->quote($this->objectId, "integer"));
		}
	}
	
	/**
	* Creates a redirect to a certificate download
	*
	* @param integer $ref_id Ref ID of the ILIAS object
	*/
	public static function _goto($ref_id)
	{
		/**
		 * @var $ilCtrl ilCtrl
		 */
		global $DIC;

		$ilCtrl = $DIC['ilCtrl'];

		include_once "./Services/Object/classes/class.ilObject.php";
		$type = ilObject::_lookupType($ref_id, true);
		switch ($type)
		{
			case 'sahs':
				$cmd_link = "ilias.php?baseClass=ilSAHSPresentationGUI&ref_id=".$ref_id.
					"&cmd=downloadCertificate";
				ilUtil::redirect($cmd_link);
				break;
			case 'tst':
			default:
				$ilCtrl->redirectByClass("ilrepositorygui", "frameset");
				break;
		}
	}
	
	/**
	 * Get custom certificate fields
	 */
	static function getCustomCertificateFields()
	{
		include_once("./Services/User/classes/class.ilUserDefinedFields.php");
		$user_field_definitions = ilUserDefinedFields::_getInstance();
		$fds = $user_field_definitions->getDefinitions();
		$fields = array();
		foreach ($fds as $f)
		{
			if ($f["certificate"])
			{
				$fields[$f["field_id"]] = array("name" => $f["field_name"],
					"ph" => "[#".str_replace(" ", "_", strtoupper($f["field_name"]))."]");
			}
		}
		
		return $fields;
	}

	/**
	 * @return string
	 */
	public function getExchangeContent()
	{
		if(!file_exists($this->getXSLPath()))
		{
			return '';
		}

		$output           = '';
		$xsl_file_content = file_get_contents($this->getXSLPath());
		$xsl              = file_get_contents("./Services/Certificate/xml/fo2xhtml.xsl");

		if((strlen($xsl_file_content)) && (strlen($xsl)))
		{
			$args   = array('/_xml' => $xsl_file_content, '/_xsl' => $xsl);
			$xh     = xslt_create();
			$output = xslt_process($xh, "arg:/_xml", "arg:/_xsl", NULL, $args, NULL);
			xslt_error($xh);
			xslt_free($xh);
		}

		$output = preg_replace("/<\?xml[^>]+?>/", "", $output);
		// dirty hack: the php xslt processing seems not to recognize the following
		// replacements, so we do it in the code as well
		$output = str_replace("&#xA0;", "<br />", $output);
		$output = str_replace("&#160;", "<br />", $output);

		return $output;
	}

	/**
	 * @param string $content
	 * @param array $insert_tags
	 * @return bool
	 * @throws Exception
	 */
	public function outCertificateWithGivenContentAndVariables($content, array $insert_tags)
	{
		ilDatePresentation::setUseRelativeDates(false);

		$form_fields = $this->getFormFieldsFromFO();
		$form_fields['certificate_text'] = $content;
		$xslfo = $this->processXHTML2FO($form_fields);

		$content = $this->exchangeCertificateVariables($xslfo, $insert_tags);
		$content = str_replace('[BR]', "<fo:block/>", $content);

		try
		{
			$pdf_base64 = ilRpcClientFactory::factory('RPCTransformationHandler')->ilFO2PDF($content);
			ilUtil::deliverData($pdf_base64->scalar, $this->getAdapter()->getCertificateFilename(array()), "application/pdf");
		}
		catch(Exception $e)
		{
			$this->log->write(__METHOD__.': '.$e->getMessage());
			return false;
		}

		ilDatePresentation::setUseRelativeDates(true);
	}
}
