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

namespace ILIAS\Data;

/**
 * A version number that consists of three numbers (major, minor, patch).
 */
class Version
{
    private const REGEXP = '(?<major>\d+)([.](?<minor>\d+)([.](?<patch>\d+))?)?';

    protected int $major;
    protected int $minor;
    protected int $patch;

    public function __construct(string $version)
    {
        $match = [];
        if (!preg_match("/" . self::REGEXP . "/", $version, $match)) {
            throw new \InvalidArgumentException(
                "Expected version string '$version' to match this regular expression: " . self::REGEXP
            );
        }
        $this->major = (int) $match["major"];
        $this->minor = (int) ($match["minor"] ?? 0);
        $this->patch = (int) ($match["patch"] ?? 0);
    }

    public function getMajor(): int
    {
        return $this->major;
    }

    public function getMinor(): int
    {
        return $this->minor;
    }

    public function getPatch(): int
    {
        return $this->patch;
    }

    public function equals(Version $other): bool
    {
        return
            $this->major === $other->major
            && $this->minor === $other->minor
            && $this->patch === $other->patch;
    }

    public function isGreaterThan(Version $other): bool
    {
        return version_compare((string) $this, (string) $other, '>');
    }

    public function isGreaterThanOrEquals(Version $other): bool
    {
        return $this->equals($other) || $this->isGreaterThan($other);
    }

    public function isSmallerThan(Version $other): bool
    {
        return $other->isGreaterThan($this);
    }

    public function isSmallerThanOrEquals(Version $other): bool
    {
        return $other->isGreaterThan($this) || $this->equals($other);
    }

    public function __toString()
    {
        return "$this->major.$this->minor.$this->patch";
    }
}
