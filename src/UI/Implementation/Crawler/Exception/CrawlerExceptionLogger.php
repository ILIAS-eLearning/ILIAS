<?php
namespace ILIAS\UI\Implementation\Crawler\Exception;

/**
 * Sometimes we would like to store exception, instead of throwing them on spot,
 * e.g. for the purpose of testing.
 */
class CrawlerExceptionLogger implements CrawlerExceptionHandler
{
    protected $exceptions = array();

    /**
     *	@inheritdoc
     */
    public function handleException(CrawlerException $ex)
    {
        $this->exceptions[] = $ex;
    }

    /**
     * Get all exception thrown sofar and reset the logger.
     *
     * @return	CrawlerException[]	$return
     */
    public function exceptions()
    {
        $return = $this->exceptions;
        $this->exceptions = array();
        return $return;
    }
}
