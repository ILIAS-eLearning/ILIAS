<?php

declare(strict_types=1);

/* Copyright (c) 2017 Nils Haagen <nils.haagen@concepts-and-training.de> Extended GPL, see docs/LICENSE */

namespace ILIAS\Data;

/**
 * Color expresses a certain color by giving the mixing ratio
 * in the RGB color space.
 *
 * @author Nils Haagen <nils.haagen@concepts-and-training.de>
 */
class Color
{
    protected int $r;
    protected int $g;
    protected int $b;

    public function __construct(int $r, int $g, int $b)
    {
        if ($r < 0 || $r > 255) {
            throw new \InvalidArgumentException("Unexpected value for \$r: '$r'");
        }
        if ($g < 0 || $g > 255) {
            throw new \InvalidArgumentException("Unexpected value for \$g: '$g'");
        }
        if ($b < 0 || $b > 255) {
            throw new \InvalidArgumentException("Unexpected value for \$b: '$b'");
        }
        $this->r = $r;
        $this->g = $g;
        $this->b = $b;
    }

    /**
     * Get the valule for red.
     */
    public function r(): int
    {
        return $this->r;
    }

    /**
     * Get the valule for green.
     */
    public function g(): int
    {
        return $this->g;
    }

    /**
     * Get the valule for blue.
     */
    public function b(): int
    {
        return $this->b;
    }

    /**
     * Return array with RGB-values.
     *
     * @return int[]
     */
    public function asArray(): array
    {
        return array(
            $this->r,
            $this->g,
            $this->b
        );
    }

    /**
     * Return color-value in hex-format.
     */
    public function asHex(): string
    {
        $hex = '#';
        foreach ($this->asArray() as $value) {
            $hex .= str_pad(dechex($value), 2, '0', STR_PAD_LEFT);
        }
        return $hex;
    }

    /**
     * Return string with RGB-notation
     */
    public function asRGBString(): string
    {
        return 'rgb(' . implode(', ', $this->asArray()) . ')';
    }

    /**
     * Based on https://de.wikipedia.org/wiki/Luminanz
     * this function decides if the color can be considered "dark".
     * With a dark background, i.e., a lighter (white) color should be used
     * for the foreground.
     */
    public function isDark(): bool
    {
        $sum = 0.299 * $this->r + 0.587 * $this->g + 0.114 * $this->b;

        return $sum < 128;
    }
}
