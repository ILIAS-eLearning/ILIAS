<?php namespace ILIAS\GlobalScreen\Scope\Layout\MetaContent\Media;

/**
 * Class AbstractMediaWithPath
 * @author Fabian Schmid <fs@studer-raimann.ch>
 */
abstract class AbstractMediaWithPath extends AbstractMedia
{
    public function getContent() : string
    {
        $content = parent::getContent();
        return rtrim($content, "?") . "?" . $this->version;
    }

}
