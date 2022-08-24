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
use ILIAS\UI\Implementation\Crawler\Entry as Entry;
use PHPUnit\Framework\TestCase;

class ComponentEntryRulesTest extends TestCase
{
    protected array $empty_rules_array = [
        "usage" => [],
        "composition" => [],
        "interaction" => [],
        "wording" => [],
        "ordering" => [],
        "style" => [],
        "responsiveness" => [],
        "accessibility" => []
    ];

    protected array $invalid_categories1_array = [
        "usage",
        "wrong"
    ];

    protected array $invalid_categories2_array = [
        "usage" => [],
        "wrong" => []
    ];

    protected array $invalid_category_item_array = [
        "usage" => ["Correct"],
        "composition" => "Wrong"
    ];

    protected array $invalid_category_value_array = [
        "usage" => ["Correct"],
        "composition" => [["wrong"]]
    ];

    protected array $correct_rules1_array = [
        "usage" => [1 => "Usage Rule 1", 2 => "Usage Rule 2"],
        "composition" => [1 => "composition Rule 1", 2 => "composition Rule 2"],
        "interaction" => [1 => "interaction Rule 1", 2 => "interaction Rule 2"],
        "wording" => [1 => "wording Rule 1", 2 => "wording Rule 2"],
        "ordering" => [1 => "ordering Rule 1", 2 => "ordering Rule 2"],
        "style" => [1 => "style Rule 1", 2 => "style Rule 2"],
        "responsiveness" => [1 => "responsiveness Rule 1", 2 => "responsiveness Rule 2"],
        "accessibility" => [1 => "accessibility Rule 1", 2 => "accessibility Rule 2"]
    ];

    protected array $correct_rules2_array = [
        "usage" => [1 => "Usage Rule 1", 2 => "Usage Rule 2"],
        "accessibility" => ""
    ];

    protected array $correct_rules2_array_return = [
        "usage" => [1 => "Usage Rule 1", 2 => "Usage Rule 2"],
        "composition" => [],
        "interaction" => [],
        "wording" => [],
        "ordering" => [],
        "style" => [],
        "responsiveness" => [],
        "accessibility" => []
    ];

    protected Crawler\EntriesYamlParser $parser;
    protected ProperEntryFixture $proper_entry;

    protected function setUp(): void
    {
        $this->parser = new Crawler\EntriesYamlParser();
        $this->proper_entry = new ProperEntryFixture();
    }


    /**
     * @throws Crawler\Exception\CrawlerException
     */
    public function testEmptyRules(): void
    {
        $rule = new Entry\ComponentEntryRules();
        $this->assertEquals($this->empty_rules_array, $rule->getRules());

        $rule = new Entry\ComponentEntryRules(array());
        $this->assertEquals($this->empty_rules_array, $rule->getRules());
    }

    /**
     * @throws Crawler\Exception\CrawlerException
     */
    public function testHasRules(): void
    {
        $rules = new Entry\ComponentEntryRules($this->empty_rules_array);
        $this->assertFalse($rules->hasRules());
        $rules = new Entry\ComponentEntryRules($this->correct_rules1_array);
        $this->assertTrue($rules->hasRules());
    }

    /**
     * @throws Crawler\Exception\CrawlerException
     */
    public function testInvalidRules(): void
    {
        $this->expectException(TypeError::class);
        new Entry\ComponentEntryRules(null);

        $this->expectException(TypeError::class);
        new Entry\ComponentEntryRules('rule1');
    }

    /**
     * @throws Crawler\Exception\CrawlerException
     */
    public function testInvalidCategories1(): void
    {
        try {
            new Entry\ComponentEntryRules($this->invalid_categories1_array);
            $this->assertFalse("This should not happen");
        } catch (Crawler\Exception\CrawlerException $e) {
            $this->assertEquals(Crawler\Exception\CrawlerException::INVALID_INDEX, $e->getCode());
        }
    }

    /**
     * @throws Crawler\Exception\CrawlerException
     */
    public function testInvalidCategories2(): void
    {
        try {
            new Entry\ComponentEntryRules($this->invalid_categories2_array);
            $this->assertFalse("This should not happen");
        } catch (Crawler\Exception\CrawlerException $e) {
            $this->assertEquals(Crawler\Exception\CrawlerException::INVALID_INDEX, $e->getCode());
        }
    }

    /**
     * @throws Crawler\Exception\CrawlerException
     */
    public function testInvalidCategoryItem(): void
    {
        try {
            new Entry\ComponentEntryRules($this->invalid_category_item_array);
            $this->assertFalse("This should not happen");
        } catch (Crawler\Exception\CrawlerException $e) {
            $this->assertEquals(Crawler\Exception\CrawlerException::ARRAY_EXPECTED, $e->getCode());
        }
    }

    /**
     * @throws Crawler\Exception\CrawlerException
     */
    public function testInvalidCategoryValue(): void
    {
        try {
            new Entry\ComponentEntryRules($this->invalid_category_value_array);
            $this->assertFalse("This should not happen");
        } catch (Crawler\Exception\CrawlerException $e) {
            $this->assertEquals(Crawler\Exception\CrawlerException::STRING_EXPECTED, $e->getCode());
        }
    }

    /**
     * @throws Crawler\Exception\CrawlerException
     */
    public function testCorrectRules1(): void
    {
        $rules = new Entry\ComponentEntryRules($this->correct_rules1_array);
        $this->assertEquals($this->correct_rules1_array, $rules->getRules());
    }

    /**
     * @throws Crawler\Exception\CrawlerException
     */
    public function testCorrectRules2(): void
    {
        $rules = new Entry\ComponentEntryRules($this->correct_rules2_array);
        $this->assertEquals($this->correct_rules2_array_return, $rules->getRules());
    }

    public function testParseProperEntryToArray(): void
    {
        $entry = $this->parser->parseArrayFromFile("tests/UI/Crawler/Fixture/ProperEntry.php")[0];

        $entry["rules"]['composition'] = [];
        $entry["rules"]['interaction'] = [];
        $entry["rules"]['wording'] = [];
        $entry["rules"]['ordering'] = [];
        $entry["rules"]['responsiveness'] = [];
        $entry["rules"]['accessibility'] = [];

        $rules = new Entry\ComponentEntryRules($entry["rules"]);
        $this->assertEquals($rules->getRules(), $entry["rules"]);
    }
}
