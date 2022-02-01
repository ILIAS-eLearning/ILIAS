<?php namespace ILIAS\GlobalScreen\Scope\Layout\MetaContent\Media;

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
 * Class Js
 *
 * @author Fabian Schmid <fs@studer-raimann.ch>
 */
abstract class AbstractMedia
{

    /**
     * @var string
     */
    protected $content = "";
    /**
     * @var string
     */
    protected $version = '';

    /**
     * AbstractMedia constructor.
     *
     * @param string $content
     */
    public function __construct(string $content, string $version)
    {
        $this->content = $content;
        $this->version = $version;
    }


    /**
     * @return string
     */
    public function getContent() : string
    {
        return $this->content;
    }
}
