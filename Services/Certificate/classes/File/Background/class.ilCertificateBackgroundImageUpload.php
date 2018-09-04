<?php
/* Copyright (c) 1998-2018 ILIAS open source, Extended GPL, see docs/LICENSE */

/**
 * @author  Niels Theen <ntheen@databay.de>
 */
class ilCertificateBackgroundImageUpload
{
	const BACKGROUND_IMAGE_NAME = 'background.jpg';
	const BACKGROUND_THUMBNAIL_IMAGE_NAME = 'background.jpg.thumb.jpg';
	const BACKGROUND_TEMPORARY_FILENAME = 'background_upload.tmp';

	/**
	 * @var \ILIAS\FileUpload\FileUpload
	 */
	private $fileUpload;

	/**
	 * @var string
	 */
	private $certificatePath;

	/**
	 * @var ilLanguage
	 */
	private $language;

	/**
	 * @param \ILIAS\FileUpload\FileUpload $fileUpload
	 * @param string $certificatePath
	 * @param ilLanguage $language
	 */
	public function __construct(\ILIAS\FileUpload\FileUpload $fileUpload, string $certificatePath, ilLanguage $language)
	{
		$this->fileUpload = $fileUpload;
		$this->certificatePath = $certificatePath;
		$this->language = $language;
	}

	/**
	 * Uploads a background image for the certificate. Creates a new directory for the
	 * certificate if needed. Removes an existing certificate image if necessary
	 *
	 * @param string $imageTempFilename Name of the temporary uploaded image file
	 * @param $version - Version of the current certifcate template
	 * @return integer An errorcode if the image upload fails, 0 otherwise
	 * @throws ilException
	 * @throws \ILIAS\FileUpload\Exception\IllegalStateException
	 */
	public function upload(string $imageTempFilename, int $version)
	{
		$imagepath = CLIENT_WEB_DIR . $this->certificatePath;

		if (!file_exists($imagepath)) {
			ilUtil::makeDirParents($imagepath);
		}

		$backgroundImageTempFilePath = $this->createBackgroundImageTempfilePath();

		$this->uploadFile($imageTempFilename, $backgroundImageTempFilePath);

		$backgroundImagePath = CLIENT_WEB_DIR . $this->certificatePath . 'background_' . $version . '.jpg';
		ilUtil::convertImage($backgroundImageTempFilePath, $backgroundImagePath, 'JPEG');

		$backgroundImageThumbnailPath = $this->createBackgroundImageThumbPath();
		ilUtil::convertImage($backgroundImageTempFilePath, $backgroundImageThumbnailPath, 'JPEG', 100);

		$convert_filename = self::BACKGROUND_IMAGE_NAME;

		if (!file_exists($backgroundImagePath)) {
			// something went wrong converting the file. use the original file and hope, that PDF can work with it
			if (!ilUtil::moveUploadedFile($backgroundImageTempFilePath, $convert_filename, $backgroundImagePath)) {
				throw new ilException('Unable to convert the file and the original file');
			}
		}

		unlink($backgroundImageTempFilePath);

		if (file_exists($backgroundImagePath) && (filesize($backgroundImagePath) > 0)) {
			return $this->certificatePath . 'background_' . $version . '.jpg';
		}

		throw new ilException('The given temporary filename is empty');
	}

	/**
	 * @param string $temporaryFilename
	 * @param string $targetFileName
	 * @throws \ILIAS\FileUpload\Exception\IllegalStateException
	 * @throws ilException
	 * @throws ilFileUtilsException
	 */
	private function uploadFile(string $temporaryFilename, string $targetFileName)
	{
		$targetFilename = basename($targetFileName);
		$targetFilename = ilFileUtils::getValidFilename($targetFilename);

		$targetFilesystem = $this->getTargetFilesystem($targetFileName);
		$targetDir = $this->getTargetDir($targetFileName);

		if (false === $this->fileUpload->hasBeenProcessed()) {
			$this->fileUpload->process();
		}

		if (false === $this->fileUpload->hasUploads()) {
			throw new ilException($this->language->txt('upload_error_file_not_found'));
		}

		/**
		 * @var \ILIAS\FileUpload\DTO\UploadResult $uploadResult
		 */
		$uploadResult = $this->fileUpload->getResults()[$temporaryFilename];
		$processingStatus = $uploadResult->getStatus();
		if ($processingStatus->getCode() === ILIAS\FileUpload\DTO\ProcessingStatus::REJECTED) {
			throw new ilException($processingStatus->getMessage());
		}

		$this->fileUpload->moveOneFileTo(
			$uploadResult,
			$targetDir,
			$targetFilesystem,
			$targetFilename,
			true
		);
	}

	/**
	 * @param string $target
	 * @return int
	 */
	private function getTargetFilesystem(string $target)
	{
		switch (true) {
			case strpos($target, ILIAS_WEB_DIR . '/' . CLIENT_ID) === 0:
			case strpos($target, './' . ILIAS_WEB_DIR . '/' . CLIENT_ID) === 0:
			case strpos($target, CLIENT_WEB_DIR) === 0:
				$targetFilesystem = \ILIAS\FileUpload\Location::WEB;
				break;
			case strpos($target, CLIENT_DATA_DIR . "/temp") === 0:
				$targetFilesystem = \ILIAS\FileUpload\Location::TEMPORARY;
				break;
			case strpos($target, CLIENT_DATA_DIR) === 0:
				$targetFilesystem = \ILIAS\FileUpload\Location::STORAGE;
				break;
			case strpos($target, ILIAS_ABSOLUTE_PATH . '/Customizing') === 0:
				$targetFilesystem = \ILIAS\FileUpload\Location::CUSTOMIZING;
				break;
			default:
				throw new InvalidArgumentException("Can not move files to \"$target\" because path can not be mapped to web, storage or customizing location.");
		}

		return $targetFilesystem;
	}

	/**
	 * @param $target
	 * @return array
	 */
	private function getTargetDir(string $target)
	{
		$absTargetDir = dirname($target);
		$targetDir = ILIAS\Filesystem\Util\LegacyPathHelper::createRelativePath($absTargetDir);

		return  $targetDir;
	}

	/**
	 * Returns the filesystem path of the background image temp file during upload
	 *
	 * @return string The filesystem path of the background image temp file
	 */
	private function createBackgroundImageTempfilePath()
	{
		return CLIENT_WEB_DIR . $this->certificatePath . self::BACKGROUND_TEMPORARY_FILENAME;
	}

	/**
	 * Returns the filesystem path of the background image thumbnail
	 *
	 * @return string The filesystem path of the background image thumbnail
	 */
	private function createBackgroundImageThumbPath()
	{
		return CLIENT_WEB_DIR . $this->certificatePath . self::BACKGROUND_THUMBNAIL_IMAGE_NAME;
	}

}
