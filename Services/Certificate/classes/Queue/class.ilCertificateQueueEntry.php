<?php


class ilCertificateQueueEntry
{
	/**
	 * @var int
	 */
	private $objId;

	/**
	 * @var int
	 */
	private $userId;

	/**
	 * @var string
	 */
	private $adapterClass;

	/**
	 * @var string
	 */
	private $state;

	/**
	 * @var int
	 */
	private $startedTimestamp;

	/**
	 * @param integer $objId
	 * @param integer $userId
	 * @param string $adapterClass
	 * @param string $state
	 * @param integer|null $startedTimestamp
	 */
	public function __construct(
		$objId,
		$userId,
		$adapterClass,
		$state,
		$startedTimestamp = null
	) {
		$this->objId = $objId;
		$this->userId = $userId;
		$this->adapterClass = $adapterClass;
		$this->state = $state;
		$this->startedTimestamp = $startedTimestamp;
	}

	/**
	 * @return int
	 */
	public function getObjId(): int
	{
		return $this->objId;
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
	public function getAdapterClass(): string
	{
		return $this->adapterClass;
	}

	/**
	 * @return string
	 */
	public function getState(): string
	{
		return $this->state;
	}

	/**
	 * @return int
	 */
	public function getStartedTimestamp(): ?int
	{
		return $this->startedTimestamp;
	}
}
