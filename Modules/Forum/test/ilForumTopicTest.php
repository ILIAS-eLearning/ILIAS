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

use ILIAS\DI\Container;
use PHPUnit\Framework\TestCase;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\MockObject\Builder\InvocationMocker;

class ilForumTopicTest extends TestCase
{
    /** @var MockObject&ilDBInterface */
    private $mockDatabase;
    /** @var MockObject&ilObjUser */
    private $mockUser;
    private ?Container $dic = null;

    public function testConstruct(): void
    {
        $id = 78;

        $valueAsObject = new stdClass();

        $valueAsObject->thr_top_fk = 8;
        $valueAsObject->thr_display_user_id = 8;
        $valueAsObject->thr_usr_alias = '';
        $valueAsObject->thr_subject = '';
        $valueAsObject->thr_date = '';
        $valueAsObject->thr_update = '';
        $valueAsObject->import_name = '';
        $valueAsObject->thr_num_posts = 8;
        $valueAsObject->thr_last_post = '';
        $valueAsObject->visits = 8;
        $valueAsObject->is_sticky = false;
        $valueAsObject->is_closed = false;
        $valueAsObject->frm_obj_id = 8;
        $valueAsObject->avg_rating = 9;
        $valueAsObject->thr_author_id = 8;

        $mockStatement = $this->getMockBuilder(ilDBStatement::class)->disableOriginalConstructor()->getMock();
        $mockStatement->expects(self::once())
                      ->method('fetchRow')
                      ->with(ilDBConstants::FETCHMODE_OBJECT)
                      ->willReturn($valueAsObject);

        $this->withIgnoredQuery(
            $this->mockDatabase->expects(self::once())->method('queryF'),
            ['integer'],
            [$id]
        )->willReturn($mockStatement);

        $instance = new ilForumTopic($id);

        $this->assertInstanceOf(ilForumTopic::class, $instance);
    }

    public function testAssignData(): void
    {
        $data = [
            'thr_pk' => '',
            'thr_top_fk' => '',
            'thr_subject' => '',
            'thr_display_user_id' => '',
            'thr_usr_alias' => '',
            'thr_last_post' => '',
            'thr_date' => '',
            'thr_update' => '',
            'visits' => '',
            'import_name' => '',
            'is_sticky' => '',
            'is_closed' => '',
            'avg_rating' => '',
            'thr_author_id' => '',

            'num_posts' => '',
            'num_unread_posts' => '',
            'num_new_posts' => '',
            'usr_notification_is_enabled' => '',
        ];

        $instance = new ilForumTopic();
        $instance->assignData($data);

        $this->assertSame(0, $instance->getId());
        $this->assertSame(0, $instance->getForumId());
        $this->assertSame('', $instance->getSubject());
        $this->assertSame(0, $instance->getDisplayUserId());
        $this->assertSame('', $instance->getUserAlias());
        $this->assertSame('', $instance->getLastPostString());
        $this->assertSame('', $instance->getCreateDate());
        $this->assertSame('', $instance->getChangeDate());
        $this->assertSame(0, $instance->getVisits());
        $this->assertSame('', $instance->getImportName());
        $this->assertFalse($instance->isSticky());
        $this->assertFalse($instance->isClosed());
        $this->assertSame(0.0, $instance->getAverageRating());
        $this->assertSame(0, $instance->getThrAuthorId());

        $this->assertSame(0, $instance->getNumPosts());
        $this->assertSame(0, $instance->getNumUnreadPosts());
        $this->assertSame(0, $instance->getNumNewPosts());
        $this->assertFalse($instance->isUserNotificationEnabled());
    }

