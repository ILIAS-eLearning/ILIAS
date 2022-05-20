<?php namespace ILIAS\GlobalScreen\Scope\Layout\MetaContent\Media;

use LogicException;

/******************************************************************************
 * This file is part of ILIAS, a powerful learning management system.
 * ILIAS is licensed with the GPL-3.0, you should have received a copy
 * of said license along with the source code.
 * If this is not the case or you just want to try ILIAS, you'll find
 * us at:
 *      https://www.ilias.de
 *      https://github.com/ILIAS-eLearning
 *****************************************************************************/

/**
 * Class Css
 *
 * @author Fabian Schmid <fs@studer-raimann.ch>
 */
class Css extends AbstractMediaWithPath
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
    public function __construct(string $content, string $version, string $media = self::MEDIA_SCREEN)
    {
        if (!in_array($media, [self::MEDIA_ALL, self::MEDIA_PRINT, self::MEDIA_SCREEN, self::MEDIA_SPEECH])) {
            throw new LogicException("Invalid media type for CSS");
        }
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
