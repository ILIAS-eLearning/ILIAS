<?php declare(strict_types=1);

namespace ILIAS\UI\Implementation\Crawler\Exception;

/**
 * Sometimes we would like to store exception, instead of throwing them on spot,
 * e.g. for the purpose of testing.
 */
class CrawlerExceptionLogger implements CrawlerExceptionHandler
{
    protected array $exceptions = array();

    /**
     *	@inheritdoc
     */
    public function handleException(CrawlerException $ex) : void
    {
        $this->exceptions[] = $ex;
    }

    /**
     * Get all exception thrown so far and reset the logger.
     *
     * @return	CrawlerException[]	$return
     */
    public function exceptions() : array
    {
        $return = $this->exceptions;
        $this->exceptions = array();
        return $return;
    }
}
