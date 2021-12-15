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
