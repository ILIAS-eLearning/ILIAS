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
    public function __construct(\ilMailAddressType $inner, bool $useCache)
    {
        $this->inner = $inner;
        $this->useCache = $useCache;
    }

    /**
     * @return string
     */
    private function getCacheKey() : string
    {
        $address = $this->getAddress();
        return (string) $address;
    }

    /**
     * @inheritdoc
     */
    public function validate(int $senderId) : bool
    {
        $cacheKey = $this->getCacheKey();

        if (!$this->useCache || !isset(self::$isValidCache[$cacheKey])) {
            self::$isValidCache[$cacheKey] = $this->inner->validate($senderId);
        }

        return self::$isValidCache[$cacheKey];
    }

    /**
     * @inheritdoc
     */
    public function getErrors() : array
    {
        return $this->inner->getErrors();
    }

    /**
     * @inheritdoc
     */
    public function getAddress() : \ilMailAddress
    {
        return $this->inner->getAddress();
    }

    /**
     * @inheritdoc
     */
    public function resolve() : array
    {
        $cacheKey = $this->getCacheKey();

        if (!$this->useCache || !isset(self::$usrIdsByAddressCache[$cacheKey])) {
            self::$usrIdsByAddressCache[$cacheKey] = $this->inner->resolve();
        }

        return self::$usrIdsByAddressCache[$cacheKey];
    }
}
