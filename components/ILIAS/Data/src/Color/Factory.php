<?php

declare(strict_types=1);

/* Copyright (c) 2017 Nils Haagen <nils.haagen@concepts-and-training.de> Extended GPL, see docs/LICENSE */

namespace ILIAS\Data\Color;

use ILIAS\Data\Color;

/**
 * Builds a Color from either hex- or rgb values.
 *
 * @author Nils Haagen <nils.haagen@concepts-and-training.de>
 */
class Factory
{
    /**
     * Determine type of input and validate it, then build a color.
     * A Color can be constructed with an array of rgb-integers or from
     * a hex-value both short and longhand notation.
     *
     * @param string|int[] $value
     * @throws \InvalidArgumentException
     */
    public function build($value): Color
    {
        if (is_array($value)) {
            $this->checkRGB($value);
            return $this->fromRGB($value);
        }

        if (is_string($value)) {
            $hex = $this->trimHash($value);
            $this->checkHex($hex);
            return $this->fromHex($hex);
        }

        throw new \InvalidArgumentException("Cannot construct color from " . var_export($value, true), 1);
    }

    /**
     * Validate hex value.
    *
     * @throws \InvalidArgumentException
     */
    private function checkHex(string $hex): void
    {
        $hexpattern = '/^([a-f0-9]{6}|[a-f0-9]{3})$/i';
        if (!preg_match($hexpattern, $hex)) {
            throw new \InvalidArgumentException($hex . " is is not a proper color value.", 1);
        }
    }

    /**
     * Validate rgb-values.
    *
     * @throws \InvalidArgumentException
     */
    private function checkRGB(array $rgb): void
    {
        if (count($rgb) !== 3) {
            throw new \InvalidArgumentException("Array with three values (RGB) needed.", 1);
        }
        foreach ($rgb as $value) {
            if (!is_int($value)) {
                throw new \InvalidArgumentException("RGB-value must be an integer", 1);
            }
            if ($value > 255 || $value < 0) {
                throw new \InvalidArgumentException("RGB-value must be between 0 and 255", 1);
            }
        }
    }

    /**
     * Build a color from hex-value.
     */
    private function fromHex(string $hex): Color
    {
        $hex = $this->unshorten($this->trimHash($hex));
        $chunks = str_split($hex, 2);
        $rgb = array_map('hexdec', $chunks);
        return new Color($rgb[0], $rgb[1], $rgb[2]);
    }

    /**
     * Removes beginning '#' of a hex-value, if it is there.
     */
    private function trimHash(string $hex): string
    {
        if ($hex[0] === '#') {
            $hex = ltrim($hex, '#');
        }
        return $hex;
    }

    /**
     * Expand a shorthand notation of hex-color to longhand notation.
     */
    private function unshorten(string $hex): string
    {
        if (strlen($hex) === 3) {
            $hex = $hex[0] . $hex[0] . $hex[1] . $hex[1] . $hex[2] . $hex[2];
        }
        return $hex;
    }

    /**
     * Build a color from RGB-values.
     *
     * @param int[] $rgb
     */
    private function fromRGB(array $rgb): Color
    {
        return new Color($rgb[0], $rgb[1], $rgb[2]);
    }
}
