<?php


class ilCertificateTemplate
{
	/**
	 * @var int
	 */
	private $obj_id;

	/**
	 * @var string
	 */
	private $certificateContent;

	/**
	 * @var string
	 */
	private $certificateHash;

	/**
	 * @var string
	 */
	private $templateValues;

	/**
	 * @var string
	 */
	private $version;

	/**
	 * @var string
	 */
	private $iliasVersion;

	/**
	 * @var int
	 */
	private $createdTimestamp;

	/**
	 * @var bool
	 */
	private $currentlyActive;

	/**
	 * @var int|null
	 */
	private $id;

	/**
	 * @var string|null
	 */
	private $backgroundImagePath;

	/**
	 * @var string
	 */
	private $obj_type;

	/**
	 * @param integer $obj_id
	 * @param $obj_type
	 * @param string $certificateContent
	 * @param string $certificateHash
	 * @param string $templateValues
	 * @param string $version
	 * @param string $iliasVersion
	 * @param integer $createdTimestamp
	 * @param boolean $currentlyActive
	 * @param null $backgroundImagePath
	 * @param integer|null $id
	 */
	public function __construct(
		$obj_id,
		$obj_type,
		$certificateContent,
		$certificateHash,
		$templateValues,
		$version,
		$iliasVersion,
		$createdTimestamp,
		$currentlyActive,
		$backgroundImagePath = null,
		$id = null
	) {
		$this->obj_id = $obj_id;
		$this->obj_type = $obj_type;
		$this->certificateContent = $certificateContent;
		$this->certificateHash = $certificateHash;
		$this->templateValues = $templateValues;
		$this->version = $version;
		$this->iliasVersion = $iliasVersion;
		$this->createdTimestamp = $createdTimestamp;
		$this->currentlyActive = $currentlyActive;
		$this->backgroundImagePath = $backgroundImagePath;
		$this->id = $id;
	}

	/**
	 * @return int
	 */
	public function getObjId(): int
	{
		return $this->obj_id;
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
	public function getCertificateHash(): string
	{
		return $this->certificateHash;
	}

	/**
	 * @return string
	 */
	public function getTemplateValues(): string
	{
		return $this->templateValues;
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
	 * @return int
	 */
	public function getCreatedTimestamp(): int
	{
		return $this->createdTimestamp;
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
	public function getId()
	{
		return $this->id;
	}

	/**
	 * @return string
	 */
	public function getBackgroundImagePath()
	{
		return $this->backgroundImagePath;
	}

	/**
	 * @return string
	 */
	public function getObjType(): string
	{
		return $this->obj_type;
	}
}
