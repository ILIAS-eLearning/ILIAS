<?php
/* Copyright (c) 1998-2016 ILIAS open source, Extended GPL, see docs/LICENSE */

/**
 * Class ilMailCachedAddressType
 */
class ilMailCachedAddressType implements \ilMailAddressType
{
	/** @var array[] */
	protected static $usrIdsByAddressCache = [];

	/** @var bool[] */
	protected static $isValidCache = [];
	
	/** @var \ilMailAddressType */
	protected $inner;

	/** @var bool */
	protected $useCache = true;

	/**
	 * ilMailCachedRoleAddressType constructor.
	 * @param \ilMailAddressType $inner
	 * @param bool               $useCache
	 */
	public function __construct(\ilMailAddressType $inner, bool $useCache) {
		$this->inner = $inner;
		$this->useCache = $useCache;
	}

	/**
	 * @inheritdoc
	 */
	public function validate(int $senderId): bool
	{
		if (!$this->useCache || !isset(self::$isValidCache[$senderId])) {
			self::$isValidCache[$senderId] = $this->inner->validate($senderId);
		}

		return self::$isValidCache[$senderId];
	}

	/**
	 * @inheritdoc
	 */
	public function getErrors(): array
	{
		return $this->inner->getErrors();
	}

	/**
	 * @inheritdoc
	 */
	public function getAddress(): \ilMailAddress
	{
		return $this->inner->getAddress();
	}

	/**
	 * @inheritdoc
	 */
	public function resolve(): array
	{
		$address = $this->getAddress();
		$cacheKey = (string)$address;

		if (!$this->useCache || !isset(self::$usrIdsByAddressCache[$cacheKey])) {
			self::$usrIdsByAddressCache[$cacheKey] = $this->inner->resolve();
		}

		return self::$usrIdsByAddressCache[$cacheKey];
	}
}