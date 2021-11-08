<?php declare(strict_types=1);

namespace ILIAS\UI\Implementation\Crawler\Exception;

/**
 * A wrapper around the exception to simply throw them.
 */
class CrawlerExceptionThrower implements CrawlerExceptionHandler
{
    protected array $exceptions = array();

    /**
     * @inheritdoc
     */
    public function handleException(CrawlerException $ex) : void
    {
        throw $ex;
    }
}
