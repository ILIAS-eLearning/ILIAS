<?php

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

declare(strict_types=1);

require_once('libs/composer/vendor/autoload.php');
include_once('./tests/UI/UITestHelper.php');

use PHPUnit\Framework\TestCase;

use ILIAS\UI\Implementation\Crawler\Entry\ComponentEntry as Entry;
use ILIAS\UI\Implementation\Crawler\Entry\ComponentEntries as Entries;
use ILIAS\Data\URI;

class KSDocumentationTreeRecursionTest extends TestCase
{
    protected UITestHelper $ui_helper;
    protected $entries_data;
    protected Entries $entries;
    protected Entry $entry;
    protected array $entry_data;
    protected URI $test_uri;
    protected KSDocumentationTreeRecursion $tree_recursion;

    protected function setUp() : void
    {
        $this->ui_helper = new UITestHelper();

        $this->entries_data = include './tests/UI/Crawler/Fixture/EntriesFixture.php';
        $this->entries = new Entries();
        $this->entries->addEntriesFromArray($this->entries_data);
        $this->test_uri = new URI('http://ilias.de');
        $this->tree_recursion = new KSDocumentationTreeRecursion($this->entries, $this->test_uri, '');
    }

    public function testConstruct() : void
    {
        $this->assertInstanceOf('KSDocumentationTreeRecursion', $this->tree_recursion);
    }

    public function testGetChildren() : void
    {
        $this->assertEquals(
            [$this->entries->getEntryById('Entry2')],
            $this->tree_recursion->getChildren($this->entries->getEntryById('Entry1'))
        );
        $this->assertEquals([], $this->tree_recursion->getChildren($this->entries->getEntryById('Entry2')));
    }

    public function testBuild() : void
    {
        $tree_factory = $this->ui_helper->factory()->tree()->node();
        $built_node = $this->tree_recursion->build($tree_factory, $this->entries->getEntryById('Entry1'));
        $this->assertInstanceOf('ILIAS\UI\Implementation\Component\Tree\Node\Simple', $built_node);
    }

    public function testIsNodeExpandedByDefault() : void
    {
        $tree_factory = $this->ui_helper->factory()->tree()->node();
        $built_root_node = $this->tree_recursion->build($tree_factory, $this->entries->getEntryById('Entry1'));
        $built_child_node = $this->tree_recursion->build($tree_factory, $this->entries->getEntryById('Entry2'));

        $this->assertEquals(true, $built_root_node->isExpanded());
        $this->assertEquals(false, $built_child_node->isExpanded());
    }

    public function testIsNodeHighlightedByDefault() : void
    {
        $this->tree_recursion = new KSDocumentationTreeRecursion($this->entries, $this->test_uri, 'Entry2');

        $tree_factory = $this->ui_helper->factory()->tree()->node();
        $built_root_node = $this->tree_recursion->build($tree_factory, $this->entries->getEntryById('Entry1'));
        $built_child_node = $this->tree_recursion->build($tree_factory, $this->entries->getEntryById('Entry2'));

        $this->assertEquals(false, $built_root_node->isHighlighted());
        $this->assertEquals(true, $built_child_node->isHighlighted());
    }

    public function testIsNodeExpandedAfterActivatingEntry2() : void
    {
        $this->tree_recursion = new KSDocumentationTreeRecursion($this->entries, $this->test_uri, 'Entry2');

        $tree_factory = $this->ui_helper->factory()->tree()->node();
        $built_root_node = $this->tree_recursion->build($tree_factory, $this->entries->getEntryById('Entry1'));
        $built_child_node = $this->tree_recursion->build($tree_factory, $this->entries->getEntryById('Entry2'));

        $this->assertEquals(true, $built_root_node->isExpanded());
        $this->assertEquals(false, $built_child_node->isExpanded());
    }

    public function testIsNodeHighlightedAfterActivatingEntry() : void
    {
        $this->tree_recursion = new KSDocumentationTreeRecursion($this->entries, $this->test_uri, 'Entry2');

        $tree_factory = $this->ui_helper->factory()->tree()->node();
        $built_root_node = $this->tree_recursion->build($tree_factory, $this->entries->getEntryById('Entry1'));
        $built_child_node = $this->tree_recursion->build($tree_factory, $this->entries->getEntryById('Entry2'));

        $this->assertEquals(false, $built_root_node->isHighlighted());
        $this->assertEquals(true, $built_child_node->isHighlighted());
    }
}
