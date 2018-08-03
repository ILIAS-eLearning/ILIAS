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
	* Returns the filesystem path of the background image
	* @param  bool $asRelative
	* @return string The filesystem path of the background image
	*/
	public function getBackgroundImageDirectory($asRelative = false, $backgroundImagePath = '')
	{
		if($asRelative)
		{
			return str_replace(
				array(CLIENT_WEB_DIR, '//'),
				array('[CLIENT_WEB_DIR]', '/'),
				$backgroundImagePath
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

		return str_replace(
			ilUtil::removeTrailingPathSeparators(ILIAS_ABSOLUTE_PATH),
			ilUtil::removeTrailingPathSeparators(ILIAS_HTTP_PATH),
			$webdir
		);
	}
	
	/**
	* Returns the web path of the background image thumbnail
	*
	* @return string The web path of the background image thumbnail
	*/
	public function getBackgroundImageThumbPathWeb()
	{
		// TODO: this is generic now -> provide better solution
		return str_replace(
			ilUtil::removeTrailingPathSeparators(ILIAS_ABSOLUTE_PATH),
			ilUtil::removeTrailingPathSeparators(ILIAS_HTTP_PATH),
			$this->getBackgroundImageThumbPath()
		);
	}
	
	/**
	* Deletes the background image of a certificate
	*
	* @return boolean TRUE if the process succeeds
	*/
	public function deleteBackgroundImage($version)
	{
		$result = true;
		if (file_exists($this->getBackgroundImageThumbPath())) {
			$result = $result & unlink($this->getBackgroundImageThumbPath());
		}

		$filename = $this->getBackgroundImageDirectory() . 'background_' . $version . '.jpg';
		if (file_exists($filename)) {
			$result = $result & unlink($filename);
		}

		if (file_exists($this->getBackgroundImageTempfilePath())) {
			$result = $result & unlink($this->getBackgroundImageTempfilePath());
		}

		return $result;
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

		try {
			$pdf_base64 = ilRpcClientFactory::factory('RPCTransformationHandler')
				->ilFO2PDF($this->exchangeCertificateVariables($xslfo));

			ilUtil::deliverData(
				$pdf_base64->scalar,
				$this->getAdapter()->getCertificateFilename(),
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
	* Saves the XSL-FO code to a file
	*
	* @param string $xslfo XSL-FO code
	*/
	public function createCertificateFile($xslfo, $filename = '')
	{
		if (!file_exists($this->certificatePath)) {
			ilUtil::makeDirParents($this->certificatePath);
		}

		if (strlen($filename) == 0) {
			$filename = $this->getXSLPath();
		}

		$fileHandle = fopen($filename, "w");
		fwrite($fileHandle, $xslfo);
		fclose($fileHandle);
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
		if (!empty($image_tempfilename)) {
			$convert_filename = $this->getBackgroundImageName();

			$imagepath = $this->certificatePath;

			if (!file_exists($imagepath)) {
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
			$backgroundImagePath = $this->getBackgroundImageDirectory() . 'background_' . $version . '.jpg';

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
	 * @throws ilException
	 */
	public function hasBackgroundImage()
	{
		$template = $this->templateRepository->fetchCurrentlyActiveCertificate($this->objectId);

		if (file_exists($template->getBackgroundImagePath())
			&& (filesize($template->getBackgroundImagePath()) > 0)
		) {
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
	* Builds an export file in ZIP format and delivers it
	*/
	public function deliverExportFileXML()
	{
		$exportpath = $this->createArchiveDirectory();
		ilUtil::makeDir($exportpath);

		$adapter = $this->getAdapter();
		$objId = $adapter->getCertificateID();

		$templates = $this->templateRepository->fetchCertificateTemplatesByObjId($objId);

		/** @var ilCertificateTemplate $template */
		foreach ($templates as $template) {
			$xslExport = $template->getCertificateContent();
			$version = $template->getVersion();
			$this->createCertificateFile($xslExport, $exportpath . 'certificate_' . $version . ' .xml');
			$backgroundImagePath = $template->getBackgroundImagePath();

			if ($backgroundImagePath !== null && $backgroundImagePath !== '') {
				copy($backgroundImagePath, $exportpath . basename($backgroundImagePath));
			}
			$zipfile = time() . "__" . IL_INST_ID . "__" . $this->getAdapter()->getAdapterType() . "__" . $this->objectId . "__certificate.zip";
			ilUtil::zip($exportpath, $this->certificatePath . $zipfile);
		}


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
		$importPath = $this->createArchiveDirectory();
		if (!ilUtil::moveUploadedFile($zipfile, $filename, $importPath . $filename)) {
			ilUtil::delDir($importPath);
			return FALSE;
		}

		ilUtil::unzip($importPath . $filename, TRUE);
		$subDirectory = str_replace(".zip", "", strtolower($filename)) . "/";
		$copyDirectory = $importPath;

		if (is_dir($importPath . $subDirectory)) {
			$importPath = $importPath .$subDirectory;
			$copyDirectory = $importPath . $subDirectory;
		}

		$directoryInfo = ilUtil::getDir($importPath);

		$xmlFiles = 0;
		$otherFiles = 0;

		foreach ($directoryInfo as $file) {
			if (strcmp($file["type"], "file") == 0) {
				if (strpos($file["entry"], ".xml") !== false) {
					$xmlFiles++;
				}
				else if (strpos($file["entry"], ".zip") !== false)
				{}
				else {
					$otherFiles++;
				}
			}
		}

		// if one XML file is in the archive, we try to import it
		if ($xmlFiles == 1) {
			$version = 1;
			foreach ($directoryInfo as $file) {
				if (strcmp($file["type"], "file") == 0) {
					if (strpos($file["entry"], '.xml') !== false) {
						$xsl = file_get_contents($copyDirectory . $file["entry"]);
						// as long as we cannot make RPC calls in a given directory, we have
						// to add the complete path to every url
						$xsl = preg_replace_callback("/url\([']{0,1}(.*?)[']{0,1}\)/", function(array $matches) {
							$basePath = rtrim(dirname($this->getBackgroundImageDirectory(true)), '/');
							$fileName = basename($matches[1]);

							return 'url(' . $basePath . '/' . $fileName . ')';
						}, $xsl);

						$currentCertificate = $this->templateRepository->fetchCurrentlyActiveCertificate($this->objectId);

						$version = $currentCertificate->getVersion();

						$template = new ilCertificateTemplate(
							$this->objectId,
							$xsl,
							md5($xsl),
							json_encode($this->placeholderDescriptionObject->getPlaceholderDescriptions()),
							$version + 1,
							ILIAS_VERSION_NUMERIC,
							time(),
							true
						);

						$this->templateRepository->save($template);
					}
					else if (strpos($file["entry"], '.zip') !== false)
					{}
					else if (strpos($file["entry"], '.jpg') !== false) {
						@copy($copyDirectory . $file["entry"], $this->certificatePath . $file["entry"]);
						$backgroundImage = 'background_' . $version . '.jpg';

						if (strcmp($this->getBackgroundImageDirectory() . $backgroundImage, $this->certificatePath . $file["entry"]) == 0) {
							// upload of the background image, create a preview
							ilUtil::convertImage(
								$this->getBackgroundImageDirectory() . $backgroundImage,
								$this->getBackgroundImageThumbPath(),
								'JPEG',
								100
							);
						}
					}
				}
			}
		} else {
			ilUtil::delDir($importPath);
			return false;
		}

		ilUtil::delDir($importPath);
		return true;
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
		if ($deliver) {
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

		$set = $ilDB->query("SELECT obj_id FROM il_certificate WHERE " . $ilDB->in("obj_id", $a_obj_ids, "", "integer"));
		while($row = $ilDB->fetchAssoc($set)) {
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
		if((bool)$a_value) {
			$this->db->replace("il_certificate", array("obj_id" => array("integer", $this->objectId)), array());
		} else {
			$this->db->manipulate("DELETE FROM il_certificate WHERE obj_id = " . $this->db->quote($this->objectId, "integer"));
		}
	}

	/**
	 * Get custom certificate fields
	 */
	static function getCustomCertificateFields()
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
