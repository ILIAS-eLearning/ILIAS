<?php
namespace ILIAS\LTI\ToolProvider\Content;

/******************************************************************************
 *
 * This file is part of ILIAS, a powerful learning management system.
 *
 * ILIAS is licensed with the GPL-3.0, you should have received a copy
 * of said license along with the source code.
 *
 * If this is not the case or you just want to try ILIAS, you'll find
 * us at:
 *      https://www.ilias.de
 *      https://github.com/ILIAS-eLearning
 *
 *****************************************************************************/
/**
 * Class to represent a content-item image object
 *
 * @author  Stephen P Vickers <stephen@spvsoftwareproducts.com>
 * @copyright  SPV Software Products
 * @license  http://www.gnu.org/licenses/lgpl.html GNU Lesser General Public License, version 3
 */
class Image
{

    /**
     * URL of image.
     *
     * @var string $url
     */
    private ?string $url = null;

    /**
     * Width of image.
     *
     * @var int|null $width
     */
    private ?int $width = null;

    /**
     * Height of image.
     *
     * @var int|null $height
     */
    private ?int $height = null;

    /**
     * Class constructor.
     * @param string   $url    URL of image
     * @param int|null $width  Width of image in pixels (optional)
     * @param int|null $height Height of image in pixels (optional)
     */
    public function __construct(string $url, int $width = null, int $height = null)
    {
        $this->url = $url;
        $this->height = $height;
        $this->width = $width;
    }

    /**
     * Generate the JSON-LD object representation of the image.
     *
     * @return object
     */
    public function toJsonldObject() : object
    {
        $image = new \stdClass();
        $image->{'@id'} = $this->url;
        if (!is_null($this->width)) {
            $image->width = $this->width;
        }
        if (!is_null($this->height)) {
            $image->height = $this->height;
        }

        return $image;
    }

    /**
     * Generate the JSON object representation of the image.
     *
     * @return string
     */
    public function toJsonObject() : string
    {
        $image = new \stdClass();
        $image->url = $this->url;
        if (!is_null($this->width)) {
            $image->width = $this->width;
        }
        if (!is_null($this->height)) {
            $image->height = $this->height;
        }

        return $image;
    }

    /**
     * Generate an Image object from its JSON or JSON-LD representation.
     * @param object $item A JSON or JSON-LD object representing a content-item
     * @return Image|null  The Image object
     */
    public static function fromJsonObject(object $item) : ?Image
    {
        $obj = null;
        $width = null;
        $height = null;
        if (is_object($item)) {
            $url = null;
            foreach (get_object_vars($item) as $name => $value) {
                switch ($name) {
                    case '@id':
                    case 'url':
                        $url = $item->{$name};
                        break;
                    case 'width':
                        $width = $item->width;
                        break;
                    case 'height':
                        $height = $item->height;
                        break;
                }
            }
        } else {
            $url = $item;
        }
        if ($url) {
            $obj = new Image($url, $height, $width);
        }

        return $obj;
    }
}
