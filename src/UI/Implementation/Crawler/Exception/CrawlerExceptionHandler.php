<?php
namespace ILIAS\UI\Implementation\Crawler\Exception;

/**
 * Handle Crawler exceptions.
 */
interface CrawlerExceptionHandler
{

    /**
     * Handle an exception request.
     *
     * @param	int	$exception_code
     * @param	string	$exception_info
     */
    public function handleException(CrawlerException $ex);
}
