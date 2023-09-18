<?php

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
        if ($this->major > $other->major) {
            return true;
        }
        if ($this->major < $other->major) {
            return false;
        }
        if ($this->minor > $other->minor) {
            return true;
        }
        if ($this->minor < $other->major) {
            return false;
        }
        return $this->patch > $other->patch;
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
