<?php namespace ILIAS\GlobalScreen\Scope\Layout\MetaContent\Media;

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
