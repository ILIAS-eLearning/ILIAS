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

namespace ILIAS\MetaData\Elements;

use PHPUnit\Framework\TestCase;
use ILIAS\MetaData\Elements\NoID;
use ILIAS\MetaData\Elements\Markers\MarkerFactoryInterface;
use ILIAS\MetaData\Elements\Markers\Action;
use ILIAS\MetaData\Elements\Markers\MarkerInterface;
use ILIAS\MetaData\Repository\Utilities\ScaffoldProviderInterface;
use ILIAS\MetaData\Structure\Definitions\DefinitionInterface;
use ILIAS\MetaData\Elements\Data\Type;
use ILIAS\MetaData\Elements\Data\DataInterface;
use ILIAS\MetaData\Elements\Data\NullData;
use ILIAS\MetaData\Structure\Definitions\NullDefinition;

class ElementTest extends TestCase
{
    protected function getDefinition(string $name): DefinitionInterface
    {
        return new class ($name) extends NullDefinition {
            public function __construct(protected string $name)
            {
            }

            public function name(): string
            {
                return $this->name;
            }
        };
    }

    protected function getElement(
        int|NoID $id,
        Element ...$elements
    ): Element {
        return new Element(
            $id,
            $this->getDefinition('name'),
            new NullData(),
            ...$elements
        );
    }

    protected function getElementWithName(
        int|NoID $id,
        string $name,
        Element ...$elements
    ): Element {
        return new Element(
            $id,
            $this->getDefinition($name),
            new NullData(),
            ...$elements
        );
    }

    public function testSubAndSuperElements(): void
    {
        $el11 = $this->getElement(11);
        $el1 = $this->getElement(1, $el11);
        $el2 = $this->getElement(2);
        $root = $this->getElement(NoID::ROOT, $el1, $el2);

        $subs = $root->getSubElements();
        $this->assertSame($el1, $subs->current());
        $subs->next();
        $this->assertSame($el2, $subs->current());
        $subs->next();
        $this->assertNull($subs->current());

        $this->assertSame($root, $el1->getSuperElement());
        $this->assertSame($el11, $el1->getSubElements()->current());
    }

    public function testGetData(): void
    {
        $data = new NullData();
        $el = new Element(
            7,
            $this->getDefinition('name'),
            $data
        );

        $this->assertSame($data, $el->getData());
    }

    public function testIsScaffold(): void
    {
        $scaffold = $this->getElement(NoID::SCAFFOLD);
        $not_scaffold = $this->getElement(5);

        $this->assertTrue($scaffold->isScaffold());
        $this->assertFalse($not_scaffold->isScaffold());
    }

    public function testGetMarkerAndIsMarked(): void
    {
        $mark_me = $this->getElement(13);
        $stay_away = $this->getElement(7);
        $mark_me->mark(new MockMarkerFactory(), Action::NEUTRAL);

        $this->assertTrue($mark_me->isMarked());
        $this->assertInstanceOf(MarkerInterface::class, $mark_me->getMarker());
        $this->assertFalse($stay_away->isMarked());
        $this->assertNull($stay_away->getMarker());
    }

    public function testMarkerTrail(): void
    {
        $el111 = $this->getElement(111);
        $el11 = $this->getElement(11, $el111);
        $el12 = $this->getElement(12);
        $el1 = $this->getElement(1, $el11, $el12);
        $el2 = $this->getElement(2);
        $root = $this->getElement(NoID::ROOT, $el1, $el2);

        $el11->mark(new MockMarkerFactory(), Action::CREATE_OR_UPDATE);

        $this->assertTrue($el11->isMarked());
        $this->assertSame(Action::CREATE_OR_UPDATE, $el11->getMarker()->action());
        $this->assertTrue($el1->isMarked());
        $this->assertSame(Action::NEUTRAL, $el1->getMarker()->action());
        $this->assertTrue($root->isMarked());
        $this->assertSame(Action::NEUTRAL, $root->getMarker()->action());

        $this->assertFalse($el111->isMarked());
        $this->assertFalse($el12->isMarked());
        $this->assertFalse($el2->isMarked());
    }

    public function testMarkTwice(): void
    {
        $marker_factory = new MockMarkerFactory();
        $sub = $this->getElement(11);
        $el = $this->getElement(1, $sub);

        $el->mark($marker_factory, Action::CREATE_OR_UPDATE);
        $this->assertSame(Action::CREATE_OR_UPDATE, $el->getMarker()->action());

        $sub->mark($marker_factory, Action::DELETE);
        $this->assertSame(Action::DELETE, $sub->getMarker()->action());
        $this->assertSame(Action::CREATE_OR_UPDATE, $el->getMarker()->action());

        $el->mark($marker_factory, Action::DELETE);
        $this->assertSame(Action::DELETE, $el->getMarker()->action());
    }

