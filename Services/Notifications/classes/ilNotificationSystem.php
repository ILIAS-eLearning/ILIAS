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

namespace ILIAS\Notifications;

use ILIAS\Notifications\Model\ilNotificationConfig;
use ilObjectFactory;
use ilObjUser;
use ilRbacReview;

/**
 * @author Jan Posselt <jposselt@databay.de>
 */
class ilNotificationSystem
{
    /** @var array<string, list<ilNotificationHandler>> */
    private array $handler = [];
    private string $defaultLanguage = 'en';
    private readonly ilRbacReview $rbacReview;

    public function __construct(ilRbacReview $rbacReview = null)
    {
        $this->addHandler('osd', new ilNotificationOSDHandler());
        $this->addHandler('mail', new ilNotificationMailHandler());

        if ($rbacReview === null) {
            global $DIC;
            $rbacReview = $DIC->rbac()->review();
        }
        $this->rbacReview = $rbacReview;
    }

    private function addHandler(string $channel, ilNotificationHandler $handler): void
    {
        if (!array_key_exists($channel, $this->handler)) {
            $this->handler[$channel] = [];
        }

        $this->handler[$channel][] = $handler;
    }

    /**
     * @param list<int> $users
     */
    private function toUsers(ilNotificationConfig $notification, array $users, bool $process_async = false): void
    {
        if ($process_async === false) {
            $adminConfig = ilNotificationDatabaseHandler::loadUserConfig(-1);
            $usersWithCustomConfig = ilNotificationDatabaseHandler::getUsersWithCustomConfig($users);
            $channels = ilNotificationDatabaseHandler::getAvailableChannels();
            $types = ilNotificationDatabaseHandler::getAvailableTypes();
            $lang = ilNotificationDatabaseHandler::getTranslatedLanguageVariablesOfNotificationParameters(
                $notification->getLanguageParameters()
            );

            $user_by_handler = [];
            if (isset($types[$notification->getType()]['config_type'])) {
                if ($types[$notification->getType()]['config_type'] === 'set_by_user') {
                    $it = new ilNotificationUserIterator($notification->getType(), $users);
                    $channelsByAdmin = false;
                    foreach ($it as $usr_id => $data) {
                        if (!isset($channels[$data['channel']])) {
                            continue;
                        }
                        if (!isset($user_by_handler[$data['channel']])) {
                            $user_by_handler[$data['channel']] = [];
                        }
                        $user_by_handler[$data['channel']][] = $usr_id;
                    }
                } elseif ($types[$notification->getType()]['config_type'] !== 'disabled') {
                    $channelsByAdmin = true;
                    if (isset($adminConfig[$notification->getType()])) {
                        foreach ($adminConfig[$notification->getType()] as $channel) {
                            if (!isset($channels[$channel]) || !$channels[$channel]) {
                                continue;
                            }
                            $user_by_handler[$channel] = $users;
                        }
                    }
                }
            }

            $userCache = [];

            foreach ($user_by_handler as $handler => $h_users) {
                $handler = $this->handler[$handler];
                foreach ($h_users as $userId) {
                    if (!isset($userCache[$userId])) {
                        $user = ilObjectFactory::getInstanceByObjId($userId, false);
                        if (!($user instanceof ilObjUser)) {
                            continue;
                        }
                        $userCache[$userId] = $user;
                    }
                    $user = $userCache[$userId];

                    $instance = $notification->getUserInstance($user, $lang, $this->defaultLanguage);
                    foreach ($handler as $h) {
                        $h->notify($instance);
                    }
                }
            }
        } else {
            ilNotificationDatabaseHandler::enqueueByUsers($notification, $users);
        }
    }

    private function toListeners(ilNotificationConfig $notification, int $ref_id, bool $process_async = false): void
    {
        if ($process_async === false) {
            $users = ilNotificationDatabaseHandler::getUsersByListener($notification->getType(), $ref_id);
            if ($notification->hasDisableAfterDeliverySet()) {
                ilNotificationDatabaseHandler::disableListeners($notification->getType(), $ref_id);
            }
        } else {
            ilNotificationDatabaseHandler::enqueueByListener($notification, $ref_id);
        }
    }

    /**
     * @param list<int> $roles
     */
    private function toRoles(ilNotificationConfig $notification, array $roles, bool $process_async = false): void
    {
        $users = [];
        foreach ($roles as $role) {
            $users[] = $this->rbacReview->assignedUsers($role);
        }
        $users = array_unique(array_merge(...$users));

        $this->toUsers($notification, $users, $process_async);
    }

    /**
     * @param list<int> $users
     */
    public static function sendNotificationToUsers(
        ilNotificationConfig $notification,
        array $users,
        bool $processAsync = false
    ): void {
        global $DIC;
        $DIC->notifications()->system()->toUsers($notification, $users, $processAsync);
    }

    public static function sendNotificationToListeners(
        ilNotificationConfig $notification,
        int $ref_id,
        bool $processAsync = false
    ): void {
        global $DIC;
        $DIC->notifications()->system()->toListeners($notification, $ref_id, $processAsync);
    }

    /**
     * @param list<int> $roles
     */
    public static function sendNotificationToRoles(
        ilNotificationConfig $notification,
        array $roles,
        bool $processAsync = false
    ): void {
        global $DIC;
        $DIC->notifications()->system()->toRoles($notification, $roles, $processAsync);
    }

    public static function enableListeners(string $module, int $ref_id): void
    {
        ilNotificationDatabaseHandler::enableListeners($module, $ref_id);
    }

    /**
     * @param list<int> $users
     */
    public static function enableUserListeners(string $module, int $ref_id, array $users): void
    {
        if ($users) {
            ilNotificationDatabaseHandler::enableListeners($module, $ref_id, $users);
        }
    }

    public function clear(string $channel = ''): void
    {
        $channels = $this->handler;
        if ($channel !== '') {
            $channels = [$this->handler[$channel]] ?? [];
        }
        foreach ($channels as $c) {
            foreach ($c as $handler) {
                $handler->clear();
            }
        }
    }
}
