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

namespace ILIAS\TermsOfService;

use ilDBConstants;
use ilDBInterface;
use ILIAS\DI\Container;
use ILIAS\LegalDocuments\Consumer as ConsumerInterface;
use ILIAS\LegalDocuments\UseSlot;
use ilObjUser;
use ILIAS\LegalDocuments\LazyProvide;
use ILIAS\LegalDocuments\ConsumerToolbox\Blocks;
use Closure;
use ILIAS\LegalDocuments\ConsumerToolbox\ConsumerSlots\PublicApi;

class Consumer implements ConsumerInterface
{
    private readonly Container $container;
    private readonly Closure $lazy_users;

    public const ID = 'tos';
    public const GOTO_NAME = 'agreement';

    public function __construct(?Container $container = null)
    {
        $this->container = $container ?? $GLOBALS['DIC'];
    }

    public function id(): string
    {
        return self::ID;
    }

    public function uses(UseSlot $slot, LazyProvide $provide): UseSlot
    {
        $blocks = new Blocks($this->id(), $this->container, $provide);
        $default = $blocks->defaultMappings();
        $global_settings = new Settings($blocks->selectSettingsFrom($blocks->readOnlyStore($blocks->globalStore())));
        $is_active = $global_settings->enabled()->value();
        $build_user = fn(ilObjUser $user) => $blocks->user($global_settings, new UserSettings($user, $blocks->selectSettingsFrom(
            $blocks->userStore($user)
        ), $this->container->refinery()), $user);
        $public_api = new PublicApi($is_active, $build_user);
        $slot = $slot->hasDocuments($default->contentAsComponent(), $default->conditionDefinitions())
                     ->hasHistory()
                     ->hasPublicApi($public_api);

        if (!$is_active) {
            return $slot->hasPublicPage($blocks->notAvailable(...), self::GOTO_NAME);
        }

        $user = $build_user($this->container->user());
        $constraint = $this->container->refinery()->custom()->constraint(...);

        return $slot->canWithdraw($blocks->slot()->withdrawProcess($user, $global_settings, $this->userHasWithdrawn(...)))
                    ->hasAgreement($blocks->slot()->agreement($user, $global_settings), self::GOTO_NAME)
                    ->showInFooter($blocks->slot()->modifyFooter($user))
                    ->showOnLoginPage($blocks->slot()->showOnLoginPage())
                    ->onSelfRegistration($blocks->slot()->selfRegistration($user, $build_user))
                    ->hasOnlineStatusFilter($blocks->slot()->onlineStatusFilter($this->usersWhoDidntAgree($this->container->database())))
                    ->hasUserManagementFields($blocks->userManagementAgreeDateField($build_user, 'tos_agree_date', 'tos'))
                    ->canReadInternalMails($blocks->slot()->canReadInternalMails($build_user))
                    ->canUseSoapApi($constraint(fn($u) => !$public_api->needsToAgree($u), 'TOS not accepted.'));
    }

    private function userHasWithdrawn(): void
    {
        $this->container['ilAppEventHandler']->raise(
            'Services/TermsOfService',
            'withdraw',
            ['event' => $this->container->user()]
        );
    }

    private function usersWhoDidntAgree(ilDBInterface $database): Closure
    {
        return function (array $users) use ($database): array {
            $users = $database->in('usr_id', $users, false, ilDBConstants::T_INTEGER);
            $result = $database->query('SELECT usr_id FROM usr_data WHERE agree_date IS NULL AND ' . $users);

            return array_map(intval(...), array_column($database->fetchAll($result), 'usr_id'));
        };
    }
}
