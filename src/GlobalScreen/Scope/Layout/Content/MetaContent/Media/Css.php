<?php namespace ILIAS\GlobalScreen\Scope\Layout\Content\MetaContent\Media;

use LogicException;

/**
 * Class Css
 *
 * @author Fabian Schmid <fs@studer-raimann.ch>
 */
class Css extends AbstractMedia
{

    const MEDIA_ALL = "all";
    const MEDIA_SCREEN = "screen";
    const MEDIA_PRINT = "print";
    const MEDIA_SPEECH = "speech";
    /**
     * @var string
     */
    private $media = self::MEDIA_SCREEN;


    /**
     * Css constructor.
     *
     * @param string $content
     * @param string $media
     */
    public function __construct(string $content, string $media = self::MEDIA_SCREEN)
    {
        if (!in_array($media, [self::MEDIA_ALL, self::MEDIA_PRINT, self::MEDIA_SCREEN, self::MEDIA_SPEECH])) {
            throw new LogicException("Invalid media type for CSS");
        }
        parent::__construct($content);
        $this->media = $media;
    }


    /**
     * @return string
     */
    public function getMedia() : string
    {
        return $this->media;
    }
}
