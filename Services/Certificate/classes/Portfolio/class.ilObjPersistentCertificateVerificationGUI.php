<?php
/* Copyright (c) 1998-2019 ILIAS open source, Extended GPL, see docs/LICENSE */

/**
 * @author  Niels Theen <ntheen@databay.de>
 */
class ilObjPersistentCertificateVerificationGUI extends ilObject2GUI
{
	/**
	 * @var
	 */
	private $dic;

	/**
	 * @var ilPortfolioCertificateFileService
	 */
	private $fileService;

	public function __construct(
		int $a_id = 0,
		int $a_id_type = self::REPOSITORY_NODE_ID,
		int $a_parent_node_id = 0,
		\ILIAS\DI\Container $dic = null,
		ilPortfolioCertificateFileService $fileService = null
	) {
		if (null === $dic) {
			global $DIC;
			$dic = $DIC;
		}
		$this->dic = $dic;

		if (null === $fileService) {
			$fileService = new ilPortfolioCertificateFileService();
		}
		$this->fileService = $fileService;

		parent::__construct($a_id, $a_id_type, $a_parent_node_id);
	}

	public function downloadFromPortfolioPage(ilPortfolioPage $a_page)
	{
		$objectId = $this->object->getId();

		if(ilPCVerification::isInPortfolioPage($a_page, 'crta', (int) $objectId)) {
			$userId = $this->user->getId();
			$this->fileService->deliverCertificate((int) $userId, (int) $objectId);
		}

		throw new ilException($this->lng->txt('permission_denied'));
	}

	/**
	 * Functions that must be overwritten
	 */
	function getType()
	{
		return 'crta';
	}
}
