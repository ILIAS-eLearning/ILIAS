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

class ilForumNotificationTest extends TestCase
{
    /** @var MockObject&ilDBInterface */
    private $database;
    /** @var MockObject&ilObjUser */
    private $user;
    /** @var MockObject&ilTree */
    private $tree;
    private ?Container $dic = null;

    public function testConstruct(): void
    {
        $this->assertInstanceOf(ilForumNotification::class, new ilForumNotification(938));
    }

    public function testGetterAndSetter(): void
    {
        $instance = new ilForumNotification(940);
        $instance->setNotificationId(1);
        $this->assertSame(1, $instance->getNotificationId());
        $instance->setUserId(2);
        $this->assertSame(2, $instance->getUserId());
        $instance->setForumId(3);
        $this->assertSame(3, $instance->getForumId());
        $instance->setThreadId(4);
        $this->assertSame(4, $instance->getThreadId());
        $instance->setInterestedEvents(5);
        $this->assertSame(5, $instance->getInterestedEvents());
        $instance->setAdminForce(true);
        $this->assertTrue($instance->getAdminForce());
        $instance->setUserToggle(true);
        $this->assertTrue($instance->getUserToggle());
        $instance->setForumRefId(6);
        $this->assertSame(6, $instance->getForumRefId());
        $instance->setUserIdNoti(7);
        $this->assertSame(7, $instance->getUserIdNoti());
    }

    public function testIsAdminForceNotification(): void
    {
        $forumId = 745;
        $userId = 271;

        $mockStatement = $this->getMockBuilder(ilDBStatement::class)->disableOriginalConstructor()->getMock();
        $this->database->expects(self::once())->method('queryF')->with(
            '
			SELECT admin_force_noti FROM frm_notification
			WHERE user_id = %s
			AND frm_id = %s
			AND user_id_noti > %s ',
            ['integer', 'integer', 'integer'],
            [$userId, $forumId, 0]
        )->willReturn($mockStatement);
        $this->database->expects(self::once())->method('fetchAssoc')->with($mockStatement)->willReturn(['admin_force_noti' => '1']);

        $instance = new ilForumNotification(375);
        $instance->setForumId($forumId);
        $instance->setUserId($userId);

        $this->assertTrue($instance->isAdminForceNotification());
    }

    public function testIsAdminForceNotificationFailed(): void
    {
        $forumId = 745;
        $userId = 271;

        $mockStatement = $this->getMockBuilder(ilDBStatement::class)->disableOriginalConstructor()->getMock();
        $this->database->expects(self::once())->method('queryF')->with(
            '
			SELECT admin_force_noti FROM frm_notification
			WHERE user_id = %s
			AND frm_id = %s
			AND user_id_noti > %s ',
            ['integer', 'integer', 'integer'],
            [$userId, $forumId, 0]
        )->willReturn($mockStatement);
        $this->database->expects(self::once())->method('fetchAssoc')->with($mockStatement)->willReturn(null);

        $instance = new ilForumNotification(375);
        $instance->setForumId($forumId);
        $instance->setUserId($userId);

        $this->assertFalse($instance->isAdminForceNotification());
    }

    public function testIsUserToggleNotification(): void
    {
        $forumId = 745;
        $userId = 271;

        $mockStatement = $this->getMockBuilder(ilDBStatement::class)->disableOriginalConstructor()->getMock();
        $this->database->expects(self::once())->method('queryF')->with(
            '
			SELECT user_toggle_noti FROM frm_notification
			WHERE user_id = %s
			AND frm_id = %s
			AND user_id_noti > %s',
            ['integer', 'integer', 'integer'],
            [$userId, $forumId, 0]
        )->willReturn($mockStatement);
        $this->database->expects(self::once())->method('fetchAssoc')->with($mockStatement)->willReturn(['user_toggle_noti' => '1']);

        $instance = new ilForumNotification(375);
        $instance->setForumId($forumId);
        $instance->setUserId($userId);

        $this->assertTrue($instance->isUserToggleNotification());
    }

    public function testIsUserToggleNotificationFailed(): void
    {
        $forumId = 745;
        $userId = 271;

        $mockStatement = $this->getMockBuilder(ilDBStatement::class)->disableOriginalConstructor()->getMock();
        $this->database->expects(self::once())->method('queryF')->with(
            '
			SELECT user_toggle_noti FROM frm_notification
			WHERE user_id = %s
			AND frm_id = %s
			AND user_id_noti > %s',
            ['integer', 'integer', 'integer'],
            [$userId, $forumId, 0]
        )->willReturn($mockStatement);
        $this->database->expects(self::once())->method('fetchAssoc')->with($mockStatement)->willReturn(null);

        $instance = new ilForumNotification(375);
        $instance->setForumId($forumId);
        $instance->setUserId($userId);

        $this->assertFalse($instance->isUserToggleNotification());
    }

