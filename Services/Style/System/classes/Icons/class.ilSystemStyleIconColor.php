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

/***
 * ilSystemStyleIconColor is used capsulate the data used to represent one color found in icons in the ILIAS GUI (form).
 */
class ilSystemStyleIconColor
{
    public const GREY = 0;
    public const RED = 1;
    public const GREEN = 2;
    public const BLUE = 3;

    /**
     * Unique ID to identify the color by (currently same as color)
     */
    protected string $id = '';

    /**
     * Value of the color
     */
    protected string $color = '';

    /**
     * Name of the color in text
     */
    protected string $name = '';

    /**
     * Description of the color
     */
    protected string $description = '';

    /**
     * ilSystemStyleIconColor constructor.
     */
    public function __construct(string $id, string $name, string $color, string $description = '')
    {
        $this->setId($id);
        $this->setColor($color);
        $this->setName($name);
        $this->setDescription($description);
    }

    public function getId() : string
    {
        return $this->id;
    }

    public function setId(string $id) : void
    {
        $this->id = $id;
    }

    public function getColor() : string
    {
        return $this->color;
    }

    /**
     * @throws ilSystemStyleColorException
     */
    public function setColor(string $color) : void
    {
        $color = strtoupper($color);

        if (!ctype_xdigit($color) || (strlen($color) != 6 && strlen($color) != 3)) {
            throw new ilSystemStyleColorException(ilSystemStyleColorException::INVALID_COLOR_EXCEPTION, $color);
        }

        $this->color = $color;
    }

    public function getName() : string
    {
        return $this->name;
    }

    public function setName(string $name) : void
    {
        $this->name = $name;
    }

    public function getDescription() : string
    {
        return $this->description;
    }

    public function setDescription(string $description) : void
    {
        $this->description = $description;
    }

    /**
     * Used to order the colors according to their dominant aspect. Due to the vast numbers of colors to be displayed
     * to the user, they must be ordered in some fashion, dominant aspect and brightness are possible values.
     */
    public function getDominatAspect() : int
    {
        $r = $this->getRedAspect();
        $g = $this->getGreenAspect();
        $b = $this->getBlueAspect();

        if ($r == $g && $r == $b && $g == $b) {
            return self::GREY;
        } elseif ($r > $g && $r > $b) {
            return self::RED;
        } elseif ($g > $r && $g > $b) {
            return self::GREEN;
        } else {
            return self::BLUE;
        }
    }

    /**
     * Get red aspect from a color in hex format
     */
    public function getRedAspect() : int
    {
        return hexdec(substr($this->getColor(), 0, 2));
    }

    /**
     * Get green aspect from a color in hex format
     */
    public function getGreenAspect() : int
    {
        return hexdec(substr($this->getColor(), 2, 2));
    }

    /**
     * Get blue aspect from a color in hex format
     */
    public function getBlueAspect() : int
    {
        return hexdec(substr($this->getColor(), 4, 2));
    }

    /**
     * Used to sort the colors according to their brightness. Due to the vast numbers of colors to be displayed
     * to the user, they must be ordered in some fashion, dominant aspect and brightness are possible values.
     * See: https://en.wikipedia.org/wiki/YIQ
     */
    public function getPerceivedBrightness() : float
    {
        $r = $this->getRedAspect();
        $g = $this->getGreenAspect();
        $b = $this->getBlueAspect();

        return sqrt(
            $r * $r * .299 +
            $g * $g * .587 +
            $b * $b * .114
        );
    }

    /**
     * Used to sort colors according to their brightness
     */
    public static function compareColors(ilSystemStyleIconColor $color1, ilSystemStyleIconColor $color2) : int
    {
        $value1 = $color1->getPerceivedBrightness();
        $value2 = $color2->getPerceivedBrightness();

        if ($value1 == $value2) {
            return 0;
        }
        return ($value1 > $value2) ? +1 : -1;
    }
}
