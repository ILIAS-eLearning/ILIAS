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

use ILIAS\UI\Implementation\Crawler\Entry\ComponentEntry as Entry;
use ILIAS\UI\Implementation\Crawler\Entry\ComponentEntryDescription as Description;
use ILIAS\UI\Implementation\Crawler\Entry\ComponentEntryRules as Rules;

use PHPUnit\Framework\TestCase;

class ComponentEntryTest extends TestCase
{
    protected Entry $entry;
    protected array $entry_data;

    protected function setUp(): void
    {
        $this->entry_data = include "tests/UI/Crawler/Fixture/EntryFixture.php";
        $this->entry = new Entry($this->entry_data);
    }


    public function testConstruct(): void
    {
        $this->assertInstanceOf(Entry::class, $this->entry);
    }

    public function testGetId(): void
    {
        $this->assertEquals($this->entry_data["id"], $this->entry->getId());
        $this->entry->setId("newId");
        $this->assertEquals("newId", $this->entry->getId());
    }

    public function testGetTitle(): void
    {
        $this->assertEquals($this->entry_data["title"], $this->entry->getTitle());
        $this->entry->setTitle("newTitle");
        $this->assertEquals("newTitle", $this->entry->getTitle());
    }

    public function testIsAbstract(): void
    {
        $this->assertEquals($this->entry_data["abstract"], $this->entry->isAbstract());
        $this->entry->setIsAbstract(false);
        $this->assertEquals(false, $this->entry->isAbstract());
    }

    public function testStatusEntry(): void
    {
        $this->assertEquals($this->entry_data["status_entry"], $this->entry->getStatusEntry());
        $this->entry->setStatusEntry("someStatus");
        $this->assertEquals("someStatus", $this->entry->getStatusEntry());
    }

    public function testStatusImplementation(): void
    {
        $this->assertEquals($this->entry_data["status_implementation"], $this->entry->getStatusImplementation());
        $this->entry->setStatusImplementation("someStatus");
        $this->assertEquals("someStatus", $this->entry->getStatusImplementation());
    }

    public function testSetDescription(): void
    {
        $this->assertEquals(new Description($this->entry_data["description"]), $this->entry->getDescription());
        $this->assertEquals($this->entry_data["description"], $this->entry->getDescriptionAsArray());
        $this->entry->setDescription(new Description([]));
        $this->assertEquals(new Description([]), $this->entry->getDescription());
        $this->assertEquals(array(
            'purpose' => '',
            'composition' => '',
            'effect' => '',
            'rivals' => array()), $this->entry->getDescriptionAsArray());
    }

    public function testSetBackground(): void
    {
        $this->assertEquals($this->entry_data["background"], $this->entry->getBackground());
        $this->entry->setBackground("someBackground");
        $this->assertEquals("someBackground", $this->entry->getBackground());
    }

    public function testContext(): void
    {
        $this->assertEquals($this->entry_data["context"], $this->entry->getContext());
        $this->entry->setContext([]);
        $this->assertEquals([], $this->entry->getContext());
    }

    public function testFeatureWikiReferences(): void
    {
        $this->assertEquals($this->entry_data["feature_wiki_references"], $this->entry->getFeatureWikiReferences());
        $this->entry->setFeatureWikiReferences([]);
        $this->assertEquals([], $this->entry->getFeatureWikiReferences());
    }

    public function testRules(): void
    {
        $this->assertEquals(new Rules($this->entry_data["rules"]), $this->entry->getRules());
        $this->assertEquals($this->entry_data["rules"], $this->entry->getRulesAsArray());
        $this->entry->setRules(new Rules([]));
        $this->assertEquals(new Rules([]), $this->entry->getRules());
    }

    public function testSelector(): void
    {
        $this->assertEquals($this->entry_data["selector"], $this->entry->getSelector());
        $this->entry->setSelector("otherSelector");
        $this->assertEquals("otherSelector", $this->entry->getSelector());
    }

    public function testLessVariables(): void
    {
        $this->assertEquals($this->entry_data["less_variables"], $this->entry->getLessVariables());
        $this->entry->setLessVariables([]);
        $this->assertEquals([], $this->entry->getLessVariables());
    }

    public function testPath(): void
    {
        $this->assertEquals($this->entry_data["path"], $this->entry->getPath());
        $this->entry->setPath("");
        $this->assertEquals("", $this->entry->getPath());
    }

    public function testParent(): void
    {
        $this->assertEquals($this->entry_data["parent"], $this->entry->getParent());
        $this->entry->setParent("test");
        $this->assertEquals("test", $this->entry->getParent());
    }

    public function testChildren(): void
    {
        $this->assertEquals($this->entry_data["children"], $this->entry->getChildren());
        $this->entry->setChildren([]);
        $this->assertEquals([], $this->entry->getChildren());
        $this->entry->addChildren(
            array(
                0 => 'Child1',
                1 => 'Child2'
            )
        );
        $this->assertEquals(['Child1','Child2'], $this->entry->getChildren());
        $this->entry->addChild('Child3');
        $this->assertEquals(['Child1','Child2','Child3'], $this->entry->getChildren());
    }

    public function testExamplePath(): void
    {
        $this->assertEquals('src/UI/Entry1Title', $this->entry->getExamplesPath());
    }

    public function testExamplesNull(): void
    {
        $this->assertEquals([], $this->entry->getExamples());
    }

    public function testJsonSerialize(): void
    {
        $this->assertEquals(include "tests/UI/Crawler/Fixture/EntryFixture.php", $this->entry->jsonSerialize());
    }

    public function testNamespace(): void
    {
        $this->assertEquals($this->entry_data["namespace"], $this->entry->getNamespace());
        $this->entry->setNamespace("");
        $this->assertEquals("", $this->entry->getNamespace());
    }
}
