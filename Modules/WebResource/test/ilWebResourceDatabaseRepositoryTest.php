<?php declare(strict_types=1);

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
 
use PHPUnit\Framework\TestCase;
use PHPUnit\Framework\MockObject\MockObject;
use ILIAS\DI\Container;

/**
 * Unit tests for ilWebLinkDatabaseRepository
 * @author  Tim Schmitz <schmitz@leifos.com>
 */
class ilWebResourceDatabaseRepositoryTest extends TestCase
{
    protected ?Container $dic = null;
    protected ilObjUser $user;
    protected ilWebLinkRepository $web_link_repo;

    protected function setUp() : void
    {
        parent::setUp();
        $this->initDependencies();
    }

    protected function initDependencies() : void
    {
        global $DIC;
        $this->dic = is_object($DIC) ? clone $DIC : $DIC;
        $GLOBALS['DIC'] = new Container();

        $user = $this->getMockBuilder(ilObjUser::class)
                     ->disableOriginalConstructor()
                     ->getMock();
        $user->expects($this->never())
             ->method($this->anything());

        $this->user = $user;
        $this->setGlobal('ilUser', $user);
    }

    /**
     * @param ilDBInterface&MockObject          $mock_db
     * @param int                               $webr_id
     * @param bool                              $update_history
     * @param int                               $current_time
     * @param DateTimeImmutable&MockObject[]    $datetimes
     * @return void
     */
    protected function setGlobalDBAndRepo(
        ilDBInterface $mock_db,
        int $webr_id,
        bool $update_history,
        int $current_time,
        array $datetimes
    ) : void {
        $this->setGlobal('ilDB', $mock_db);

        $this->web_link_repo = $this->getMockBuilder(ilWebLinkDatabaseRepository::class)
                                    ->setConstructorArgs([$webr_id, $update_history])
                                    ->onlyMethods(['getCurrentTime', 'getNewDateTimeImmutable'])
                                    ->getMock();

        $this->web_link_repo->method('getCurrentTime')
                            ->willReturn($current_time);
        $this->web_link_repo->method('getNewDateTimeImmutable')
                            ->willReturnOnConsecutiveCalls(...$datetimes);
    }

    protected function setGlobal(string $name, MockObject $obj) : void
    {
        global $DIC;

        $GLOBALS[$name] = $obj;
        unset($DIC[$name]);
        $DIC[$name] = static function (Container $c) use ($obj) {
            return $obj;
        };
    }

    protected function tearDown() : void
    {
        global $DIC;
        $DIC = $this->dic;
        parent::tearDown();
    }

    /**
     * @return DateTimeImmutable&MockObject
     */
    protected function getNewDateTimeMock(int $timestamp) : MockObject
    {
        $datetime = $this->getMockBuilder(DateTimeImmutable::class)
                         ->disableOriginalConstructor()
                         ->onlyMethods(['getTimestamp'])
                         ->getMock();
        $datetime->method('getTimestamp')
                 ->willReturn($timestamp);

        return $datetime;
    }

    public function testCreateItem() : void
    {
        $mock_db = $this->getMockBuilder(ilDBInterface::class)
                        ->disableOriginalConstructor()
                        ->getMock();

        $mock_db->expects($this->exactly(3))
                ->method('nextId')
                ->withConsecutive(
                    [ilWebLinkDatabaseRepository::ITEMS_TABLE],
                    [ilWebLinkDatabaseRepository::PARAMS_TABLE],
                    [ilWebLinkDatabaseRepository::PARAMS_TABLE]
                )
                ->willReturn(7, 71, 72);

        $mock_db->expects($this->exactly(3))
                ->method('insert')
                ->withConsecutive(
                    [
                        ilWebLinkDatabaseRepository::PARAMS_TABLE,
                        [
                            'webr_id' => ['integer', 0],
                            'link_id' => ['integer', 7],
                            'param_id' => ['integer', 71],
                            'name' => ['text', 'name1'],
                            'value' => ['integer', ilWebLinkBaseParameter::VALUES['user_id']]
                        ]
                    ],
                    [
                        ilWebLinkDatabaseRepository::PARAMS_TABLE,
                        [
                            'webr_id' => ['integer', 0],
                            'link_id' => ['integer', 7],
                            'param_id' => ['integer', 72],
                            'name' => ['text', 'name2'],
                            'value' => ['integer', ilWebLinkBaseParameter::VALUES['login']]
                        ]
                    ],
                    [
                        ilWebLinkDatabaseRepository::ITEMS_TABLE,
                        [
                            'internal' => ['integer', 0],
                            'webr_id' => ['integer', 0],
                            'link_id' => ['integer', 7],
                            'title' => ['text', 'title'],
                            'description' => ['text', 'description'],
                            'target' => ['text', 'target'],
                            'active' => ['integer', 1],
                            'create_date' => ['integer', 12345678],
                            'last_update' => ['integer', 12345678]
                        ]
                    ]
                );

        $history = Mockery::mock('alias:' . ilHistory::class);
        $history->shouldReceive('_createEntry')
                ->once()
                ->with(0, 'add', ['title']);

        $link_input = Mockery::mock('alias:' . ilLinkInputGUI::class);
        $link_input->shouldReceive('isInternalLink')
                   ->never();

        $param1 = new ilWebLinkDraftParameter(
            ilWebLinkBaseParameter::VALUES['user_id'],
            'name1'
        );
        $param2 = new ilWebLinkDraftParameter(
            ilWebLinkBaseParameter::VALUES['login'],
            'name2'
        );
        $item = new ilWebLinkDraftItem(
            false,
            'title',
            'description',
            'target',
            true,
            [$param1, $param2]
        );

        $datetime1 = $this->getNewDateTimeMock(12345678);
        $datetime2 = $this->getNewDateTimeMock(12345678);

        $this->setGlobalDBAndRepo(
            $mock_db,
            0,
            true,
            12345678,
            [$datetime1, $datetime2]
        );

        $expected_param1 = new ilWebLinkParameter(
            $this->user,
            0,
            7,
            71,
            ilWebLinkBaseParameter::VALUES['user_id'],
            'name1'
        );

        $expected_param2 = new ilWebLinkParameter(
            $this->user,
            0,
            7,
            72,
            ilWebLinkBaseParameter::VALUES['login'],
            'name2'
        );

        $this->assertEquals(
            new ilWebLinkItemExternal(
                0,
                7,
                'title',
                'description',
                'target',
                true,
                $datetime1,
                $datetime2,
                [$expected_param1, $expected_param2]
            ),
            $this->web_link_repo->createItem($item)
        );
    }

    /**
     * TODO implement test cases for the other methods of the repo,
     *  including the other paths of createItem.
     */
}
