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

namespace ILIAS\Registration\DualOptIn\Service;

use ILIAS\Data\Clock\ClockFactory;
use ILIAS\Data\ObjectId;
use ILIAS\Registration\DualOptIn\Entity\PendingRegistration;
use ILIAS\Registration\DualOptIn\ValueObjects\PendingRegistrationHash;
use ILIAS\Registration\DualOptIn\Exception\PendingRegistrationExpiredException;
use ILIAS\Registration\DualOptIn\Exception\PendingRegistrationNotFoundException;
use ILIAS\Registration\DualOptIn\Exception\PendingRegistrationAlreadyConfirmedException;
use ILIAS\Registration\DualOptIn\Repository\PendingRegistrationRepository;
use ILIAS\User\Settings\NewAccountMail\Repository as NewAccountMailRepository;
use ILIAS\Registration\DualOptIn\Mail\DualOptInMail;

final readonly class DualOptInServiceImpl implements DualOptInService
{
    public const string ID = 'reg_hash_service';

    public function __construct(
        private \ilRegistrationSettings $settings,
        private PendingRegistrationRepository $pending_reg_repository,
        private \ilDBInterface $db,
        private \ilComponentLogger $logger,
        private ClockFactory $clock_factory
    ) {
    }

    public function verifyHashAndActivateUser(PendingRegistrationHash $hash): \ilObjUser
    {
        $pending_reg = $this->findConfirmableRegistration($hash);

        /** @var \ilObjUser $user */
        $user = \ilObjectFactory::getInstanceByObjId($pending_reg->userId()->toInt());
        $this->activateUser($user);

        $this->pending_reg_repository->delete(
            $pending_reg->withConfirmed()
        );

        return $user;
    }

    /**
     * @throws PendingRegistrationNotFoundException
     * @throws PendingRegistrationExpiredException
     * @throws PendingRegistrationAlreadyConfirmedException
     */
    private function findConfirmableRegistration(PendingRegistrationHash $hash): PendingRegistration
    {
        $pending_reg = $this->pending_reg_repository->findByHashValue($hash->toString());
        if ($pending_reg === null) {
            throw new PendingRegistrationNotFoundException();
        }

        $lifetime = $this->settings->getRegistrationHashLifetime();
        $pending_reg = $pending_reg->withEvaluatedState(
            $this->clock_factory->utc()->now(),
            $lifetime > 0 ? $lifetime : null
        );

        if ($pending_reg->isConfirmed()) {
            throw new PendingRegistrationAlreadyConfirmedException();
        }

        if ($pending_reg->isExpired()) {
            $this->triggerExpiredUserCleanup($pending_reg);
            throw new PendingRegistrationExpiredException();
        }

        return $pending_reg;
    }

    public function distributeMailsOnRegistration(\ilObjUser $user): void
    {
        $pending_reg = $this->createPendingRegistration($user->getId());

        $mail = new DualOptInMail(
            $user,
            $pending_reg,
            $this->settings->getRegistrationHashLifetime()
        );
        $mail->setRecipients([$user]);
        $mail->send();
    }

    private function createPendingRegistration(int $usr_id): PendingRegistration
    {
        $pending_reg = new PendingRegistration(
            $this->pending_reg_repository->nextIdentity(),
            new ObjectId($usr_id),
            $this->pending_reg_repository->findNewHash(),
            $this->clock_factory->utc()->now()
        );
        $this->pending_reg_repository->store($pending_reg);

        return $pending_reg;
    }

    public function deleteExpiredUserObjects(?int $usr_id_to_prioritize = null): int
    {
        $this->logger->debug(
            'Started deletion of inactive user objects with expired confirmation hash values (dual opt in) ...'
        );

        $lifetime = $this->settings->getRegistrationHashLifetime();
        if ($lifetime <= 0) {
            $this->logger->debug('Registration hash lifetime is <= 0, skipping deletion.');
            return 0;
        }

        $now = $this->clock_factory->utc()->now();
        $interval = new \DateInterval("PT{$lifetime}S");
        $cutoff = $now->sub($interval);

        $expired_regs = array_filter(
            array_map(
                static fn(PendingRegistration $reg): PendingRegistration => $reg->withEvaluatedState($now, $lifetime),
                $this->pending_reg_repository->findExpired($cutoff->getTimestamp(), $usr_id_to_prioritize)
            ),
            static fn(PendingRegistration $reg): bool => $reg->isExpired()
        );

        $this->logger->info(
            \sprintf(
                '%d inactive user objects eligible for deletion found and deleted (cutoff: %s, lifetime: %d s).',
                \count($expired_regs),
                $cutoff->format(\DateTimeInterface::ATOM),
                $lifetime
            )
        );

        $this->pending_reg_repository->delete(...$expired_regs);

        $num_deleted_users = 0;
        foreach ($expired_regs as $expired_reg) {
            $user = \ilObjectFactory::getInstanceByObjId($expired_reg->userId()->toInt(), false);
            if (!($user instanceof \ilObjUser)) {
                continue;
            }

            $this->logger->info(
                \sprintf(
                    'Deleting user (login: %s | id: %d) – expired dual opt-in (created: %s, cutoff: %s, lifetime: %d s)',
                    $user->getLogin(),
                    $user->getId(),
                    $expired_reg->createdAt()->format(\DateTimeInterface::ATOM),
                    $cutoff->format(\DateTimeInterface::ATOM),
                    $lifetime
                )
            );

            $user->delete();
            ++$num_deleted_users;
        }

        $this->logger->info(
            \sprintf(
                '%d inactive user objects with expired confirmation hash values (dual opt-in) deleted.',
                $num_deleted_users
            )
        );

        return $num_deleted_users;
    }

    private function activateUser(\ilObjUser $user): void
    {
        $user->setActive(true);

        $password = '';
        if ($this->settings->passwordGenerationEnabled()) {
            $password = \ilSecuritySettingsChecker::generatePasswords(1)[0];
            $user->setPasswd($password, \ilObjUser::PASSWD_PLAIN);
            $user->setLastPasswordChangeTS($this->clock_factory->utc()->now()->getTimestamp());
        }

        $user->update();

        $this->sendRegistrationMail($user, $password);
    }

    private function triggerExpiredUserCleanup(PendingRegistration $expired_reg): void
    {
        $soap_client = new \ilSoapClient();
        $soap_client->setResponseTimeout(1);
        $soap_client->enableWSDL(true);
        $soap_client->init();

        $this->logger->info(
            'Triggered soap call (background process) for deletion of inactive ' .
            'user objects with expired confirmation hash values (dual opt in) ...'
        );

        $sid = session_id() . '::' . CLIENT_ID;
        $soap_client->call('deleteExpiredDualOptInUserObjects', [$sid, $expired_reg->userId()->toInt()]);
    }

    private function sendRegistrationMail(\ilObjUser $user, string $password): void
    {
        $account_mail = (new \ilAccountRegistrationMail(
            $this->settings,
            \ilLoggerFactory::getLogger('user'),
            new NewAccountMailRepository($this->db)
        ))->withEmailConfirmationRegistrationMode();

        if ($user->getPref('reg_target') ?? '') {
            $account_mail = $account_mail->withPermanentLinkTarget($user->getPref('reg_target'));
        }

        $account_mail->send($user, $password);
    }
}
