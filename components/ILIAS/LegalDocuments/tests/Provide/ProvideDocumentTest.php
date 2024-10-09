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

namespace ILIAS\LegalDocuments\test\Provide;

use ILIAS\UI\Component\Component;
use ILIAS\LegalDocuments\Value\DocumentContent;
use ILIAS\UI\Component\Input\Field\Group;
use ILIAS\LegalDocuments\Value\CriterionContent;
use ILIAS\LegalDocuments\Condition;
use ILIAS\LegalDocuments\ConditionDefinition;
use ILIAS\LegalDocuments\Value\Criterion;
use ILIAS\LegalDocuments\Value\Document;
use ILIAS\LegalDocuments\EditLinks;
use ILIAS\LegalDocuments\Table\EditableDocumentTable;
use ILIAS\UI\Component\Legacy\Legacy;
use ILIAS\LegalDocuments\Legacy\Table as LegacyTable;
use ILIAS\LegalDocuments\Table as TableInterface;
use ILIAS\LegalDocuments\Table\DocumentTable;
use ILIAS\UI\Factory as UIFactory;
use ILIAS\LegalDocuments\test\ContainerMock;
use ILIAS\DI\Container;
use ILIAS\LegalDocuments\Repository\DocumentRepository;
use PHPUnit\Framework\TestCase;
use ILIAS\LegalDocuments\Provide\ProvideDocument;
use ILIAS\LegalDocuments\SelectionMap;
use ilGlobalTemplateInterface;
use ilLanguage;
use stdClass;
use ilObjUser;

require_once __DIR__ . '/../ContainerMock.php';

class ProvideDocumentTest extends TestCase
{
    use ContainerMock;

    public function testConstruct(): void
    {
        $this->assertInstanceOf(ProvideDocument::class, new ProvideDocument(
            'foo',
            $this->mock(DocumentRepository::class),
            new SelectionMap(),
            [],
            $this->mock(Container::class)
        ));
    }

    public function testTableReadOnly(): void
    {
        $dummy_gui = new stdClass();
        $legacy = $this->mock(Legacy::class);

        $container = $this->mockTree(Container::class, [
            'ui' => [
                'factory' => $this->mockMethod(UIFactory::class, 'legacy', ['table html'], $legacy),
                'mainTemplate' => $this->mock(ilGlobalTemplateInterface::class),
            ],
            'language' => $this->mock(ilLanguage::class),
        ]);

        $table = $this->mockMethod(LegacyTable::class, 'getHTML', [], 'table html');
        $create_table_gui = function ($gui, string $command, TableInterface $t) use ($dummy_gui, $table): LegacyTable {
            $this->assertSame($dummy_gui, $gui);
            $this->assertSame('dummy command', $command);
            $this->assertInstanceOf(DocumentTable::class, $t);
            return $table;
        };

        $instance = new ProvideDocument('foo', $this->mock(DocumentRepository::class), new SelectionMap(), [], $container, $create_table_gui);
        $this->assertSame($legacy, $instance->table($dummy_gui, 'dummy command'));
    }

    public function testTableEditable(): void
    {
        $dummy_gui = new stdClass();
        $legacy = $this->mock(Legacy::class);

        $container = $this->mockTree(Container::class, [
            'ui' => [
                'factory' => $this->mockMethod(UIFactory::class, 'legacy', ['table html'], $legacy),
                'mainTemplate' => $this->mock(ilGlobalTemplateInterface::class),
            ],
            'language' => $this->mock(ilLanguage::class),
        ]);

        $table = $this->mockMethod(LegacyTable::class, 'getHTML', [], 'table html');
        $create_table_gui = function ($gui, string $command, TableInterface $t) use ($dummy_gui, $table): LegacyTable {
            $this->assertSame($dummy_gui, $gui);
            $this->assertSame('dummy command', $command);
            $this->assertInstanceOf(EditableDocumentTable::class, $t);
            return $table;
        };

        $instance = new ProvideDocument('foo', $this->mock(DocumentRepository::class), new SelectionMap(), [], $container, $create_table_gui);
        $this->assertSame($legacy, $instance->table($dummy_gui, 'dummy command', $this->mock(EditLinks::class)));
    }

