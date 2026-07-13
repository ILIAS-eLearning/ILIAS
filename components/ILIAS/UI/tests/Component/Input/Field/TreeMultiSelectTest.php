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
 */

declare(strict_types=1);

use ILIAS\UI\Component\Input\Field\Node\NodeRetrieval;
use ILIAS\UI\Implementation\Render\JavaScriptBinding;
use ILIAS\UI\Implementation\Component\Input\Field;
use ILIAS\UI\Implementation\Component;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\Attributes\DataProvider;

require_once(__DIR__ . "/../../../../../../../vendor/composer/vendor/autoload.php");
require_once(__DIR__ . "/../../../Base.php");
require_once(__DIR__ . "/InputTest.php");

/**
 * @author Thibeau Fuhrer <thibeau@sr.solutions>
 */
class TreeMultiSelectTest extends \ILIAS_UI_TestBase
{
    protected Component\Button\Bulky & MockObject $bulky_stub;
    protected string $bulky_html;
    protected Component\Link\Standard & MockObject $link_stub;
    protected string $link_html;
    protected Component\Menu\Drilldown & MockObject $drilldown_stub;
    protected string $drilldown_html;
    protected Component\Breadcrumbs\Breadcrumbs & MockObject $breadcrumbs_stub;
    protected string $breadcrumbs_html;

    protected Component\SignalGeneratorInterface $signal_generator;
    protected Component\Menu\Factory $menu_factory;

    protected function setUp(): void
    {
        [$this->bulky_stub, $this->bulky_html] = $this->getBulkyStub();
        [$this->link_stub, $this->link_html] = $this->getLinkStub();
        [$this->drilldown_stub, $this->drilldown_html] = $this->getDrilldownStub();
        [$this->breadcrumbs_stub, $this->breadcrumbs_html] = $this->getBreadcrumbsStub();

        $this->signal_generator = new IncrementalSignalGenerator();

        $this->menu_factory = $this->createMock(Component\Menu\Factory::class);
        $this->menu_factory->method('drilldown')->willReturn($this->drilldown_stub);

        parent::setUp();
    }

    public function getDefaultRenderer(
        ?JavaScriptBinding $js_binding = null,
        array $with_stub_renderings = [],
        array $with_additional_contexts = [],
    ): TestDefaultRenderer {
        $with_stub_renderings = array_merge($with_stub_renderings, [
            $this->bulky_stub,
            $this->drilldown_stub,
            $this->breadcrumbs_stub,
        ]);

        return parent::getDefaultRenderer($js_binding, $with_stub_renderings, $with_additional_contexts);
    }

    public function getUIFactory(): \NoUIFactory
    {
        $icon_factory = $this->createMock(Component\Symbol\Icon\Factory::class);
        $glyph_factory = $this->createMock(Component\Symbol\Glyph\Factory::class);
        $glyph_mock = $this->createMock(Component\Symbol\Glyph\Glyph::class);
        $glyph_factory->method($this->anything())->willReturn($glyph_mock);
        $symbol_factory = $this->createMock(Component\Symbol\Factory::class);
        $symbol_factory->method('icon')->willReturn($icon_factory);

        $button_factory = $this->createMock(Component\Button\Factory::class);
        $button_factory->method('bulky')->willReturn($this->bulky_stub);

        $link_factory = $this->createMock(Component\Link\Factory::class);
        $link_factory->method('standard')->willReturn($this->link_stub);

        $node_factory = $this->createMock(Field\Node\Factory::class);
        $field_factory = $this->createMock(Field\Factory::class);
        $field_factory->method('node')->willReturn($node_factory);
        $input_factory = $this->createMock(Component\Input\Factory::class);
        $input_factory->method('field')->willReturn($field_factory);

        return new class (
            $button_factory,
            $symbol_factory,
            $link_factory,
            $input_factory,
            $this->menu_factory,
            $this->breadcrumbs_stub,
        ) extends \NoUIFactory {
            public function __construct(
                protected Component\Button\Factory $button_factory,
                protected Component\Symbol\Factory $symbol_factory,
                protected Component\Link\Factory $link_factory,
                protected Component\Input\Factory $input_factory,
                protected Component\Menu\Factory $menu_factory,
                protected Component\Breadcrumbs\Breadcrumbs $breadcrumbs,
            ) {
            }
            public function button(): Component\Button\Factory
            {
                return $this->button_factory;
            }
            public function breadcrumbs(array $crumbs): Component\Breadcrumbs\Breadcrumbs
            {
                return $this->breadcrumbs;
            }
            public function symbol(): Component\Symbol\Factory
            {
                return $this->symbol_factory;
            }
            public function link(): Component\Link\Factory
            {
                return $this->link_factory;
            }
            public function input(): Component\Input\Factory
            {
                return $this->input_factory;
            }
            public function menu(): Component\Menu\Factory
            {
                return $this->menu_factory;
            }
        };
    }

