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

namespace ILIAS\Mail\Attachments;

use InvalidArgumentException;
use ILIAS\ResourceStorage\Identification\ResourceCollectionIdentification;

final class MailAttachments
{
    public const string SERIALIZED_RCID_KEY = '__irss_rcid';

    private function __construct(
        private readonly ?ResourceCollectionIdentification $rcid,
        /** @var list<string> */
        private readonly array $legacy_filenames,
    ) {
    }

    public static function empty(): self
    {
        return new self(null, []);
    }

    public static function fromIrss(ResourceCollectionIdentification $rcid): self
    {
        return new self($rcid, []);
    }

    /**
     * @param list<string> $filenames
     */
    public static function fromLegacyFilenames(array $filenames): self
    {
        if ($filenames === []) {
            return self::empty();
        }

        return new self(null, array_values($filenames));
    }

    public static function fromDb(?string $raw): ?self
    {
        if ($raw === null || $raw === '') {
            return null;
        }

        if (str_contains($raw, 'a:')) {
            $unserialized = unserialize($raw, ['allowed_classes' => false]);
            if (!is_array($unserialized)) {
                return null;
            }

            if (isset($unserialized[self::SERIALIZED_RCID_KEY])) {
                return self::fromIrss(
                    new ResourceCollectionIdentification((string) $unserialized[self::SERIALIZED_RCID_KEY])
                );
            }

            return self::fromLegacyFilenames($unserialized);
        }

        return self::fromIrss(new ResourceCollectionIdentification($raw));
    }

    public static function fromBackgroundTask(string $serialized): self
    {
        $parsed = unserialize($serialized, ['allowed_classes' => false]);
        if (!is_array($parsed)) {
            return self::empty();
        }

        if (isset($parsed[self::SERIALIZED_RCID_KEY])) {
            return self::fromIrss(
                new ResourceCollectionIdentification((string) $parsed[self::SERIALIZED_RCID_KEY])
            );
        }

        return self::fromLegacyFilenames($parsed);
    }

    public function isEmpty(): bool
    {
        return $this->rcid === null && $this->legacy_filenames === [];
    }

    public function isIrss(): bool
    {
        return $this->rcid !== null;
    }

    public function isLegacy(): bool
    {
        return $this->rcid === null && $this->legacy_filenames !== [];
    }

    public function rcid(): ResourceCollectionIdentification
    {
        if ($this->rcid === null) {
            throw new InvalidArgumentException('Mail attachments are not stored in IRSS.');
        }

        return $this->rcid;
    }

    /**
     * @return list<string>
     */
    public function legacyFilenames(): array
    {
        if ($this->isIrss()) {
            throw new InvalidArgumentException('Mail attachments are not legacy filenames.');
        }

        return $this->legacy_filenames;
    }

    public function toDb(): string
    {
        if ($this->isIrss()) {
            return $this->rcid->serialize();
        }

        return serialize($this->legacy_filenames);
    }

    public function toBackgroundTask(): string
    {
        if ($this->isIrss()) {
            return serialize([self::SERIALIZED_RCID_KEY => $this->rcid->serialize()]);
        }

        return serialize($this->legacy_filenames);
    }

    public function stageRcidOrNull(): ?ResourceCollectionIdentification
    {
        return $this->rcid;
    }
}
