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

namespace ILIAS\Notifications\Repository;

use ilDBConstants;
use ilDBInterface;
use ILIAS\Notifications\Model\Push\PushSubscription;
use ilObjUser;

readonly class PushRepository
{
    public function __construct(private ilDBInterface $database, private ilObjUser $user)
    {
    }

    public function addSubscription(PushSubscription $subscription): void
    {
        $this->database->insert(
            'push_subscriptions',
            [
                'endpoint' => [ilDBConstants::T_TEXT, $subscription->getEndpoint()],
                'user_id' => [ilDBConstants::T_INTEGER, $this->user->getId()],
                'p256dh' => [ilDBConstants::T_TEXT, $subscription->getP256dh()],
                'auth' => [ilDBConstants::T_TEXT, $subscription->getAuth()],
            ]
        );
    }

    public function deleteSubscription(string $auth): void
    {
        $this->database->manipulateF('DELETE FROM push_subscriptions WHERE auth = %s', [ilDBConstants::T_TEXT], [$auth]);
    }

    /**
     * @return PushSubscription[]
     */
    public function getUserSubscriptions(?int $user_id = null): array
    {
        $stmt = $this->database->queryF(
            'SELECT * FROM push_subscriptions WHERE user_id = %s',
            [ilDBConstants::T_INTEGER],
            [$user_id ?? $this->user->getId()]
        );

        $result = [];
        while ($row = $stmt->fetchAssoc()) {
            $result[] = new PushSubscription(
                $row['endpoint'],
                $row['auth'],
                $row['p256dh']
            );
        }

        return $result;
    }
}
