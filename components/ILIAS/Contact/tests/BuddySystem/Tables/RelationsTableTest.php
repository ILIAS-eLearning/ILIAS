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

namespace ILIAS\Contact\Test\BuddySystem\Tables;

use PHPUnit\Framework\TestCase;
use ILIAS\Contact\BuddySystem\Tables\RelationsTable;
use ILIAS\UI\Factory as UIFactory;
use ilLanguage;
use ilUIService;
use ILIAS\HTTP\GlobalHttpState as Http;
use ilBuddyList;
use ilBuddySystemRelation;
use ilBuddySystemRelationCollection;
use ilBuddySystemRelationStateTableFilterMapper;
use ilBuddySystemRelationStateFactory;
use ILIAS\LegalDocuments\test\ContainerMock;
use ILIAS\DI\Container;
use stdClass;
use ilDBInterface;
use ilCtrlInterface;
use Psr\Http\Message\ServerRequestInterface;
use ILIAS\UI\Component\Input\Container\Filter\Standard as Filter;
use ILIAS\UI\Component\Table\Data as Table;
use Closure;
use ilBuddySystemRelationState as State;
use ILIAS\UI\Component\Table\Action\Action;
use ArrayIterator;
use ReflectionProperty;

require_once __DIR__ . '/../../../../LegalDocuments/tests/ContainerMock.php';

class RelationsTableTest extends TestCase
{
    use ContainerMock;

    public function testConstruct(): void
    {
        $this->assertInstanceOf(RelationsTable::class, new RelationsTable(
            ...array_map($this->mock(...), [UIFactory::class, ilLanguage::class, ilUIService::class, Http::class])
        ));
    }

    public function testData(): void
    {
        if (!defined('ANONYMOUS_USER_ID')) {
            define('ANONYMOUS_USER_ID', 7);
        }
        $container = [
            'ilDB' => $this->mockTree(ilDBInterface::class, ['in' => 'dummy', 'queryF' => []]),
            'lng' => $this->mock(ilLanguage::class),
            'ilCtrl' => $this->mock(ilCtrlInterface::class),
        ];
        $original = $GLOBALS['DIC'] ?? null;
        $GLOBALS['DIC'] = $this->mockTree(Container::class, ['user' => ['getId' => 12345]]);
        $GLOBALS['DIC']->method('offsetGet')->willReturnCallback(fn($k) => $container[$k]);
        $std_class = new stdClass();
        $std_class->usr_id = 12345;
        $std_class->public_profile = false;
        $std_class->login = 'dummy';
        $array = [null, $std_class, null, $std_class];
        $container['ilDB']->expects(self::exactly(4))
            ->method('fetchObject')
            ->willReturnCallback(function () use (&$array) {
                return next($array) ?: null;
            });
        $relation = $this->mockTree(ilBuddySystemRelation::class, ['getState' => []]);
        $relations = $this->mockTree(ilBuddySystemRelationCollection::class, ['toArray' => [12345 => $relation]]);
        $relations->expects(self::once())->method('filter')->willReturnCallback(function ($f) use ($relations, $relation) {
            $this->assertTrue($f($relation));
            return $relations;
        });
        $mock = $this->mockTree(ilBuddyList::class, ['getRelations' => $relations]);
        $mapper = $this->mockMethod(ilBuddySystemRelationStateTableFilterMapper::class, 'filterMatchesRelation', ['huhu'], true);
        $mapper->expects(self::once())->method('text')->willReturn('foo');
        $factory = $this->mock(ilBuddySystemRelationStateFactory::class);
        $factory->expects(self::exactly(2))->method('getTableFilterStateMapper')->with($relation->getState())->willReturn($mapper);

        $this->setBuddyList(12345, $mock);
        $this->setStateFactory($factory);

        $this->assertEquals([[
            'user_id' => 12345,
            'public_name' => '',
            'login' => 'dummy',
            'state' => $relation->getState(),
            'points' => [],
            'state-text' => 'foo',
        ]], RelationsTable::data(['state' => 'huhu']));

        $this->setStateFactory(null);
        $this->setBuddyList(12345, null);
        $GLOBALS['DIC'] = $original;
    }

    public function testBuild(): void
    {
        $original = $GLOBALS['DIC'] ?? null;
        $GLOBALS['DIC'] = $this->mockTree(Container::class, ['user' => []]);
        $request = $this->mock(ServerRequestInterface::class);
        $filter = $this->mock(Filter::class);
        $single_action = $this->mock(Action::class);
        $multi_actions = ['a' => $this->mock(Action::class), 'b' => $this->mock(Action::class)];
        $actions = array_merge($multi_actions, ['->' => $single_action]);
        $data_table = $this->mock(Table::class);
        $data_table->expects(self::once())->method('withRequest')->with($request)->willReturn($data_table);
        $data_table->expects(self::once())->method('withActions')->with($actions)->willReturn($data_table);

        $table = new class (
            $this->mockTree(UIFactory::class, ['table' => ['data' => $data_table]]),
            $this->mock(ilLanguage::class),
            $this->mockTree(ilUIService::class, ['filter' => ['standard' => $filter]]),
            $this->mockTree(Http::class, ['request' => $request])
        ) extends RelationsTable {
            public static Closure $data;
            public static function data(array $filter = []): array
            {
                return (self::$data)($filter);
            }
        };
        $table::$data = fn() => [
            [
                'user_id' => 12345,
                'state' => $this->mock(State::class),
                'points' => [$this->mock(State::class), $this->mock(State::class)],
            ],
        ];

        $this->setStateFactory($this->mock(ilBuddySystemRelationStateFactory::class));

        $components = $table->build($multi_actions, '', fn() => $single_action);
        $this->assertSame(2, count($components));
        $this->assertSame($filter, $components[0]);
        $this->assertSame($data_table, $components[1]);

        $this->setStateFactory(null);
        $GLOBALS['DIC'] = $original;
    }

    private function setBuddyList(int $id, ?ilBuddyList $mock): void
    {
        $p = new ReflectionProperty(ilBuddyList::class, 'instances');
        $array = $p->getValue();
        $array[$id] = $mock;
        $p->setValue(null, $array);
    }

    private function setStateFactory(?ilBuddySystemRelationStateFactory $mock): void
    {
        $p = new ReflectionProperty(ilBuddySystemRelationStateFactory::class, 'instance');
        $p->setValue(null, $mock);
    }
}