    public function testInsert(): void
    {
        $instance = new ilForumTopic();
        $nextId = 8;
        $instance->setId(9);
        $instance->setForumId(10);
        $instance->setSubject('aa');
        $instance->setDisplayUserId(188);
        $instance->setUserAlias('jl');
        $instance->setNumPosts(86);
        $instance->setLastPostString('ahssh');
        $instance->setCreateDate('some date');
        $instance->setImportName('xaii');
        $instance->setSticky(true);
        $instance->setClosed(true);
        $instance->setAverageRating(78);
        $instance->setThrAuthorId(8890);

        $this->mockDatabase->expects(self::once())->method('nextId')->with('frm_threads')->willReturn($nextId);

        $this->mockDatabase->expects(self::once())->method('insert')->with(
            'frm_threads',
            [
                'thr_pk' => ['integer', $nextId],
                'thr_top_fk' => ['integer', 10],
                'thr_subject' => ['text', 'aa'],
                'thr_display_user_id' => ['integer', 188],
                'thr_usr_alias' => ['text', 'jl'],
                'thr_num_posts' => ['integer', 86],
                'thr_last_post' => ['text', 'ahssh'],
                'thr_date' => ['timestamp', 'some date'],
                'thr_update' => ['timestamp', null],
                'import_name' => ['text', 'xaii'],
                'is_sticky' => ['integer', 1],
                'is_closed' => ['integer', 1],
                'avg_rating' => ['text', 78],
                'thr_author_id' => ['integer', 8890],
            ]
        );

        $this->assertTrue($instance->insert());
    }

    public function testInsertFalse(): void
    {
        $instance = new ilForumTopic();
        $this->mockDatabase->expects(self::never())->method('nextId');
        $this->mockDatabase->expects(self::never())->method('insert');
        $instance->setForumId(0);
        $this->assertFalse($instance->insert());
    }

    public function testUpdate(): void
    {
        $instance = new ilForumTopic();
        $instance->setId(8);
        $instance->setForumId(789);
        $instance->setSubject('abc');
        $instance->setNumPosts(67);
        $instance->setLastPostString('hej');
        $instance->setAverageRating(27);

        $this->withIgnoredQuery(
            $this->mockDatabase->expects(self::once())->method('manipulateF'),
            ['integer', 'text', 'timestamp', 'integer', 'text', 'text', 'integer'],
            [
                789,
                'abc',
                date('Y-m-d H:i:s'),
                67,
                'hej',
                '27',
                8,
            ]
        )->willReturn(0);
        $this->assertTrue($instance->update());
    }

    public function testUpdateFalse(): void
    {
        $instance = new ilForumTopic();
        $this->mockDatabase->expects(self::never())->method('manipulateF');
        $instance->setForumId(0);
        $this->assertFalse($instance->update());
    }

    public function testReload(): void
    {
        $mockStatement = $this->getMockBuilder(ilDBStatement::class)->disableOriginalConstructor()->getMock();
        $mockStatement->expects(self::once())->method('fetchRow')->willReturn(null);
        $this->mockDatabase->expects(self::once())->method('queryF')->willReturn($mockStatement);
        $instance = new ilForumTopic();
        $instance->setId(89);
        $instance->reload();
    }

    public function testGetPostRootId(): void
    {
        $id = 909;
        $stdObject = new stdClass();
        $stdObject->pos_fk = 5678;
        $mockStatement = $this->getMockBuilder(ilDBStatement::class)->disableOriginalConstructor()->getMock();
        $this->mockDatabase->expects(self::once())->method('queryF')->with(
            'SELECT pos_fk FROM frm_posts_tree WHERE thr_fk = %s AND parent_pos = %s AND depth = %s ORDER BY rgt DESC',
            ['integer', 'integer', 'integer'],
            [$id, 0, 1]
        )->willReturn($mockStatement);
        $this->mockDatabase->expects(self::once())->method('fetchObject')->with($mockStatement)->willReturn($stdObject);

        $instance = new ilForumTopic();
        $instance->setId($id);
        $this->assertSame($stdObject->pos_fk, $instance->getPostRootId());
    }