    public function testChooseDocumentFor(): void
    {
        $document = $this->mockMethod(Document::class, 'criteria', [], []);
        $user = $this->mock(ilObjUser::class);

        $criterion = $this->mockTree(Criterion::class, ['content' => ['type' => 'bar']]);

        $repository = $this->mockMethod(DocumentRepository::class, 'all', [], [
            $this->mockMethod(Document::class, 'criteria', [], [$criterion]),
            $document,
        ]);

        $instance = new ProvideDocument('foo', $repository, new SelectionMap([
            'bar' => $this->mockMethod(ConditionDefinition::class, 'withCriterion', [$criterion->content()], $this->mockMethod(
                Condition::class,
                'eval',
                [$user],
                false
            )),
        ]), [], $this->mock(Container::class));
        $result = $instance->chooseDocumentFor($user);
        $this->assertTrue($result->isOk());
        $this->assertSame($document, $result->value());
    }

    public function testDocumentMatches(): void
    {
        $criterion = $this->mockTree(Criterion::class, ['content' => ['type' => 'bar']]);
        $document = $this->mockMethod(Document::class, 'criteria', [], [$criterion]);
        $user = $this->mock(ilObjUser::class);

        $instance = new ProvideDocument('doo', $this->mock(DocumentRepository::class), new SelectionMap([
            'bar' => $this->mockMethod(ConditionDefinition::class, 'withCriterion', [$criterion->content()], $this->mockMethod(
                Condition::class,
                'eval',
                [$user],
                true
            )),
        ]), [], $this->mock(Container::class));

        $this->assertTrue($instance->documentMatches($document, $user));
    }

    public function testRepository(): void
    {
        $repository = $this->mock(DocumentRepository::class);
        $instance = new ProvideDocument('foo', $repository, new SelectionMap(), [], $this->mock(Container::class));
        $this->assertSame($repository, $instance->repository());
    }

    public function testHash(): void
    {
        $instance = new ProvideDocument('foo', $this->mock(DocumentRepository::class), new SelectionMap(), [], $this->mock(Container::class));
        $hash = $instance->hash();
        $this->assertTrue(is_string($hash));
        $this->assertSame(254, strlen($hash));
    }

    public function testToCondition(): void
    {
        $condition = $this->mock(Condition::class);
        $content = $this->mockMethod(CriterionContent::class, 'type', [], 'bar');

        $instance = new ProvideDocument('foo', $this->mock(DocumentRepository::class), new SelectionMap([
            'bar' => $this->mockMethod(ConditionDefinition::class, 'withCriterion', [$content], $condition),
        ]), [], $this->mock(Container::class));

        $this->assertSame($condition, $instance->toCondition($content));
    }

    public function testConditionGroupsWithoutContent(): void
    {
        $group = $this->mock(Group::class);

        $instance = new ProvideDocument('foo', $this->mock(DocumentRepository::class), new SelectionMap([
            'bar' => $this->mockMethod(ConditionDefinition::class, 'formGroup', [[]], $group),
            'baz' => $this->mockMethod(ConditionDefinition::class, 'formGroup', [[]], $group),
        ]), [], $this->mock(Container::class));

        $this->assertSame(['bar' => $group, 'baz' => $group], $instance->conditionGroups()->choices());
    }