    public function testInsertAdminForce(): void
    {
        $userToggle = true;
        $adminForce = false;
        $forumId = 970;
        $userId = 530;
        $objUserId = 3627;
        $nextId = 3737;

        $this->user->expects(self::once())->method('getId')->willReturn($objUserId);

        $this->database->expects(self::once())->method('nextId')->willReturn($nextId);
        $this->database->expects(self::once())->method('manipulateF')->with(
            '
			INSERT INTO frm_notification
				(notification_id, user_id, frm_id, admin_force_noti, user_toggle_noti, user_id_noti)
			VALUES(%s, %s, %s, %s, %s, %s)',
            ['integer', 'integer', 'integer', 'integer', 'integer', 'integer'],
            [
                $nextId,
                $userId,
                $forumId,
                $adminForce,
                $userToggle,
                $objUserId
            ]
        );

        $instance = new ilForumNotification(480);
        $instance->setUserId($userId);
        $instance->setForumId($forumId);
        $instance->setAdminForce($adminForce);
        $instance->setUserToggle($userToggle);

        $instance->insertAdminForce();
    }

    public function testDeleteAdminForce(): void
    {
        $userId = 739;
        $forumId = 48849;

        $this->database->expects(self::once())->method('manipulateF')->with(
            '
			DELETE FROM frm_notification
			WHERE 	user_id = %s
			AND		frm_id = %s
			AND		admin_force_noti = %s
			AND		user_id_noti > %s',
            ['integer', 'integer', 'integer', 'integer'],
            [$userId, $forumId, 1, 0]
        );

        $instance = new ilForumNotification(292);
        $instance->setUserId($userId);
        $instance->setForumId($forumId);

        $instance->deleteAdminForce();
    }

    public function testDeleteUserToggle(): void
    {
        $forumId = 3877;
        $userId = 3839;
        $this->database->expects(self::once())->method('manipulateF')->with(
            '
			DELETE FROM frm_notification
			WHERE 	user_id = %s
			AND		frm_id = %s
			AND		admin_force_noti = %s
			AND		user_toggle_noti = %s
			AND		user_id_noti > %s',
            ['integer', 'integer', 'integer', 'integer', 'integer'],
            [$userId, $forumId, 1, 1, 0]
        );

        $instance = new ilForumNotification(3830);
        $instance->setUserId($userId);
        $instance->setForumId($forumId);
        $instance->deleteUserToggle();
    }

    public function testupdateUserToggle(): void
    {
        $userToggle = true;
        $forumId = 3877;
        $userId = 3839;

        $this->database->expects(self::once())->method('manipulateF')->with(
            'UPDATE frm_notification SET user_toggle_noti = %s WHERE user_id = %s AND frm_id = %s AND admin_force_noti = %s',
            ['integer', 'integer', 'integer', 'integer'],
            [$userToggle, $userId, $forumId, 1]
        );

        $instance = new ilForumNotification(3830);
        $instance->setUserId($userId);
        $instance->setForumId($forumId);
        $instance->setUserToggle($userToggle);
        $instance->updateUserToggle();
    }

    public function testCheckForumsExistsInsert(): void
    {
        $nodeData = [];
        $userId = 927;
        $refId = 847;
        $subTree = [['child' => 3719, 'ref_id' => 3738, 'obj_id' => 182]];
        $pathNode = [['child' => $refId, 'type' => 'aa']];

        $this->tree->expects(self::once())->method('getNodePath')->with($subTree[0]['child'], $refId)->willReturn($pathNode);
        $this->tree->expects(self::once())->method('getNodeData')->with($refId)->willReturn($nodeData);
        $this->tree->expects(self::once())->method('getSubTree')->with(
            $nodeData,
            true,
            ['frm']
        )->willReturn($subTree);

        ilForumNotification::checkForumsExistsInsert($refId, $userId);
    }

    public function testUpdate(): void
    {
        $forumId = 1122;
        $userId = 484;
        $events = 848;
        $userToggle = true;
        $adminForce = false;
        $this->database->expects(self::once())->method('manipulateF')->with(
            'UPDATE frm_notification SET admin_force_noti = %s, user_toggle_noti = %s, ' .
            'interested_events = %s WHERE user_id = %s AND frm_id = %s',
            ['integer', 'integer', 'integer', 'integer', 'integer'],
            [
                (int) $adminForce,
                (int) $userToggle,
                $events,
                $userId,
                $forumId
            ]
        );

        $instance = new ilForumNotification(8380);
        $instance->setAdminForce($adminForce);
        $instance->setUserToggle($userToggle);
        $instance->setInterestedEvents($events);
        $instance->setUserId($userId);
        $instance->setForumId($forumId);

        $instance->update();
    }

