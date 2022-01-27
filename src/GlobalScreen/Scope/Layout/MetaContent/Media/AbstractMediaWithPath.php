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

        // the version string is only appended if the content string is not
        // a data uri, otherwise the data uri will behave incorrectly.
        if (!$this->isContentDataUri($content)) {
            if ($this->hasContentParameters($content)) {
                return rtrim($content, "&") . "&version=" . $this->version;
            }
            else {
                return rtrim($content, "?") . "?version=" . $this->version;
            }
        }

        return $content;
    }

    protected function isContentDataUri(string $content) : bool
    {
        // regex pattern matches if a string follows the data uri syntax.
        // https://developer.mozilla.org/en-US/docs/Web/HTTP/Basics_of_HTTP/Data_URIs#syntax

        return (bool) preg_match('/^(data:)([a-z\/]*)((;base64)?)(,?)([A-z0-9=]*)$/', $content);
    }

    protected function hasContentParameters(string $content): bool
    {
        return (bool) (strpos($content, "?") !== false);
    }
}