    public function testMarkWithScaffolds(): void
    {
        $marker_factory = new MockMarkerFactory();
        $sub = $this->getElement(NoID::SCAFFOLD);
        $el = $this->getElement(NoID::SCAFFOLD, $sub);

        $sub->mark($marker_factory, Action::CREATE_OR_UPDATE);
        $this->assertSame(Action::CREATE_OR_UPDATE, $sub->getMarker()->action());
        $this->assertSame(Action::CREATE_OR_UPDATE, $el->getMarker()->action());

        $sub = $this->getElement(NoID::SCAFFOLD);
        $el = $this->getElement(NoID::SCAFFOLD, $sub);

        $sub->mark($marker_factory, Action::DELETE);
        $this->assertSame(Action::DELETE, $sub->getMarker()->action());
        $this->assertSame(Action::NEUTRAL, $el->getMarker()->action());
    }

    public function testAddScaffolds(): void
    {
        $second = $this->getElementWithName(6, 'second');
        $el = $this->getElement(13, $second);

        $el->addScaffoldsToSubElements(new MockScaffoldProvider());

        $subs = $el->getSubElements();
        $this->assertTrue($subs->current()->isScaffold());
        $this->assertSame('first', $subs->current()->getDefinition()->name());
        $subs->next();
        $this->assertSame($second, $subs->current());
        $subs->next();
        $this->assertTrue($subs->current()->isScaffold());
        $this->assertSame('second', $subs->current()->getDefinition()->name());
        $subs->next();
        $this->assertTrue($subs->current()->isScaffold());
        $this->assertSame('third', $subs->current()->getDefinition()->name());
        $subs->next();
        $this->assertNull($subs->current());
    }

    public function testAddScaffoldByName(): void
    {
        $second = $this->getElementWithName(6, 'second');
        $third = $this->getElementWithName(17, 'third');
        $el = $this->getElement(13, $second, $third);

        $el->addScaffoldToSubElements(new MockScaffoldProvider(), 'second');

        $subs = $el->getSubElements();
        $this->assertSame($second, $subs->current());
        $subs->next();
        $this->assertTrue($subs->current()->isScaffold());
        $this->assertSame('second', $subs->current()->getDefinition()->name());
        $subs->next();
        $this->assertSame($third, $subs->current());
        $subs->next();
        $this->assertNull($subs->current());
    }

    public function testAddScaffoldsWithSubElementsException(): void
    {
        $el = $this->getElement(37);

        $this->expectException(\ilMDElementsException::class);
        $el->addScaffoldsToSubElements(new MockBrokenScaffoldProvider());
    }

    public function testAddScaffoldByNameWithSubElementsException(): void
    {
        $el = $this->getElement(37);

        $this->expectException(\ilMDElementsException::class);
        $el->addScaffoldToSubElements(new MockBrokenScaffoldProvider(), 'with sub');
    }
}

class MockMarkerFactory implements MarkerFactoryInterface
{
    public function marker(Action $action, string $data_value = ''): MarkerInterface
    {
        return new MockMarker($action);
    }
}

class MockMarker implements MarkerInterface
{
    protected Action $action;

    public function __construct(Action $action)
    {
        $this->action = $action;
    }

    public function action(): Action
    {
        return $this->action;
    }

    public function dataValue(): string
    {
        return '';
    }
}

class MockScaffoldProvider implements ScaffoldProviderInterface
{
    protected function getScaffold(string $name, ElementInterface ...$elements): ElementInterface
    {
        $definition = new class ($name) implements DefinitionInterface {
            protected string $name;

            public function __construct(string $name)
            {
                $this->name = $name;
            }

            public function name(): string
            {
                return $this->name;
            }

            public function unique(): bool
            {
                return false;
            }

            public function dataType(): Type
            {
                return Type::NULL;
            }
        };

        $data = new class () implements DataInterface {
            public function type(): Type
            {
                return Type::STRING;
            }

            public function value(): string
            {
                return 'value';
            }
        };

        return new Element(
            NoID::SCAFFOLD,
            $definition,
            $data,
            ...$elements
        );
    }

    public function getScaffoldsForElement(ElementInterface $element): \Generator
    {
        $first = $this->getScaffold('first');
        $second = $this->getScaffold('second');
        $third = $this->getScaffold('third');

        yield 'second' => $first;
        yield 'third' => $second;
        yield '' => $third;
    }
}

class MockBrokenScaffoldProvider extends MockScaffoldProvider
{
    public function getScaffoldsForElement(ElementInterface $element): \Generator
    {
        $sub = $this->getScaffold('name');
        $with_sub = $this->getScaffold('with sub', $sub);

        yield '' => $with_sub;
    }
}
