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

require_once(__DIR__ . "/../../../../../../../../vendor/composer/vendor/autoload.php");
require_once(__DIR__ . "/../../../../Base.php");

/**
 * @author Thibeau Fuhrer <thibeau@sr.solutions>
 */
class FieldAsyncNodeTest extends \ILIAS_UI_TestBase
{
    protected Component\Symbol\Glyph\Glyph & MockObject $glyph_stub;
    protected string $glyph_html;
    protected Component\Symbol\Icon\Icon & MockObject $icon_stub;
    protected string $icon_html;
    protected \ILIAS\Data\URI & MockObject $uri_stub;
    protected string $uri_string;

    protected Component\Symbol\Glyph\Factory & MockObject $glyph_factory;
    protected Component\Symbol\Icon\Factory & MockObject $icon_factory;
    protected Component\Symbol\Factory & MockObject $symbol_factory;

    protected function setUp(): void
    {
        $this->uri_string = sha1(\ILIAS\Data\URI::class);
        $this->uri_stub = $this->createMock(\ILIAS\Data\URI::class);
        $this->uri_stub->method('__toString')->willReturn($this->uri_string);

        [$this->glyph_stub, $this->glyph_html] = $this->getGlyphStub();
        [$this->icon_stub, $this->icon_html] = $this->getIconStub();

        $this->glyph_factory = $this->createMock(Component\Symbol\Glyph\Factory::class);
        $this->glyph_factory->method($this->anything())->willReturn($this->glyph_stub);

        $this->icon_factory = $this->createMock(Component\Symbol\Icon\Factory::class);
        $this->icon_factory->method('standard')->willReturn($this->icon_stub);

        $this->symbol_factory = $this->createMock(Component\Symbol\Factory::class);
        $this->symbol_factory->method('glyph')->willReturn($this->glyph_factory);
        $this->symbol_factory->method('icon')->willReturn($this->icon_factory);

        parent::setUp();
    }

    public function getDefaultRenderer(
        ?JavaScriptBinding $js_binding = null,
        array $with_stub_renderings = [],
        array $with_additional_contexts = [],
    ): TestDefaultRenderer {
        $with_stub_renderings = array_merge($with_stub_renderings, [
            $this->glyph_stub,
            $this->icon_stub,
        ]);

        return parent::getDefaultRenderer($js_binding, $with_stub_renderings, $with_additional_contexts);
    }

    public function getUIFactory(): \NoUIFactory
    {
        return new class ($this->symbol_factory) extends \NoUIFactory {
            public function __construct(
                protected Component\Symbol\Factory $symbol_factory,
            ) {
            }
            public function symbol(): Component\Symbol\Factory
            {
                return $this->symbol_factory;
            }
        };
    }

    public function testRenderWithUrl(): void
    {
        $node_id = 'some-existing-node-id';
        $node_name = 'some existing node name';

        $component = $this->getNodeFactory()->async($this->uri_stub, [$node_id], $node_name);
        $renderer = $this->getDefaultRenderer();

        $expected = <<<HTML
<li data-node-id="$node_id" class="c-input-node c-input-node__async c-drilldown__branch" data-render-url="$this->uri_string">
    <button type="button" class="c-drilldown__menulevel--trigger" aria-expanded="false">
        <span> $this->icon_html<span data-node-name>$node_name</span></span>
        <span> $this->glyph_html</span>
    </button>
    <button type="button" class="c-input-node__select" aria-label="select_node">
        <span data-action="select">$this->glyph_html</span>
        <span class="hidden" data-action="remove">$this->glyph_html</span>
    </button>
    <ul></ul>
</li>
HTML;

        $actual = $renderer->render($component);

        $this->assertEquals(
            $this->brutallyTrimHTML($expected),
            $this->brutallyTrimHTML($actual),
        );
    }

    protected function testRenderWithIcon(): void
    {
        [$icon_stub, $icon_html] = $this->createSimpleRenderingStub(Component\Symbol\Icon\Custom::class);

        $component = $this->getNodeFactory()->async($this->uri_stub, [''], $icon_stub);
        $renderer = $this->getDefaultRenderer(null, [$icon_stub]);

        $actual = $renderer->render($component);

        $this->assertTrue(str_contains($actual, $icon_html));
    }

    public function testRenderWithoutIcon(): void
    {
        $first_node_name_letter = 's';
        $node_name = "{$first_node_name_letter}ome existing node name";

        $this->icon_factory->expects($this->once())->method('standard');
        $this->icon_stub->expects($this->once())->method('withAbbreviation')->with($first_node_name_letter);

        $component = $this->getNodeFactory()->async($this->uri_stub, [''], $node_name);
        $renderer = $this->getDefaultRenderer();

        $actual = $renderer->render($component);

        $this->assertTrue(str_contains($actual, $this->icon_html));
    }

    protected function testConstructorWithValidNodePath(): void
    {
        $root_node_id = 0;
        $level_one_node_id = 1;
        $level_two_node_id = 2;
        $parent_ids = [$root_node_id, $level_one_node_id];
        $full_node_path = [...$parent_ids, $level_two_node_id];

        $node = $this->getNodeFactory()->async($this->uri_stub, $full_node_path, "");

        $this->assertEquals($full_node_path, $node->getFullPath());
        $this->assertEquals($parent_ids, $node->getParentIds());
        $this->assertEquals($level_two_node_id, $node->getId());
    }

    #[DataProvider('provideInvalidNodePaths')]
    protected function testConstructorWithInvalidNodePath(array $path): void
    {
        $this->expectException(\InvalidArgumentException::class);
        $this->getNodeFactory()->async($this->uri_stub, $path, "");
    }

    public static function provideInvalidNodePaths(): array
    {
        return [
            [1.0],
            [false],
            [null],
            [],
        ];
    }

    protected function getNodeFactory(): Field\Node\Factory
    {
        return new Field\Node\Factory();
    }

    /** @return array{0: Component\Symbol\Glyph\Glyph, 1: string} */
    protected function getGlyphStub(): array
    {
        return $this->createSimpleRenderingStub(Component\Symbol\Glyph\Glyph::class);
    }

    /** @return array{0: Component\Symbol\Icon\Standard, 1: string} */
    protected function getIconStub(): array
    {
        return $this->createSimpleRenderingStub(Component\Symbol\Icon\Standard::class);
    }

    /** @return array{0: ILIAS\UI\Component\Component, 1: string} */
    protected function createSimpleRenderingStub(string $class_name): array
    {
        $stub = $this->createMock($class_name);
        $html = sha1($class_name);
        $stub->method('getCanonicalName')->willReturn($html);
        $stub->method($this->anything())->willReturnSelf();
        return [$stub, $html];
    }
}