    public function testGetFirstVisiblePostId(): void
    {
        $id = 909;
        $stdObject = new stdClass();
        $stdObject->pos_fk = 5678;
        $mockStatement = $this->getMockBuilder(ilDBStatement::class)->disableOriginalConstructor()->getMock();
        $this->mockDatabase->expects(self::once())->method('queryF')->with(
            'SELECT pos_fk FROM frm_posts_tree WHERE thr_fk = %s AND parent_pos != %s AND depth = %s ORDER BY rgt DESC',
            ['integer', 'integer', 'integer'],
            [$id, 0, 2]
        )->willReturn($mockStatement);
        $this->mockDatabase->expects(self::once())->method('fetchObject')->with($mockStatement)->willReturn($stdObject);

        $instance = new ilForumTopic();
        $instance->setId($id);
        $this->assertSame($stdObject->pos_fk, $instance->getFirstVisiblePostId());
    }

    public function testGetPostRootIdFailed(): void
    {
        $id = 909;
        $mockStatement = $this->getMockBuilder(ilDBStatement::class)->disableOriginalConstructor()->getMock();
        $this->mockDatabase->expects(self::once())->method('queryF')->with(
            'SELECT pos_fk FROM frm_posts_tree WHERE thr_fk = %s AND parent_pos = %s AND depth = %s ORDER BY rgt DESC',
            ['integer', 'integer', 'integer'],
            [$id, 0, 1]
        )->willReturn($mockStatement);
        $this->mockDatabase->expects(self::once())->method('fetchObject')->with($mockStatement)->willReturn(null);

        $instance = new ilForumTopic();
        $instance->setId($id);
        $this->assertSame(0, $instance->getPostRootId());
    }

    public function testGetFirstVisiblePostIdFailed(): void
    {
        $id = 909;
        $mockStatement = $this->getMockBuilder(ilDBStatement::class)->disableOriginalConstructor()->getMock();
        $this->mockDatabase->expects(self::once())->method('queryF')->with(
            'SELECT pos_fk FROM frm_posts_tree WHERE thr_fk = %s AND parent_pos != %s AND depth = %s ORDER BY rgt DESC',
            ['integer', 'integer', 'integer'],
            [$id, 0, 2]
        )->willReturn($mockStatement);
        $this->mockDatabase->expects(self::once())->method('fetchObject')->with($mockStatement)->willReturn(null);

        $instance = new ilForumTopic();
        $instance->setId($id);
        $this->assertSame(0, $instance->getFirstVisiblePostId());
    }

    public function testCountPosts(): void
    {
        $id = 789;
        $mockStatement = $this->getMockBuilder(ilDBStatement::class)->disableOriginalConstructor()->getMock();
        $this->withIgnoredQuery(
            $this->mockDatabase->expects(self::once())->method('queryF'),
            ['integer'],
            [$id]
        )->willReturn($mockStatement);
        $this->mockDatabase->expects(self::once())->method('fetchAssoc')->with($mockStatement)->willReturn(['cnt' => 678]);

        $instance = new ilForumTopic();
        $instance->setId($id);
        $this->assertSame(678, $instance->countPosts(true));
    }

    public function testCountPostsFailed(): void
    {
        $id = 789;
        $mockStatement = $this->getMockBuilder(ilDBStatement::class)->disableOriginalConstructor()->getMock();
        $this->withIgnoredQuery(
            $this->mockDatabase->expects(self::once())->method('queryF'),
            ['integer'],
            [$id]
        )->willReturn($mockStatement);
        $this->mockDatabase->expects(self::once())->method('fetchAssoc')->with($mockStatement)->willReturn(null);

        $instance = new ilForumTopic();
        $instance->setId($id);
        $this->assertSame(0, $instance->countPosts(true));
    }

    public function testCountActivePosts(): void
    {
        $id = 789;
        $userId = 354;
        $this->mockUser->expects(self::once())->method('getId')->willReturn($userId);
        $mockStatement = $this->getMockBuilder(ilDBStatement::class)->disableOriginalConstructor()->getMock();
        $this->withIgnoredQuery(
            $this->mockDatabase->expects(self::once())->method('queryF'),
            ['integer', 'integer', 'integer', 'integer'],
            ['1', '0', $userId, $id]
        )->willReturn($mockStatement);
        $this->mockDatabase->expects(self::once())->method('fetchAssoc')->with($mockStatement)->willReturn(['cnt' => 79]);

        $instance = new ilForumTopic();
        $instance->setId($id);
        $this->assertSame(79, $instance->countActivePosts(true));
    }

