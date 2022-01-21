<?php declare(strict_types=1);
/* Copyright (c) 1998-2021 ILIAS open source, Extended GPL, see docs/LICENSE */

/**
 * Class ilMailCachedAddressType
 */
class ilMailCachedAddressType implements ilMailAddressType
{
    /** @var array<string, int[]>  */
    protected static array $usrIdsByAddressCache = [];
    /** @var array<string, bool> */
    protected static array $isValidCache = [];
    protected ilMailAddressType $inner;
    protected bool $useCache = true;

    public function __construct(ilMailAddressType $inner, bool $useCache)
    {
        $this->inner = $inner;
        $this->useCache = $useCache;
    }
    
    public static function clearCache() : void
    {
        self::$isValidCache = [];
        self::$usrIdsByAddressCache = [];
    }

    private function getCacheKey() : string
    {
        $address = $this->getAddress();
        return (string) $address;
    }

    public function validate(int $senderId) : bool
    {
        $cacheKey = $this->getCacheKey();

        if (!$this->useCache || !isset(self::$isValidCache[$cacheKey])) {
            self::$isValidCache[$cacheKey] = $this->inner->validate($senderId);
        }

        return self::$isValidCache[$cacheKey];
    }

    public function getErrors() : array
    {
        return $this->inner->getErrors();
    }

    public function getAddress() : ilMailAddress
    {
        return $this->inner->getAddress();
    }

    public function resolve() : array
    {
        $cacheKey = $this->getCacheKey();

        if (!$this->useCache || !isset(self::$usrIdsByAddressCache[$cacheKey])) {
            self::$usrIdsByAddressCache[$cacheKey] = $this->inner->resolve();
        }

        return self::$usrIdsByAddressCache[$cacheKey];
    }
}
