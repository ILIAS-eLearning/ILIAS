<?php
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
     * @param  string|int[] $value
     * @throws \InvalidArgumentException
     * @return Color
     */
    public function build($value)
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

        throw new \InvalidArgumentException("Cannot construct color from " . $value, 1);
    }

    /**
    * Validate hex value.
    *
    * @param string $hex
    * @throws \InvalidArgumentException
    */
    private function checkHex($hex)
    {
        $hexpattern = '/^([a-f0-9]{6}|[a-f0-9]{3})$/i';
        if (!preg_match($hexpattern, $hex)) {
            throw new \InvalidArgumentException($hex . " is is not a proper color value.", 1);
        }
    }

    /**
    * Validate rgb-values.
    *
    * @param array $rgb
    * @throws \InvalidArgumentException
    */
    private function checkRGB($rgb)
    {
        if (count($rgb) !== 3) {
            throw new \InvalidArgumentException("Array with three values (RGB) needed.", 1);
        }
        foreach ($rgb as $value) {
            if (!is_integer($value)) {
                throw new \InvalidArgumentException("RGB-value must be an integer", 1);
            }
            if ($value > 255 || $value < 0) {
                throw new \InvalidArgumentException("RGB-value must be between 0 and 255", 1);
            }
        }
    }

    /**
    * Build a color from hex-value.
    *
    * @param string $hex
    * @return \Color
    */
    private function fromHex($hex)
    {
        $hex = $this->unshorten($this->trimHash($hex));
        $chunks = str_split($hex, 2);
        $rgb = array_map('hexdec', $chunks);
        return new Color($rgb[0], $rgb[1], $rgb[2]);
    }

    /**
     * Removes beginning '#' of a hex-value, if it is there.
     *
     * @param string $hex
     * @return string
     */
    private function trimHash($hex)
    {
        if (substr($hex, 0, 1) === '#') {
            $hex = ltrim($hex, '#');
        }
        return $hex;
    }

    /**
     * Expand a shorthand notation of hex-color to longhand notation.
     *
     * @param string $hex
     * @return string
     */
    private function unshorten($hex)
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
     * @return \Color
     */
    private function fromRGB($rgb)
    {
        return new Color($rgb[0], $rgb[1], $rgb[2]);
    }
}
