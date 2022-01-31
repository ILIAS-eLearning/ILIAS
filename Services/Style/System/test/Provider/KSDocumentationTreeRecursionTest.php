<?php declare(strict_types=1);

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

    public function testConstruct()
    {
        $this->assertInstanceOf('KSDocumentationTreeRecursion', $this->tree_recursion);
    }

    public function testGetChildren()
    {
        $this->assertEquals(
            [$this->entries->getEntryById('Entry2')],
            $this->tree_recursion->getChildren($this->entries->getEntryById('Entry1'))
        );
        $this->assertEquals([], $this->tree_recursion->getChildren($this->entries->getEntryById('Entry2')));
    }

    public function testBuild()
    {
        $tree_factory = $this->ui_helper->factory()->tree()->node();
        $built_node = $this->tree_recursion->build($tree_factory, $this->entries->getEntryById('Entry1'));
        $this->assertInstanceOf('ILIAS\UI\Implementation\Component\Tree\Node\Simple', $built_node);
    }

    public function testIsNodeExpandedByDefault()
    {
        $tree_factory = $this->ui_helper->factory()->tree()->node();
        $built_root_node = $this->tree_recursion->build($tree_factory, $this->entries->getEntryById('Entry1'));
        $built_child_node = $this->tree_recursion->build($tree_factory, $this->entries->getEntryById('Entry2'));

        $this->assertEquals(false, $built_root_node->isExpanded());
        $this->assertEquals(false, $built_child_node->isExpanded());
    }

    public function testIsNodeHighlightedByDefault()
    {
        $this->tree_recursion = new KSDocumentationTreeRecursion($this->entries, $this->test_uri, 'Entry2');

        $tree_factory = $this->ui_helper->factory()->tree()->node();
        $built_root_node = $this->tree_recursion->build($tree_factory, $this->entries->getEntryById('Entry1'));
        $built_child_node = $this->tree_recursion->build($tree_factory, $this->entries->getEntryById('Entry2'));

        $this->assertEquals(false, $built_root_node->isHighlighted());
        $this->assertEquals(true, $built_child_node->isHighlighted());
    }

    public function testIsNodeExpandedAfterActivatingEntry2()
    {
        $this->tree_recursion = new KSDocumentationTreeRecursion($this->entries, $this->test_uri, 'Entry2');

        $tree_factory = $this->ui_helper->factory()->tree()->node();
        $built_root_node = $this->tree_recursion->build($tree_factory, $this->entries->getEntryById('Entry1'));
        $built_child_node = $this->tree_recursion->build($tree_factory, $this->entries->getEntryById('Entry2'));

        $this->assertEquals(true, $built_root_node->isExpanded());
        $this->assertEquals(false, $built_child_node->isExpanded());
    }

    public function testIsNodeHighlightedAfterActivatingEntry()
    {
        $this->tree_recursion = new KSDocumentationTreeRecursion($this->entries, $this->test_uri, 'Entry2');

        $tree_factory = $this->ui_helper->factory()->tree()->node();
        $built_root_node = $this->tree_recursion->build($tree_factory, $this->entries->getEntryById('Entry1'));
        $built_child_node = $this->tree_recursion->build($tree_factory, $this->entries->getEntryById('Entry2'));

        $this->assertEquals(false, $built_root_node->isHighlighted());
        $this->assertEquals(true, $built_child_node->isHighlighted());
    }
}
