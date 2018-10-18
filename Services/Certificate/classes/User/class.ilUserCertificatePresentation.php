<?php
/* Copyright (c) 1998-2018 ILIAS open source, Extended GPL, see docs/LICENSE */

/**
 * @author  Niels Theen <ntheen@databay.de>
 */
class ilUserCertificatePresentation
{
	/**
	 * @var ilUserCertificate 
	 */
	private $userCertificate;

	/**
	 * @var string 
	 */
	private $objectTitle;

	/**
	 * @var string 
	 */
	private $objectDescription;

	/**
	 * @param ilUserCertificate $userCertificate
	 * @param string $objectTitle
	 * @param string $objectDescription
	 */
	public function __construct(ilUserCertificate $userCertificate, string $objectTitle, string $objectDescription)
	{
		$this->userCertificate = $userCertificate;
		$this->objectTitle = $objectTitle;
		$this->objectDescription = $objectDescription;
	}

	/**
	 * @return ilUserCertificate
	 */
	public function getUserCertificate(): ilUserCertificate
	{
		return $this->userCertificate;
	}

	/**
	 * @return string
	 */
	public function getObjectTitle(): string
	{
		return $this->objectTitle;
	}

	/**
	 * @return string
	 */
	public function getObjectDescription(): string
	{
		return $this->objectDescription;
	}
}
