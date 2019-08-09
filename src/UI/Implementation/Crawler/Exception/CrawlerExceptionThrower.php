<?php
namespace ILIAS\UI\Implementation\Crawler\Exception;

/**
 * A wrapper around the excepiton to simply throw them.
 */
class CrawlerExceptionThrower implements CrawlerExceptionHandler
{
    protected $exceptions = array();

    /**
     * @inheritdoc
     */
    public function handleException(CrawlerException $ex)
    {
        throw $ex;
    }
}