    #[DataProvider('getInvalidArgumentsForWithValue')]
    public function testWithValueForInvalidArguments(mixed $value): void
    {
        $node_retrieval = $this->getNodeRetrieval();
        $component = $this->getFieldFactory()->treeMultiSelect($node_retrieval, '');
        $this->expectException(InvalidArgumentException::class);
        $component = $component->withValue($value);
    }

    #[DataProvider('getValidArgumentsForWithValue')]
    public function testWithValueForValidArguments(array $value): void
    {
        $node_retrieval = $this->getNodeRetrieval();
        $component = $this->getFieldFactory()->treeMultiSelect($node_retrieval, '');
        $component = $component->withValue($value);
        $this->assertEquals($value, $component->getValue());
    }

    public function testRenderWithValue(): void
    {
        $node_id = 'some-existing-node-id';
        $node_name = 'some existing node';

        [$leaf_stub,] = $this->getLeafStub($node_id, $node_name);

        $node_retrieval = $this->getNodeRetrieval([], [$leaf_stub]);

        $component = $this->getFieldFactory()->treeMultiSelect($node_retrieval, '');
        $component = $component->withValue([$node_id]);

        $renderer = $this->getDefaultRenderer(null, [$leaf_stub]);

        $expected_html = <<<HTML
<li data-node-id="$node_id">
    <span data-node-name>$node_name</span>
    <button data-action="remove" type="button" class="close" aria-label="unselect_node">
        <span aria-hidden="true">&times;</span>
    </button>
    <input id="id_2" type="hidden" value="$node_id" />
</li>
HTML;

        $actual_html = $renderer->render($component);

        $this->assertTrue(
            str_contains(
                $this->brutallyTrimHTML($actual_html),
                $this->brutallyTrimHTML($expected_html),
            )
        );
    }

    public function testRenderWithDisabled(): void
    {
        $node_retrieval = $this->getNodeRetrieval();

        $component = $this->getFieldFactory()->treeMultiSelect($node_retrieval, '');
        $component = $component->withDisabled(true);

        $renderer = $this->getDefaultRenderer();

        $expected_html = <<<HTML
<input id="id_2" type="button" aria-label="select" value="select" disabled>
HTML;

        $actual_html = $renderer->render($component);

        $this->assertTrue(
            str_contains(
                $this->brutallyTrimHTML($actual_html),
                $this->brutallyTrimHTML($expected_html),
            )
        );
    }

    public function testRenderWithRequired(): void
    {
        $node_retrieval = $this->getNodeRetrieval();

        $component = $this->getFieldFactory()->treeMultiSelect($node_retrieval, '');
        $component = $component->withRequired(true);

        $renderer = $this->getDefaultRenderer();

        $expected_html = <<<HTML
<label for="id_2"><span class="sr-only">required_field</span><span class="asterisk" aria-hidden="true">*</span></label>
HTML;

        $actual_html = $renderer->render($component);

        $this->assertTrue(
            str_contains(
                $this->brutallyTrimHTML($actual_html),
                $this->brutallyTrimHTML($expected_html),
            )
        );
    }

    public function testRenderDrilldownMenu(): void
    {
        $this->menu_factory = $this->getMenuFactory();

        [$leaf_stub, $leaf_html] = $this->getLeafStub();

        $tree_select_label = 'some tree select label';

        $node_retrieval = $this->getNodeRetrieval([$leaf_stub]);

        $component = $this->getFieldFactory()->treeMultiSelect($node_retrieval, $tree_select_label);
        $component = $component->withNameFrom(new DefNamesource());

        $renderer = $this->getDefaultRenderer(null, [$leaf_stub]);

        $expected_html = <<<HTML
<fieldset class="c-input" data-il-ui-component="tree-multi-select-field-input" data-il-ui-input-name="name_0">
    <label for="id_3">some tree select label</label>
    <div class="c-input__field">
        <div class="c-input-tree_select">
            <dialog class="c-modal">
                <div class="modal-dialog" role="document">
                    <div class="modal-content">
                        <div class="modal-header">
                        <button data-action="close" type="button" class="close" aria-label="close">
                            <span aria-hidden="true">&times;</span>
                        </button>
                            <h1 class="modal-title">$tree_select_label</h1>
                        </div>
                        <div class="modal-body">
                            <template>$this->breadcrumbs_html</template>
                            $this->breadcrumbs_html
                            <section class="c-drilldown" id="id_2">
                                <header class="c-drilldown__header--showbacknav">
                                    <div></div>
                                    <div></div>
                                    <div class="c-drilldown__backnav"> $this->bulky_html</div>
                                </header>
                                <div class="c-drilldown__menu">
                                    <ul aria-live="polite" aria-label="$tree_select_label">
                                        $leaf_html
                                        <li class="c-drilldown__menu--no-items"> drilldown_no_items</li>
                                    </ul>
                                </div>
                            </section>
                        </div>
                        <div class="modal-footer">
                            <button data-action="close" type="button" class="btn btn-default" aria-label="close">close</button>
                            <button data-action="close" type="button" class="btn btn-primary" aria-label="select">select</button>
                        </div>
                    </div>
                </div>
            </dialog>
            <ul class="c-input-tree_select__selection">
                <template>
                    <li data-node-id="">
                    <span data-node-name></span>
                    <button data-action="remove" type="button" class="close" aria-label="">
                        <span aria-hidden="true">&times;</span>
                    </button>
                    <input id="id_1" type="hidden" name="name_0[input_0][]" value="" /></li>
                </template>
            </ul>
            <input id="id_3" type="button" aria-label="select" value="select">
        </div>
    </div>
</fieldset>
HTML;

        $actual_html = $renderer->render($component);

        $this->assertEquals(
            $this->brutallyTrimHTML($expected_html),
            $this->brutallyTrimHTML($actual_html),
        );
    }

