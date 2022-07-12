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
 
require_once("libs/composer/vendor/autoload.php");
include_once("tests/UI/Crawler/Fixture/Fixture.php");

use ILIAS\UI\Implementation\Crawler as Crawler;
use PHPUnit\Framework\TestCase;

/**
 * Tests the actual UI components from src/UI. If no error is thrown, everything should be fine.
 */
class UIComponentsTest extends TestCase
{
    protected Crawler\FactoriesCrawler $crawler;
    protected string $path_to_base_factory = "src/UI/Factory.php";


    protected function setUp() : void
    {
        $this->crawler = new Crawler\FactoriesCrawler();
    }

    /**
     * @throws Crawler\Exception\CrawlerException
     */
    public function testAllUIComponentsFactoriesForNotThrowingErrors() : void
    {
        $this->crawler->crawlFactory($this->path_to_base_factory);
        /**
         * This assertion is only reached if all entries have been successfully parsed (no error was thrown)
         */
        $this->assertTrue(true);
    }
}
