<?php namespace ILIAS\GlobalScreen\Scope\MainMenu\Factory;

/**
 * Interface hasAsyncContent
 *
 * @author Fabian Schmid <fs@studer-raimann.ch>
 */
interface hasAsyncContent
{

    /**
     * @return string
     */
    public function getAsyncContentURL() : string;


    /**
     * @param string $async_content_url
     *
     * @return hasAsyncContent
     */
    public function withAsyncContentURL(string $async_content_url) : hasAsyncContent;
}
