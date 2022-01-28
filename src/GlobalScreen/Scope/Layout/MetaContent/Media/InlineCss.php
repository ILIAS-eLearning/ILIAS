<?php namespace ILIAS\GlobalScreen\Scope\Layout\MetaContent\Media;

/**
 * Class InlineCss
 *
 * @author Fabian Schmid <fs@studer-raimann.ch>
 */
class InlineCss extends AbstractMedia
{
    const MEDIA_SCREEN = "screen";
    /**
     * @var string
     */
    private $media = self::MEDIA_SCREEN;


    /**
     * InlineCss constructor.
     *
     * @param string $content
     * @param string $media
     */
    public function __construct(string $content, string $version, string $media = self::MEDIA_SCREEN)
    {
        parent::__construct($content, $version);
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
