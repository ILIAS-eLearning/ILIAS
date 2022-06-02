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
 
namespace ILIAS\UI\Implementation\Crawler\Entry;

use ILIAS\UI\Implementation\Crawler as Crawler;

/**
 * Abstract Entry Part to share some common entry functionality
 *
 * @author Timon Amstutz <timon.amstutz@ilub.unibe.ch>
 */
class AbstractEntryPart
{
    protected ?Crawler\Exception\Factory $f = null;

    public function __construct()
    {
        $this->f = new Crawler\Exception\Factory();
    }

    protected function assert() : Crawler\Exception\CrawlerAssertion
    {
        return $this->f->assertion();
    }
}