    public static function getInvalidArgumentsForWithValue(): array
    {
        return [
            [true],
            [new stdClass()],
            ['1'],
            [1.000],
            [1],
        ];
    }

    public static function getValidArgumentsForWithValue(): array
    {
        return [
            [[1, 2, 3]],
            [[-1]],
            [['1', '2', '3']],
            [['1']],
            [['']],
            [[' ']],
            [[]]
        ];
    }

    protected function getMenuFactory(): Component\Menu\Factory
    {
        return new Component\Menu\Factory($this->signal_generator);
    }

    protected function getFieldFactory(?Field\Node\Factory $node_factory = null): Field\Factory
    {
        return new Field\Factory(
            ($node_factory) ?: $this->createMock(Field\Node\Factory::class),
            $this->createMock(Component\Input\UploadLimitResolver::class),
            $this->signal_generator,
            $this->getDataFactory(),
            $this->getRefinery(),
            $this->getLanguage(),
        );
    }

    /**
     * @param Field\Node\Node[] $node_stubs to yield from NodeRetrieval::getNodes()
     * @param Field\Node\Leaf[] $leaf_stubs to yield from NodeRetrieval::getNodesAsLeaf()
     */
    protected function getNodeRetrieval(array $node_stubs = [], array $leaf_stubs = []): NodeRetrieval & MockObject
    {
        $node_retrieval = $this->createMock(NodeRetrieval::class);
        $node_retrieval->method('getNodes')->willReturnCallback(static fn() => yield from $node_stubs);
        $node_retrieval->method('getNodesAsLeaf')->willReturnCallback(static fn() => yield from $leaf_stubs);
        return $node_retrieval;
    }

    /** @return array{0: Field\Node\Leaf & MockObject, 1: string} */
    protected function getLeafStub(string|int $id = '', string $name = ''): array
    {
        $stub = $this->createMock(Field\Node\Leaf::class);
        $html = sha1(Field\Node\Leaf::class);
        $stub->method('getCanonicalName')->willReturn($html);
        $stub->method('getId')->willReturn($id);
        $stub->method('getName')->willReturn($name);
        return [$stub, $html];
    }

    /** @return array{0: Component\Breadcrumbs\Breadcrumbs & MockObject, 1: string} */
    protected function getBreadcrumbsStub(): array
    {
        return $this->createSimpleRenderingStub(Component\Breadcrumbs\Breadcrumbs::class);
    }

    /** @return array{0: Component\Menu\Drilldown & MockObject, 1: string} */
    protected function getDrilldownStub(): array
    {
        return $this->createSimpleRenderingStub(Component\Menu\Drilldown::class);
    }

    /** @return array{0: Component\Link\Standard & MockObject, 1: string} */
    protected function getLinkStub(): array
    {
        return $this->createSimpleRenderingStub(Component\Link\Standard::class);
    }

    /** @return array{0: Component\Button\Bulky & MockObject, 1: string} */
    protected function getBulkyStub(): array
    {
        return $this->createSimpleRenderingStub(Component\Button\Bulky::class);
    }

    /** @return array{0: ILIAS\UI\Component\Component & MockObject, 1: string} */
    protected function createSimpleRenderingStub(string $class_name): array
    {
        $stub = $this->createMock($class_name);
        $html = sha1($class_name);
        $stub->method('getCanonicalName')->willReturn($html);
        $stub->method($this->anything())->willReturnSelf();
        return [$stub, $html];
    }
}
