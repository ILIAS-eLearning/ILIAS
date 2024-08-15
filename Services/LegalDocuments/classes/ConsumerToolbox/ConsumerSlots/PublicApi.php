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

namespace ILIAS\LegalDocuments\ConsumerToolbox\ConsumerSlots;

use DateTimeImmutable;
use ILIAS\LegalDocuments\ConsumerSlots\PublicApi as PublicApiInterface;
use ILIAS\LegalDocuments\ConsumerToolbox\User;
use ilObjUser;
use WeakMap;
use Closure;

class PublicApi implements PublicApiInterface
{
    /**
     * @var WeakMap<ilObjUser, User>
     */
    private readonly WeakMap $cache;

    public function __construct(private readonly bool $active, private readonly Closure $build_user)
    {
        $this->cache = new WeakMap();
    }

    /**
     * Returns wether or not the corresponding consumer is active.
     * Please note that this doesn't influence the other methods.
     */
    public function active(): bool
    {
        return $this->active;
    }

    public function agreed(ilObjUser $user): bool
    {
        return $this->everAgreed($user);
    }

    public function agreedToCurrentlyMatchingDocument(ilObjUser $user): bool
    {
        return !$this->user($user)->needsToAcceptNewDocument();
    }

    public function everAgreed(ilObjUser $user): bool
    {
        return !$this->user($user)->neverAgreed();
    }

    public function canAgree(ilObjUser $user): bool
    {
        return !$this->user($user)->cannotAgree();
    }

    public function needsToAgree(ilObjUser $user): bool
    {
        return !$this->canAgree($user)
            && $this->user($user)->needsToAcceptNewDocument();
    }

    public function agreeDate(ilObjUser $user): ?DateTimeImmutable
    {
        return $this->user($user)->agreeDate()->value();
    }

    private function user(ilObjUser $user): User
    {
        if (!isset($this->cache[$user])) {
            $this->cache[$user] = ($this->build_user)($user);
        }

        return $this->cache[$user];
    }
}
