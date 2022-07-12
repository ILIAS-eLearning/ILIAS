<?php declare(strict_types=1);

/**
 * This file is part of ILIAS, a powerful learning management system
 * published by ILIAS open source e-Learning e.V.
 *
 * ILIAS is licensed with the GPL-3.0,
 * see https://www.gnu.org/licenses/gpl-3.0.en.html
 * You should have received a copy of said license along with the
 * source code, too.
 *
 * If this is not the case or you just want to try ILIAS, you'll find
 * us at:
 * https://www.ilias.de
 * https://github.com/ILIAS-eLearning
 *
 *********************************************************************/
 
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