    public function testCountActivePostsFailed(): void
    {
        $id = 789;
        $userId = 354;
        $this->mockUser->expects(self::once())->method('getId')->willReturn($userId);
        $mockStatement = $this->getMockBuilder(ilDBStatement::class)->disableOriginalConstructor()->getMock();
        $this->withIgnoredQuery(
            $this->mockDatabase->expects(self::once())->method('queryF'),
            ['integer', 'integer', 'integer', 'integer'],
            ['1', '0', $userId, $id]
        )->willReturn($mockStatement);
        $this->mockDatabase->expects(self::once())->method('fetchAssoc')->with($mockStatement)->willReturn(null);

        $instance = new ilForumTopic();
        $instance->setId($id);
        $this->assertSame(0, $instance->countActivePosts(true));
    }

    public function testGetAllPostIds(): void
    {
        $firstRow = new stdClass();
        $firstRow->pos_pk = 89;
        $id = 284;
        $mockStatement = $this->getMockBuilder(ilDBStatement::class)->disableOriginalConstructor()->getMock();
        $mockStatement->expects(self::exactly(2))
                      ->method('fetchRow')
                      ->with(ilDBConstants::FETCHMODE_OBJECT)
                      ->willReturnOnConsecutiveCalls($firstRow, null);
        $this->mockDatabase->expects(self::once())->method('queryF')->with(
            'SELECT pos_pk FROM frm_posts WHERE pos_thr_fk = %s',
            ['integer'],
            [$id]
        )->willReturn($mockStatement);

        $instance = new ilForumTopic();
        $instance->setId($id);
        $this->assertSame([$firstRow->pos_pk => $firstRow->pos_pk], $instance->getAllPostIds());
    }

    public function testIsNotificationEnabled(): void
    {
        $id = 723;
        $userId = 639;

        $instance = new ilForumTopic();
        $instance->setId($id);

        $mockStatement = $this->getMockBuilder(ilDBStatement::class)->disableOriginalConstructor()->getMock();
        $this->mockDatabase->expects(self::once())->method('queryF')->with(
            'SELECT COUNT(notification_id) cnt FROM frm_notification WHERE user_id = %s AND thread_id = %s',
            ['integer', 'integer'],
            [$userId, $id]
        )->willReturn($mockStatement);
        $this->mockDatabase->expects(self::once())->method('fetchAssoc')->with($mockStatement)->willReturn(['cnt' => 46]);

        $this->assertTrue($instance->isNotificationEnabled($userId));
    }

    public function testIsNotificationEnabledNoResult(): void
    {
        $id = 723;
        $userId = 639;

        $instance = new ilForumTopic();
        $instance->setId($id);

        $mockStatement = $this->getMockBuilder(ilDBStatement::class)->disableOriginalConstructor()->getMock();
        $this->mockDatabase->expects(self::once())->method('queryF')->with(
            'SELECT COUNT(notification_id) cnt FROM frm_notification WHERE user_id = %s AND thread_id = %s',
            ['integer', 'integer'],
            [$userId, $id]
        )->willReturn($mockStatement);
        $this->mockDatabase->expects(self::once())->method('fetchAssoc')->with($mockStatement)->willReturn(null);

        $this->assertFalse($instance->isNotificationEnabled($userId));
    }

    public function testIsNotificationEnabledInvalidIds(): void
    {
        $id = 723;
        $userId = 0;

        $instance = new ilForumTopic();
        $instance->setId($id);

        $this->mockDatabase->expects(self::never())->method('queryF');
        $this->mockDatabase->expects(self::never())->method('fetchAssoc');

        $this->assertFalse($instance->isNotificationEnabled($userId));
    }

