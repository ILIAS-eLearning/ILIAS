<?php


class ilUserCertificateTemplate
{
	/**
	 * @var int
	 */
	private $patternCertificateId;

	/**
	 * @var int
	 */
	private $objId;

	/**
	 * @var string
	 */
	private $objType;

	/**
	 * @var int
	 */
	private $userId;

	/**
	 * @var string
	 */
	private $userName;

	/**
	 * @var int
	 */
	private $acquiredTimestamp;

	/**
	 * @var string
	 */
	private $certificateContent;

	/**
	 * @var string
	 */
	private $templateValues;

	/**
	 * @var int
	 */
	private $validUntil;

	/**
	 * @var string
	 */
	private $version;

	/**
	 * @var string
	 */
	private $iliasVersion;

	/**
	 * @var bool
	 */
	private $currentlyActive;

	/**
	 * @var int|null
	 */
	private $id;

	/**
	 * @param integer $patternCertificateId
	 * @param integer $objId
	 * @param string $objType
	 * @param integer $userId
	 * @param string $userName
	 * @param integer $acquiredTimestamp
	 * @param string $certificateContent
	 * @param string $templateValues
	 * @param integer $validUntil
	 * @param string $version
	 * @param string $iliasVersion
	 * @param boolean $currentlyActive
	 * @param integer|null $id
	 */
	public function __construct(
		$patternCertificateId,
		$objId,
		$objType,
		$userId,
		$userName,
		$acquiredTimestamp,
		$certificateContent,
		$templateValues,
		$validUntil,
		$version,
		$iliasVersion,
		$currentlyActive,
		$id = null
	) {
		$this->patternCertificateId = $patternCertificateId;
		$this->objId = $objId;
		$this->objType = $objType;
		$this->userId = $userId;
		$this->userName = $userName;
		$this->acquiredTimestamp = $acquiredTimestamp;
		$this->certificateContent = $certificateContent;
		$this->templateValues = $templateValues;
		$this->validUntil = $validUntil;
		$this->version = $version;
		$this->iliasVersion = $iliasVersion;
		$this->currentlyActive = $currentlyActive;
		$this->id = $id;
	}

	/**
	 * @return int
	 */
	public function getPatternCertificateId(): int
	{
		return $this->patternCertificateId;
	}

	/**
	 * @return int
	 */
	public function getObjId(): int
	{
		return $this->objId;
	}

	/**
	 * @return string
	 */
	public function getObjType(): string
	{
		return $this->objType;
	}

	/**
	 * @return int
	 */
	public function getUserId(): int
	{
		return $this->userId;
	}

	/**
	 * @return string
	 */
	public function getUserName(): string
	{
		return $this->userName;
	}

	/**
	 * @return int
	 */
	public function getAcquiredTimestamp(): int
	{
		return $this->acquiredTimestamp;
	}

	/**
	 * @return string
	 */
	public function getCertificateContent(): string
	{
		return $this->certificateContent;
	}

	/**
	 * @return string
	 */
	public function getTemplateValues(): string
	{
		return $this->templateValues;
	}

	/**
	 * @return int
	 */
	public function getValidUntil()
	{
		return $this->validUntil;
	}

	/**
	 * @return string
	 */
	public function getVersion(): string
	{
		return $this->version;
	}

	/**
	 * @return string
	 */
	public function getIliasVersion(): string
	{
		return $this->iliasVersion;
	}

	/**
	 * @return bool
	 */
	public function isCurrentlyActive(): bool
	{
		return $this->currentlyActive;
	}

	/**
	 * @return int|null
	 */
	public function getId(): int
	{
		return $this->id;
	}
}
