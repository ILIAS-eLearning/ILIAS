<?php namespace ILIAS\GlobalScreen\Scope\Layout\MetaContent\Media;

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
     * AbstractMedia constructor.
     *
     * @param string $content
     */
    public function __construct(string $content)
    {
        $this->content = $content;
    }


    /**
     * @return string
     */
    public function getContent() : string
    {
        return $this->content;
    }
}
