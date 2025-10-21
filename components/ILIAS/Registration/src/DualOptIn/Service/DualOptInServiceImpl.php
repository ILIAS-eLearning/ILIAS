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

use DateInterval;
use DateTimeInterface;
use ilAccountRegistrationMail;
use ilComponentLogger;
use ilDBInterface;
use ILIAS\Data\Clock\ClockFactoryImpl;
use ILIAS\Data\ObjectId;
use ILIAS\Registration\DualOptIn\Entity\PendingRegistration;
use ILIAS\Registration\DualOptIn\ValueObjects\PendingRegistrationHash;
use ILIAS\Registration\DualOptIn\ValueObjects\PendingRegistrationId;
use ILIAS\Registration\DualOptIn\Exception\PendingRegistrationExpiredException;
use ILIAS\Registration\DualOptIn\Exception\PendingRegistrationNotFoundException;
use ILIAS\Registration\DualOptIn\Repository\PendingRegistrationRepository;
use ILIAS\User\Settings\NewAccountMail\Repository as NewAccountMailRepository;
use ilLoggerFactory;
use ilObjectFactory;
use ilObjUser;
use ilRegistrationMimeMailNotification;
use ilRegistrationSettings;
use ilSecuritySettingsChecker;
use ilSoapClient;

class DualOptInServiceImpl implements DualOptInService
{
    public const string ID = "reg_hash_service";

    public function __construct(
        protected readonly PendingRegistrationRepository $pending_reg_repository,
        protected readonly ilDBInterface $db,
        protected readonly ilComponentLogger $logger,
    ) {
    }

    /**
     * @throws PendingRegistrationNotFoundException
     * @throws PendingRegistrationExpiredException
     */
    public function verifyHashAndActivateUser(PendingRegistrationHash $hash): ilObjUser
    {
        $pending_reg = $this->verifyHash($hash);

        /** @var ilObjUser $user */
        $user = ilObjectFactory::getInstanceByObjId($pending_reg->getUserId());
        $this->activateUser($user);

        $this->pending_reg_repository->deleteById($pending_reg->getId());

        return $user;
    }

    /**
     * @throws PendingRegistrationNotFoundException
     * @throws PendingRegistrationExpiredException
     */
    private function verifyHash(PendingRegistrationHash $hash): PendingRegistration
    {
        $pending_reg = $this->pending_reg_repository->findByHashValue($hash->toString());
        if (!$pending_reg) {
            throw new PendingRegistrationNotFoundException();
        }

        $lifetime = (new ilRegistrationSettings())->getRegistrationHashLifetime();
        if ($lifetime > 0) {
            $interval = new DateInterval("PT{$lifetime}S");
            $cutoff = (new ClockFactoryImpl())->utc()->now()->sub($interval);
            $created = $pending_reg->getCreateDate();

            if ($created < $cutoff) {
                $this->triggerExpiredUserCleanup($pending_reg->getUserId());
                throw new PendingRegistrationExpiredException();
            }
        }

        return $pending_reg;
    }

    public function distributeMailsOnRegistration(ilObjUser $user, ilRegistrationSettings $settings): void
    {
        $pending_reg = $this->createPendingRegistration($user->getId());

        $mail = new ilRegistrationMimeMailNotification($user, $pending_reg, $settings->getRegistrationHashLifetime());
        $mail->setType(ilRegistrationMimeMailNotification::TYPE_NOTIFICATION_ACTIVATION);
        $mail->setRecipients([$user]);
        $mail->send();
    }

    private function createPendingRegistration(int $usr_id): PendingRegistration
    {
        $uuid = PendingRegistrationId::create();
        $user_id = new ObjectId($usr_id);
        $hash = $this->pending_reg_repository->findNewHash();
        $creation_date = (new ClockFactoryImpl())->utc()->now();

        $pending_reg = new PendingRegistration($uuid, $user_id, $hash, $creation_date);
        $this->pending_reg_repository->store($pending_reg);

        return $pending_reg;
    }

    public function deleteExpiredUserObjects(int $usr_id): void
    {
        $this->logger->debug(
            'Started deletion of inactive user objects with expired confirmation hash values (dual opt in) ...'
        );
        $lifetime = (new ilRegistrationSettings())->getRegistrationHashLifetime();

        if ($lifetime <= 0) {
            $this->logger->debug('Registration hash lifetime is <= 0, kipping deletion.');
            return;
        }

        $interval = new DateInterval("PT{$lifetime}S");
        $cutoff = (new ClockFactoryImpl())->utc()->now()->sub($interval);

        $deleted_regs = $this->pending_reg_repository->deleteExpired($cutoff->getTimestamp(), $usr_id);

        $this->logger->info(sprintf(
            '%d inactive user objects eligible for deletion found and deleted (cutoff: %s, lifetime: %d s).',
            count($deleted_regs),
            $cutoff->format(DateTimeInterface::ATOM),
            $lifetime
        ));

        $num_deleted_users = 0;
        foreach ($deleted_regs as $deleted_reg) {
            $user = ilObjectFactory::getInstanceByObjId($deleted_reg->getUserId(), false);
            if (!($user instanceof ilObjUser)) {
                continue;
            }

            $this->logger->info(sprintf(
                'Deleting user (login: %s | id: %d) – expired dual opt-in (created: %s, cutoff: %s, lifetime: %d s)',
                $user->getLogin(),
                $user->getId(),
                $deleted_reg->getCreateDate()->format(ilObjUser::DATABASE_DATE_FORMAT),
                $cutoff->format(DateTimeInterface::ATOM),
                $lifetime
            ));

            $user->delete();
            ++$num_deleted_users;
        }

        $this->logger->info(sprintf(
            '%d inactive user objects with expired confirmation hash values (dual opt-in) deleted.',
            $num_deleted_users
        ));
    }

    private function activateUser(ilObjUser $user): void
    {
        $user->setActive(true);

        $settings = new ilRegistrationSettings();

        $password = '';
        if ($settings->passwordGenerationEnabled()) {
            $password = ilSecuritySettingsChecker::generatePasswords(1)[0];
            $user->setPasswd($password, ilObjUser::PASSWD_PLAIN);
            $user->setLastPasswordChangeTS((new ClockFactoryImpl())->utc()->now()->getTimestamp());
        }

        $user->update();

        $this->sendRegistrationMail($user, $settings, $password);
    }

    private function triggerExpiredUserCleanup(int $usr_id): void
    {
        $soap_client = new ilSoapClient();
        $soap_client->setResponseTimeout(1);
        $soap_client->enableWSDL(true);
        $soap_client->init();

        $this->logger->info(
            'Triggered soap call (background process) for deletion of inactive user objects with expired confirmation hash values (dual opt in) ...'
        );

        $sid = session_id() . '::' . CLIENT_ID;
        $soap_client->call('deleteExpiredDualOptInUserObjects', [$sid, $usr_id]);
    }

    private function sendRegistrationMail(ilObjUser $user, ilRegistrationSettings $settings, string $password): void
    {
        $account_mail = (new ilAccountRegistrationMail(
            $settings,
            ilLoggerFactory::getLogger('user'),
            new NewAccountMailRepository($this->db)
        ))->withEmailConfirmationRegistrationMode();

        if ($user->getPref('reg_target') ?? '') {
            $account_mail = $account_mail->withPermanentLinkTarget($user->getPref('reg_target'));
        }

        $account_mail->send($user, $password);
    }
}
