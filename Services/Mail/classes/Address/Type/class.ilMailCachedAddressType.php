<?php

declare(strict_types=1);

/**
 * This file is part of ILIAS, a powerful learning management system
 * published by ILIAS open source e-Learning e.V.
 *
 * ILIAS is licensed with the GPL-3.0,
 * see https://www.gnu.org/licenses/gpl-3.0.en.html
 * You should have received a copy of said license along with the
 * source code, too.
 *
 * If this is not the case or you just want to try ILIAS, you'll find
 * us at:
 * https://www.ilias.de
 * https://github.com/ILIAS-eLearning
 *
 *********************************************************************/

/**
 * Class ilMailCachedAddressType
 */
class ilMailCachedAddressType implements ilMailAddressType
{
    /** @var array<string, int[]>  */
    protected static array $usrIdsByAddressCache = [];
    /** @var array<string, bool> */
    protected static array $isValidCache = [];

    public function __construct(protected ilMailAddressType $inner, protected bool $useCache)
    {
    }

    public static function clearCache(): void
    {
        self::$isValidCache = [];
        self::$usrIdsByAddressCache = [];
    }

    private function getCacheKey(): string
    {
        $address = $this->getAddress();
        return (string) $address;
    }

    public function validate(int $senderId): bool
    {
        $cacheKey = $this->getCacheKey();

        if (!$this->useCache || !isset(self::$isValidCache[$cacheKey])) {
            self::$isValidCache[$cacheKey] = $this->inner->validate($senderId);
        }

        return self::$isValidCache[$cacheKey];
    }

    public function getErrors(): array
    {
        return $this->inner->getErrors();
    }

    public function getAddress(): ilMailAddress
    {
        return $this->inner->getAddress();
    }

    public function resolve(): array
    {
        $cacheKey = $this->getCacheKey();

        if (!$this->useCache || !isset(self::$usrIdsByAddressCache[$cacheKey])) {
            self::$usrIdsByAddressCache[$cacheKey] = $this->inner->resolve();
        }

        return self::$usrIdsByAddressCache[$cacheKey];
    }
}
