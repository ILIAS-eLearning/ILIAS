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

use PHPUnit\Framework\TestCase;

/**
 * @author  Ingmar Szmais <iszmais@databay.de>
 */
class ilNotificationOSDTest extends ilNotificationsBaseTest
{
    private \ILIAS\Notifications\ilNotificationOSDHandler $handler;
    private ilObjUser $user;
    private ilDBInterface $db;
    private array $database;
    private array $result;

    private function createDBFunctionCalls(int $insert = 0, int $queryF = 0, int $fetchAssoc = 0, int $manipulateF = 0): void
    {
        $this->database = [];
        $this->result = [];
        $this->db->expects(self::exactly($insert))->method('nextId')->willReturnCallback(function (string $table): int {
            return count($this->database) + 1;
        });
        $this->db->expects(self::exactly($insert))->method('insert')->willReturnCallback(function (string $table, array $object): int {
            foreach ($object as &$value) {
                $value = $value[1];
            }
            unset($value);
            $this->database[] = $object;
            return $object['notification_osd_id'];
        });
        $this->db->expects(self::exactly($queryF))->method('queryF')->willReturnCallback(function (string $query, array $types, array $values): ilPDOStatement {
            $this->result = [];
            if (strpos($query, 'WHERE usr_id') !== false) {
                foreach ($this->database as $row) {
                    if ($row['usr_id'] === $values[0]) {
                        $this->result[] = $row;
                    }
                }
            }
            if (strpos($query, 'WHERE notification_osd_id') !== false) {
                foreach ($this->database as $row) {
                    if ($row['notification_osd_id'] === $values[0]) {
                        $this->result[] = $row;
                    }
                }
            }
            if (strpos($query, 'SELECT count(*) AS count') !== false) {
                $this->result = [0 => ['count' => count($this->result)]];
            }
            return $this->createMock(ilPDOStatement::class);
        });
        $this->db->expects(self::exactly($fetchAssoc))->method('fetchAssoc')->willReturnCallback(function (ilPDOStatement $rset): ?array {
            return array_shift($this->result);
        });
        $this->db->expects(self::exactly($manipulateF))->method('manipulateF')->willReturnCallback(function (string $query, array $types, array $values): int {
            if (count($values) === 1) {
                foreach ($this->database as $key => $row) {
                    if ($row['notification_osd_id'] === $values[0]) {
                        unset($this->database[$key]);
                        return 1;
                    }
                }
            }
            if (count($values) === 2) {
                $i = 0;
                foreach ($this->database as $key => $row) {
                    if ($row['usr_id'] === $values[0] && $row['type'] === $values[1]) {
                        unset($this->database[$key]);
                        $i++;
                    }
                }
                return $i;
            }
            return 0;
        });
    }

    protected function setUp(): void
    {
        $this->db = $this->createMock(ilDBPdo::class);
        $this->handler = new \ILIAS\Notifications\ilNotificationOSDHandler(
            new ILIAS\Notifications\Repository\ilNotificationOSDRepository($this->db)
        );
        $this->user = $this->createMock(ilObjUser::class);
        $this->user->method('getId')->willReturn(4);
    }

    public function testCreateNotification(): void
    {
        $this->createDBFunctionCalls(1);
        $config = new \ILIAS\Notifications\Model\ilNotificationConfig('test_type');
        $config->setTitleVar('Test Notification');
        $config->setShortDescriptionVar('This is a test notification');
        $test_obj = new \ILIAS\Notifications\Model\ilNotificationObject($config, $this->user);
        $this->handler->notify($test_obj);

        $this->assertCount(1, $this->database);
    }

    public function testGet0Notification(): void
    {
        $this->createDBFunctionCalls(0, 1, 1);
        $this->assertCount(0, $this->handler->getNotificationsForUser($this->user->getId()));
    }

    public function testGetNotification(): void
    {
        $this->createDBFunctionCalls(1, 1, 2);
        $config = new \ILIAS\Notifications\Model\ilNotificationConfig('test_type');
        $test_obj = new \ILIAS\Notifications\Model\ilNotificationObject($config, $this->user);
        $this->handler->notify($test_obj);

        $this->assertCount(1, $this->handler->getNotificationsForUser($this->user->getId()));
    }

    public function testRemoveNotification(): void
    {
        $this->createDBFunctionCalls(1, 3, 4, 1);
        $config = new \ILIAS\Notifications\Model\ilNotificationConfig('test_type');
        $test_obj = new \ILIAS\Notifications\Model\ilNotificationObject($config, $this->user);
        $this->handler->notify($test_obj);

        $notifications = $this->handler->getNotificationsForUser($this->user->getId());

        $this->assertCount(1, $notifications);
        $this->assertTrue($this->handler->removeNotification($notifications[0]->getId()));
        $this->assertCount(0, $this->handler->getNotificationsForUser($this->user->getId()));
    }

    public function testRemoveNoNotification(): void
    {
        $this->createDBFunctionCalls(0, 2, 2, 0);
        $this->assertCount(0, $this->handler->getNotificationsForUser($this->user->getId()));
        $this->assertFalse($this->handler->removeNotification(3));
    }

    public function testCreateMultipleUniqueNotifications(): void
    {
        $this->createDBFunctionCalls(3, 0, 0, 3);
        $config = new \ILIAS\Notifications\Model\ilNotificationConfig('who_is_online');
        $config->setTitleVar('Unique Test Notification');
        $config->setShortDescriptionVar('This is a unqiue test notification');
        $test_obj = new \ILIAS\Notifications\Model\ilNotificationObject($config, $this->user);
        $this->handler->notify($test_obj);
        $this->handler->notify($test_obj);
        $this->handler->notify($test_obj);

        $this->assertCount(1, $this->database);
    }
}