    public function testConditionGroups(): void
    {
        $group = $this->mock(Group::class);
        $content = $this->mockTree(CriterionContent::class, [
            'type' => 'baz',
            'arguments' => ['a', 'b', 'c'],
        ]);

        $instance = new ProvideDocument('foo', $this->mock(DocumentRepository::class), new SelectionMap([
            'bar' => $this->mockMethod(ConditionDefinition::class, 'formGroup', [[]], $group),
            'baz' => $this->mockMethod(ConditionDefinition::class, 'formGroup', [$content->arguments()], $group),
        ]), [], $this->mock(Container::class));

        $this->assertSame(['bar' => $group, 'baz' => $group], $instance->conditionGroups($content)->choices());
    }

    public function testValidateCriteriaContent(): void
    {
        $content = new CriterionContent('foo', ['bar']);
        $existing_content = new CriterionContent('hoo', ['haz']);
        $other_existing_content = new CriterionContent('foo', ['baz']);

        $condition = $this->mock(Condition::class);
        $condition->expects(self::exactly(2))->method('knownToNeverMatchWith')->with($condition)->willReturn(false);

        $definition = $this->mock(ConditionDefinition::class);
        $consecutive = [$content, $other_existing_content, $content];
        $definition->expects(self::exactly(3))->method('withCriterion')->with(
            $this->callback(function ($value) use (&$consecutive) {
                $this->assertSame(array_shift($consecutive), $value);
                return true;
            })
        )->willReturn($condition);

        $instance = new ProvideDocument('foo', $this->mock(DocumentRepository::class), new SelectionMap([
            'hoo' => $this->mockMethod(ConditionDefinition::class, 'withCriterion', [$existing_content], $condition),
            'foo' => $definition,
        ]), [], $this->mock(Container::class));

        $result = $instance->validateCriteriaContent([
            $this->mockTree(Criterion::class, ['content' => $existing_content]),
            $this->mockTree(Criterion::class, ['content' => $other_existing_content]),
        ], $content);
        $this->assertTrue($result->isOk());
        $this->assertSame($content, $result->value());
    }

    public function testDuplicateCriteriaContent(): void
    {
        $content = new CriterionContent('foo', ['bar']);

        $instance = new ProvideDocument('foo', $this->mock(DocumentRepository::class), new SelectionMap(), [], $this->mock(Container::class));
        $result = $instance->validateCriteriaContent([
            $this->mockTree(Criterion::class, ['content' => new CriterionContent('foo', ['bar'])])
        ], $content);
        $this->assertFalse($result->isOk());
        $this->assertSame(ProvideDocument::CRITERION_ALREADY_EXISTS, $result->error());
    }

    public function testNeverMatchingCriteriaContent(): void
    {
        $content = new CriterionContent('foo', ['bar']);
        $existing_content = new CriterionContent('hoo', ['haz']);

        $condition = $this->mock(Condition::class);
        $condition->expects(self::once())->method('knownToNeverMatchWith')->with($condition)->willReturn(true);

        $instance = new ProvideDocument('foo', $this->mock(DocumentRepository::class), new SelectionMap([
            'hoo' => $this->mockMethod(ConditionDefinition::class, 'withCriterion', [$existing_content], $condition),
            'foo' => $this->mockMethod(ConditionDefinition::class, 'withCriterion', [$content], $condition),
        ]), [], $this->mock(Container::class));

        $result = $instance->validateCriteriaContent([
            $this->mockTree(Criterion::class, ['content' => $existing_content]),
        ], $content);
        $this->assertFalse($result->isOk());
        $this->assertSame(ProvideDocument::CRITERION_WOULD_NEVER_MATCH, $result->error());
    }

    public function testContentAsComponent(): void
    {
        $content = $this->mockTree(DocumentContent::class, ['type' => 'html']);
        $component = $this->mock(Component::class);

        $instance = new ProvideDocument('foo', $this->mock(DocumentRepository::class), new SelectionMap(), [
            'html' => function (DocumentContent $c) use ($content, $component): Component {
                $this->assertSame($content, $c);
                return $component;
            }
        ], $this->mock(Container::class));

        $this->assertSame($component, $instance->contentAsComponent($content));
    }
}
