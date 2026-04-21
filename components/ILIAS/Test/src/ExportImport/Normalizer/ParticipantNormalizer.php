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

namespace ILIAS\Test\ExportImport\Normalizer;

use DateTimeImmutable;
use ILIAS\Test\Participants\Participant;
use ILIAS\TestQuestionPool\ExportImport\Foundation\Contracts\Normalizer;
use ILIAS\TestQuestionPool\ExportImport\Foundation\Contracts\Transformations;
use ILIAS\TestQuestionPool\ExportImport\Foundation\Normalizing\Attributes\Normalizes;
use ILIAS\TestQuestionPool\ExportImport\Foundation\Normalizing\Envelopes\Id;
use ILIAS\TestQuestionPool\ExportImport\Foundation\Normalizing\NormalizingException;

/**
 * @implements Normalizer<Participant, array>
 */
#[Normalizes(Participant::class)]
class ParticipantNormalizer implements Normalizer
{
    public function __construct(
        private readonly Transformations $tt,
    ) {
    }

    /**
     * @inheritDoc
     */
    public function normalize($value): array|float|bool|int|string|null
    {
        if (!$value instanceof Participant) {
            throw new NormalizingException('Invalid value', $value);
        }

        return [
            'user_id' => $this->tt->normalize(new Id($value->getUserId(), 'user')),
            'active_id' => $this->tt->normalize(new Id($value->getActiveId(), 'participant')),
            'test_id' => $this->tt->normalize(new Id($value->getTestId(), 'test')),
            'anonymous_id' => $value->getAnonymousId(),
            'firstname' => $value->getFirstname(),
            'lastname' => $value->getLastname(),
            'login' => $value->getLogin(),
            'importname' => $value->getImportname(),
            'matriculation' => $value->getMatriculation(),
            'extra_time' => $value->getExtraTime(),
            'attempts' => $value->getAttempts(),
            'client_ip_from' => $value->getClientIpFrom(),
            'client_ip_to' => $value->getClientIpTo(),
            'invitation_date' => $value->getInvitationDate(),
            'submitted' => $value->getSubmitted(),
            'last_started_attempt' => $value->getLastStartedAttempt(),
            'last_finished_attempt' => $value->getLastFinishedAttempt(),
            'unfinished_attempts' => $value->hasUnfinishedAttempts(),
            'first_access' => $this->tt->normalize($value->getFirstAccess()),
            'last_access' => $this->tt->normalize($value->getLastAccess()),
            'scoring_finalized' => $value->isScoringFinalized(),
        ];
    }

    /**
     * @inheritDoc
     */
    public function denormalize(array|float|bool|int|string|null $value, string $type): Participant
    {
        if ($type !== Participant::class) {
            throw new NormalizingException("Invalid type for Participant: {$type}");
        }

        return new Participant(
            $this->tt->denormalize($value['user_id'], Id::class)->getId(),
            $this->tt->denormalize($value['active_id'], Id::class)->getId(),
            $this->tt->denormalize($value['test_id'], Id::class)->getId(),
            $this->tt->nullableString($value['anonymous_id']),
            $this->tt->string($value['firstname']),
            $this->tt->string($value['lastname']),
            $this->tt->string($value['login']),
            $this->tt->nullableString($value['importname']),
            $this->tt->string($value['matriculation']),
            $this->tt->int($value['extra_time']),
            $this->tt->int($value['attempts']),
            $this->tt->nullableString($value['client_ip_from']),
            $this->tt->nullableString($value['client_ip_to']),
            $this->tt->nullableInt($value['invitation_date']),
            $this->tt->nullableBool($value['submitted']),
            $this->tt->nullableInt($value['last_started_attempt']),
            $this->tt->nullableInt($value['last_finished_attempt']),
            $this->tt->bool($value['unfinished_attempts']),
            $this->tt->denormalize($value['first_access'], DateTimeImmutable::class),
            $this->tt->denormalize($value['last_access'], DateTimeImmutable::class),
            $this->tt->bool($value['scoring_finalized']),
        );
    }
}
