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

namespace ILIAS\LegalDocuments\ConsumerToolbox;

use ILIAS\Data\Result\Error;
use ilObjUser;
use ILIAS\Data\Result;
use ILIAS\Data\Result\Ok;
use ILIAS\LegalDocuments\Provide\ProvideHistory;
use ILIAS\LegalDocuments\Value\Document;
use Closure;
use ILIAS\LegalDocuments\ConsumerToolbox\Setting\BooleanSetting;
use ILIAS\LegalDocuments\Provide;
use ilAuthUtils;
use ILIAS\Data\Clock\ClockInterface as Clock;

class User
{
    /** @var Closure(): Result */
    private readonly Closure $matching_document;

    public function __construct(
        private readonly ilObjUser $user,
        private readonly Settings $settings,
        private readonly UserSettings $user_settings,
        private readonly Provide $legal_documents,
        private readonly Clock $clock
    ) {
        $this->matching_document = $this->lazy(fn() => $this->legal_documents->document()->chooseDocumentFor($this->user));
    }

    public function isLoggedIn(): bool
    {
        return !in_array($this->user->getId(), [ANONYMOUS_USER_ID, 0], true);
    }

    public function cannotAgree(): bool
    {
        return in_array($this->user->getId(), [ANONYMOUS_USER_ID, SYSTEM_USER_ID, 0], true);
    }

    public function neverAgreed(): bool
    {
        return null === $this->agreeDate()->value();
    }

    /**
     * @return Setting<bool>
     */
    public function withdrawalRequested(): Setting
    {
        return $this->user_settings->withdrawalRequested();
    }

    /**
     * @return Setting<?DateTimeImmutable>
     */
    public function agreeDate(): Setting
    {
        return $this->user_settings->agreeDate();
    }

    public function didNotAcceptCurrentVersion(): bool
    {
        $false = fn() => new Ok(false);
        return $this->settings->validateOnLogin()->value() && $this->matchingDocument()->map($this->didNotAccept(...))->except($false)->value();
    }

    public function matchingDocument(): Result
    {
        return ($this->matching_document)();
    }

    public function acceptedDocument(): Result
    {
        return $this->cannotAgree() || $this->neverAgreed() ?
            new Error('User never agreed.') :
            $this->legal_documents->history()->acceptedDocument($this->user);
    }

    public function acceptMatchingDocument(): void
    {
        $this->legal_documents->history()->acceptDocument(
            $this->user,
            $this->matchingDocument()->value()
        );
        $this->agreeDate()->update($this->clock->now());
    }

    public function isLDAPUser(): bool
    {
        return $this->authMode() === (string) ilAuthUtils::AUTH_LDAP;
    }

    public function isExternalAccount(): bool
    {
        return in_array((int) $this->authMode(), [ilAuthUtils::AUTH_PROVIDER_LTI, ilAuthUtils::AUTH_ECS], true);
    }

    public function format(string $format_string): string
    {
        return str_ireplace('[BR]', "\n", sprintf(
            $format_string,
            $this->user->getFullname(),
            $this->user->getLogin(),
            $this->user->getExternalAccount()
        ));
    }

    public function raw(): ilObjUser
    {
        return $this->user;
    }

    private function authMode(): string
    {
        $auth_mode = $this->user->getAuthMode();
        return $auth_mode === 'default' ?
                          $this->settings->authMode()->value() :
                          $auth_mode;
    }

    private function didNotAccept(Document $document): bool
    {
        return !$this->legal_documents->history()->alreadyAccepted($this->user, $document);
    }

    /**
     * @template A
     * @param callable(): A $create_value
     * @return Closure(): A
     */
    private function lazy(callable $create_value): Closure
    {
        $proc = function () use (&$proc, $create_value) {
            $value = $create_value();
            $proc = fn() => $value;
            return $value;
        };
        return function () use (&$proc) {
            return $proc();
        };
    }
}
