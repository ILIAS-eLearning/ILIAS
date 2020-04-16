<?php
include_once("Services/Style/System/classes/Exceptions/class.ilSystemStyleColorException.php");

/***
 * ilSystemStyleIconColor is used capsulate the data used to represent one color found in icons in the ILIAS GUI (form).
 *
 * @author            Timon Amstutz <timon.amstutz@ilub.unibe.ch>
 * @version           $Id$
 *
 */
class ilSystemStyleIconColor
{
    const GREY = 0;
    const RED = 1;
    const GREEN = 2;
    const BLUE = 3;

    /**
     * Unique ID to identify the color by (currently same as color)
     *
     * @var string
     */
    protected $id = "";

    /**
     * Value of the color
     *
     * @var string
     */
    protected $color = "";

    /**
     * Name of the color in text
     *
     * @var string
     */
    protected $name = "";

    /**
     * Description of the color
     *
     * @var string
     */
    protected $description = "";

    /**
     * Calculated brightness
     *
     * @var null
     */
    protected $brightness = null;

    /**
     * ilSystemStyleIconColor constructor.
     * @param $id
     * @param $name
     * @param $color
     * @param $description
     */
    public function __construct($id, $name, $color, $description = "")
    {
        $this->setId($id);
        $this->setColor($color);
        $this->setName($name);
        $this->setDescription($description);
    }

    /**
     * @return string
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * @param string $id
     */
    public function setId($id)
    {
        $this->id = $id;
    }

    /**
     * @return string
     */
    public function getColor()
    {
        return $this->color;
    }

    /**
     * @param $color
     * @throws ilSystemStyleColorException
     */
    public function setColor($color)
    {
        $color = strtoupper($color);

        if (!ctype_xdigit($color) || strlen($color) != 6) {
            throw new ilSystemStyleColorException(ilSystemStyleColorException::INVALID_COLOR_EXCEPTION, $color);
        }

        $this->color = $color;
    }

    /**
     * @return string
     */
    public function getName()
    {
        return $this->name;
    }

    /**
     * @param string $name
     */
    public function setName($name)
    {
        $this->name = $name;
    }

    /**
     * @return string
     */
    public function getDescription()
    {
        return $this->description;
    }

    /**
     * @param string $description
     */
    public function setDescription($description)
    {
        $this->description = $description;
    }

    /**
     * Used to order the colors according to their dominant aspect. Due to the vast numbers of colors to be displayed
     * to the user, they must be ordered in some fashion, dominant aspect and brightness are possible values.
     * @return int
     */
    public function getDominatAspect()
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
     *
     * @return number
     */
    public function getRedAspect()
    {
        return hexdec(substr($this->getColor(), 0, 2));
    }

    /**
     * Get green aspect from a color in hex format
     *
     * @return number
     */
    public function getGreenAspect()
    {
        return hexdec(substr($this->getColor(), 2, 2));
    }

    /**
     * Get blue aspect from a color in hex format
     *
     * @return number
     */
    public function getBlueAspect()
    {
        return hexdec(substr($this->getColor(), 4, 2));
    }

    /**
     * Used to sort the colors according to their brightness. Due to the vast numbers of colors to be displayed
     * to the user, they must be ordered in some fashion, dominant aspect and brightness are possible values.
     * See: https://en.wikipedia.org/wiki/YIQ
     *
     * @return float|null
     */
    public function getPerceivedBrightness()
    {
        if ($this->brightness === null) {
            $r = $this->getRedAspect();
            $g = $this->getGreenAspect();
            $b = $this->getBlueAspect();

            $this->brightness = sqrt(
                $r * $r * .299 +
                $g * $g * .587 +
                $b * $b * .114
            );
        }
        return $this->brightness;
    }

    /**
     * Used to sort colors according to their brightness
     *
     * @param ilSystemStyleIconColor $color1
     * @param ilSystemStyleIconColor $color2
     * @return int
     */
    public static function compareColors(ilSystemStyleIconColor $color1, ilSystemStyleIconColor $color2)
    {
        $value1 = $color1->getPerceivedBrightness();
        $value2 = $color2->getPerceivedBrightness();

        if ($value1 == $value2) {
            return 0;
        }
        return ($value1 > $value2) ? +1 : -1;
    }
}
