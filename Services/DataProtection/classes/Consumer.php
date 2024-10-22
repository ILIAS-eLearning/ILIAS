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

namespace ILIAS\DataProtection;

use ILIAS\LegalDocuments\ConsumerToolbox\UI;
use ILIAS\LegalDocuments\ConsumerToolbox\User;
use ilDBConstants;
use ilDBInterface;
use ILIAS\LegalDocuments\UseSlot;
use ILIAS\LegalDocuments\Consumer as ConsumerInterface;
use ILIAS\Data\Result\Ok;
use ilObjUser;
use ILIAS\LegalDocuments\ConsumerToolbox\Blocks;
use ILIAS\LegalDocuments\LazyProvide;
use ILIAS\LegalDocuments\Provide;
use ILIAS\DI\Container;
use Closure;
use ILIAS\UI\Component\MainControls\Footer;
use ILIAS\LegalDocuments\Value\Document;
use ILIAS\LegalDocuments\ConsumerToolbox\ConsumerSlots\PublicApi;

final class Consumer implements ConsumerInterface
{
    public const ID = 'dpro';
    public const GOTO_NAME = 'data_protection';

    private readonly Container $container;

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
        $build_user = fn(ilObjUser $user) => $blocks->user($global_settings, new UserSettings(
            $blocks->selectSettingsFrom($blocks->userStore($user))
        ), $user);
        $public_api = new PublicApi($is_active, $build_user);
        $slot = $slot->hasDocuments($default->contentAsComponent(), $default->conditionDefinitions())
                     ->hasHistory()
                     ->hasPublicApi($public_api);

        if (!$is_active) {
            return $slot->hasPublicPage($blocks->notAvailable(...), self::GOTO_NAME);
        }

        $user = $build_user($this->container->user());

        $slot = $slot->showOnLoginPage($blocks->slot()->showOnLoginPage());

        $agreement = $blocks->slot()->agreement($user, $global_settings);
        $constraint = $this->container->refinery()->custom()->constraint(...);

        if ($global_settings->noAcceptance()->value()) {
            $slot = $slot->showInFooter($this->showMatchingDocument($user, $blocks->ui(), $provide))
                         ->hasPublicPage($agreement->showAgreement(...), self::GOTO_NAME);
        } else {
            $slot = $slot->canWithdraw($blocks->slot()->withdrawProcess($user, $global_settings, $this->userHasWithdrawn(...)))
                         ->hasAgreement($agreement, self::GOTO_NAME)
                         ->showInFooter($blocks->slot()->modifyFooter($user))
                         ->onSelfRegistration($blocks->slot()->selfRegistration($user, $build_user))
                         ->hasOnlineStatusFilter($blocks->slot()->onlineStatusFilter($this->usersWhoDidntAgree($this->container->database())))
                         ->hasUserManagementFields($blocks->userManagementAgreeDateField($build_user, 'dpro_agree_date', 'dpro'))
                         ->canReadInternalMails($blocks->slot()->canReadInternalMails($build_user))
                         ->canUseSoapApi($constraint(fn($u) => !$public_api->needsToAgree($u), 'Data Protection not agreed.'));
        }

        return $slot;
    }

    private function showMatchingDocument(User $user, UI $ui, Provide $legal_documents): Closure
    {
        return function ($footer) use ($user, $ui, $legal_documents) {
            if ($user->cannotAgree()) {
                return $footer;
            }

            $render = fn(Document $document): Footer => $footer->withAdditionalModalAndTrigger($ui->create()->modal()->roundtrip(
                $document->content()->title(),
                [$legal_documents->document()->contentAsComponent($document->content())]
            ), $ui->create()->button()->shy($ui->txt('usr_agreement'), ''));

            return $user->matchingDocument()
                        ->map($render)
                        ->except(fn() => new Ok($footer))->value();
        };
    }

    private function userHasWithdrawn(): void
    {
        $this->container['ilAppEventHandler']->raise(
            'Services/DataProtection',
            'withdraw',
            ['event' => $this->container->user()]
        );
    }

    private function usersWhoDidntAgree(ilDBInterface $database): Closure
    {
        return function (array $users) use ($database): array {
            $users = $database->in('usr_id', $users, false, ilDBConstants::T_INTEGER);
            $result = $database->query(
                'SELECT usr_id FROM usr_pref WHERE keyword = "dpro_agree_date" AND (value IS NULL OR value = "false" OR value = "") AND ' . $users
            );

            return array_map(intval(...), array_column($database->fetchAll($result), 'usr_id'));
        };
    }
}
