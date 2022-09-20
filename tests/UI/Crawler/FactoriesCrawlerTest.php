<?php

declare(strict_types=1);

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

class FactoriesCrawlerTest extends TestCase
{
    protected Crawler\FactoriesCrawler $crawler;

    protected function setUp(): void
    {
        $this->crawler = new Crawler\FactoriesCrawler();
    }

    /**
     * @throws Crawler\Exception\CrawlerException
     */
    public function testAccessInvalidEntry(): void
    {
        try {
            $entries = $this->crawler->crawlFactory("tests/UI/Crawler/Fixture/ComponentsTreeFixture/RootFactory.php");
            $entries->getEntryById("NonExistent")->getChildren();
            $entries->getParentsOfEntry("NonExistent");
            $this->assertFalse("This should not happen");
        } catch (Crawler\Exception\CrawlerException $e) {
            $this->assertEquals(Crawler\Exception\CrawlerException::INVALID_ID, $e->getCode());
        }
    }

    /**
     * @throws Crawler\Exception\CrawlerException
     */
    public function testParseValidFile(): void
    {
        $entries = $this->crawler->crawlFactory("tests/UI/Crawler/Fixture/ComponentsTreeFixture/RootFactory.php");
        $this->assertCount(6, $entries);
        $this->assertCount(
            2,
            $entries->getEntryById("testsUICrawlerFixtureComponentsTreeFixtureComponent1FactoryComponent1")->getChildren()
        );
        $this->assertCount(
            3,
            $entries->getDescendantsOfEntry("testsUICrawlerFixtureComponentsTreeFixtureComponent1FactoryComponent1")
        );
        $this->assertCount(
            1,
            $entries->getEntryById("testsUICrawlerFixtureComponentsTreeFixtureComponent2FactoryComponent2")->getChildren()
        );
        $this->assertCount(
            0,
            $entries->getParentsOfEntry("testsUICrawlerFixtureComponentsTreeFixtureComponent1FactoryComponent1")
        );
        $this->assertCount(
            2,
            $entries->getParentsOfEntry("testsUICrawlerFixtureComponentsTreeFixtureComponent1component12component121Component121")
        );
    }

    /**
     * @throws Crawler\Exception\CrawlerException
     */
    public function testLoopFactory(): void
    {
        try {
            $this->crawler->crawlFactory("tests/UI/Crawler/Fixture/LoopFactory.php");

            $this->assertFalse("This should not happen");
        } catch (Crawler\Exception\CrawlerException $e) {
            $this->assertEquals(Crawler\Exception\CrawlerException::CRAWL_MAX_NESTING_REACHED, $e->getCode());
        }
    }

    /**
     *
     * @throws Crawler\Exception\CrawlerException
     */
    public function testIdenticalNamesFactory(): void
    {
        $this->crawler->crawlFactory("tests/UI/Crawler/Fixture/IdenticalNamesFactory.php");
        $this->assertTrue(true);
    }

    /**
     * @throws Crawler\Exception\CrawlerException
     */
    public function testIdenticalEntriesFactory(): void
    {
        try {
            $this->crawler->crawlFactory("tests/UI/Crawler/Fixture/IdenticalEntriesFactory.php");
            $this->assertFalse("This should not happen");
        } catch (Crawler\Exception\CrawlerException $e) {
            $this->assertEquals(Crawler\Exception\CrawlerException::DUPLICATE_ENTRY, $e->getCode());
        }
    }

    /**
     * @throws Crawler\Exception\CrawlerException
     */
    public function testNoNamespaceFactory(): void
    {
        try {
            $this->crawler->crawlFactory("tests/UI/Crawler/Fixture/NoNamespaceFactory.php");
            $this->assertFalse("This should not happen");
        } catch (Crawler\Exception\CrawlerException $e) {
            $this->assertEquals(Crawler\Exception\CrawlerException::ENTRY_WITH_NO_VALID_RETURN_STATEMENT, $e->getCode());
        }
    }

    /**
     * @throws Crawler\Exception\CrawlerException
     */
    public function testNoClosingDescriptionFactory(): void
    {
        try {
            $this->crawler->crawlFactory("tests/UI/Crawler/Fixture/NoClosingDescriptionFactory.php");

            $this->assertFalse("This should not happen");
        } catch (Crawler\Exception\CrawlerException $e) {
            $this->assertEquals(Crawler\Exception\CrawlerException::ENTRY_WITH_NO_YAML_DESCRIPTION, $e->getCode());
        }
    }
}
