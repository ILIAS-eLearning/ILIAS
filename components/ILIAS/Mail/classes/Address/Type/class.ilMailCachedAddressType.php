<?php

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

declare(strict_types=1);

class ilMailCachedAddressType implements ilMailAddressType
{
    /** @var array<string, int[]>  */
    protected static array $usr_ids_by_address_cache = [];
    /** @var array<string, bool> */
    protected static array $is_valid_cache = [];

    public function __construct(protected ilMailAddressType $inner, protected bool $use_cache)
    {
    }

    public static function clearCache(): void
    {
        self::$is_valid_cache = [];
        self::$usr_ids_by_address_cache = [];
    }

    private function getCacheKey(): string
    {
        $address = $this->getAddress();
        return (string) $address;
    }

    public function validate(int $sender_id): bool
    {
        $cache_key = $this->getCacheKey();
        if (!$this->use_cache || !isset(self::$is_valid_cache[$cache_key])) {
            self::$is_valid_cache[$cache_key] = $this->inner->validate($sender_id);
        }

        return self::$is_valid_cache[$cache_key];
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
        $cache_key = $this->getCacheKey();
        if (!$this->use_cache || !isset(self::$usr_ids_by_address_cache[$cache_key])) {
            self::$usr_ids_by_address_cache[$cache_key] = $this->inner->resolve();
        }

        return self::$usr_ids_by_address_cache[$cache_key];
    }
}
