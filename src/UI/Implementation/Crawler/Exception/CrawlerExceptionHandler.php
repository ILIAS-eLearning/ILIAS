<?php declare(strict_types=1);

namespace ILIAS\UI\Implementation\Crawler\Exception;

/**
 * Handle Crawler exceptions.
 */
interface CrawlerExceptionHandler
{
    /**
     * Handle an exception request.
     */
    public function handleException(CrawlerException $ex) : void;
}
