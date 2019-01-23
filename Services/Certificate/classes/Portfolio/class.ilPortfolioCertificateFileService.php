<?php
/* Copyright (c) 1998-2019 ILIAS open source, Extended GPL, see docs/LICENSE */

use ILIAS\Filesystem\Filesystem;

/**
 * @author  Niels Theen <ntheen@databay.de>
 */
class ilPortfolioCertificateFileService
{
	/**
	 * @var Filesystem
	 */
	private $filesystem;

	/**
	 * @param Filesystem|null $filesystem
	 */
	public function __construct(Filesystem $filesystem = null)
	{
		if (null === $filesystem) {
			global $DIC;
			$filesystem = $DIC->filesystem()->storage();
		}
		$this->filesystem = $filesystem;
	}

	/**
	 * @param int $userId
	 * @param int $objectId
	 * @throws \ILIAS\Filesystem\Exception\FileAlreadyExistsException
	 * @throws \ILIAS\Filesystem\Exception\IOException
	 * @throws ilException
	 */
	public function createCertificateFile(int $userId, int $objectId)
	{
		$userCertificateRepository = new ilUserCertificateRepository();

		$userCertificate = $userCertificateRepository->fetchActiveCertificate($userId, $objectId);

		$dirPath = 'PersistentCertificates/' . $userId . '/' . $objectId;
		if (false === $this->filesystem->hasDir($dirPath)) {
			$this->filesystem->createDir($dirPath);
		}

		$pdfGenerator = new ilPdfGenerator($userCertificateRepository, $this->log);

		$pdfScalar = $pdfGenerator->generate($userCertificate->getId());

		$this->filesystem->write($dirPath . '/certificate.pdf', $pdfScalar);
	}

	/**
	 * @param $userId
	 * @param $objectId
	 * @throws ilException
	 * @throws ilFileUtilsException
	 */
	public function deliverCertificate($userId, $objectId)
	{
		$dirPath = 'PersistentCertificates/' . $userId . '/' . $objectId;
		$fileName = 'certificate.pdf';

		$completePath = $dirPath . '/' . $fileName;
		if($this->filesystem->has($completePath)) {
			$userCertificateRepository = new ilUserCertificateRepository();

			$userCertificate = $userCertificateRepository->fetchActiveCertificateForPresentation($userId, $objectId);

			$downloadFilePath = CLIENT_DATA_DIR . '/' . $completePath;
			$delivery = new \ilFileDelivery($downloadFilePath);
			$delivery->setMimeType(\ilMimeTypeUtil::APPLICATION__PDF);
			$delivery->setConvertFileNameToAsci(true);
			$delivery->setDownloadFileName(\ilFileUtils::getValidFilename($userCertificate->getObjectTitle() . '.pdf'));

			$delivery->deliver();
		}
	}
}