    public function testEnableNotification(): void
    {
        $nextId = 3847;
        $id = 3739;
        $userId = 8283;

        $mockStatement = $this->getMockBuilder(ilDBStatement::class)->disableOriginalConstructor()->getMock();
        $this->mockDatabase->expects(self::once())->method('nextId')->with('frm_notification')->willReturn($nextId);
        $this->mockDatabase->expects(self::once())->method('queryF')->willReturn($mockStatement);
        $this->withIgnoredQuery(
            $this->mockDatabase->expects(self::once())->method('manipulateF'),
            ['integer', 'integer', 'integer'],
            [$nextId, $userId, $id]
        )->willReturn(0);
        $this->mockDatabase->expects(self::once())->method('fetchAssoc')->with($mockStatement)->willReturn(['cnt' => 0]);

        $instance = new ilForumTopic();
        $instance->setId($id);
        $instance->enableNotification($userId);
    }

    public function testDisableNotification(): void
    {
        $id = 384;
        $userId = 48475;

        $this->mockDatabase->expects(self::once())->method('manipulateF')->with(
            'DELETE FROM frm_notification WHERE user_id = %s AND thread_id = %s',
            ['integer', 'integer'],
            [$userId, $id]
        );

        $instance = new ilForumTopic();
        $instance->setId($id);
        $instance->disableNotification($userId);
    }

    public function testMakeSticky(): void
    {
        $id = 1929;

        $this->mockDatabase->expects(self::once())->method('manipulateF')->with(
            'UPDATE frm_threads SET is_sticky = %s WHERE thr_pk = %s',
            ['integer', 'integer'],
            [1, $id]
        );

        $instance = new ilForumTopic();
        $instance->setId($id);
        $this->assertTrue($instance->makeSticky());
    }

    public function testMakeStickyFailed(): void
    {
        $this->mockDatabase->expects(self::never())->method('manipulateF');

        $instance = new ilForumTopic();
        $this->assertFalse($instance->makeSticky());
    }

    public function testUnmakeSticky(): void
    {
        $id = 1929;

        $this->mockDatabase->expects(self::once())->method('manipulateF')->with(
            'UPDATE frm_threads SET is_sticky = %s WHERE thr_pk = %s',
            ['integer', 'integer'],
            [0, $id]
        );

        $instance = new ilForumTopic();
        $instance->setId($id);
        $instance->setSticky(true);
        $this->assertTrue($instance->unmakeSticky());
    }

    public function testUnmakeStickyFalse(): void
    {
        $id = 1929;

        $this->mockDatabase->expects(self::never())->method('manipulateF');

        $instance = new ilForumTopic();
        $instance->setId($id);
        $this->assertFalse($instance->unmakeSticky());
    }

    public function testClose(): void
    {
        $id = 1929;

        $this->mockDatabase->expects(self::once())->method('manipulateF')->with(
            'UPDATE frm_threads SET is_closed = %s WHERE thr_pk = %s',
            ['integer', 'integer'],
            [1, $id]
        );

        $instance = new ilForumTopic();
        $instance->setId($id);
        $instance->setClosed(false);
        $instance->close();
    }

    public function testReopen(): void
    {
        $id = 1929;

        $this->mockDatabase->expects(self::once())->method('manipulateF')->with(
            'UPDATE frm_threads SET is_closed = %s WHERE thr_pk = %s',
            ['integer', 'integer'],
            [0, $id]
        );

        $instance = new ilForumTopic();
        $instance->setId($id);
        $instance->setClosed(true);
        $instance->reopen();
    }

    protected function setUp(): void
    {
        global $DIC;

        $this->dic = is_object($DIC) ? clone $DIC : $DIC;

        $DIC = new Container();

        $this->mockDatabase = $this->getMockBuilder(ilDBInterface::class)->getMock();
        $this->mockUser = $this->getMockBuilder(ilObjUser::class)->disableOriginalConstructor()->getMock();

        $DIC['ilDB'] = $this->mockDatabase;
        $DIC['ilUser'] = $this->mockUser;
    }

    protected function tearDown(): void
    {
        global $DIC;

        $DIC = $this->dic;

        parent::tearDown();
    }

    private function withIgnoredQuery(InvocationMocker $mock, array ...$expected): InvocationMocker
    {
        return $mock->willReturnCallback(function ($ignored, ...$actual) use ($expected): void {
            $this->assertSame($expected, $actual);
        });
    }
}