    public function testDeleteNotificationAllUsers(): void
    {
        $forumId = 490;
        $this->database->expects(self::once())->method('manipulateF')->with(
            'DELETE FROM frm_notification WHERE frm_id = %s AND user_id_noti > %s',
            ['integer', 'integer'],
            [$forumId, 0]
        );

        $instance = new ilForumNotification(3490);
        $instance->setForumId($forumId);

        $instance->deleteNotificationAllUsers();
    }


    public function testRead(): void
    {
        $forumId = 4859;
        $row = [
            'notification_id' => 789,
            'user_id' => 490,
            'frm_id' => 380,
            'thread_id' => 280,
            'admin_force_noti' => 20,
            'user_toggle_noti' => 90,
            'interested_events' => 8,
        ];
        $mockStatement = $this->getMockBuilder(ilDBStatement::class)->disableOriginalConstructor()->getMock();
        $this->database->expects(self::exactly(2))->method('fetchAssoc')->willReturn(
            $row,
            null
        );
        $this->database->expects(self::once())->method('queryF')->with(
            'SELECT * FROM frm_notification WHERE frm_id = %s',
            ['integer'],
            [$forumId]
        )->willReturn($mockStatement);

        $instance = new ilForumNotification(84849);
        $instance->setForumId($forumId);

        $this->assertSame([
            $row['user_id'] => $row,
        ], $instance->read());
    }

    public function testMergeThreadNotifications(): void
    {
        $srcRow = ['user_id' => 47349];
        $mismatchUserIdRow = ['user_id' => 37, 'notification_id' => 48];
        $matchUserIdRow = ['user_id' => $srcRow['user_id'], 'notification_id' => 380];
        $targetId = 840;
        $srcId = 5749;
        $srcStatement = $this->getMockBuilder(ilDBStatement::class)->disableOriginalConstructor()->getMock();
        $targetStatement = $this->getMockBuilder(ilDBStatement::class)->disableOriginalConstructor()->getMock();
        $this->database->expects(self::exactly(2))->method('queryF')->withConsecutive(
            [
                'SELECT notification_id, user_id FROM frm_notification WHERE frm_id = %s AND  thread_id = %s ORDER BY user_id ASC',
                ['integer', 'integer'],
                [0, $srcId],
            ],
            [
                'SELECT DISTINCT user_id FROM frm_notification WHERE frm_id = %s AND  thread_id = %s ORDER BY user_id ASC',
                ['integer', 'integer'],
                [0, $targetId],
            ],
        )->willReturnOnConsecutiveCalls($srcStatement, $targetStatement);

        $this->database->expects(self::exactly(5))
                       ->method('fetchAssoc')
                       ->withConsecutive([$srcStatement], [$srcStatement], [$targetStatement], [$targetStatement], [$targetStatement])
                       ->willReturnOnConsecutiveCalls($srcRow, null, $matchUserIdRow, $mismatchUserIdRow, null);

        $this->database->expects(self::once())->method('manipulateF')->with(
            'DELETE FROM frm_notification WHERE notification_id = %s',
            ['integer'],
            [$matchUserIdRow['notification_id']]
        );

        $this->database->expects(self::once())->method('update')->with(
            'frm_notification',
            ['thread_id' => ['integer', $targetId]],
            ['thread_id' => ['integer', $srcId]]
        );

        ilForumNotification::mergeThreadNotifications($srcId, $targetId);
    }

    public function testExistsNotification(): void
    {
        $adminForce = false;
        $forumId = 7332;
        $userId = 5758;

        $statement = $this->getMockBuilder(ilDBStatement::class)->disableOriginalConstructor()->getMock();
        $this->database->expects(self::once())->method('queryF')->with(
            'SELECT user_id FROM frm_notification WHERE user_id = %s AND frm_id = %s AND admin_force_noti = %s',
            ['integer', 'integer', 'integer'],
            [$userId, $forumId, (int) $adminForce]
        )->willReturn($statement);

        $this->database->expects(self::once())->method('numRows')->with($statement)->willReturn(8);

        $instance = new ilForumNotification(434);
        $instance->setForumId($forumId);
        $instance->setUserId($userId);

        $this->assertTrue($instance->existsNotification());
    }

    protected function setUp(): void
    {
        global $DIC;

        $this->dic = is_object($DIC) ? clone $DIC : $DIC;

        $DIC = new Container();

        $DIC['ilDB'] = ($this->database = $this->createMock(ilDBInterface::class));
        $DIC['ilUser'] = ($this->user = $this->getMockBuilder(ilObjUser::class)->disableOriginalConstructor()->getMock());
        $DIC['ilObjDataCache'] = $this->getMockBuilder(ilObjectDataCache::class)->disableOriginalConstructor()->getMock();
        $DIC['tree'] = ($this->tree = $this->getMockBuilder(ilTree::class)->disableOriginalConstructor()->getMock());
    }

    protected function tearDown(): void
    {
        global $DIC;

        $DIC = $this->dic;

        parent::tearDown();
    }
}
